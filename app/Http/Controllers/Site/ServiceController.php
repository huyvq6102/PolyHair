<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Services\ServiceService;
use App\Services\TypeService;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    protected $serviceService;
    protected $typeService;

    public function __construct(ServiceService $serviceService, TypeService $typeService)
    {
        $this->serviceService = $serviceService;
        $this->typeService = $typeService;
    }

    /**
     * Display a listing of services.
     */
    public function index(Request $request)
    {
        $types = $this->typeService->getAll();
        $typeId = $request->get('type');

        if ($typeId) {
            // For backward compatibility, map type to category
            $services = Service::with(['category', 'serviceVariants', 'ownedCombos'])
                ->where('category_id', $typeId)
                ->orderBy('id', 'desc')
                ->paginate(6);
        } else {
            $services = Service::with(['category', 'serviceVariants', 'ownedCombos'])
                ->orderBy('id', 'desc')
                ->paginate(6);
        }

        return view('site.service-list', compact('services', 'types', 'typeId'));
    }

    /**
     * Display the specified service.
     */
    public function show($id)
    {
        $service = $this->serviceService->getOne($id);
        $relatedServices = $this->serviceService->getRelated($service->category_id ?? 0, $id);

        return view('site.service-detail', compact('service', 'relatedServices'));
    }
}
