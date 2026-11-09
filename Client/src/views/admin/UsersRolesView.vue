<template>
  <div class="space-y-6 max-w-7xl mx-auto font-poppins">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <h2 class="dash-title text-xl sm:text-2xl">
          {{ isBranchManager ? 'Branch Staff Management' : 'Users & Staff Management' }}
        </h2>
        <p class="dash-subtitle mt-0.5">
          {{ isBranchManager ? 'Manage sellers, servants, and delivery staff for your branch' : 'Manage branch managers, staff roles, quotas, and permissions' }}
        </p>
      </div>

      <div class="flex flex-wrap items-center gap-2 sm:gap-3 w-full sm:w-auto">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search by name, amharic or email"
          class="dash-input w-auto min-w-[160px] py-1.5 px-3"
        />
        <button
          @click="focusAddUser"
          class="dash-btn-primary text-xs sm:text-sm py-1.5 px-3.5"
        >
          <span>+ Add {{ isBranchManager ? 'Staff Member' : 'User' }}</span>
        </button>
      </div>
    </div>

    <!-- Alert / Messages -->
    <div v-if="successMsg" class="p-3 bg-[#EBF7EB] border border-[#C5E9C6] rounded-lg text-xs text-[#1E7422] flex items-center justify-between">
      <span>{{ successMsg }}</span>
      <button @click="successMsg = ''" class="text-[#1E7422] font-bold ml-2">&times;</button>
    </div>
    <div v-if="errorMsg" class="p-3 bg-[#FEECEC] border border-[#F5C2C2] rounded-lg text-xs text-[#B91C1C] flex items-center justify-between">
      <span>{{ errorMsg }}</span>
      <button @click="errorMsg = ''" class="text-[#B91C1C] font-bold ml-2">&times;</button>
    </div>

    <!-- Branch Staff Quota Warning / Badge Bar (Super Admin & Branch Manager) -->
    <div v-if="quotas.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
      <div
        v-for="q in quotas"
        :key="q.branch_id"
        class="p-3.5 bg-white border rounded-xl shadow-2xs flex items-center justify-between"
        :class="q.is_staff_quota_reached ? 'border-amber-300 bg-amber-50/40' : 'border-[#E9E0D4]'"
      >
        <div>
          <span class="font-bold text-xs text-[#2A1D15]">{{ q.branch_name }}</span>
          <div class="text-[11px] text-[#7A6B60] mt-0.5">
            Staff: <strong :class="q.is_staff_quota_reached ? 'text-amber-800' : 'text-[#BA5420]'">{{ q.staff_count }}</strong> / {{ q.max_staff }}
            · Managers: <strong>{{ q.manager_count }}</strong> / {{ q.max_managers }}
          </div>
        </div>
        <span
          class="text-[10px] font-bold px-2 py-0.5 rounded-full"
          :class="q.is_staff_quota_reached ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800'"
        >
          {{ q.is_staff_quota_reached ? 'Quota Full' : 'Available' }}
        </span>
      </div>
    </div>

    <!-- Tabs -->
    <div class="flex items-center space-x-6 border-b border-[#EAE0D4] pb-2 text-xs font-semibold">
      <button
        @click="switchTab('staff')"
        :class="activeTab === 'staff' ? 'text-[#BA5420] border-b-2 border-[#BA5420] pb-2 font-bold' : 'text-[#7A6B60]'"
      >
        Staff & Managers
      </button>
      <button
        v-if="isSuperAdmin"
        @click="switchTab('customers')"
        :class="activeTab === 'customers' ? 'text-[#BA5420] border-b-2 border-[#BA5420] pb-2 font-bold' : 'text-[#7A6B60]'"
      >
        Customers
      </button>
      <button
        @click="switchTab('permissions')"
        :class="activeTab === 'permissions' ? 'text-[#BA5420] border-b-2 border-[#BA5420] pb-2 font-bold' : 'text-[#7A6B60]'"
      >
        Role Permissions
      </button>
    </div>

    <!-- Role Filter Pills Bar -->
    <div v-if="activeTab !== 'permissions'" class="flex flex-wrap items-center justify-between gap-2 text-xs">
      <div class="flex flex-wrap items-center gap-2">
        <select
          v-model="selectedRoleFilter"
          @change="loadUsers"
          class="bg-white border border-[#DDD2C5] text-[#55463C] font-semibold rounded-lg px-2.5 py-1 text-xs"
        >
          <option value="all">All Roles</option>
          <option value="Super Admin">Super Admin</option>
          <option value="Main Branch Manager">Main Branch Manager</option>
          <option value="Branch Manager">Branch Manager</option>
          <option value="Branch Seller / Cashier">Branch Seller / Cashier</option>
          <option value="Servant">Servant</option>
          <option value="Delivery Staff">Delivery Staff</option>
          <option value="Branch Staff">Branch Staff</option>
        </select>

        <select
          v-if="isSuperAdmin"
          v-model="selectedBranchFilter"
          @change="loadUsers"
          class="bg-white border border-[#DDD2C5] text-[#55463C] font-semibold rounded-lg px-2.5 py-1 text-xs"
        >
          <option value="all">All Branches</option>
          <option v-for="b in branches" :key="b.branch_id" :value="b.branch_id">
            {{ b.branch_name }}
          </option>
        </select>
      </div>

      <span class="text-[#8A7B71] font-semibold">{{ filteredUsers.length }} users listed</span>
    </div>

    <!-- Users Table (For Staff or Customers) -->
    <div v-if="activeTab !== 'permissions'" class="bg-white border border-[#E9E0D4] rounded-xl shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full min-w-[700px] text-left text-sm">
          <thead class="bg-[#FAF6F0] text-[11px] font-bold text-[#827165] tracking-wider uppercase border-b border-[#EFE8DF]">
            <tr>
              <th class="py-3 px-5">Staff Member</th>
              <th class="py-3 px-4">Amharic (ተያዥ)</th>
              <th class="py-3 px-4">Role</th>
              <th class="py-3 px-4">Branch</th>
              <th v-if="isSuperAdmin" class="py-3 px-4 text-right">Salary (ETB)</th>
              <th class="py-3 px-4 text-center">Status</th>
              <th class="py-3 px-5 text-right">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#F3EDE4]">
            <tr v-for="u in filteredUsers" :key="u.user_id" class="hover:bg-[#FCFAF7] transition">
              <td class="py-3 px-5 flex items-center space-x-3">
                <span class="w-8 h-8 rounded-full bg-[#EAE2D8] text-[#55463C] font-bold text-xs flex items-center justify-center shrink-0">
                  {{ u.initials }}
                </span>
                <div>
                  <div class="font-bold text-xs text-[#2A1D15]">{{ u.full_name }}</div>
                  <div class="text-[11px] text-[#7A6B60]">{{ u.email }} <span v-if="u.phone">· {{ u.phone }}</span></div>
                </div>
              </td>
              <td class="py-3 px-4 text-xs font-medium text-[#4A3B32]">
                {{ u.full_name_am || '—' }}
              </td>
              <td class="py-3 px-4">
                <span
                  class="px-2.5 py-0.5 rounded-full text-xs font-semibold"
                  :class="{
                    'bg-[#FFF3E0] text-[#E65100]': u.role === 'Super Admin',
                    'bg-[#EBF3FB] text-[#1565C0]': u.role === 'Main Branch Manager',
                    'bg-[#FAF0E6] text-[#BA5420]': u.role === 'Branch Manager' || u.role === 'Branch Admin',
                    'bg-[#EFEAE2] text-[#5A4A3E]': !['Super Admin', 'Main Branch Manager', 'Branch Manager', 'Branch Admin'].includes(u.role)
                  }"
                >
                  {{ u.role }}
                </span>
              </td>
              <td class="py-3 px-4 text-xs text-[#4A3B32] font-medium">{{ u.branch_name }}</td>
              <td v-if="isSuperAdmin" class="py-3 px-4 text-right font-serif font-bold text-xs text-[#BA5420]">
                {{ u.salary !== null ? `ETB ${Number(u.salary).toLocaleString()}` : '—' }}
              </td>
              <td class="py-3 px-4 text-center">
                <span
                  class="inline-block px-2.5 py-0.5 text-xs font-semibold rounded-full border"
                  :class="u.status === 'Active' ? 'bg-[#EAF5EA] text-[#2E7D32] border-[#C8E6C9]' : 'bg-[#FFF8E6] text-[#A67503] border-[#FEE39E]'"
                >
                  {{ u.employment_status || u.status }}
                </span>
              </td>
              <td class="py-3 px-5 text-right">
                <button
                  @click="openEditUserModal(u)"
                  class="px-3 py-1 text-xs font-semibold bg-white border border-[#D8CDC0] hover:bg-[#F4ECE3] text-[#4A3B32] rounded-md shadow-2xs transition cursor-pointer"
                >
                  Edit
                </button>
              </td>
            </tr>
            <tr v-if="filteredUsers.length === 0">
              <td :colspan="isSuperAdmin ? 7 : 6" class="py-8 text-center text-xs text-[#8A7B71]">
                No users found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Bottom Two Columns: Add User Form + Permission Matrix -->
    <div id="add-user-section" class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
      <!-- Add User Form -->
      <div class="bg-white border border-[#E9E0D4] rounded-xl shadow-sm p-6 space-y-4">
        <h3 class="font-serif font-bold text-base sm:text-lg text-[#2A1D15]">
          Add new {{ isBranchManager ? 'branch staff member' : 'team member' }}
        </h3>

        <form @submit.prevent="submitAddUser" class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Full name (English) *</label>
              <input
                v-model="newUser.full_name"
                type="text"
                placeholder="e.g. Tigist Alemu"
                class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
                required
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Full name in Amharic (ተያዥ)</label>
              <input
                v-model="newUser.full_name_am"
                type="text"
                placeholder="e.g. ጥግስት ዓለሙ"
                class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Email *</label>
              <input
                v-model="newUser.email"
                type="email"
                placeholder="e.g. staff@sweetbakes.com"
                class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
                required
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Phone number</label>
              <input
                v-model="newUser.phone"
                type="text"
                placeholder="e.g. +251 91 100 0001"
                class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Staff Role *</label>
              <select
                v-model="newUser.role"
                class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
                required
              >
                <template v-if="isSuperAdmin">
                  <option value="Super Admin">Super Admin</option>
                  <option value="Main Branch Manager">Main Branch Manager</option>
                  <option value="Branch Manager">Branch Manager</option>
                  <option value="Branch Seller / Cashier">Branch Seller / Cashier</option>
                  <option value="Servant">Servant</option>
                  <option value="Delivery Staff">Delivery Staff</option>
                  <option value="Branch Staff">Branch Staff (General)</option>
                </template>
                <template v-else>
                  <option value="Branch Seller / Cashier">Branch Seller / Cashier</option>
                  <option value="Servant">Servant</option>
                  <option value="Delivery Staff">Delivery Staff</option>
                  <option value="Branch Staff">Branch Staff (General)</option>
                </template>
              </select>
            </div>

            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Assigned Branch</label>
              <select
                v-model="newUser.branch_id"
                class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
                :disabled="!isSuperAdmin"
              >
                <option :value="null">None (Super Admin)</option>
                <option v-for="b in branches" :key="b.branch_id" :value="b.branch_id">
                  {{ b.branch_name }}
                </option>
              </select>
            </div>
          </div>

          <!-- Salary input: Editable only by Super Admin -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Monthly Salary / Wage (ETB)</label>
              <input
                v-if="isSuperAdmin"
                v-model.number="newUser.salary"
                type="number"
                min="0"
                step="100"
                placeholder="e.g. 9000"
                class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] font-bold text-[#BA5420]"
              />
              <input
                v-else
                type="text"
                value="Controlled by Super Admin"
                readonly
                class="w-full bg-[#F7F2EA] border border-[#DCD0C2] rounded-lg px-3 py-2 text-xs text-[#8A7B71] italic"
              />
            </div>

            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Employment Status</label>
              <select
                v-model="newUser.employment_status"
                class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
              >
                <option value="Active">Active</option>
                <option value="On Leave">On Leave</option>
                <option value="Inactive">Inactive</option>
                <option value="Suspended">Suspended</option>
              </select>
            </div>
          </div>

          <div class="p-3 bg-[#FCFAF7] border border-[#EFE8DF] rounded-lg text-xs text-[#8A7B71] leading-relaxed">
            <span v-if="isBranchManager">
              Branch Managers can recruit and manage Sellers, Servants, and Delivery Staff for their assigned branch up to branch quotas. All salaries are controlled and approved centrally by the Super Admin.
            </span>
            <span v-else>
              Super Admin possesses complete authority to create managers and staff, adjust salaries, change assigned branches, and configure branch quotas.
            </span>
          </div>

          <div class="flex justify-end space-x-3 pt-2">
            <button
              type="submit"
              :disabled="isAddingUser"
              class="px-5 py-2 text-xs font-semibold bg-[#BA5420] hover:bg-[#9E4314] text-white rounded-lg shadow-sm transition disabled:opacity-50"
            >
              {{ isAddingUser ? 'Creating...' : 'Create Staff Account' }}
            </button>
          </div>
        </form>
      </div>

      <!-- What Each Role Can Do (Permission Matrix) -->
      <div class="bg-white border border-[#E9E0D4] rounded-xl shadow-sm p-6 space-y-4">
        <h3 class="font-serif font-bold text-base sm:text-lg text-[#2A1D15]">Role-Based Access Matrix</h3>

        <div class="overflow-x-auto border border-[#EFE8DF] rounded-xl">
          <table class="w-full text-left text-xs">
            <thead class="bg-[#FAF6F0] text-[#88786B] uppercase border-b border-[#EFE8DF] font-semibold">
              <tr>
                <th class="py-2.5 px-3">Responsibility</th>
                <th class="py-2.5 px-2 text-center">Super</th>
                <th class="py-2.5 px-2 text-center">Manager</th>
                <th class="py-2.5 px-2 text-center">Seller</th>
                <th class="py-2.5 px-2 text-center">Delivery/Servant</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#F3EDE4]">
              <tr v-for="perm in permissionMatrix" :key="perm.action" class="hover:bg-[#FCFAF7]">
                <td class="py-2.5 px-3 font-medium text-[#382C24]">{{ perm.action }}</td>
                <td class="py-2.5 px-2 text-center font-bold text-[#2E7D32]">{{ perm.super }}</td>
                <td class="py-2.5 px-2 text-center font-bold" :class="perm.manager === 'Yes' ? 'text-[#2E7D32]' : 'text-[#8A7B71]'">{{ perm.manager }}</td>
                <td class="py-2.5 px-2 text-center font-bold" :class="perm.seller === 'Yes' ? 'text-[#2E7D32]' : 'text-[#8A7B71]'">{{ perm.seller }}</td>
                <td class="py-2.5 px-2 text-center font-bold" :class="perm.staff === 'Yes' ? 'text-[#2E7D32]' : 'text-[#8A7B71]'">{{ perm.staff }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- MODAL: Edit User Modal -->
    <div v-if="isEditModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-3 sm:p-4">
      <div class="bg-white rounded-2xl shadow-2xl border border-[#E9E0D4] max-w-lg w-full p-4 sm:p-6 space-y-4 sm:space-y-5 animate-in fade-in duration-200 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-[#EFE8DF] pb-3">
          <div>
            <h3 class="text-lg font-serif font-bold text-[#2A1D15]">Edit Staff Member</h3>
            <p class="text-xs text-[#8A7B71]">{{ editingUser?.email }}</p>
          </div>
          <button @click="isEditModalOpen = false" class="text-[#8A7B71] hover:text-[#2A1D15] text-xl font-bold p-1">
            &times;
          </button>
        </div>

        <form @submit.prevent="submitEditUser" class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Full name (English) *</label>
              <input
                v-model="editUserForm.full_name"
                type="text"
                required
                class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Full name in Amharic (ተያዥ)</label>
              <input
                v-model="editUserForm.full_name_am"
                type="text"
                class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Role *</label>
              <select
                v-model="editUserForm.role"
                required
                class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
                :disabled="!isSuperAdmin && ['Super Admin', 'Main Branch Manager', 'Branch Manager', 'Branch Admin'].includes(editingUser?.role)"
              >
                <template v-if="isSuperAdmin">
                  <option value="Super Admin">Super Admin</option>
                  <option value="Main Branch Manager">Main Branch Manager</option>
                  <option value="Branch Manager">Branch Manager</option>
                  <option value="Branch Seller / Cashier">Branch Seller / Cashier</option>
                  <option value="Servant">Servant</option>
                  <option value="Delivery Staff">Delivery Staff</option>
                  <option value="Branch Staff">Branch Staff (General)</option>
                  <option value="Customer">Customer</option>
                </template>
                <template v-else>
                  <option value="Branch Seller / Cashier">Branch Seller / Cashier</option>
                  <option value="Servant">Servant</option>
                  <option value="Delivery Staff">Delivery Staff</option>
                  <option value="Branch Staff">Branch Staff (General)</option>
                </template>
              </select>
            </div>

            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Assigned branch</label>
              <select
                v-model="editUserForm.branch_id"
                class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
                :disabled="!isSuperAdmin"
              >
                <option :value="null">None (Super Admin/Customer)</option>
                <option v-for="b in branches" :key="b.branch_id" :value="b.branch_id">
                  {{ b.branch_name }}
                </option>
              </select>
            </div>
          </div>

          <!-- Salary input: Editable only by Super Admin -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Monthly Salary / Wage (ETB)</label>
              <input
                v-if="isSuperAdmin"
                v-model.number="editUserForm.salary"
                type="number"
                min="0"
                step="100"
                class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#BA5420] font-bold"
              />
              <input
                v-else
                type="text"
                value="Controlled by Super Admin"
                readonly
                class="w-full bg-[#F7F2EA] border border-[#DCD0C2] rounded-lg px-3 py-2 text-xs text-[#8A7B71] italic"
              />
            </div>

            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Employment Status</label>
              <select
                v-model="editUserForm.employment_status"
                class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
              >
                <option value="Active">Active</option>
                <option value="On Leave">On Leave</option>
                <option value="Inactive">Inactive</option>
                <option value="Suspended">Suspended</option>
                <option value="Terminated">Terminated</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Phone</label>
              <input
                v-model="editUserForm.phone"
                type="text"
                placeholder="+251 91 100 0001"
                class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Account Active Status</label>
              <select
                v-model="editUserForm.status"
                class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
              >
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
              </select>
            </div>
          </div>

          <div class="flex justify-end space-x-3 pt-3 border-t border-[#EFE8DF]">
            <button
              type="button"
              @click="isEditModalOpen = false"
              class="px-4 py-2 text-xs font-medium text-[#6A5A50] hover:bg-[#F3EFE9] rounded-lg transition"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="isSavingEdit"
              class="px-5 py-2 text-xs font-semibold bg-[#BA5420] hover:bg-[#9E4314] text-white rounded-lg shadow-sm transition disabled:opacity-50"
            >
              {{ isSavingEdit ? 'Saving...' : 'Update Staff Record' }}
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
const activeTab = ref('staff');
const searchQuery = ref('');
const users = ref([]);
const branches = ref([]);
const quotas = ref([]);
const selectedRoleFilter = ref('all');
const selectedBranchFilter = ref('all');
const successMsg = ref('');
const errorMsg = ref('');
const isAddingUser = ref(false);

const isSuperAdmin = computed(() => authStore.user?.role === 'Super Admin');
const isBranchManager = computed(() => ['Branch Manager', 'Branch Admin'].includes(authStore.user?.role));

// Edit User Modal
const isEditModalOpen = ref(false);
const editingUser = ref(null);
const isSavingEdit = ref(false);
const editUserForm = ref({
  full_name: '',
  full_name_am: '',
  role: 'Branch Staff',
  branch_id: null,
  phone: '',
  salary: 0,
  employment_status: 'Active',
  status: 'Active',
});

const newUser = ref({
  full_name: '',
  full_name_am: '',
  email: '',
  phone: '',
  role: 'Branch Seller / Cashier',
  branch_id: authStore.user?.branch_id || 2,
  salary: 8500,
  employment_status: 'Active',
});

const permissionMatrix = [
  { action: 'Manage branches & quotas', super: 'Yes', manager: 'No', seller: 'No', staff: 'No' },
  { action: 'Edit employee salaries', super: 'Yes', manager: 'No', seller: 'No', staff: 'No' },
  { action: 'Manage branch staff', super: 'Yes', manager: 'Yes (Own)', seller: 'No', staff: 'No' },
  { action: 'Transfer stock between branches', super: 'Yes', manager: 'Main only', seller: 'No', staff: 'No' },
  { action: 'Manage branch orders', super: 'Yes', manager: 'Yes (Own)', seller: 'Yes (Own)', staff: 'Prep only' },
  { action: 'Submit reports', super: 'Yes', manager: 'To Super Admin', seller: 'To Manager', staff: 'To Manager' },
];

const loadUsers = async () => {
  try {
    const params = {
      tab: activeTab.value,
      role: selectedRoleFilter.value !== 'all' ? selectedRoleFilter.value : undefined,
      branch_id: selectedBranchFilter.value !== 'all' ? selectedBranchFilter.value : undefined,
    };
    const [uRes, bRes, qRes] = await Promise.all([
      api.get('/admin/users-list', { params }),
      api.get('/branches'),
      api.get('/admin/branches-quotas').catch(() => ({ data: [] })),
    ]);
    users.value = uRes.data;
    branches.value = bRes.data;
    quotas.value = qRes.data || [];
  } catch (err) {
    console.error('Failed to load users', err);
  }
};

const switchTab = (tab) => {
  activeTab.value = tab;
  loadUsers();
};

const filteredUsers = computed(() => {
  if (!searchQuery.value) return users.value;
  const q = searchQuery.value.toLowerCase();
  return users.value.filter(u =>
    (u.full_name && u.full_name.toLowerCase().includes(q)) ||
    (u.full_name_am && u.full_name_am.includes(q)) ||
    (u.email && u.email.toLowerCase().includes(q))
  );
});

const focusAddUser = () => {
  const el = document.getElementById('add-user-section');
  if (el) {
    el.scrollIntoView({ behavior: 'smooth' });
  }
};

const openEditUserModal = (u) => {
  editingUser.value = u;
  editUserForm.value = {
    full_name: u.full_name,
    full_name_am: u.full_name_am || '',
    role: u.role,
    branch_id: u.branch_id,
    phone: u.phone || '',
    salary: u.salary || 0,
    employment_status: u.employment_status || 'Active',
    status: u.status,
  };
  isEditModalOpen.value = true;
};

const submitAddUser = async () => {
  isAddingUser.value = true;
  errorMsg.value = '';
  successMsg.value = '';
  try {
    const payload = { ...newUser.value };
    if (isBranchManager.value) {
      payload.branch_id = authStore.user?.branch_id;
      delete payload.salary;
    }
    await api.post('/admin/users', payload);
    successMsg.value = `Staff account for ${newUser.value.full_name} created successfully.`;
    newUser.value = {
      full_name: '',
      full_name_am: '',
      email: '',
      phone: '',
      role: 'Branch Seller / Cashier',
      branch_id: authStore.user?.branch_id || 2,
      salary: 8500,
      employment_status: 'Active',
    };
    await loadUsers();
  } catch (err) {
    errorMsg.value = err.response?.data?.message || 'Failed to create user account.';
  } finally {
    isAddingUser.value = false;
  }
};

const submitEditUser = async () => {
  if (!editingUser.value) return;
  isSavingEdit.value = true;
  errorMsg.value = '';
  successMsg.value = '';
  try {
    const payload = { ...editUserForm.value };
    if (!isSuperAdmin.value) {
      delete payload.salary;
      delete payload.branch_id;
    }
    await api.patch(`/admin/users/${editingUser.value.user_id}`, payload);
    isEditModalOpen.value = false;
    successMsg.value = `User record for ${editUserForm.value.full_name} updated successfully.`;
    await loadUsers();
  } catch (err) {
    errorMsg.value = err.response?.data?.message || 'Failed to update user record.';
  } finally {
    isSavingEdit.value = false;
  }
};

onMounted(() => {
  loadUsers();
});
</script>
