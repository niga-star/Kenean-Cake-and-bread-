<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Branch;
use App\Models\Product;
use App\Models\BranchInventory;
use App\Models\InventoryTransaction;
use App\Models\StockDispatch;
use App\Models\StockDispatchItem;
use App\Models\ReceivedStock;
use App\Models\AuditLog;
use App\Http\Controllers\StockDispatchController;
use App\Http\Controllers\InventoryController;

$passed = 0;
$failed = 0;

function assertCondition($cond, $msg) {
    global $passed, $failed;
    if ($cond) {
        echo "  [PASS] {$msg}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$msg}\n";
        $failed++;
    }
}

echo "=================================================================\n";
echo "SweetBakes Branch Manager Stock Management Verification Test Suite\n";
echo "=================================================================\n\n";

// Setup test branches and users
$mainBranch = Branch::where('branch_type', 'Main')->first() ?? Branch::create([
    'branch_name' => 'Main Production Facility',
    'branch_type' => 'Main',
    'address' => 'Bole Medhanealem, Addis Ababa',
    'phone' => '+251911000001',
    'status' => 'Active',
]);

$regBranch = Branch::where('branch_type', 'Regular')->first() ?? Branch::create([
    'branch_name' => 'Kazanchis Branch',
    'branch_type' => 'Regular',
    'address' => 'Kazanchis, Addis Ababa',
    'phone' => '+251911000002',
    'status' => 'Active',
]);

$mainManager = User::where('email', 'mainmgr_test@sweetbakes.com')->first() ?? User::create([
    'full_name' => 'Test Main Manager',
    'email' => 'mainmgr_test@sweetbakes.com',
    'phone' => '+251911111111',
    'password' => bcrypt('Password123!'),
    'role' => 'Main Branch Manager',
    'branch_id' => $mainBranch->branch_id,
    'status' => 'Active',
]);

$regManager = User::where('email', 'regmgr_test@sweetbakes.com')->first() ?? User::create([
    'full_name' => 'Test Branch Manager',
    'email' => 'regmgr_test@sweetbakes.com',
    'phone' => '+251922222222',
    'password' => bcrypt('Password123!'),
    'role' => 'Branch Manager',
    'branch_id' => $regBranch->branch_id,
    'status' => 'Active',
]);

$product = Product::first() ?? Product::create([
    'product_name' => 'Signature Red Velvet Cake',
    'sku' => 'CAKE-RV-001',
    'base_price' => 1200.00,
    'status' => 'Active',
]);

// Ensure Main Branch has plenty of stock for dispatch
$mainInv = BranchInventory::firstOrCreate(
    ['branch_id' => $mainBranch->branch_id, 'product_id' => $product->product_id],
    ['quantity_on_hand' => 100, 'reserved_quantity' => 0, 'reorder_level' => 10]
);
$mainInv->quantity_on_hand = 150;
$mainInv->save();

$dispatchCtrl = app(StockDispatchController::class);
$inventoryCtrl = app(InventoryController::class);

echo "--- 1. Testing Receive Stock from Main Branch (Confirm Receiving) ---\n";

// 1.1 Main Branch Manager creates a dispatch manifest
$createReq = Request::create('/api/admin/stock-dispatches', 'POST', [
    'to_branch_id' => $regBranch->branch_id,
    'notes' => 'Fresh morning delivery',
    'items' => [
        ['product_id' => $product->product_id, 'quantity_dispatched' => 25],
    ],
]);
$createReq->setUserResolver(fn() => $mainManager);
$res = $dispatchCtrl->store($createReq);
assertCondition($res->getStatusCode() === 201, "Main Branch created dispatch manifest (201 Created)");
$dispatchData = json_decode($res->getContent(), true);
$dispatchId = $dispatchData['dispatch']['dispatch_id'];

// 1.2 Main Branch Manager sends the shipment
$sendReq = Request::create("/api/admin/stock-dispatches/{$dispatchId}/send", 'POST');
$sendReq->setUserResolver(fn() => $mainManager);
$res = $dispatchCtrl->send($sendReq, $dispatchId);
assertCondition($res->getStatusCode() === 200, "Main Branch dispatched shipment (Status: In Transit)");

// 1.3 Record branch stock before receive
$branchStockBefore = (int) BranchInventory::where('branch_id', $regBranch->branch_id)
    ->where('product_id', $product->product_id)
    ->value('quantity_on_hand');

// 1.4 Receiving Branch Manager clicks "Confirm Receiving" (calls POST /api/admin/stock-dispatches/{id}/receive)
$recvReq = Request::create("/api/admin/stock-dispatches/{$dispatchId}/receive", 'POST', [
    'notes' => 'Shipment received in excellent condition',
]);
$recvReq->setUserResolver(fn() => $regManager);
$recvRes = $dispatchCtrl->receive($recvReq, $dispatchId);
assertCondition($recvRes->getStatusCode() === 200, "Branch Manager confirmed receiving shipment (200 OK)");

$recvData = json_decode($recvRes->getContent(), true);
assertCondition($recvData['total_units_received'] === 25, "Confirmed 25 units received on manifest");
assertCondition($recvData['status'] === 'received', "Dispatch manifest status updated to 'received'");

// 1.5 Verify branch inventory automatically updated
$branchStockAfter = (int) BranchInventory::where('branch_id', $regBranch->branch_id)
    ->where('product_id', $product->product_id)
    ->value('quantity_on_hand');
assertCondition($branchStockAfter === ($branchStockBefore + 25), "Branch product stock automatically increased by +25 units");

// 1.6 Verify ReceivedStock record exists
$receivedRecord = ReceivedStock::where('dispatch_id', $dispatchId)->first();
assertCondition($receivedRecord && $receivedRecord->quantity_received === 25, "ReceivedStock ledger record created with quantity 25");

// 1.7 Verify immutable InventoryTransaction ledger recorded
$tx = InventoryTransaction::where('reference_type', 'StockDispatch')
    ->where('reference_id', $dispatchId)
    ->where('branch_id', $regBranch->branch_id)
    ->first();
assertCondition($tx && $tx->transaction_type === 'transfer_receive' && (int)$tx->quantity === 25, "Immutable InventoryTransaction recorded (type: transfer_receive, qty: +25)");

echo "\n--- 2. Testing Manual Stock Adjustment by Branch Manager (In-House Production & Direct Adjustment) ---\n";

// 2.1 Branch Manager produces 15 units in-house (type: production)
$stockBeforeProduction = (int) BranchInventory::where('branch_id', $regBranch->branch_id)
    ->where('product_id', $product->product_id)
    ->value('quantity_on_hand');

$prodReq = \App\Http\Requests\AdjustInventoryRequest::create('/api/admin/inventory/adjust', 'POST', [
    'branch_id' => $regBranch->branch_id,
    'product_id' => $product->product_id,
    'type' => 'production',
    'quantity' => 15,
    'reason' => 'In-house branch production - Morning freshly baked batch',
]);
$prodReq->setUserResolver(fn() => $regManager);
$prodReq->setContainer($app);
$prodReq->validateResolved();
$prodRes = $inventoryCtrl->adjust($prodReq);
assertCondition($prodRes->getStatusCode() === 200, "Branch Manager recorded in-house production (200 OK)");

$prodData = json_decode($prodRes->getContent(), true);
assertCondition($prodData['details']['new_quantity'] === ($stockBeforeProduction + 15), "Branch on-hand stock increased by +15 units from in-house production");

// 2.2 Branch Manager records manual count adjustment (+8 units)
$stockBeforeManual = (int) BranchInventory::where('branch_id', $regBranch->branch_id)
    ->where('product_id', $product->product_id)
    ->value('quantity_on_hand');

$manualReq = \App\Http\Requests\AdjustInventoryRequest::create('/api/admin/inventory/adjust', 'POST', [
    'branch_id' => $regBranch->branch_id,
    'product_id' => $product->product_id,
    'type' => 'manual_adjustment',
    'quantity' => 8,
    'reason' => 'Physical shelf count adjustment - Found surplus units',
]);
$manualReq->setUserResolver(fn() => $regManager);
$manualReq->setContainer($app);
$manualReq->validateResolved();
$manualRes = $inventoryCtrl->adjust($manualReq);
assertCondition($manualRes->getStatusCode() === 200, "Branch Manager recorded manual adjustment (200 OK)");

$stockAfterManual = (int) BranchInventory::where('branch_id', $regBranch->branch_id)
    ->where('product_id', $product->product_id)
    ->value('quantity_on_hand');
assertCondition($stockAfterManual === ($stockBeforeManual + 8), "Branch stock increased by +8 units from manual adjustment");

// 2.3 Verify AuditLog and Transaction for production
$prodTx = InventoryTransaction::where('branch_id', $regBranch->branch_id)
    ->where('product_id', $product->product_id)
    ->where('transaction_type', 'production')
    ->latest('transaction_id')
    ->first();
assertCondition($prodTx && (int)$prodTx->quantity === 15, "InventoryTransaction for production verified (+15)");

$audit = AuditLog::where('action', 'Stock adjustment')
    ->where('user_id', $regManager->user_id)
    ->latest('log_id')
    ->first();
assertCondition($audit !== null, "AuditLog successfully recorded user, old quantity, new quantity, and reason");

echo "\n=================================================================\n";
echo "Test Run Complete: {$passed} Passed, {$failed} Failed\n";
echo "=================================================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
