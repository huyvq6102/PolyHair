<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "--- DEBUG START ---\\n";

// 1. List Services matching "nam"
echo "Searching for services with 'nam'...\\n";
$services = \App\Models\Service::where('name', 'like', '%nam%')->with('serviceVariants')->get();
if ($services->isEmpty()) {
    echo "No services found with 'nam'. Listing first 5 services:\\n";
    $services = \App\Models\Service::with('serviceVariants')->take(5)->get();
}

foreach ($services as $s) {
    echo "Service: [{$s->id}] {$s->name}\\n";
    foreach ($s->serviceVariants as $v) {
        echo "  - Variant: [{$v->id}] {$v->name} (ServiceID: {$v->service_id})\\n";
    }
}

// 2. List Promotions and their relations
echo "\\nPromotions:\\n";
$promotions = \App\Models\Promotion::with(['services', 'serviceVariants'])->get();

foreach ($promotions as $p) {
    echo "Promo: [{$p->id}] Code: {$p->code} (Scope: {$p->apply_scope})\\n";
    
    $sIds = $p->services->pluck('id')->toArray();
    echo "  - Services: " . implode(', ', $sIds) . "\\n";
    
    $vIds = $p->serviceVariants->pluck('id')->toArray();
    echo "  - Variants: " . implode(', ', $vIds) . "\\n";
    
    // Check Raw DB Pivot
    $pivots = \Illuminate\Support\Facades\DB::table('promotion_service')->where('promotion_id', $p->id)->get();
    echo "  - Raw Pivot Rows: " . $pivots->count() . "\\n";
    foreach ($pivots as $row) {
        echo "    * S:{$row->service_id} | V:{$row->service_variant_id} | C:{$row->combo_id}\\n";
    }
}

echo "--- DEBUG END ---\\n";

