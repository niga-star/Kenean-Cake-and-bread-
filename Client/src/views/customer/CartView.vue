<template>
  <div class="sweetbakes-container py-8 sm:py-10 space-y-8 font-sans selection:bg-[#FCE7F0] selection:text-[#DE1B54] text-[#29231F] dark:text-[#FAF3EE]">
    <!-- Header with Continue Shopping -->
    <div class="flex items-center justify-between border-b border-gray-200 dark:border-[#382318] pb-4">
      <div class="flex items-center space-x-3">
        <span class="text-3xl">🛒</span>
        <h1 class="text-2xl sm:text-3xl font-serif font-black text-[#2A1810] dark:text-[#FAF3EE]">{{ localeStore.t('your_artisan_bag') }}</h1>
      </div>

      <router-link
        to="/shop"
        class="text-xs font-bold text-[#DE1B54] hover:text-[#C51345] flex items-center space-x-1"
      >
        <span>← {{ localeStore.t('continue_shopping') }}</span>
      </router-link>
    </div>

    <!-- Active Branch Notice -->
    <div class="bg-white dark:bg-[#1E120B] border border-gray-200 dark:border-[#382318] px-5 py-3.5 rounded-2xl flex flex-col sm:flex-row items-center justify-between text-xs text-[#29231F] dark:text-[#FAF3EE] shadow-2xs gap-3">
      <div class="flex items-center space-x-2.5">
        <span class="w-2.5 h-2.5 rounded-full bg-[#387A54] animate-pulse shrink-0"></span>
        <span v-if="localeStore.isAmharic">በ <strong class="text-[#2A1810] dark:text-[#FAF3EE]">{{ localeStore.tBranch(currentBranchName) }} ቅርንጫፍ</strong> በመግዛት ላይ። የምርት ብዛት ከዚህ ቅርንጫፍ ክምችት ብቻ የተያዘ ነው።</span>
        <span v-else>Shopping at <strong class="text-[#2A1810] dark:text-[#FAF3EE]">{{ currentBranchName }} Branch</strong>. Quantities are strictly reserved from this bakery's on-hand inventory.</span>
      </div>
      <button
        type="button"
        @click="branchStore.openPicker()"
        class="font-bold text-[#DE1B54] hover:underline text-xs shrink-0 cursor-pointer"
      >
        {{ localeStore.t('change_branch') }}
      </button>
    </div>

    <!-- Free Delivery Progress Bar -->
    <div class="bg-white dark:bg-[#1E120B] rounded-2xl border border-gray-200 dark:border-[#382318] p-4 shadow-2xs space-y-2">
      <div class="flex justify-between items-center text-xs">
        <span v-if="subtotal >= 1000" class="font-bold text-[#387A54] dark:text-emerald-400 flex items-center space-x-1">
          <span>🎉</span>
          <span>{{ localeStore.t('congrats_free_delivery') }}</span>
        </span>
        <span v-else class="text-[#786B60] dark:text-[#C5B7AF]">
          <template v-if="localeStore.isAmharic">
            ነፃ ለማድረስ <strong class="text-[#2A1810] dark:text-[#FAF3EE]">{{ (1000 - subtotal).toLocaleString() }} ብር</strong> ይጨምሩ!
          </template>
          <template v-else>
            Add <strong class="text-[#2A1810] dark:text-[#FAF3EE]">ETB {{ (1000 - subtotal).toLocaleString() }}</strong> more to unlock <strong>Free Delivery</strong>!
          </template>
        </span>
        <span class="font-bold text-[#2A1810] dark:text-[#FAF3EE]">{{ Math.min(100, Math.round((subtotal / 1000) * 100)) }}%</span>
      </div>
      <div class="w-full h-2.5 bg-[#FAF7F5] dark:bg-[#2A1810] rounded-full overflow-hidden border border-gray-200 dark:border-[#382318]">
        <div
          class="h-full bg-[#DE1B54] transition-all duration-500 rounded-full"
          :style="{ width: `${Math.min(100, (subtotal / 1000) * 100)}%` }"
        ></div>
      </div>
    </div>

    <!-- Revalidation Warnings Banner -->
    <div
      v-if="cartStore.revalidationWarnings.length > 0"
      class="bg-[#FAF6EC] border-2 border-[#B98235] rounded-3xl p-5 shadow-xs space-y-3"
    >
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2 text-xs font-bold text-[#B98235]">
          <span class="text-base">⚠️</span>
          <span v-if="localeStore.isAmharic">ለ {{ localeStore.tBranch(currentBranchName) }} ቅርንጫፍ የምርት ክምችት እና ዋጋ ማረጋገጫ</span>
          <span v-else>Stock &amp; Price Revalidation for {{ currentBranchName }} Branch</span>
        </div>
        <button
          @click="cartStore.clearWarnings"
          class="text-xs font-bold text-[#B98235] hover:underline cursor-pointer"
        >
          {{ localeStore.t('dismiss_all') }}
        </button>
      </div>

      <div class="space-y-3">
        <div
          v-for="(warn, wIdx) in cartStore.revalidationWarnings"
          :key="wIdx"
          class="bg-white/95 rounded-2xl p-4 border border-[#E8D8B8] text-xs space-y-2 shadow-2xs"
        >
          <div class="flex items-start justify-between">
            <div class="text-[#2A1810] dark:text-[#FAF3EE] font-semibold leading-relaxed">
              {{ warn.message }}
            </div>
            <button
              @click="cartStore.dismissWarning(wIdx)"
              class="text-gray-400 hover:text-gray-600 text-xs ml-2 cursor-pointer"
            >
              ✕
            </button>
          </div>

          <div v-if="warn.suggestions && warn.suggestions.length > 0" class="pt-2 space-y-1.5 border-t border-[#FAF6EC] dark:border-[#382318]">
            <div class="text-[11px] font-bold text-[#DE1B54]">
              {{ localeStore.t('available_other_branches') }}
            </div>
            <div class="flex flex-wrap items-center gap-2">
              <div
                v-for="alt in warn.suggestions"
                :key="alt.branch_id"
                class="inline-flex items-center space-x-2 bg-rose-50/50 dark:bg-[#20120B] border border-rose-100 dark:border-[#382318] px-3 py-1 rounded-xl text-[11px]"
              >
                <span class="font-bold text-[#2A1810] dark:text-[#FAF3EE]">{{ localeStore.tBranch(alt.branch_name) }}</span>
                <span class="text-[#786B60] dark:text-[#A8988E]">({{ alt.available }} available &middot; {{ localeStore.formatPrice(alt.price) }})</span>
                <button
                  type="button"
                  @click="switchBranch(alt)"
                  class="px-2 py-0.5 bg-[#DE1B54] text-white rounded-lg font-bold hover:bg-[#C51345] text-[10px] transition cursor-pointer"
                >
                  <template v-if="localeStore.isAmharic">ወደ {{ localeStore.tBranch(alt.branch_name) }} ቀይር</template>
                  <template v-else>Switch to {{ alt.branch_name }}</template>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <EmptyState
      v-if="cartStore.items.length === 0"
      icon="🥐"
      :title="localeStore.t('empty_bag_checkout')"
      :description="localeStore.t('empty_bag_drawer_desc')"
      :actionLabel="localeStore.t('explore_all')"
      actionLink="/shop"
    />

    <!-- Cart Layout Grid: Items Table & Summary -->
    <div v-else class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
      <!-- Left Column: Items Table (8 cols) -->
      <div class="lg:col-span-8 space-y-6">
        <div class="bg-white dark:bg-[#1E120B] rounded-3xl border border-[#E6DED3] dark:border-[#382318] overflow-hidden shadow-2xs">
          <!-- Desktop Table Header (hidden on mobile) -->
          <div class="hidden md:grid bg-[#F8F3EA] dark:bg-[#2A1810] px-6 py-3.5 border-b border-[#E6DED3] dark:border-[#382318] grid-cols-12 text-[11px] font-bold uppercase tracking-wider text-[#786B60] dark:text-[#C5B7AF]">
            <div class="col-span-6">{{ localeStore.t('bakery_item_col') }}</div>
            <div class="col-span-2 text-center">{{ localeStore.t('unit_price_col') }}</div>
            <div class="col-span-2 text-center">{{ localeStore.t('quantity_col') }}</div>
            <div class="col-span-2 text-right">{{ localeStore.t('subtotal_col') }}</div>
          </div>

          <!-- Items Lines -->
          <div class="divide-y divide-[#E6DED3] dark:divide-[#382318]">
            <div
              v-for="item in cartStore.items"
              :key="item.product_id"
              class="p-4 sm:p-6 text-xs"
              :class="(item.available_stock ?? item.available ?? 1) <= 0 ? 'bg-[#FAF0F0]/50 dark:bg-rose-950/20' : ''"
            >
              <!-- Mobile Card Layout (< 768px) -->
              <div class="md:hidden flex flex-col space-y-3">
                <div class="flex items-start justify-between space-x-3">
                  <div class="flex items-center space-x-3">
                    <img
                      :src="item.image_url || 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=160'"
                      :alt="item.product_name"
                      loading="lazy"
                      decoding="async"
                      class="w-16 h-16 rounded-2xl object-cover shrink-0 border border-[#E6DED3] dark:border-[#382318] bg-[#E8D8C4]/30"
                    />
                    <div>
                      <h3 class="font-product font-bold text-sm text-[#29231F] dark:text-[#FAF3EE]">{{ localeStore.tProduct(item.product_name) }}</h3>
                      <div class="text-xs font-bold text-[#A87552] dark:text-rose-400 mt-0.5">
                        {{ localeStore.formatPrice(item.price) }}
                      </div>
                      <div v-if="(item.available_stock ?? item.available ?? 1) > 0" class="text-[11px] text-[#387A54] dark:text-emerald-400 font-medium">
                        ✓ {{ localeStore.isAmharic ? `${item.available_stock ?? item.available} በክምችት አለ` : `In Stock (${item.available_stock ?? item.available})` }}
                      </div>
                      <div v-else class="text-[11px] text-[#B84B42] font-bold">
                        <template v-if="localeStore.isAmharic">በ {{ localeStore.tBranch(currentBranchName) }} አልቋል</template>
                        <template v-else>Sold out at {{ currentBranchName }}</template>
                      </div>
                    </div>
                  </div>

                  <button
                    @click="cartStore.removeItem(item.product_id)"
                    class="text-[#786B60] dark:text-gray-400 hover:text-[#B84B42] text-sm p-1.5 cursor-pointer"
                    :title="localeStore.t('remove_item')"
                  >
                    ✕
                  </button>
                </div>

                <div class="flex items-center justify-between pt-1 border-t border-gray-100 dark:border-[#2A1810]">
                  <!-- Quantity Stepper -->
                  <div v-if="(item.available_stock ?? item.available ?? 1) > 0" class="flex items-center border border-gray-200 dark:border-[#382318] rounded-xl bg-white dark:bg-[#170E0A] shadow-2xs">
                    <button
                      @click="decreaseQty(item)"
                      class="px-3 py-1.5 text-xs font-bold text-[#2A1810] dark:text-[#FAF3EE] hover:bg-rose-50 dark:hover:bg-[#2A1810] rounded-l-xl cursor-pointer"
                    >
                      &minus;
                    </button>
                    <span class="px-3 py-1.5 text-xs font-bold text-[#2A1810] dark:text-[#FAF3EE] min-w-[28px] text-center">
                      {{ item.quantity }}
                    </span>
                    <button
                      @click="increaseQty(item)"
                      class="px-3 py-1.5 text-xs font-bold text-[#2A1810] dark:text-[#FAF3EE] hover:bg-rose-50 dark:hover:bg-[#2A1810] rounded-r-xl disabled:opacity-40 cursor-pointer"
                      :disabled="item.quantity >= (item.available_stock ?? item.available ?? 999)"
                    >
                      +
                    </button>
                  </div>
                  <div v-else class="text-[11px] font-bold text-[#B84B42]">{{ localeStore.t('sold_out_badge') }}</div>

                  <div class="text-right">
                    <span class="text-[10px] text-gray-500 dark:text-gray-400 block">{{ localeStore.t('subtotal_col') }}:</span>
                    <span class="font-black text-[#2A1810] dark:text-[#FAF3EE] text-sm">
                      {{ localeStore.formatPrice(item.price * item.quantity) }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Desktop Row Layout (>= 768px) -->
              <div class="hidden md:grid grid-cols-12 items-center gap-3">
                <!-- Product info (6 cols) -->
                <div class="col-span-6 flex items-center space-x-3.5">
                  <img
                    :src="item.image_url || 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=160'"
                    :alt="item.product_name"
                    loading="lazy"
                    decoding="async"
                    class="w-16 h-16 rounded-2xl object-cover shrink-0 border border-gray-200 dark:border-[#382318] bg-[#E8D8C4]/30"
                    :class="(item.available_stock ?? item.available ?? 1) <= 0 ? 'grayscale-50' : ''"
                  />
                  <div>
                    <h3 class="font-product font-bold text-sm text-[#2A1810] dark:text-[#FAF3EE]">{{ localeStore.tProduct(item.product_name) }}</h3>
                    <div v-if="(item.available_stock ?? item.available ?? 1) > 0" class="text-[11px] text-[#387A54] dark:text-emerald-400 font-medium mt-0.5">
                      ✓ {{ localeStore.isAmharic ? `${item.available_stock ?? item.available} በክምችት አለ` : `Available (${item.available_stock ?? item.available} in stock)` }}
                    </div>
                    <div v-else class="text-[11px] text-[#B84B42] font-bold mt-0.5">
                      <template v-if="localeStore.isAmharic">በ {{ localeStore.tBranch(currentBranchName) }} አልቋል</template>
                      <template v-else>Sold out at {{ currentBranchName }}</template>
                    </div>
                  </div>
                </div>

                <!-- Price (2 cols) -->
                <div class="col-span-2 text-center font-bold text-[#2A1810] dark:text-rose-400">
                  {{ localeStore.formatPrice(item.price) }}
                </div>

                <!-- Quantity Stepper (2 cols) -->
                <div class="col-span-2 flex items-center justify-center">
                  <div v-if="(item.available_stock ?? item.available ?? 1) > 0" class="flex items-center border border-gray-200 dark:border-[#382318] rounded-xl bg-white dark:bg-[#170E0A] shadow-2xs">
                    <button
                      @click="decreaseQty(item)"
                      class="px-2.5 py-1 text-xs font-bold text-[#2A1810] dark:text-[#FAF3EE] hover:bg-rose-50 dark:hover:bg-[#2A1810] rounded-l-xl cursor-pointer"
                    >
                      &minus;
                    </button>
                    <span class="px-2.5 py-1 text-xs font-bold text-[#2A1810] dark:text-[#FAF3EE] min-w-[28px] text-center">
                      {{ item.quantity }}
                    </span>
                    <button
                      @click="increaseQty(item)"
                      class="px-2.5 py-1 text-xs font-bold text-[#2A1810] dark:text-[#FAF3EE] hover:bg-rose-50 dark:hover:bg-[#2A1810] rounded-r-xl disabled:opacity-40 cursor-pointer"
                      :disabled="item.quantity >= (item.available_stock ?? item.available ?? 999)"
                    >
                      +
                    </button>
                  </div>
                  <div v-else class="text-[11px] font-bold text-[#B84B42]">
                    {{ localeStore.t('sold_out_badge') }}
                  </div>
                </div>

                <!-- Total & Trash Icon (2 cols) -->
                <div class="col-span-2 flex items-center justify-end space-x-3">
                  <span class="font-black text-[#2A1810] dark:text-[#FAF3EE] text-sm">
                    {{ localeStore.formatPrice(item.price * item.quantity) }}
                  </span>
                  <button
                    @click="cartStore.removeItem(item.product_id)"
                    class="text-[#786B60] dark:text-gray-400 hover:text-[#B84B42] transition text-sm cursor-pointer p-1"
                    :title="localeStore.t('remove_item')"
                  >
                    ✕
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Dynamic Database Promotions Engine Card -->
        <div class="relative rounded-3xl bg-gradient-to-r from-[#FFF5F2] via-[#FDF3EE] to-[#FCEEE8] dark:from-[#25150E] dark:via-[#20110A] dark:to-[#170E0A] border border-rose-200/70 dark:border-[#382318] p-5 sm:p-6 shadow-xs space-y-4">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center space-x-3.5">
              <span class="text-3xl">🎁</span>
              <div>
                <h4 class="font-serif font-black text-base text-[#2A1810] dark:text-[#FAF3EE]">
                  {{ localeStore.t('loyalty_coupons_title') }}
                </h4>
                <p class="text-xs text-[#786B60] dark:text-[#C5B7AF] mt-0.5">
                  {{ localeStore.t('loyalty_coupons_desc') }}
                </p>
              </div>
            </div>

            <!-- Active Promo Status or Input Form -->
            <div v-if="appliedPromo" class="flex items-center space-x-2 bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-300 dark:border-emerald-700/60 rounded-2xl px-4 py-2 shrink-0">
              <div class="text-xs">
                <span class="font-mono font-bold text-emerald-900 dark:text-emerald-300 uppercase">{{ appliedPromo.promotion_name }}</span>
                <span class="text-emerald-700 dark:text-emerald-400 ml-1.5 font-semibold">
                  ({{ appliedPromo.discount_type === 'percentage' ? `${appliedPromo.discount_value}% OFF` : `${localeStore.formatPrice(appliedPromo.discount_value)} OFF` }})
                </span>
              </div>
              <button
                @click="handleRemovePromo"
                class="ml-2 text-xs font-bold text-red-600 hover:text-red-800 p-1 rounded hover:bg-red-50 dark:hover:bg-red-950/40 cursor-pointer"
                :title="localeStore.t('remove_btn')"
              >
                ✕
              </button>
            </div>
          </div>

          <!-- Promo Input Form -->
          <div v-if="!appliedPromo" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 pt-2 border-t border-rose-200/50 dark:border-[#382318]">
            <div class="relative flex-1">
              <input
                v-model="promoInput"
                @keyup.enter="handleApplyPromo()"
                type="text"
                :placeholder="localeStore.t('enter_promo_placeholder')"
                class="w-full bg-white dark:bg-[#1E120B] border border-gray-200 dark:border-[#382318] rounded-xl px-3.5 py-2 text-xs font-mono font-semibold uppercase text-[#29231F] dark:text-[#FAF3EE] placeholder:normal-case placeholder:font-sans placeholder:font-normal focus:outline-none focus:border-[#DE1B54]"
              />
            </div>
            <button
              @click="handleApplyPromo()"
              :disabled="promoLoading || !promoInput.trim()"
              class="px-5 py-2.5 min-h-[38px] bg-[#DE1B54] hover:bg-[#C51345] disabled:opacity-50 text-white text-xs font-bold rounded-xl transition shadow-xs cursor-pointer shrink-0 active:scale-95"
            >
              {{ promoLoading ? localeStore.t('verifying_coupon') : localeStore.t('apply_coupon_btn') }}
            </button>
          </div>

          <div v-if="promoError" class="text-xs text-red-600 font-medium">
            ⚠️ {{ promoError }}
          </div>

          <!-- Quick Promo Suggestions -->
          <div v-if="!appliedPromo" class="flex flex-wrap items-center gap-2 pt-1 text-xs">
            <span class="text-[11px] text-[#786B60] dark:text-[#C5B7AF] font-semibold">{{ localeStore.t('available_specials') }}</span>
            <button
              @click="handleApplyPromo('KENEAN10')"
              class="px-2.5 py-1 bg-white dark:bg-[#1E120B] hover:bg-rose-50 dark:hover:bg-[#341822] border border-rose-200 dark:border-[#382318] text-[#DE1B54] rounded-lg font-mono text-[11px] font-bold shadow-2xs cursor-pointer transition"
            >
              KENEAN10 (10% Off)
            </button>
            <button
              @click="handleApplyPromo('YETSOM15')"
              class="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/60 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 border border-emerald-300 dark:border-emerald-700/60 text-emerald-900 dark:text-emerald-300 rounded-lg font-mono text-[11px] font-bold shadow-2xs cursor-pointer transition"
            >
              🌿 YETSOM15 (15% Off)
            </button>
            <button
              @click="handleApplyPromo('WELCOME50')"
              class="px-2.5 py-1 bg-white dark:bg-[#1E120B] hover:bg-rose-50 dark:hover:bg-[#341822] border border-rose-200 dark:border-[#382318] text-[#DE1B54] rounded-lg font-mono text-[11px] font-bold shadow-2xs cursor-pointer transition"
            >
              WELCOME50 ({{ localeStore.isAmharic ? '50 ብር ቅናሽ' : 'ETB 50 Off' }})
            </button>
          </div>
        </div>
      </div>

      <!-- Right Column: Order Summary (4 cols) -->
      <div class="lg:col-span-4 bg-white dark:bg-[#1E120B] rounded-3xl border border-gray-200 dark:border-[#382318] p-5 sm:p-6 space-y-5 shadow-2xs">
        <h2 class="text-base font-serif font-bold text-[#2A1810] dark:text-[#FAF3EE] border-b border-gray-100 dark:border-[#382318] pb-3">
          {{ localeStore.t('order_summary') }}
        </h2>

        <div class="space-y-3 text-xs">
          <div class="flex justify-between items-center text-[#786B60] dark:text-[#C5B7AF]">
            <span>{{ localeStore.t('items_subtotal') }}</span>
            <span class="font-bold text-[#29231F] dark:text-[#FAF3EE]">{{ localeStore.formatPrice(subtotal) }}</span>
          </div>

          <div class="flex justify-between items-center text-[#786B60] dark:text-[#C5B7AF]">
            <span>{{ localeStore.t('delivery_fee') }}</span>
            <span class="font-bold" :class="deliveryFee === 0 ? 'text-[#387A54] dark:text-emerald-400' : 'text-[#29231F] dark:text-[#FAF3EE]'">
              {{ deliveryFee === 0 ? localeStore.t('free_label') : localeStore.formatPrice(deliveryFee) }}
            </span>
          </div>

          <div v-if="discount > 0" class="flex justify-between items-center text-[#387A54] dark:text-emerald-400">
            <span class="flex items-center space-x-1">
              <span>{{ localeStore.t('promo_discount') }}</span>
              <span v-if="appliedPromo" class="font-mono text-[10px] bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 px-1.5 py-0.2 rounded font-bold">
                {{ appliedPromo.promotion_name }}
              </span>
            </span>
            <span class="font-bold">- {{ localeStore.formatPrice(discount) }}</span>
          </div>

          <div class="pt-3 border-t border-gray-100 dark:border-[#382318] flex justify-between items-center text-base font-black text-[#2A1810] dark:text-[#FAF3EE]">
            <span>{{ localeStore.t('grand_total') }}</span>
            <span class="text-xl font-serif text-[#DE1B54]">{{ localeStore.formatPrice(total) }}</span>
          </div>
        </div>

        <div class="space-y-2.5 pt-2">
          <!-- Branch Shopping Unavailable in Cart -->
          <div v-if="!branchStore.isCurrentBranchShoppingEnabled" class="p-3.5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 rounded-2xl text-xs text-[#B84B42] dark:text-rose-400 font-semibold space-y-1.5">
            <div class="flex items-center space-x-2">
              <span>⚠️</span>
              <span>{{ localeStore.isAmharic ? 'ይቅርታ፣ ይህ ቅርንጫፍ በአሁኑ ጊዜ ለግዢ ዝግ ነው።' : 'Sorry, this branch is currently unavailable for shopping.' }}</span>
            </div>
            <button
              @click="branchStore.openPicker()"
              class="w-full mt-1.5 py-2 bg-[#DE1B54] hover:bg-[#C51345] text-white text-xs font-bold rounded-xl transition cursor-pointer"
            >
              {{ localeStore.isAmharic ? 'ወደ ሌላ ቅርንጫፍ ቀይር' : 'Switch to an Active Branch' }} &rarr;
            </button>
          </div>

          <!-- Checkout button: disabled if any sold out item present -->
          <div v-if="cartStore.hasSoldOutItems" class="space-y-2">
            <button
              @click="removeSoldOutItems"
              class="w-full py-2.5 bg-[#FAF0F0] dark:bg-rose-950/40 border border-[#E8C4C4] dark:border-rose-900/60 text-[#B84B42] hover:bg-[#B84B42] hover:text-white text-xs font-bold rounded-2xl transition cursor-pointer"
            >
              {{ localeStore.t('clear_sold_out_btn') }}
            </button>
            <button
              disabled
              class="block w-full py-3.5 bg-gray-200 dark:bg-gray-800 text-gray-500 text-xs font-bold uppercase tracking-wider rounded-2xl text-center cursor-not-allowed"
            >
              {{ localeStore.t('remove_sold_out_notice') }}
            </button>
          </div>
          <button
            v-else
            @click="handleProceedToCheckout"
            :disabled="!branchStore.isCurrentBranchShoppingEnabled"
            class="block w-full py-4 min-h-[50px] bg-[#DE1B54] hover:bg-[#C51345] disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs sm:text-sm font-bold uppercase tracking-wider rounded-2xl text-center shadow-md hover:shadow-lg transition active:scale-98 cursor-pointer"
          >
            {{ localeStore.isAmharic ? 'ትዕዛዝ ያጠናቁ' : 'PROCEED TO CHECKOUT' }} →
          </button>

          <router-link
            to="/shop"
            class="block w-full py-3.5 min-h-[46px] bg-white dark:bg-[#1E120B] border border-gray-200 dark:border-[#382318] hover:bg-rose-50 dark:hover:bg-[#341822] text-[#2A1810] dark:text-[#FAF3EE] hover:text-[#DE1B54] text-xs sm:text-sm font-bold rounded-2xl text-center shadow-xs transition"
          >
            {{ localeStore.t('add_more_items') }}
          </router-link>
        </div>

        <div class="pt-2 text-[11px] text-[#786B60] dark:text-[#C5B7AF] text-center flex items-center justify-center space-x-1.5">
          <span>🔒</span>
          <span>{{ localeStore.isAmharic ? 'በቴሌብር፣ በቻፓ እና በጥሬ ገንዘብ የተጠበቀ ክፍያ' : 'Encrypted telebirr, Chapa & cash checkout' }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import EmptyState from '../../components/EmptyState.vue';
import api from '../../api';
import { useBranchStore } from '../../stores/branch';
import { useCartStore } from '../../stores/cart';
import { useAuthStore } from '../../stores/auth';
import { useToastStore } from '../../stores/toast';
import { useLocaleStore } from '../../stores/locale';

const router = useRouter();
const branchStore = useBranchStore();
const cartStore = useCartStore();
const authStore = useAuthStore();
const toastStore = useToastStore();
const localeStore = useLocaleStore();

function handleProceedToCheckout() {
  if (!branchStore.isCurrentBranchShoppingEnabled) {
    toastStore.warning(localeStore.isAmharic 
      ? 'ይቅርታ፣ ይህ ቅርንጫፍ በአሁኑ ጊዜ ለግዢ ዝግ ነው።' 
      : 'Sorry, this branch is currently unavailable for shopping.');
    return;
  }
  if (!authStore.isAuthenticated) {
    toastStore.info(localeStore.isAmharic 
      ? 'ወደ ክፍያ ለመቀጠል እባክዎ ይግቡ ወይም ይመዝገቡ።' 
      : 'Please sign in or create an account to proceed to checkout.');
    router.push({ path: '/login', query: { redirect: '/checkout' } });
    return;
  }
  router.push('/checkout');
}

const promoInput = ref('');
const promoLoading = ref(false);
const promoError = ref('');

const appliedPromo = computed(() => cartStore.appliedPromo);

const currentBranchName = computed(() => branchStore.selectedBranch?.branch_name || 'Bole');

onMounted(async () => {
  if (cartStore.items.length > 0) {
    await cartStore.revalidateForBranch(branchStore.selectedBranchId || 2);
  }
});

const subtotal = computed(() => {
  return cartStore.items.reduce((sum, item) => sum + (Number(item.price) * Number(item.quantity)), 0);
});

const deliveryFee = computed(() => {
  return subtotal.value >= 1000 || subtotal.value === 0 ? 0 : 60;
});

const discount = computed(() => {
  return cartStore.discountAmount;
});

const total = computed(() => {
  return Math.max(0, subtotal.value + deliveryFee.value - discount.value);
});

function increaseQty(item) {
  const res = cartStore.updateQuantity(item.product_id, item.quantity + 1);
  if (res && !res.success && res.message) {
    toastStore.error(res.message);
  }
}

function decreaseQty(item) {
  cartStore.updateQuantity(item.product_id, item.quantity - 1);
}

function removeSoldOutItems() {
  cartStore.items = cartStore.items.filter(i => !i.isSoldOut && (i.available || 0) > 0);
  cartStore.save();
  toastStore.info('Sold-out items cleared from your bag.');
}

function switchBranch(alt) {
  branchStore.setSelectedBranch(alt.branch_id);
  toastStore.info(`Switched shopping branch to ${alt.branch_name}.`);
}

async function handleApplyPromo(codeToApply = null) {
  const code = (codeToApply || promoInput.value).trim().toUpperCase();
  if (!code) {
    promoError.value = 'Please enter a coupon code.';
    return;
  }
  promoLoading.value = true;
  promoError.value = '';
  try {
    const res = await api.post('/promotions/validate', {
      code,
      subtotal: subtotal.value,
      branch_id: branchStore.selectedBranchId || cartStore.branchId || 2,
    });
    if (res.data?.valid) {
      cartStore.setPromo(res.data);
      promoInput.value = '';
      toastStore.success(res.data.message || `Coupon ${code} applied successfully!`);
    } else {
      promoError.value = res.data?.message || 'Invalid promotion code.';
    }
  } catch (err) {
    promoError.value = err.response?.data?.message || 'Invalid or expired promotion code.';
  } finally {
    promoLoading.value = false;
  }
}

function handleRemovePromo() {
  cartStore.removePromo();
  toastStore.info('Promotion removed.');
}
</script>
