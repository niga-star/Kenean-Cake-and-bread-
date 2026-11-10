<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Branch;
use App\Models\BranchProductPrice;
use App\Models\BranchInventory;
use App\Models\AuditLog;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\OrderItem;

class CatalogController extends Controller
{
    public function categories()
    {
        return response()->json(Category::withCount('products')->get());
    }

    public function products(Request $request)
    {
        $branchId = $request->query('branch_id');
        $categoryId = $request->query('category_id');
        $search = $request->query('search');

        $query = Product::with(['category', 'images', 'branchPrices', 'reviews'])
            ->where('status', 'Active');

        if ($categoryId && $categoryId !== 'all') {
            $query->where('category_id', $categoryId);
        }

        if ($request->has('is_fasting') && $request->query('is_fasting') !== '' && $request->query('is_fasting') !== 'all') {
            $isFasting = filter_var($request->query('is_fasting'), FILTER_VALIDATE_BOOLEAN);
            $query->where('is_fasting', $isFasting);
        }

        if ($search) {
            $query->where('product_name', 'like', "%{$search}%");
        }

        $activePromos = \App\Models\Promotion::where('status', 'Active')
            ->where('start_date', '<=', \Carbon\Carbon::today())
            ->where('end_date', '>=', \Carbon\Carbon::today())
            ->with('products')
            ->get();

        $discountedMap = [];
        foreach ($activePromos as $pr) {
            foreach ($pr->products as $pProd) {
                $discountedMap[$pProd->product_id] = [
                    'type' => $pr->discount_type,
                    'value' => (float) $pr->discount_value,
                ];
            }
        }

        $todaySoldMap = [];
        $weekSoldMap = [];
        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();

        $itemsQuery = OrderItem::whereHas('order', function ($oq) use ($branchId, $today) {
            $oq->where('order_status', '!=', 'Cancelled');
            if ($branchId && $branchId !== 'all') {
                $oq->where('branch_id', $branchId);
            }
            $oq->whereDate('placed_at', $today);
        })->groupBy('product_id')->selectRaw('product_id, SUM(quantity) as qty')->pluck('qty', 'product_id');
        $todaySoldMap = $itemsQuery->toArray();

        $weekQuery = OrderItem::whereHas('order', function ($oq) use ($branchId, $weekStart) {
            $oq->where('order_status', '!=', 'Cancelled');
            if ($branchId && $branchId !== 'all') {
                $oq->where('branch_id', $branchId);
            }
            $oq->whereDate('placed_at', '>=', $weekStart);
        })->groupBy('product_id')->selectRaw('product_id, SUM(quantity) as qty')->pluck('qty', 'product_id');
        $weekSoldMap = $weekQuery->toArray();

        $products = $query->get()->map(function ($product) use ($branchId, $discountedMap, $todaySoldMap, $weekSoldMap) {
            $resolvedPrice = (float) $product->base_price;
            $hasOverride = false;

            if ($branchId && $branchId !== 'all') {
                $override = $product->branchPrices
                    ->where('branch_id', $branchId)
                    ->where('active', true)
                    ->first();
                if ($override) {
                    $resolvedPrice = (float) $override->price;
                    $hasOverride = true;
                }
            }

            $dailySold = (int) ($todaySoldMap[$product->product_id] ?? 0);
            $weeklySold = (int) ($weekSoldMap[$product->product_id] ?? 0);
            $dailyRevenue = (float) ($dailySold * $resolvedPrice);
            $weeklyRevenue = (float) ($weeklySold * $resolvedPrice);

            // Get branch stock if branch specified
            $onHand = 20;
            $reserved = 0;
            $available = 20;
            $reorderLevel = 10;
            $stockStatus = 'Healthy';

            if ($branchId && $branchId !== 'all') {
                $inventory = BranchInventory::where('branch_id', $branchId)
                    ->where('product_id', $product->product_id)
                    ->first();

                if ($inventory) {
                    $onHand = (int) $inventory->quantity_on_hand;
                    $reserved = (int) $inventory->reserved_quantity;
                    $reorderLevel = (int) $inventory->reorder_level;
                    $available = max(0, $onHand - $reserved);
                    $stockStatus = $inventory->status;
                } else {
                    // Check if inventory exists in any branch or initialize
                    $anyInv = BranchInventory::where('product_id', $product->product_id)->first();
                    if ($anyInv) {
                        $onHand = (int) $anyInv->quantity_on_hand;
                        $reserved = (int) $anyInv->reserved_quantity;
                        $reorderLevel = (int) $anyInv->reorder_level;
                        $available = max(0, $onHand - $reserved);
                        $stockStatus = $anyInv->status;
                    }
                }
            }

            // Real dynamic reviews & ratings from MySQL
            $revCount = $product->reviews->count();
            $avgRating = $revCount > 0 ? round((float) $product->reviews->avg('rating'), 1) : null;
            $displayReviewCount = $revCount;

            // Discount computation from MySQL promotions
            $discountBadge = null;
            $originalPrice = null;
            if (isset($discountedMap[$product->product_id])) {
                $disc = $discountedMap[$product->product_id];
                $discountBadge = "-{$disc['value']}%";
                $originalPrice = round($resolvedPrice / (1 - ($disc['value'] / 100)));
            }

            return [
                'product_id' => $product->product_id,
                'category_id' => $product->category_id,
                'category_name' => $product->category?->category_name,
                'product_name' => $product->product_name,
                'description' => $product->description,
                'base_price' => (float) $product->base_price,
                'price' => $resolvedPrice,
                'resolved_price' => $resolvedPrice,
                'has_branch_override' => $hasOverride,
                'shelf_life_hours' => $product->shelf_life_hours,
                'is_fasting' => (bool) $product->is_fasting,
                'status' => $product->status,
                'image_url' => $product->images->first()?->image_url ?? '/mockup/shop_chocolate_cake.jpg',
                'images' => $product->images,
                'on_hand' => $onHand,
                'reserved' => $reserved,
                'available' => $available,
                'reorder_level' => $reorderLevel,
                'stock_status' => $stockStatus,
                'rating' => (string) $avgRating,
                'reviews_count' => $displayReviewCount,
                'discount_badge' => $discountBadge,
                'original_price' => $originalPrice,
                'daily_sold' => $dailySold,
                'weekly_sold' => $weeklySold,
                'daily_revenue' => $dailyRevenue,
                'weekly_revenue' => $weeklyRevenue,
            ];
        });

        return response()->json($products);
    }

    /**
     * Branch sales performance metrics for Branch Managers:
     * Daily product sales, weekly product sales, daily revenue, weekly revenue
     */
    public function branchSalesMetrics(Request $request)
    {
        $user = $request->user();
        $branchId = $request->query('branch_id');
        if (!$branchId || $branchId === 'all') {
            $branchId = $user?->branch_id ?: 1;
        }

        // Branch Manager privacy check
        if ($user && in_array($user->role, ['Branch Manager', 'Branch Admin']) && (int) $branchId !== (int) $user->branch_id) {
            $branchId = $user->branch_id;
        }

        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();

        $todayOrders = \App\Models\Order::where('branch_id', $branchId)
            ->where('order_status', '!=', 'Cancelled')
            ->whereDate('placed_at', $today);

        $weekOrders = \App\Models\Order::where('branch_id', $branchId)
            ->where('order_status', '!=', 'Cancelled')
            ->whereDate('placed_at', '>=', $weekStart);

        $todayUnits = (int) OrderItem::whereHas('order', function ($q) use ($branchId, $today) {
            $q->where('branch_id', $branchId)->where('order_status', '!=', 'Cancelled')->whereDate('placed_at', $today);
        })->sum('quantity');

        $weekUnits = (int) OrderItem::whereHas('order', function ($q) use ($branchId, $weekStart) {
            $q->where('branch_id', $branchId)->where('order_status', '!=', 'Cancelled')->whereDate('placed_at', '>=', $weekStart);
        })->sum('quantity');

        $todayRev = (float) $todayOrders->sum('total_amount');
        $weekRev = (float) $weekOrders->sum('total_amount');

        $branch = Branch::find($branchId);

        return response()->json([
            'branch_id' => (int) $branchId,
            'branch_name' => $branch?->branch_name ?? 'My Branch',
            'today_units_sold' => $todayUnits,
            'week_units_sold' => $weekUnits,
            'today_revenue' => $todayRev,
            'week_revenue' => $weekRev,
        ]);
    }

    public function show(Request $request, $id)
    {
        $branchId = $request->query('branch_id');
        $product = Product::with(['category', 'images', 'branchPrices', 'reviews'])->findOrFail($id);

        $resolvedPrice = (float) $product->base_price;
        $hasOverride = false;

        if ($branchId && $branchId !== 'all') {
            $override = $product->branchPrices
                ->where('branch_id', $branchId)
                ->where('active', true)
                ->first();
            if ($override) {
                $resolvedPrice = (float) $override->price;
                $hasOverride = true;
            }
        }

        $onHand = 0;
        $reserved = 0;
        $available = 0;
        $reorderLevel = 10;
        $stockStatus = 'Healthy';

        if ($branchId && $branchId !== 'all') {
            $inventory = BranchInventory::where('branch_id', $branchId)
                ->where('product_id', $product->product_id)
                ->first();

            if ($inventory) {
                $onHand = (int) $inventory->quantity_on_hand;
                $reserved = (int) $inventory->reserved_quantity;
                $reorderLevel = (int) $inventory->reorder_level;
                $available = max(0, $onHand - $reserved);
                $stockStatus = $inventory->status;
            } else {
                $stockStatus = 'Sold out';
            }
        }

        $revCount = $product->reviews->count();
        $avgRating = $revCount > 0 ? round((float) $product->reviews->avg('rating'), 1) : null;
        $displayReviewCount = $revCount;

        $activePromo = \App\Models\Promotion::where('status', 'Active')
            ->where('start_date', '<=', \Carbon\Carbon::today())
            ->where('end_date', '>=', \Carbon\Carbon::today())
            ->whereHas('products', function ($q) use ($product) {
                $q->where('promotion_products.product_id', $product->product_id);
            })
            ->first();

        $discountBadge = null;
        $originalPrice = null;
        if ($activePromo) {
            $discountBadge = "-{$activePromo->discount_value}%";
            $originalPrice = round($resolvedPrice / (1 - ((float)$activePromo->discount_value / 100)));
        }

        return response()->json([
            'product_id' => $product->product_id,
            'category_id' => $product->category_id,
            'category' => $product->category,
            'category_name' => $product->category?->category_name,
            'product_name' => $product->product_name,
            'description' => $product->description,
            'base_price' => (float) $product->base_price,
            'resolved_price' => $resolvedPrice,
            'has_branch_override' => $hasOverride,
            'shelf_life_hours' => $product->shelf_life_hours,
            'is_fasting' => (bool) $product->is_fasting,
            'status' => $product->status,
            'image_url' => $product->images->first()?->image_url ?? '/mockup/shop_chocolate_cake.jpg',
            'images' => $product->images,
            'on_hand' => $onHand,
            'reserved' => $reserved,
            'available' => $available,
            'reorder_level' => $reorderLevel,
            'stock_status' => $stockStatus,
            'rating' => (string) $avgRating,
            'reviews_count' => $displayReviewCount,
            'discount_badge' => $discountBadge,
            'original_price' => $originalPrice,
        ]);
    }

    public function branchAvailability($id)
    {
        $product = Product::with(['branchPrices'])->findOrFail($id);
        $branches = Branch::where('status', 'Active')->get();

        $availability = $branches->map(function ($branch) use ($product) {
            $inventory = BranchInventory::where('branch_id', $branch->branch_id)
                ->where('product_id', $product->product_id)
                ->first();

            $onHand = $inventory ? (int) $inventory->quantity_on_hand : 0;
            $reserved = $inventory ? (int) $inventory->reserved_quantity : 0;
            $available = max(0, $onHand - $reserved);

            $override = $product->branchPrices
                ->where('branch_id', $branch->branch_id)
                ->where('active', true)
                ->first();
            $resolvedPrice = $override ? (float) $override->price : (float) $product->base_price;

            return [
                'branch_id' => $branch->branch_id,
                'branch_name' => $branch->branch_name,
                'branch_code' => $branch->branch_code,
                'city' => $branch->city,
                'address' => $branch->address,
                'phone' => $branch->phone,
                'opening_hours' => $branch->opening_hours,
                'available' => $available,
                'price' => $resolvedPrice,
                'has_override' => (bool) $override,
                'in_stock' => $available > 0,
            ];
        });

        return response()->json([
            'product_id' => $product->product_id,
            'product_name' => $product->product_name,
            'branches' => $availability,
        ]);
    }

    /**
     * Verify caller is an authorized bakery staff member.
     */
    protected function checkBranchManagerPermission(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if ($user->role === 'Customer') {
            return response()->json([
                'message' => 'Access denied: Customers are not permitted to modify bakery products or prices.',
            ], 403);
        }

        return null;
    }

    public function store(Request $request)
    {
        $authError = $this->checkBranchManagerPermission($request);
        if ($authError) return $authError;

        $user = $request->user();

        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,category_id',
            'base_price' => 'required|numeric|min:0',
            'shelf_life_hours' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'image_url' => 'nullable|string',
        ]);

        $product = Product::create([
            'product_name' => $validated['product_name'],
            'category_id' => $validated['category_id'],
            'base_price' => $validated['base_price'],
            'shelf_life_hours' => $validated['shelf_life_hours'] ?? 48,
            'description' => $validated['description'] ?? '',
            'status' => 'Active',
        ]);

        if (!empty($validated['image_url'])) {
            ProductImage::create([
                'product_id' => $product->product_id,
                'image_url' => $validated['image_url'],
                'sort_order' => 1,
            ]);
        }

        // Initialize branch inventory for all existing branches
        $branches = Branch::all();
        foreach ($branches as $branch) {
            BranchInventory::firstOrCreate(
                ['branch_id' => $branch->branch_id, 'product_id' => $product->product_id],
                [
                    'quantity_on_hand' => 20,
                    'reserved_quantity' => 0,
                    'reorder_level' => 10,
                ]
            );
        }

        // Set branch-specific price for the creating manager's branch
        if ($user->branch_id) {
            BranchProductPrice::updateOrCreate(
                ['branch_id' => $user->branch_id, 'product_id' => $product->product_id],
                ['price' => $validated['base_price'], 'active' => true]
            );
        }

        AuditLog::create([
            'user_id' => $user->user_id,
            'user_name' => $user->full_name . " ({$user->role})",
            'action' => 'Product created',
            'record' => ($user->branch ? $user->branch->branch_name . ' · ' : '') . $product->product_name,
            'entity_type' => 'Product',
            'entity_id' => (string) $product->product_id,
            'old_value' => '—',
            'new_value' => 'ETB ' . number_format($product->base_price, 0),
            'created_at' => now(),
        ]);

        return response()->json($product->load(['category', 'images']), 201);
    }

    public function update(Request $request, $id)
    {
        $authError = $this->checkBranchManagerPermission($request);
        if ($authError) return $authError;

        $user = $request->user();
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'product_name' => 'sometimes|string|max:255',
            'category_id' => 'sometimes|exists:categories,category_id',
            'base_price' => 'sometimes|numeric|min:0',
            'shelf_life_hours' => 'sometimes|integer|min:1',
            'description' => 'sometimes|string',
            'status' => 'sometimes|in:Active,Inactive',
            'image_url' => 'nullable|string',
        ]);

        $oldPrice = $user->branch_id ? $product->getPriceForBranch($user->branch_id) : $product->base_price;

        // When a Branch Manager updates price, update THEIR branch's price override independently!
        if (isset($validated['base_price']) && $user->branch_id) {
            $newPrice = (float) $validated['base_price'];
            BranchProductPrice::updateOrCreate(
                ['branch_id' => $user->branch_id, 'product_id' => $product->product_id],
                ['price' => $newPrice, 'active' => true]
            );

            // Do not alter global base_price to preserve independent pricing for other branches
            unset($validated['base_price']);

            if ((float) $newPrice !== (float) $oldPrice) {
                AuditLog::create([
                    'user_id' => $user->user_id,
                    'user_name' => $user->full_name . " ({$user->role})",
                    'action' => 'Price change',
                    'record' => ($user->branch ? $user->branch->branch_name : 'Branch') . ' · ' . $product->product_name,
                    'entity_type' => 'BranchProductPrice',
                    'entity_id' => (string) $product->product_id,
                    'old_value' => 'ETB ' . number_format($oldPrice, 0),
                    'new_value' => 'ETB ' . number_format($newPrice, 0),
                    'created_at' => now(),
                ]);
            }
        }

        $product->update($validated);

        if (!empty($validated['image_url'])) {
            $primaryImg = $product->images()->orderBy('sort_order')->first();
            if ($primaryImg) {
                $primaryImg->update(['image_url' => $validated['image_url']]);
            } else {
                ProductImage::create([
                    'product_id' => $product->product_id,
                    'image_url' => $validated['image_url'],
                    'sort_order' => 0,
                ]);
            }
        }

        AuditLog::create([
            'user_id' => $user->user_id,
            'user_name' => $user->full_name . " ({$user->role})",
            'action' => 'Product updated',
            'record' => ($user->branch ? $user->branch->branch_name . ' · ' : '') . $product->product_name,
            'entity_type' => 'Product',
            'entity_id' => (string) $product->product_id,
            'old_value' => 'Updated',
            'new_value' => $product->product_name,
            'created_at' => now(),
        ]);

        return response()->json($product->load(['category', 'images', 'branchPrices']));
    }

    public function destroy(Request $request, $id)
    {
        $authError = $this->checkBranchManagerPermission($request);
        if ($authError) return $authError;

        $user = $request->user();
        $product = Product::findOrFail($id);
        $name = $product->product_name;

        // Check if referenced in historical orders
        $hasOrders = \App\Models\OrderItem::where('product_id', $id)->exists();

        if ($hasOrders) {
            $product->update(['status' => 'Inactive']);
            $msg = 'Product is referenced in existing orders; marked as Inactive instead of deleting.';
        } else {
            \App\Models\BranchInventory::where('product_id', $id)->delete();
            \App\Models\BranchProductPrice::where('product_id', $id)->delete();
            \App\Models\ProductImage::where('product_id', $id)->delete();
            \App\Models\Review::where('product_id', $id)->delete();
            $product->delete();
            $msg = 'Product permanently deleted.';
        }

        AuditLog::create([
            'user_id' => $user->user_id,
            'user_name' => $user->full_name . " ({$user->role})",
            'action' => 'Product deleted',
            'record' => ($user->branch ? $user->branch->branch_name . ' · ' : '') . $name,
            'entity_type' => 'Product',
            'entity_id' => (string) $id,
            'old_value' => $name,
            'new_value' => 'Deleted',
            'created_at' => now(),
        ]);

        return response()->json(['message' => $msg, 'product_id' => (int) $id]);
    }

    public function uploadImage(Request $request)
    {
        $authError = $this->checkBranchManagerPermission($request);
        if ($authError) return $authError;

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $file = $request->file('image');
        $safeExtension = in_array(strtolower($file->extension()), ['jpeg', 'png', 'jpg', 'webp']) ? $file->extension() : 'jpg';
        $filename = 'product_' . time() . '_' . uniqid() . '.' . $safeExtension;
        $targetDir = public_path('uploads/products');

        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $file->move($targetDir, $filename);
        $imageUrl = '/uploads/products/' . $filename;

        return response()->json([
            'message' => 'Image uploaded successfully',
            'image_url' => $imageUrl,
        ]);
    }

    public function destroyCategory(Request $request, $id)
    {
        $authError = $this->checkBranchManagerPermission($request);
        if ($authError) return $authError;

        $user = $request->user();
        $category = Category::findOrFail($id);
        $name = $category->category_name;

        $productCount = Product::where('category_id', $id)->count();
        if ($productCount > 0) {
            return response()->json([
                'message' => "Cannot delete category '{$name}' because {$productCount} product(s) are assigned to it.",
            ], 422);
        }

        $category->delete();

        AuditLog::create([
            'user_id' => $user->user_id,
            'user_name' => $user->full_name . " ({$user->role})",
            'action' => 'Category deleted',
            'record' => $name,
            'entity_type' => 'Category',
            'entity_id' => (string) $id,
            'old_value' => $name,
            'new_value' => 'Deleted',
            'created_at' => now(),
        ]);

        return response()->json(['message' => "Category '{$name}' deleted successfully"]);
    }

    public function storeCategory(Request $request)
    {
        $authError = $this->checkBranchManagerPermission($request);
        if ($authError) return $authError;

        $user = $request->user();

        $validated = $request->validate([
            'category_name' => 'required|string|max:255|unique:categories,category_name',
            'description' => 'nullable|string',
        ]);

        $category = Category::create([
            'category_name' => $validated['category_name'],
            'slug' => Str::slug($validated['category_name']),
            'description' => $validated['description'] ?? '',
        ]);

        AuditLog::create([
            'user_id' => $user->user_id,
            'user_name' => $user->full_name . " ({$user->role})",
            'action' => 'Category created',
            'record' => $category->category_name,
            'entity_type' => 'Category',
            'entity_id' => (string) $category->category_id,
            'old_value' => '—',
            'new_value' => $category->category_name,
            'created_at' => now(),
        ]);

        return response()->json($category, 201);
    }

    public function setBranchPrice(Request $request, $productId)
    {
        $authError = $this->checkBranchManagerPermission($request);
        if ($authError) return $authError;

        $user = $request->user();

        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,branch_id',
            'price' => 'required|numeric|min:0',
            'active' => 'sometimes|boolean',
        ]);

        // Branch-tenancy: Branch Manager can ONLY update prices for their own branch!
        if ($user->branch_id && (int) $validated['branch_id'] !== (int) $user->branch_id) {
            return response()->json([
                'message' => 'Access denied: You can only set and update product prices for your own assigned branch.',
            ], 403);
        }

        $product = Product::findOrFail($productId);
        $oldPrice = $product->getPriceForBranch($validated['branch_id']);

        $priceRecord = BranchProductPrice::updateOrCreate(
            ['branch_id' => $validated['branch_id'], 'product_id' => $productId],
            ['price' => $validated['price'], 'active' => $validated['active'] ?? true]
        );

        $priceRecord->load('branch');

        AuditLog::create([
            'user_id' => $user->user_id,
            'user_name' => $user->full_name . " ({$user->role})",
            'action' => 'Price change',
            'record' => ($priceRecord->branch ? $priceRecord->branch->branch_name : "Branch #{$validated['branch_id']}") . ' · ' . $product->product_name,
            'entity_type' => 'BranchProductPrice',
            'entity_id' => (string) $priceRecord->id,
            'old_value' => 'ETB ' . number_format($oldPrice, 0),
            'new_value' => 'ETB ' . number_format($validated['price'], 0),
            'created_at' => now(),
        ]);

        // Section 6: Every price change is logged and a notification is sent to the Super Admin automatically
        $superAdmins = \App\Models\User::where('role', 'Super Admin')->get();
        $branchLabel = $priceRecord->branch ? $priceRecord->branch->branch_name : "Branch #{$validated['branch_id']}";
        foreach ($superAdmins as $admin) {
            \App\Models\Notification::create([
                'user_id' => $admin->user_id,
                'category' => 'Price Update',
                'title' => "Price Override: {$product->product_name}",
                'message' => "{$user->full_name} updated price of {$product->product_name} at {$branchLabel} from ETB " . number_format($oldPrice, 0) . " to ETB " . number_format($validated['price'], 0),
                'link' => '/admin/products',
                'is_read' => false,
            ]);
        }

        return response()->json($priceRecord);
    }

    /**
     * Get branch prices for a branch (GET /api/admin/branch-prices)
     */
    public function getBranchPrices(Request $request)
    {
        $user = $request->user();
        $branchId = ($user->role === 'Super Admin')
            ? ($request->query('branch_id') ?: $user->branch_id)
            : ($user->branch_id ?? \App\Models\Branch::where('manager_id', $user->user_id)->value('branch_id'));

        if (!$branchId) {
            return response()->json(['message' => 'Branch ID required.'], 422);
        }

        $prices = BranchProductPrice::with('product')
            ->where('branch_id', $branchId)
            ->get();

        return response()->json(['success' => true, 'branch_id' => (int) $branchId, 'prices' => $prices]);
    }

    /**
     * Set/update branch price (PUT /api/admin/branch-prices)
     */
    public function updateBranchPrices(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,product_id',
            'branch_id' => 'nullable|exists:branches,branch_id',
            'price' => 'required|numeric|min:0',
            'active' => 'sometimes|boolean',
        ]);

        $user = $request->user();
        $targetBranchId = ($user->role === 'Super Admin')
            ? ($validated['branch_id'] ?? $user->branch_id)
            : ($user->branch_id ?? \App\Models\Branch::where('manager_id', $user->user_id)->value('branch_id'));

        if (!$targetBranchId) {
            return response()->json(['message' => 'Branch ID required.'], 422);
        }

        $request->merge(['branch_id' => $targetBranchId]);
        return $this->setBranchPrice($request, $validated['product_id']);
    }

    /**
     * Submit a Product & Price Update Report to the Super Admin.
     * Section 1 requirement: Branch Managers must submit product and price update reports to the Super Admin.
     */
    public function reportPriceUpdate(Request $request)
    {
        $authError = $this->checkBranchManagerPermission($request);
        if ($authError) return $authError;

        $user = $request->user();

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'required|string|min:5|max:2000',
        ]);

        $superAdmin = \App\Models\User::where('role', 'Super Admin')->first();
        $branchName = $user->branch ? $user->branch->branch_name : 'Branch';

        $reportTitle = $validated['title'] ?: "Product & Price Update Report - {$branchName}";

        $report = \App\Models\StaffReport::create([
            'user_id' => $user->user_id,
            'branch_id' => $user->branch_id ?: 1,
            'report_type' => 'Price Update',
            'title' => $reportTitle,
            'content' => $validated['content'],
            'submitted_to_id' => $superAdmin?->user_id,
            'status' => 'Submitted',
        ]);

        AuditLog::create([
            'user_id' => $user->user_id,
            'user_name' => $user->full_name . " ({$user->role})",
            'action' => 'Price update report submitted',
            'record' => $report->title,
            'entity_type' => 'StaffReport',
            'entity_id' => (string) $report->report_id,
            'old_value' => '—',
            'new_value' => "Price update report forwarded to Super Admin ({$branchName})",
            'created_at' => now(),
        ]);

        return response()->json([
            'message' => 'Product and price update report successfully submitted to the Super Admin.',
            'report' => $report->load(['user', 'branch']),
        ], 201);
    }
}
