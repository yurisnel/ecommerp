<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\WarehouseController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\CustomerGroupController;
use App\Http\Controllers\Admin\CustomerAddressController;
use App\Http\Controllers\Admin\DiscountRuleController;
use App\Http\Controllers\Admin\SalesOrderController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\ShippingController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\UploadController;
use App\Http\Controllers\Admin\SalesChannelController;
use App\Http\Controllers\Admin\OrderStatusController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

use App\Http\Controllers\Api\AuthController;

Route::prefix('v1')->group(function () {
    // Public Auth Routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Protected Routes (Sanctum)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'profile']);
    });
});

// Admin Routes - In a real app, these should be protected by auth:sanctum
Route::prefix('v1')->group(function () {

    // Media & Uploads
    Route::post('upload', [UploadController::class, 'upload']);
    Route::get('upload-test', function () {
        return response()->json(['status' => 'ok']);
    });

    // Auth & Users
    Route::apiResource('users', UserController::class);
    Route::apiResource('order-statuses', OrderStatusController::class);
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('permissions', PermissionController::class);

    // Products & Inventory
    Route::apiResource('products', ProductController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('suppliers', SupplierController::class);
    Route::apiResource('warehouses', WarehouseController::class);

    // Inventory Operations
    Route::get('inventory', [InventoryController::class, 'index']);
    Route::get('inventory/stats', [InventoryController::class, 'getStats']);
    Route::get('inventory/alerts', [InventoryController::class, 'getAlerts']);
    Route::get('inventory/entries', [InventoryController::class, 'getProductEntries']);
    Route::get('inventory/entries/{id}', [InventoryController::class, 'showEntry']);
    Route::get('inventory/movements', [InventoryController::class, 'getStockMovements']);
    Route::post('inventory/entry', [InventoryController::class, 'createEntry']);
    Route::put('inventory/entries/{id}', [InventoryController::class, 'updateEntry']);
    Route::delete('inventory/entries/{id}', [InventoryController::class, 'deleteEntry']);
    Route::post('inventory/adjust', [InventoryController::class, 'adjustInventory']);
    Route::post('inventory/transfer', [InventoryController::class, 'transferStock']);
    Route::get('inventory/product/{id}', [InventoryController::class, 'getProductStatus']);

    // Customers
    Route::get('customers/stats', [CustomerController::class, 'stats']);
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('customer-groups', CustomerGroupController::class);

    // Customer Addresses
    Route::get('customers/{customerId}/addresses', [CustomerAddressController::class, 'indexByCustomer']);
    Route::post('addresses', [CustomerAddressController::class, 'store']);
    Route::put('addresses/{id}', [CustomerAddressController::class, 'update']);
    Route::delete('addresses/{id}', [CustomerAddressController::class, 'destroy']);
    Route::post('addresses/{id}/default', [CustomerAddressController::class, 'setDefault']);

    // Sales & Orders
    Route::get('orders/recent', [SalesOrderController::class, 'recent']);
    Route::get('orders/stats', [SalesOrderController::class, 'stats']);
    Route::post('orders/payment', [SalesOrderController::class, 'processPayment']);
    Route::post('orders/{id}/update-status', [SalesOrderController::class, 'updateStatus']);
    Route::get('orders/{id}/valid-transitions', [SalesOrderController::class, 'getValidTransitions']);
    Route::delete('orders/{orderId}/items/{itemId}', [SalesOrderController::class, 'deleteOrderItem']);

    Route::apiResource('orders', SalesOrderController::class);
    Route::apiResource('sales-channels', SalesChannelController::class);

    // Discounts
    Route::get('discounts', [DiscountRuleController::class, 'index']);
    Route::post('discounts', [DiscountRuleController::class, 'store']);
    Route::post('discounts/calculate', [DiscountRuleController::class, 'calculateDiscount']);

    // Shipping
    Route::get('shipping/methods', [ShippingController::class, 'listMethods']);
    Route::get('shipping/zones', [ShippingController::class, 'listZones']);
    Route::post('shipping/available', [ShippingController::class, 'getAvailableMethods']);
    Route::post('shipping/calculate', [ShippingController::class, 'calculateCost']);

    // Payment Methods
    Route::get('payment-methods', [PaymentMethodController::class, 'index']);
    Route::get('payment-methods/active', [PaymentMethodController::class, 'active']);
    Route::put('payment-methods/{id}/config', [PaymentMethodController::class, 'updateConfig']);
    Route::post('payment-methods/{id}/toggle', [PaymentMethodController::class, 'toggleStatus']);

    // Employees
    Route::apiResource('employees', EmployeeController::class);
    Route::apiResource('departments', DepartmentController::class);
});
