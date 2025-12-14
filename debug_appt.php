<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$id = 20;
echo "Checking Appointment $id...\n";
$appt = \App\Models\Appointment::with('appointmentDetails')->find($id);

if (!$appt) {
    echo "Appointment not found.\n";
    exit;
}

echo "Appointment ID: {$appt->id}, Status: {$appt->status}\n";
foreach ($appt->appointmentDetails as $d) {
    echo "Detail ID: {$d->id}\n";
    echo "  - Variant ID: " . var_export($d->service_variant_id, true) . "\n";
    echo "  - Combo ID: " . var_export($d->combo_id, true) . "\n";
    echo "  - Notes: " . var_export($d->notes, true) . "\n";
    echo "  - Price Snapshot: {$d->price_snapshot}\n";
}

