<template>
  <div class="sweetbakes-container py-8 sm:py-10 space-y-8 font-sans selection:bg-[#FCE7F0] selection:text-[#DE1B54] text-[#29231F] dark:text-[#FAF3EE]">
    <!-- Breadcrumb & Page Title -->
    <div class="space-y-1">
      <div class="flex items-center space-x-2 text-xs text-[#786B60] dark:text-[#C5B7AF]">
        <router-link to="/" class="hover:text-[#DE1B54] transition">{{ localeStore.t('home') }}</router-link>
        <span>/</span>
        <span class="text-[#2A1810] dark:text-[#FAF3EE] font-semibold">{{ localeStore.t('my_account_breadcrumb') }}</span>
      </div>
      <h1 class="text-2xl sm:text-4xl font-serif font-black text-[#2A1810] dark:text-[#FAF3EE]">
        {{ localeStore.t('account_orders_title') }}
      </h1>
      <p class="text-xs sm:text-sm text-[#786B60] dark:text-[#C5B7AF]">
        {{ localeStore.t('account_sub') }}
      </p>
    </div>

    <!-- Layout Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
      <!-- Left Sidebar (4 cols) -->
      <aside class="lg:col-span-4 bg-white dark:bg-[#1E120B] rounded-3xl border border-gray-200 dark:border-[#382318] p-5 sm:p-7 space-y-6 shadow-xs">
        <!-- User Avatar & Profile Card -->
        <div class="text-center space-y-3 pb-6 border-b border-gray-100 dark:border-[#382318]">
          <div class="relative w-20 h-20 rounded-full bg-rose-50 dark:bg-[#341822] border-2 border-[#DE1B54]/40 flex items-center justify-center mx-auto text-3xl shadow-xs">
            👨‍🍳
            <span class="absolute bottom-0 right-0 w-5 h-5 rounded-full bg-[#387A54] border-2 border-white dark:border-[#1E120B] flex items-center justify-center text-[10px] text-white font-bold" title="Active Account">
              ✓
            </span>
          </div>
          <div>
            <h2 class="text-lg font-serif font-bold text-[#2A1810] dark:text-[#FAF3EE]">
              {{ userName }}
            </h2>
            <p class="text-xs text-[#786B60] dark:text-[#C5B7AF] mt-0.5">{{ userEmail }}</p>
            <div class="flex items-center justify-center gap-2 mt-2">
              <span class="inline-block px-3 py-0.5 bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] text-[#2A1810] dark:text-[#FAF3EE] text-[11px] font-bold rounded-full">
                {{ authStore.user?.role || (localeStore.isAmharic ? 'ደንበኛ' : 'Customer') }}
              </span>
              <span class="inline-block px-2.5 py-0.5 bg-[#387A54]/10 text-[#387A54] dark:text-emerald-400 text-[11px] font-bold rounded-full">
                Artisan Club
              </span>
            </div>
          </div>
        </div>

        <!-- Sidebar Navigation Buttons -->
        <nav class="space-y-1 text-xs font-semibold">
          <button
            @click="activeTab = 'orders'"
            class="w-full text-left px-4 py-3 min-h-[44px] rounded-2xl transition flex items-center justify-between cursor-pointer"
            :class="activeTab === 'orders' ? 'bg-[#DE1B54] text-white shadow-xs' : 'text-[#2A1810] dark:text-[#FAF3EE] hover:bg-rose-50 dark:hover:bg-[#341822]'"
          >
            <div class="flex items-center space-x-3">
              <span class="text-base">📦</span>
              <span>{{ localeStore.t('order_history_tab') }}</span>
            </div>
            <span
              v-if="orders.length > 0"
              class="px-2 py-0.5 rounded-full text-[11px] font-bold"
              :class="activeTab === 'orders' ? 'bg-white/20 text-white' : 'bg-rose-100 dark:bg-[#341822] text-[#DE1B54]'"
            >
              {{ orders.length }}
            </span>
          </button>

          <button
            @click="activeTab = 'custom_cakes'"
            class="w-full text-left px-4 py-3 min-h-[44px] rounded-2xl transition flex items-center justify-between cursor-pointer"
            :class="activeTab === 'custom_cakes' ? 'bg-[#DE1B54] text-white shadow-xs' : 'text-[#2A1810] dark:text-[#FAF3EE] hover:bg-rose-50 dark:hover:bg-[#341822]'"
          >
            <div class="flex items-center space-x-3">
              <span class="text-base">🎂</span>
              <span>{{ localeStore.t('custom_cakes_tab') }}</span>
            </div>
            <span
              v-if="pendingQuotesCount > 0"
              class="px-2 py-0.5 bg-[#B84B42] text-white rounded-full text-[10px] font-black animate-pulse"
            >
              {{ pendingQuotesCount }} Action
            </span>
          </button>

          <button
            @click="activeTab = 'wishlist'"
            class="w-full text-left px-4 py-3 min-h-[44px] rounded-2xl transition flex items-center justify-between cursor-pointer"
            :class="activeTab === 'wishlist' ? 'bg-[#DE1B54] text-white shadow-xs' : 'text-[#2A1810] dark:text-[#FAF3EE] hover:bg-rose-50 dark:hover:bg-[#341822]'"
          >
            <div class="flex items-center space-x-3">
              <span class="text-base">❤️</span>
              <span>{{ localeStore.t('wishlist_tab') }}</span>
            </div>
            <span
              v-if="wishlistStore.count > 0"
              class="px-2 py-0.5 rounded-full text-[11px] font-bold"
              :class="activeTab === 'wishlist' ? 'bg-white/20 text-white' : 'bg-rose-100 dark:bg-[#341822] text-[#DE1B54]'"
            >
              {{ wishlistStore.count }}
            </span>
          </button>

          <button
            @click="activeTab = 'addresses'"
            class="w-full text-left px-4 py-3 min-h-[44px] rounded-2xl transition flex items-center space-x-3 cursor-pointer"
            :class="activeTab === 'addresses' ? 'bg-[#DE1B54] text-white shadow-xs' : 'text-[#2A1810] dark:text-[#FAF3EE] hover:bg-rose-50 dark:hover:bg-[#341822]'"
          >
            <span class="text-base">📍</span>
            <span>{{ localeStore.t('saved_addresses_tab') }}</span>
          </button>

          <button
            @click="activeTab = 'settings'"
            class="w-full text-left px-4 py-3 min-h-[44px] rounded-2xl transition flex items-center space-x-3 cursor-pointer"
            :class="activeTab === 'settings' ? 'bg-[#DE1B54] text-white shadow-xs' : 'text-[#2A1810] dark:text-[#FAF3EE] hover:bg-rose-50 dark:hover:bg-[#341822]'"
          >
            <span class="text-base">⚙️</span>
            <span>{{ localeStore.t('profile_settings_tab') }}</span>
          </button>

          <button
            @click="handleLogout"
            class="w-full text-left px-4 py-3 min-h-[44px] rounded-2xl text-[#B84B42] hover:bg-[#B84B42]/10 transition flex items-center space-x-3 pt-3 mt-2 border-t border-gray-100 dark:border-[#382318] cursor-pointer"
          >
            <span class="text-base">🚪</span>
            <span>{{ localeStore.t('logout') }}</span>
          </button>
        </nav>

        <!-- Current Branch Affinity Widget -->
        <div class="p-4 rounded-2xl bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] space-y-2 text-xs">
          <div class="flex items-center justify-between">
            <span class="text-[11px] uppercase tracking-wider font-bold text-[#786B60] dark:text-[#C5B7AF]">{{ localeStore.t('active_branch_tag') }}</span>
            <router-link to="/branches" class="text-[11px] text-[#DE1B54] font-bold hover:underline">{{ localeStore.t('change_branch') }}</router-link>
          </div>
          <div class="font-bold text-[#2A1810] dark:text-[#FAF3EE] flex items-center space-x-2">
            <span>🏛️</span>
            <span>{{ localeStore.tBranch(currentBranchName) }}</span>
          </div>
          <p class="text-[11px] text-[#786B60] dark:text-[#C5B7AF]">
            {{ localeStore.isAmharic ? 'ትዕዛዞች በቀጥታ የሚዘጋጁት እና የሚደርሱት በዚህ ቅርንጫፍ ነው::' : "Orders are fulfilled directly by this branch's master bakers and delivery fleet." }}
          </p>
        </div>
      </aside>

      <!-- Right Main Content Area (8 cols) -->
      <main class="lg:col-span-8 bg-white dark:bg-[#1E120B] rounded-3xl border border-gray-200 dark:border-[#382318] p-5 sm:p-8 space-y-6 shadow-xs min-h-[580px]">
        <!-- 1. Orders Tab -->
        <div v-if="activeTab === 'orders'" class="space-y-6">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 dark:border-[#382318] pb-4">
            <div>
              <div class="flex items-center space-x-2">
                <h2 class="text-xl font-serif font-bold text-[#2A1810] dark:text-[#FAF3EE]">{{ localeStore.isAmharic ? 'የትዕዛዝ ታሪኬ' : 'My Order History' }}</h2>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 dark:bg-[#341822] text-[#DE1B54]">
                  {{ orders.length }} {{ orders.length === 1 ? (localeStore.isAmharic ? 'ትዕዛዝ' : 'Order') : (localeStore.isAmharic ? 'ትዕዛዞች' : 'Orders') }}
                </span>
              </div>
              <p class="text-xs text-[#786B60] dark:text-[#C5B7AF] mt-0.5">
                {{ localeStore.isAmharic ? 'የቀጥታ የማብሰል ሂደት፣ የማድረሻ ሁኔታ እና ዝርዝር ደረሰኞችን ይከታተሉ' : 'Track live kitchen preparation, delivery progress, and view itemized receipts' }}
              </p>
            </div>
            <div class="flex items-center space-x-2">
              <button
                @click="loadData"
                :disabled="isLoadingOrders"
                class="inline-flex items-center space-x-1.5 px-3 py-2 min-h-[38px] bg-gray-100 dark:bg-[#2A1810] hover:bg-gray-200 dark:hover:bg-[#382318] text-[#2A1810] dark:text-[#FAF3EE] text-xs font-bold rounded-xl transition cursor-pointer disabled:opacity-50 shadow-2xs"
                :title="localeStore.isAmharic ? 'ትዕዛዞችን ከዳቦ ቤቱ ጋር አመሳስል' : 'Sync orders with bakery'"
              >
                <span :class="{'animate-spin': isLoadingOrders}">🔄</span>
                <span>{{ isLoadingOrders ? (localeStore.isAmharic ? 'በማመሳሰል ላይ...' : 'Syncing...') : (localeStore.isAmharic ? 'አድስ' : 'Refresh') }}</span>
              </button>
              <router-link
                to="/shop"
                class="inline-flex items-center space-x-1.5 px-4 py-2 min-h-[38px] bg-[#DE1B54] hover:bg-[#C51345] text-white text-xs font-bold rounded-xl transition shadow-xs active:scale-95"
              >
                <span>{{ localeStore.isAmharic ? '+ ዳቦ ቤቱን ጎብኝ' : '+ Explore Bakery' }}</span>
                <span>→</span>
              </router-link>
            </div>
          </div>

          <!-- Filter Pills -->
          <div v-if="orders.length > 0" class="flex flex-wrap items-center gap-2 overflow-x-auto pb-1 text-xs font-bold">
            <button
              @click="orderFilter = 'all'"
              class="px-3.5 py-1.5 rounded-full transition cursor-pointer whitespace-nowrap"
              :class="orderFilter === 'all' ? 'bg-[#DE1B54] text-white shadow-xs' : 'bg-gray-100 dark:bg-[#2A1810] text-[#786B60] dark:text-[#C5B7AF] hover:text-[#2A1810] dark:hover:text-[#FAF3EE]'"
            >
              {{ localeStore.isAmharic ? `ሁሉም ትዕዛዞች (${orders.length})` : `All Orders (${orders.length})` }}
            </button>
            <button
              @click="orderFilter = 'active'"
              class="px-3.5 py-1.5 rounded-full transition cursor-pointer whitespace-nowrap flex items-center space-x-1.5"
              :class="orderFilter === 'active' ? 'bg-[#DE1B54] text-white shadow-xs' : 'bg-gray-100 dark:bg-[#2A1810] text-[#786B60] dark:text-[#C5B7AF] hover:text-[#2A1810] dark:hover:text-[#FAF3EE]'"
            >
              <span v-if="activeOrdersCount > 0" class="w-2 h-2 rounded-full bg-emerald-400 animate-ping inline-block"></span>
              <span>{{ localeStore.isAmharic ? `በሂደት ላይ (${activeOrdersCount})` : `Active Progress (${activeOrdersCount})` }}</span>
            </button>
            <button
              @click="orderFilter = 'completed'"
              class="px-3.5 py-1.5 rounded-full transition cursor-pointer whitespace-nowrap"
              :class="orderFilter === 'completed' ? 'bg-[#DE1B54] text-white shadow-xs' : 'bg-gray-100 dark:bg-[#2A1810] text-[#786B60] dark:text-[#C5B7AF] hover:text-[#2A1810] dark:hover:text-[#FAF3EE]'"
            >
              {{ localeStore.isAmharic ? `የተጠናቀቁ (${completedOrdersCount})` : `Completed (${completedOrdersCount})` }}
            </button>
            <button
              @click="orderFilter = 'cancelled'"
              class="px-3.5 py-1.5 rounded-full transition cursor-pointer whitespace-nowrap"
              :class="orderFilter === 'cancelled' ? 'bg-[#DE1B54] text-white shadow-xs' : 'bg-gray-100 dark:bg-[#2A1810] text-[#786B60] dark:text-[#C5B7AF] hover:text-[#2A1810] dark:hover:text-[#FAF3EE]'"
            >
              {{ localeStore.isAmharic ? `የተሰረዙ (${cancelledOrdersCount})` : `Cancelled (${cancelledOrdersCount})` }}
            </button>

            <!-- Clear All Finished History Button -->
            <button
              v-if="(completedOrdersCount + cancelledOrdersCount) > 0"
              @click="handleClearFinishedOrders"
              class="ml-auto px-3 py-1.5 rounded-full text-xs font-bold text-[#B84B42] hover:bg-[#B84B42]/10 border border-[#B84B42]/30 transition cursor-pointer flex items-center space-x-1 whitespace-nowrap shrink-0"
              :title="localeStore.isAmharic ? 'የተጠናቀቁ እና የተሰረዙ ትዕዛዞችን ከታሪክ አጽዳ' : 'Clear all completed and cancelled orders from your history'"
            >
              <span>🗑️</span>
              <span>{{ localeStore.isAmharic ? `ያለቁትን አጽዳ (${completedOrdersCount + cancelledOrdersCount})` : `Clear Finished (${completedOrdersCount + cancelledOrdersCount})` }}</span>
            </button>
          </div>

          <!-- Loading State Skeleton -->
          <div v-if="isLoadingOrders" class="space-y-4">
            <div v-for="n in 2" :key="n" class="p-6 rounded-2xl border border-gray-200 dark:border-[#382318] bg-[#FAF7F5] dark:bg-[#2A1810] animate-pulse space-y-4">
              <div class="h-6 bg-gray-200 dark:bg-[#382318] rounded-md w-1/3"></div>
              <div class="h-4 bg-gray-200 dark:bg-[#382318] rounded-md w-2/3"></div>
              <div class="h-10 bg-gray-200 dark:bg-[#382318] rounded-xl w-full"></div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else-if="displayOrders.length === 0" class="text-center py-16 space-y-3">
            <div class="text-5xl">🥐</div>
            <h3 class="font-serif font-bold text-lg text-[#2A1810] dark:text-[#FAF3EE]">
              {{ orderFilter === 'all' 
                ? (localeStore.isAmharic ? 'እስካሁን ምንም ትዕዛዝ አልተሰጠም' : 'No Orders Placed Yet') 
                : (localeStore.isAmharic ? `ምንም ${orderFilter === 'active' ? 'በሂደት ላይ ያለ' : (orderFilter === 'completed' ? 'የተጠናቀቀ' : 'የተሰረዘ')} ትዕዛዝ የለም` : `No ${orderFilter} orders found`) }}
            </h3>
            <p class="text-xs text-[#786B60] dark:text-[#C5B7AF] max-w-sm mx-auto">
              {{ orderFilter === 'all' 
                ? (localeStore.isAmharic ? "እስካሁን ምንም ትዕዛዝ አልሰጡም። በአቅራቢያዎ የሚገኘውን ቅርንጫፍ ይምረጡና ትኩስ ምርቶቻችንን ይዘዙ!" : "You haven't placed an order yet. Select your neighborhood branch and browse our fresh oven batches!") 
                : (localeStore.isAmharic ? 'በአሁኑ ጊዜ በመለያዎ ውስጥ ምንም ትዕዛዝ የለም።' : `You currently have no ${orderFilter} orders in your bakery account.`) }}
            </p>
            <router-link
              to="/shop"
              class="inline-block px-5 py-2.5 min-h-[40px] bg-[#DE1B54] hover:bg-[#C51345] text-white text-xs font-bold rounded-xl shadow-xs transition active:scale-95"
            >
              {{ localeStore.isAmharic ? "የዛሬ ትኩስ ምርቶችን እይ" : "Browse Today's Bakes" }}
            </router-link>
          </div>

          <!-- Orders List -->
          <div v-else class="space-y-5">
            <div
              v-for="order in displayOrders"
              :key="order.order_number"
              class="p-5 sm:p-6 rounded-3xl border border-gray-200 dark:border-[#382318] bg-[#FAF7F5] dark:bg-[#2A1810] hover:border-[#DE1B54]/50 transition-all space-y-5 shadow-xs"
            >
              <!-- Card Top Header -->
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-200 dark:border-[#382318] pb-4">
                <div class="flex items-center space-x-3.5">
                  <div class="w-12 h-12 rounded-2xl bg-white dark:bg-[#1E120B] border border-gray-200 dark:border-[#382318] flex items-center justify-center text-2xl shrink-0 shadow-2xs">
                    {{ order.fulfillment_method === 'Delivery' ? '🚚' : '🛍️' }}
                  </div>
                  <div>
                    <div class="flex flex-wrap items-center gap-2">
                      <span class="font-mono font-black text-sm text-[#2A1810] dark:text-[#FAF3EE]">#{{ order.order_number }}</span>
                      <span class="text-[11px] px-2.5 py-0.5 rounded-full font-bold"
                        :class="order.fulfillment_method === 'Delivery' ? 'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300'">
                        {{ order.fulfillment_method === 'Delivery' ? (localeStore.isAmharic ? 'ማድረሻ' : 'Delivery') : (localeStore.isAmharic ? 'መውሰጃ' : 'Pickup') }}
                      </span>
                    </div>
                    <div class="text-xs text-[#786B60] dark:text-[#C5B7AF] flex flex-wrap items-center gap-2 mt-1">
                      <span>🕒 {{ order.date_formatted }}</span>
                      <span>&middot;</span>
                      <span class="text-[#DE1B54] font-semibold flex items-center space-x-1">
                        <span>📍</span>
                        <span>{{ localeStore.tBranch(order.branch_name) }} ({{ order.branch_city }})</span>
                      </span>
                    </div>
                  </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                  <span
                    class="px-3 py-1 rounded-full text-xs font-bold capitalize flex items-center space-x-1.5"
                    :class="statusBadgeClass(order.order_status)"
                  >
                    <span v-if="['Pending', 'Confirmed', 'Preparing'].includes(order.order_status)" class="w-1.5 h-1.5 rounded-full bg-current animate-ping"></span>
                    <span>{{ tOrderStatus(order.order_status) }}</span>
                  </span>

                  <!-- Delete History Button for Finished Orders -->
                  <button
                    v-if="['Completed', 'Delivered', 'Cancelled'].includes(order.order_status)"
                    @click.stop="handleDeleteOrder(order)"
                    class="px-3 py-1.5 min-h-[36px] bg-white dark:bg-[#1E120B] border border-gray-200 dark:border-[#382318] hover:border-[#B84B42] hover:bg-[#B84B42]/10 text-xs font-bold text-[#786B60] dark:text-[#C5B7AF] hover:text-[#B84B42] rounded-xl transition shadow-2xs flex items-center space-x-1 cursor-pointer"
                    :title="localeStore.isAmharic ? 'ይህንን የተጠናቀቀ ትዕዛዝ ከመለያ ታሪክ ያስወግዱ' : 'Remove this finished order from your account history'"
                  >
                    <span>🗑️</span>
                    <span class="hidden sm:inline">{{ localeStore.isAmharic ? 'ታሪክ አጥፋ' : 'Delete History' }}</span>
                  </button>

                  <router-link
                    :to="`/orders/${order.order_number}`"
                    class="px-3.5 py-1.5 min-h-[36px] bg-white dark:bg-[#1E120B] border border-gray-200 dark:border-[#382318] hover:border-[#DE1B54] text-xs font-bold text-[#2A1810] dark:text-[#FAF3EE] hover:text-[#DE1B54] rounded-xl transition shadow-2xs flex items-center space-x-1"
                    :title="localeStore.isAmharic ? 'የቀጥታ መከታተያ ገጽ ክፈት' : 'Open dedicated tracking page'"
                  >
                    <span>{{ localeStore.isAmharic ? 'የቀጥታ መከታተያ' : 'Live Tracker' }}</span>
                    <span>→</span>
                  </router-link>
                </div>
              </div>

              <!-- Visual Progress Stepper for each order -->
              <div v-if="order.order_status !== 'Cancelled'" class="bg-white dark:bg-[#1E120B] p-4 sm:p-5 rounded-2xl border border-gray-200 dark:border-[#382318] shadow-2xs space-y-3">
                <div class="flex items-center justify-between text-xs font-bold">
                  <span class="text-[#786B60] dark:text-[#C5B7AF] flex items-center space-x-1.5">
                    <span>{{ localeStore.isAmharic ? '⚡ የትዕዛዝ ሂደት:' : '⚡ Order Progress:' }}</span>
                    <span class="text-[#2A1810] dark:text-[#FAF3EE]">{{ getOrderProgressMessage(order.order_status, order.fulfillment_method) }}</span>
                  </span>
                  <span class="text-[11px] font-mono text-[#DE1B54]">{{ localeStore.isAmharic ? `ደረጃ ${Math.max(1, order.current_step)} ከ 5` : `Step ${Math.max(1, order.current_step)} of 5` }}</span>
                </div>

                <!-- 5-Step Stepper -->
                <div class="relative pt-2 pb-1">
                  <!-- Stepper Track -->
                  <div class="absolute top-5 left-4 right-4 h-1 bg-gray-200 dark:bg-[#382318] -z-0">
                    <div
                      class="h-full bg-[#387A54] transition-all duration-500"
                      :style="{ width: ((Math.max(1, order.current_step) - 1) / 4) * 100 + '%' }"
                    ></div>
                  </div>

                  <!-- Step Circles -->
                  <div class="flex justify-between items-start relative z-10">
                    <div
                      v-for="step in 5"
                      :key="step"
                      class="flex flex-col items-center space-y-1.5 text-center flex-1"
                    >
                      <!-- Circle -->
                      <div
                        class="w-7 h-7 sm:w-8 sm:h-8 rounded-full flex items-center justify-center text-xs font-black transition-all shadow-xs"
                        :class="[
                          isOrderStepCompleted(order.order_status, step)
                            ? 'bg-[#387A54] text-white ring-4 ring-[#387A54]/20'
                            : isOrderStepCurrent(order.order_status, step)
                            ? 'bg-[#DE1B54] text-white ring-4 ring-[#DE1B54]/20 scale-110 animate-pulse'
                            : 'bg-white dark:bg-[#2A1810] border-2 border-gray-300 dark:border-[#4A3225] text-[#786B60] dark:text-[#C5B7AF]'
                        ]"
                      >
                        <span v-if="isOrderStepCompleted(order.order_status, step)">✓</span>
                        <span v-else>{{ step }}</span>
                      </div>

                      <!-- Label -->
                      <span
                        class="text-[10px] sm:text-[11px] font-bold leading-tight"
                        :class="[
                          isOrderStepCompleted(order.order_status, step) || isOrderStepCurrent(order.order_status, step)
                            ? 'text-[#2A1810] dark:text-[#FAF3EE]'
                            : 'text-[#A0938A] dark:text-[#786B60]'
                        ]"
                      >
                        {{ getOrderStepName(step, order.fulfillment_method) }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Cancelled Banner if Order was Cancelled -->
              <div v-else class="p-4 rounded-2xl bg-[#B84B42]/10 border border-[#B84B42]/30 flex items-start space-x-3 text-xs">
                <span class="text-xl">⚠️</span>
                <div class="space-y-0.5">
                  <div class="font-bold text-[#B84B42]">{{ localeStore.isAmharic ? 'ይህ ትዕዛዝ ተሰርዟል' : 'This order has been cancelled' }}</div>
                  <p class="text-[#786B60] dark:text-[#C5B7AF]">
                    {{ localeStore.isAmharic ? 'የተያዘው የክምችት መጠን ተለቋል። ክፍያ ከተፈጸመ፣ ተመላሹ ወደ መጀመሪያው የክፍያ መንገድዎ ይመለሳል።' : 'Stock reservations have been released. If payment was completed, your refund is processed to your original payment method.' }}
                  </p>
                </div>
              </div>

              <!-- Quick Summary Row + Toggle Button -->
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs pt-1">
                <div class="text-[#786B60] dark:text-[#C5B7AF] truncate max-w-md">
                  <strong class="text-[#2A1810] dark:text-[#FAF3EE]">{{ localeStore.isAmharic ? 'ምርቶች:' : 'Items:' }}</strong> {{ order.items_summary }}
                </div>
                <div class="flex items-center space-x-4 shrink-0">
                  <div class="text-right">
                    <span class="text-[#786B60] dark:text-[#C5B7AF] text-[11px]">{{ localeStore.isAmharic ? 'ጠቅላላ:' : 'Total:' }} </span>
                    <span class="font-black text-sm sm:text-base text-[#DE1B54]">{{ localeStore.formatPrice(order.total_amount) }}</span>
                  </div>
                  <button
                    @click="toggleOrderExpansion(order.order_id)"
                    class="px-3 py-1.5 rounded-xl border border-gray-200 dark:border-[#382318] hover:border-[#DE1B54] bg-white dark:bg-[#1E120B] text-xs font-bold text-[#2A1810] dark:text-[#FAF3EE] hover:text-[#DE1B54] transition cursor-pointer flex items-center space-x-1 shadow-2xs"
                  >
                    <span>{{ expandedOrders[order.order_id] ? (localeStore.isAmharic ? 'ዝርዝር ደብቅ' : 'Hide Details') : (localeStore.isAmharic ? 'ዝርዝር እና ደረሰኝ እይ' : 'View Details & Receipt') }}</span>
                    <span>{{ expandedOrders[order.order_id] ? '▲' : '▼' }}</span>
                  </button>
                </div>
              </div>

              <!-- Expandable Detailed Accordion Drawer -->
              <div
                v-if="expandedOrders[order.order_id]"
                class="pt-4 border-t border-gray-200 dark:border-[#382318] space-y-4"
              >
                <!-- Items Table / Grid -->
                <div class="space-y-2">
                  <h4 class="text-xs font-bold uppercase tracking-wider text-[#786B60] dark:text-[#C5B7AF]">{{ localeStore.isAmharic ? 'የታዘዙ ምርቶች' : 'Items Ordered' }}</h4>
                  <div class="divide-y divide-gray-100 dark:divide-[#382318] bg-white dark:bg-[#1E120B] rounded-2xl border border-gray-200 dark:border-[#382318] overflow-hidden">
                    <div
                      v-for="item in order.items"
                      :key="item.order_item_id"
                      class="p-3.5 flex items-center justify-between text-xs gap-3"
                    >
                      <div class="flex items-center space-x-3">
                        <div class="w-9 h-9 rounded-xl bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] flex items-center justify-center text-base">
                          🧁
                        </div>
                        <div>
                          <div class="font-bold text-[#2A1810] dark:text-[#FAF3EE]">{{ localeStore.tProduct(item.product_name) }}</div>
                          <div class="text-[11px] text-[#786B60] dark:text-[#C5B7AF]">
                            {{ localeStore.isAmharic ? 'ብዛት:' : 'Qty:' }} <strong class="text-[#2A1810] dark:text-[#FAF3EE]">{{ item.quantity }}</strong> &times; {{ localeStore.formatPrice(item.unit_price) }}
                          </div>
                        </div>
                      </div>
                      <div class="font-bold text-[#2A1810] dark:text-[#FAF3EE] text-right">
                        {{ localeStore.formatPrice(item.subtotal) }}
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Info Grid (Payment & Delivery) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                  <!-- Payment Details Card -->
                  <div class="p-3.5 rounded-2xl bg-white dark:bg-[#1E120B] border border-gray-200 dark:border-[#382318] space-y-1.5">
                    <div class="font-bold text-[#786B60] dark:text-[#C5B7AF] flex items-center space-x-1.5">
                      <span>{{ localeStore.isAmharic ? '💳 የክፍያ መረጃ' : '💳 Payment Information' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                      <span class="text-[#786B60] dark:text-[#C5B7AF]">{{ localeStore.isAmharic ? 'ዘዴ:' : 'Method:' }}</span>
                      <span class="font-bold text-[#2A1810] dark:text-[#FAF3EE]">{{ tPaymentMethod(order.payment?.payment_method) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                      <span class="text-[#786B60] dark:text-[#C5B7AF]">{{ localeStore.isAmharic ? 'የክፍያ ሁኔታ:' : 'Payment Status:' }}</span>
                      <span
                        class="px-2 py-0.5 rounded-full text-[11px] font-bold"
                        :class="order.payment?.payment_status === 'Paid' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300'"
                      >
                        {{ tPaymentStatus(order.payment?.payment_status) }}
                      </span>
                    </div>
                    <div v-if="order.payment?.transaction_reference" class="flex items-center justify-between">
                      <span class="text-[#786B60] dark:text-[#C5B7AF]">{{ localeStore.isAmharic ? 'የግብይት መለያ:' : 'Tx Reference:' }}</span>
                      <span class="font-mono font-bold text-[#DE1B54] text-[11px]">{{ order.payment.transaction_reference }}</span>
                    </div>
                  </div>

                  <!-- Fulfillment & Location Card -->
                  <div class="p-3.5 rounded-2xl bg-white dark:bg-[#1E120B] border border-gray-200 dark:border-[#382318] space-y-1.5">
                    <div class="font-bold text-[#786B60] dark:text-[#C5B7AF] flex items-center space-x-1.5">
                      <span>{{ order.fulfillment_method === 'Delivery' ? (localeStore.isAmharic ? '🚚 የማድረሻ ዝርዝር' : '🚚 Delivery Details') : (localeStore.isAmharic ? '🏬 የመውሰጃ ዝርዝር' : '🏬 Pickup Details') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                      <span class="text-[#786B60] dark:text-[#C5B7AF]">{{ localeStore.isAmharic ? 'ቅርንጫፍ:' : 'Branch:' }}</span>
                      <span class="font-bold text-[#2A1810] dark:text-[#FAF3EE]">{{ localeStore.tBranch(order.branch_name) }}</span>
                    </div>
                    <div v-if="order.branch_address" class="text-[11px] text-[#786B60] dark:text-[#C5B7AF]">
                      {{ localeStore.isAmharic ? 'አድራሻ:' : 'Address:' }} {{ order.branch_address }}
                    </div>
                    <div v-if="order.branch_phone" class="text-[11px] text-[#DE1B54] font-semibold">
                      {{ localeStore.isAmharic ? 'የደንበኞች ድጋፍ:' : 'Support:' }} <a :href="'tel:' + order.branch_phone" class="hover:underline">{{ order.branch_phone }}</a>
                    </div>
                  </div>
                </div>

                <!-- Status Progression History Milestones -->
                <div v-if="order.status_history_items && order.status_history_items.length > 0" class="space-y-2">
                  <h4 class="text-xs font-bold uppercase tracking-wider text-[#786B60] dark:text-[#C5B7AF]">{{ localeStore.isAmharic ? 'የሂደት ታሪክ' : 'Milestone Timeline' }}</h4>
                  <div class="p-3.5 rounded-2xl bg-white dark:bg-[#1E120B] border border-gray-200 dark:border-[#382318] space-y-2 text-xs">
                    <div
                      v-for="(hist, hIdx) in order.status_history_items"
                      :key="hist.history_id || hIdx"
                      class="flex items-start space-x-2.5"
                    >
                      <span class="text-emerald-600 dark:text-emerald-400 font-bold shrink-0 mt-0.5">✓</span>
                      <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-1.5">
                          <span class="font-bold text-[#2A1810] dark:text-[#FAF3EE]">{{ tOrderStatus(hist.new_status) }}</span>
                          <span class="text-[10px] text-[#786B60] dark:text-[#C5B7AF]">({{ formatDate(hist.changed_at) }})</span>
                        </div>
                        <div v-if="hist.note" class="text-[11px] text-[#786B60] dark:text-[#C5B7AF] mt-0.5 italic">
                          "{{ hist.note }}"
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 2. Custom Cakes & Quotes Tab -->
        <div v-else-if="activeTab === 'custom_cakes'" class="space-y-6">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#E6DED3] pb-4">
            <div>
              <h2 class="text-xl font-serif font-bold text-[#29231F] dark:text-[#FAF3EE]">{{ localeStore.isAmharic ? 'የልዩ ትዕዛዝ ኬኮች እና የዋጋ ግምቶች' : 'Custom Cake Requests & Quotes' }}</h2>
              <p class="text-xs text-[#786B60] dark:text-[#C5B7AF] mt-0.5">{{ localeStore.isAmharic ? 'የዳቦ ጋጋሪውን የዋጋ ግምት ይገምግሙ፣ በ50% ቅድመ ክፍያ ያረጋግጡ እና ሂደቱን ይከታተሉ' : 'Review baker quotes, confirm with 50% deposit, and track progress' }}</p>
            </div>
            <router-link
              to="/custom-cake"
              class="inline-flex items-center space-x-1.5 px-4 py-2 min-h-[38px] bg-[#DE1B54] hover:bg-[#C51345] text-white text-xs font-bold rounded-xl transition shadow-xs active:scale-95"
            >
              <span>{{ localeStore.isAmharic ? '+ አዲስ ኬክ ይንደፉ' : '+ Design New Cake' }}</span>
              <span>→</span>
            </router-link>
          </div>

          <div v-if="customCakes.length === 0" class="p-8 sm:p-10 text-center bg-[#FAF7F5] dark:bg-[#2A1810] rounded-3xl border border-gray-200 dark:border-[#382318] space-y-3">
            <div class="text-5xl">🎂</div>
            <h3 class="font-serif font-bold text-base text-[#2A1810] dark:text-[#FAF3EE]">{{ localeStore.isAmharic ? 'ምንም የልዩ ትዕዛዝ ኬክ ጥያቄ አልተገኘም' : 'No Custom Cake Requests Found' }}</h3>
            <p class="text-xs text-[#786B60] dark:text-[#C5B7AF] max-w-sm mx-auto leading-relaxed">
              {{ localeStore.isAmharic ? 'ሰርግ፣ ልደት ወይም የድርጅት በዓል እያዘጋጁ ነው? የእርስዎን ልዩ ኬክ ከዋና ዋና ዳቦ ጋጋሪዎቻችን ጋር ይንደፉ።' : 'Planning a wedding, birthday, or corporate celebration? Design your bespoke centerpiece with our master bakers.' }}
            </p>
            <router-link
              to="/custom-cake"
              class="inline-block px-5 py-2.5 min-h-[40px] bg-[#DE1B54] hover:bg-[#C51345] text-white font-bold text-xs rounded-xl shadow-xs transition active:scale-95"
            >
              {{ localeStore.isAmharic ? 'ኬክ ስቱዲዮ ክፈት →' : 'Launch Cake Studio →' }}
            </router-link>
          </div>

          <div v-else class="space-y-4">
            <div
              v-for="cake in customCakes"
              :key="cake.request_id"
              class="p-5 rounded-2xl border border-gray-200 dark:border-[#382318] bg-[#FAF7F5] dark:bg-[#2A1810] space-y-4 shadow-2xs"
            >
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-200 dark:border-[#382318] pb-3">
                <div class="space-y-0.5">
                  <div class="flex items-center space-x-2">
                    <span class="font-mono font-bold text-sm text-[#2A1810] dark:text-[#FAF3EE]">#{{ cake.request_number }}</span>
                    <span class="text-xs text-[#786B60] dark:text-[#C5B7AF]">({{ localeStore.tBranch(cake.branch?.branch_name || 'Bole') }} {{ localeStore.isAmharic ? 'ቅርንጫፍ' : 'Branch' }})</span>
                  </div>
                  <div class="text-[11px] text-[#786B60] dark:text-[#C5B7AF]">
                    {{ localeStore.isAmharic ? 'የሚያስፈልግበት ቀን:' : 'Needed by:' }} <strong class="text-[#2A1810] dark:text-[#FAF3EE]">{{ cake.needed_by }}</strong> &middot; {{ localeStore.isAmharic ? 'የቀረበበት:' : 'Submitted' }} {{ formatDate(cake.created_at) }}
                  </div>
                </div>

                <div class="flex items-center space-x-2">
                  <span
                    class="px-3 py-1 rounded-full text-xs font-bold capitalize"
                    :class="customCakeStatusBadge(cake.status)"
                  >
                    {{ tOrderStatus(cake.status) }}
                  </span>
                </div>
              </div>

              <!-- Details Grid -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs text-[#2A1810] dark:text-[#FAF3EE] bg-white dark:bg-[#1E120B] p-4 rounded-xl border border-gray-200 dark:border-[#382318]">
                <div>
                  <span class="text-[#786B60] dark:text-[#C5B7AF]">{{ localeStore.isAmharic ? 'መጠን እና ጣዕም:' : 'Size & Flavor:' }}</span>
                  <div class="font-semibold">{{ cake.cake_size }} &middot; {{ cake.flavor }}</div>
                </div>
                <div>
                  <span class="text-[#786B60] dark:text-[#C5B7AF]">{{ localeStore.isAmharic ? 'በኬኩ ላይ የሚጻፍ መልዕክት:' : 'Message on Cake:' }}</span>
                  <div class="font-semibold italic">{{ cake.message || (localeStore.isAmharic ? 'የለም' : 'None') }}</div>
                </div>
                <div class="sm:col-span-2" v-if="cake.design">
                  <span class="text-[#786B60] dark:text-[#C5B7AF]">{{ localeStore.isAmharic ? 'የንድፍ ዝርዝር:' : 'Design Brief:' }}</span>
                  <div class="font-medium text-[#2A1810] dark:text-[#FAF3EE] mt-0.5">{{ cake.design }}</div>
                </div>
              </div>

              <!-- Attached Reference Photos Gallery -->
              <div v-if="cake.images && cake.images.length > 0" class="space-y-1.5">
                <span class="text-[10px] uppercase font-bold text-[#786B60] dark:text-[#C5B7AF] block">{{ localeStore.isAmharic ? `የእርስዎ ማጣቀሻ ፎቶዎች (${cake.images.length}):` : `Your Reference Photos (${cake.images.length}):` }}</span>
                <div class="flex space-x-2 overflow-x-auto pb-1">
                  <img
                    v-for="(img, idx) in cake.images"
                    :key="idx"
                    :src="img.image_url"
                    alt="Reference photo"
                    @click="zoomImage = img.image_url"
                    class="w-14 h-14 object-cover rounded-xl border border-gray-200 dark:border-[#382318] shadow-2xs shrink-0 cursor-pointer hover:opacity-90 hover:scale-105 transition-transform"
                    :title="localeStore.isAmharic ? 'ፎቶውን ለማጉላት ይጫኑ' : 'Click to zoom photo'"
                  />
                </div>
              </div>

              <!-- Visual Lifecycle Progress Stepper -->
              <div class="p-3 bg-white dark:bg-[#1E120B] rounded-xl border border-gray-200 dark:border-[#382318] space-y-2">
                <div class="flex items-center justify-between text-[11px] font-bold text-[#786B60] dark:text-[#C5B7AF]">
                  <span>{{ localeStore.isAmharic ? 'የሂደት ቅደም ተከተል' : 'Progress Lifecycle' }}</span>
                  <span class="text-[#DE1B54]">{{ tOrderStatus(cake.status) }}</span>
                </div>
                <div class="grid grid-cols-5 gap-1.5 text-center text-[10px]">
                  <div class="p-1 rounded-lg" :class="isCakeStepActive(cake.status, 1) ? 'bg-[#387A54] text-white font-bold' : 'bg-gray-100 dark:bg-[#25150E] text-gray-500'">
                    {{ localeStore.isAmharic ? '1. ማጠቃለያ' : '1. Brief' }}
                  </div>
                  <div class="p-1 rounded-lg" :class="isCakeStepActive(cake.status, 2) ? 'bg-[#387A54] text-white font-bold' : (cake.status === 'Submitted' ? 'bg-amber-100 text-amber-800 font-bold' : 'bg-gray-100 dark:bg-[#25150E] text-gray-500')">
                    {{ localeStore.isAmharic ? '2. የዋጋ ግምት' : '2. Quote' }}
                  </div>
                  <div class="p-1 rounded-lg" :class="isCakeStepActive(cake.status, 3) ? 'bg-[#387A54] text-white font-bold' : 'bg-gray-100 dark:bg-[#25150E] text-gray-500'">
                    {{ localeStore.isAmharic ? '3. ተረጋግጧል' : '3. Confirmed' }}
                  </div>
                  <div class="p-1 rounded-lg" :class="isCakeStepActive(cake.status, 4) ? (isCakeStepActive(cake.status, 5) ? 'bg-[#387A54] text-white' : 'bg-[#DE1B54] text-white font-bold') : 'bg-gray-100 dark:bg-[#25150E] text-gray-500'">
                    {{ localeStore.isAmharic ? '4. መጋገር' : '4. Baking' }}
                  </div>
                  <div class="p-1 rounded-lg" :class="isCakeStepActive(cake.status, 5) ? 'bg-[#387A54] text-white font-bold' : 'bg-gray-100 dark:bg-[#25150E] text-gray-500'">
                    {{ localeStore.isAmharic ? '5. ዝግጁ' : '5. Ready' }}
                  </div>
                </div>
              </div>

              <!-- Rejection Notice -->
              <div v-if="cake.status === 'Rejected'" class="p-3 bg-[#B84B42]/10 border border-[#B84B42]/30 rounded-xl text-xs text-[#B84B42] flex items-center space-x-2">
                <span>⚠️</span>
                <div>
                  <strong>{{ localeStore.isAmharic ? 'በዳቦ ጋጋሪው አልተፈቀደም:' : 'Declined by Baker:' }}</strong> {{ cake.rejection_reason || (localeStore.isAmharic ? 'ለተመረጠው ቀን የዳቦ መጋገሪያው አቅም ሞልቷል።' : 'Baker capacity exceeded for the selected date.') }}
                </div>
              </div>

              <!-- Baker Quote Section (When Quoted) -->
              <div v-else-if="cake.status === 'Quoted'" class="p-4 bg-white dark:bg-[#1E120B] rounded-2xl border-2 border-[#DE1B54]/40 space-y-3">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-[#382318] pb-2">
                  <div class="flex items-center space-x-1.5">
                    <span class="text-base">📜</span>
                    <span class="text-xs font-bold text-[#2A1810] dark:text-[#FAF3EE] uppercase tracking-wider">
                      {{ localeStore.isAmharic ? 'ይፋዊ የዋጋ ግምት' : 'Official Baker Quotation' }}
                    </span>
                  </div>
                  <span class="px-2.5 py-0.5 rounded font-mono font-bold text-[11px] bg-amber-100 dark:bg-amber-950/60 text-amber-900 dark:text-amber-300 border border-amber-300 dark:border-amber-700">
                    QT-{{ cake.request_number }}
                  </span>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                  <div>
                    <div class="text-xs text-[#786B60] dark:text-[#C5B7AF] font-medium">{{ localeStore.isAmharic ? 'የዋና ዳቦ ጋጋሪው የዋጋ ግምት:' : "Head Baker's Quotation:" }}</div>
                    <div class="text-2xl font-black text-[#DE1B54]">
                      {{ localeStore.formatPrice(cake.quoted_price) }}
                    </div>
                    <div class="text-[11px] text-[#387A54] font-bold mt-0.5">
                      {{ localeStore.isAmharic ? `✓ ለማረጋገጥ ${cake.deposit_required_percentage || 50}% ቅድመ ክፍያ፡ ${localeStore.formatPrice((Number(cake.quoted_price) * (Number(cake.deposit_required_percentage) || 50)) / 100)}` : `✓ ${cake.deposit_required_percentage || 50}% Deposit to Confirm: ETB ${((Number(cake.quoted_price) * (Number(cake.deposit_required_percentage) || 50)) / 100).toLocaleString()}` }}
                    </div>
                  </div>

                  <div class="flex flex-wrap items-center gap-2">
                    <button
                      type="button"
                      @click="openQuotationModal(cake)"
                      class="px-3.5 py-2.5 min-h-[40px] bg-white dark:bg-[#25150E] border border-[#BA5420] text-[#BA5420] hover:bg-orange-50 dark:hover:bg-[#341822] text-xs font-bold rounded-xl shadow-xs transition cursor-pointer flex items-center space-x-1"
                    >
                      <span>📄</span>
                      <span>{{ localeStore.isAmharic ? 'ይፋዊ ደረሰኝ እይ' : 'View Official Quote Slip' }}</span>
                    </button>
                    <button
                      @click="acceptQuote(cake, 'deposit')"
                      class="px-4 py-2.5 min-h-[40px] bg-rose-50 dark:bg-[#341822] border border-[#DE1B54] text-[#DE1B54] hover:bg-rose-100 text-xs font-bold rounded-xl shadow-xs transition cursor-pointer"
                    >
                      {{ localeStore.isAmharic ? `ቅድመ ክፍያ ይክፈሉ (${localeStore.formatPrice((Number(cake.quoted_price) * (Number(cake.deposit_required_percentage) || 50)) / 100)})` : `Pay Deposit (ETB ${((Number(cake.quoted_price) * (Number(cake.deposit_required_percentage) || 50)) / 100).toLocaleString()})` }}
                    </button>
                    <button
                      @click="acceptQuote(cake, 'full')"
                      class="px-4 py-2.5 min-h-[40px] bg-[#DE1B54] hover:bg-[#C51345] text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer"
                    >
                      {{ localeStore.isAmharic ? `ሙሉውን ይክፈሉ (${localeStore.formatPrice(cake.quoted_price)})` : `Pay Full (ETB ${Number(cake.quoted_price).toLocaleString()})` }}
                    </button>
                    <button
                      @click="cancelCake(cake)"
                      class="px-3 py-2 min-h-[40px] bg-gray-100 dark:bg-[#25150E] hover:bg-gray-200 text-gray-700 dark:text-gray-300 text-xs font-semibold rounded-xl cursor-pointer"
                    >
                      {{ localeStore.isAmharic ? 'አልፈልግም' : 'Decline' }}
                    </button>
                  </div>
                </div>
              </div>

              <!-- Preparing Banner -->
              <div v-else-if="cake.status === 'Preparing'" class="p-3.5 bg-rose-50 dark:bg-[#341822] border border-rose-200 dark:border-[#58263A] rounded-xl text-xs space-y-1">
                <div class="font-bold text-[#DE1B54] flex items-center space-x-1.5">
                  <span>🍳</span>
                  <span>{{ localeStore.isAmharic ? 'ኬኩን የመጋገር እና የማስዋብ ስራ በሂደት ላይ ነው!' : 'Artisan Baking & Decorating in Progress!' }}</span>
                </div>
                <p class="text-[11px] text-[#6E5D53] dark:text-[#C5B7AF]">
                  {{ localeStore.isAmharic ? `በ${localeStore.tBranch(cake.branch?.branch_name || 'Bole')} የሚገኘው ባለሙያችን የእርስዎን ልዩ ኬክ በሰጡት መመሪያ መሰረት በማዘጋጀት ላይ ነው።` : `Our master pastry chef at ${cake.branch?.branch_name || 'Bole'} is currently crafting and decorating your custom centerpiece according to your brief.` }}
                </p>
              </div>

              <!-- Ready Banner -->
              <div v-else-if="cake.status === 'Ready'" class="p-3.5 bg-purple-50 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-800 rounded-xl text-xs space-y-1">
                <div class="font-bold text-purple-900 dark:text-purple-300 flex items-center space-x-1.5">
                  <span>🎂</span>
                  <span>{{ localeStore.isAmharic ? 'የእርስዎ ልዩ ኬክ ተዘጋጅቶ አልቋል!' : 'Your Custom Cake is Ready!' }}</span>
                </div>
                <p class="text-[11px] text-purple-800 dark:text-purple-200">
                  {{ localeStore.isAmharic ? `ኬክዎ በ${localeStore.tBranch(cake.branch?.branch_name || 'Bole')} ቅርንጫፍ ተዘጋጅቶ በቅዝቃዜ ተቀምጧል። ለመውሰድ ወይም ለማድረስ ዝግጁ ነው።` : `Your cake is boxed in chilled storage at ${cake.branch?.branch_name || 'Bole'}. Available for collection or dispatch.` }}
                </p>
              </div>

              <!-- Delivered / Completed Banner -->
              <div v-else-if="cake.status === 'Delivered' || cake.status === 'Picked up'" class="p-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-xl text-xs text-emerald-800 dark:text-emerald-300 font-bold flex items-center space-x-2">
                <span>✓</span>
                <span>{{ localeStore.isAmharic ? '✓ የበዓሉ ኬክ ተረክቧል፣ ትዕዛዙም ተጠናቋል! ዝግጅትዎ አስደሳች እንደነበር ተስፋ እናደርጋለን።' : 'Celebration Cake Handed Over & Order Complete! We hope it made your event unforgettable.' }}</span>
              </div>

              <!-- Confirmed status or Under Review -->
              <div v-else class="pt-1 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                <span class="text-[#387A54] font-bold flex items-center space-x-1" v-if="cake.status === 'Confirmed'">
                  <span>✓</span>
                  <span>{{ localeStore.isAmharic ? `✓ ቅድመ ክፍያ ተረጋግጧል! ዋና ዳቦ ጋጋሪው ለ${cake.needed_by} ማዘጋጀቱን አረጋግጧል።` : `✓ Deposit confirmed! Head baker has locked in kitchen preparation for ${cake.needed_by}.` }}</span>
                </span>
                <span class="text-[#B98235] font-medium flex items-center space-x-1" v-else>
                  <span>⏳</span>
                  <span>{{ localeStore.isAmharic ? '⏳ በቅርንጫፉ ዋና ባለሙያ በመገምገም ላይ። ይፋዊ የዋጋ ግምት በ2-4 ሰዓታት ውስጥ ይሰጣል።' : 'Under review by branch master decorator. Official quote expected within 2-4 hours.' }}</span>
                </span>
                <button
                  @click="cancelCake(cake)"
                  class="text-[11px] text-[#B84B42] hover:underline font-semibold cursor-pointer text-left sm:text-right"
                >
                  {{ localeStore.isAmharic ? 'ጥያቄውን ሰርዝ' : 'Cancel Request' }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Wishlist & Favorites Tab -->
        <div v-else-if="activeTab === 'wishlist'" class="space-y-6">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 dark:border-[#382318] pb-4">
            <div>
              <div class="flex items-center space-x-2">
                <h2 class="text-xl font-serif font-bold text-[#2A1810] dark:text-[#FAF3EE]">{{ localeStore.isAmharic ? 'የምኞት ዝርዝሬ እና ተወዳጆች' : 'My Wishlist & Favorites' }}</h2>
                <span v-if="wishlistStore.count > 0" class="px-2.5 py-0.5 rounded-full bg-rose-100 dark:bg-rose-900/60 text-[#DE1B54] text-xs font-bold">
                  {{ wishlistStore.count }} {{ localeStore.isAmharic ? 'እቃዎች' : 'items' }}
                </span>
              </div>
              <p class="text-xs text-[#786B60] dark:text-[#A8988E] mt-0.5">{{ localeStore.isAmharic ? 'በSweetBakes የመረጧቸው እና ያስቀመጧቸው ልዩ ኬኮች፣ ዳቦዎች እና ጣፋጮች' : 'Your handpicked artisan cakes, breads, and treats saved across SweetBakes' }}</p>
            </div>

            <div class="flex items-center space-x-2">
              <button
                v-if="wishlistStore.count > 0"
                @click="clearAllWishlist"
                class="px-3 py-1.5 min-h-[38px] text-xs font-semibold text-[#B84B42] hover:bg-rose-50 dark:hover:bg-[#341822] rounded-xl border border-rose-200 dark:border-[#B84B42]/30 transition cursor-pointer"
              >
                {{ localeStore.isAmharic ? 'ሁሉንም አጽዳ' : 'Clear All' }}
              </button>
              <router-link
                to="/shop"
                class="px-3.5 py-1.5 min-h-[38px] bg-[#DE1B54] hover:bg-[#C51345] text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center space-x-1.5 cursor-pointer"
              >
                <span>{{ localeStore.isAmharic ? 'ሱቁን ጎብኝ →' : 'Explore Shop →' }}</span>
              </router-link>
            </div>
          </div>

          <!-- Empty Wishlist State -->
          <div v-if="wishlistProducts.length === 0" class="py-16 text-center bg-[#FAF7F5] dark:bg-[#20120B] rounded-2xl border border-gray-100 dark:border-[#382318] p-8 space-y-4">
            <div class="text-4xl">❤️</div>
            <h3 class="font-serif font-bold text-lg text-[#2A1810] dark:text-[#FAF3EE]">{{ localeStore.isAmharic ? 'የምኞት ዝርዝርዎ ባዶ ነው' : 'Your Wishlist is Empty' }}</h3>
            <p class="text-xs text-[#786B60] dark:text-[#A8988E] max-w-md mx-auto leading-relaxed">
              {{ localeStore.isAmharic ? 'በሱቃችን ወይም በመነሻ ገጻችን ላይ የሚወዱትን ኬክ ወይም ኬክ ሲያዩ፣ ለቀጣይ ፈጣን ትዕዛዝ ለማስቀመጥ የልብ ቅርጽ ምልክቱን ይጫኑ።' : 'Whenever you see a cake or pastry you love in our shop or homepage, click the heart icon to save it here for fast ordering.' }}
            </p>
            <router-link
              to="/shop"
              class="inline-block px-5 py-2.5 min-h-[42px] bg-[#DE1B54] hover:bg-[#C51345] text-white text-xs font-bold rounded-xl shadow-xs transition"
            >
              {{ localeStore.isAmharic ? 'ጣፋጮችን ይመልከቱ' : 'Browse Bakery Delights' }}
            </router-link>
          </div>

          <!-- Wishlist Grid -->
          <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
              v-for="item in wishlistProducts"
              :key="item.product_id"
              class="bg-white dark:bg-[#20120B] rounded-2xl border border-gray-100 dark:border-[#382318] p-4 flex flex-col justify-between hover:shadow-lg hover:border-rose-300 dark:hover:border-rose-900/50 transition duration-200 group"
            >
              <div class="space-y-3">
                <div class="relative aspect-4/3 rounded-xl overflow-hidden bg-[#FAF7F5] dark:bg-[#2A1810]">
                  <img
                    :src="getWishlistImage(item)"
                    :alt="item.product_name"
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                  />
                  <span class="absolute top-2 left-2 px-2 py-0.5 rounded-full text-[10px] font-bold bg-white/90 dark:bg-black/70 backdrop-blur-xs text-[#2A1810] dark:text-[#FAF3EE]">
                    {{ item.category?.category_name || item.category_name || 'Artisan' }}
                  </span>
                  <button
                    type="button"
                    @click.stop="removeWishlistItem(item.product_id)"
                    class="absolute top-2 right-2 w-7 h-7 rounded-full bg-white/95 dark:bg-black/80 text-rose-500 hover:text-rose-700 flex items-center justify-center text-xs shadow-xs hover:scale-110 transition cursor-pointer"
                    :title="localeStore.isAmharic ? 'ከምኞት ዝርዝር አስወግድ' : 'Remove from wishlist'"
                  >
                    ✕
                  </button>
                </div>

                <div>
                  <router-link
                    :to="`/product/${item.product_id}`"
                    class="font-serif font-bold text-sm text-[#2A1810] dark:text-[#FAF3EE] hover:text-[#DE1B54] transition line-clamp-1 block"
                  >
                    {{ localeStore.tProduct(item.product_name) }}
                  </router-link>
                  <p class="text-[11px] text-[#786B60] dark:text-[#A8988E] line-clamp-2 mt-1">
                    {{ item.description || (localeStore.isAmharic ? 'በጣፋጭ ሁኔታ የተዘጋጀ ትኩስ የዳቦ ቤት ምርት።' : 'Deliciously fresh artisan bakery item.') }}
                  </p>
                </div>
              </div>

              <div class="pt-3 border-t border-gray-100 dark:border-[#382318] mt-3 flex items-center justify-between gap-2">
                <div>
                  <div class="text-[9px] uppercase tracking-wider text-gray-400 font-semibold">{{ localeStore.isAmharic ? 'ዋጋ' : 'Price' }}</div>
                  <div class="text-sm font-black text-[#DE1B54]">
                    {{ localeStore.formatPrice(item.resolved_price || item.base_price || 0) }}
                  </div>
                </div>

                <button
                  type="button"
                  @click="addWishlistToCart(item)"
                  class="px-3.5 py-2 min-h-[38px] bg-[#DE1B54] hover:bg-[#C51345] text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center space-x-1.5 cursor-pointer active:scale-95"
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                  <span>{{ localeStore.isAmharic ? 'ወደ ቅርጫት ጨምር' : 'Add to Bag' }}</span>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- 3. Addresses Tab -->
        <div v-else-if="activeTab === 'addresses'" class="space-y-6">
          <div class="flex items-center justify-between border-b border-gray-100 dark:border-[#382318] pb-4">
            <div>
              <h2 class="text-xl font-serif font-bold text-[#2A1810] dark:text-[#FAF3EE]">{{ localeStore.isAmharic ? 'የተቀመጡ የማድረሻ አድራሻዎች' : 'Saved Delivery Addresses' }}</h2>
              <p class="text-xs text-[#786B60] dark:text-[#A8988E] mt-0.5">{{ localeStore.isAmharic ? 'በአዲስ አበባ ዙሪያ ለማድረስ የሚያገለግሉ አድራሻዎች' : 'Addresses used for dispatch across Addis Ababa' }}</p>
            </div>
            <button
              @click="showAddressModal = true"
              class="px-3.5 py-2 min-h-[40px] bg-[#DE1B54] hover:bg-[#C51345] text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer"
            >
              {{ localeStore.isAmharic ? '+ አድራሻ ጨምር' : '+ Add Address' }}
            </button>
          </div>

          <!-- Delivery Addresses Grid / Empty State -->
          <div v-if="addresses.length === 0" class="py-12 text-center bg-[#FAF7F5] dark:bg-[#20120B] rounded-2xl border border-gray-100 dark:border-[#382318] p-6 space-y-3">
            <div class="text-3xl">📍</div>
            <div class="font-serif font-bold text-base text-[#2A1810] dark:text-[#FAF3EE]">{{ localeStore.isAmharic ? 'እስካሁን ምንም የተቀመጠ የማድረሻ አድራሻ የለም' : 'No Saved Delivery Addresses Yet' }}</div>
            <p class="text-xs text-[#786B60] dark:text-[#A8988E] max-w-sm mx-auto">
              {{ localeStore.isAmharic ? 'ለፈጣን ክፍያ የቤት፣ የቢሮ ወይም የበዓል ቦታ አድራሻዎን ያስቀምጡ።' : 'Add your home, office, or celebration venue address for faster one-click checkout.' }}
            </p>
            <button
              @click="showAddressModal = true"
              class="px-4 py-2 bg-[#DE1B54] hover:bg-[#C51345] text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer"
            >
              {{ localeStore.isAmharic ? '+ የመጀመሪያ አድራሻ ጨምር' : '+ Add First Address' }}
            </button>
          </div>

          <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div
              v-for="addr in addresses"
              :key="addr.address_id"
              class="p-5 rounded-2xl border space-y-2 text-xs relative transition"
              :class="addr.is_default
                ? 'bg-rose-50/40 dark:bg-[#20120B] border-2 border-rose-200 dark:border-[#4A2518]'
                : 'bg-white dark:bg-[#20120B] border border-gray-100 dark:border-[#382318]'"
            >
              <div class="flex items-center justify-between">
                <span class="font-bold text-[#2A1810] dark:text-[#FAF3EE] flex items-center space-x-1.5">
                  <span>{{ addr.is_default ? '🏠' : '📍' }}</span>
                  <span>{{ addr.region || 'Addis Ababa' }} {{ localeStore.isAmharic ? 'አድራሻ' : 'Address' }}</span>
                </span>
                <span
                  v-if="addr.is_default"
                  class="text-[10px] bg-[#387A54]/15 text-[#387A54] font-bold px-2 py-0.5 rounded-full"
                >
                  {{ localeStore.isAmharic ? 'ነባሪ' : 'Default' }}
                </span>
                <button
                  v-else
                  @click="makeDefaultAddress(addr.address_id)"
                  class="text-[10px] text-[#786B60] hover:text-[#DE1B54] font-semibold cursor-pointer"
                >
                  {{ localeStore.isAmharic ? 'ነባሪ አድርግ' : 'Set as Default' }}
                </button>
              </div>

              <p class="text-[#786B60] dark:text-[#A8988E] leading-relaxed">
                {{ addr.street }} &middot; {{ addr.city || 'Addis Ababa' }}
                <span v-if="addr.notes" class="block text-[11px] text-gray-500 italic mt-0.5">{{ addr.notes }}</span>
              </p>

              <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-[#382318]">
                <span class="text-[#786B60] dark:text-[#A8988E] text-[11px]">{{ formatDate(addr.created_at) }}</span>
                <button
                  @click="deleteAddress(addr.address_id)"
                  class="text-[11px] text-[#B84B42] font-semibold cursor-pointer hover:underline"
                >
                  {{ localeStore.isAmharic ? 'አስወግድ' : 'Remove' }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- 4. Profile & Security Tab -->
        <div v-else-if="activeTab === 'settings'" class="space-y-6">
          <div class="border-b border-gray-100 dark:border-[#382318] pb-4">
            <h2 class="text-xl font-serif font-bold text-[#2A1810] dark:text-[#FAF3EE]">{{ localeStore.isAmharic ? 'የመገለጫ እና የደህንነት ቅንብሮች' : 'Profile & Security Settings' }}</h2>
            <p class="text-xs text-[#786B60] dark:text-[#A8988E] mt-0.5">{{ localeStore.isAmharic ? 'የግል መረጃዎን እና የስልክ ማረጋገጫዎን ያስተዳድሩ' : 'Manage your personal credentials and phone verification' }}</p>
          </div>

          <form @submit.prevent="saveProfile" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-[#2A1810] dark:text-[#FAF3EE] mb-1">{{ localeStore.isAmharic ? 'ሙሉ ስም' : 'Full Name' }}</label>
                <input
                  v-model="editName"
                  type="text"
                  class="w-full bg-[#FAF7F5] dark:bg-[#1E120B] border border-gray-200 dark:border-[#382318] rounded-xl px-4 py-2.5 text-xs text-[#2A1810] dark:text-[#FAF3EE] focus:outline-none focus:ring-1 focus:ring-[#DE1B54]"
                  required
                />
              </div>

              <div>
                <label class="block text-xs font-bold text-[#2A1810] dark:text-[#FAF3EE] mb-1">{{ localeStore.isAmharic ? 'ኢሜይል አድራሻ' : 'Email Address' }}</label>
                <input
                  :value="userEmail"
                  disabled
                  class="w-full bg-gray-100 dark:bg-[#25150E] border border-gray-200 dark:border-[#382318] rounded-xl px-4 py-2.5 text-xs text-gray-500 cursor-not-allowed"
                />
                <span class="text-[10px] text-[#786B60] dark:text-[#A8988E]">{{ localeStore.isAmharic ? 'ለመለያ ደህንነት ሲባል ኢሜይልን በቀጥታ መቀየር አይቻልም።' : 'Email cannot be changed directly for account security.' }}</span>
              </div>

              <div>
                <label class="block text-xs font-bold text-[#2A1810] dark:text-[#FAF3EE] mb-1">{{ localeStore.isAmharic ? 'የኢትዮጵያ ስልክ ቁጥር' : 'Ethiopian Phone Number' }}</label>
                <input
                  v-model="editPhone"
                  type="text"
                  placeholder="+251 9XX XXX XXX"
                  class="w-full bg-[#FAF7F5] dark:bg-[#1E120B] border border-gray-200 dark:border-[#382318] rounded-xl px-4 py-2.5 text-xs text-[#2A1810] dark:text-[#FAF3EE] focus:outline-none focus:ring-1 focus:ring-[#DE1B54]"
                />
                <span class="text-[10px] text-[#786B60] dark:text-[#A8988E]">{{ localeStore.isAmharic ? 'ትዕዛዝዎን ለማድረስ ከማስተባበሪያ ቡድናችን ጋር ለመገናኘት ያገለግላል።' : 'Used by bakery dispatchers for order delivery coordination.' }}</span>
              </div>

              <div>
                <label class="block text-xs font-bold text-[#2A1810] dark:text-[#FAF3EE] mb-1">{{ localeStore.isAmharic ? 'ተመራጭ ቋንቋ' : 'Preferred Language' }}</label>
                <select
                  v-model="localeStore.currentLocale"
                  class="w-full bg-[#FAF7F5] dark:bg-[#1E120B] border border-gray-200 dark:border-[#382318] rounded-xl px-4 py-2.5 text-xs text-[#2A1810] dark:text-[#FAF3EE] focus:outline-none focus:ring-1 focus:ring-[#DE1B54]"
                >
                  <option value="en">English (US)</option>
                  <option value="am">አማርኛ (Amharic)</option>
                </select>
              </div>
            </div>

            <div class="pt-2">
              <button
                type="submit"
                :disabled="savingProfile"
                class="px-6 py-2.5 min-h-[40px] bg-[#DE1B54] hover:bg-[#C51345] disabled:opacity-50 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer"
              >
                {{ savingProfile ? (localeStore.isAmharic ? 'በማስቀመጥ ላይ...' : 'Saving Changes...') : (localeStore.isAmharic ? 'ለውጦችን አስቀምጥ' : 'Save Profile Changes') }}
              </button>
            </div>
          </form>

          <!-- Password Reset Box -->
          <div class="pt-6 border-t border-gray-100 dark:border-[#382318] space-y-3">
            <h3 class="text-sm font-serif font-bold text-[#2A1810] dark:text-[#FAF3EE]">{{ localeStore.isAmharic ? 'ደህንነት እና የይለፍ ቃል' : 'Security & Password' }}</h3>
            <p class="text-xs text-[#786B60] dark:text-[#A8988E]">{{ localeStore.isAmharic ? 'መለያዎን እና የትዕዛዝ ታሪክዎን ለመጠበቅ የይለፍ ቃልዎን ይቀይሩ።' : 'Change password to protect your account and saved order history.' }}</p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div>
                <label class="block text-xs font-bold text-[#2A1810] dark:text-[#FAF3EE] mb-1">{{ localeStore.isAmharic ? 'የአሁኑ የይለፍ ቃል' : 'Current Password' }}</label>
                <input
                  v-model="currentPassword"
                  type="password"
                  :placeholder="localeStore.isAmharic ? 'የአሁኑ የይለፍ ቃል' : 'Current password'"
                  class="w-full bg-[#FAF7F5] dark:bg-[#1E120B] border border-gray-200 dark:border-[#382318] rounded-xl px-4 py-2 text-xs text-[#2A1810] dark:text-[#FAF3EE]"
                />
              </div>
              <div>
                <label class="block text-xs font-bold text-[#2A1810] dark:text-[#FAF3EE] mb-1">{{ localeStore.isAmharic ? 'አዲስ የይለፍ ቃል' : 'New Password' }}</label>
                <input
                  v-model="newPassword"
                  type="password"
                  :placeholder="localeStore.isAmharic ? 'ቢያንስ 8 ፊደላት' : 'Min 8 characters'"
                  class="w-full bg-[#FAF7F5] dark:bg-[#1E120B] border border-gray-200 dark:border-[#382318] rounded-xl px-4 py-2 text-xs text-[#2A1810] dark:text-[#FAF3EE]"
                />
              </div>
              <div>
                <label class="block text-xs font-bold text-[#2A1810] dark:text-[#FAF3EE] mb-1">{{ localeStore.isAmharic ? 'አዲሱን የይለፍ ቃል ያረጋግጡ' : 'Confirm New Password' }}</label>
                <input
                  v-model="confirmPassword"
                  type="password"
                  :placeholder="localeStore.isAmharic ? 'አዲሱን የይለፍ ቃል በድጋሚ ያስገቡ' : 'Re-type new password'"
                  class="w-full bg-[#FAF7F5] dark:bg-[#1E120B] border border-gray-200 dark:border-[#382318] rounded-xl px-4 py-2 text-xs text-[#2A1810] dark:text-[#FAF3EE]"
                />
              </div>
            </div>
            <button
              @click="updatePassword"
              :disabled="updatingPassword"
              class="px-5 py-2 min-h-[40px] bg-white dark:bg-[#20120B] border border-gray-200 dark:border-[#382318] hover:border-[#DE1B54] text-[#2A1810] dark:text-[#FAF3EE] text-xs font-bold rounded-xl shadow-2xs transition cursor-pointer disabled:opacity-50"
            >
              {{ updatingPassword ? (localeStore.isAmharic ? 'በማዘመን ላይ...' : 'Updating...') : (localeStore.isAmharic ? 'የይለፍ ቃል አዘምን' : 'Update Password') }}
            </button>
          </div>
        </div>
      </main>
    </div>

    <!-- Add Delivery Address Modal -->
    <div
      v-if="showAddressModal"
      class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
    >
      <div class="bg-white dark:bg-[#1E120B] rounded-3xl p-6 sm:p-7 max-w-md w-full border border-gray-200 dark:border-[#382318] shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-[#382318] pb-3">
          <div class="flex items-center space-x-2">
            <span class="text-xl">📍</span>
            <h3 class="font-serif font-bold text-base text-[#2A1810] dark:text-[#FAF3EE]">{{ localeStore.isAmharic ? 'የማድረሻ አድራሻ ጨምር' : 'Add Delivery Address' }}</h3>
          </div>
          <button @click="showAddressModal = false" class="text-gray-400 hover:text-black dark:hover:text-white text-sm cursor-pointer">✕</button>
        </div>

        <form @submit.prevent="handleSaveAddress" class="space-y-3.5 text-xs">
          <div>
            <label class="block font-bold text-[#2A1810] dark:text-[#FAF3EE] mb-1">{{ localeStore.isAmharic ? 'መንገድ / ህንፃ / የቤት ቁጥር *' : 'Street / Building / House # *' }}</label>
            <input
              v-model="addressForm.street"
              type="text"
              :placeholder="localeStore.isAmharic ? 'ምሳሌ፡ ካሜሩን ጎዳና፣ ኤድና ሞል አጠገብ፣ የቤት ቁጥር 204' : 'e.g. Cameroon St, Near Edna Mall, House 204'"
              class="w-full bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] rounded-xl px-3.5 py-2 text-xs text-[#2A1810] dark:text-[#FAF3EE] focus:outline-none focus:border-[#DE1B54]"
              required
            />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-[#2A1810] dark:text-[#FAF3EE] mb-1">{{ localeStore.isAmharic ? 'ክፍለ ከተማ / አካባቢ' : 'Sub-City / Region' }}</label>
              <select
                v-model="addressForm.region"
                class="w-full bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] rounded-xl px-3 py-2 text-xs text-[#2A1810] dark:text-[#FAF3EE] focus:outline-none focus:border-[#DE1B54]"
              >
                <option value="Bole">Bole</option>
                <option value="Kirkos">Kirkos / Mexico</option>
                <option value="Yeka">Yeka / Megenagna</option>
                <option value="Arada">Arada / Piassa</option>
                <option value="CMC">CMC / Ayat</option>
              </select>
            </div>

            <div>
              <label class="block font-bold text-[#2A1810] dark:text-[#FAF3EE] mb-1">{{ localeStore.isAmharic ? 'ከተማ' : 'City' }}</label>
              <input
                v-model="addressForm.city"
                type="text"
                class="w-full bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] rounded-xl px-3.5 py-2 text-xs text-[#2A1810] dark:text-[#FAF3EE] focus:outline-none focus:border-[#DE1B54]"
              />
            </div>
          </div>

          <div>
            <label class="block font-bold text-[#2A1810] dark:text-[#FAF3EE] mb-1">{{ localeStore.isAmharic ? 'ተጨማሪ ማስታወሻ (አስገዳጅ ያልሆነ)' : 'Delivery Notes (Optional)' }}</label>
            <input
              v-model="addressForm.notes"
              type="text"
              :placeholder="localeStore.isAmharic ? 'ምሳሌ፡ ደወል ቁጥር 4 ይደውሉ ወይም ጥበቃ ጋር ያስቀምጡ' : 'e.g. Ring buzzer 4, leave with security'"
              class="w-full bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] rounded-xl px-3.5 py-2 text-xs text-[#2A1810] dark:text-[#FAF3EE] focus:outline-none focus:border-[#DE1B54]"
            />
          </div>

          <div class="flex items-center space-x-2 pt-1">
            <input
              v-model="addressForm.is_default"
              type="checkbox"
              id="set_default_addr"
              class="rounded border-gray-300 text-[#DE1B54] focus:ring-[#DE1B54]"
            />
            <label for="set_default_addr" class="text-xs text-[#786B60] dark:text-[#C5B7AF] cursor-pointer">{{ localeStore.isAmharic ? 'እንደ ነባሪ የማድረሻ አድራሻ አድርግ' : 'Set as default delivery address' }}</label>
          </div>

          <div class="pt-3 flex justify-end space-x-2">
            <button
              type="button"
              @click="showAddressModal = false"
              class="px-4 py-2 border border-gray-200 dark:border-[#382318] text-[#2A1810] dark:text-[#FAF3EE] rounded-xl font-bold cursor-pointer hover:bg-gray-50"
            >
              {{ localeStore.isAmharic ? 'ሰርዝ' : 'Cancel' }}
            </button>
            <button
              type="submit"
              :disabled="savingAddress"
              class="px-5 py-2 bg-[#DE1B54] hover:bg-[#C51345] disabled:opacity-50 text-white rounded-xl font-bold cursor-pointer"
            >
              {{ savingAddress ? (localeStore.isAmharic ? 'በማስቀመጥ ላይ...' : 'Saving...') : (localeStore.isAmharic ? 'አድራሻ አስቀምጥ' : 'Save Address') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Customer Official Quotation Slip Modal -->
    <div
      v-if="showQuotationModal && activeQuotationCake"
      class="fixed inset-0 z-50 bg-black/75 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto"
      @click.self="showQuotationModal = false"
    >
      <div class="relative max-w-2xl w-full bg-[#FAF7F2] dark:bg-[#1E120B] rounded-3xl shadow-2xl border border-[#E9E0D4] dark:border-[#382318] overflow-hidden my-8 print:m-0 print:border-none print:shadow-none print:w-full">
        <!-- Top bar -->
        <div class="bg-[#3D251E] text-white px-6 py-4 flex items-center justify-between print:hidden">
          <div class="flex items-center space-x-2">
            <span class="text-lg">🧁</span>
            <span class="font-serif font-bold text-base tracking-wide">SweetBakes Official Quotation</span>
          </div>
          <div class="flex items-center space-x-2">
            <button
              type="button"
              @click="printQuotation"
              class="px-3 py-1.5 bg-[#BA5420] hover:bg-[#9E4314] text-white text-xs font-bold rounded-xl transition cursor-pointer flex items-center space-x-1"
            >
              <span>🖨️</span>
              <span>Print / Save PDF</span>
            </button>
            <button
              type="button"
              @click="showQuotationModal = false"
              class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 text-white font-bold flex items-center justify-center cursor-pointer transition"
            >
              ✕
            </button>
          </div>
        </div>

        <!-- Document Body -->
        <div class="p-6 sm:p-8 space-y-6 text-[#2D231D] dark:text-[#FAF3EE]">
          <!-- Document Header -->
          <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 border-b-2 border-[#3D251E]/20 dark:border-[#FAF3EE]/20 pb-5">
            <div>
              <div class="font-serif text-2xl font-black text-[#3D251E] dark:text-[#FAF3EE] tracking-tight">SweetBakes Bakery</div>
              <div class="text-xs text-[#7A6B60] dark:text-[#C5B7AF] mt-0.5 font-medium">Artisan Patisserie &amp; Bespoke Celebration Cakes</div>
              <div class="text-xs text-[#5A4B41] dark:text-[#D1C2BA] mt-1">
                📍 {{ activeQuotationCake.branch?.branch_name || 'Bole Main Branch' }}, Dessie &amp; Addis Ababa
              </div>
              <div class="text-xs text-[#5A4B41] dark:text-[#D1C2BA]">
                📞 +251 921 894 395 &middot; info@sweetbakes.et
              </div>
            </div>

            <div class="sm:text-right bg-white dark:bg-[#2A1810] p-3.5 rounded-2xl border border-[#E9E0D4] dark:border-[#382318] shadow-2xs">
              <div class="text-[10px] font-bold text-[#8A7B71] dark:text-[#A8988E] uppercase tracking-wider">Official Quotation</div>
              <div class="text-lg font-mono font-black text-[#BA5420] mt-0.5">
                QT-{{ activeQuotationCake.request_number }}
              </div>
              <div class="text-xs text-[#7A6B60] dark:text-[#C5B7AF] mt-1">
                Date: <strong class="text-[#2D231D] dark:text-[#FAF3EE]">{{ new Date().toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) }}</strong>
              </div>
              <div class="text-xs text-[#7A6B60] dark:text-[#C5B7AF]">
                Valid for: <strong class="text-[#2D231D] dark:text-[#FAF3EE]">7 Days</strong>
              </div>
            </div>
          </div>

          <!-- Customer & Order Overview -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-white dark:bg-[#2A1810] p-4 rounded-2xl border border-[#E9E0D4] dark:border-[#382318]">
            <div class="space-y-1 text-xs">
              <div class="text-[10px] font-bold text-[#8A7B71] dark:text-[#A8988E] uppercase tracking-wider">Customer Information</div>
              <div class="text-sm font-bold text-[#2D231D] dark:text-[#FAF3EE]">
                {{ authStore.user?.full_name || activeQuotationCake.customer_name || 'Valued Customer' }}
              </div>
              <div class="text-[#5A4B41] dark:text-[#D1C2BA]">
                Phone: {{ authStore.user?.phone || activeQuotationCake.customer_phone || 'N/A' }}
              </div>
              <div class="text-[#5A4B41] dark:text-[#D1C2BA]" v-if="authStore.user?.email">
                Email: {{ authStore.user.email }}
              </div>
            </div>

            <div class="space-y-1 text-xs sm:text-right">
              <div class="text-[10px] font-bold text-[#8A7B71] dark:text-[#A8988E] uppercase tracking-wider">Fulfillment Details</div>
              <div class="text-sm font-bold text-[#BA5420]">
                Due Date: {{ activeQuotationCake.needed_by }}
              </div>
              <div class="text-[#5A4B41] dark:text-[#D1C2BA]">
                Branch: {{ activeQuotationCake.branch?.branch_name || 'Bole Main Branch' }}
              </div>
              <div class="text-[#5A4B41] dark:text-[#D1C2BA]">
                Occasion: {{ activeQuotationCake.occasion || 'Celebration' }}
              </div>
            </div>
          </div>

          <!-- Specifications -->
          <div class="space-y-2">
            <div class="text-xs font-bold text-[#3D251E] dark:text-[#FAF3EE] uppercase tracking-wider">Cake Specifications</div>
            <div class="bg-white dark:bg-[#2A1810] rounded-2xl border border-[#E9E0D4] dark:border-[#382318] p-4 text-xs space-y-2">
              <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                <div>
                  <span class="text-[#8A7B71] dark:text-[#A8988E] block text-[11px]">Size &amp; Tiers</span>
                  <strong class="text-[#2D231D] dark:text-[#FAF3EE]">{{ activeQuotationCake.cake_size }}</strong>
                </div>
                <div>
                  <span class="text-[#8A7B71] dark:text-[#A8988E] block text-[11px]">Flavor Sponge</span>
                  <strong class="text-[#2D231D] dark:text-[#FAF3EE]">{{ activeQuotationCake.flavor }}</strong>
                </div>
                <div>
                  <span class="text-[#8A7B71] dark:text-[#A8988E] block text-[11px]">Dietary</span>
                  <strong class="text-[#2D231D] dark:text-[#FAF3EE]">{{ activeQuotationCake.is_fasting ? 'Fasting (Tsome)' : 'Standard Dairy' }}</strong>
                </div>
              </div>
              <div v-if="activeQuotationCake.message || activeQuotationCake.inscription" class="pt-2 border-t border-[#F0EAE1] dark:border-[#382318]">
                <span class="text-[#8A7B71] dark:text-[#A8988E] block text-[11px]">Inscription:</span>
                <span class="font-serif italic text-sm text-[#BA5420] font-bold">"{{ activeQuotationCake.message || activeQuotationCake.inscription }}"</span>
              </div>
              <div v-if="activeQuotationCake.design" class="pt-2 border-t border-[#F0EAE1] dark:border-[#382318]">
                <span class="text-[#8A7B71] dark:text-[#A8988E] block text-[11px]">Design Brief:</span>
                <p class="text-[#5A4B41] dark:text-[#D1C2BA] italic mt-0.5">{{ activeQuotationCake.design }}</p>
              </div>
            </div>
          </div>

          <!-- Itemized Financial Breakdown Table -->
          <div class="space-y-2">
            <div class="text-xs font-bold text-[#3D251E] dark:text-[#FAF3EE] uppercase tracking-wider">Itemized Costing</div>
            <div class="bg-white dark:bg-[#2A1810] rounded-2xl border border-[#E9E0D4] dark:border-[#382318] overflow-hidden">
              <table class="w-full text-xs">
                <thead class="bg-[#FAF7F2] dark:bg-[#25150E] border-b border-[#E9E0D4] dark:border-[#382318] text-[#65554B] dark:text-[#C5B7AF]">
                  <tr>
                    <th class="py-2.5 px-4 text-left font-bold">Item Description</th>
                    <th class="py-2.5 px-4 text-right font-bold">Amount (ETB)</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-[#F0EAE1] dark:divide-[#382318]">
                  <tr>
                    <td class="py-2.5 px-4">
                      <div class="font-bold text-[#2D231D] dark:text-[#FAF3EE]">Artisan Sponge Cake ({{ activeQuotationCake.cake_size }})</div>
                      <div class="text-[11px] text-[#8A7B71] dark:text-[#A8988E]">Freshly baked {{ activeQuotationCake.flavor }} tiers with premium buttercream layers</div>
                    </td>
                    <td class="py-2.5 px-4 text-right font-bold text-[#2D231D] dark:text-[#FAF3EE]">
                      ETB {{ Number(activeQuotationCake.design_config?.official_quote?.base_sponge_price || Math.round(Number(activeQuotationCake.quoted_price || 0) * 0.6)).toLocaleString() }}
                    </td>
                  </tr>
                  <tr>
                    <td class="py-2.5 px-4">
                      <div class="font-bold text-[#2D231D] dark:text-[#FAF3EE]">Handmade Fondant &amp; Specialty Decorating</div>
                      <div class="text-[11px] text-[#8A7B71] dark:text-[#A8988E]">Artisan sculpting, custom piping, color palette matching</div>
                    </td>
                    <td class="py-2.5 px-4 text-right font-bold text-[#2D231D] dark:text-[#FAF3EE]">
                      ETB {{ Number(activeQuotationCake.design_config?.official_quote?.artistry_price || Math.round(Number(activeQuotationCake.quoted_price || 0) * 0.3)).toLocaleString() }}
                    </td>
                  </tr>
                  <tr v-if="Number(activeQuotationCake.design_config?.official_quote?.topper_price) > 0 || !activeQuotationCake.design_config?.official_quote">
                    <td class="py-2.5 px-4">
                      <div class="font-bold text-[#2D231D] dark:text-[#FAF3EE]">Occasion Topper &amp; Decorative Elements</div>
                      <div class="text-[11px] text-[#8A7B71] dark:text-[#A8988E]">Celebration topper, sugar floral accents</div>
                    </td>
                    <td class="py-2.5 px-4 text-right font-bold text-[#2D231D] dark:text-[#FAF3EE]">
                      ETB {{ Number(activeQuotationCake.design_config?.official_quote?.topper_price || Math.round(Number(activeQuotationCake.quoted_price || 0) * 0.1)).toLocaleString() }}
                    </td>
                  </tr>
                </tbody>
                <tfoot class="bg-[#FAF7F2] dark:bg-[#25150E] border-t-2 border-[#E9E0D4] dark:border-[#382318]">
                  <tr>
                    <td class="py-3 px-4 font-black text-sm text-[#3D251E] dark:text-[#FAF3EE]">Total Quoted Price</td>
                    <td class="py-3 px-4 text-right font-black text-base text-[#BA5420]">
                      ETB {{ Number(activeQuotationCake.quoted_price || 0).toLocaleString() }}
                    </td>
                  </tr>
                  <tr class="text-[11px]">
                    <td class="py-2 px-4 text-[#387A54] dark:text-emerald-400 font-bold">
                      Advance Deposit Required to Confirm ({{ activeQuotationCake.deposit_required_percentage || 50 }}%)
                    </td>
                    <td class="py-2 px-4 text-right text-[#387A54] dark:text-emerald-400 font-black">
                      ETB {{ Math.round((Number(activeQuotationCake.quoted_price || 0) * (Number(activeQuotationCake.deposit_required_percentage || 50))) / 100).toLocaleString() }}
                    </td>
                  </tr>
                  <tr class="text-[11px]">
                    <td class="py-2 px-4 text-[#7A6B60] dark:text-[#C5B7AF]">
                      Remaining Balance Due on Handover
                    </td>
                    <td class="py-2 px-4 text-right text-[#2D231D] dark:text-[#FAF3EE] font-bold">
                      ETB {{ Math.round(Number(activeQuotationCake.quoted_price || 0) - ((Number(activeQuotationCake.quoted_price || 0) * (Number(activeQuotationCake.deposit_required_percentage || 50))) / 100)).toLocaleString() }}
                    </td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>

          <!-- Special Notes -->
          <div v-if="activeQuotationCake.design_config?.official_quote?.quote_notes" class="p-3 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 rounded-xl text-xs space-y-1">
            <span class="font-bold text-amber-900 dark:text-amber-300 block">Chef &amp; Baker Notes:</span>
            <p class="text-amber-800 dark:text-amber-200 leading-snug">{{ activeQuotationCake.design_config.official_quote.quote_notes }}</p>
          </div>

          <!-- Bakery Terms -->
          <div class="text-[11px] text-[#7A6B60] dark:text-[#C5B7AF] space-y-1 pt-3 border-t border-[#E9E0D4] dark:border-[#382318]">
            <div class="font-bold text-[#3D251E] dark:text-[#FAF3EE]">SweetBakes Terms &amp; Policies:</div>
            <ul class="list-disc list-inside space-y-0.5">
              <li>Quotation is valid for 7 calendar days from issuance.</li>
              <li>A 50% deposit confirms kitchen scheduling and oven reservation.</li>
              <li>Hand-crafted with fresh, premium ingredients at the selected branch bakehouse.</li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Reference Photo Zoom Modal -->
    <div
      v-if="zoomImage"
      class="fixed inset-0 z-50 bg-black/80 backdrop-blur-xs flex items-center justify-center p-4 cursor-pointer"
      @click="zoomImage = null"
    >
      <div class="relative max-w-xl w-full flex items-center justify-center" @click.stop>
        <img :src="zoomImage" alt="Zoomed Reference" class="max-w-full max-h-[80vh] object-contain rounded-2xl shadow-2xl" />
        <button
          type="button"
          @click="zoomImage = null"
          class="absolute top-3 right-3 w-8 h-8 rounded-full bg-white/90 text-black font-bold flex items-center justify-center shadow-lg cursor-pointer"
        >
          ✕
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { useBranchStore } from '../../stores/branch';
import { useCartStore } from '../../stores/cart';
import { useToastStore } from '../../stores/toast';
import { useWishlistStore } from '../../stores/wishlist';
import { useLocaleStore } from '../../stores/locale';
import api from '../../api';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const branchStore = useBranchStore();
const cartStore = useCartStore();
const toast = useToastStore();
const wishlistStore = useWishlistStore();
const localeStore = useLocaleStore();

const activeTab = ref(
  route.query.tab === 'custom-cakes' ? 'custom_cakes' :
  route.query.tab === 'wishlist' ? 'wishlist' :
  route.query.tab === 'addresses' ? 'addresses' :
  route.query.tab === 'settings' ? 'settings' : 'orders'
);
const orders = ref([]);
const customCakes = ref([]);
const addresses = ref([]);
const zoomImage = ref(null);
const showQuotationModal = ref(false);
const activeQuotationCake = ref(null);

function openQuotationModal(cake) {
  activeQuotationCake.value = cake;
  showQuotationModal.value = true;
}

function printQuotation() {
  window.print();
}

const editName = ref('');
const editPhone = ref(authStore.user?.phone || '');
const currentPassword = ref('');
const newPassword = ref('');
const confirmPassword = ref('');
const savingProfile = ref(false);
const updatingPassword = ref(false);
const showAddressModal = ref(false);
const savingAddress = ref(false);
const addressForm = ref({
  street: '',
  city: 'Addis Ababa',
  region: 'Bole',
  notes: '',
  is_default: false,
});

const userName = computed(() => authStore.user?.full_name || 'Valued Customer');
const userEmail = computed(() => authStore.user?.email || '');

const currentBranchName = computed(() => {
  const b = branchStore.branches.find((br) => br.branch_id === branchStore.selectedBranchId);
  return b ? `${b.branch_name} (${b.city || 'Addis Ababa'})` : 'Select Branch';
});

const isLoadingOrders = ref(false);
const orderFilter = ref('all');
const expandedOrders = ref({});

function toggleOrderExpansion(orderId) {
  expandedOrders.value[orderId] = !expandedOrders.value[orderId];
}

const activeOrdersCount = computed(() => {
  return orders.value.filter((o) =>
    ['Pending', 'Confirmed', 'Preparing', 'Ready', 'Out for delivery'].includes(o.order_status)
  ).length;
});

const completedOrdersCount = computed(() => {
  return orders.value.filter((o) =>
    ['Completed', 'Delivered'].includes(o.order_status)
  ).length;
});

const cancelledOrdersCount = computed(() => {
  return orders.value.filter((o) => o.order_status === 'Cancelled').length;
});

function getOrderStepNumber(status) {
  switch (status) {
    case 'Pending': return 1;
    case 'Confirmed': return 2;
    case 'Preparing': return 3;
    case 'Ready':
    case 'Out for delivery': return 4;
    case 'Completed':
    case 'Delivered': return 5;
    case 'Cancelled': return -1;
    default: return 1;
  }
}

function isOrderStepCompleted(currentStatus, stepIndex) {
  if (currentStatus === 'Cancelled') return false;
  return getOrderStepNumber(currentStatus) > stepIndex;
}

function isOrderStepCurrent(currentStatus, stepIndex) {
  if (currentStatus === 'Cancelled') return false;
  return getOrderStepNumber(currentStatus) === stepIndex;
}

function tOrderStatus(status) {
  if (!localeStore.isAmharic) return status;
  switch (status) {
    case 'Pending': return 'በመጠባበቅ ላይ';
    case 'Confirmed': return 'ተረጋግጧል';
    case 'Preparing': return 'በዝግጅት ላይ';
    case 'Ready': return 'ዝግጁ ነው';
    case 'Out for delivery': return 'በማድረስ ላይ';
    case 'Delivered': return 'ደርሷል';
    case 'Completed': return 'ተጠናቋል';
    case 'Cancelled': return 'ተሰርዟል';
    case 'Quoted': return 'ዋጋ ተሰጥቷል';
    case 'Submitted': return 'ተልኳል';
    case 'Rejected': return 'ተቀባይነት አላገኘም';
    case 'Picked up': return 'ተወስዷል';
    default: return status;
  }
}

function tPaymentMethod(pm) {
  if (!localeStore.isAmharic) return pm || 'Pay on delivery';
  if (!pm || pm === 'Pay on delivery') return 'ሲደርስ የሚከፈል';
  if (pm === 'Pay on pickup') return 'ሲወሰድ የሚከፈል';
  return pm;
}

function tPaymentStatus(ps) {
  if (!localeStore.isAmharic) return ps || 'Pending';
  if (ps === 'Paid') return 'ተከፍሏል';
  if (ps === 'Pending') return 'ያልተከፈለ';
  if (ps === 'Refunded') return 'ተመልሷል';
  return ps;
}

function getOrderStepName(stepIndex, fulfillmentMethod) {
  switch (stepIndex) {
    case 1: return localeStore.isAmharic ? 'ተልኳል' : 'Placed';
    case 2: return localeStore.isAmharic ? 'ተረጋግጧል' : 'Confirmed';
    case 3: return localeStore.isAmharic ? 'በዝግጅት ላይ' : 'Preparing';
    case 4: return fulfillmentMethod === 'Delivery' 
      ? (localeStore.isAmharic ? 'በማድረስ ላይ' : 'Out for Delivery') 
      : (localeStore.isAmharic ? 'ለመውሰድ ዝግጁ' : 'Ready for Pickup');
    case 5: return fulfillmentMethod === 'Delivery' 
      ? (localeStore.isAmharic ? 'ደርሷል' : 'Delivered') 
      : (localeStore.isAmharic ? 'ተጠናቋል' : 'Completed');
    default: return '';
  }
}

function getOrderProgressMessage(status, fulfillmentMethod) {
  switch (status) {
    case 'Pending':
      return localeStore.isAmharic 
        ? 'የክፍያ ማረጋገጫ ወይም የቅርንጫፍ ተቀባይነት በመጠበቅ ላይ' 
        : 'Awaiting online payment verification or branch confirmation';
    case 'Confirmed':
      return localeStore.isAmharic 
        ? 'ትዕዛዙ ተረጋግጦ ወደ መጋገሪያ ክፍል ተልኳል' 
        : 'Order verified and queued for master bakery production';
    case 'Preparing':
      return localeStore.isAmharic 
        ? 'ዳቦ ጋጋሪዎች ትኩስ ምርቶችዎን በማዘጋጀት እና በመጋገር ላይ ናቸው' 
        : 'Bakers are mixing, baking, and hand-crafting your items in the oven';
    case 'Ready':
      return fulfillmentMethod === 'Delivery'
        ? (localeStore.isAmharic ? 'ምርቶቹ ታሽገው ለመላክ ዝግጁ ሆነዋል' : 'Items boxed, waiting for our courier fleet dispatch')
        : (localeStore.isAmharic ? 'ትኩስ ሆነው ታሽገው በመደብራችን ካውንተር ለመውሰድ ዝግጁ ናቸው' : 'Freshly packed and ready for pickup at our branch counter');
    case 'Out for delivery':
      return localeStore.isAmharic 
        ? 'አድራሻዎ ድረስ ለማድረስ መልእክተኛው በመንገድ ላይ ነው' 
        : 'Our delivery courier is currently on the road to your doorstep';
    case 'Completed':
    case 'Delivered':
      return localeStore.isAmharic 
        ? 'ትዕዛዝዎ በተሳካ ሁኔታ ተጠናቋል! መልካም የምግብ ጊዜ' 
        : 'Order fulfilled successfully! Enjoy your fresh treats';
    case 'Cancelled':
      return localeStore.isAmharic 
        ? 'ትዕዛዙ ተሰርዟል' 
        : 'Order was cancelled and inventory reservations released';
    default:
      return localeStore.isAmharic ? 'ትዕዛዝ በሂደት ላይ' : 'Order in progress';
  }
}

const displayOrders = computed(() => {
  let list = orders.value;
  if (!Array.isArray(list) || list.length === 0) return [];

  if (orderFilter.value === 'active') {
    list = list.filter((o) =>
      ['Pending', 'Confirmed', 'Preparing', 'Ready', 'Out for delivery'].includes(o.order_status)
    );
  } else if (orderFilter.value === 'completed') {
    list = list.filter((o) => ['Completed', 'Delivered'].includes(o.order_status));
  } else if (orderFilter.value === 'cancelled') {
    list = list.filter((o) => o.order_status === 'Cancelled');
  }

  return list.map((o) => ({
    ...o,
    date_formatted: new Date(o.placed_at || o.created_at).toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    }),
    branch_name: o.branch?.branch_name || 'SweetBakes Branch',
    branch_city: o.branch?.city || 'Addis Ababa',
    branch_phone: o.branch?.phone || '',
    branch_address: o.branch?.address || '',
    items_summary: o.items && o.items.length > 0
      ? o.items.map((i) => `${i.quantity} × ${i.product_name || i.product?.product_name || 'Item'}`).join(', ')
      : 'Bakery Selection',
    total_amount_formatted: Number(o.total_amount).toLocaleString(),
    current_step: getOrderStepNumber(o.order_status),
    status_history_items: o.status_history || o.statusHistory || [],
  }));
});

const pendingQuotesCount = computed(() => {
  return customCakes.value.filter((c) => c.status === 'Quoted').length;
});

async function loadData() {
  editName.value = userName.value;
  if (authStore.user?.phone) {
    editPhone.value = authStore.user.phone;
  }

  isLoadingOrders.value = true;
  try {
    const res = await api.get('/orders');
    if (res.data?.orders) {
      orders.value = res.data.orders;
    } else if (Array.isArray(res.data)) {
      orders.value = res.data;
    }
  } catch (err) {
    console.error('Failed to load orders', err);
  } finally {
    isLoadingOrders.value = false;
  }

  try {
    const resCake = await api.get('/custom-cakes');
    if (resCake.data?.requests) {
      customCakes.value = resCake.data.requests;
    } else if (Array.isArray(resCake.data)) {
      customCakes.value = resCake.data;
    }
  } catch (err) {
    console.error('Failed to load custom cakes', err);
  }

  try {
    const resAddr = await api.get('/addresses');
    addresses.value = Array.isArray(resAddr.data) ? resAddr.data : [];
  } catch (err) {
    console.error('Failed to load delivery addresses', err);
    addresses.value = [];
  }
}

async function handleDeleteOrder(order) {
  const confirmMsg = `Remove order #${order.order_number} from your order history?`;
  if (!window.confirm(confirmMsg)) return;

  try {
    const res = await api.delete(`/orders/${order.order_id || order.order_number}`);
    toast.success(res.data?.message || `Order #${order.order_number} removed from history.`);
    orders.value = orders.value.filter(
      (o) => o.order_id !== order.order_id && o.order_number !== order.order_number
    );
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to remove order from history.');
  }
}

async function handleClearFinishedOrders() {
  const totalFinished = completedOrdersCount.value + cancelledOrdersCount.value;
  if (totalFinished === 0) return;
  const confirmMsg = `Remove all ${totalFinished} finished (completed & cancelled) orders from your history?`;
  if (!window.confirm(confirmMsg)) return;

  try {
    const res = await api.post('/orders/clear-finished');
    toast.success(res.data?.message || 'Finished orders cleared successfully.');
    orders.value = orders.value.filter(
      (o) => !['Completed', 'Delivered', 'Cancelled'].includes(o.order_status)
    );
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to clear finished orders.');
  }
}

async function handleSaveAddress() {
  if (!addressForm.value.street.trim()) {
    toast.error('Please enter a valid street or building address.');
    return;
  }
  savingAddress.value = true;
  try {
    const res = await api.post('/addresses', addressForm.value);
    toast.success(res.data?.message || 'Address saved successfully!');
    showAddressModal.value = false;
    addressForm.value = { street: '', city: 'Addis Ababa', region: 'Bole', notes: '', is_default: false };
    const resAddr = await api.get('/addresses');
    addresses.value = Array.isArray(resAddr.data) ? resAddr.data : [];
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to save address.');
  } finally {
    savingAddress.value = false;
  }
}

async function deleteAddress(id) {
  if (!confirm('Remove this delivery address?')) return;
  try {
    await api.delete(`/addresses/${id}`);
    addresses.value = addresses.value.filter((a) => a.address_id !== id);
    toast.info('Address removed.');
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to delete address.');
  }
}

async function makeDefaultAddress(id) {
  try {
    await api.post(`/addresses/${id}/default`);
    addresses.value.forEach((a) => { a.is_default = a.address_id === id; });
    toast.success('Default address updated.');
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to update default address.');
  }
}

const wishlistProducts = computed(() => {
  return wishlistStore.items || [];
});

const defaultBakeryImages = [
  'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=600&q=80',
  'https://images.unsplash.com/photo-1588195538326-c5b1e9f80a1b?w=600&q=80',
  'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=600&q=80',
  'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=600&q=80',
];

function getWishlistImage(item) {
  if (item.image_url) return item.image_url;
  if (item.images && item.images.length > 0) return item.images[0].image_url;
  const hash = Math.abs((item.product_id || 1) % defaultBakeryImages.length);
  return defaultBakeryImages[hash];
}

async function removeWishlistItem(productId) {
  await wishlistStore.removeFromWishlist(productId);
  toast.info('Removed item from your wishlist.');
}

async function clearAllWishlist() {
  if (!confirm('Are you sure you want to clear all favorited items from your wishlist?')) return;
  wishlistStore.clear();
  toast.info('Cleared all items from your wishlist.');
}

function addWishlistToCart(item) {
  const price = Number(item.resolved_price || item.base_price || item.price || 0);
  const result = cartStore.addItem(
    {
      product_id: item.product_id,
      product_name: item.product_name,
      price: price,
      image_url: getWishlistImage(item),
      quantity: 1,
      available: 50,
      available_stock: 50,
      isSoldOut: false,
    },
    branchStore.selectedBranchId
  );

  if (result && result.reason === 'capped') {
    toast.warning(result.message);
  } else if (result && result.success === false) {
    toast.error(result.message || 'Could not add to cart.');
  } else {
    toast.success(`Added "${item.product_name}" to your bag!`);
    cartStore.openDrawer();
  }
}

onMounted(() => {
  loadData();
  wishlistStore.init();
});

function formatDate(d) {
  if (!d) return 'Recently';
  return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

function statusBadgeClass(status) {
  switch (status) {
    case 'Delivered':
    case 'Completed':
      return 'bg-[#387A54]/15 text-[#387A54] border border-[#387A54]/30';
    case 'Preparing':
      return 'bg-[#B98235]/15 text-[#B98235] border border-[#B98235]/30';
    case 'Confirmed':
      return 'bg-rose-50 text-[#DE1B54] border border-rose-200 dark:bg-[#341822] dark:border-[#58263A]';
    case 'Out for delivery':
    case 'Ready':
      return 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:border-emerald-800';
    case 'Cancelled':
      return 'bg-[#B84B42]/15 text-[#B84B42] border border-[#B84B42]/30';
    default:
      return 'bg-gray-100 text-gray-700 border border-gray-200 dark:bg-[#25150E] dark:text-[#C5B7AF]';
  }
}

function isCakeStepActive(status, step) {
  if (status === 'Cancelled' || status === 'Rejected') return false;
  if (step === 1) return true; // Submitted
  if (step === 2) return status === 'Quoted' || status === 'Confirmed' || status === 'Preparing' || status === 'Ready' || status === 'Delivered' || status === 'Picked up';
  if (step === 3) return status === 'Confirmed' || status === 'Preparing' || status === 'Ready' || status === 'Delivered' || status === 'Picked up';
  if (step === 4) return status === 'Preparing' || status === 'Ready' || status === 'Delivered' || status === 'Picked up';
  if (step === 5) return status === 'Ready' || status === 'Delivered' || status === 'Picked up';
  return false;
}

function customCakeStatusBadge(status) {
  switch (status) {
    case 'Quoted':
      return 'bg-[#B98235]/15 text-[#B98235] border border-[#B98235]/30';
    case 'Confirmed':
      return 'bg-[#387A54]/15 text-[#387A54] border border-[#387A54]/30';
    case 'Preparing':
      return 'bg-rose-50 text-[#DE1B54] border border-rose-200 dark:bg-[#341822] dark:border-[#58263A]';
    case 'Ready':
      return 'bg-[#387A54]/20 text-[#387A54] border border-[#387A54]/40';
    case 'Delivered':
    case 'Picked up':
      return 'bg-[#387A54]/10 text-[#387A54] border border-[#387A54]/20';
    case 'Rejected':
    case 'Cancelled':
      return 'bg-[#B84B42]/15 text-[#B84B42] border border-[#B84B42]/30';
    default:
      return 'bg-[#FAF7F5] dark:bg-[#20120B] text-[#2A1810] dark:text-[#FAF3EE] border border-gray-200 dark:border-[#382318]';
  }
}

async function acceptQuote(cake, choice) {
  try {
    await api.post(`/custom-cakes/${cake.request_id}/accept-quote`, {
      payment_choice: choice,
      payment_method: 'Chapa',
    });
    toast.success('Custom cake quote accepted! Your order is Confirmed.');
    await loadData();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to accept quote.');
  }
}

async function cancelCake(cake) {
  const reason = prompt('Please enter cancellation reason:', 'Change of plan');
  if (reason === null) return;

  try {
    const res = await api.post(`/custom-cakes/${cake.request_id}/cancel`, {
      reason: reason || 'Cancelled by customer',
    });
    toast.info(`Request cancelled. Policy: ${res.data.refund_policy_applied || 'Standard'}`);
    await loadData();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to cancel custom cake.');
  }
}

async function saveProfile() {
  if (!editName.value.trim()) {
    toast.error('Please enter your full name.');
    return;
  }
  savingProfile.value = true;
  try {
    const res = await api.patch('/auth/profile', {
      full_name: editName.value.trim(),
      phone: editPhone.value ? editPhone.value.trim() : null,
    });
    if (authStore.user) {
      authStore.user.full_name = res.data.user.full_name;
      authStore.user.phone = res.data.user.phone;
      localStorage.setItem('user', JSON.stringify(authStore.user));
    }
    toast.success(res.data?.message || 'Profile details saved successfully!');
  } catch (err) {
    toast.error(err.response?.data?.message || err.response?.data?.errors?.phone?.[0] || 'Failed to update profile.');
  } finally {
    savingProfile.value = false;
  }
}

async function updatePassword() {
  if (!newPassword.value || newPassword.value.length < 8) {
    toast.error('Password must be at least 8 characters.');
    return;
  }
  if (newPassword.value !== confirmPassword.value) {
    toast.error('Passwords do not match.');
    return;
  }
  updatingPassword.value = true;
  try {
    const payload = {
      password: newPassword.value,
      password_confirmation: confirmPassword.value,
    };
    if (currentPassword.value) {
      payload.current_password = currentPassword.value;
    }
    const res = await api.post('/auth/change-password', payload);
    toast.success(res.data?.message || 'Password updated successfully!');
    currentPassword.value = '';
    newPassword.value = '';
    confirmPassword.value = '';
  } catch (err) {
    toast.error(err.response?.data?.message || err.response?.data?.errors?.password?.[0] || 'Failed to update password.');
  } finally {
    updatingPassword.value = false;
  }
}

function handleLogout() {
  authStore.logout();
  toast.info('You have been signed out.');
  router.push('/login');
}
</script>
