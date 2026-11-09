<template>
  <div class="space-y-6 max-w-5xl mx-auto font-poppins">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div class="flex items-center space-x-3.5">
        <div class="w-11 h-11 rounded-2xl bg-[#F6EFE6] text-[#3D251E] flex items-center justify-center text-xl shrink-0 border border-[#E8DFD3] shadow-xs">
          <svg class="w-5 h-5 stroke-[1.8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
        </div>
        <div>
          <h2 class="dash-title text-xl sm:text-2xl">My Staff Account</h2>
          <p class="dash-subtitle mt-0.5">Your official employee profile and salary details</p>
        </div>
      </div>

      <button
        @click="openRequestModal"
        class="dash-btn-primary text-xs sm:text-sm py-1.5 px-3.5"
      >
        <svg class="w-4 h-4 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
        </svg>
        <span>Request Correction</span>
      </button>
    </div>

    <!-- Alert / Status Messages -->
    <div v-if="successMsg" class="p-3 bg-[#EBF7EB] border border-[#C5E9C6] rounded-lg text-xs text-[#1E7422] flex items-center justify-between">
      <span>{{ successMsg }}</span>
      <button @click="successMsg = ''" class="text-[#1E7422] font-bold ml-2">&times;</button>
    </div>

    <!-- Top Profile Summary Card -->
    <div class="bg-white border border-[#E9E0D4] rounded-2xl shadow-sm p-6 sm:p-7 relative overflow-hidden">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
        <div class="flex items-center space-x-4">
          <div class="w-20 h-20 rounded-2xl overflow-hidden bg-[#FAF0E6] border-2 border-amber-300/40 shrink-0 shadow-sm flex items-center justify-center text-white text-2xl font-bold">
            <img
              src="/mockup/avatar_mekdes.jpg"
              alt="Avatar"
              class="w-full h-full object-cover"
              onerror="this.style.display='none'"
            />
          </div>
          <div>
            <div class="flex items-center space-x-2">
              <h3 class="text-lg sm:text-xl font-serif font-bold text-[#2A1D15]">{{ userProfile.full_name }}</h3>
              <span v-if="userProfile.full_name_am" class="text-xs px-2 py-0.5 rounded-md bg-[#FAF0E6] text-[#BA5420] font-medium border border-[#F0DFD0]">
                {{ userProfile.full_name_am }}
              </span>
            </div>
            <p class="text-xs text-[#7A6B60] mt-0.5">{{ userProfile.email }}</p>
            <div class="flex flex-wrap items-center gap-2 mt-2.5">
              <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#FFF3E0] text-[#E65100] border border-[#FFE0B2]">
                {{ displayRole }}
              </span>
              <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-medium bg-[#F6F2EC] text-[#5A4A3E] border border-[#E5DACD]">
                🏢 {{ userProfile.branch?.branch_name || 'All Branches (HQ)' }}
              </span>
              <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#EAF5EA] text-[#2E7D32] border border-[#C8E6C9]">
                ● {{ userProfile.status || 'Active' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Monthly Salary Highlight Badge -->
        <div class="bg-gradient-to-br from-[#FAF4ED] to-[#F5ECE0] border border-[#E8DACB] rounded-2xl p-4 sm:p-5 text-right shrink-0">
          <div class="text-[11px] font-semibold text-[#8C7A6D] uppercase tracking-wider">Assigned Monthly Wage</div>
          <div class="text-2xl sm:text-3xl font-serif font-extrabold text-[#BA5420] mt-1">
            ETB {{ Number(userProfile.salary || 0).toLocaleString() }}
          </div>
          <div class="text-[11px] text-[#7A6B60] mt-1 flex items-center justify-end space-x-1">
            <svg class="w-3.5 h-3.5 text-[#2E7D32]" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span>Managed by Super Admin</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Two Columns: Official Employment Details + Role Permissions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
      <!-- Left: Employment Details -->
      <div class="bg-white border border-[#E9E0D4] rounded-2xl shadow-sm p-6 space-y-4">
        <h3 class="font-serif font-bold text-base text-[#2A1D15] flex items-center space-x-2">
          <span>📋</span>
          <span>Employment Record</span>
        </h3>

        <div class="divide-y divide-[#F3EDE4] text-xs">
          <div class="py-2.5 flex justify-between">
            <span class="text-[#88786B]">Full Name (English)</span>
            <span class="font-semibold text-[#2D231D]">{{ userProfile.full_name }}</span>
          </div>
          <div class="py-2.5 flex justify-between">
            <span class="text-[#88786B]">Full Name in Amharic (ተያዥ)</span>
            <span class="font-semibold text-[#2D231D]">{{ userProfile.full_name_am || 'Not specified' }}</span>
          </div>
          <div class="py-2.5 flex justify-between">
            <span class="text-[#88786B]">Official Staff Role</span>
            <span class="font-semibold text-[#2D231D]">{{ displayRole }}</span>
          </div>
          <div class="py-2.5 flex justify-between">
            <span class="text-[#88786B]">Assigned Branch</span>
            <span class="font-semibold text-[#2D231D]">{{ userProfile.branch?.branch_name || 'All Branches' }}</span>
          </div>
          <div class="py-2.5 flex justify-between">
            <span class="text-[#88786B]">Branch Code</span>
            <span class="font-mono font-semibold text-[#5A4A3E]">{{ userProfile.branch?.branch_code || 'HQ' }}</span>
          </div>
          <div class="py-2.5 flex justify-between">
            <span class="text-[#88786B]">Contact Phone</span>
            <span class="font-mono font-semibold text-[#2D231D]">{{ userProfile.phone || '+251 91 100 0001' }}</span>
          </div>
          <div class="py-2.5 flex justify-between">
            <span class="text-[#88786B]">Employment Status</span>
            <span class="font-semibold text-[#2E7D32]">{{ userProfile.employment_status || 'Active' }}</span>
          </div>
          <div class="py-2.5 flex justify-between">
            <span class="text-[#88786B]">Hire Date</span>
            <span class="font-semibold text-[#2D231D]">{{ userProfile.hire_date || '2024-01-15' }}</span>
          </div>
        </div>

        <div class="p-3 bg-[#FCFAF7] border border-[#EFE8DF] rounded-xl text-[11px] text-[#8C7D73] leading-relaxed">
          <strong class="text-[#5A4A3E]">Security Policy:</strong> To protect business records and payroll integrity, staff cannot directly edit personal or employment details. If a correction is needed, please use the request button above.
        </div>
      </div>

      <!-- Right: Role Dashboard Permissions & Responsibilities -->
      <div class="bg-white border border-[#E9E0D4] rounded-2xl shadow-sm p-6 space-y-4">
        <h3 class="font-serif font-bold text-base text-[#2A1D15] flex items-center space-x-2">
          <span>🛡️</span>
          <span>Role Scope & Permissions</span>
        </h3>

        <div class="space-y-3 text-xs">
          <div class="p-3.5 bg-[#FAF7F2] border border-[#EBDDCF] rounded-xl space-y-1">
            <div class="font-semibold text-[#3A2E26]">Hierarchy Reporting Path</div>
            <p class="text-[#7A6B60] text-[11px]">
              {{ reportingHierarchy }}
            </p>
          </div>

          <div class="p-3.5 bg-[#FAF7F2] border border-[#EBDDCF] rounded-xl space-y-2">
            <div class="font-semibold text-[#3A2E26]">Assigned Responsibilities</div>
            <ul class="list-disc list-inside space-y-1 text-[#6D5D51] text-[11px]">
              <li v-for="(resp, i) in roleResponsibilities" :key="i">{{ resp }}</li>
            </ul>
          </div>

          <div class="p-3.5 bg-[#FAF7F2] border border-[#EBDDCF] rounded-xl space-y-1">
            <div class="font-semibold text-[#3A2E26]">Need to Submit a Shift or Sales Report?</div>
            <p class="text-[#7A6B60] text-[11px]">
              Staff members can submit daily sales summaries, task updates, or delivery logs directly from the
              <router-link to="/admin/staff-reports" class="text-[#BA5420] font-semibold underline ml-1">Staff Reports</router-link>
              portal.
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Previous Salary Correction Requests History -->
    <div class="bg-white border border-[#E9E0D4] rounded-2xl shadow-sm p-6 space-y-4">
      <div class="flex items-center justify-between">
        <h3 class="font-serif font-bold text-base text-[#2A1D15] flex items-center space-x-2">
          <span>📝</span>
          <span>My Correction Requests to Super Admin</span>
        </h3>
        <span class="text-xs text-[#8A7B71]">{{ requests.length }} requests recorded</span>
      </div>

      <div class="overflow-x-auto border border-[#EFE8DF] rounded-xl">
        <table class="w-full text-left text-xs">
          <thead class="bg-[#FAF6F0] text-[#88786B] uppercase font-semibold border-b border-[#EFE8DF]">
            <tr>
              <th class="py-3 px-4">Date</th>
              <th class="py-3 px-4">Current Salary</th>
              <th class="py-3 px-4">Requested Salary</th>
              <th class="py-3 px-4">Reason / Notes</th>
              <th class="py-3 px-4 text-center">Status</th>
              <th class="py-3 px-4">Admin Remarks</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#F3EDE4]">
            <tr v-for="r in requests" :key="r.request_id" class="hover:bg-[#FCFAF7]">
              <td class="py-3 px-4 font-mono text-[#5A4A3E]">{{ new Date(r.created_at).toLocaleDateString() }}</td>
              <td class="py-3 px-4 font-medium text-[#2D231D]">ETB {{ Number(r.current_salary).toLocaleString() }}</td>
              <td class="py-3 px-4 font-bold text-[#BA5420]">
                {{ r.requested_salary ? `ETB ${Number(r.requested_salary).toLocaleString()}` : '—' }}
              </td>
              <td class="py-3 px-4 max-w-xs text-[#4A3B32] truncate" :title="r.reason">{{ r.reason }}</td>
              <td class="py-3 px-4 text-center">
                <span
                  class="px-2.5 py-0.5 rounded-full text-[11px] font-bold"
                  :class="{
                    'bg-amber-100 text-amber-800 border border-amber-200': r.status === 'Pending',
                    'bg-emerald-100 text-emerald-800 border border-emerald-200': r.status === 'Approved',
                    'bg-rose-100 text-rose-800 border border-rose-200': r.status === 'Rejected',
                  }"
                >
                  {{ r.status }}
                </span>
              </td>
              <td class="py-3 px-4 text-[#7A6B60] italic">{{ r.admin_notes || '—' }}</td>
            </tr>
            <tr v-if="requests.length === 0">
              <td colspan="6" class="py-6 text-center text-xs text-[#8A7B71]">
                No correction requests submitted yet.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- MODAL: Request Correction from Super Admin -->
    <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
      <div class="bg-white rounded-2xl shadow-2xl border border-[#E9E0D4] max-w-md w-full p-6 space-y-4 animate-in fade-in">
        <div class="flex items-center justify-between border-b border-[#EFE8DF] pb-3">
          <h3 class="font-serif font-bold text-base text-[#2A1D15]">Request Profile / Salary Correction</h3>
          <button @click="isModalOpen = false" class="text-[#8A7B71] hover:text-[#2A1D15] text-xl font-bold">&times;</button>
        </div>

        <form @submit.prevent="submitRequest" class="space-y-4">
          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Current Monthly Salary</label>
            <input
              type="text"
              :value="`ETB ${Number(userProfile.salary || 0).toLocaleString()}`"
              readonly
              class="w-full bg-[#F7F2EA] border border-[#DCD0C2] rounded-lg px-3 py-2 text-xs font-mono text-[#5A4A3E]"
            />
          </div>

          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Proposed Salary / Wage (ETB) - Optional</label>
            <input
              v-model.number="form.requested_salary"
              type="number"
              min="0"
              placeholder="e.g. 11000"
              class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
            />
          </div>

          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Reason for Request *</label>
            <textarea
              v-model="form.reason"
              rows="4"
              required
              placeholder="Please describe why this salary correction or employment profile change is requested..."
              class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
            ></textarea>
          </div>

          <div class="p-3 bg-[#FCFAF7] border border-[#EFE8DF] rounded-lg text-[11px] text-[#8C7D73]">
            This request will be delivered directly to the Super Admin for formal review and audit log verification.
          </div>

          <div class="flex justify-end space-x-3 pt-2">
            <button
              type="button"
              @click="isModalOpen = false"
              class="px-4 py-2 text-xs font-medium text-[#6A5A50] hover:bg-[#F3EFE9] rounded-lg transition"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="isSubmitting"
              class="px-5 py-2 text-xs font-semibold bg-[#BA5420] hover:bg-[#9E4314] text-white rounded-lg shadow-sm transition disabled:opacity-50"
            >
              {{ isSubmitting ? 'Submitting...' : 'Submit Request' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '../../stores/auth';
import api from '../../api';

const authStore = useAuthStore();
const userProfile = ref({ ...authStore.user });
const requests = ref([]);
const isModalOpen = ref(false);
const isSubmitting = ref(false);
const successMsg = ref('');

const form = ref({
  requested_salary: null,
  reason: '',
});

const displayRole = computed(() => {
  if (userProfile.value.role === 'Branch Admin') return 'Branch Manager';
  return userProfile.value.role || 'Staff Member';
});

const reportingHierarchy = computed(() => {
  const role = userProfile.value.role;
  if (role === 'Super Admin') return 'Executive Administrator → Board / Business Owner';
  if (role === 'Main Branch Manager') return 'Main Branch Manager → Super Admin';
  if (['Branch Manager', 'Branch Admin'].includes(role)) return 'Branch Manager → Super Admin';
  return 'Branch Staff (Seller / Servant / Delivery) → Branch Manager → Super Admin';
});

const roleResponsibilities = computed(() => {
  const role = userProfile.value.role;
  if (role === 'Super Admin') {
    return [
      'Full business authority across all branches, managers, and staff.',
      'Sole administrator with permission to edit salaries and wages.',
      'Control branch staff quotas and global product pricing.',
    ];
  }
  if (['Branch Manager', 'Branch Admin'].includes(role)) {
    return [
      'Manage assigned branch staff (sellers, servants, delivery staff).',
      'Monitor branch sales, inventory, and order preparation.',
      'Receive staff reports and submit consolidated reports to Super Admin.',
    ];
  }
  if (['Branch Seller', 'Branch Seller / Cashier', 'Cashier'].includes(role)) {
    return [
      'Manage customer checkout, counter sales, and branch orders.',
      'View branch product stock and prices.',
      'Submit daily sales summary reports to the Branch Manager.',
    ];
  }
  if (role === 'Delivery Staff') {
    return [
      'Dispatch and fulfill customer delivery orders for assigned branch zones.',
      'Report courier completion status and transit logs to Branch Manager.',
    ];
  }
  if (role === 'Servant') {
    return [
      'Support bakery operations, food preparation, and kitchen sanitation.',
      'Submit daily shift and task reports to Branch Manager.',
    ];
  }
  return [
    'Perform assigned branch duties with professional diligence.',
    'Submit routine task reports to Branch Manager.',
  ];
});

const loadRequests = async () => {
  try {
    const res = await api.get('/admin/salary-requests');
    requests.value = res.data;
  } catch (err) {
    console.error('Failed to load salary requests', err);
  }
};

const openRequestModal = () => {
  form.value = {
    requested_salary: null,
    reason: '',
  };
  isModalOpen.value = true;
};

const submitRequest = async () => {
  isSubmitting.value = true;
  try {
    await api.post('/admin/salary-requests', form.value);
    isModalOpen.value = false;
    successMsg.value = 'Salary correction request submitted successfully to the Super Admin.';
    await loadRequests();
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to submit correction request.');
  } finally {
    isSubmitting.value = false;
  }
};

onMounted(() => {
  loadRequests();
});
</script>

