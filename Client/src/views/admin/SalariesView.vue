<template>
  <div class="space-y-6 max-w-7xl mx-auto font-poppins">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div class="flex items-center space-x-3.5">
        <div class="w-12 h-12 rounded-2xl bg-[#FAF0E6] text-[#BA5420] flex items-center justify-center text-xl shrink-0 border border-[#F0DFD0] shadow-xs">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <div>
          <h2 class="text-xl sm:text-2xl font-serif font-bold text-[#2A1D15]">Salaries & Wage Management</h2>
          <p class="text-xs text-[#7A6B60] mt-0.5">Centralized payroll control and branch wage expense tracking</p>
        </div>
      </div>

      <!-- Quick Tab Switcher -->
      <div class="flex items-center space-x-2 bg-white border border-[#D8CDC0] rounded-xl p-1 shadow-xs">
        <button
          @click="activeTab = 'payroll'"
          class="px-3 py-1.5 text-xs font-semibold rounded-lg transition"
          :class="activeTab === 'payroll' ? 'bg-[#BA5420] text-white shadow-xs' : 'text-[#6A5A50] hover:text-[#2A1D15]'"
        >
          Branch Payroll
        </button>
        <button
          @click="activeTab = 'requests'"
          class="px-3 py-1.5 text-xs font-semibold rounded-lg transition flex items-center space-x-1.5"
          :class="activeTab === 'requests' ? 'bg-[#BA5420] text-white shadow-xs' : 'text-[#6A5A50] hover:text-[#2A1D15]'"
        >
          <span>Correction Requests</span>
          <span v-if="pendingRequestsCount > 0" class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-[#E53935] text-white">
            {{ pendingRequestsCount }}
          </span>
        </button>
      </div>
    </div>

    <!-- Alert / Status Messages -->
    <div v-if="successMsg" class="p-3 bg-[#EBF7EB] border border-[#C5E9C6] rounded-lg text-xs text-[#1E7422] flex items-center justify-between">
      <span>{{ successMsg }}</span>
      <button @click="successMsg = ''" class="text-[#1E7422] font-bold ml-2">&times;</button>
    </div>

    <!-- Top KPI Cards: Total Business Payroll & Analytics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-white border border-[#E9E0D4] rounded-2xl p-5 shadow-sm space-y-1">
        <div class="text-[11px] font-semibold text-[#8C7A6D] uppercase tracking-wider">Total Wage Expenses</div>
        <div class="text-2xl font-serif font-bold text-[#BA5420]">
          ETB {{ Number(summary.total_wage_expenses || 0).toLocaleString() }}
        </div>
        <p class="text-[11px] text-[#7A6B60]">Monthly business payroll</p>
      </div>

      <div class="bg-white border border-[#E9E0D4] rounded-2xl p-5 shadow-sm space-y-1">
        <div class="text-[11px] font-semibold text-[#8C7A6D] uppercase tracking-wider">Total Staff on Payroll</div>
        <div class="text-2xl font-serif font-bold text-[#2A1D15]">
          {{ summary.total_employees || 0 }} <span class="text-xs font-normal text-[#8A7B71]">employees</span>
        </div>
        <p class="text-[11px] text-[#7A6B60]">Across all bakery branches</p>
      </div>

      <div class="bg-white border border-[#E9E0D4] rounded-2xl p-5 shadow-sm space-y-1">
        <div class="text-[11px] font-semibold text-[#8C7A6D] uppercase tracking-wider">Average Monthly Wage</div>
        <div class="text-2xl font-serif font-bold text-[#2A1D15]">
          ETB {{ Number(summary.average_wage || 0).toLocaleString() }}
        </div>
        <p class="text-[11px] text-[#7A6B60]">Per staff member</p>
      </div>

      <div class="bg-white border border-[#E9E0D4] rounded-2xl p-5 shadow-sm space-y-1">
        <div class="text-[11px] font-semibold text-[#8C7A6D] uppercase tracking-wider">Pending Requests</div>
        <div class="text-2xl font-serif font-bold" :class="pendingRequestsCount > 0 ? 'text-[#E53935]' : 'text-[#2E7D32]'">
          {{ pendingRequestsCount }}
        </div>
        <p class="text-[11px] text-[#7A6B60]">Awaiting Super Admin review</p>
      </div>
    </div>

    <!-- TAB 1: Branch Payroll & Employees -->
    <div v-if="activeTab === 'payroll'" class="space-y-6">
      <!-- Branch by Branch Payroll Expense Breakdown Cards -->
      <div class="bg-white border border-[#E9E0D4] rounded-2xl shadow-sm p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-[#EFE8DF] pb-3">
          <h3 class="font-serif font-bold text-base text-[#2A1D15] flex items-center space-x-2">
            <span>🏪</span>
            <span>Branch-by-Branch Wage Expense Breakdown</span>
          </h3>
          <span class="text-xs text-[#8A7B71]">{{ branchBreakdown.length }} branches</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div
            v-for="b in branchBreakdown"
            :key="b.branch_id"
            class="bg-[#FAF7F2] border border-[#EFE4D6] rounded-xl p-4 space-y-2"
          >
            <div class="flex items-center justify-between">
              <span class="font-bold text-sm text-[#2A1D15]">{{ b.branch_name }}</span>
              <span class="font-mono text-[10px] text-[#8C7D73] font-semibold bg-white px-1.5 py-0.5 rounded border border-[#E8DACB]">{{ b.branch_code }}</span>
            </div>
            <div>
              <div class="text-lg font-serif font-bold text-[#BA5420]">
                ETB {{ Number(b.total_wage || 0).toLocaleString() }}
              </div>
              <div class="text-[11px] text-[#7A6B60] mt-0.5 flex justify-between">
                <span>Staff: {{ b.staff_count }} / {{ b.max_staff || 15 }}</span>
                <span>Avg: ETB {{ Number(b.avg_wage || 0).toLocaleString() }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- All Employees Salary Table with Super Admin Edit Action -->
      <div class="bg-white border border-[#E9E0D4] rounded-2xl shadow-sm overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-[#EFE8DF] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div>
            <h3 class="font-serif font-bold text-base text-[#2A1D15]">All Employees & Assigned Salaries</h3>
            <p class="text-xs text-[#7A6B60] mt-0.5">Control individual wages and log all salary adjustments to audit records</p>
          </div>

          <!-- Branch Filter -->
          <div class="flex items-center space-x-2">
            <label class="text-xs font-medium text-[#7D6E64]">Filter Branch:</label>
            <select
              v-model="selectedBranchFilter"
              @change="loadSalaryData"
              class="bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-2.5 py-1.5 text-xs text-[#2D231D]"
            >
              <option value="all">All branches</option>
              <option v-for="b in branchBreakdown" :key="b.branch_id" :value="b.branch_id">
                {{ b.branch_name }}
              </option>
            </select>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs min-w-[700px]">
            <thead class="bg-[#FAF6F0] text-[#88786B] uppercase font-semibold border-b border-[#EFE8DF]">
              <tr>
                <th class="py-3 px-5">Employee</th>
                <th class="py-3 px-4">Amharic (ተያዥ)</th>
                <th class="py-3 px-4">Role</th>
                <th class="py-3 px-4">Branch</th>
                <th class="py-3 px-4 text-right">Monthly Salary</th>
                <th class="py-3 px-4 text-center">Status</th>
                <th class="py-3 px-5 text-right">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#F3EDE4]">
              <tr v-for="emp in employees" :key="emp.user_id" class="hover:bg-[#FCFAF7] transition">
                <td class="py-3 px-5">
                  <div class="font-bold text-xs text-[#2A1D15]">{{ emp.full_name }}</div>
                  <div class="text-[11px] text-[#7A6B60]">{{ emp.email }}</div>
                </td>
                <td class="py-3 px-4 text-xs font-medium text-[#4A3B32]">
                  {{ emp.full_name_am || '—' }}
                </td>
                <td class="py-3 px-4">
                  <span
                    class="px-2 py-0.5 rounded-full text-[11px] font-semibold"
                    :class="{
                      'bg-[#FFF3E0] text-[#E65100]': emp.role === 'Super Admin',
                      'bg-[#EBF3FB] text-[#1565C0]': emp.role === 'Main Branch Manager',
                      'bg-[#FAF0E6] text-[#BA5420]': emp.role === 'Branch Manager' || emp.role === 'Branch Admin',
                      'bg-[#F3EFE9] text-[#5A4B41]': !['Super Admin', 'Main Branch Manager', 'Branch Manager', 'Branch Admin'].includes(emp.role)
                    }"
                  >
                    {{ emp.role }}
                  </span>
                </td>
                <td class="py-3 px-4 text-xs text-[#4A3B32] font-medium">{{ emp.branch_name }}</td>
                <td class="py-3 px-4 text-right font-serif font-bold text-sm text-[#BA5420]">
                  ETB {{ Number(emp.salary || 0).toLocaleString() }}
                </td>
                <td class="py-3 px-4 text-center">
                  <span
                    class="px-2 py-0.5 rounded-full text-[10px] font-semibold border"
                    :class="emp.status === 'Active' ? 'bg-[#EAF5EA] text-[#2E7D32] border-[#C8E6C9]' : 'bg-gray-100 text-gray-700 border-gray-200'"
                  >
                    {{ emp.status }}
                  </span>
                </td>
                <td class="py-3 px-5 text-right">
                  <button
                    @click="openEditSalaryModal(emp)"
                    class="inline-flex items-center space-x-1 px-3 py-1 text-xs font-semibold bg-white border border-[#D8CDC0] hover:bg-[#F4ECE3] text-[#3E3027] rounded-md shadow-2xs transition cursor-pointer"
                  >
                    <svg class="w-3 h-3 text-[#7A6B60]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                    <span>Edit Wage</span>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- TAB 2: Review Salary Correction Requests -->
    <div v-if="activeTab === 'requests'" class="bg-white border border-[#E9E0D4] rounded-2xl shadow-sm p-6 space-y-4">
      <div class="flex items-center justify-between border-b border-[#EFE8DF] pb-3">
        <div>
          <h3 class="font-serif font-bold text-base text-[#2A1D15]">Employee Salary Correction Requests</h3>
          <p class="text-xs text-[#7A6B60] mt-0.5">Review, approve, or reject formal wage adjustment requests from branch staff and managers</p>
        </div>
        <span class="text-xs text-[#8A7B71]">{{ requests.length }} requests total</span>
      </div>

      <div class="overflow-x-auto border border-[#EFE8DF] rounded-xl">
        <table class="w-full text-left text-xs min-w-[750px]">
          <thead class="bg-[#FAF6F0] text-[#88786B] uppercase font-semibold border-b border-[#EFE8DF]">
            <tr>
              <th class="py-3 px-4">Date</th>
              <th class="py-3 px-4">Employee</th>
              <th class="py-3 px-4">Branch</th>
              <th class="py-3 px-4">Current</th>
              <th class="py-3 px-4">Proposed</th>
              <th class="py-3 px-4">Reason</th>
              <th class="py-3 px-4 text-center">Status</th>
              <th class="py-3 px-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#F3EDE4]">
            <tr v-for="r in requests" :key="r.request_id" class="hover:bg-[#FCFAF7]">
              <td class="py-3 px-4 font-mono text-[#5A4A3E]">{{ new Date(r.created_at).toLocaleDateString() }}</td>
              <td class="py-3 px-4">
                <div class="font-semibold text-[#2D231D]">{{ r.user?.full_name }}</div>
                <div class="text-[10px] text-[#7A6B60]">{{ r.user?.role }}</div>
              </td>
              <td class="py-3 px-4 text-[#4A3B32] font-medium">{{ r.branch?.branch_name || 'HQ' }}</td>
              <td class="py-3 px-4 font-medium text-[#2D231D]">ETB {{ Number(r.current_salary).toLocaleString() }}</td>
              <td class="py-3 px-4 font-bold text-[#BA5420]">
                {{ r.requested_salary ? `ETB ${Number(r.requested_salary).toLocaleString()}` : '—' }}
              </td>
              <td class="py-3 px-4 max-w-xs text-[#4A3B32] truncate" :title="r.reason">{{ r.reason }}</td>
              <td class="py-3 px-4 text-center">
                <span
                  class="px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                  :class="{
                    'bg-amber-100 text-amber-800 border border-amber-200': r.status === 'Pending',
                    'bg-emerald-100 text-emerald-800 border border-emerald-200': r.status === 'Approved',
                    'bg-rose-100 text-rose-800 border border-rose-200': r.status === 'Rejected',
                  }"
                >
                  {{ r.status }}
                </span>
              </td>
              <td class="py-3 px-4 text-right space-x-1.5">
                <template v-if="r.status === 'Pending'">
                  <button
                    @click="reviewRequest(r, 'Approved')"
                    class="px-2.5 py-1 text-[11px] font-bold bg-[#2E7D32] hover:bg-[#256628] text-white rounded-md shadow-2xs cursor-pointer transition"
                  >
                    Approve
                  </button>
                  <button
                    @click="reviewRequest(r, 'Rejected')"
                    class="px-2.5 py-1 text-[11px] font-bold bg-white border border-[#D8CDC0] hover:bg-rose-50 text-rose-700 rounded-md shadow-2xs cursor-pointer transition"
                  >
                    Reject
                  </button>
                </template>
                <span v-else class="text-[11px] text-[#8C7D73] italic">Reviewed</span>
              </td>
            </tr>
            <tr v-if="requests.length === 0">
              <td colspan="8" class="py-6 text-center text-xs text-[#8A7B71]">
                No salary correction requests recorded.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- MODAL: Super Admin Edit Employee Salary -->
    <div v-if="isEditSalaryModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
      <div class="bg-white rounded-2xl shadow-2xl border border-[#E9E0D4] max-w-md w-full p-6 space-y-4 animate-in fade-in">
        <div class="flex items-center justify-between border-b border-[#EFE8DF] pb-3">
          <div>
            <h3 class="font-serif font-bold text-base text-[#2A1D15]">Update Employee Salary</h3>
            <p class="text-xs text-[#8A7B71]">{{ selectedEmployee?.full_name }} ({{ selectedEmployee?.role }})</p>
          </div>
          <button @click="isEditSalaryModalOpen = false" class="text-[#8A7B71] hover:text-[#2A1D15] text-xl font-bold">&times;</button>
        </div>

        <form @submit.prevent="submitUpdateSalary" class="space-y-4">
          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Assigned Branch</label>
            <input
              type="text"
              :value="selectedEmployee?.branch_name || 'HQ'"
              readonly
              class="w-full bg-[#F7F2EA] border border-[#DCD0C2] rounded-lg px-3 py-2 text-xs text-[#5A4A3E]"
            />
          </div>

          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">New Monthly Wage (ETB) *</label>
            <input
              v-model.number="editSalaryForm.salary"
              type="number"
              min="0"
              step="50"
              required
              class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs font-bold text-[#BA5420]"
            />
          </div>

          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Reason for Adjustment</label>
            <input
              v-model="editSalaryForm.reason"
              type="text"
              placeholder="e.g. Annual merit review, role promotion, branch alignment"
              class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
            />
          </div>

          <div class="p-3 bg-[#FCFAF7] border border-[#EFE8DF] rounded-lg text-[11px] text-[#8C7D73]">
            This salary update will be recorded in the official audit log with your Super Admin signature.
          </div>

          <div class="flex justify-end space-x-3 pt-2">
            <button
              type="button"
              @click="isEditSalaryModalOpen = false"
              class="px-4 py-2 text-xs font-medium text-[#6A5A50] hover:bg-[#F3EFE9] rounded-lg transition"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="isUpdatingSalary"
              class="px-5 py-2 text-xs font-semibold bg-[#BA5420] hover:bg-[#9E4314] text-white rounded-lg shadow-sm transition disabled:opacity-50"
            >
              {{ isUpdatingSalary ? 'Updating...' : 'Save Salary' }}
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

const activeTab = ref('payroll');
const summary = ref({});
const branchBreakdown = ref([]);
const employees = ref([]);
const requests = ref([]);
const selectedBranchFilter = ref('all');
const successMsg = ref('');

// Edit Salary Modal
const isEditSalaryModalOpen = ref(false);
const selectedEmployee = ref(null);
const isUpdatingSalary = ref(false);
const editSalaryForm = ref({
  salary: 0,
  reason: '',
});

const pendingRequestsCount = computed(() => {
  return requests.value.filter(r => r.status === 'Pending').length;
});

const loadSalaryData = async () => {
  try {
    const res = await api.get('/admin/salaries', {
      params: { branch_id: selectedBranchFilter.value }
    });
    summary.value = res.data.summary || {};
    branchBreakdown.value = res.data.branch_breakdown || [];
    employees.value = res.data.employees || [];
  } catch (err) {
    console.error('Failed to load salary data', err);
  }
};

const loadRequests = async () => {
  try {
    const res = await api.get('/admin/salary-requests');
    requests.value = res.data;
  } catch (err) {
    console.error('Failed to load requests', err);
  }
};

const openEditSalaryModal = (emp) => {
  selectedEmployee.value = emp;
  editSalaryForm.value = {
    salary: emp.salary || 0,
    reason: '',
  };
  isEditSalaryModalOpen.value = true;
};

const submitUpdateSalary = async () => {
  if (!selectedEmployee.value) return;
  isUpdatingSalary.value = true;
  try {
    await api.patch(`/admin/salaries/${selectedEmployee.value.user_id}`, editSalaryForm.value);
    isEditSalaryModalOpen.value = false;
    successMsg.value = `Salary for ${selectedEmployee.value.full_name} updated successfully to ETB ${Number(editSalaryForm.value.salary).toLocaleString()}.`;
    await loadSalaryData();
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to update salary.');
  } finally {
    isUpdatingSalary.value = false;
  }
};

const reviewRequest = async (requestItem, status) => {
  const notes = prompt(`Enter optional remarks for ${status.toLowerCase()} this salary request:`) || '';
  try {
    await api.patch(`/admin/salary-requests/${requestItem.request_id}/status`, {
      status,
      admin_notes: notes,
      approved_salary: requestItem.requested_salary,
    });
    successMsg.value = `Request from ${requestItem.user?.full_name} marked as ${status}.`;
    await Promise.all([loadRequests(), loadSalaryData()]);
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to review request.');
  }
};

onMounted(() => {
  loadSalaryData();
  loadRequests();
});
</script>
