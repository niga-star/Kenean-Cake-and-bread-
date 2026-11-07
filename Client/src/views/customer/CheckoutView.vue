<template>
  <div class="sweetbakes-container py-8 sm:py-10 space-y-8 font-sans selection:bg-[#FCE7F0] selection:text-[#DE1B54] text-[#29231F] dark:text-[#FAF3EE]">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-gray-200 dark:border-[#382318] pb-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-serif font-black text-[#2A1810] dark:text-[#FAF3EE]">Checkout</h1>
        <p class="text-xs text-[#786B60] dark:text-[#C5B7AF] mt-1">Please enter your fulfillment and payment details to place your bakery order.</p>
      </div>

      <router-link to="/cart" class="text-xs font-bold text-[#DE1B54] hover:text-[#C51345] flex items-center space-x-1 transition">
        <span>← Back to Bag</span>
      </router-link>
    </div>

    <!-- Active Branch & Draft Notification Bar -->
    <div class="space-y-3">
      <div class="bg-white dark:bg-[#1E120B] border border-gray-200 dark:border-[#382318] px-5 py-3.5 rounded-2xl flex flex-col sm:flex-row items-center justify-between text-xs text-[#29231F] dark:text-[#FAF3EE] shadow-2xs gap-3">
        <div class="flex items-center space-x-2.5">
          <span class="w-2.5 h-2.5 rounded-full bg-[#387A54] animate-pulse shrink-0"></span>
          <span>Order fulfilled strictly by <strong class="text-[#2A1810] dark:text-[#FAF3EE]">{{ currentBranchName }} Branch</strong>. Stock and delivery boundaries are locked to this location.</span>
        </div>
        <button
          type="button"
          @click="branchStore.openPicker()"
          class="font-bold text-[#DE1B54] hover:underline text-xs shrink-0 cursor-pointer"
        >
          Switch branch →
        </button>
      </div>

      <!-- Restored Draft Banner (Survives Refresh) -->
      <div v-if="hasRestoredDraft" class="bg-rose-50/60 dark:bg-[#2A1810] border border-rose-200 dark:border-[#4A2518] px-4 py-2.5 rounded-xl flex items-center justify-between text-xs text-[#2A1810] dark:text-[#FAF3EE]">
        <div class="flex items-center space-x-2">
          <span>💾</span>
          <span>We restored your checkout draft from your last session.</span>
        </div>
        <button
          type="button"
          @click="clearSavedDraft"
          class="text-xs font-bold text-[#DE1B54] hover:underline cursor-pointer"
        >
          Reset form
        </button>
      </div>
    </div>

    <!-- Empty Bag State -->
    <div v-if="cartItems.length === 0" class="bg-white dark:bg-[#1E120B] rounded-3xl border border-gray-200 dark:border-[#382318] p-12 text-center space-y-4">
      <div class="text-4xl">🛍️</div>
      <h2 class="text-xl font-serif font-bold text-[#2A1810] dark:text-[#FAF3EE]">Your shopping bag is empty</h2>
      <p class="text-xs text-[#786B60] dark:text-[#C5B7AF] max-w-sm mx-auto">
        Please explore our artisanal fresh bakery collection and add cakes or pastries before proceeding to checkout.
      </p>
      <div class="pt-2">
        <router-link
          to="/shop"
          class="inline-flex items-center px-6 py-3 bg-[#DE1B54] hover:bg-[#C51345] text-white text-xs font-bold uppercase tracking-wider rounded-2xl shadow-xs transition cursor-pointer"
        >
          Browse Bakery Menu →
        </router-link>
      </div>
    </div>

    <!-- Checkout Grid -->
    <form v-else @submit.prevent="handlePlaceOrder" class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
      <!-- Left Column: Fulfillment, Contact & Address (7 cols) -->
      <div class="lg:col-span-7 space-y-6">
        <!-- 1. Fulfillment Mode -->
        <div class="bg-white dark:bg-[#1E120B] rounded-3xl border border-gray-200 dark:border-[#382318] p-5 sm:p-8 space-y-6 shadow-2xs">
          <div class="flex items-center justify-between border-b border-gray-100 dark:border-[#382318] pb-3">
            <h2 class="text-base font-serif font-bold text-[#2A1810] dark:text-[#FAF3EE]">
              1. Fulfillment Method
            </h2>
            <span class="text-xs text-[#786B60] dark:text-[#C5B7AF] font-medium">Step 1 of 3</span>
          </div>

          <!-- Fulfillment Toggle Buttons -->
          <div class="grid grid-cols-1 xs:grid-cols-2 gap-3">
            <button
              type="button"
              @click="setMethod('Delivery')"
              class="py-3.5 px-4 min-h-[48px] rounded-2xl text-xs font-bold transition flex items-center justify-center space-x-2 cursor-pointer active:scale-98"
              :class="fulfillmentMethod === 'Delivery' 
                ? 'bg-[#DE1B54] text-white shadow-xs' 
                : 'bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] text-[#786B60] dark:text-[#C5B7AF] hover:bg-rose-50 dark:hover:bg-[#341822]'"
            >
              <span class="text-base">🚚</span>
              <span>Citywide Doorstep Delivery</span>
            </button>

            <button
              type="button"
              @click="setMethod('Pickup')"
              class="py-3.5 px-4 min-h-[48px] rounded-2xl text-xs font-bold transition flex items-center justify-center space-x-2 cursor-pointer active:scale-98"
              :class="fulfillmentMethod === 'Pickup' 
                ? 'bg-[#DE1B54] text-white shadow-xs' 
                : 'bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] text-[#786B60] dark:text-[#C5B7AF] hover:bg-rose-50 dark:hover:bg-[#341822]'"
            >
              <span class="text-base">🏪</span>
              <span>Shop Pickup ({{ currentBranchName }})</span>
            </button>
          </div>

          <!-- Delivery / Pickup Time Slots -->
          <div class="space-y-2 pt-1">
            <label class="block text-xs font-bold uppercase tracking-wider text-[#2A1810] dark:text-[#FAF3EE]">
              Preferred {{ fulfillmentMethod === 'Pickup' ? 'Pickup' : 'Delivery' }} Window
            </label>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
              <button
                type="button"
                v-for="slot in timeSlots"
                :key="slot.value"
                @click="selectedTimeSlot = slot.value"
                class="py-2.5 px-2.5 min-h-[42px] rounded-xl text-xs font-bold transition border text-center cursor-pointer"
                :class="selectedTimeSlot === slot.value 
                  ? 'bg-[#DE1B54] text-white border-[#DE1B54] shadow-xs' 
                  : 'bg-[#FAF7F5] dark:bg-[#2A1810] border-gray-200 dark:border-[#382318] text-[#2A1810] dark:text-[#FAF3EE] hover:bg-rose-50 dark:hover:bg-[#341822]'"
              >
                <div class="text-[10px] opacity-80">{{ slot.period }}</div>
                <div class="text-xs mt-0.5 whitespace-nowrap">{{ slot.time }}</div>
              </button>
            </div>
          </div>

          <!-- Pickup Counter Notice -->
          <div v-if="fulfillmentMethod === 'Pickup'" class="p-4 bg-rose-50/40 dark:bg-[#2A1810] rounded-2xl border border-rose-100 dark:border-[#382318] space-y-2 text-xs">
            <div class="font-bold text-[#2A1810] dark:text-[#FAF3EE] flex items-center space-x-2">
              <span class="text-base">📍</span>
              <span>Pickup Counter: {{ currentBranchName }} Branch</span>
            </div>
            <div class="text-[#786B60] dark:text-[#C5B7AF] pl-6">
              {{ branchDetails?.address || 'Bole Medhanialem Area, Addis Ababa' }}
            </div>
            <div class="text-[#DE1B54] font-semibold pl-6">
              🕒 Daily Hours: {{ branchDetails?.opening_hours || 'Mon - Sun: 07:00 - 21:00' }}
            </div>
            <div class="text-[#786B60] dark:text-[#C5B7AF] pl-6">
              📞 Branch Phone: {{ branchDetails?.phone || '+251 911 123 456' }}
            </div>
            <div class="text-[11px] text-[#387A54] dark:text-emerald-400 font-bold pl-6 pt-1">
              ✓ Free Shop Pickup (ETB 0 fulfillment fee)
            </div>
          </div>
        </div>

        <!-- 2. Contact Information -->
        <div class="bg-white dark:bg-[#1E120B] rounded-3xl border border-gray-200 dark:border-[#382318] p-5 sm:p-8 space-y-5 shadow-2xs">
          <div class="flex items-center justify-between border-b border-gray-100 dark:border-[#382318] pb-3">
            <h2 class="text-base font-serif font-bold text-[#2A1810] dark:text-[#FAF3EE]">
              2. Contact Information
            </h2>
            <span class="text-xs text-[#786B60] dark:text-[#C5B7AF] font-medium">Step 2 of 3</span>
          </div>

          <!-- Guest Sign-in Hint -->
          <div v-if="!authStore.isAuthenticated" class="p-3 bg-amber-50/60 dark:bg-[#2A1810] border border-amber-200 dark:border-amber-900/40 rounded-xl text-xs text-[#8A5016] dark:text-amber-300 flex items-center justify-between">
            <div class="flex items-center space-x-2">
              <span>💡</span>
              <span>Have a SweetBakes account?</span>
            </div>
            <router-link to="/login" class="font-bold underline hover:text-[#DE1B54] ml-2">
              Sign In for 1-click address autofill →
            </router-link>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-[#2A1810] dark:text-[#FAF3EE] mb-1.5">
                Full Name <span class="text-red-500">*</span>
              </label>
              <input
                v-model="customer.name"
                type="text"
                placeholder="e.g. Sara Mengistu"
                class="w-full bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] focus:border-[#DE1B54] focus:bg-white dark:focus:bg-[#1E120B] rounded-xl px-4 py-2.5 text-xs text-[#2A1810] dark:text-[#FAF3EE] focus:outline-none transition min-h-[42px]"
                required
              />
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-[#2A1810] dark:text-[#FAF3EE] mb-1.5">
                Phone Number (Ethiopian format) <span class="text-red-500">*</span>
              </label>
              <input
                v-model="customer.phone"
                type="tel"
                placeholder="+251 9XX XXX XXX or 09XX XXX XXX"
                class="w-full bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] focus:border-[#DE1B54] focus:bg-white dark:focus:bg-[#1E120B] rounded-xl px-4 py-2.5 text-xs text-[#2A1810] dark:text-[#FAF3EE] focus:outline-none transition min-h-[42px]"
                required
              />
            </div>
          </div>
        </div>

        <!-- 3. Delivery Address (Only for Home Delivery) -->
        <div v-if="fulfillmentMethod === 'Delivery'" class="bg-white dark:bg-[#1E120B] rounded-3xl border border-gray-200 dark:border-[#382318] p-5 sm:p-8 space-y-5 shadow-2xs">
          <div class="flex items-center justify-between border-b border-gray-100 dark:border-[#382318] pb-3">
            <h2 class="text-base font-serif font-bold text-[#2A1810] dark:text-[#FAF3EE]">
              3. Delivery Address &amp; Zone
            </h2>
            <span class="text-xs text-[#786B60] dark:text-[#C5B7AF] font-medium">Step 3 of 3</span>
          </div>

          <!-- Saved Address Selector (Authenticated Users) -->
          <div v-if="authStore.isAuthenticated && savedAddresses.length > 0" class="space-y-3">
            <div class="flex items-center justify-between text-xs">
              <span class="font-bold text-[#2A1810] dark:text-[#FAF3EE] flex items-center space-x-1.5">
                <span>📍</span>
                <span>Select from Saved Addresses ({{ savedAddresses.length }})</span>
              </span>
              <button
                type="button"
                @click="addressMode = addressMode === 'saved' ? 'custom' : 'saved'"
                class="text-xs font-bold text-[#DE1B54] hover:underline cursor-pointer"
              >
                {{ addressMode === 'saved' ? '+ Enter new address instead' : '← Pick from saved addresses' }}
              </button>
            </div>

            <!-- Saved Addresses Grid -->
            <div v-if="addressMode === 'saved'" class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
              <div
                v-for="addr in savedAddresses"
                :key="addr.address_id"
                @click="selectSavedAddress(addr)"
                class="p-4 rounded-2xl border text-xs cursor-pointer transition relative flex flex-col justify-between"
                :class="selectedAddressId === addr.address_id
                  ? 'border-[#DE1B54] bg-rose-50/50 dark:bg-[#341822]/40 shadow-xs'
                  : 'border-gray-200 dark:border-[#382318] bg-[#FAF7F5] dark:bg-[#2A1810] hover:border-rose-200'"
              >
                <div class="space-y-1">
                  <div class="flex items-center justify-between">
                    <span class="font-bold text-[#2A1810] dark:text-[#FAF3EE] flex items-center space-x-1">
                      <span>{{ addr.is_default ? '⭐' : '📍' }}</span>
                      <span>{{ addr.region || 'Addis Ababa' }}</span>
                    </span>
                    <span v-if="addr.is_default" class="text-[10px] font-bold bg-[#DE1B54]/10 text-[#DE1B54] px-1.5 py-0.5 rounded-full">
                      Default
                    </span>
                  </div>
                  <div class="text-[#29231F] dark:text-[#FAF3EE] font-medium line-clamp-2">
                    {{ addr.street }}
                  </div>
                  <div v-if="addr.notes" class="text-[11px] text-[#786B60] dark:text-[#C5B7AF] italic line-clamp-1">
                    “{{ addr.notes }}”
                  </div>
                </div>

                <div class="pt-2 flex items-center justify-between text-[11px]">
                  <span :class="selectedAddressId === addr.address_id ? 'text-[#DE1B54] font-bold' : 'text-[#786B60] dark:text-[#C5B7AF]'">
                    {{ selectedAddressId === addr.address_id ? '✓ Selected for Delivery' : 'Click to select' }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Zone Selection -->
          <div class="space-y-1.5">
            <label class="block text-xs font-bold uppercase tracking-wider text-[#2A1810] dark:text-[#FAF3EE]">
              Select Delivery Zone ({{ currentBranchName }} Branch Coverage) <span class="text-red-500">*</span>
            </label>
            <div v-if="loadingZones" class="text-xs text-[#786B60] py-2">Loading active delivery zones...</div>
            <select
              v-else
              v-model="selectedZoneId"
              @change="onZoneChange"
              class="w-full bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] focus:border-[#DE1B54] focus:bg-white dark:focus:bg-[#1E120B] rounded-xl px-4 py-2.5 text-xs text-[#29231F] dark:text-[#FAF3EE] focus:outline-none font-medium min-h-[42px]"
              required
            >
              <option value="" disabled>-- Select your delivery zone in Addis Ababa --</option>
              <option
                v-for="zone in activeZones"
                :key="zone.zone_id"
                :value="zone.zone_id"
              >
                {{ zone.zone_name }} — ETB {{ subtotal >= 1000 ? '0 (Free over ETB 1,000)' : zone.delivery_fee }}
              </option>
              <option value="outside">Address is outside these active zones</option>
            </select>
          </div>

          <!-- Outside Zone Warning -->
          <div v-if="selectedZoneId === 'outside'" class="p-4 bg-[#FAF0F0] dark:bg-rose-950/40 border border-[#E8C4C4] dark:border-rose-900/60 rounded-2xl text-xs text-[#B84B42] space-y-2">
            <div class="font-bold flex items-center space-x-1.5">
              <span>⚠️</span>
              <span>Outside {{ currentBranchName }} Delivery Radius</span>
            </div>
            <p>
              {{ currentBranchName }} branch does not deliver to addresses outside its active coverage zone. You can switch to Shop Pickup or switch to another branch nearby.
            </p>
            <div class="pt-1 flex items-center space-x-3">
              <button
                type="button"
                @click="setMethod('Pickup')"
                class="px-3 py-1.5 bg-[#B84B42] text-white rounded-xl font-bold text-xs shadow-xs cursor-pointer"
              >
                Switch to Free Shop Pickup (ETB 0)
              </button>
              <button
                type="button"
                @click="branchStore.openPicker()"
                class="font-bold text-[#B84B42] underline cursor-pointer"
              >
                Switch Branch
              </button>
            </div>
          </div>

          <!-- Specific Address Text Input -->
          <div class="space-y-1.5">
            <label class="block text-xs font-bold uppercase tracking-wider text-[#2A1810] dark:text-[#FAF3EE]">
              Specific Street Address / Landmark / House No. <span class="text-red-500">*</span>
            </label>
            <input
              v-model="customer.address"
              type="text"
              placeholder="e.g. Bole Medhanialem, Near Edna Mall, Street 3, House 42"
              class="w-full bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] focus:border-[#DE1B54] focus:bg-white dark:focus:bg-[#1E120B] rounded-xl px-4 py-2.5 text-xs text-[#29231F] dark:text-[#FAF3EE] focus:outline-none transition min-h-[42px]"
              required
            />
          </div>

          <!-- Save Address for Future Checkbox (for Authenticated Users) -->
          <div v-if="authStore.isAuthenticated && addressMode === 'custom'" class="pt-1">
            <label class="flex items-center space-x-2 text-xs text-[#2A1810] dark:text-[#FAF3EE] cursor-pointer">
              <input
                type="checkbox"
                v-model="saveAddressForLater"
                class="rounded border-gray-300 text-[#DE1B54] focus:ring-[#DE1B54]"
              />
              <span>Save this delivery address to my account for one-click future orders</span>
            </label>
          </div>

          <!-- Special Delivery Instructions -->
          <div class="space-y-1.5 pt-1">
            <label class="block text-xs font-bold uppercase tracking-wider text-[#2A1810] dark:text-[#FAF3EE]">
              Gate Code, Landmark, or Delivery Instructions (Optional)
            </label>
            <textarea
              v-model="customer.notes"
              rows="2"
              placeholder="e.g. Ring gate bell #4, call upon arrival at the gate, or custom message on greeting card..."
              class="w-full bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] focus:border-[#DE1B54] focus:bg-white dark:focus:bg-[#1E120B] rounded-xl p-3 text-xs text-[#29231F] dark:text-[#FAF3EE] focus:outline-none transition"
            ></textarea>
          </div>
        </div>
      </div>

      <!-- Right Column: Order Review & Payment Methods (5 cols) -->
      <div class="lg:col-span-5 space-y-6">
        <div class="bg-white dark:bg-[#1E120B] rounded-3xl border border-gray-200 dark:border-[#382318] p-5 sm:p-8 space-y-5 shadow-2xs">
          <!-- Header with Live Stock Re-check -->
          <div class="flex items-center justify-between border-b border-gray-100 dark:border-[#382318] pb-3">
            <h2 class="text-base font-serif font-bold text-[#2A1810] dark:text-[#FAF3EE]">
              Order Summary
            </h2>
            <button
              type="button"
              @click="recheckStock"
              :disabled="cartStore.isRevalidating"
              class="text-[11px] font-bold text-[#DE1B54] hover:underline flex items-center space-x-1 cursor-pointer disabled:opacity-50"
            >
              <span>{{ cartStore.isRevalidating ? 'Checking...' : '⟳ Check Live Stock' }}</span>
            </button>
          </div>

          <!-- Items Review List with Stock Reassurance Badges -->
          <div class="space-y-3 divide-y divide-gray-100 dark:divide-[#382318] max-h-60 overflow-y-auto pr-1">
            <div
              v-for="item in cartItems"
              :key="item.product_id"
              class="pt-3 first:pt-0 flex items-center justify-between text-xs"
            >
              <div class="flex items-center space-x-3">
                <img
                  :src="item.image_url || 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=100'"
                  :alt="item.product_name"
                  class="w-12 h-12 rounded-xl object-cover border border-gray-200 dark:border-[#382318] bg-[#FAF7F5] dark:bg-[#2A1810] shrink-0"
                />
                <div>
                  <div class="font-bold text-[#2A1810] dark:text-[#FAF3EE]">{{ item.product_name }}</div>
                  <div class="text-[11px] text-[#786B60] dark:text-[#C5B7AF]">
                    ETB {{ Number(item.price).toLocaleString() }} &times; {{ item.quantity }}
                  </div>
                  <!-- Live Stock Badge -->
                  <div class="text-[10px] mt-0.5">
                    <span
                      v-if="item.isSoldOut || (item.available !== undefined && item.available <= 0)"
                      class="text-red-600 font-bold"
                    >
                      ✕ Sold Out at {{ currentBranchName }}
                    </span>
                    <span
                      v-else-if="item.available !== undefined && item.available <= 3"
                      class="text-amber-600 dark:text-amber-400 font-bold"
                    >
                      ⚠️ Only {{ item.available }} left in branch
                    </span>
                    <span
                      v-else
                      class="text-[#387A54] dark:text-emerald-400 font-medium"
                    >
                      ✓ In stock at {{ currentBranchName }}
                    </span>
                  </div>
                </div>
              </div>

              <div class="font-black text-[#DE1B54] shrink-0 pl-2">
                ETB {{ (item.price * item.quantity).toLocaleString() }}
              </div>
            </div>
          </div>

          <!-- Calculation Breakdown -->
          <div class="space-y-2.5 pt-3 border-t border-gray-100 dark:border-[#382318] text-xs">
            <div class="flex justify-between items-center text-[#786B60] dark:text-[#C5B7AF]">
              <span>Subtotal</span>
              <span class="font-bold text-[#2A1810] dark:text-[#FAF3EE]">ETB {{ subtotal.toLocaleString() }}</span>
            </div>

            <div class="flex justify-between items-center text-[#786B60] dark:text-[#C5B7AF]">
              <span>Delivery Fee</span>
              <span class="font-bold" :class="actualDeliveryFee === 0 ? 'text-[#387A54] dark:text-emerald-400' : 'text-[#2A1810] dark:text-[#FAF3EE]'">
                {{ fulfillmentMethod === 'Delivery' ? (actualDeliveryFee === 0 ? 'FREE (Over ETB 1,000)' : `ETB ${actualDeliveryFee}`) : 'ETB 0 (Pickup)' }}
              </span>
            </div>

            <div v-if="discount > 0" class="flex justify-between items-center text-[#387A54] dark:text-emerald-400">
              <span class="flex items-center space-x-1.5">
                <span>Promo Discount</span>
                <span v-if="appliedPromo" class="font-mono text-[10px] bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 px-1.5 py-0.2 rounded font-bold">
                  {{ appliedPromo.promotion_name }}
                </span>
              </span>
              <span class="font-bold">- ETB {{ discount.toLocaleString() }}</span>
            </div>

            <div class="pt-3 border-t border-gray-100 dark:border-[#382318] flex justify-between items-center text-base font-black text-[#2A1810] dark:text-[#FAF3EE]">
              <span>Grand Total</span>
              <span class="text-xl font-serif text-[#DE1B54]">ETB {{ grandTotal.toLocaleString() }}</span>
            </div>
          </div>

          <!-- Coupon Box -->
          <div class="p-3 bg-[#FAF7F5] dark:bg-[#2A1810] rounded-2xl border border-gray-200 dark:border-[#382318] space-y-2">
            <div class="flex items-center justify-between text-xs">
              <span class="font-bold text-[#2A1810] dark:text-[#FAF3EE] flex items-center space-x-1">
                <span>🎟️</span>
                <span>Coupon Code</span>
              </span>
              <span v-if="appliedPromo" class="font-mono font-bold text-emerald-800 dark:text-emerald-300 text-[11px]">
                {{ appliedPromo.promotion_name }} Active
              </span>
            </div>
            <div v-if="!appliedPromo" class="flex items-center space-x-2">
              <input
                v-model="checkoutPromoInput"
                @keyup.enter="applyCheckoutPromo"
                type="text"
                placeholder="Code (e.g. KENEAN10)"
                class="flex-1 bg-white dark:bg-[#1E120B] border border-gray-200 dark:border-[#382318] rounded-xl px-3 py-1.5 text-xs font-mono uppercase text-[#2A1810] dark:text-[#FAF3EE] focus:outline-none focus:border-[#DE1B54]"
              />
              <button
                type="button"
                @click="applyCheckoutPromo"
                :disabled="checkoutPromoLoading || !checkoutPromoInput.trim()"
                class="px-3.5 py-1.5 bg-[#DE1B54] hover:bg-[#C51345] text-white text-xs font-bold rounded-xl transition cursor-pointer disabled:opacity-50 shrink-0"
              >
                {{ checkoutPromoLoading ? '...' : 'Apply' }}
              </button>
            </div>
            <div v-else class="flex items-center justify-between text-[11px] text-emerald-800 dark:text-emerald-300 font-semibold bg-white dark:bg-[#1E120B] p-2 rounded-xl border border-emerald-200 dark:border-emerald-800/50">
              <span>Savings: - ETB {{ discount.toLocaleString() }}</span>
              <button type="button" @click="removeCheckoutPromo" class="text-red-600 hover:text-red-800 text-xs font-bold cursor-pointer">
                Remove
              </button>
            </div>
            <div v-if="checkoutPromoError" class="text-[11px] text-red-600 font-medium">
              ⚠️ {{ checkoutPromoError }}
            </div>
          </div>

          <!-- Branded Addis Ababa Payment Methods -->
          <div class="space-y-3 pt-3 border-t border-gray-100 dark:border-[#382318]">
            <div class="flex items-center justify-between">
              <h3 class="text-xs font-bold uppercase tracking-wider text-[#2A1810] dark:text-[#FAF3EE]">
                Select Payment Method
              </h3>
              <span class="text-[11px] text-[#786B60] dark:text-[#C5B7AF]">Addis Ababa Gateway</span>
            </div>

            <div class="space-y-2.5">
              <!-- Telebirr -->
              <label
                class="flex items-start space-x-3 p-3.5 rounded-2xl border cursor-pointer transition relative"
                :class="paymentMethod === 'telebirr' ? 'border-[#0072CE] bg-[#0072CE]/5 dark:bg-[#0072CE]/10 shadow-2xs ring-1 ring-[#0072CE]' : 'border-gray-200 dark:border-[#382318] bg-white dark:bg-[#1E120B] hover:border-gray-300'"
              >
                <input
                  type="radio"
                  value="telebirr"
                  v-model="paymentMethod"
                  class="mt-1 text-[#0072CE] focus:ring-[#0072CE]"
                />
                <div class="flex-1">
                  <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-[#2A1810] dark:text-[#FAF3EE] flex items-center space-x-1.5">
                      <span class="w-2.5 h-2.5 rounded-full bg-[#0072CE] inline-block"></span>
                      <span>Telebirr Mobile Money</span>
                    </span>
                    <span class="text-[10px] font-bold bg-[#0072CE]/10 text-[#0072CE] px-2 py-0.5 rounded-full">
                      Ethiopia #1 Mobile
                    </span>
                  </div>
                  <div class="text-[11px] text-[#786B60] dark:text-[#C5B7AF] mt-0.5">
                    Pay seamlessly with your Ethio Telecom Telebirr account or phone number.
                  </div>
                </div>
              </label>

              <!-- CBE Birr -->
              <label
                class="flex items-start space-x-3 p-3.5 rounded-2xl border cursor-pointer transition relative"
                :class="paymentMethod === 'cbe_birr' ? 'border-[#7B2CBF] bg-[#7B2CBF]/5 dark:bg-[#7B2CBF]/10 shadow-2xs ring-1 ring-[#7B2CBF]' : 'border-gray-200 dark:border-[#382318] bg-white dark:bg-[#1E120B] hover:border-gray-300'"
              >
                <input
                  type="radio"
                  value="cbe_birr"
                  v-model="paymentMethod"
                  class="mt-1 text-[#7B2CBF] focus:ring-[#7B2CBF]"
                />
                <div class="flex-1">
                  <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-[#2A1810] dark:text-[#FAF3EE] flex items-center space-x-1.5">
                      <span class="w-2.5 h-2.5 rounded-full bg-[#7B2CBF] inline-block"></span>
                      <span>CBE Birr &amp; Mobile Banking</span>
                    </span>
                    <span class="text-[10px] font-bold bg-[#7B2CBF]/10 text-[#7B2CBF] px-2 py-0.5 rounded-full">
                      Commercial Bank
                    </span>
                  </div>
                  <div class="text-[11px] text-[#786B60] dark:text-[#C5B7AF] mt-0.5">
                    Direct instant mobile payment via Commercial Bank of Ethiopia (CBE).
                  </div>
                </div>
              </label>

              <!-- Chapa (Cards) -->
              <label
                class="flex items-start space-x-3 p-3.5 rounded-2xl border cursor-pointer transition relative"
                :class="paymentMethod === 'chapa' ? 'border-[#00A389] bg-[#00A389]/5 dark:bg-[#00A389]/10 shadow-2xs ring-1 ring-[#00A389]' : 'border-gray-200 dark:border-[#382318] bg-white dark:bg-[#1E120B] hover:border-gray-300'"
              >
                <input
                  type="radio"
                  value="chapa"
                  v-model="paymentMethod"
                  class="mt-1 text-[#00A389] focus:ring-[#00A389]"
                />
                <div class="flex-1">
                  <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-[#2A1810] dark:text-[#FAF3EE] flex items-center space-x-1.5">
                      <span class="w-2.5 h-2.5 rounded-full bg-[#00A389] inline-block"></span>
                      <span>Debit / Credit Card (Chapa)</span>
                    </span>
                    <span class="text-[10px] font-bold bg-[#00A389]/10 text-[#00A389] px-2 py-0.5 rounded-full">
                      Visa · Mastercard
                    </span>
                  </div>
                  <div class="text-[11px] text-[#786B60] dark:text-[#C5B7AF] mt-0.5">
                    Pay securely using local or international Visa, Mastercard, or debit cards.
                  </div>
                </div>
              </label>

              <!-- Cash on Delivery / Counter Pickup -->
              <label
                class="flex items-start space-x-3 p-3.5 rounded-2xl border cursor-pointer transition relative"
                :class="paymentMethod === 'cod' ? 'border-[#DE1B54] bg-[#DE1B54]/5 dark:bg-[#341822]/40 shadow-2xs ring-1 ring-[#DE1B54]' : 'border-gray-200 dark:border-[#382318] bg-white dark:bg-[#1E120B] hover:border-gray-300'"
              >
                <input
                  type="radio"
                  value="cod"
                  v-model="paymentMethod"
                  class="mt-1 text-[#DE1B54] focus:ring-[#DE1B54]"
                />
                <div class="flex-1">
                  <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-[#2A1810] dark:text-[#FAF3EE] flex items-center space-x-1.5">
                      <span class="w-2.5 h-2.5 rounded-full bg-[#DE1B54] inline-block"></span>
                      <span>{{ fulfillmentMethod === 'Pickup' ? 'Pay on Pickup (Cash / POS)' : 'Cash on Delivery' }}</span>
                    </span>
                    <span class="text-[10px] font-bold bg-gray-100 dark:bg-[#2A1810] text-[#786B60] dark:text-[#C5B7AF] px-2 py-0.5 rounded-full">
                      Zero Upfront Fee
                    </span>
                  </div>
                  <div class="text-[11px] text-[#786B60] dark:text-[#C5B7AF] mt-0.5">
                    {{ fulfillmentMethod === 'Pickup' ? 'Pay with cash or card upon collecting your order at the counter.' : 'Pay cash directly to our delivery courier at your doorstep.' }}
                  </div>
                </div>
              </label>
            </div>
          </div>

          <!-- Error Alert Banner -->
          <div v-if="orderError" class="p-3.5 bg-[#FAF0F0] dark:bg-rose-950/40 border border-[#E8C4C4] dark:border-rose-900/60 text-xs text-[#B84B42] rounded-xl flex items-start space-x-2">
            <span class="shrink-0">⚠️</span>
            <span>{{ orderError }}</span>
          </div>

          <!-- Place Order Button -->
          <button
            type="submit"
            :disabled="isSubmitting || (fulfillmentMethod === 'Delivery' && selectedZoneId === 'outside') || cartStore.hasSoldOutItems"
            class="w-full py-4 min-h-[48px] bg-[#DE1B54] hover:bg-[#C51345] disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-bold uppercase tracking-wider rounded-2xl shadow-md transition active:scale-98 cursor-pointer flex items-center justify-center space-x-2"
          >
            <span v-if="isSubmitting" class="animate-spin inline-block mr-1">⌛</span>
            <span>
              {{ isSubmitting ? 'Confirming with bakery...' : `Place Order &middot; ETB ${grandTotal.toLocaleString()} →` }}
            </span>
          </button>

          <!-- Security Badges Reassurance -->
          <div class="pt-2 border-t border-gray-100 dark:border-[#382318] flex items-center justify-center space-x-4 text-[10px] text-[#786B60] dark:text-[#C5B7AF]">
            <span class="flex items-center space-x-1">
              <span>🔒</span>
              <span>256-Bit Encrypted</span>
            </span>
            <span>&bull;</span>
            <span class="flex items-center space-x-1">
              <span>⏱️</span>
              <span>Live Order Tracking</span>
            </span>
            <span>&bull;</span>
            <span class="flex items-center space-x-1">
              <span>🍞</span>
              <span>Fresh Baked Daily</span>
            </span>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useBranchStore } from '../../stores/branch';
import { useCartStore } from '../../stores/cart';
import { useAuthStore } from '../../stores/auth';
import { useToastStore } from '../../stores/toast';
import api from '../../api';

const router = useRouter();
const branchStore = useBranchStore();
const cartStore = useCartStore();
const authStore = useAuthStore();
const toastStore = useToastStore();

// Form States
const fulfillmentMethod = ref(cartStore.fulfillmentMethod || 'Delivery');
const paymentMethod = ref('telebirr'); // Modern default: Telebirr
const baseDeliveryFee = ref(60);
const selectedZoneId = ref(null);
const activeZones = ref([]);
const branchDetails = ref(null);
const isSubmitting = ref(false);
const orderError = ref(null);
const loadingZones = ref(false);
const hasRestoredDraft = ref(false);

// Address Management
const savedAddresses = ref([]);
const addressMode = ref('saved'); // 'saved' or 'custom'
const selectedAddressId = ref(null);
const saveAddressForLater = ref(false);

const timeSlots = [
  { period: '🌅 Morning', time: '08:00 – 11:00 AM', value: 'Morning (08:00 - 11:00 AM)' },
  { period: '☀️ Midday', time: '11:00 AM – 02:00 PM', value: 'Midday (11:00 AM - 02:00 PM)' },
  { period: '🌇 Afternoon', time: '02:00 – 05:00 PM', value: 'Afternoon (02:00 - 05:00 PM)' },
  { period: '🌙 Evening', time: '05:00 – 08:00 PM', value: 'Evening (05:00 - 08:00 PM)' },
];
const selectedTimeSlot = ref('Morning (08:00 - 11:00 AM)');

const customer = ref({
  name: '',
  phone: '',
  address: '',
  notes: '',
});

// Computed Branch & Cart
const branchId = computed(() => {
  return Number(cartStore.branchId || branchStore.selectedBranchId || 2);
});

const currentBranch = computed(() => {
  const bList = branchStore.branches || [];
  return bList.find((b) => b.branch_id === branchId.value) || branchStore.selectedBranch;
});

const currentBranchName = computed(() => {
  if (currentBranch.value?.branch_name) return currentBranch.value.branch_name;
  if (branchId.value === 3) return 'Megenagna';
  if (branchId.value === 4) return 'Piassa';
  if (branchId.value === 1) return 'Main Branch';
  return 'Bole';
});

const cartItems = computed(() => cartStore.items);

// Promo code handling
const checkoutPromoInput = ref('');
const checkoutPromoLoading = ref(false);
const checkoutPromoError = ref('');

const appliedPromo = computed(() => cartStore.appliedPromo);
const discount = computed(() => cartStore.discountAmount);

const subtotal = computed(() => {
  return cartItems.value.reduce((sum, item) => sum + (Number(item.price) * Number(item.quantity)), 0);
});

const actualDeliveryFee = computed(() => {
  if (fulfillmentMethod.value !== 'Delivery') return 0;
  if (subtotal.value >= 1000) return 0; // Free delivery promotion over 1000 ETB
  return baseDeliveryFee.value;
});

const grandTotal = computed(() => {
  return Math.max(0, (subtotal.value - discount.value) + actualDeliveryFee.value);
});

// Draft Persistence Key
const DRAFT_KEY = 'sweetbakes_checkout_draft';

function saveDraftToStorage() {
  try {
    const draft = {
      name: customer.value.name,
      phone: customer.value.phone,
      address: customer.value.address,
      notes: customer.value.notes,
      fulfillmentMethod: fulfillmentMethod.value,
      selectedTimeSlot: selectedTimeSlot.value,
      selectedZoneId: selectedZoneId.value,
      paymentMethod: paymentMethod.value,
      addressMode: addressMode.value,
      selectedAddressId: selectedAddressId.value,
      saveAddressForLater: saveAddressForLater.value,
    };
    localStorage.setItem(DRAFT_KEY, JSON.stringify(draft));
  } catch (e) {
    console.warn('Could not save checkout draft', e);
  }
}

function restoreDraftFromStorage() {
  try {
    const stored = localStorage.getItem(DRAFT_KEY);
    if (!stored) return false;
    const draft = JSON.parse(stored);
    if (draft && typeof draft === 'object') {
      if (draft.name) customer.value.name = draft.name;
      if (draft.phone) customer.value.phone = draft.phone;
      if (draft.address) customer.value.address = draft.address;
      if (draft.notes) customer.value.notes = draft.notes;
      if (draft.fulfillmentMethod) fulfillmentMethod.value = draft.fulfillmentMethod;
      if (draft.selectedTimeSlot) selectedTimeSlot.value = draft.selectedTimeSlot;
      if (draft.selectedZoneId) selectedZoneId.value = draft.selectedZoneId;
      if (draft.paymentMethod) paymentMethod.value = draft.paymentMethod;
      if (draft.addressMode) addressMode.value = draft.addressMode;
      if (draft.selectedAddressId) selectedAddressId.value = draft.selectedAddressId;
      if (draft.saveAddressForLater !== undefined) saveAddressForLater.value = draft.saveAddressForLater;
      return true;
    }
  } catch (e) {
    console.warn('Could not restore checkout draft', e);
  }
  return false;
}

function clearSavedDraft() {
  localStorage.removeItem(DRAFT_KEY);
  hasRestoredDraft.value = false;
  if (authStore.user) {
    customer.value.name = authStore.user.full_name || '';
    customer.value.phone = authStore.user.phone || '';
  } else {
    customer.value.name = '';
    customer.value.phone = '';
  }
  customer.value.address = '';
  customer.value.notes = '';
  selectedAddressId.value = null;
  toastStore.info('Checkout draft cleared.');
}

// Watchers for persistent state
watch(
  [customer, fulfillmentMethod, selectedTimeSlot, selectedZoneId, paymentMethod, addressMode, selectedAddressId, saveAddressForLater],
  () => {
    saveDraftToStorage();
  },
  { deep: true }
);

function setMethod(method) {
  fulfillmentMethod.value = method;
  cartStore.fulfillmentMethod = method;
  localStorage.setItem('cart_fulfillment', method);
}

function selectSavedAddress(addr) {
  selectedAddressId.value = addr.address_id;
  customer.value.address = addr.street;
  if (addr.notes) {
    customer.value.notes = addr.notes;
  }
  // If address region matches an active zone, match it
  if (addr.region && activeZones.value.length > 0) {
    const match = activeZones.value.find((z) => z.zone_name.toLowerCase().includes(addr.region.toLowerCase()) || addr.region.toLowerCase().includes(z.zone_name.toLowerCase()));
    if (match) {
      selectedZoneId.value = match.zone_id;
      baseDeliveryFee.value = parseFloat(match.delivery_fee) || 60;
    }
  }
}

function onZoneChange() {
  if (selectedZoneId.value && selectedZoneId.value !== 'outside') {
    const zone = activeZones.value.find((z) => z.zone_id === selectedZoneId.value);
    if (zone) {
      baseDeliveryFee.value = parseFloat(zone.delivery_fee) || 60;
    }
  }
}

async function fetchUserAddresses() {
  if (!authStore.isAuthenticated) {
    savedAddresses.value = [];
    addressMode.value = 'custom';
    return;
  }
  try {
    const res = await api.get('/addresses');
    savedAddresses.value = Array.isArray(res.data) ? res.data : [];
    if (savedAddresses.value.length > 0) {
      // Default to saved address mode unless user was already in custom mode
      if (!customer.value.address) {
        const def = savedAddresses.value.find((a) => a.is_default) || savedAddresses.value[0];
        selectSavedAddress(def);
      }
    } else {
      addressMode.value = 'custom';
    }
  } catch (err) {
    console.error('Failed to load user addresses', err);
    savedAddresses.value = [];
    addressMode.value = 'custom';
  }
}

async function loadBranchAndZones(targetBranchId) {
  if (!targetBranchId) return;
  loadingZones.value = true;
  try {
    const [zoneRes, branchRes] = await Promise.all([
      api.get(`/branches/${targetBranchId}/delivery-zones`),
      api.get(`/branches/${targetBranchId}`),
    ]);

    const zones = zoneRes.data || [];
    activeZones.value = zones.filter((z) => z.status === 'Active');

    if (activeZones.value.length > 0) {
      const exists = activeZones.value.some((z) => z.zone_id === selectedZoneId.value);
      if (!exists && selectedZoneId.value !== 'outside') {
        selectedZoneId.value = activeZones.value[0].zone_id;
        baseDeliveryFee.value = parseFloat(activeZones.value[0].delivery_fee) || 60;
      }
    } else {
      selectedZoneId.value = null;
      baseDeliveryFee.value = 60;
    }

    branchDetails.value = branchRes.data;
  } catch (err) {
    console.error('Failed to load branch delivery zones or details', err);
    activeZones.value = [];
    selectedZoneId.value = null;
  } finally {
    loadingZones.value = false;
  }
}

async function recheckStock() {
  await cartStore.revalidateForBranch(branchId.value);
  toastStore.info(`Stock status verified for ${currentBranchName.value} Branch.`);
}

async function applyCheckoutPromo() {
  const code = checkoutPromoInput.value.trim().toUpperCase();
  if (!code) {
    checkoutPromoError.value = 'Please enter a valid coupon code.';
    return;
  }
  checkoutPromoLoading.value = true;
  checkoutPromoError.value = '';
  try {
    const res = await api.post('/promotions/validate', {
      code,
      subtotal: subtotal.value,
      branch_id: branchId.value,
    });
    if (res.data?.valid) {
      cartStore.setPromo(res.data);
      checkoutPromoInput.value = '';
      toastStore.success(res.data.message || `Coupon ${code} applied successfully!`);
    } else {
      checkoutPromoError.value = res.data?.message || 'Invalid promotion code.';
    }
  } catch (err) {
    checkoutPromoError.value = err.response?.data?.message || 'Invalid or expired promotion code.';
  } finally {
    checkoutPromoLoading.value = false;
  }
}

function removeCheckoutPromo() {
  cartStore.removePromo();
  toastStore.info('Promotion removed.');
}

watch(
  () => branchId.value,
  async (newId) => {
    if (newId) {
      await loadBranchAndZones(newId);
      await cartStore.revalidateForBranch(newId);
    }
  },
  { immediate: true }
);

onMounted(async () => {
  // 1. Fetch branch data if needed
  if (branchStore.branches.length === 0) {
    await branchStore.fetchBranches();
  }

  // 2. Restore Draft from LocalStorage
  const restored = restoreDraftFromStorage();
  hasRestoredDraft.value = restored;

  // 3. Populate default user info if still blank
  if (authStore.user) {
    if (!customer.value.name) customer.value.name = authStore.user.full_name || '';
    if (!customer.value.phone) customer.value.phone = authStore.user.phone || '';
  }

  // 4. Fetch Saved Addresses & Active Zones
  await Promise.all([
    fetchUserAddresses(),
    loadBranchAndZones(branchId.value),
    cartStore.revalidateForBranch(branchId.value),
  ]);
});

async function handlePlaceOrder() {
  orderError.value = null;

  if (cartItems.value.length === 0) {
    orderError.value = 'Your shopping bag is empty. Please add items before checking out.';
    return;
  }

  if (cartStore.hasSoldOutItems) {
    orderError.value = `Some items in your bag are sold out at ${currentBranchName.value} Branch. Please review your bag before checkout.`;
    return;
  }

  if (fulfillmentMethod.value === 'Delivery') {
    if (selectedZoneId.value === 'outside') {
      orderError.value = `${currentBranchName.value} branch does not deliver to addresses outside its designated coverage zone. Please select an active delivery zone or choose Shop Pickup.`;
      return;
    }
    if (!selectedZoneId.value && activeZones.value.length > 0) {
      orderError.value = 'Please select a delivery zone.';
      return;
    }
    if (!customer.value.address || !customer.value.address.trim()) {
      orderError.value = 'Please enter your specific street delivery address.';
      return;
    }
  }

  if (!customer.value.name || !customer.value.name.trim()) {
    orderError.value = 'Please provide your full contact name.';
    return;
  }

  if (!customer.value.phone || !customer.value.phone.trim()) {
    orderError.value = 'Please provide a valid Ethiopian contact phone number.';
    return;
  }

  isSubmitting.value = true;
  try {
    // Map payment methods cleanly to backend enum
    let mappedPayment = 'Chapa';
    if (paymentMethod.value === 'telebirr') {
      mappedPayment = 'Telebirr';
    } else if (paymentMethod.value === 'cbe_birr') {
      mappedPayment = 'CBE Birr';
    } else if (paymentMethod.value === 'chapa' || paymentMethod.value === 'online') {
      mappedPayment = 'Chapa';
    } else if (paymentMethod.value === 'cod') {
      mappedPayment = fulfillmentMethod.value === 'Pickup' ? 'Pay on pickup' : 'Pay on delivery';
    }

    // Save address to user profile if requested
    if (authStore.isAuthenticated && saveAddressForLater.value && customer.value.address.trim()) {
      try {
        await api.post('/addresses', {
          street: customer.value.address.trim(),
          city: 'Addis Ababa',
          region: currentBranchName.value,
          notes: customer.value.notes ? customer.value.notes.trim() : null,
          is_default: false,
        });
      } catch (saveErr) {
        console.warn('Could not background-save new address to profile', saveErr);
      }
    }

    const idempotencyKey = 'order_' + Date.now() + '_' + Math.random().toString(36).substring(2, 7);
    const targetBranch = branchId.value;

    const payload = {
      branch_id: targetBranch,
      customer_name: customer.value.name ? customer.value.name.trim() : '',
      customer_phone: customer.value.phone ? customer.value.phone.trim() : '',
      fulfillment_method: fulfillmentMethod.value,
      payment_method: mappedPayment,
      address_id: selectedAddressId.value || null,
      delivery_address: customer.value.address || (fulfillmentMethod.value === 'Pickup' ? `${currentBranchName.value} Branch Counter Pickup` : 'Addis Ababa Doorstep Delivery'),
      promo_code: appliedPromo.value?.promotion_name || null,
      order_notes: (selectedTimeSlot.value ? `[Slot: ${selectedTimeSlot.value}] ` : '') + (customer.value.notes ? customer.value.notes.trim() : ''),
      zone_id: fulfillmentMethod.value === 'Delivery' ? selectedZoneId.value : null,
      idempotency_key: idempotencyKey,
      items: cartItems.value.map((i) => ({
        product_id: i.product_id,
        quantity: i.quantity,
      })),
    };

    const res = await api.post('/orders', payload, {
      headers: {
        'Idempotency-Key': idempotencyKey,
      }
    });

    const orderNumber = res.data?.order_number || 'ORD-1001';

    // Clear Draft from storage upon successful order placement
    localStorage.removeItem(DRAFT_KEY);

    cartStore.clearCart();
    toastStore.success(`Order ${orderNumber} placed successfully with ${currentBranchName.value} Branch!`);
    router.push(`/orders/${orderNumber}`);
  } catch (err) {
    orderError.value = err.response?.data?.message || 'Failed to place order. Please review your stock or address.';
  } finally {
    isSubmitting.value = false;
  }
}
</script>
