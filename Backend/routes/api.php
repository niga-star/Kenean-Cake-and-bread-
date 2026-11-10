<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CustomCakeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\OverviewController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\WebsiteSettingController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\StaffReportController;
use App\Http\Controllers\OrderReportController;
use App\Http\Controllers\ReceivedStockController;
use App\Http\Controllers\StockDispatchController;

// Public Auth with Rate Limiting (v4.1 Section 10 & 11: 5 attempts/min)
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:60,1');
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:30,1');
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:30,1');
Route::post('/auth/switch-role', [AuthController::class, 'switchRole'])->middleware('throttle:30,1');

// Public Branches & Catalog
Route::get('/branches', [BranchController::class, 'index']);
Route::get('/branches/{id}', [BranchController::class, 'show']);
Route::get('/branches/{id}/delivery-zones', [BranchController::class, 'deliveryZones']);
Route::get('/categories', [CatalogController::class, 'categories']);
Route::get('/products', [CatalogController::class, 'products']);
Route::get('/products/{id}', [CatalogController::class, 'show']);
Route::get('/catalog/products', [CatalogController::class, 'products']);
Route::get('/catalog/products/{id}', [CatalogController::class, 'show']);
Route::get('/catalog/products/{id}/branch-availability', [CatalogController::class, 'branchAvailability']);
Route::get('/products/{id}/branch-availability', [CatalogController::class, 'branchAvailability']);
Route::get('/branches/{id}/products', [CatalogController::class, 'products']);

// Public Reviews & Testimonials
Route::get('/reviews/featured', [ReviewController::class, 'featured']);
Route::get('/products/{id}/reviews', [ReviewController::class, 'productReviews']);
Route::post('/products/{id}/reviews', [ReviewController::class, 'store']);

// Public Dynamic Website Settings & Banners
Route::get('/business-info', [WebsiteSettingController::class, 'businessInfo']);
Route::get('/banners', [WebsiteSettingController::class, 'banners']);

// Public Wishlist (Authenticated or Local)
Route::get('/wishlist', [WishlistController::class, 'index']);
Route::post('/wishlist/toggle', [WishlistController::class, 'toggle']);
Route::post('/wishlist/sync', [WishlistController::class, 'sync']);
Route::post('/wishlist/guest-products', [WishlistController::class, 'guestProducts']);

// Public Newsletter
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->middleware('throttle:20,1');

// Shopping Cart (Database Backed)
Route::get('/cart', [CartController::class, 'getCart']);
Route::post('/cart/sync', [CartController::class, 'syncCart']);
Route::delete('/cart', [CartController::class, 'clearCart']);

// Public Contact Form
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:60,1');

// Public Promotions
Route::get('/promotions/active', [PromotionController::class, 'activePromotions']);
Route::post('/promotions/validate', [PromotionController::class, 'validatePromo']);

// Public Payment Webhook
Route::post('/payments/webhook', [PaymentController::class, 'webhook']);

// Core application endpoints
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/auth/user', [AuthController::class, 'user']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::patch('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::post('/auth/change-password', [AuthController::class, 'changePassword']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/mark-read', [NotificationController::class, 'markAllRead']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
});

// Admin & Branch Scoped Endpoints - Protected with RoleAuthorizationMiddleware
Route::prefix('/admin')->middleware(['admin.auth'])->group(function () {
    // Overview (Super Admin, Main Branch Manager, Branch Admin)
    Route::get('/overview', [OverviewController::class, 'index']);

    // Notifications (All Admin & Staff Roles)
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/mark-read', [NotificationController::class, 'markAllRead']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/alert-rules', [NotificationController::class, 'saveAlertRules']);

    // Users and Roles (Super Admin full, Main Branch Manager limited to Main Branch)
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users-list', [UserController::class, 'index']);
    Route::get('/auth/users', [AuthController::class, 'listUsers'])->middleware('role:Super Admin,Main Branch Manager');
    Route::post('/users', [UserController::class, 'store']);
    Route::patch('/users/{id}/status', [UserController::class, 'updateStatus']);
    Route::put('/users/{id}/salary', [UserController::class, 'updateSalary'])->middleware('role:Super Admin');
    Route::match(['put', 'patch'], '/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::get('/branches-quotas', [UserController::class, 'branchQuotas']);

    // System Audit Logs (Super Admin, Main Branch Manager, Branch Manager)
    Route::get('/audit-logs', [AuditLogController::class, 'index']);

    // Salary and Wage Management (Section 4)
    Route::get('/salaries', [SalaryController::class, 'index'])->middleware('role:Super Admin');
    Route::get('/salaries/my-account', [SalaryController::class, 'myAccount']);
    Route::match(['put', 'patch'], '/salaries/{userId}', [SalaryController::class, 'updateSalary'])->middleware('role:Super Admin');
    Route::post('/salary-requests', [SalaryController::class, 'submitCorrectionRequest']);
    Route::get('/salary-requests', [SalaryController::class, 'listCorrectionRequests']);
    Route::match(['post', 'put', 'patch'], '/salary-requests/{id}/review', [SalaryController::class, 'reviewCorrectionRequest'])->middleware('role:Super Admin');
    Route::match(['post', 'put', 'patch'], '/salary-requests/{id}/status', [SalaryController::class, 'reviewCorrectionRequest'])->middleware('role:Super Admin');

    // Staff Reporting Hierarchy (Section 9)
    Route::get('/staff-reports', [StaffReportController::class, 'index']);
    Route::post('/staff-reports', [StaffReportController::class, 'store']);
    Route::match(['post', 'put', 'patch'], '/staff-reports/{id}/review', [StaffReportController::class, 'review']);

    // Inventory (Scoped by controller to caller's branch if Branch Admin / Staff)
    Route::get('/inventory', [InventoryController::class, 'index']);
    Route::post('/inventory/adjust', [InventoryController::class, 'adjust']);
    Route::get('/inventory/transactions', [InventoryController::class, 'transactions']);
    Route::get('/inventory/reconcile', [InventoryController::class, 'reconcile']);
    Route::get('/received-stocks', [ReceivedStockController::class, 'index']);
    Route::post('/received-stocks', [ReceivedStockController::class, 'store']);

    // Stock Dispatches (v4.1 Section 5 & 11)
    Route::get('/dispatches', [StockDispatchController::class, 'index']);
    Route::post('/dispatches', [StockDispatchController::class, 'store']);
    Route::get('/dispatches/{id}', [StockDispatchController::class, 'show']);
    Route::post('/dispatches/{id}/send', [StockDispatchController::class, 'send']);
    Route::post('/dispatches/{id}/cancel', [StockDispatchController::class, 'cancel']);
    Route::get('/stock-dispatches', [StockDispatchController::class, 'index']);
    Route::post('/stock-dispatches', [StockDispatchController::class, 'store']);
    Route::get('/stock-dispatches/{id}', [StockDispatchController::class, 'show']);
    Route::post('/stock-dispatches/{id}/send', [StockDispatchController::class, 'send']);
    Route::post('/stock-dispatches/{id}/cancel', [StockDispatchController::class, 'cancel']);

    // Transfers & Distribution
    Route::get('/transfers/dashboard', [TransferController::class, 'dashboard']);
    Route::get('/transfers', [TransferController::class, 'index']);
    Route::get('/transfers/{id}', [TransferController::class, 'show']);
    Route::post('/transfers', [TransferController::class, 'store'])->middleware('role:Super Admin,Main Branch Manager');
    Route::post('/transfers/{id}/approve', [TransferController::class, 'approve'])->middleware('role:Super Admin');
    Route::post('/transfers/{id}/dispatch', [TransferController::class, 'dispatch'])->middleware('role:Super Admin,Main Branch Manager');
    Route::post('/transfers/{id}/receive', [TransferController::class, 'receive']);
    Route::post('/transfers/{id}/resolve', [TransferController::class, 'resolve'])->middleware('role:Super Admin,Main Branch Manager');
    Route::post('/transfers/{id}/cancel', [TransferController::class, 'cancel']);
    Route::post('/transfers/{id}/remind', [TransferController::class, 'remind'])->middleware('role:Super Admin,Main Branch Manager');
    Route::post('/stock-requests', [TransferController::class, 'storeRequest']);
    Route::post('/stock-requests/{id}/convert', [TransferController::class, 'convertRequest'])->middleware('role:Super Admin,Main Branch Manager');

    // Orders (Section 2: Branch Managers & Staff manage; Super Admin blocked from direct order processing)
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::match(['patch', 'put'], '/orders/{id}/status', [OrderController::class, 'updateStatus'])
        ->middleware('role:Branch Manager,Branch Admin,Main Branch Manager,Branch Staff,Branch Seller,Branch Seller / Cashier,Cashier,Servant,Delivery Staff');
    Route::patch('/orders/{id}/branch', [OrderController::class, 'reassignBranch'])->middleware('role:Super Admin');

    // Custom Cakes (Section 2: Branch Managers & Staff manage; Super Admin blocked from direct management)
    Route::get('/custom-cakes', [CustomCakeController::class, 'index']);
    Route::get('/custom-cakes/{id}', [CustomCakeController::class, 'show']);
    Route::match(['patch', 'put'], '/custom-cakes/{id}/quote', [CustomCakeController::class, 'quote'])
        ->middleware('role:Super Admin,Main Branch Manager,Branch Manager,Branch Admin,Branch Seller,Branch Seller / Cashier,Cashier,Branch Staff');
    Route::match(['patch', 'put'], '/custom-cakes/{id}/status', [CustomCakeController::class, 'updateStatus'])
        ->middleware('role:Branch Manager,Branch Admin,Main Branch Manager,Branch Staff,Branch Seller,Branch Seller / Cashier,Cashier,Servant,Delivery Staff');

    // Payments & Refunds
    Route::get('/payments', [PaymentController::class, 'index']);
    Route::post('/payments/refund', [PaymentController::class, 'refund'])->middleware('role:Super Admin,Main Branch Manager');

    // Three-Level Order & Custom Cake Reporting System
    // Level 1: Branch Seller
    Route::get('/reports/seller/today-orders', [OrderReportController::class, 'sellerTodayOrders']);
    Route::get('/reports/seller/my-reports', [OrderReportController::class, 'sellerReportsIndex']);
    Route::get('/reports/seller/daily-reports', [OrderReportController::class, 'sellerReportsIndex']);
    Route::get('/reports/seller/daily-reports/{id}', [OrderReportController::class, 'sellerReportShow']);
    Route::post('/reports/seller/daily', [OrderReportController::class, 'sellerReportStore']);
    Route::post('/reports/seller/daily-reports', [OrderReportController::class, 'sellerReportStore']);
    Route::put('/reports/seller/daily-reports/{id}', [OrderReportController::class, 'sellerReportUpdate']);
    Route::delete('/reports/seller/daily-reports/{id}', [OrderReportController::class, 'sellerReportDestroy']);

    // Level 2: Branch Manager (Verification & Consolidation - Exclusively for Branch Managers)
    Route::get('/reports/manager/seller-reports', [OrderReportController::class, 'sellerReportsIndex'])
        ->middleware('role:Main Branch Manager,Branch Manager,Branch Admin');
    Route::post('/reports/manager/seller-reports/{id}/verify', [OrderReportController::class, 'managerVerifyReport'])
        ->middleware('role:Main Branch Manager,Branch Manager,Branch Admin');
    Route::post('/reports/manager/verify/{id}', [OrderReportController::class, 'managerVerifyReport'])
        ->middleware('role:Main Branch Manager,Branch Manager,Branch Admin');
    Route::get('/reports/manager/pending-consolidation', [OrderReportController::class, 'managerPendingConsolidation'])
        ->middleware('role:Main Branch Manager,Branch Manager,Branch Admin');
    Route::post('/reports/manager/consolidate-and-submit', [OrderReportController::class, 'managerConsolidateAndSubmit'])
        ->middleware('role:Main Branch Manager,Branch Manager,Branch Admin');
    Route::get('/reports/manager/consolidated-reports', [OrderReportController::class, 'managerConsolidatedReports'])
        ->middleware('role:Main Branch Manager,Branch Manager,Branch Admin');
    Route::get('/reports/manager/consolidated-reports/{id}', [OrderReportController::class, 'managerConsolidatedShow'])
        ->middleware('role:Main Branch Manager,Branch Manager,Branch Admin');
    Route::post('/reports/manager/consolidated-reports/{id}/resubmit', [OrderReportController::class, 'managerConsolidatedResubmit'])
        ->middleware('role:Main Branch Manager,Branch Manager,Branch Admin');

    // Level 3: Super Admin (Centralized Monitoring & Approval)
    Route::get('/reports/admin/branch-summary', [OrderReportController::class, 'adminBranchSummary'])
        ->middleware('role:Super Admin');
    Route::get('/reports/super-admin/overview', [OrderReportController::class, 'superAdminOverview'])
        ->middleware('role:Super Admin');
    Route::get('/reports/super-admin/consolidated/{id}', [OrderReportController::class, 'superAdminConsolidatedShow'])
        ->middleware('role:Super Admin,Main Branch Manager,Branch Manager,Branch Admin');
    Route::post('/reports/super-admin/consolidated/{id}/verify', [OrderReportController::class, 'superAdminVerify'])
        ->middleware('role:Super Admin');

    // Legacy reports endpoint for backward compatibility where needed
    Route::get('/reports/summary', [ReportController::class, 'summary']);
    Route::get('/reports/data', [ReportController::class, 'reportData']);

    // Branch management (Super Admin Only)
    Route::post('/branches', [BranchController::class, 'store'])->middleware('role:Super Admin');
    Route::patch('/branches/{id}', [BranchController::class, 'update'])->middleware('role:Super Admin');
    Route::patch('/branches/{id}/shopping-access', [BranchController::class, 'toggleShoppingAccess'])->middleware('role:Super Admin');
    Route::post('/branches/{id}/delivery-zones', [BranchController::class, 'addDeliveryZone'])->middleware('role:Super Admin');
    Route::delete('/branches/{id}/delivery-zones/{zoneId}', [BranchController::class, 'deleteDeliveryZone'])->middleware('role:Super Admin');

    // Branch Sales Performance Metrics (Branch Managers & Staff)
    Route::get('/products/branch-metrics', [CatalogController::class, 'branchSalesMetrics']);

    // Catalog & Pricing Management (Branch Managers & Branch Staff/Sellers manage their own branch)
    Route::get('/branch-prices', [CatalogController::class, 'getBranchPrices'])->middleware('role:Main Branch Manager,Branch Manager,Branch Admin,Super Admin');
    Route::put('/branch-prices', [CatalogController::class, 'updateBranchPrices'])->middleware('role:Main Branch Manager,Branch Manager,Branch Admin,Super Admin');
    Route::post('/products', [CatalogController::class, 'store'])->middleware('role:Main Branch Manager,Branch Manager,Branch Admin,Branch Seller,Branch Seller / Cashier,Cashier,Branch Staff');
    Route::patch('/products/{id}', [CatalogController::class, 'update'])->middleware('role:Main Branch Manager,Branch Manager,Branch Admin,Branch Seller,Branch Seller / Cashier,Cashier,Branch Staff');
    Route::delete('/products/{id}', [CatalogController::class, 'destroy'])->middleware('role:Main Branch Manager,Branch Manager,Branch Admin,Branch Seller,Branch Seller / Cashier,Cashier,Branch Staff');
    Route::post('/products/upload-image', [CatalogController::class, 'uploadImage'])->middleware('role:Main Branch Manager,Branch Manager,Branch Admin,Branch Seller,Branch Seller / Cashier,Cashier,Branch Staff');
    Route::post('/categories', [CatalogController::class, 'storeCategory'])->middleware('role:Main Branch Manager,Branch Manager,Branch Admin,Branch Seller,Branch Seller / Cashier,Cashier,Branch Staff');
    Route::delete('/categories/{id}', [CatalogController::class, 'destroyCategory'])->middleware('role:Main Branch Manager,Branch Manager,Branch Admin,Branch Seller,Branch Seller / Cashier,Cashier,Branch Staff');
    Route::post('/products/{id}/branch-price', [CatalogController::class, 'setBranchPrice'])->middleware('role:Main Branch Manager,Branch Manager,Branch Admin,Branch Seller,Branch Seller / Cashier,Cashier,Branch Staff');
    Route::post('/products/report-price-update', [CatalogController::class, 'reportPriceUpdate'])->middleware('role:Main Branch Manager,Branch Manager,Branch Admin,Branch Seller,Branch Seller / Cashier,Cashier,Branch Staff');

    // Banners & Website Content Management
    Route::get('/banners', [WebsiteSettingController::class, 'adminBanners']);
    Route::post('/banners', [WebsiteSettingController::class, 'storeBanner'])->middleware('role:Super Admin');
    Route::patch('/banners/{id}', [WebsiteSettingController::class, 'updateBanner'])->middleware('role:Super Admin');
    Route::delete('/banners/{id}', [WebsiteSettingController::class, 'destroyBanner'])->middleware('role:Super Admin');

    // Customer Inquiries, Reviews & Newsletter Management
    Route::get('/contact-inquiries', [ContactController::class, 'index'])->middleware('role:Super Admin,Main Branch Manager');
    Route::get('/reviews', [ReviewController::class, 'adminIndex'])->middleware('role:Super Admin,Main Branch Manager');
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy'])->middleware('role:Super Admin');
    Route::get('/newsletter-subscribers', [NewsletterController::class, 'index'])->middleware('role:Super Admin,Main Branch Manager');

    // Database Backup (Super Admin Only, Section 12)
    Route::post('/database/backup', function (\Illuminate\Http\Request $request) {
        \Illuminate\Support\Facades\Artisan::call('db:backup');
        return response()->json([
            'message' => 'Automated database backup successfully created and verified.',
            'output' => trim(\Illuminate\Support\Facades\Artisan::output()),
        ]);
    })->middleware('role:Super Admin');

    // Expired Carts & Tokens Maintenance Purge (Super Admin Only, Section 12)
    Route::post('/maintenance/purge-expired', function (\Illuminate\Http\Request $request) {
        \Illuminate\Support\Facades\Artisan::call('app:purge-expired');
        return response()->json([
            'message' => 'Maintenance purge executed successfully.',
            'output' => trim(\Illuminate\Support\Facades\Artisan::output()),
        ]);
    })->middleware('role:Super Admin');
});

// Customer Storefront Order & Cake Endpoints
Route::post('/orders/track', [OrderController::class, 'trackOrder'])->middleware('throttle:15,1');
Route::get('/orders', [OrderController::class, 'index']);
Route::get('/orders/{id}', [OrderController::class, 'show']);
Route::post('/orders', [OrderController::class, 'store']);
Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);
Route::delete('/orders/{id}', [OrderController::class, 'destroy']);
Route::post('/orders/clear-finished', [OrderController::class, 'clearFinishedHistory']);
Route::post('/orders/release-expired', [OrderController::class, 'releaseExpiredEndpoint']);
Route::get('/custom-cakes', [CustomCakeController::class, 'index']);
Route::post('/custom-cakes', [CustomCakeController::class, 'store']);
Route::post('/custom-cakes/upload-image', [CustomCakeController::class, 'uploadImage'])->middleware('throttle:60,1');
Route::get('/custom-cakes/{id}', [CustomCakeController::class, 'show']);
Route::post('/custom-cakes/{id}/accept-quote', [CustomCakeController::class, 'acceptQuote']);
Route::post('/custom-cakes/{id}/cancel', [CustomCakeController::class, 'cancel']);

// Customer Addresses
Route::get('/addresses', [\App\Http\Controllers\AddressController::class, 'index']);
Route::post('/addresses', [\App\Http\Controllers\AddressController::class, 'store']);
Route::delete('/addresses/{id}', [\App\Http\Controllers\AddressController::class, 'destroy']);
Route::post('/addresses/{id}/default', [\App\Http\Controllers\AddressController::class, 'makeDefault']);
