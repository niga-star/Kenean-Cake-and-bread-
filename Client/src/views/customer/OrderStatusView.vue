<template>
  <div class="sweetbakes-container py-8 sm:py-10 space-y-8 font-sans selection:bg-[#FCE7F0] selection:text-[#DE1B54] text-[#29231F] dark:text-[#FAF3EE]">
    <!-- Loading State -->
    <div v-if="loadingOrder" class="py-20 text-center space-y-3">
      <div class="text-3xl animate-bounce">🧁</div>
      <div class="font-serif font-bold text-base text-[#2A1810] dark:text-[#FAF3EE]">{{ localeStore.isAmharic ? 'የትዕዛዝ ማረጋገጫ በመጫን ላይ...' : 'Loading Order Confirmation...' }}</div>
    </div>

    <!-- Clean Empty State when Order Not Found -->
    <div v-else-if="!order" class="text-center py-20 px-4 bg-white dark:bg-[#1E120B] rounded-3xl border border-gray-200 dark:border-[#382318] max-w-lg mx-auto space-y-4">
      <div class="text-4xl">🔍</div>
      <h2 class="font-serif font-black text-2xl text-[#2A1810] dark:text-[#FAF3EE]">{{ localeStore.t('order_not_found_title') }}</h2>
      <p class="text-xs sm:text-sm text-[#786B60] dark:text-[#C5B7AF]">
        {{ localeStore.t('order_not_found_desc') }}
      </p>
      <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3">
        <router-link
          to="/shop"
          class="px-6 py-2.5 bg-[#DE1B54] hover:bg-[#C51345] text-white text-xs font-bold rounded-xl shadow-xs transition"
        >
          {{ localeStore.t('browse_shop_btn') }}
        </router-link>
        <router-link
          to="/account"
          class="px-6 py-2.5 bg-white dark:bg-[#1E120B] border border-gray-200 dark:border-[#382318] text-[#2A1810] dark:text-[#FAF3EE] text-xs font-bold rounded-xl shadow-xs transition"
        >
          {{ localeStore.t('my_account_orders') }}
        </router-link>
      </div>
    </div>

    <!-- Loaded Real Order Content (Interactive Web View) -->
    <div v-else class="space-y-8 print:hidden">
      <!-- Status Alert Header Banner -->
      <div
        class="p-5 rounded-3xl text-xs space-y-1.5 shadow-2xs border transition"
        :class="{
          'bg-[#387A54]/10 border-[#387A54]/30 text-[#387A54] dark:text-emerald-400': order.order_status === 'Confirmed' || order.order_status === 'Completed',
          'bg-[#B98235]/10 border-[#B98235]/30 text-[#B98235] dark:text-amber-400': order.order_status === 'Pending',
          'bg-[#DE1B54]/10 border-[#DE1B54]/30 text-[#DE1B54]': order.order_status === 'Preparing',
          'bg-[#2A1810]/10 border-[#2A1810]/30 text-[#2A1810] dark:text-[#FAF3EE]': order.order_status === 'Out for delivery' || order.order_status === 'Ready',
          'bg-[#B84B42]/10 border-[#B84B42]/30 text-[#B84B42]': order.order_status === 'Cancelled'
        }"
      >
        <div class="font-bold text-sm sm:text-base flex items-center space-x-2">
          <span>{{ statusIcon }}</span>
          <span>{{ statusHeadline }}</span>
        </div>
        <div class="text-xs text-[#786B60] dark:text-[#C5B7AF]">
          {{ localeStore.isAmharic ? 'ትዕዛዙ በቀጥታ የሚዘጋጀው በ፡' : 'Fulfilling bakery:' }} <strong class="text-[#2A1810] dark:text-[#FAF3EE]">{{ localeStore.tBranch(order.branch?.branch_name || 'Bole') }} ({{ currentBranchCode }})</strong>. {{ localeStore.isAmharic ? 'ምርቱ ከዚህ ቅርንጫፍ ተዘጋጅቶ ይላካል።' : 'Stock was reserved and items are fulfilled directly from this kitchen.' }}
        </div>
      </div>

      <!-- Order Header Row -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-200 dark:border-[#382318] pb-6">
        <div>
          <div class="text-[11px] font-bold uppercase tracking-widest text-[#786B60] dark:text-[#C5B7AF]">{{ localeStore.t('order_conf_title') }}</div>
          <h1 class="text-2xl sm:text-4xl font-serif font-bold text-[#2A1810] dark:text-[#FAF3EE] mt-0.5">
            {{ order.order_number }}
          </h1>
          <div class="text-xs text-[#786B60] dark:text-[#C5B7AF] mt-1">
            {{ localeStore.t('placed_on_label') }} {{ orderDateFormatted }} &middot; {{ localeStore.t('est_fulfillment_time') }}
          </div>
        </div>

        <div class="flex items-center space-x-3">
          <span
            class="px-4 py-1.5 rounded-full text-xs font-bold capitalize shadow-2xs"
            :class="statusBadgeClass(order.order_status || 'Pending')"
          >
            {{ order.order_status || 'Pending' }}
          </span>

          <button
            @click="printReceipt"
            class="px-4 py-2 min-h-[40px] bg-white dark:bg-[#1E120B] border border-gray-200 dark:border-[#382318] hover:border-[#DE1B54] hover:text-[#DE1B54] text-xs font-bold text-[#2A1810] dark:text-[#FAF3EE] rounded-xl transition shadow-2xs flex items-center space-x-1.5 cursor-pointer"
          >
            <span>🖨️</span>
            <span>{{ localeStore.t('print_receipt_btn') }}</span>
          </button>
        </div>
      </div>

      <!-- Simulated Test Payment Success Alert -->
      <div
        v-if="order.payment?.payment_status === 'PAID_TEST' || order.payment?.is_test"
        class="p-4 bg-purple-50 dark:bg-purple-950/40 border-2 border-purple-200 dark:border-purple-800 rounded-3xl text-xs text-purple-900 dark:text-purple-200 space-y-1.5 shadow-2xs"
      >
        <div class="font-bold flex items-center justify-between">
          <span class="flex items-center space-x-2">
            <span class="text-base">🧪</span>
            <span class="text-sm font-serif font-black">{{ localeStore.isAmharic ? 'የተሳካ የሙከራ ክፍያ (TEST MODE)' : 'Simulated Payment Successful (TEST MODE)' }}</span>
          </span>
          <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-purple-200 dark:bg-purple-900 text-purple-800 dark:text-purple-200 border border-purple-300">
            PAID (TEST)
          </span>
        </div>
        <p class="text-[11px] leading-relaxed text-purple-800 dark:text-purple-300">
          {{ localeStore.isAmharic
            ? `ይህ ክፍያ በ${order.payment?.payment_method || 'ቴስት'} በኩል የተመሰለ የሙከራ ክፍያ ነው። እውነተኛ ገንዘብ አልተከፈለም። ማጣቀሻ፡ ${order.payment?.transaction_reference || 'TEST-TX'}`
            : `This payment was successfully simulated via ${order.payment?.payment_method || 'Test Gateway'} in sandbox mode. No real money was charged. Reference: ${order.payment?.transaction_reference || 'TEST-TX'}`
          }}
        </p>
      </div>

      <!-- Cash on Pickup Instruction Alert -->
      <div
        v-else-if="order.payment?.payment_method === 'CASH_ON_PICKUP' || order.payment?.payment_method === 'Pay on pickup'"
        class="p-4 bg-amber-50 dark:bg-amber-950/40 border-2 border-amber-200 dark:border-amber-800 rounded-3xl text-xs text-amber-900 dark:text-amber-200 space-y-1.5 shadow-2xs"
      >
        <div class="font-bold flex items-center justify-between">
          <span class="flex items-center space-x-2">
            <span class="text-base">🛍️</span>
            <span class="text-sm font-serif font-black">{{ localeStore.isAmharic ? 'በቅርንጫፍ ሲረከቡ የሚከፈል (Cash on Pickup)' : 'Cash on Pickup · Payment Instructions' }}</span>
          </span>
          <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-200 dark:bg-amber-900 text-amber-800 dark:text-amber-200 border border-amber-300">
            PAYMENT PENDING
          </span>
        </div>
        <p class="text-[11px] leading-relaxed text-amber-800 dark:text-amber-300">
          {{ localeStore.isAmharic
            ? `ትዕዛዝዎ ተመዝግቧል! እባክዎ ምርቱን ከ${localeStore.tBranch(order.branch?.branch_name || 'ቅርንጫፍ')} ዴስክ ሲረከቡ ጠቅላላ ${Number(order.total_amount || 0).toLocaleString()} ብር በጥሬ ገንዘብ ወይም በፖስ ካርድ ይክፈሉ።`
            : `Your order is placed! Please pay the total amount of ETB ${Number(order.total_amount || 0).toLocaleString()} in cash or POS card when collecting your order at ${order.branch?.branch_name || 'the bakery counter'}.`
          }}
        </p>
      </div>

    <!-- Main Content 2-Column Grid -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-start">
      <!-- Left Column: Stepper Timeline (5 cols) -->
      <div class="md:col-span-5 bg-white rounded-3xl border border-[#E6DED3] p-6 sm:p-7 shadow-xs space-y-6">
        <div class="flex items-center justify-between border-b border-[#E6DED3] pb-3">
          <h2 class="text-base font-serif font-bold text-[#29231F]">{{ localeStore.t('prep_progress_title') }}</h2>
          <span class="text-[11px] text-[#A87552] font-bold">{{ localeStore.t('live_status_tag') }}</span>
        </div>

        <!-- Vertical Stepper Steps -->
        <div class="relative space-y-7 pl-2">
          <!-- Step 1: Order Placed -->
          <div class="flex items-start space-x-3.5 relative">
            <div class="w-8 h-8 rounded-full bg-[#387A54] text-white flex items-center justify-center text-xs font-bold shrink-0 z-10 shadow-xs">
              ✓
            </div>
            <div>
              <div class="text-xs font-bold text-[#29231F]">1 &middot; {{ localeStore.isAmharic ? 'ትዕዛዝ ተቀብለናል' : 'Order Placed' }}</div>
              <div class="text-[11px] text-[#786B60]">{{ localeStore.isAmharic ? `ክምችት ተይዟል በ ${localeStore.tBranch(order?.branch?.branch_name || 'Bole')}` : `Stock reserved at ${order?.branch?.branch_name || 'Bole'}` }}</div>
            </div>
            <!-- Connecting Line -->
            <div
              class="absolute left-4 top-8 w-0.5 h-8"
              :class="isStepActiveOrDone(2) ? 'bg-[#387A54]' : 'bg-[#E6DED3]'"
            ></div>
          </div>

          <!-- Step 2: Confirmed by Branch -->
          <div class="flex items-start space-x-3.5 relative">
            <div
              class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shrink-0 z-10 shadow-xs transition"
              :class="isStepActiveOrDone(2) ? 'bg-[#387A54] text-white' : 'bg-[#F8F3EA] text-[#786B60] border border-[#E6DED3]'"
            >
              {{ isStepActiveOrDone(2) ? '✓' : '2' }}
            </div>
            <div>
              <div class="text-xs font-bold text-[#29231F]">2 &middot; {{ localeStore.isAmharic ? 'በቅርንጫፉ ተረጋግጧል' : 'Confirmed by Branch' }}</div>
              <div class="text-[11px] text-[#786B60]">
                {{ isStepActiveOrDone(2) ? (localeStore.isAmharic ? 'ክፍያ ተረጋግጦ ትዕዛዙ ተቀባይነት አግኝቷል' : 'Payment verified & order accepted') : (localeStore.isAmharic ? 'የዳቦ ቤቱን ማረጋገጫ በመጠባበቅ ላይ' : 'Awaiting kitchen confirmation') }}
              </div>
            </div>
            <div
              class="absolute left-4 top-8 w-0.5 h-8"
              :class="isStepActiveOrDone(3) ? 'bg-[#387A54]' : 'bg-[#E6DED3]'"
            ></div>
          </div>

          <!-- Step 3: Preparing -->
          <div class="flex items-start space-x-3.5 relative">
            <div
              class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shrink-0 z-10 shadow-xs transition"
              :class="isStepActiveOrDone(3) ? (isStepActiveOrDone(4) ? 'bg-[#387A54] text-white' : 'bg-[#A87552] text-white') : 'bg-[#F8F3EA] text-[#786B60] border border-[#E6DED3]'"
            >
              {{ isStepActiveOrDone(4) ? '✓' : '3' }}
            </div>
            <div>
              <div class="text-xs font-bold text-[#29231F]">3 &middot; {{ localeStore.isAmharic ? 'በመጋገር እና ማሸግ ላይ' : 'Artisan Baking & Packing' }}</div>
              <div class="text-[11px] text-[#786B60]">{{ localeStore.isAmharic ? 'ማስዋብ እና ማዘጋጀት ላይ' : 'Ovens firing, decorating, and boxing' }}</div>
            </div>
            <div
              class="absolute left-4 top-8 w-0.5 h-8"
              :class="isStepActiveOrDone(4) ? 'bg-[#387A54]' : 'bg-[#E6DED3]'"
            ></div>
          </div>

          <!-- Step 4: Out for Delivery / Ready -->
          <div class="flex items-start space-x-3.5 relative">
            <div
              class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shrink-0 z-10 shadow-xs transition"
              :class="isStepActiveOrDone(4) ? (isStepActiveOrDone(5) ? 'bg-[#387A54] text-white' : 'bg-[#DE1B54] text-white') : 'bg-rose-50 text-[#786B60] border border-gray-200'"
            >
              {{ isStepActiveOrDone(5) ? '✓' : '4' }}
            </div>
            <div>
              <div class="text-xs font-bold text-[#29231F]">
                4 &middot; {{ order?.fulfillment_method === 'Pickup' ? (localeStore.isAmharic ? 'ለመውሰድ ዝግጁ ነው' : 'Ready for Pickup') : (localeStore.isAmharic ? 'በማድረስ ላይ ነው' : 'Dispatched / Out for Delivery') }}
              </div>
              <div class="text-[11px] text-[#786B60]">
                {{ order?.fulfillment_method === 'Pickup' ? (localeStore.isAmharic ? 'በዳቦ ቤቱ ዴስክ ይገኛል' : 'Ready at bakery counter') : (localeStore.isAmharic ? 'አድራሹ ወደ አድራሻዎ በመጓዝ ላይ ነው' : 'Courier en route to your address') }}
              </div>
            </div>
            <div
              class="absolute left-4 top-8 w-0.5 h-8"
              :class="isStepActiveOrDone(5) ? 'bg-[#387A54]' : 'bg-[#E6DED3]'"
            ></div>
          </div>

          <!-- Step 5: Completed -->
          <div class="flex items-start space-x-3.5">
            <div
              class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shrink-0 z-10 shadow-xs transition"
              :class="isStepActiveOrDone(5) ? 'bg-[#387A54] text-white' : 'bg-[#F8F3EA] text-[#786B60] border border-[#E6DED3]'"
            >
              {{ isStepActiveOrDone(5) ? '✓' : '5' }}
            </div>
            <div>
              <div class="text-xs font-bold text-[#29231F]">
                5 &middot; {{ order?.fulfillment_method === 'Pickup' ? (localeStore.isAmharic ? 'ተረክበዋል' : 'Picked Up') : (localeStore.isAmharic ? 'ደርሷል' : 'Delivered') }}
              </div>
              <div class="text-[11px] text-[#786B60]">{{ localeStore.isAmharic ? 'ትዕዛዙ ተጠናቋል። መልካም ጊዜ!' : 'Order complete. Fresh bakery enjoyed!' }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Details & Items Card (7 cols) -->
      <div class="md:col-span-7 bg-white dark:bg-[#1E120B] rounded-3xl border border-[#E6DED3] dark:border-[#382318] p-6 sm:p-7 shadow-xs space-y-6">
        <h2 class="text-base font-serif font-bold text-[#29231F] dark:text-[#FAF3EE] border-b border-[#E6DED3] dark:border-[#382318] pb-3">
          {{ localeStore.isAmharic ? 'የትዕዛዝ ማጠቃለያ እና ደረሰኝ' : 'Order Summary & Receipt' }}
        </h2>

        <!-- Metadata Grid -->
        <div class="space-y-3 text-xs border-b border-[#E6DED3] dark:border-[#382318] pb-4">
          <div class="flex justify-between items-center">
            <span class="text-[#786B60] dark:text-[#A8988E]">{{ localeStore.isAmharic ? 'ደንበኛ' : 'Customer' }}</span>
            <span class="font-bold text-[#2A1810] dark:text-[#FAF3EE]">
              {{ order?.customer_name || order?.user?.full_name || 'Customer' }}
              <span v-if="order?.customer_phone || order?.user?.phone" class="text-xs font-normal text-[#786B60] dark:text-[#A8988E]">
                ({{ order?.customer_phone || order?.user?.phone }})
              </span>
            </span>
          </div>

          <div class="flex justify-between items-center">
            <span class="text-[#786B60] dark:text-[#A8988E]">{{ localeStore.t('fulfilling_branch_label') }}</span>
            <span class="font-bold text-[#29231F] dark:text-[#FAF3EE]">
              {{ localeStore.tBranch(order?.branch?.branch_name || 'Bole Flagship') }} ({{ currentBranchCode }})
            </span>
          </div>

          <div class="flex justify-between items-center">
            <span class="text-[#786B60] dark:text-[#A8988E]">{{ localeStore.isAmharic ? 'የመረከቢያ መንገድ' : 'Fulfillment Mode' }}</span>
            <span class="font-bold text-[#DE1B54]">
              {{ order?.fulfillment_method === 'Delivery' ? ('🚀 ' + localeStore.t('doorstep_deliv_label')) : ('🛍️ ' + localeStore.t('instore_pickup_label')) }}
            </span>
          </div>

          <div class="flex justify-between items-center">
            <span class="text-[#786B60] dark:text-[#A8988E]">{{ localeStore.isAmharic ? 'የክፍያ ዘዴ' : 'Payment Method' }}</span>
            <div class="flex items-center space-x-1.5 font-bold">
              <span :class="order?.payment?.payment_status?.includes('PAID') ? 'text-[#387A54] dark:text-emerald-400' : 'text-[#B98235] dark:text-amber-400'">
                {{ order?.payment?.payment_method === 'CASH_ON_PICKUP' ? (localeStore.isAmharic ? 'በቅርንጫፍ ሲረከቡ የሚከፈል' : 'Cash on Pickup') : (order?.payment?.payment_method || 'Online') }} &middot; {{ order?.payment?.payment_status === 'PAID_TEST' ? 'PAID' : (order?.payment?.payment_status || 'PENDING') }}
              </span>
              <span
                v-if="order?.payment?.is_test || order?.payment?.transaction_reference?.startsWith('TEST-') || order?.payment?.payment_status?.includes('TEST')"
                class="px-1.5 py-0.2 text-[9px] font-black uppercase rounded bg-purple-100 text-purple-700 dark:bg-purple-900/60 dark:text-purple-300 border border-purple-300 dark:border-purple-700"
              >
                TEST MODE
              </span>
            </div>
          </div>

          <div v-if="order?.payment?.transaction_reference" class="flex justify-between items-center">
            <span class="text-[#786B60] dark:text-[#A8988E]">{{ localeStore.isAmharic ? 'የግብይት ማጣቀሻ' : 'Transaction Ref' }}</span>
            <span class="font-mono text-xs font-bold text-[#2A1810] dark:text-[#FAF3EE]">
              {{ order?.payment?.transaction_reference }}
            </span>
          </div>

          <div class="flex justify-between items-start">
            <span class="text-[#786B60] dark:text-[#A8988E]">{{ order?.fulfillment_method === 'Pickup' ? (localeStore.isAmharic ? 'የመረከቢያ ቦታ' : 'Pickup Location') : (localeStore.isAmharic ? 'የማድረሻ አድራሻ' : 'Delivery Address') }}</span>
            <span class="font-semibold text-[#2A1810] dark:text-[#FAF3EE] text-right max-w-[280px]">
              {{ order?.fulfillment_method === 'Pickup' ? (order?.branch?.address || 'Cameroon St, Bole, Addis Ababa') : (order?.address?.street || order?.delivery_address || 'Customer Address, Addis Ababa') }}
            </span>
          </div>
        </div>

        <!-- Items Breakdown -->
        <div class="space-y-3 text-xs">
          <div class="text-[11px] font-bold uppercase tracking-wider text-[#786B60] dark:text-[#A8988E]">
            {{ localeStore.t('bakes_in_order') }} ({{ displayItems.length }})
          </div>

          <div class="space-y-2 divide-y divide-gray-100 dark:divide-[#382318]">
            <div
              v-for="item in displayItems"
              :key="item.order_item_id || item.product_name"
              class="pt-2 first:pt-0 flex justify-between items-center"
            >
              <div class="flex items-center space-x-2.5">
                <span class="w-6 h-6 rounded-lg bg-rose-50 dark:bg-[#341822] flex items-center justify-center text-xs font-bold text-[#DE1B54]">
                  {{ item.quantity }}×
                </span>
                <span class="text-[#2A1810] dark:text-[#FAF3EE] font-medium">{{ localeStore.tProduct(item.product?.product_name || item.product_name) }}</span>
              </div>
              <span class="font-bold text-[#2A1810] dark:text-rose-400">
                {{ localeStore.formatPrice((item.unit_price || item.price) * item.quantity) }}
              </span>
            </div>
          </div>
        </div>

        <!-- Totals Breakdown -->
        <div class="pt-4 border-t border-gray-100 dark:border-[#382318] space-y-2 text-xs">
          <div class="flex justify-between items-center text-[#786B60] dark:text-[#A8988E]">
            <span>{{ localeStore.t('subtotal') }}</span>
            <span class="font-semibold text-[#2A1810] dark:text-[#FAF3EE]">{{ localeStore.formatPrice(itemsSubtotal) }}</span>
          </div>

          <div v-if="Number(order?.delivery_fee) > 0" class="flex justify-between items-center text-[#786B60] dark:text-[#A8988E]">
            <span>{{ localeStore.t('delivery_fee') }}</span>
            <span class="font-semibold text-[#2A1810] dark:text-[#FAF3EE]">{{ localeStore.formatPrice(order?.delivery_fee) }}</span>
          </div>

          <div class="pt-2 border-t border-gray-100 dark:border-[#382318] flex justify-between items-center text-base font-bold text-[#2A1810] dark:text-[#FAF3EE]">
            <span>{{ localeStore.t('grand_total') }}</span>
            <span class="text-[#DE1B54] text-lg font-black">{{ localeStore.formatPrice(order?.total_amount || itemsSubtotal) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Questions Box -->
    <div class="bg-rose-50/60 dark:bg-[#20120B] rounded-3xl border border-rose-100 dark:border-[#382318] p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
      <div class="space-y-1">
        <h3 class="text-xs font-bold text-[#2A1810] dark:text-[#FAF3EE] uppercase tracking-wider flex items-center space-x-1.5">
          <span>📞</span>
          <span>{{ localeStore.isAmharic ? 'ስለዚህ ትዕዛዝ ጥያቄ አለዎት?' : 'Questions about this bake order?' }}</span>
        </h3>
        <p class="text-xs text-[#786B60] dark:text-[#A8988E] leading-relaxed">
          <template v-if="localeStore.isAmharic">
            ለ <strong class="text-[#2A1810] dark:text-[#FAF3EE]">{{ localeStore.tBranch(order?.branch?.branch_name || 'Bole') }}</strong> በ <strong class="text-[#DE1B54]">{{ order?.branch?.phone || '+251 911 123 456' }}</strong> በቀጥታ ይደውሉ።
          </template>
          <template v-else>
            Call {{ order?.branch?.branch_name || 'Bole Flagship' }} directly at <strong class="text-[#DE1B54]">{{ order?.branch?.phone || '+251 911 123 456' }}</strong>.
          </template>
        </p>
      </div>

      <a
        :href="`tel:${order?.branch?.phone || '+251911123456'}`"
        class="px-5 py-2.5 min-h-[40px] bg-white dark:bg-[#1E120B] border border-gray-200 dark:border-[#382318] hover:border-[#DE1B54] text-xs font-bold text-[#2A1810] dark:text-[#FAF3EE] rounded-xl shadow-2xs transition shrink-0 flex items-center justify-center cursor-pointer"
      >
        {{ localeStore.t('call_bakery_btn') }}
      </a>
    </div>

    <!-- Bottom Buttons Row -->
    <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
      <router-link
        to="/shop"
        class="px-6 py-3 min-h-[44px] bg-[#DE1B54] hover:bg-[#C51345] text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center justify-center cursor-pointer"
      >
        ← {{ localeStore.t('continue_shopping') }}
      </router-link>

      <!-- Cancel Order Button with rules -->
      <div class="flex items-center space-x-3">
        <button
          v-if="canCustomerCancel"
          @click="cancelOrder"
          :disabled="cancelling"
          class="px-5 py-2.5 bg-white border border-[#B84B42]/40 hover:bg-[#B84B42]/10 text-[#B84B42] text-xs font-bold rounded-xl transition"
        >
          {{ cancelling ? (localeStore.isAmharic ? 'በመሰረዝ ላይ...' : 'Cancelling Order...') : (localeStore.isAmharic ? 'ትዕዛዝ ሰርዝ' : 'Cancel Order') }}
        </button>

        <span
          v-else-if="order?.order_status !== 'Cancelled' && order?.order_status !== 'Completed'"
          class="text-xs text-[#786B60] italic"
        >
          {{ localeStore.isAmharic ? 'መጋገር ከጀመረ በኋላ ትዕዛዝ መሰረዝ አይቻልም።' : 'Cancellation is locked once artisan baking begins.' }}
        </span>
      </div>
    </div>
    </div>

    <!-- ========================================================================= -->
    <!-- DEDICATED PROFESSIONAL ONE-PAGE SWEETBAKES ORDER RECEIPT (PRINT MEDIA ONLY) -->
    <!-- ========================================================================= -->
    <div
      v-if="order"
      id="order-receipt-container"
      class="hidden print:block bg-white text-[#1A1412] font-sans antialiased"
      aria-label="SweetBakes Official Order Receipt"
    >
      <div class="receipt-card max-w-[760px] mx-auto p-5 sm:p-6 bg-white border border-[#D4C8BE] rounded-2xl">
        
        <!-- 1. Header: Bakery Identity & Receipt Document Title -->
        <div class="flex items-start justify-between border-b-2 border-[#3D251E] pb-3 mb-3">
          <!-- Left: Logo & Company Name -->
          <div class="space-y-0.5">
            <div class="flex items-center space-x-2">
              <span class="text-2xl leading-none">🧁</span>
              <span class="font-serif font-black text-2xl tracking-tight text-[#3D251E]">SweetBakes</span>
              <span class="text-xs uppercase font-sans font-black tracking-widest text-[#BA5420] ml-1">Bakery</span>
            </div>
            <p class="text-[11px] text-[#5C4A3E] font-medium tracking-wide">
              Artisan Cakes &middot; Fresh Pastries &middot; Handcrafted Daily
            </p>
          </div>

          <!-- Right: Document Heading & Status -->
          <div class="text-right space-y-1">
            <div class="inline-block border-2 border-[#3D251E] px-3 py-0.5 rounded text-xs font-black tracking-widest text-[#3D251E] uppercase">
              ORDER RECEIPT
            </div>
            <div class="text-[11px] font-bold text-[#3D251E]">
              Status: 
              <span 
                class="uppercase tracking-wider px-2 py-0.5 rounded text-[10px] font-bold inline-block"
                :class="{
                  'bg-emerald-100 text-emerald-900 border border-emerald-300': ['Confirmed', 'Ready', 'Completed'].includes(order.order_status),
                  'bg-amber-100 text-amber-900 border border-amber-300': ['Pending', 'Preparing'].includes(order.order_status),
                  'bg-rose-100 text-rose-900 border border-rose-300': order.order_status === 'Cancelled',
                }"
              >
                {{ order.order_status || 'Confirmed' }}
              </span>
            </div>
          </div>
        </div>

        <!-- 2. Branch & Order Reference Grid -->
        <div class="grid grid-cols-2 gap-4 text-xs border-b border-[#E6DED3] pb-3 mb-3">
          <!-- Fulfilling Branch Info -->
          <div class="space-y-0.5">
            <div class="text-[10px] font-bold uppercase tracking-wider text-[#8A796E]">Fulfilling Bakery Branch</div>
            <div class="font-bold text-[#3D251E] text-sm">
              {{ resolvedBranchName }}
              <span v-if="order.branch?.branch_type === 'Main'" class="text-[10px] font-normal text-[#BA5420]">(Main HQ)</span>
            </div>
            <div class="text-[#5C4A3E] text-[11px]">
              📍 {{ resolvedBranchAddress }}
            </div>
            <div class="text-[#5C4A3E] text-[11px]">
              📞 {{ resolvedBranchPhone }} &middot; ✉️ {{ resolvedBranchEmail }}
            </div>
          </div>

          <!-- Order Identification -->
          <div class="space-y-0.5 text-right">
            <div class="text-[10px] font-bold uppercase tracking-wider text-[#8A796E]">Order Identification</div>
            <div class="font-mono font-black text-base text-[#3D251E]">
              #{{ order.order_number }}
            </div>
            <div class="text-[#5C4A3E] text-[11px]">
              <span class="font-semibold text-[#8A796E]">Date Placed:</span> {{ orderDateFormatted }}
            </div>
            <div class="text-[#8A796E] text-[10px]">
              Receipt Printed: {{ printTimestamp }}
            </div>
          </div>
        </div>

        <!-- 3. Customer & Fulfillment Details Box -->
        <div class="grid grid-cols-2 gap-4 bg-[#FAF7F2] border border-[#E6DED3] rounded-xl p-3 text-xs mb-3">
          <!-- Customer Details -->
          <div class="space-y-0.5">
            <div class="text-[10px] font-bold uppercase tracking-wider text-[#8A796E]">Customer Details</div>
            <div class="font-bold text-[#3D251E] text-sm">
              {{ resolvedCustomerName }}
            </div>
            <div class="text-[#5C4A3E] text-[11px] font-medium">
              📞 {{ resolvedCustomerPhone }}
            </div>
            <div v-if="resolvedCustomerEmail" class="text-[#5C4A3E] text-[11px] truncate">
              ✉️ {{ resolvedCustomerEmail }}
            </div>
          </div>

          <!-- Fulfillment & Destination -->
          <div class="space-y-0.5">
            <div class="text-[10px] font-bold uppercase tracking-wider text-[#8A796E]">Fulfillment Mode</div>
            <div class="font-bold text-[#3D251E] text-xs">
              <span v-if="order.fulfillment_method === 'Delivery'" class="text-[#BA5420]">
                🚀 Doorstep Courier Delivery
              </span>
              <span v-else class="text-[#047857]">
                🛍️ Bakery Counter Pickup
              </span>
            </div>
            <div class="text-[#5C4A3E] text-[11px] leading-snug pt-0.5">
              <span class="font-semibold text-[#3D251E]">
                {{ order.fulfillment_method === 'Pickup' ? 'Pickup Location:' : 'Delivery Address:' }}
              </span>
              <span class="block text-[#5C4A3E] mt-0.5">{{ resolvedDeliveryAddress }}</span>
            </div>
          </div>
        </div>

        <!-- 4. Itemized Items Table -->
        <div class="mb-3">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="border-b-2 border-[#3D251E] text-[10px] font-bold uppercase tracking-wider text-[#3D251E]">
                <th class="py-1.5 px-2 w-8 text-center">#</th>
                <th class="py-1.5 px-2">Item Description</th>
                <th class="py-1.5 px-3 text-right">Unit Price</th>
                <th class="py-1.5 px-3 text-center">Qty</th>
                <th class="py-1.5 px-2 text-right">Line Total</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#E6DED3]">
              <tr 
                v-for="(item, idx) in displayItems" 
                :key="item.order_item_id || idx"
                class="text-[#3D251E]"
              >
                <td class="py-1.5 px-2 text-center text-[#8A796E] font-mono text-[11px]">{{ idx + 1 }}</td>
                <td class="py-1.5 px-2">
                  <div class="font-bold text-[11px]">{{ localeStore.tProduct(item.product?.product_name || item.product_name) }}</div>
                </td>
                <td class="py-1.5 px-3 text-right font-mono text-[#5C4A3E] text-[11px]">
                  ETB {{ Number(item.unit_price || item.price || 0).toFixed(2) }}
                </td>
                <td class="py-1.5 px-3 text-center font-bold text-[11px]">
                  {{ item.quantity }}
                </td>
                <td class="py-1.5 px-2 text-right font-mono font-bold text-[#3D251E] text-[11px]">
                  ETB {{ Number((item.unit_price || item.price || 0) * (item.quantity || 1)).toFixed(2) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- 5. Payment Details & Financial Totals Breakdown -->
        <div class="grid grid-cols-12 gap-3 border-t-2 border-[#3D251E] pt-2.5 mb-3">
          <!-- Left: Payment details -->
          <div class="col-span-7 space-y-1 text-xs text-[#5C4A3E]">
            <div class="text-[10px] font-bold uppercase tracking-wider text-[#8A796E]">Payment Information</div>
            <div class="flex items-center space-x-1.5 text-[11px]">
              <span class="font-semibold text-[#3D251E]">Method:</span>
              <span>{{ resolvedPaymentMethod }}</span>
            </div>
            <div class="flex items-center space-x-1.5 text-[11px]">
              <span class="font-semibold text-[#3D251E]">Status:</span>
              <span class="font-bold text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 text-[10px]">
                {{ resolvedPaymentStatus.toUpperCase() }} ✓
              </span>
            </div>
            <div v-if="resolvedTransactionRef" class="flex items-center space-x-1.5 text-[10px]">
              <span class="font-semibold text-[#3D251E]">Ref:</span>
              <span class="font-mono text-[#3D251E]">{{ resolvedTransactionRef }}</span>
            </div>
          </div>

          <!-- Right: Financial Summary -->
          <div class="col-span-5 space-y-1 text-xs">
            <div class="flex justify-between items-center text-[#5C4A3E] text-[11px]">
              <span>Subtotal:</span>
              <span class="font-mono font-semibold">ETB {{ Number(itemsSubtotal).toFixed(2) }}</span>
            </div>

            <div class="flex justify-between items-center text-[#5C4A3E] text-[11px]">
              <span>Delivery Fee:</span>
              <span class="font-mono font-semibold">
                {{ Number(order.delivery_fee) > 0 ? `ETB ${Number(order.delivery_fee).toFixed(2)}` : 'ETB 0.00 (Free)' }}
              </span>
            </div>

            <div class="border-t border-[#3D251E] pt-1 mt-1 flex justify-between items-center">
              <span class="text-xs font-black text-[#3D251E] uppercase">Grand Total:</span>
              <span class="text-sm font-black font-mono text-[#3D251E]">
                ETB {{ Number(order.total_amount || itemsSubtotal).toFixed(2) }}
              </span>
            </div>
          </div>
        </div>

        <!-- 6. Receipt Footer & Thank-You Message -->
        <div class="border-t border-dashed border-[#E6DED3] pt-2.5 text-center space-y-0.5">
          <div class="text-xs font-serif font-bold text-[#3D251E]">
            ❤️ Thank you for choosing SweetBakes Bakery!
          </div>
          <p class="text-[10px] text-[#7A6B60] max-w-lg mx-auto leading-tight">
            We appreciate your business and hope you enjoy our freshly baked delicacies. For any questions regarding this order, please contact our fulfilling branch quoting your Order Number.
          </p>
          <div class="text-[9px] text-[#A6978C] uppercase tracking-wider pt-0.5">
            SweetBakes &amp; Kenean Cake &middot; Dessie, Ethiopia &middot; www.sweetbakes.com
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useToastStore } from '../../stores/toast';
import { useDialogStore } from '../../stores/dialog';
import { useLocaleStore } from '../../stores/locale';
import api from '../../api';

const route = useRoute();
const toast = useToastStore();
const dialog = useDialogStore();
const localeStore = useLocaleStore();
const orderNumberParam = computed(() => route.params.order_number || route.params.id || '');

const order = ref(null);
const loadingOrder = ref(true);
const cancelling = ref(false);

const orderDateFormatted = computed(() => {
  const d = order.value?.placed_at || order.value?.created_at;
  if (!d) return 'Today';
  return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
});

const currentBranchCode = computed(() => {
  const b = order.value?.branch;
  if (!b) return 'Branch 01';
  return b.branch_type === 'Main' ? 'Main HQ' : `Branch ${String(b.branch_id).padStart(2, '0')}`;
});

const statusIcon = computed(() => {
  const s = order.value?.order_status || 'Pending';
  switch (s) {
    case 'Confirmed': return '✅';
    case 'Pending': return '⏳';
    case 'Preparing': return '👨‍🍳';
    case 'Ready': return '🛍️';
    case 'Out for delivery': return '🚀';
    case 'Completed': return '🎉';
    case 'Cancelled': return '❌';
    default: return '📦';
  }
});

const statusHeadline = computed(() => {
  const s = order.value?.order_status || 'Pending';
  if (localeStore.isAmharic) {
    switch (s) {
      case 'Confirmed': return 'ትዕዛዙ ተረጋግጧል፣ ክፍያውም ተረጋግጧል!';
      case 'Pending': return 'ትዕዛዝ ተሰጥቷል። ክምችት ተይዟል፣ ማረጋገጫ በመጠባበቅ ላይ።';
      case 'Preparing': return 'ዋና ዳቦ ጋጋሪዎች ትዕዛዝዎን በኩሽና ውስጥ በማዘጋጀት ላይ ናቸው።';
      case 'Ready': return order.value?.fulfillment_method === 'Pickup' ? 'ትዕዛዝዎ በመደብራችን ካውንተር ለመውሰድ ዝግጁ ነው!' : 'ትዕዛዝዎ ታሽጎ ለመላኪያ ዝግጁ ሆኗል።';
      case 'Out for delivery': return 'አድራሹ ትዕዛዝዎን ይዞ ወደ ደጃፍዎ በመምጣት ላይ ነው!';
      case 'Completed': return 'ትዕዛዙ ተጠናቋል። መልካም የምግብ ጊዜ!';
      case 'Cancelled': return 'ይህ ትዕዛዝ ተሰርዟል።';
      default: return 'ትዕዛዝ በሂደት ላይ';
    }
  }
  switch (s) {
    case 'Confirmed': return 'Order confirmed & payment verified!';
    case 'Pending': return 'Order placed. Stock reserved, awaiting confirmation.';
    case 'Preparing': return 'Master bakers are crafting your order in the kitchen.';
    case 'Ready': return order.value?.fulfillment_method === 'Pickup' ? 'Your order is ready for pickup at the counter!' : 'Order boxed and ready for delivery courier.';
    case 'Out for delivery': return 'Courier is en route to your doorstep!';
    case 'Completed': return 'Order completed. Fresh confectionery enjoyed!';
    case 'Cancelled': return 'This order was cancelled.';
    default: return 'Order In Progress';
  }
});

const canCustomerCancel = computed(() => {
  const s = order.value?.order_status;
  return s === 'Pending' || s === 'Confirmed';
});

const displayItems = computed(() => {
  return order.value?.items || [];
});

const itemsSubtotal = computed(() => {
  return displayItems.value.reduce((acc, item) => acc + ((item.unit_price || item.price || 0) * (item.quantity || 1)), 0);
});

const loadOrder = async () => {
  if (!orderNumberParam.value) {
    order.value = null;
    loadingOrder.value = false;
    return;
  }
  loadingOrder.value = true;
  try {
    const res = await api.get(`/orders/${orderNumberParam.value}`);
    order.value = res.data;
  } catch (err) {
    console.error('Failed to load order', err);
    order.value = null;
  } finally {
    loadingOrder.value = false;
  }
};

onMounted(() => {
  loadOrder();
});

function isStepActiveOrDone(step) {
  const status = order.value?.order_status || 'Pending';
  if (status === 'Cancelled') return false;
  if (status === 'Pending') return step <= 1;
  if (status === 'Confirmed') return step <= 2;
  if (status === 'Preparing') return step <= 3;
  if (status === 'Out for delivery' || status === 'Ready') return step <= 4;
  if (status === 'Completed') return step <= 5;
  return false;
}

function statusBadgeClass(status) {
  switch (status) {
    case 'Pending':
      return 'bg-[#B98235]/15 text-[#B98235] border border-[#B98235]/30';
    case 'Confirmed':
      return 'bg-rose-50 text-[#DE1B54] border border-rose-200 dark:bg-[#341822] dark:border-[#58263A]';
    case 'Preparing':
      return 'bg-[#B98235]/15 text-[#B98235] border border-[#B98235]/30';
    case 'Out for delivery':
    case 'Ready':
      return 'bg-[#387A54]/15 text-[#387A54] border border-[#387A54]/30';
    case 'Completed':
      return 'bg-[#387A54]/20 text-[#387A54] border border-[#387A54]/40';
    case 'Cancelled':
      return 'bg-[#B84B42]/15 text-[#B84B42] border border-[#B84B42]/30';
    default:
      return 'bg-gray-100 text-[#2A1810] border border-gray-200 dark:bg-[#20120B] dark:text-[#FAF3EE] dark:border-[#382318]';
  }
}

async function cancelOrder() {
  const confirmed = await dialog.confirm({
    title: 'Cancel Order',
    message: 'Are you sure you want to cancel this order? Any payment made will be refunded and stock reservation released.',
    confirmText: 'Yes, Cancel Order',
    variant: 'danger',
  });
  if (!confirmed) {
    return;
  }

  cancelling.value = true;
  try {
    await api.post(`/orders/${order.value?.order_number || orderNumberParam.value}/cancel`, {
      reason: 'Cancelled by customer before preparation',
    });
    toast.success('Your order has been cancelled and stock reservation released.');
    await loadOrder();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to cancel order.');
  } finally {
    cancelling.value = false;
  }
}

function printReceipt() {
  const originalTitle = document.title;
  if (order.value?.order_number) {
    document.title = `SweetBakes-Receipt-${order.value.order_number}`;
  }
  window.print();
  setTimeout(() => {
    document.title = originalTitle;
  }, 1000);
}

const printTimestamp = computed(() => {
  return new Date().toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
});

const resolvedCustomerName = computed(() => {
  return order.value?.customer_name || order.value?.user?.full_name || 'Valued Customer';
});

const resolvedCustomerPhone = computed(() => {
  return order.value?.customer_phone || order.value?.user?.phone || 'N/A';
});

const resolvedCustomerEmail = computed(() => {
  return order.value?.customer_email || order.value?.user?.email || null;
});

const resolvedBranchName = computed(() => {
  return order.value?.branch?.branch_name || 'Main Branch - Dessie Piazza';
});

const resolvedBranchAddress = computed(() => {
  return order.value?.branch?.address || 'Piazza Commercial Center, Dessie, Ethiopia';
});

const resolvedBranchPhone = computed(() => {
  return order.value?.branch?.phone || '+251 911 123 456';
});

const resolvedBranchEmail = computed(() => {
  return order.value?.branch?.email || 'info@sweetbakes.com';
});

const resolvedPaymentMethod = computed(() => {
  return order.value?.payment?.payment_method ||
    (order.value?.payments && order.value.payments.length > 0 ? order.value.payments[0].payment_method : null) ||
    'Online (Chapa)';
});

const resolvedPaymentStatus = computed(() => {
  return order.value?.payment?.payment_status ||
    (order.value?.payments && order.value.payments.length > 0 ? order.value.payments[0].payment_status : null) ||
    'Paid';
});

const resolvedTransactionRef = computed(() => {
  return order.value?.payment?.transaction_reference ||
    (order.value?.payments && order.value.payments.length > 0 ? order.value.payments[0].transaction_reference : null) ||
    null;
});

const resolvedDeliveryAddress = computed(() => {
  if (order.value?.fulfillment_method === 'Pickup') {
    return order.value?.branch?.address || 'Bakery Counter Pickup (Self-Collection)';
  }
  if (order.value?.address?.street) {
    const a = order.value.address;
    return [a.street, a.city, a.region].filter(Boolean).join(', ');
  }
  return order.value?.delivery_address || 'Dessie, Ethiopia';
});
</script>

<style>
@media print {
  @page {
    size: A4 portrait;
    margin: 8mm 10mm;
  }

  /* Reset global document styles for printing */
  html, body {
    margin: 0 !important;
    padding: 0 !important;
    height: auto !important;
    min-height: 100% !important;
    background: #ffffff !important;
    background-color: #ffffff !important;
    color: #1a1412 !important;
    font-size: 11pt !important;
    line-height: 1.3 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  /* Reset app container */
  #app, main, .sweetbakes-container {
    background: #ffffff !important;
    background-color: #ffffff !important;
    padding: 0 !important;
    margin: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
  }

  /* Force hide all non-receipt elements */
  header,
  footer,
  nav,
  aside,
  .print\:hidden,
  .no-print {
    display: none !important;
    visibility: hidden !important;
    height: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    overflow: hidden !important;
  }

  /* Display ONLY the dedicated receipt container */
  #order-receipt-container {
    display: block !important;
    visibility: visible !important;
    position: static !important;
    width: 100% !important;
    max-width: 760px !important;
    margin: 0 auto !important;
    padding: 0 !important;
    box-sizing: border-box !important;
    background: #ffffff !important;
    color: #1a1412 !important;
    page-break-inside: avoid !important;
    break-inside: avoid !important;
    page-break-after: avoid !important;
    page-break-before: avoid !important;
  }

  #order-receipt-container .receipt-card {
    border: 1px solid #d4c8be !important;
    border-radius: 12px !important;
    box-shadow: none !important;
    background-color: #ffffff !important;
    page-break-inside: avoid !important;
    break-inside: avoid !important;
  }

  #order-receipt-container table {
    page-break-inside: avoid !important;
    break-inside: avoid !important;
  }

  #order-receipt-container tr {
    page-break-inside: avoid !important;
    break-inside: avoid !important;
  }
}
</style>
