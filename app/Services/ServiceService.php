<?php

namespace App\Services;

use App\Models\Service;
use App\Models\ServiceVariant;
use App\Models\Combo;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ServiceService
{
    /**
     * Get all services with category.
     */
    public function getAll()
    {
        return Service::with(['category', 'serviceVariants', 'ownedCombos'])
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Get one service by id.
     */
    public function getOne($id)
    {
        return Service::with([
            'category',
            'serviceVariants.variantAttributes',
            'ownedCombos.comboItems.service',
            'ownedCombos.comboItems.serviceVariant',
        ])->findOrFail($id);
    }

    /**
     * Get services by category.
     */
    public function getByCategory($categoryId)
    {
        return Service::with('category')
            ->where('category_id', $categoryId)
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Get services by category with pagination.
     */
    public function getByCategoryPaginated($categoryId, $limit = 10, $offset = 0)
    {
        return Service::where('category_id', $categoryId)
            ->orderBy('id', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get();
    }

    /**
     * Count services by category.
     */
    public function countByCategory($categoryId)
    {
        return Service::where('category_id', $categoryId)->count();
    }

    /**
     * Get related services.
     */
    public function getRelated($categoryId, $serviceId)
    {
        return Service::where('category_id', $categoryId)
            ->where('id', '!=', $serviceId)
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Get services with limit.
     */
    public function getWithLimit($limit = 10, $offset = 0)
    {
        return Service::with(['category', 'serviceVariants', 'ownedCombos'])
            ->orderBy('id', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get();
    }

    /**
     * Get minimal service list for selectors.
     */
    public function getSimpleList()
    {
        return Service::orderBy('name')
            ->select('id', 'name')
            ->get();
    }

    /**
     * Get base services (single services without variants).
     */
    public function getBaseServices()
    {
        return Service::with('category')
            ->whereNull('deleted_at')
            ->whereDoesntHave('serviceVariants')
            ->orderBy('name')
            ->get(['id', 'name', 'category_id', 'base_price']);
    }

    /**
     * Create a new service.
     */
    public function create(array $serviceData, array $variants = [], array $combos = [])
    {
        $service = new Service();
        return $this->persist($service, $serviceData, $variants, $combos);
    }

    /**
     * Update a service.
     */
    public function update($id, array $serviceData, array $variants = [], array $combos = [])
    {
        $service = Service::findOrFail($id);
        return $this->persist($service, $serviceData, $variants, $combos);
    }

    /**
     * Delete a service.
     */
    public function delete($id)
    {
        $service = Service::findOrFail($id);
        
        // Delete image if exists
        if ($service->image && Storage::disk('public')->exists('legacy/images/products/' . $service->image)) {
            Storage::disk('public')->delete('legacy/images/products/' . $service->image);
        }
        
        return $service->delete();
    }

    /**
     * Search services by name.
     */
    public function search($name)
    {
        return Service::with(['category', 'serviceVariants', 'ownedCombos'])
            ->where('name', 'like', "%{$name}%")
            ->get();
    }

    /**
     * Get service statistics by category.
     */
    public function getStatisticsByCategory()
    {
        return Service::selectRaw('
                service_categories.id,
                service_categories.name,
                COUNT(*) as so_luong
            ')
            ->join('service_categories', 'service_categories.id', '=', 'services.category_id')
            ->groupBy('service_categories.id', 'service_categories.name')
            ->get();
    }

    /**
     * Persist service with relations inside a transaction.
     */
    protected function persist(Service $service, array $serviceData, array $variants, array $combos)
    {
        return DB::transaction(function () use ($service, $serviceData, $variants, $combos) {
            $payload = $this->prepareServicePayload($serviceData, $service->id);
            $service->fill($payload);
            $service->save();

            $variantMap = $this->syncVariants($service, $variants);
            $this->syncCombos($service, $combos, $variantMap);

            return $service->load([
                'category',
                'serviceVariants',
                'ownedCombos.comboItems.serviceVariant',
                'ownedCombos.comboItems.service',
            ]);
        });
    }

    /**
     * Normalize service payload.
     */
    protected function prepareServicePayload(array $data, ?int $serviceId = null): array
    {
        return [
            'category_id' => Arr::get($data, 'category_id'),
            'name' => Arr::get($data, 'name'),
            'description' => Arr::get($data, 'description'),
            'image' => Arr::get($data, 'image'),
            'status' => Arr::get($data, 'status', 'Hoạt động'),
            'base_price' => Arr::get($data, 'base_price'),
            'base_duration' => Arr::get($data, 'base_duration'),
        ];
    }

    /**
     * Sync service variants.
     */
    protected function syncVariants(Service $service, array $variants): array
    {
        $handled = [];
        $map = [];

        foreach ($variants as $uid => $variant) {
            $variantId = Arr::get($variant, 'id');
            $payload = [
                'name' => Arr::get($variant, 'name'),
                'price' => Arr::get($variant, 'price'),
                'duration' => Arr::get($variant, 'duration'),
                'is_default' => $this->boolValue(Arr::get($variant, 'is_default'), false),
                'is_active' => $this->boolValue(Arr::get($variant, 'is_active'), true),
                'notes' => Arr::get($variant, 'notes'),
            ];

            /** @var ServiceVariant $variantModel */
            if ($variantId) {
                $variantModel = $service->serviceVariants()->findOrFail($variantId);
                $variantModel->update($payload);
            } else {
                $variantModel = $service->serviceVariants()->create($payload);
            }

            $this->syncVariantAttributes($variantModel, Arr::get($variant, 'attributes', []));
            $handled[] = $variantModel->id;
            $map[$uid] = $variantModel->id;
        }

        if (!empty($handled)) {
            $service->serviceVariants()->whereNotIn('id', $handled)->delete();
        } else {
            // No variants were provided; remove all existing ones.
            $service->serviceVariants()->delete();
        }

        return $map;
    }

    /**
     * Sync combos and nested items.
     */
    protected function syncCombos(Service $service, array $combos, array $variantMap): void
    {
        $handledComboIds = [];

        foreach ($combos as $comboIndex => $comboData) {
            if (empty($comboData['name'])) {
                continue;
            }

            $variantUids = array_filter((array) Arr::get($comboData, 'variant_uids', []));
            $variantIds = collect($variantUids)
                ->map(fn ($uid) => $variantMap[$uid] ?? null)
                ->filter()
                ->values();

            if ($variantIds->isEmpty()) {
                continue;
            }

            $comboId = Arr::get($comboData, 'id');
            $payload = [
                'name' => Arr::get($comboData, 'name'),
                'slug' => Arr::get($comboData, 'slug') ?: Str::slug(Arr::get($comboData, 'name') . '-' . uniqid()),
                'description' => Arr::get($comboData, 'description'),
                'image' => Arr::get($comboData, 'image'),
                'category_id' => $service->category_id,
                'owner_service_id' => $service->id,
                'price' => Arr::get($comboData, 'price'),
                'status' => Arr::get($comboData, 'status', 'Hoạt động'),
                'sort_order' => Arr::get($comboData, 'sort_order', $comboIndex),
            ];

            /** @var Combo $combo */
            if ($comboId) {
                $combo = $service->ownedCombos()->findOrFail($comboId);
                $combo->update($payload);
            } else {
                $combo = $service->ownedCombos()->create($payload);
            }

            $handledComboIds[] = $combo->id;
            $this->syncComboSelections($combo, $variantIds->all());
        }

        if (!empty($handledComboIds)) {
            $service->ownedCombos()->whereNotIn('id', $handledComboIds)->delete();
        } else {
            $service->ownedCombos()->delete();
        }
    }

    /**
     * Update combo selections based on variant IDs.
     */
    protected function syncComboSelections(Combo $combo, array $variantIds): void
    {
        $variantServiceMap = ServiceVariant::whereIn('id', $variantIds)
            ->pluck('service_id', 'id');

        $handledItemIds = [];
        foreach ($variantIds as $variantId) {
            $payload = [
                'service_id' => $variantServiceMap[$variantId] ?? null,
                'service_variant_id' => $variantId,
                'quantity' => 1,
                'price_override' => null,
                'notes' => null,
            ];

            $comboItem = $combo->comboItems()
                ->updateOrCreate(
                    ['service_variant_id' => $variantId],
                    $payload
                );

            $handledItemIds[] = $comboItem->id;
        }

        if (!empty($handledItemIds)) {
            $combo->comboItems()->whereNotIn('id', $handledItemIds)->delete();
        } else {
            $combo->comboItems()->delete();
        }
    }

    /**
     * Sync variant attributes list.
     */
    protected function syncVariantAttributes(ServiceVariant $variant, array $attributes): void
    {
        $variant->variantAttributes()->delete();

        foreach ($attributes as $attribute) {
            $name = trim((string) Arr::get($attribute, 'name'));
            $value = trim((string) Arr::get($attribute, 'value'));

            if ($name === '' && $value === '') {
                continue;
            }

            $variant->variantAttributes()->create([
                'attribute_name' => $name,
                'attribute_value' => $value,
            ]);
        }
    }

    /**
     * Helper to cast truthy values.
     */
    protected function boolValue($value, bool $default = false): bool
    {
        if ($value === null) {
            return $default;
        }

        $result = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        return $result ?? $default;
    }
}
