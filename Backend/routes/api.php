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

// Public Auth with Rate Limiting (Section 12 requirement)
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:60,1');
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
});

// Admin & Branch Scoped Endpoints - Protected with RoleAuthorizationMiddleware
Route::prefix('/admin')->middleware(['admin.auth'])->group(function () {
    // Overview (Super Admin, Main Branch Manager, Branch Admin)
    Route::get('/overview', [OverviewController::class, 'index']);

    // Users and Roles (Super Admin full, Main Branch Manager limited to Main Branch)
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users-list', [UserController::class, 'index']);
    Route::get('/auth/users', [AuthController::class, 'listUsers'])->middleware('role:Super Admin,Main Branch Manager');
    Route::post('/users', [UserController::class, 'store']);
    Route::match(['put', 'patch'], '/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->middleware('role:Super Admin');
    Route::get('/branches-quotas', [UserController::class, 'branchQuotas']);

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

    // Orders (Scoped by controller to caller's branch if Branch Admin / Staff)
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']);
    Route::patch('/orders/{id}/branch', [OrderController::class, 'reassignBranch'])->middleware('role:Super Admin');

    // Custom Cakes (Scoped by controller to caller's branch if Branch Admin)
    Route::get('/custom-cakes', [CustomCakeController::class, 'index']);
    Route::get('/custom-cakes/{id}', [CustomCakeController::class, 'show']);
    Route::patch('/custom-cakes/{id}/quote', [CustomCakeController::class, 'quote']);
    Route::patch('/custom-cakes/{id}/status', [CustomCakeController::class, 'updateStatus']);

    // Payments & Refunds
    Route::get('/payments', [PaymentController::class, 'index']);
    Route::post('/payments/refund', [PaymentController::class, 'refund'])->middleware('role:Super Admin,Main Branch Manager');

    // Reports (Scoped by controller)
    Route::get('/reports/summary', [ReportController::class, 'summary']);
    Route::get('/reports/data', [ReportController::class, 'reportData']);
    Route::post('/reports/send-daily-summary', [ReportController::class, 'sendDailySummary']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/mark-read', [NotificationController::class, 'markAllRead']);
    Route::post('/notifications/alert-rules', [NotificationController::class, 'saveAlertRules'])->middleware('role:Super Admin');

    // Settings (Super Admin Only)
    Route::get('/settings', [SettingController::class, 'index'])->middleware('role:Super Admin');
    Route::post('/settings', [SettingController::class, 'update'])->middleware('role:Super Admin');

    // Audit Log (Super Admin and Branch Managers)
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->middleware('role:Super Admin,Main Branch Manager,Branch Manager,Branch Admin');

    // Branch management (Super Admin Only)
    Route::post('/branches', [BranchController::class, 'store'])->middleware('role:Super Admin');
    Route::patch('/branches/{id}', [BranchController::class, 'update'])->middleware('role:Super Admin');
    Route::patch('/branches/{id}/shopping-access', [BranchController::class, 'toggleShoppingAccess'])->middleware('role:Super Admin');
    Route::post('/branches/{id}/delivery-zones', [BranchController::class, 'addDeliveryZone'])->middleware('role:Super Admin');
    Route::delete('/branches/{id}/delivery-zones/{zoneId}', [BranchController::class, 'deleteDeliveryZone'])->middleware('role:Super Admin');

    // Catalog & Pricing Management (Section 1: Branch Managers Only; Super Admin and Staff blocked)
    Route::post('/products', [CatalogController::class, 'store'])->middleware('role:Branch Manager,Branch Admin,Main Branch Manager');
    Route::patch('/products/{id}', [CatalogController::class, 'update'])->middleware('role:Branch Manager,Branch Admin,Main Branch Manager');
    Route::delete('/products/{id}', [CatalogController::class, 'destroy'])->middleware('role:Branch Manager,Branch Admin,Main Branch Manager');
    Route::post('/products/upload-image', [CatalogController::class, 'uploadImage'])->middleware('role:Branch Manager,Branch Admin,Main Branch Manager');
    Route::post('/categories', [CatalogController::class, 'storeCategory'])->middleware('role:Branch Manager,Branch Admin,Main Branch Manager');
    Route::delete('/categories/{id}', [CatalogController::class, 'destroyCategory'])->middleware('role:Branch Manager,Branch Admin,Main Branch Manager');
    Route::post('/products/{id}/branch-price', [CatalogController::class, 'setBranchPrice'])->middleware('role:Branch Manager,Branch Admin,Main Branch Manager');
    Route::post('/products/report-price-update', [CatalogController::class, 'reportPriceUpdate'])->middleware('role:Branch Manager,Branch Admin,Main Branch Manager');

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
