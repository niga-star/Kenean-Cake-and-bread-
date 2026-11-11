<template>
  <div class="space-y-6 max-w-7xl mx-auto font-poppins">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <div class="flex items-center space-x-2">
          <h2 class="dash-title text-xl sm:text-2xl">Products and Prices</h2>
          <span
            class="text-[11px] font-bold px-2.5 py-0.5 rounded-full border"
            :class="isBranchManager ? 'dash-badge-pending' : 'dash-badge-neutral'"
          >
            {{ isBranchManager ? 'Branch Manager Control' : 'Read-Only (Seller)' }}
          </span>
        </div>
        <p class="dash-subtitle mt-0.5">
          {{ isBranchManager
            ? `Set and update product prices independently for ${currentBranchName}. Changes will not affect other branches.`
            : `Viewing active catalog and counter prices for ${currentBranchName}. Only Branch Managers can update prices.` }}
        </p>
      </div>

      <div class="flex flex-wrap items-center gap-2 sm:gap-3 w-full sm:w-auto">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search products..."
          class="dash-input w-auto min-w-[150px] py-1.5 px-3"
        />

        <!-- Branch Manager Controls -->
        <template v-if="isBranchManager">
          <button
            @click="openPriceReportModal"
            class="dash-btn-secondary text-xs sm:text-sm py-1.5 px-3"
            title="Submit a product and price update report to the Super Admin"
          >
            <span>📢 Report to Super Admin</span>
          </button>
          <button
            @click="isCategoriesModalOpen = true"
            class="dash-btn-secondary text-xs sm:text-sm py-1.5 px-3"
          >
            Categories
          </button>
          <button
            @click="openAddProductModal"
            class="dash-btn-primary text-xs sm:text-sm py-1.5 px-3.5"
          >
            <span>+ Add Product</span>
          </button>
        </template>
      </div>
    </div>

    <!-- Branch Status Notice Card -->
    <div
      class="p-3.5 rounded-xl border flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-2xs"
      :class="isBranchManager ? 'bg-amber-50/60 border-amber-200' : 'bg-blue-50/60 border-blue-200'"
    >
      <div class="flex items-center space-x-3">
        <span class="text-2xl">{{ isBranchManager ? '🏪' : '🛡️' }}</span>
        <div>
          <div class="text-xs font-bold text-[#2A1D15]">
            Assigned Branch: <span class="text-[#BA5420]">{{ currentBranchName }}</span>
          </div>
          <div class="text-[11px] text-[#6A5A50] mt-0.5">
            <template v-if="isBranchManager">
              Independent pricing active: Any price change you save applies exclusively to <strong>{{ currentBranchName }}</strong>.
            </template>
            <template v-else>
              You have view-only access as a Branch Seller/Staff. To request a price or catalog change, submit a daily report to your Branch Manager.
            </template>
          </div>
        </div>
      </div>
      <div v-if="isBranchManager" class="shrink-0 text-right">
        <button
          @click="openPriceReportModal"
          class="text-xs font-bold text-[#BA5420] hover:underline cursor-pointer"
        >
          Send Price Notice →
        </button>
      </div>
    </div>

    <!-- Branch Sales Performance Cards (Branch Manager Daily & Weekly Sales Monitoring) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="dash-card p-4">
        <div class="text-[11px] font-bold uppercase tracking-wider text-[#7D6E64]">Daily Product Sales</div>
        <div class="text-2xl sm:text-3xl font-serif font-black text-[#1E7422] mt-1">
          {{ branchMetrics.today_units_sold ?? 0 }} <span class="text-xs font-sans font-medium text-[#7D6E64]">units sold</span>
        </div>
        <div class="text-xs text-[#7A6B60] mt-0.5">Today's pastry &amp; cake volume</div>
      </div>
      <div class="dash-card p-4">
        <div class="text-[11px] font-bold uppercase tracking-wider text-[#7D6E64]">Weekly Product Sales</div>
        <div class="text-2xl sm:text-3xl font-serif font-black text-[#1565C0] mt-1">
          {{ branchMetrics.week_units_sold ?? 0 }} <span class="text-xs font-sans font-medium text-[#7D6E64]">units this week</span>
        </div>
        <div class="text-xs text-[#7A6B60] mt-0.5">7-day branch sales volume</div>
      </div>
      <div class="dash-card p-4">
        <div class="text-[11px] font-bold uppercase tracking-wider text-[#7D6E64]">Daily Branch Revenue</div>
        <div class="text-2xl sm:text-3xl font-serif font-black text-[#2A1D15] mt-1">
          ETB {{ Number(branchMetrics.today_revenue ?? 0).toLocaleString() }}
        </div>
        <div class="text-xs text-[#1E7422] font-semibold mt-0.5">Today's gross counter sales</div>
      </div>
      <div class="dash-card p-4">
        <div class="text-[11px] font-bold uppercase tracking-wider text-[#7D6E64]">Weekly Branch Revenue</div>
        <div class="text-2xl sm:text-3xl font-serif font-black text-[#BA5420] mt-1">
          ETB {{ Number(branchMetrics.week_revenue ?? 0).toLocaleString() }}
        </div>
        <div class="text-xs text-[#BA5420] font-semibold mt-0.5">Cumulative week sales</div>
      </div>
    </div>

    <!-- Alert / Messages -->
    <div v-if="successMsg" class="p-3 bg-[#EBF7EB] border border-[#C5E9C6] rounded-lg text-xs text-[#1E7422] flex items-center justify-between">
      <span>{{ successMsg }}</span>
      <button @click="successMsg = ''" class="text-[#1E7422] font-bold ml-2 cursor-pointer">&times;</button>
    </div>
    <div v-if="errorMsg" class="p-3 bg-[#FDEAEA] border border-[#FFCDD2] rounded-lg text-xs text-[#C62828] flex items-center justify-between">
      <span>{{ errorMsg }}</span>
      <button @click="errorMsg = ''" class="text-[#C62828] font-bold ml-2 cursor-pointer">&times;</button>
    </div>

    <!-- Category Pills -->
    <div class="flex items-center justify-between text-xs">
      <div class="flex items-center space-x-2 overflow-x-auto pb-1">
        <button
          @click="selectedCategory = 'all'"
          class="px-3.5 py-1 rounded-full font-semibold transition shrink-0 cursor-pointer"
          :class="selectedCategory === 'all' ? 'bg-[#984518] text-white shadow-2xs' : 'bg-white border border-[#DDD2C5] text-[#55463C]'"
        >
          All
        </button>
        <button
          v-for="c in categories"
          :key="c.category_id"
          @click="selectedCategory = c.category_id"
          class="px-3.5 py-1 rounded-full font-semibold transition shrink-0 cursor-pointer"
          :class="selectedCategory === c.category_id ? 'bg-[#984518] text-white shadow-2xs' : 'bg-white border border-[#DDD2C5] text-[#55463C]'"
        >
          {{ c.category_name }}
        </button>
      </div>
      <span class="text-[#8A7B71] font-semibold shrink-0 ml-2">{{ filteredProducts.length }} products</span>
    </div>

    <!-- Products Table -->
    <div class="bg-white border border-[#E9E0D4] rounded-xl shadow-2xs overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full min-w-[620px] text-left text-sm">
          <thead class="bg-[#FAF6F0] text-[11px] font-bold text-[#827165] tracking-wider uppercase border-b border-[#EFE8DF]">
            <tr>
              <th class="py-3 px-5">Product</th>
              <th class="py-3 px-4">Category</th>
              <th class="py-3 px-4 text-center">Daily Sold</th>
              <th class="py-3 px-4 text-center">Weekly Sold</th>
              <th class="py-3 px-4 text-center">Standard Base Price</th>
              <th class="py-3 px-4 text-center">{{ currentBranchName }} Branch Price</th>
              <th class="py-3 px-4 text-center">Status</th>
              <th class="py-3 px-5 text-right">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#F3EDE4]">
            <tr v-if="!filteredProducts || filteredProducts.length === 0">
              <td colspan="8" class="py-12 text-center text-sm text-[#7A6B60]">
                No products found. Click <button @click="openAddProductModal" class="font-bold text-[#BA5420] underline cursor-pointer">+ Add Product</button> to add your first product.
              </td>
            </tr>
            <tr
              v-for="p in filteredProducts"
              :key="p.product_id"
              class="hover:bg-[#FCFAF7] transition cursor-pointer"
              :class="selectedProduct?.product_id === p.product_id ? 'bg-[#FAF3EC]' : ''"
              @click="selectProductForEdit(p)"
            >
              <td class="py-3.5 px-5 flex items-center space-x-3">
                <img :src="p.image_url" class="w-10 h-10 rounded-lg object-cover border border-[#DACDC0] shrink-0" />
                <div>
                  <div class="font-product font-bold text-xs sm:text-sm text-[#2A1D15]">{{ p.product_name }}</div>
                  <div class="text-[11px] text-[#8A7B71] truncate max-w-xs">{{ p.description }}</div>
                </div>
              </td>
              <td class="py-3.5 px-4 text-xs text-[#55463C]">{{ p.category_name }}</td>
              <td class="py-3.5 px-4 text-center font-bold text-[#1E7422] text-xs">
                {{ p.daily_sold || 0 }} units
              </td>
              <td class="py-3.5 px-4 text-center font-bold text-[#1565C0] text-xs">
                {{ p.weekly_sold || 0 }} units
              </td>
              <td class="py-3.5 px-4 text-center text-xs font-medium text-[#7D6E64]">
                ETB {{ Number(p.base_price).toLocaleString() }}
              </td>
              <td class="py-3.5 px-4 text-center">
                <div class="inline-flex items-center space-x-1.5">
                  <span class="text-xs font-bold text-[#BA5420]">
                    ETB {{ Number(getBranchResolvedPrice(p)).toLocaleString() }}
                  </span>
                  <span
                    v-if="hasBranchPriceOverride(p)"
                    class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-amber-100 text-amber-800"
                    title="Specific price override active for this branch"
                  >
                    Custom
                  </span>
                </div>
              </td>
              <td class="py-3.5 px-4 text-center">
                <span
                  class="px-2.5 py-0.5 rounded-full text-xs font-semibold border"
                  :class="p.status === 'Active' ? 'bg-[#EAF5EA] text-[#2E7D32] border-[#C8E6C9]' : 'bg-[#FDEAEA] text-[#C62828] border-[#FFCDD2]'"
                >
                  {{ p.status }}
                </span>
              </td>
              <td class="py-3.5 px-5 text-right">
                <div class="flex items-center justify-end space-x-2">
                  <button
                    v-if="isBranchManager"
                    @click.stop="selectProductForEdit(p)"
                    class="px-2.5 py-1 text-xs font-medium bg-white border border-[#D8CDC0] hover:bg-[#F4ECE3] text-[#4A3B32] rounded-md shadow-2xs transition cursor-pointer"
                  >
                    Manage Price
                  </button>
                  <button
                    v-if="isBranchManager"
                    @click.stop="confirmDeleteProduct(p)"
                    class="px-2.5 py-1 text-xs font-medium bg-red-50 border border-red-200 hover:bg-red-100 text-red-700 rounded-md shadow-2xs transition cursor-pointer"
                    title="Delete product"
                  >
                    Delete
                  </button>
                  <button
                    v-if="!isBranchManager"
                    @click.stop="selectProductForEdit(p)"
                    class="px-2.5 py-1 text-xs font-medium bg-gray-50 border border-gray-200 hover:bg-gray-100 text-gray-700 rounded-md transition cursor-pointer"
                  >
                    View Details
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Product Editor Box (Branch Manager Edit Mode OR Seller View Mode) -->
    <div v-if="selectedProduct" class="bg-white border border-[#E9E0D4] rounded-xl shadow-2xs p-6 space-y-5">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#EFE8DF] pb-3">
        <div class="flex items-center space-x-3">
          <img :src="selectedProduct.image_url || '/mockup/shop_chocolate_cake.jpg'" class="w-14 h-14 rounded-lg object-cover border border-[#DACDC0] shrink-0" />
          <div>
            <h3 class="font-serif font-bold text-base sm:text-lg text-[#2A1D15]">{{ selectedProduct.product_name }}</h3>
            <span class="text-xs text-[#8A7B71]">ID: #{{ selectedProduct.product_id }} · {{ selectedProduct.category_name }}</span>
            <div v-if="isBranchManager" class="mt-1">
              <label class="text-[11px] font-semibold text-[#BA5420] hover:underline cursor-pointer">
                📷 Upload New Photo
                <input type="file" accept="image/*" class="hidden" @change="e => uploadImage(e, selectedProduct)" />
              </label>
            </div>
          </div>
        </div>

        <div class="flex items-center space-x-3">
          <div v-if="isBranchManager" class="flex items-center space-x-1.5">
            <label class="text-xs font-medium text-[#7D6E64]">Status:</label>
            <select
              v-model="selectedProduct.status"
              class="bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-2.5 py-1 text-xs font-semibold text-[#2D231D]"
            >
              <option value="Active">Active</option>
              <option value="Inactive">Inactive</option>
            </select>
          </div>
          <button
            v-if="isBranchManager"
            type="button"
            @click="confirmDeleteProduct(selectedProduct)"
            class="px-3 py-1 text-xs font-semibold bg-red-50 border border-red-200 hover:bg-red-100 text-red-700 rounded-lg shadow-2xs transition cursor-pointer"
          >
            🗑️ Delete
          </button>
          <button
            type="button"
            @click="selectedProduct = null"
            class="text-[#8A7B71] hover:text-[#2A1D15] text-xl font-bold p-1 cursor-pointer"
          >
            &times;
          </button>
        </div>
      </div>

      <!-- If Branch Manager: Full Edit Form -->
      <form v-if="isBranchManager" @submit.prevent="saveProduct" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Product Name *</label>
            <input
              v-model="selectedProduct.product_name"
              type="text"
              required
              class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-1 focus:ring-[#BA5420]"
            />
          </div>
          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Category</label>
            <select
              v-model="selectedProduct.category_id"
              class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-1 focus:ring-[#BA5420]"
            >
              <option v-for="c in categories" :key="c.category_id" :value="c.category_id">
                {{ c.category_name }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Shelf Life (hours)</label>
            <input
              v-model.number="selectedProduct.shelf_life_hours"
              type="number"
              min="1"
              class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-1 focus:ring-[#BA5420]"
            />
          </div>
        </div>

        <div>
          <label class="block text-xs font-medium text-[#7D6E64] mb-1">Description</label>
          <textarea
            v-model="selectedProduct.description"
            rows="2"
            class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-1 focus:ring-[#BA5420]"
          ></textarea>
        </div>

        <!-- Independent Branch Price Control (Section 1 Requirement) -->
        <div class="pt-3 border-t border-[#EFE8DF] space-y-3 bg-[#FAF6F0] p-4 rounded-xl border border-[#EAE0D2]">
          <div class="flex items-center justify-between">
            <div>
              <h4 class="font-serif font-bold text-xs sm:text-sm text-[#2A1D15]">
                {{ currentBranchName }} Branch Price (ETB)
              </h4>
              <p class="text-[11px] text-[#8C7D73] font-poppins">
                Set the independent price for your branch. This change will strictly apply to {{ currentBranchName }} and will NOT affect other branches.
              </p>
            </div>
            <span class="text-[11px] bg-amber-100 text-amber-900 px-2.5 py-0.5 rounded-full font-bold">
              Independent Branch Control
            </span>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">
                Your Branch Counter Price (ETB) *
              </label>
              <input
                v-model.number="currentBranchPrice"
                type="number"
                required
                min="0"
                class="w-full bg-white border border-[#D8CDC0] rounded-lg px-3 py-2 text-sm font-bold text-[#BA5420] focus:ring-1 focus:ring-[#BA5420]"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">
                Standard Catalog Base Price (Reference)
              </label>
              <input
                :value="selectedProduct.base_price"
                disabled
                class="w-full bg-gray-100 border border-gray-200 rounded-lg px-3 py-2 text-sm font-medium text-gray-500 cursor-not-allowed"
              />
            </div>
          </div>
        </div>

        <div class="flex justify-end space-x-3 pt-3 border-t border-[#EFE8DF]">
          <button
            type="button"
            @click="selectedProduct = null"
            class="px-4 py-2 text-xs font-medium text-[#6A5A50] hover:bg-[#F3EFE9] rounded-lg transition cursor-pointer"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="isSaving"
            class="px-5 py-2 text-xs font-semibold bg-[#BA5420] hover:bg-[#9E4314] text-white rounded-lg shadow-2xs transition disabled:opacity-50 cursor-pointer"
          >
            {{ isSaving ? 'Saving...' : `Save ${currentBranchName} Price` }}
          </button>
        </div>
      </form>

      <!-- If Seller / Staff: Read-Only Detail View -->
      <div v-else class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 bg-gray-50 p-4 rounded-xl">
          <div>
            <span class="text-[11px] text-[#7A6B60] block font-medium">Product</span>
            <span class="font-bold text-sm text-[#2A1D15]">{{ selectedProduct.product_name }}</span>
          </div>
          <div>
            <span class="text-[11px] text-[#7A6B60] block font-medium">{{ currentBranchName }} Branch Price</span>
            <span class="font-bold text-sm text-[#BA5420]">ETB {{ Number(getBranchResolvedPrice(selectedProduct)).toLocaleString() }}</span>
          </div>
          <div>
            <span class="text-[11px] text-[#7A6B60] block font-medium">Shelf Life</span>
            <span class="font-medium text-sm text-[#2A1D15]">{{ selectedProduct.shelf_life_hours }} hours</span>
          </div>
        </div>
        <p class="text-xs text-[#7A6B60]">{{ selectedProduct.description || 'No description provided.' }}</p>
      </div>
    </div>

    <!-- MODAL 1: Submit Price Update Report to Super Admin (Section 1 Requirement) -->
    <div v-if="isPriceReportModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
      <div class="bg-white rounded-2xl shadow-2xl border border-[#E9E0D4] max-w-lg w-full p-6 space-y-4 animate-in fade-in duration-200">
        <div class="flex items-center justify-between border-b border-[#EFE8DF] pb-3">
          <div>
            <h3 class="text-lg font-serif font-bold text-[#2A1D15]">Submit Price Update Report</h3>
            <p class="text-xs text-[#8A7B71]">Forward branch pricing changes directly to the Super Admin</p>
          </div>
          <button @click="isPriceReportModalOpen = false" class="text-[#8A7B71] hover:text-[#2A1D15] text-xl font-bold p-1 cursor-pointer">
            &times;
          </button>
        </div>

        <form @submit.prevent="submitPriceReport" class="space-y-4">
          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Report Subject *</label>
            <input
              v-model="priceReportForm.title"
              type="text"
              required
              class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
            />
          </div>

          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Pricing Adjustments Summary *</label>
            <textarea
              v-model="priceReportForm.content"
              rows="4"
              required
              placeholder="e.g. Updated prices for Chocolate Cake (ETB 900) and Artisan Breads due to flour and dairy wholesale cost adjustments..."
              class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
            ></textarea>
          </div>

          <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-[11px] text-amber-900">
            ℹ️ This report will be logged with your timestamp and delivered immediately to the Super Admin Executive Reporting Center.
          </div>

          <div class="flex justify-end space-x-3 pt-3 border-t border-[#EFE8DF]">
            <button
              type="button"
              @click="isPriceReportModalOpen = false"
              class="px-4 py-2 text-xs font-medium text-[#6A5A50] hover:bg-[#F3EFE9] rounded-lg transition cursor-pointer"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="isSubmittingPriceReport"
              class="px-5 py-2 text-xs font-semibold bg-[#BA5420] hover:bg-[#9E4314] text-white rounded-lg shadow-2xs transition disabled:opacity-50 cursor-pointer"
            >
              {{ isSubmittingPriceReport ? 'Submitting...' : 'Send Report to Super Admin' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL 2: Categories Management Modal -->
    <div v-if="isCategoriesModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-3 sm:p-4">
      <div class="bg-white rounded-2xl shadow-2xl border border-[#E9E0D4] max-w-xl w-full p-4 sm:p-6 space-y-4 sm:space-y-5 animate-in fade-in duration-200 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-[#EFE8DF] pb-3">
          <div>
            <h3 class="text-lg font-serif font-bold text-[#2A1D15]">Product Categories</h3>
            <p class="text-xs text-[#8A7B71]">Manage bakery catalog classification</p>
          </div>
          <button @click="isCategoriesModalOpen = false" class="text-[#8A7B71] hover:text-[#2A1D15] text-xl font-bold p-1 cursor-pointer">
            &times;
          </button>
        </div>

        <!-- Categories Table -->
        <div class="max-h-60 overflow-y-auto border border-[#EFE8DF] rounded-xl">
          <table class="w-full text-left text-xs">
            <thead class="bg-[#FAF6F0] text-[#7A6B60] uppercase font-semibold border-b border-[#EFE8DF]">
              <tr>
                <th class="py-2.5 px-4">Category Name</th>
                <th class="py-2.5 px-3">Description</th>
                <th class="py-2.5 px-3 text-right">Products</th>
                <th class="py-2.5 px-3 text-right">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#F3EDE4]">
              <tr v-for="cat in categories" :key="cat.category_id" class="hover:bg-[#FCFAF7]">
                <td class="py-2.5 px-4 font-bold text-[#2A1D15]">{{ cat.category_name }}</td>
                <td class="py-2.5 px-3 text-[#6A5A50]">{{ cat.description || '—' }}</td>
                <td class="py-2.5 px-3 text-right font-semibold text-[#BA5420]">{{ cat.products_count ?? cat.products?.length ?? '—' }}</td>
                <td class="py-2.5 px-3 text-right">
                  <button
                    type="button"
                    @click.stop="deleteCategory(cat)"
                    class="text-red-600 hover:text-red-800 font-semibold text-xs transition cursor-pointer"
                  >
                    Delete
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Add Category Sub-form -->
        <form @submit.prevent="submitNewCategory" class="bg-[#FAF6F0] p-4 rounded-xl border border-[#EAE0D2] space-y-3">
          <div class="font-bold text-xs text-[#2A1D15]">Add New Category</div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] font-medium text-[#7D6E64] mb-1">Category Name *</label>
              <input
                v-model="newCategory.category_name"
                type="text"
                placeholder="e.g. Traditional Breads"
                required
                class="w-full bg-white border border-[#D8CDC0] rounded-lg px-3 py-1.5 text-xs text-[#2D231D]"
              />
            </div>
            <div>
              <label class="block text-[11px] font-medium text-[#7D6E64] mb-1">Description</label>
              <input
                v-model="newCategory.description"
                type="text"
                placeholder="e.g. Sourdough, injera, rolls"
                class="w-full bg-white border border-[#D8CDC0] rounded-lg px-3 py-1.5 text-xs text-[#2D231D]"
              />
            </div>
          </div>
          <div class="flex justify-end">
            <button
              type="submit"
              :disabled="isSubmittingCategory"
              class="px-4 py-1.5 text-xs font-semibold bg-[#BA5420] hover:bg-[#9E4314] text-white rounded-lg shadow-2xs transition disabled:opacity-50 cursor-pointer"
            >
              {{ isSubmittingCategory ? 'Adding...' : '+ Create Category' }}
            </button>
          </div>
        </form>

        <div class="flex justify-end pt-2">
          <button
            type="button"
            @click="isCategoriesModalOpen = false"
            class="px-4 py-2 text-xs font-medium text-[#6A5A50] hover:bg-[#F3EFE9] rounded-lg transition cursor-pointer"
          >
            Close
          </button>
        </div>
      </div>
    </div>

    <!-- MODAL 3: Add New Product Modal -->
    <div v-if="isAddProductModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
      <div class="bg-white rounded-2xl shadow-2xl border border-[#E9E0D4] max-w-2xl w-full p-6 space-y-5 animate-in fade-in duration-200 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-[#EFE8DF] pb-3">
          <div>
            <h3 class="text-lg font-serif font-bold text-[#2A1D15]">Add New Bakery Item</h3>
            <p class="text-xs text-[#8A7B71]">Add cakes, bread, or pastries for {{ currentBranchName }}</p>
          </div>
          <button @click="isAddProductModalOpen = false" class="text-[#8A7B71] hover:text-[#2A1D15] text-xl font-bold p-1 cursor-pointer">
            &times;
          </button>
        </div>

        <div v-if="modalErrorMsg" class="p-3 bg-red-50 border border-red-200 rounded-lg text-xs font-semibold text-red-700 flex items-center justify-between">
          <span>{{ modalErrorMsg }}</span>
          <button type="button" @click="modalErrorMsg = ''" class="text-red-700 font-bold ml-2 cursor-pointer">&times;</button>
        </div>

        <form @submit.prevent="submitNewProduct" class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Product Name *</label>
              <input
                v-model="newProduct.product_name"
                type="text"
                placeholder="e.g. Strawberry Shortcake"
                required
                class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-1 focus:ring-[#BA5420]"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Category *</label>
              <select
                v-model="newProduct.category_id"
                required
                class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-1 focus:ring-[#BA5420]"
              >
                <option value="" disabled>Select category</option>
                <option v-for="c in categories" :key="c.category_id" :value="c.category_id">
                  {{ c.category_name }}
                </option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">{{ currentBranchName }} Price (ETB) *</label>
              <input
                v-model.number="newProduct.base_price"
                type="number"
                placeholder="e.g. 750"
                min="0"
                required
                class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-1 focus:ring-[#BA5420]"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Shelf Life (Hours) *</label>
              <input
                v-model.number="newProduct.shelf_life_hours"
                type="number"
                placeholder="48"
                min="1"
                required
                class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-1 focus:ring-[#BA5420]"
              />
            </div>
          </div>

          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Product Photo</label>
            <div class="flex items-center space-x-2">
              <input
                v-model="newProduct.image_url"
                type="text"
                placeholder="Image URL or upload file"
                class="flex-1 bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-1 focus:ring-[#BA5420]"
              />
              <label class="px-3 py-2 text-xs font-semibold bg-white border border-[#D8CDC0] hover:bg-[#F4ECE3] text-[#4A3B32] rounded-lg shadow-2xs transition cursor-pointer shrink-0">
                📁 Upload
                <input type="file" accept="image/*" class="hidden" @change="e => uploadImage(e, newProduct)" />
              </label>
            </div>
          </div>

          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Description</label>
            <textarea
              v-model="newProduct.description"
              rows="3"
              placeholder="Freshly prepared using stoneground flour and premium ingredients..."
              class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-1 focus:ring-[#BA5420]"
            ></textarea>
          </div>

          <div class="flex justify-end space-x-3 pt-3 border-t border-[#EFE8DF]">
            <button
              type="button"
              @click="isAddProductModalOpen = false"
              class="px-4 py-2 text-xs font-medium text-[#6A5A50] hover:bg-[#F3EFE9] rounded-lg transition cursor-pointer"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="isSubmittingProduct"
              class="px-5 py-2 text-xs font-semibold bg-[#BA5420] hover:bg-[#9E4314] text-white rounded-lg shadow-2xs transition disabled:opacity-50 cursor-pointer"
            >
              {{ isSubmittingProduct ? 'Saving Product...' : 'Add to Catalog' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '../../api';
import { useAuthStore } from '../../stores/auth';
import { useDialogStore } from '../../stores/dialog';
import { useToastStore } from '../../stores/toast';

const authStore = useAuthStore();
const dialog = useDialogStore();
const toast = useToastStore();

const products = ref([]);
const categories = ref([]);
const branches = ref([]);
const branchMetrics = ref({
  today_units_sold: 0,
  week_units_sold: 0,
  today_revenue: 0,
  week_revenue: 0,
});
const selectedCategory = ref('all');
const searchQuery = ref('');
const selectedProduct = ref(null);
const currentBranchPrice = ref(0);
const successMsg = ref('');
const errorMsg = ref('');
const isSaving = ref(false);

// Role & Branch scoping
const isBranchManager = computed(() => {
  return [
    'Super Admin',
    'Main Branch Manager',
    'Branch Manager',
    'Branch Admin',
    'Branch Seller / Cashier',
    'Branch Seller',
    'Cashier',
    'Branch Staff',
  ].includes(authStore.user?.role);
});

const currentBranch = computed(() => {
  return authStore.user?.branch || null;
});

const currentBranchId = computed(() => {
  return authStore.user?.branch_id || authStore.user?.branch?.branch_id || 1;
});

const currentBranchName = computed(() => {
  return currentBranch.value?.branch_name || (authStore.user?.role === 'Main Branch Manager' ? 'Main Branch' : 'Assigned Branch');
});

// Modals
const isCategoriesModalOpen = ref(false);
const isAddProductModalOpen = ref(false);
const isPriceReportModalOpen = ref(false);
const isSubmittingCategory = ref(false);
const isSubmittingProduct = ref(false);
const isSubmittingPriceReport = ref(false);
const modalErrorMsg = ref('');

const priceReportForm = ref({
  title: '',
  content: '',
});

const newCategory = ref({
  category_name: '',
  description: '',
});

const newProduct = ref({
  product_name: '',
  category_id: '',
  base_price: 600,
  shelf_life_hours: 48,
  image_url: 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=600&auto=format&fit=crop&q=80',
  description: '',
});

const loadCatalog = async () => {
  try {
    const branchParam = currentBranchId.value ? `?branch_id=${currentBranchId.value}` : '';
    const [pRes, cRes, bRes, mRes] = await Promise.all([
      api.get(`/products${branchParam}`),
      api.get('/categories'),
      api.get('/branches'),
      api.get(`/admin/products/branch-metrics${branchParam}`).catch(() => ({ data: {} })),
    ]);
    products.value = pRes.data;
    categories.value = cRes.data;
    branches.value = bRes.data;
    if (mRes.data) {
      branchMetrics.value = mRes.data;
    }

    if (!selectedProduct.value && products.value.length > 0) {
      selectProductForEdit(products.value[0]);
    }
  } catch (err) {
    console.error('Failed to load products and prices', err);
  }
};

const getBranchResolvedPrice = (p) => {
  if (!p) return 0;
  if (p.price !== undefined && p.price !== null) return p.price;
  return p.base_price || 0;
};

const hasBranchPriceOverride = (p) => {
  if (!p) return false;
  return Boolean(p.has_override || p.has_branch_override);
};

const selectProductForEdit = (p) => {
  selectedProduct.value = { ...p };
  currentBranchPrice.value = getBranchResolvedPrice(p);
};

const filteredProducts = computed(() => {
  return products.value.filter(p => {
    const matchesCat = selectedCategory.value === 'all' || p.category_id === selectedCategory.value;
    const matchesSearch = !searchQuery.value || p.product_name.toLowerCase().includes(searchQuery.value.toLowerCase());
    return matchesCat && matchesSearch;
  });
});

const saveProduct = async () => {
  if (!selectedProduct.value || !isBranchManager.value) return;
  isSaving.value = true;
  successMsg.value = '';
  errorMsg.value = '';

  try {
    // 1. Update product details (shelf life, description, status, name)
    await api.patch(`/admin/products/${selectedProduct.value.product_id}`, {
      product_name: selectedProduct.value.product_name,
      category_id: selectedProduct.value.category_id,
      shelf_life_hours: selectedProduct.value.shelf_life_hours,
      description: selectedProduct.value.description,
      status: selectedProduct.value.status,
      image_url: selectedProduct.value.image_url,
    });

    // 2. Save branch price for this specific branch only!
    if (currentBranchId.value && currentBranchPrice.value !== undefined) {
      await api.post(`/admin/products/${selectedProduct.value.product_id}/branch-price`, {
        branch_id: currentBranchId.value,
        price: currentBranchPrice.value,
        active: true,
      });
    }

    successMsg.value = `Product "${selectedProduct.value.product_name}" price updated to ETB ${currentBranchPrice.value.toLocaleString()} for ${currentBranchName.value}!`;
    await loadCatalog();
  } catch (err) {
    errorMsg.value = err.response?.data?.message || 'Failed to save product details';
  } finally {
    isSaving.value = false;
  }
};

const openAddProductModal = () => {
  modalErrorMsg.value = '';
  newProduct.value = {
    product_name: '',
    category_id: categories.value[0]?.category_id || '',
    base_price: 650,
    shelf_life_hours: 48,
    image_url: 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=600&auto=format&fit=crop&q=80',
    description: '',
  };
  isAddProductModalOpen.value = true;
};

const submitNewProduct = async () => {
  modalErrorMsg.value = '';
  if (!newProduct.value.product_name) {
    modalErrorMsg.value = 'Please provide a product name.';
    return;
  }
  if (!newProduct.value.category_id) {
    modalErrorMsg.value = 'Please select a product category.';
    return;
  }
  isSubmittingProduct.value = true;
  successMsg.value = '';
  errorMsg.value = '';
  try {
    const res = await api.post('/admin/products', newProduct.value);
    successMsg.value = `Product "${res.data.product_name}" added to catalog successfully with ${currentBranchName.value} price set to ETB ${newProduct.value.base_price}!`;
    isAddProductModalOpen.value = false;
    await loadCatalog();
    if (res.data) {
      selectProductForEdit(res.data);
    }
  } catch (err) {
    const msg = err.response?.data?.message || 'Failed to create product';
    modalErrorMsg.value = msg;
    errorMsg.value = msg;
  } finally {
    isSubmittingProduct.value = false;
  }
};

const openPriceReportModal = () => {
  priceReportForm.value = {
    title: `Product & Price Update Notice - ${currentBranchName.value}`,
    content: `Updated counter and branch prices for ${currentBranchName.value}. All changes verified against current inventory and supplier cost adjustments.`,
  };
  isPriceReportModalOpen.value = true;
};

const submitPriceReport = async () => {
  if (!priceReportForm.value.content || !isBranchManager.value) return;
  isSubmittingPriceReport.value = true;
  try {
    const res = await api.post('/admin/products/report-price-update', priceReportForm.value);
    successMsg.value = res.data?.message || 'Price update report submitted to Super Admin successfully!';
    isPriceReportModalOpen.value = false;
  } catch (err) {
    errorMsg.value = err.response?.data?.message || 'Failed to submit price report';
  } finally {
    isSubmittingPriceReport.value = false;
  }
};

const submitNewCategory = async () => {
  if (!isBranchManager.value) return;
  isSubmittingCategory.value = true;
  try {
    const res = await api.post('/admin/categories', newCategory.value);
    categories.value.push(res.data);
    newCategory.value = { category_name: '', description: '' };
    successMsg.value = `Category "${res.data.category_name}" created successfully!`;
  } catch (err) {
    errorMsg.value = err.response?.data?.message || 'Failed to create category';
  } finally {
    isSubmittingCategory.value = false;
  }
};

const deleteCategory = async (cat) => {
  if (!isBranchManager.value) return;
  const confirmed = await dialog.confirm({
    title: 'Delete Category',
    message: `Are you sure you want to delete category "${cat.category_name}"? Products inside must be reassigned first.`,
    confirmText: 'Delete Category',
    variant: 'danger',
  });
  if (!confirmed) return;
  try {
    const res = await api.delete(`/admin/categories/${cat.category_id}`);
    const msg = res.data.message || 'Category deleted.';
    successMsg.value = msg;
    toast.success(msg);
    await loadCatalog();
  } catch (err) {
    const msg = err.response?.data?.message || 'Failed to delete category';
    errorMsg.value = msg;
    toast.error(msg);
  }
};

const confirmDeleteProduct = async (prod) => {
  if (!isBranchManager.value) return;
  const confirmed = await dialog.confirm({
    title: 'Delete Product',
    message: `Are you sure you want to delete "${prod.product_name}"? This removes it from the catalog.`,
    confirmText: 'Delete Product',
    variant: 'danger',
  });
  if (!confirmed) return;
  try {
    const res = await api.delete(`/admin/products/${prod.product_id}`);
    const msg = res.data.message || 'Product deleted.';
    successMsg.value = msg;
    toast.success(msg);
    if (selectedProduct.value?.product_id === prod.product_id) {
      selectedProduct.value = null;
    }
    await loadCatalog();
  } catch (err) {
    const msg = err.response?.data?.message || 'Failed to delete product';
    errorMsg.value = msg;
    toast.error(msg);
  }
};

const uploadImage = async (event, targetObj) => {
  if (!isBranchManager.value) return;
  const file = event.target.files?.[0];
  if (!file) return;
  const formData = new FormData();
  formData.append('image', file);
  try {
    const res = await api.post('/admin/products/upload-image', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    targetObj.image_url = res.data.image_url;
    successMsg.value = 'Product photo uploaded successfully!';
    setTimeout(() => { successMsg.value = ''; }, 3500);
  } catch (err) {
    errorMsg.value = err.response?.data?.message || 'Failed to upload photo';
  }
};

onMounted(() => {
  loadCatalog();
});
</script>
