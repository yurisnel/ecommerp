<?php

use App\Models\Warehouse;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\SalesOrder;
use App\Models\Product;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting Debug Seed...\n";

try {
    echo "Creating Warehouse...\n";
    $w = Warehouse::factory()->create();
    echo "Warehouse Created: " . $w->id . "\n";
} catch (\Throwable $e) {
    echo "ERROR Warehouse: " . $e->getMessage() . "\n";
}

try {
    echo "Creating Customer with Address...\n";
    $c = Customer::factory()->create();
    echo "Customer Created: " . $c->id . "\n";

    $a = CustomerAddress::factory()->create(['customer_id' => $c->id]);
    echo "Address Created: " . $a->id . "\n";
} catch (\Throwable $e) {
    echo "ERROR Customer/Address: " . $e->getMessage() . "\n";
}

try {
    echo "Creating Sales Order...\n";
    $o = SalesOrder::factory()->create();
    echo "Order Created: " . $o->id . "\n";
} catch (\Throwable $e) {
    echo "ERROR SalesOrder: " . $e->getMessage() . "\n";
}

echo "Done.\n";
