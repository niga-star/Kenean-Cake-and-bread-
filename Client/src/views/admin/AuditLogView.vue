<template>
  <div class="space-y-6 max-w-7xl mx-auto font-poppins">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="dash-title text-xl sm:text-2xl">Audit Log</h2>
        <p class="dash-subtitle mt-0.5">
          Immutable chronological record of orders, price changes, custom cake workflows, transfers, and staff actions
        </p>
      </div>

      <div class="flex flex-wrap items-center gap-2.5">
        <!-- Scope Badge -->
        <span
          v-if="isSuperAdmin"
          class="dash-badge-pending text-xs py-1.5 px-3"
        >
          <span class="w-1.5 h-1.5 rounded-full bg-[#B87A14]"></span>
          Scope: All Branches
        </span>
        <span
          v-else
          class="dash-badge-neutral text-xs py-1.5 px-3"
        >
          <span class="w-1.5 h-1.5 rounded-full bg-[#2E7D32]"></span>
          Scope: {{ userBranchName || 'Assigned Branch' }}
        </span>

        <span class="dash-badge-neutral text-xs py-1.5 px-2.5">
          🔒 Read Only
        </span>

        <button
          @click="exportCSV"
          class="dash-btn-secondary text-xs sm:text-sm py-1.5 px-3.5"
        >
          <span>📥</span>
          <span>Export CSV</span>
        </button>
      </div>
    </div>

    <!-- Filter Control Bar -->
    <div class="dash-card p-4 space-y-3">
      <!-- Top Filter Row: Search, Branch, User, Action -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <!-- Search Input -->
        <div>
          <label class="block text-[11px] font-bold text-[#8C7D73] uppercase tracking-wider mb-1">Search</label>
          <div class="relative">
            <input
              v-model="filters.search"
              @input="onSearchInput"
              type="text"
              placeholder="Record, user, or action..."
              class="w-full bg-[#FDFBF7] border border-[#DDD2C5] rounded-xl pl-8 pr-3 py-2 text-xs text-[#2D231D] focus:ring-2 focus:ring-[#BA5420] focus:border-transparent outline-none transition"
            />
            <span class="absolute left-2.5 top-2.5 text-xs text-[#A8988B]">🔍</span>
          </div>
        </div>

        <!-- Branch Filter -->
        <div>
          <label class="block text-[11px] font-bold text-[#8C7D73] uppercase tracking-wider mb-1">Branch</label>
          <div v-if="isSuperAdmin">
            <select
              v-model="filters.branch_id"
              @change="loadLogs"
              class="w-full bg-[#FDFBF7] border border-[#DDD2C5] rounded-xl px-3 py-2 text-xs text-[#2D231D] focus:ring-2 focus:ring-[#BA5420] outline-none transition cursor-pointer"
            >
              <option value="all">All Branches</option>
              <option v-for="b in branchesList" :key="b.branch_id" :value="b.branch_id">
                {{ b.branch_name }}
              </option>
            </select>
          </div>
          <div v-else class="bg-[#F6EFE7] border border-[#DDD2C5] rounded-xl px-3 py-2 text-xs text-[#55463C] font-medium flex items-center justify-between">
            <span>{{ userBranchName || 'My Branch' }}</span>
            <span class="text-[10px] uppercase font-semibold text-[#8C7D73]">Locked</span>
          </div>
        </div>

        <!-- User Filter -->
        <div>
          <label class="block text-[11px] font-bold text-[#8C7D73] uppercase tracking-wider mb-1">Actor / User</label>
          <select
            v-model="filters.user_id"
            @change="loadLogs"
            class="w-full bg-[#FDFBF7] border border-[#DDD2C5] rounded-xl px-3 py-2 text-xs text-[#2D231D] focus:ring-2 focus:ring-[#BA5420] outline-none transition cursor-pointer"
          >
            <option value="all">All Actors</option>
            <option v-for="u in usersList" :key="u.user_id" :value="u.user_id">
              {{ u.full_name }} ({{ u.role }})
            </option>
          </select>
        </div>

        <!-- Action Filter -->
        <div>
          <label class="block text-[11px] font-bold text-[#8C7D73] uppercase tracking-wider mb-1">Action Type</label>
          <select
            v-model="filters.action"
            @change="loadLogs"
            class="w-full bg-[#FDFBF7] border border-[#DDD2C5] rounded-xl px-3 py-2 text-xs text-[#2D231D] focus:ring-2 focus:ring-[#BA5420] outline-none transition cursor-pointer"
          >
            <option value="all">All Actions</option>
            <option v-for="act in actionsList" :key="act" :value="act">
              {{ act }}
            </option>
          </select>
        </div>
      </div>

      <!-- Date Presets & Custom Pickers -->
      <div class="pt-2 border-t border-[#F2EAE1] flex flex-wrap items-center justify-between gap-3">
        <!-- Date Preset Buttons -->
        <div class="flex flex-wrap items-center gap-1.5">
          <span class="text-[11px] font-bold text-[#8C7D73] uppercase tracking-wider mr-1">Period:</span>
          <button
            v-for="preset in datePresets"
            :key="preset.id"
            @click="setPreset(preset.id)"
            class="px-2.5 py-1 text-xs rounded-lg font-medium transition cursor-pointer"
            :class="activeDatePreset === preset.id ? 'bg-[#BA5420] text-white shadow-xs' : 'bg-[#F4ECE3] text-[#55463C] hover:bg-[#EAE0D4]'"
          >
            {{ preset.label }}
          </button>
        </div>

        <!-- Custom Date Range Inputs (Visible when Custom is chosen) -->
        <div v-if="activeDatePreset === 'custom'" class="flex items-center gap-2">
          <div class="flex items-center gap-1.5">
            <span class="text-xs text-[#8C7D73]">From:</span>
            <input
              v-model="filters.from_date"
              @change="loadLogs"
              type="date"
              class="bg-[#FDFBF7] border border-[#DDD2C5] rounded-lg px-2.5 py-1 text-xs text-[#2D231D]"
            />
          </div>
          <div class="flex items-center gap-1.5">
            <span class="text-xs text-[#8C7D73]">To:</span>
            <input
              v-model="filters.to_date"
              @change="loadLogs"
              type="date"
              class="bg-[#FDFBF7] border border-[#DDD2C5] rounded-lg px-2.5 py-1 text-xs text-[#2D231D]"
            />
          </div>
        </div>

        <!-- Reset Button -->
        <button
          v-if="hasActiveFilters"
          @click="resetFilters"
          class="text-xs text-[#BA5420] hover:text-[#913B0E] font-medium transition cursor-pointer flex items-center gap-1"
        >
          <span>✕</span>
          <span>Clear Filters</span>
        </button>
      </div>
    </div>

    <!-- Audit Logs Summary & Table -->
    <div class="bg-white border border-[#E9E0D4] rounded-2xl shadow-xs overflow-hidden">
      <!-- Records Count Bar -->
      <div class="px-5 py-3 bg-[#FAF6F0] border-b border-[#EFE8DF] flex items-center justify-between text-xs text-[#6A5A4E]">
        <div class="font-medium">
          Showing <span class="font-bold text-[#2A1D15]">{{ logs.length }}</span> audit records
        </div>
        <div class="text-[11px] text-[#8A7B71]">
          Chronologically sorted · Latest first
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm min-w-[760px]">
          <thead class="bg-[#FAF6F0] text-[11px] font-bold text-[#827165] tracking-wider uppercase border-b border-[#EFE8DF]">
            <tr>
              <th class="py-3 px-5">Time & Date</th>
              <th class="py-3 px-4">Actor</th>
              <th class="py-3 px-4">Action</th>
              <th class="py-3 px-4">Record</th>
              <th class="py-3 px-4">Previous Value</th>
              <th class="py-3 px-5">New Value</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#F3EDE4]">
            <tr v-if="loading">
              <td colspan="6" class="py-8 text-center text-xs text-[#8C7D73]">
                Loading audit records...
              </td>
            </tr>
            <tr v-else-if="logs.length === 0">
              <td colspan="6" class="py-10 text-center text-xs text-[#8C7D73]">
                No audit records found matching the selected filters.
              </td>
            </tr>
            <tr
              v-else
              v-for="log in logs"
              :key="log.log_id"
              class="hover:bg-[#FCFAF7] transition text-xs"
            >
              <!-- Timestamp -->
              <td class="py-3.5 px-5 whitespace-nowrap">
                <div class="font-mono text-[#382C24] font-semibold">{{ log.time }}</div>
                <div class="text-[10px] text-[#8C7D73]">{{ log.date }}</div>
              </td>

              <!-- Actor -->
              <td class="py-3.5 px-4 font-medium text-[#382C24]">
                <div class="text-xs font-semibold text-[#2A1D15]">{{ log.user }}</div>
              </td>

              <!-- Action with badge -->
              <td class="py-3.5 px-4">
                <span
                  class="inline-block px-2.5 py-1 text-[11px] font-semibold rounded-lg border whitespace-nowrap"
                  :class="getActionBadgeClass(log.action)"
                >
                  {{ log.action }}
                </span>
              </td>

              <!-- Record -->
              <td class="py-3.5 px-4 font-mono text-[#4A3B32]">
                {{ log.record }}
              </td>

              <!-- Old value -->
              <td class="py-3.5 px-4 text-[#7A6B60] max-w-[200px] truncate" :title="log.old_value">
                {{ log.old_value }}
              </td>

              <!-- New value -->
              <td class="py-3.5 px-5 font-medium text-[#241710] max-w-[220px] truncate" :title="log.new_value">
                {{ log.new_value }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Footer Policy Notice -->
      <div class="p-4 bg-[#FCFAF7] border-t border-[#EFE8DF] text-xs text-[#8A7B71] flex items-center justify-between">
        <span>
          🛡️ Audit logs are immutable records generated on all branch activities. Edits and deletions are prohibited.
        </span>
        <span class="font-medium text-[#55463C]">
          SweetBakes Compliance Audit Trail
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '../../api';

const logs = ref([]);
const usersList = ref([]);
const actionsList = ref([]);
const branchesList = ref([]);
const loading = ref(false);
const isSuperAdmin = ref(false);
const scopedBranchId = ref(null);
const userBranchName = ref('');

const activeDatePreset = ref('all');

const datePresets = [
  { id: 'all', label: 'All Time' },
  { id: 'today', label: 'Today' },
  { id: 'this_week', label: 'This Week' },
  { id: 'this_month', label: 'This Month' },
  { id: 'custom', label: 'Custom Range' },
];

const filters = ref({
  search: '',
  branch_id: 'all',
  user_id: 'all',
  action: 'all',
  date_filter: 'all',
  from_date: '',
  to_date: '',
});

let searchTimeout = null;
const onSearchInput = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    loadLogs();
  }, 350);
};

const setPreset = (presetId) => {
  activeDatePreset.value = presetId;
  filters.value.date_filter = presetId;
  if (presetId !== 'custom') {
    filters.value.from_date = '';
    filters.value.to_date = '';
  }
  loadLogs();
};

const hasActiveFilters = computed(() => {
  return (
    filters.value.search !== '' ||
    filters.value.branch_id !== 'all' ||
    filters.value.user_id !== 'all' ||
    filters.value.action !== 'all' ||
    activeDatePreset.value !== 'all'
  );
});

const resetFilters = () => {
  filters.value.search = '';
  filters.value.branch_id = 'all';
  filters.value.user_id = 'all';
  filters.value.action = 'all';
  filters.value.date_filter = 'all';
  filters.value.from_date = '';
  filters.value.to_date = '';
  activeDatePreset.value = 'all';
  loadLogs();
};

const getActionBadgeClass = (action) => {
  if (!action) return 'bg-gray-100 text-gray-700 border-gray-200';
  const a = action.toLowerCase();
  if (a.includes('deleted') || a.includes('cancel')) {
    return 'bg-[#FDF2F2] text-[#9B1C1C] border-[#FBD5D5]';
  }
  if (a.includes('created') || a.includes('placed') || a.includes('received') || a.includes('accepted')) {
    return 'bg-[#EDF7ED] text-[#1E4620] border-[#C8E6C9]';
  }
  if (a.includes('price') || a.includes('status') || a.includes('dispatch') || a.includes('quote')) {
    return 'bg-[#FFF8E1] text-[#8D6B00] border-[#FFE082]';
  }
  if (a.includes('report') || a.includes('request') || a.includes('salary')) {
    return 'bg-[#EBF5FB] text-[#1B4F72] border-[#AED6F1]';
  }
  return 'bg-[#F4ECE3] text-[#55463C] border-[#E0D4C5]';
};

const loadLogs = async () => {
  loading.value = true;
  try {
    const params = { ...filters.value };
    if (activeDatePreset.value === 'custom') {
      params.start_date = filters.value.from_date;
      params.end_date = filters.value.to_date;
    }

    const res = await api.get('/admin/audit-logs', { params });
    logs.value = res.data.logs || [];
    usersList.value = res.data.users || [];
    actionsList.value = res.data.actions || [];
    branchesList.value = res.data.branches || [];
    isSuperAdmin.value = !!res.data.is_super_admin;
    scopedBranchId.value = res.data.scoped_branch_id;

    if (scopedBranchId.value && branchesList.value.length > 0) {
      const b = branchesList.value.find((br) => br.branch_id === scopedBranchId.value);
      if (b) userBranchName.value = b.branch_name;
    }
  } catch (err) {
    console.error('Failed to load audit logs', err);
  } finally {
    loading.value = false;
  }
};

const exportCSV = () => {
  const headers = ['Time', 'Date', 'User', 'Action', 'Record', 'Old Value', 'New Value'];
  const rows = logs.value.map((l) => [
    `"${l.time}"`,
    `"${l.date}"`,
    `"${l.user}"`,
    `"${l.action}"`,
    `"${l.record}"`,
    `"${l.old_value}"`,
    `"${l.new_value}"`,
  ]);
  const csvContent =
    'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows.map((e) => e.join(','))].join('\n');
  const encodedUri = encodeURI(csvContent);
  const link = document.createElement('a');
  link.setAttribute('href', encodedUri);
  link.setAttribute('download', `sweetbakes_audit_logs_${new Date().toISOString().split('T')[0]}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

onMounted(async () => {
  // Grab current logged in user details if available
  try {
    const meRes = await api.get('/auth/user');
    if (meRes.data) {
      isSuperAdmin.value = meRes.data.role === 'Super Admin';
      if (meRes.data.branch) {
        userBranchName.value = meRes.data.branch.branch_name;
      }
    }
  } catch (e) {}

  loadLogs();
});
</script>
