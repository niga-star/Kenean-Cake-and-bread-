<template>
  <div class="space-y-6 max-w-7xl mx-auto font-poppins">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 dash-card p-5">
      <div class="flex items-center space-x-3.5">
        <div class="w-11 h-11 rounded-2xl bg-[#F6EFE6] text-[#3D251E] flex items-center justify-center text-xl shrink-0 border border-[#E8DFD3] shadow-xs">
          <svg class="w-5 h-5 stroke-[1.8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <div>
          <h1 class="dash-title text-xl sm:text-2xl">Salary &amp; Wage Management</h1>
          <p class="dash-subtitle mt-0.5">Centralized payroll control, branch wage expense breakdown, and salary adjustments.</p>
        </div>
      </div>

      <!-- Quick Tab Switcher -->
      <div class="flex items-center space-x-2 bg-[#FAF7F2] border border-[#E8DFD5] rounded-xl p-1 shadow-xs">
        <button
          @click="activeTab = 'payroll'"
          class="transition cursor-pointer"
          :class="activeTab === 'payroll' ? 'dash-pill-active' : 'dash-pill-inactive'"
        >
          Branch Payroll
        </button>
        <button
          @click="activeTab = 'requests'"
          class="transition cursor-pointer flex items-center space-x-1.5"
          :class="activeTab === 'requests' ? 'dash-pill-active' : 'dash-pill-inactive'"
        >
          <span>Correction Requests</span>
          <span v-if="pendingRequestsCount > 0" class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-[#DE1B54] text-white">
            {{ pendingRequestsCount }}
          </span>
        </button>
      </div>
    </div>

    <!-- Alert / Status Messages -->
    <div v-if="successMsg" class="p-3 bg-[#EBF7EB] border border-[#C5E9C6] rounded-xl text-xs text-[#1E7422] flex items-center justify-between">
      <span>{{ successMsg }}</span>
      <button @click="successMsg = ''" class="text-[#1E7422] font-bold ml-2">&times;</button>
    </div>

    <!-- Top KPI Cards: Combined Business Payroll Expenses -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-white border border-[#E9E0D4] rounded-2xl p-5 shadow-sm space-y-1">
        <div class="text-[11px] font-semibold text-[#8C7A6D] uppercase tracking-wider">Combined Salary Expenses</div>
        <div class="text-2xl font-serif font-bold text-[#BA5420]">
          ETB {{ Number(summary.total_wage_expenses || 0).toLocaleString() }}
        </div>
        <p class="text-[11px] text-[#7A6B60]">Total monthly payroll for all branches</p>
      </div>

      <div class="bg-white border border-[#E9E0D4] rounded-2xl p-5 shadow-sm space-y-1">
        <div class="text-[11px] font-semibold text-[#8C7A6D] uppercase tracking-wider">Total Staff on Payroll</div>
        <div class="text-2xl font-serif font-bold text-[#2A1D15]">
          {{ summary.total_employees || 0 }} <span class="text-xs font-normal text-[#8A7B71]">employees</span>
        </div>
        <p class="text-[11px] text-[#7A6B60]">Managers, sellers, servants & delivery</p>
      </div>

      <div class="bg-white border border-[#E9E0D4] rounded-2xl p-5 shadow-sm space-y-1">
        <div class="text-[11px] font-semibold text-[#8C7A6D] uppercase tracking-wider">Average Monthly Wage</div>
        <div class="text-2xl font-serif font-bold text-[#2A1D15]">
          ETB {{ Number(summary.average_wage || 0).toLocaleString() }}
        </div>
        <p class="text-[11px] text-[#7A6B60]">Across all operating locations</p>
      </div>

      <div class="bg-white border border-[#E9E0D4] rounded-2xl p-5 shadow-sm space-y-1">
        <div class="text-[11px] font-semibold text-[#8C7A6D] uppercase tracking-wider">Pending Correction Requests</div>
        <div class="text-2xl font-serif font-bold" :class="pendingRequestsCount > 0 ? 'text-[#E53935]' : 'text-[#2E7D32]'">
          {{ pendingRequestsCount }}
        </div>
        <p class="text-[11px] text-[#7A6B60]">Submitted by branch employees</p>
      </div>
    </div>

    <!-- TAB 1: Branch Payroll & Employees -->
    <div v-if="activeTab === 'payroll'" class="space-y-6">
      <!-- Section 5: CLICKABLE BRANCH DETAILS GRID -->
      <div class="bg-white border border-[#E9E0D4] rounded-2xl shadow-sm p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-[#EFE8DF] pb-3">
          <div>
            <h3 class="font-serif font-bold text-base sm:text-lg text-[#2A1D15] flex items-center space-x-2">
              <span>🏪</span>
              <span>Click a Branch to View Staff & Salaries</span>
            </h3>
            <p class="text-xs text-[#7A6B60] mt-0.5">
              Click any branch card below to inspect its Branch Manager, staff members, and specific wage breakdown.
            </p>
          </div>
          <span class="text-xs font-semibold text-[#BA5420]">
            {{ selectedBranchFilter === 'all' ? 'Showing: Whole Business' : `Showing: ${currentSelectedBranchName}` }}
          </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 pt-1">
          <!-- Combined 'All Branches' Card -->
          <div
            @click="selectBranch('all')"
            class="rounded-xl p-4 space-y-2 border transition cursor-pointer"
            :class="selectedBranchFilter === 'all'
              ? 'bg-[#FFF7F0] border-[#BA5420] ring-2 ring-[#BA5420]/30 shadow-md'
              : 'bg-[#FAF7F2] border-[#EFE4D6] hover:bg-[#F5EFE8]'"
          >
            <div class="flex items-center justify-between">
              <span class="font-bold text-sm text-[#2A1D15]">All Branches</span>
              <span class="text-[10px] text-[#BA5420] font-bold bg-[#FAF0E6] px-1.5 py-0.5 rounded border border-[#F0D5C1]">
                Combined
              </span>
            </div>
            <div>
              <div class="text-lg font-serif font-bold text-[#BA5420]">
                ETB {{ Number(summary.total_wage_expenses || 0).toLocaleString() }}
              </div>
              <div class="text-[11px] text-[#7A6B60] mt-0.5 flex justify-between">
                <span>Staff: {{ summary.total_employees || 0 }}</span>
                <span>Avg: ETB {{ Number(summary.average_wage || 0).toLocaleString() }}</span>
              </div>
            </div>
          </div>

          <!-- Individual Clickable Branch Cards -->
          <div
            v-for="b in branchBreakdown"
            :key="b.branch_id"
            @click="selectBranch(b.branch_id)"
            class="rounded-xl p-4 space-y-2 border transition cursor-pointer"
            :class="selectedBranchFilter === b.branch_id
              ? 'bg-[#FFF7F0] border-[#BA5420] ring-2 ring-[#BA5420]/30 shadow-md'
              : 'bg-[#FAF7F2] border-[#EFE4D6] hover:bg-[#F5EFE8]'"
          >
            <div class="flex items-center justify-between">
              <span class="font-bold text-sm text-[#2A1D15] truncate" :title="b.branch_name">{{ b.branch_name }}</span>
              <span class="font-mono text-[10px] text-[#8C7D73] font-semibold bg-white px-1.5 py-0.5 rounded border border-[#E8DACB]">
                {{ b.branch_code }}
              </span>
            </div>

            <div class="text-[11px] text-[#55463C] truncate">
              <span class="text-[#8A7B71]">Manager:</span> <strong class="text-[#2A1D15]">{{ b.manager_name }}</strong>
            </div>

            <div class="pt-0.5 border-t border-[#EFE4D6]/60">
              <div class="text-base font-serif font-bold text-[#BA5420]">
                ETB {{ Number(b.total_wage || 0).toLocaleString() }}
              </div>
              <div class="text-[11px] text-[#7A6B60] mt-0.5 flex justify-between">
                <span>{{ b.staff_count }} staff</span>
                <span>Avg: {{ Number(b.avg_wage || 0).toLocaleString() }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Employees Salary Table with Clickable Rows & Search Filter -->
      <div class="bg-white border border-[#E9E0D4] rounded-2xl shadow-sm overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-[#EFE8DF] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div>
            <div class="flex items-center space-x-2">
              <h3 class="font-serif font-bold text-base sm:text-lg text-[#2A1D15]">
                {{ selectedBranchFilter === 'all' ? 'All Employees & Assigned Salaries' : `Employees assigned to ${currentSelectedBranchName}` }}
              </h3>
              <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-[#FAF0E6] text-[#BA5420] border border-[#F0D5C1]">
                {{ filteredEmployees.length }} employees
              </span>
            </div>
            <p class="text-xs text-[#7A6B60] mt-0.5">
              Click any employee row to inspect complete profile. Salary editing is restricted to Super Admin.
            </p>
          </div>

          <div class="flex flex-wrap items-center gap-3">
            <!-- Search bar -->
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search by name, amharic, phone..."
              class="bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-1.5 text-xs text-[#2D231D] focus:ring-1 focus:ring-[#BA5420] min-w-[200px]"
            />

            <!-- Reset to All Branches button -->
            <button
              v-if="selectedBranchFilter !== 'all'"
              @click="selectBranch('all')"
              class="px-3 py-1.5 text-xs font-semibold bg-white border border-[#D8CDC0] hover:bg-[#F4ECE3] text-[#3E3027] rounded-lg shadow-2xs transition cursor-pointer"
            >
              ✕ Clear Filter
            </button>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs min-w-[780px]">
            <thead class="bg-[#FAF6F0] text-[#88786B] uppercase font-semibold border-b border-[#EFE8DF]">
              <tr>
                <th class="py-3.5 px-5">Employee</th>
                <th class="py-3.5 px-4">Amharic (ተያዥ)</th>
                <th class="py-3.5 px-4">Role</th>
                <th class="py-3.5 px-4">Phone Number</th>
                <th class="py-3.5 px-4">Branch</th>
                <th class="py-3.5 px-4 text-right">Monthly Salary</th>
                <th class="py-3.5 px-4 text-center">Status</th>
                <th class="py-3.5 px-5 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#F3EDE4]">
              <tr
                v-for="emp in filteredEmployees"
                :key="emp.user_id"
                @click="openProfileModal(emp)"
                class="hover:bg-[#FCFAF7] transition cursor-pointer group"
              >
                <td class="py-3 px-5">
                  <div class="font-bold text-xs text-[#2A1D15] group-hover:text-[#BA5420] transition">{{ emp.full_name }}</div>
                  <div class="text-[11px] text-[#7A6B60]">{{ emp.email }}</div>
                </td>
                <td class="py-3 px-4 text-xs font-medium text-[#4A3B32]">
                  {{ emp.full_name_am || '—' }}
                </td>
                <td class="py-3 px-4">
                  <span
                    class="px-2 py-0.5 rounded-full text-[10px] font-semibold"
                    :class="{
                      'bg-[#FFF3E0] text-[#E65100]': emp.role === 'Super Admin',
                      'bg-[#EBF3FB] text-[#1565C0]': emp.role === 'Main Branch Manager',
                      'bg-[#FAF0E6] text-[#BA5420]': emp.role === 'Branch Manager' || emp.role === 'Branch Admin',
                      'bg-[#F3EFE9] text-[#5A4B41]': !['Super Admin', 'Main Branch Manager', 'Branch Manager', 'Branch Admin'].includes(emp.role)
                    }"
                  >
                    {{ emp.role === 'Branch Admin' ? 'Branch Manager' : emp.role }}
                  </span>
                </td>
                <td class="py-3 px-4 text-xs font-mono text-[#55463C]">
                  {{ emp.phone || '—' }}
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
                    {{ emp.employment_status || emp.status }}
                  </span>
                </td>
                <td class="py-3 px-5 text-right space-x-1" @click.stop>
                  <button
                    @click="openProfileModal(emp)"
                    class="inline-flex items-center px-2 py-1 text-[11px] font-medium bg-white border border-[#D8CDC0] hover:bg-[#F4ECE3] text-[#4A3B32] rounded-md shadow-2xs transition"
                    title="View complete information"
                  >
                    View
                  </button>
                  <button
                    @click="openEditSalaryModal(emp)"
                    class="inline-flex items-center space-x-1 px-2.5 py-1 text-[11px] font-semibold bg-[#FAF0E6] border border-[#F0D5C1] hover:bg-[#BA5420] hover:text-white text-[#BA5420] rounded-md shadow-2xs transition cursor-pointer"
                    title="Edit salary as Super Admin"
                  >
                    <span>Edit Wage</span>
                  </button>
                </td>
              </tr>
              <tr v-if="filteredEmployees.length === 0">
                <td colspan="8" class="py-8 text-center text-xs text-[#8A7B71]">
                  No employees found for the selected branch filter.
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

    <!-- ============================================== -->
    <!-- MODAL: EMPLOYEE COMPLETE PROFILE INFORMATION  -->
    <!-- ============================================== -->
    <div
      v-if="profileModalEmployee"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4"
      @click.self="profileModalEmployee = null"
    >
      <div class="bg-white rounded-2xl shadow-2xl border border-[#E9E0D4] max-w-lg w-full p-6 space-y-5 animate-in fade-in">
        <div class="flex items-start justify-between border-b border-[#EFE8DF] pb-4">
          <div class="flex items-center space-x-3.5">
            <div class="w-12 h-12 rounded-full bg-[#FAF0E6] text-[#BA5420] font-bold text-lg flex items-center justify-center border border-[#F0DFD0]">
              {{ profileModalEmployee.full_name?.charAt(0) || 'E' }}
            </div>
            <div>
              <h3 class="font-serif font-bold text-lg text-[#2A1D15]">{{ profileModalEmployee.full_name }}</h3>
              <p v-if="profileModalEmployee.full_name_am" class="text-xs text-[#BA5420] font-medium">
                ተያዥ: {{ profileModalEmployee.full_name_am }}
              </p>
            </div>
          </div>
          <button @click="profileModalEmployee = null" class="text-[#8A7B71] hover:text-[#2A1D15] text-xl font-bold">&times;</button>
        </div>

        <div class="space-y-4 text-xs">
          <!-- Two-column employee details -->
          <div class="grid grid-cols-2 gap-3 bg-[#FAF7F2] p-4 rounded-xl border border-[#EFE4D6]">
            <div>
              <span class="text-[#8A7B71] block mb-0.5">Assigned Role</span>
              <strong class="text-[#2A1D15]">{{ profileModalEmployee.role }}</strong>
            </div>
            <div>
              <span class="text-[#8A7B71] block mb-0.5">Assigned Branch</span>
              <strong class="text-[#2A1D15]">{{ profileModalEmployee.branch_name }}</strong>
            </div>
            <div>
              <span class="text-[#8A7B71] block mb-0.5">Phone Number</span>
              <span class="font-mono text-[#2A1D15] font-semibold">{{ profileModalEmployee.phone || '—' }}</span>
            </div>
            <div>
              <span class="text-[#8A7B71] block mb-0.5">Email Address</span>
              <span class="text-[#2A1D15] truncate block">{{ profileModalEmployee.email }}</span>
            </div>
            <div>
              <span class="text-[#8A7B71] block mb-0.5">Employment Status</span>
              <span class="inline-block px-2 py-0.5 text-[10px] font-bold rounded-full bg-[#EAF5EA] text-[#2E7D32] border border-[#C8E6C9]">
                {{ profileModalEmployee.employment_status || profileModalEmployee.status || 'Active' }}
              </span>
            </div>
            <div>
              <span class="text-[#8A7B71] block mb-0.5">Hire Date</span>
              <span class="text-[#2A1D15]">{{ profileModalEmployee.hire_date || '—' }}</span>
            </div>
          </div>

          <!-- Highlighted Current Salary -->
          <div class="p-4 bg-[#FFF7F0] border border-[#F0D5C1] rounded-xl flex items-center justify-between">
            <div>
              <span class="text-[11px] text-[#7A6B60] uppercase tracking-wider font-semibold block">Monthly Wage</span>
              <div class="text-2xl font-serif font-bold text-[#BA5420] mt-0.5">
                ETB {{ Number(profileModalEmployee.salary || 0).toLocaleString() }}
              </div>
            </div>
            <button
              @click="openEditSalaryFromProfile"
              class="px-4 py-2 text-xs font-semibold bg-[#BA5420] hover:bg-[#9E4314] text-white rounded-lg shadow-sm transition cursor-pointer"
            >
              Edit Salary
            </button>
          </div>
        </div>

        <div class="flex justify-end pt-2 border-t border-[#EFE8DF]">
          <button
            @click="profileModalEmployee = null"
            class="px-4 py-2 text-xs font-semibold bg-[#2A1D15] text-white rounded-lg hover:bg-[#3D2C20] transition cursor-pointer"
          >
            Close
          </button>
        </div>
      </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL: SUPER ADMIN EDIT EMPLOYEE SALARY       -->
    <!-- ============================================== -->
    <div v-if="isEditSalaryModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
      <div class="bg-white rounded-2xl shadow-2xl border border-[#E9E0D4] max-w-md w-full p-6 space-y-4 animate-in fade-in">
        <div class="flex items-center justify-between border-b border-[#EFE8DF] pb-3">
          <div>
            <h3 class="font-serif font-bold text-base text-[#2A1D15]">Update Employee Salary</h3>
            <p class="text-xs text-[#8A7B71]">
              {{ selectedEmployee?.full_name }} ({{ selectedEmployee?.role }}) · {{ selectedEmployee?.branch_name }}
            </p>
          </div>
          <button @click="isEditSalaryModalOpen = false" class="text-[#8A7B71] hover:text-[#2A1D15] text-xl font-bold">&times;</button>
        </div>

        <form @submit.prevent="submitUpdateSalary" class="space-y-4">
          <div class="grid grid-cols-2 gap-2 bg-[#FAF7F2] p-2.5 rounded-lg border border-[#E9E0D4] text-[11px]">
            <div><span class="text-[#8A7B71]">Amharic:</span> <strong>{{ selectedEmployee?.full_name_am || '—' }}</strong></div>
            <div><span class="text-[#8A7B71]">Phone:</span> <strong>{{ selectedEmployee?.phone || '—' }}</strong></div>
          </div>

          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">New Monthly Wage (ETB) *</label>
            <input
              v-model.number="editSalaryForm.salary"
              type="number"
              min="0"
              step="50"
              required
              class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-sm font-bold text-[#BA5420] focus:ring-1 focus:ring-[#BA5420]"
            />
          </div>

          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Reason for Adjustment</label>
            <input
              v-model="editSalaryForm.reason"
              type="text"
              placeholder="e.g. Annual merit review, role promotion, branch wage alignment"
              class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-1 focus:ring-[#BA5420]"
            />
          </div>

          <div class="p-3 bg-[#FCFAF7] border border-[#EFE8DF] rounded-lg text-[11px] text-[#8C7D73] leading-relaxed">
            This salary update will be recorded in the official audit log with your Super Admin signature.
          </div>

          <div class="flex justify-end space-x-3 pt-2">
            <button
              type="button"
              @click="isEditSalaryModalOpen = false"
              class="px-4 py-2 text-xs font-medium text-[#6A5A50] hover:bg-[#F3EFE9] rounded-lg transition cursor-pointer"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="isUpdatingSalary"
              class="px-5 py-2 text-xs font-semibold bg-[#BA5420] hover:bg-[#9E4314] text-white rounded-lg shadow-sm transition disabled:opacity-50 cursor-pointer"
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
import { useDialogStore } from '../../stores/dialog';
import { useToastStore } from '../../stores/toast';
import api from '../../api';

const dialog = useDialogStore();
const toast = useToastStore();

const activeTab = ref('payroll');
const summary = ref({});
const branchBreakdown = ref([]);
const employees = ref([]);
const requests = ref([]);
const selectedBranchFilter = ref('all');
const searchQuery = ref('');
const successMsg = ref('');

// Modals
const profileModalEmployee = ref(null);
const isEditSalaryModalOpen = ref(false);
const selectedEmployee = ref(null);
const isUpdatingSalary = ref(false);
const editSalaryForm = ref({
  salary: 0,
  reason: '',
});

const currentSelectedBranchName = computed(() => {
  if (selectedBranchFilter.value === 'all') return 'Whole Business';
  const found = branchBreakdown.value.find(b => b.branch_id === selectedBranchFilter.value);
  return found ? found.branch_name : 'Selected Branch';
});

const filteredEmployees = computed(() => {
  let list = employees.value;

  if (searchQuery.value && searchQuery.value.trim() !== '') {
    const q = searchQuery.value.toLowerCase().trim();
    list = list.filter(emp =>
      (emp.full_name && emp.full_name.toLowerCase().includes(q)) ||
      (emp.full_name_am && emp.full_name_am.includes(q)) ||
      (emp.email && emp.email.toLowerCase().includes(q)) ||
      (emp.phone && emp.phone.includes(q)) ||
      (emp.role && emp.role.toLowerCase().includes(q))
    );
  }

  return list;
});

const pendingRequestsCount = computed(() => {
  return requests.value.filter(r => r.status === 'Pending').length;
});

const selectBranch = (branchId) => {
  selectedBranchFilter.value = branchId;
  loadSalaryData();
};

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

const openProfileModal = (emp) => {
  profileModalEmployee.value = emp;
};

const openEditSalaryFromProfile = () => {
  const emp = profileModalEmployee.value;
  profileModalEmployee.value = null;
  openEditSalaryModal(emp);
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
    const msg = `Salary for ${selectedEmployee.value.full_name} updated successfully to ETB ${Number(editSalaryForm.value.salary).toLocaleString()}.`;
    successMsg.value = msg;
    toast.success(msg);
    await loadSalaryData();
  } catch (err) {
    const msg = err.response?.data?.message || 'Failed to update salary.';
    toast.error(msg);
  } finally {
    isUpdatingSalary.value = false;
  }
};

const reviewRequest = async (requestItem, status) => {
  const notes = await dialog.prompt({
    title: `${status} Salary Correction Request`,
    message: `Enter remarks or justification for marking request as ${status.toLowerCase()}:`,
    defaultValue: '',
    placeholder: 'Optional administrative remarks...',
    confirmText: `Confirm ${status}`,
    variant: status === 'Approved' ? 'success' : 'warning',
  });
  if (notes === null) return; // User cancelled
  try {
    await api.patch(`/admin/salary-requests/${requestItem.request_id}/status`, {
      status,
      admin_notes: notes || '',
      approved_salary: requestItem.requested_salary,
    });
    const msg = `Request from ${requestItem.user?.full_name} marked as ${status}.`;
    successMsg.value = msg;
    toast.success(msg);
    await Promise.all([loadRequests(), loadSalaryData()]);
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to review request.');
  }
};

onMounted(() => {
  loadSalaryData();
  loadRequests();
});
</script>