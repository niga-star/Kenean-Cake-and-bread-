<template>
  <div class="space-y-6 max-w-5xl mx-auto font-poppins pb-10">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-[#1E120B] p-5 sm:p-6 rounded-2xl border border-stone-200/80 dark:border-[#382318] shadow-xs">
      <div class="space-y-1">
        <div class="flex items-center space-x-2.5">
          <span class="w-9 h-9 rounded-xl bg-rose-50 dark:bg-[#341822] text-[#DE1B54] flex items-center justify-center text-lg font-black shadow-2xs">
            🔔
          </span>
          <h2 class="font-serif font-black text-xl sm:text-2xl text-[#2A1D15] dark:text-[#FAF3EE]">
            Notifications
          </h2>
          <span
            v-if="unreadCount > 0"
            class="px-2.5 py-0.5 rounded-full text-xs font-black bg-[#DE1B54] text-white shadow-2xs"
          >
            {{ unreadCount }} new
          </span>
        </div>
        <p class="text-xs sm:text-sm text-[#786960] dark:text-[#C5B7AF]">
          {{ isBranchSeller 
            ? 'Real-time order milestone updates and branch alerts' 
            : 'Operational notifications, order status changes, and pending updates' }}
        </p>
      </div>

      <div class="flex items-center space-x-2 self-start sm:self-center">
        <button
          v-if="unreadCount > 0"
          type="button"
          @click="markAllAsRead"
          :disabled="isMarkingAll"
          class="inline-flex items-center space-x-1.5 px-4 py-2 rounded-xl text-xs sm:text-sm font-bold bg-white dark:bg-[#2A1810] text-[#DE1B54] hover:bg-rose-50 dark:hover:bg-[#381B26] border border-[#DE1B54]/30 shadow-2xs hover:shadow-xs transition-all cursor-pointer active:scale-95"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
          </svg>
          <span>{{ isMarkingAll ? 'Marking...' : 'Mark all as read' }}</span>
        </button>
      </div>
    </div>

    <!-- Category Filter Tabs -->
    <div class="flex items-center space-x-2 overflow-x-auto pb-1 -mx-1 px-1">
      <button
        type="button"
        @click="setCategory('All')"
        class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all cursor-pointer whitespace-nowrap shrink-0 flex items-center space-x-1.5 shadow-2xs"
        :class="activeCategory === 'All'
          ? 'bg-[#DE1B54] text-white shadow-xs'
          : 'bg-white dark:bg-[#1E120B] text-stone-600 dark:text-stone-300 border border-stone-200 dark:border-[#382318] hover:bg-stone-50 dark:hover:bg-[#2A1810]'"
      >
        <span>All</span>
        <span
          class="px-1.5 py-0.2 rounded-full text-[10px]"
          :class="activeCategory === 'All' ? 'bg-white/20 text-white' : 'bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300'"
        >
          {{ notifications.length }}
        </span>
      </button>

      <button
        type="button"
        @click="setFilterOnlyUnread(!filterOnlyUnread)"
        class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all cursor-pointer whitespace-nowrap shrink-0 flex items-center space-x-1.5 shadow-2xs"
        :class="filterOnlyUnread
          ? 'bg-[#2A1D15] dark:bg-stone-700 text-white shadow-xs'
          : 'bg-white dark:bg-[#1E120B] text-stone-600 dark:text-stone-300 border border-stone-200 dark:border-[#382318] hover:bg-stone-50 dark:hover:bg-[#2A1810]'"
      >
        <span>Unread only</span>
        <span v-if="unreadCount > 0" class="w-2 h-2 rounded-full bg-[#DE1B54]"></span>
      </button>

      <button
        v-for="cat in availableCategories"
        :key="cat"
        type="button"
        @click="setCategory(cat)"
        class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all cursor-pointer whitespace-nowrap shrink-0 shadow-2xs"
        :class="activeCategory === cat
          ? 'bg-[#DE1B54] text-white shadow-xs'
          : 'bg-white dark:bg-[#1E120B] text-stone-600 dark:text-stone-300 border border-stone-200 dark:border-[#382318] hover:bg-stone-50 dark:hover:bg-[#2A1810]'"
      >
        {{ cat }}
      </button>
    </div>

    <!-- Main Notification List -->
    <div class="space-y-3">
      <!-- Loading Skeleton -->
      <div v-if="isLoading" class="space-y-3">
        <div v-for="i in 3" :key="i" class="p-5 rounded-2xl bg-white dark:bg-[#1E120B] border border-stone-200/80 dark:border-[#382318] animate-pulse flex items-start space-x-4">
          <div class="w-10 h-10 rounded-xl bg-stone-100 dark:bg-stone-800"></div>
          <div class="flex-1 space-y-2">
            <div class="w-1/3 h-4 bg-stone-100 dark:bg-stone-800 rounded"></div>
            <div class="w-2/3 h-3 bg-stone-100 dark:bg-stone-800 rounded"></div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div
        v-else-if="displayedNotifications.length === 0"
        class="p-12 text-center rounded-2xl bg-white dark:bg-[#1E120B] border border-stone-200/80 dark:border-[#382318] shadow-xs space-y-3"
      >
        <span class="text-4xl block">🎉</span>
        <h3 class="font-serif font-black text-lg text-[#2A1D15] dark:text-[#FAF3EE]">
          All caught up!
        </h3>
        <p class="text-xs sm:text-sm text-[#786960] dark:text-[#C5B7AF] max-w-sm mx-auto">
          No {{ filterOnlyUnread ? 'unread' : '' }} notifications at this moment. You'll receive real-time alerts when new milestones arrive.
        </p>
      </div>

      <!-- Notification Item Cards -->
      <div
        v-for="n in displayedNotifications"
        :key="n.notification_id"
        class="group relative bg-white dark:bg-[#1E120B] border rounded-2xl p-4 sm:p-5 shadow-xs hover:shadow-md transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-4"
        :class="!n.is_read 
          ? 'border-rose-200/90 dark:border-rose-900/40 bg-gradient-to-r from-rose-50/30 via-white to-white dark:from-[#25121A]/30 dark:via-[#1E120B] dark:to-[#1E120B]' 
          : 'border-stone-200/80 dark:border-[#382318] opacity-90 hover:opacity-100'"
      >
        <!-- Left: Status Icon + Text Content -->
        <div class="flex items-start space-x-3.5 flex-1 min-w-0">
          <!-- Icon Pill -->
          <div
            class="w-10 h-10 rounded-xl flex items-center justify-center text-lg shrink-0 border shadow-2xs"
            :class="getIconConfig(n).class"
          >
            {{ getIconConfig(n).icon }}
          </div>

          <div class="space-y-1 min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
              <span
                class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider border shadow-2xs"
                :class="getCategoryBadgeClass(n.category)"
              >
                {{ n.category }}
              </span>

              <span v-if="n.branch_name" class="text-[11px] font-bold text-stone-500 dark:text-stone-400">
                • {{ n.branch_name }}
              </span>

              <span v-if="!n.is_read" class="inline-flex items-center space-x-1 text-[10px] font-bold text-[#DE1B54]">
                <span class="w-1.5 h-1.5 rounded-full bg-[#DE1B54] animate-pulse"></span>
                <span>Unread</span>
              </span>
            </div>

            <!-- Title -->
            <h4 class="text-sm sm:text-base font-bold text-[#2A1D15] dark:text-[#FAF3EE] leading-snug">
              {{ n.title }}
            </h4>

            <!-- Message Body -->
            <p class="text-xs sm:text-sm text-[#6A5A50] dark:text-[#C5B7AF] leading-relaxed">
              {{ n.message }}
            </p>
          </div>
        </div>

        <!-- Right: Time Ago & Direct Action Buttons -->
        <div class="flex items-center justify-between sm:justify-end space-x-2.5 shrink-0 pt-2 sm:pt-0 border-t border-stone-100 dark:border-[#2A1810] sm:border-t-0 sm:ml-4">
          <span class="text-xs text-stone-400 dark:text-stone-500 font-medium whitespace-nowrap">
            {{ n.time_ago }}
          </span>

          <div class="flex items-center space-x-1.5">
            <!-- Mark Single Read Button -->
            <button
              v-if="!n.is_read"
              type="button"
              @click="markSingleAsRead(n)"
              class="p-2 text-stone-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 rounded-xl transition cursor-pointer"
              title="Mark as read"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
              </svg>
            </button>

            <!-- Navigate to relevant page -->
            <router-link
              v-if="n.link"
              :to="n.link"
              class="px-3.5 py-1.5 text-xs font-bold bg-[#DE1B54] hover:bg-[#C51345] text-white rounded-xl transition shadow-2xs hover:shadow-xs inline-flex items-center space-x-1 cursor-pointer active:scale-95"
            >
              <span>{{ getActionLabel(n) }}</span>
              <span>&rarr;</span>
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- Super Admin Only: System Alert Rules Section (Kept tidy at bottom) -->
    <div
      v-if="isSuperAdmin"
      class="bg-white dark:bg-[#1E120B] border border-stone-200/80 dark:border-[#382318] rounded-2xl p-6 shadow-xs space-y-4 mt-8"
    >
      <div class="flex items-center space-x-2 border-b border-stone-100 dark:border-[#2A1810] pb-3">
        <span class="text-lg">⚙️</span>
        <div>
          <h3 class="font-serif font-black text-base text-[#2A1D15] dark:text-[#FAF3EE]">
            System Alert Rules
          </h3>
          <p class="text-xs text-stone-500">Configure administrative notifications and automated reminder frequencies</p>
        </div>
      </div>

      <form @submit.prevent="saveAlertRules" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="block text-xs font-bold text-stone-600 dark:text-stone-400 mb-1">Low stock destination</label>
          <select
            v-model="alertRules.low_stock_goes_to"
            class="w-full border border-stone-200 dark:border-[#382318] rounded-xl px-3 py-2 text-xs bg-stone-50 dark:bg-[#2A1810] text-[#2D231D] dark:text-[#FAF3EE] focus:ring-1 focus:ring-[#DE1B54]"
          >
            <option value="Branch Admin and Main Manager">Branch Admin and Main Manager</option>
            <option value="Super Admin only">Super Admin only</option>
            <option value="All managers">All managers</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold text-stone-600 dark:text-stone-400 mb-1">Unreceived transfer reminder</label>
          <select
            v-model="alertRules.unreceived_reminder"
            class="w-full border border-stone-200 dark:border-[#382318] rounded-xl px-3 py-2 text-xs bg-stone-50 dark:bg-[#2A1810] text-[#2D231D] dark:text-[#FAF3EE] focus:ring-1 focus:ring-[#DE1B54]"
          >
            <option value="After 24 hours">After 24 hours</option>
            <option value="After 12 hours">After 12 hours</option>
            <option value="After 48 hours">After 48 hours</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold text-stone-600 dark:text-stone-400 mb-1">Daily summary email</label>
          <select
            v-model="alertRules.daily_summary"
            class="w-full border border-stone-200 dark:border-[#382318] rounded-xl px-3 py-2 text-xs bg-stone-50 dark:bg-[#2A1810] text-[#2D231D] dark:text-[#FAF3EE] focus:ring-1 focus:ring-[#DE1B54]"
          >
            <option value="Owner, 20:00">Owner, 20:00</option>
            <option value="Owner & Managers, 19:00">Owner & Managers, 19:00</option>
            <option value="Disabled">Disabled</option>
          </select>
        </div>

        <div class="sm:col-span-3 flex justify-end pt-2">
          <button
            type="submit"
            :disabled="isSavingRules"
            class="px-5 py-2 rounded-xl bg-[#2A1D15] hover:bg-[#3D2C20] text-white font-bold text-xs transition shadow-xs cursor-pointer"
          >
            {{ isSavingRules ? 'Saving...' : 'Save alert rules' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '../../stores/auth';
import { useToastStore } from '../../stores/toast';
import api from '../../api';

const authStore = useAuthStore();
const toastStore = useToastStore();

const activeCategory = ref('All');
const filterOnlyUnread = ref(false);
const unreadCount = ref(0);
const notifications = ref([]);
const isLoading = ref(true);
const isMarkingAll = ref(false);
const isSavingRules = ref(false);

const isSuperAdmin = computed(() => authStore.user?.role === 'Super Admin');
const isBranchSeller = computed(() => {
  const role = authStore.user?.role;
  return role === 'Branch Seller' || role === 'Branch Seller / Cashier' || role === 'Cashier';
});

const alertRules = ref({
  low_stock_goes_to: 'Branch Admin and Main Manager',
  unreceived_reminder: 'After 24 hours',
  daily_summary: 'Owner, 20:00',
});

const availableCategories = computed(() => {
  if (isBranchSeller.value) {
    return ['Orders', 'Custom Cakes', 'Reports'];
  }
  return ['Orders', 'Custom Cakes', 'Reports', 'Stock', 'Transfers'];
});

const displayedNotifications = computed(() => {
  let list = notifications.value;
  if (filterOnlyUnread.value) {
    list = list.filter((n) => !n.is_read);
  }
  return list;
});

const loadNotifications = async () => {
  isLoading.value = true;
  try {
    const res = await api.get('/admin/notifications', {
      params: { category: activeCategory.value }
    });
    notifications.value = res.data.notifications || [];
    unreadCount.value = res.data.unread_count || 0;
    if (res.data.alert_rules) {
      alertRules.value = res.data.alert_rules;
    }
  } catch (err) {
    console.error('Failed to load notifications', err);
  } finally {
    isLoading.value = false;
  }
};

const setCategory = (cat) => {
  activeCategory.value = cat;
  loadNotifications();
};

const setFilterOnlyUnread = (val) => {
  filterOnlyUnread.value = val;
};

const markAllAsRead = async () => {
  isMarkingAll.value = true;
  try {
    await api.post('/admin/notifications/mark-read');
    notifications.value.forEach((n) => (n.is_read = true));
    unreadCount.value = 0;
    toastStore.success('All notifications marked as read');
  } catch (err) {
    console.error(err);
    toastStore.error('Could not mark all as read');
  } finally {
    isMarkingAll.value = false;
  }
};

const markSingleAsRead = async (notification) => {
  try {
    await api.patch(`/admin/notifications/${notification.notification_id}/read`);
    notification.is_read = true;
    if (unreadCount.value > 0) unreadCount.value--;
  } catch (err) {
    console.error(err);
  }
};

const saveAlertRules = async () => {
  isSavingRules.value = true;
  try {
    await api.post('/admin/notifications/alert-rules', alertRules.value);
    toastStore.success('Alert rules updated successfully');
  } catch (err) {
    toastStore.error(err.response?.data?.message || 'Failed to save alert rules');
  } finally {
    isSavingRules.value = false;
  }
};

const getIconConfig = (n) => {
  const text = `${n.title} ${n.message}`.toLowerCase();
  if (text.includes('preparing') || text.includes('kitchen') || text.includes('baking')) {
    return {
      icon: '👨‍🍳',
      class: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-400 dark:border-amber-900',
    };
  }
  if (text.includes('out for delivery') || text.includes('driver') || text.includes('dispatch')) {
    return {
      icon: '🚚',
      class: 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-950/40 dark:text-sky-400 dark:border-sky-900',
    };
  }
  if (text.includes('ready') || text.includes('pickup') || text.includes('counter')) {
    return {
      icon: '🛍️',
      class: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-900',
    };
  }
  if (text.includes('custom') || text.includes('cake') || n.category === 'Custom Cakes') {
    return {
      icon: '🎂',
      class: 'bg-rose-50 text-[#DE1B54] border-rose-200 dark:bg-rose-950/40 dark:text-rose-400 dark:border-rose-900',
    };
  }
  if (text.includes('report') || n.category === 'Reports') {
    return {
      icon: '📋',
      class: 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/40 dark:text-purple-400 dark:border-purple-900',
    };
  }
  return {
    icon: '📦',
    class: 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-400 dark:border-indigo-900',
  };
};

const getCategoryBadgeClass = (category) => {
  switch (category) {
    case 'Orders':
      return 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-900';
    case 'Custom Cakes':
      return 'bg-rose-50 text-[#DE1B54] border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-900';
    case 'Reports':
      return 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-900';
    case 'Stock':
      return 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-900';
    case 'Transfers':
      return 'bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-950/40 dark:text-orange-300 dark:border-orange-900';
    default:
      return 'bg-stone-100 text-stone-700 border-stone-200 dark:bg-stone-800 dark:text-stone-300 dark:border-stone-700';
  }
};

const getActionLabel = (n) => {
  const text = `${n.title} ${n.message}`.toLowerCase();
  if (text.includes('custom') || n.category === 'Custom Cakes') return 'View Cake';
  if (text.includes('report') || n.category === 'Reports') return 'Review Report';
  return 'View Order';
};

onMounted(() => {
  loadNotifications();
});
</script>
