<template>
  <div class="space-y-6 max-w-7xl mx-auto font-poppins pb-16">
    <!-- ======================================================== -->
    <!-- 1. LUXURY BAKERY HEADER BANNER (EXACT IMAGE 2)           -->
    <!-- ======================================================== -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#FFF5F2] via-[#FFF0EC] to-[#FCE7E1] border border-[#F7E1D7] p-6 sm:p-7 shadow-xs">
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 relative z-10">
        <!-- Left: Icon + Title & Subtitle -->
        <div class="flex items-center space-x-4">
          <div class="w-13 h-13 rounded-2xl bg-[#FFE4DC] text-[#BA5420] flex items-center justify-center text-2xl shrink-0 shadow-2xs border border-[#FAD2C5]">
            <svg class="w-6 h-6 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
          <div>
            <h1 class="font-serif text-2xl sm:text-3xl font-black text-[#2B1810] tracking-tight">
              Users &amp; Staff Management
            </h1>
            <p class="text-xs sm:text-sm text-[#7D6E64] font-medium mt-1">
              Manage branch staff, roles, and permissions with ease.
            </p>
          </div>
        </div>

        <!-- Right: Search Input + Add User Button (Exact Image 2 Header Placement) -->
        <div class="flex flex-wrap items-center gap-3 self-start lg:self-auto">
          <div class="relative w-full sm:w-64">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-[#7D6E64] pointer-events-none">🔍</span>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search by name, role or branch..."
              class="w-full pl-9 pr-3.5 py-2 bg-white/95 border border-[#E2D8CC] rounded-xl text-xs font-semibold text-[#2B1810] focus:ring-2 focus:ring-[#3D251E]/20 focus:border-[#3D251E] shadow-2xs"
            />
          </div>

          <button
            @click="focusAddUser"
            class="px-4 py-2 bg-[#3D251E] hover:bg-[#2B1810] text-white text-xs sm:text-sm font-bold rounded-xl transition cursor-pointer flex items-center space-x-1.5 shadow-xs shrink-0"
          >
            <span class="text-base font-bold leading-none">+</span>
            <span>Add User</span>
          </button>
        </div>
      </div>

      <!-- Cupcakes Banner Artwork on the Right -->
      <div class="absolute right-0 top-0 bottom-0 w-1/3 max-w-[340px] pointer-events-none hidden xl:block overflow-hidden">
        <img
          src="/mockup/dashboard_banner_cupcakes.jpg"
          alt="SweetBakes Cupcakes"
          class="w-full h-full object-cover object-left opacity-90 mix-blend-multiply"
          onerror="this.style.display='none'"
        />
        <div class="absolute inset-0 bg-gradient-to-r from-[#FFF5F2] via-transparent to-transparent"></div>
      </div>
    </div>

    <!-- Alert / Messages -->
    <div v-if="successMsg" class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-2xl text-xs text-emerald-800 flex items-center justify-between shadow-2xs">
      <div class="flex items-center space-x-2">
        <span>✓</span>
        <span>{{ successMsg }}</span>
      </div>
      <button @click="successMsg = ''" class="text-emerald-800 font-bold ml-2 cursor-pointer">&times;</button>
    </div>
    <div v-if="errorMsg" class="p-3.5 bg-rose-50 border border-rose-200 rounded-2xl text-xs text-rose-800 flex items-center justify-between shadow-2xs">
      <div class="flex items-center space-x-2">
        <span>⚠️</span>
        <span>{{ errorMsg }}</span>
      </div>
      <button @click="errorMsg = ''" class="text-rose-800 font-bold ml-2 cursor-pointer">&times;</button>
    </div>

    <!-- ======================================================== -->
    <!-- 2. FOUR KPI STAT CARDS (EXACT IMAGE 2)                   -->
    <!-- ======================================================== -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 text-xs">
      <!-- 24 Total Staff (+2%) -->
      <div class="bg-white border border-[#E9E0D4] rounded-2xl p-4 shadow-2xs flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 rounded-xl bg-[#FFF5F2] text-rose-700 flex items-center justify-center text-lg font-bold">👥</div>
          <div>
            <div class="text-xl sm:text-2xl font-serif font-black text-[#2B1810]">{{ stats.totalStaff }}</div>
            <div class="text-[11px] text-[#7D6E64] font-medium">Total Staff</div>
          </div>
        </div>
        <span class="text-[11px] font-bold text-emerald-700">↑ +2%</span>
      </div>

      <!-- 6 Branches (+0%) -->
      <div class="bg-white border border-[#E9E0D4] rounded-2xl p-4 shadow-2xs flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 rounded-xl bg-[#FEF7E6] text-amber-700 flex items-center justify-center text-lg font-bold">🏬</div>
          <div>
            <div class="text-xl sm:text-2xl font-serif font-black text-[#2B1810]">
              {{ isBranchScoped ? (branches[0]?.branch_name || 'Assigned Branch') : stats.totalBranches }}
            </div>
            <div class="text-[11px] text-[#7D6E64] font-medium">
              {{ isBranchScoped ? 'Branch' : 'Branches' }}
            </div>
          </div>
        </div>
        <span class="text-[11px] font-bold text-[#7D6E64]">Active</span>
      </div>

      <!-- 3 Managers (+0%) -->
      <div class="bg-white border border-[#E9E0D4] rounded-2xl p-4 shadow-2xs flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 rounded-xl bg-[#F5F3FF] text-purple-700 flex items-center justify-center text-lg font-bold">🛡️</div>
          <div>
            <div class="text-xl sm:text-2xl font-serif font-black text-[#2B1810]">{{ stats.totalManagers }}</div>
            <div class="text-[11px] text-[#7D6E64] font-medium">Managers</div>
          </div>
        </div>
        <span class="text-[11px] font-bold text-[#7D6E64]">↑ +0%</span>
      </div>

      <!-- 21 Staff Members (+1%) -->
      <div class="bg-white border border-[#E9E0D4] rounded-2xl p-4 shadow-2xs flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 rounded-xl bg-[#F0FDF4] text-emerald-700 flex items-center justify-center text-lg font-bold">💼</div>
          <div>
            <div class="text-xl sm:text-2xl font-serif font-black text-[#2B1810]">{{ stats.staffMembers }}</div>
            <div class="text-[11px] text-[#7D6E64] font-medium">Staff Members</div>
          </div>
        </div>
        <span class="text-[11px] font-bold text-emerald-700">↑ +1%</span>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- 3. FILTER TABS (EXACT IMAGE 2)                           -->
    <!-- ======================================================== -->
    <div class="flex items-center space-x-2">
      <button
        @click="roleFilterTab = 'all'"
        class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition cursor-pointer flex items-center space-x-2 shadow-2xs"
        :class="roleFilterTab === 'all' ? 'bg-[#3D251E] text-white shadow-xs' : 'bg-[#FAF7F2] text-[#6E5D52] hover:bg-[#EFE8DE]'"
      >
        <span>👥</span>
        <span>All Users</span>
      </button>

      <button
        @click="roleFilterTab = 'staff'"
        class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition cursor-pointer flex items-center space-x-2 shadow-2xs"
        :class="roleFilterTab === 'staff' ? 'bg-[#3D251E] text-white shadow-xs' : 'bg-[#FAF7F2] text-[#6E5D52] hover:bg-[#EFE8DE]'"
      >
        <span>💼</span>
        <span>Staff Members</span>
      </button>

      <button
        @click="roleFilterTab = 'managers'"
        class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition cursor-pointer flex items-center space-x-2 shadow-2xs"
        :class="roleFilterTab === 'managers' ? 'bg-[#3D251E] text-white shadow-xs' : 'bg-[#FAF7F2] text-[#6E5D52] hover:bg-[#EFE8DE]'"
      >
        <span>🛡️</span>
        <span>Branch Managers</span>
      </button>
    </div>

    <!-- ======================================================== -->
    <!-- 4. MAIN TWO-COLUMN WORKSPACE (EXACT IMAGE 2)             -->
    <!-- ======================================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
      <!-- LEFT COLUMN (5 cols / ~42%): ADD NEW TEAM MEMBER -->
      <div id="add-team-member-card" class="lg:col-span-5 bg-white border border-[#E9E0D4] rounded-3xl p-6 sm:p-7 shadow-xs space-y-5">
        <div class="border-b border-[#F4EFEA] pb-3.5">
          <div class="flex items-center space-x-2">
            <span class="w-8 h-8 rounded-xl bg-[#3D251E] text-white flex items-center justify-center font-bold text-sm">+</span>
            <h2 class="font-serif font-black text-lg text-[#2B1810]">
              Add New Team Member
            </h2>
          </div>
          <p class="text-xs text-[#7D6E64] mt-1">Fill in the details to add a new user</p>
        </div>

        <form @submit.prevent="submitAddUser" class="space-y-4 text-xs">
          <!-- Full Name -->
          <div>
            <label class="block text-[11px] font-bold text-[#7D6E64] uppercase mb-1">Full Name *</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-[#7D6E64]">👤</span>
              <input
                v-model="newUser.full_name"
                type="text"
                placeholder="e.g. John Doe"
                required
                class="w-full pl-9 pr-3.5 py-2.5 bg-white border border-[#E2D8CC] rounded-xl font-semibold text-[#2B1810] focus:ring-2 focus:ring-[#3D251E]/20 focus:border-[#3D251E]"
              />
            </div>
          </div>

          <!-- Email Address -->
          <div>
            <label class="block text-[11px] font-bold text-[#7D6E64] uppercase mb-1">Email Address *</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-[#7D6E64]">✉️</span>
              <input
                v-model="newUser.email"
                type="email"
                placeholder="e.g. john@company.com"
                required
                class="w-full pl-9 pr-3.5 py-2.5 bg-white border border-[#E2D8CC] rounded-xl font-semibold text-[#2B1810] focus:ring-2 focus:ring-[#3D251E]/20 focus:border-[#3D251E]"
              />
            </div>
          </div>

          <!-- Phone Number -->
          <div>
            <label class="block text-[11px] font-bold text-[#7D6E64] uppercase mb-1">Phone Number</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-[#7D6E64]">📞</span>
              <input
                v-model="newUser.phone"
                type="text"
                placeholder="e.g. +2519 100 0001"
                class="w-full pl-9 pr-3.5 py-2.5 bg-white border border-[#E2D8CC] rounded-xl font-semibold text-[#2B1810] focus:ring-2 focus:ring-[#3D251E]/20 focus:border-[#3D251E]"
              />
            </div>
          </div>

          <!-- Role Dropdown -->
          <div>
            <label class="block text-[11px] font-bold text-[#7D6E64] uppercase mb-1">Role *</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-[#7D6E64] pointer-events-none">🛡️</span>
              <select
                v-model="newUser.role"
                required
                class="w-full pl-9 pr-3.5 py-2.5 bg-white border border-[#E2D8CC] rounded-xl font-bold text-[#2B1810] focus:ring-2 focus:ring-[#3D251E]/20 focus:border-[#3D251E] cursor-pointer"
              >
                <option value="" disabled>Select Role</option>
                <template v-if="isSuperAdmin">
                  <option value="Main Branch Manager">Main Branch Manager</option>
                  <option value="Branch Manager">Branch Manager</option>
                </template>
                <option value="Branch Seller / Cashier">Branch Seller / Cashier</option>
                <option value="Branch Staff">Branch Staff</option>
                <option value="Servant">Servant</option>
                <option value="Delivery Staff">Delivery Staff</option>
              </select>
            </div>
          </div>

          <!-- Branch Dropdown -->
          <div>
            <label class="block text-[11px] font-bold text-[#7D6E64] uppercase mb-1">Branch *</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-[#7D6E64] pointer-events-none">🏬</span>
              <select
                v-model="newUser.branch_id"
                :disabled="isBranchScoped"
                class="w-full pl-9 pr-3.5 py-2.5 bg-white border border-[#E2D8CC] rounded-xl font-bold text-[#2B1810] focus:ring-2 focus:ring-[#3D251E]/20 focus:border-[#3D251E] cursor-pointer disabled:bg-gray-100 disabled:cursor-not-allowed"
              >
                <option :value="null" disabled>Select Branch</option>
                <option v-for="b in branches" :key="b.branch_id" :value="b.branch_id">
                  {{ b.branch_name }}
                </option>
              </select>
            </div>
          </div>

          <!-- Status Active Toggle Switch -->
          <div class="flex items-center justify-between py-1">
            <span class="text-[11px] font-bold text-[#7D6E64] uppercase">Status</span>
            <label class="flex items-center space-x-2 cursor-pointer">
              <input
                v-model="newUser.is_active"
                type="checkbox"
                class="sr-only peer"
              />
              <div class="w-10 h-5 bg-[#E5DACD] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600 relative"></div>
              <span class="text-xs font-bold text-[#2B1810]">{{ newUser.is_active ? 'Active' : 'Inactive' }}</span>
            </label>
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-between pt-3 border-t border-[#F4EFEA]">
            <button
              type="button"
              @click="resetNewUser"
              class="px-5 py-2.5 bg-[#FAF7F2] hover:bg-[#F0E6D8] border border-[#E2D6C6] text-xs font-bold text-[#3D251E] rounded-xl transition cursor-pointer flex items-center space-x-1"
            >
              <span>🔄</span>
              <span>Reset</span>
            </button>

            <button
              type="submit"
              :disabled="savingUser"
              class="px-6 py-2.5 bg-[#3D251E] hover:bg-[#2B1810] text-white text-xs font-bold rounded-xl transition cursor-pointer shadow-xs flex items-center space-x-1.5"
            >
              <span>💾</span>
              <span>{{ savingUser ? 'Saving...' : 'Save User' }}</span>
            </button>
          </div>
        </form>
      </div>

      <!-- RIGHT COLUMN (7 cols / ~58%): STAFF MEMBERS TABLE (EXACT IMAGE 2) -->
      <div class="lg:col-span-7 bg-white border border-[#E9E0D4] rounded-3xl p-6 sm:p-7 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#F4EFEA] pb-3.5">
          <div class="flex items-center space-x-2">
            <span class="text-lg">👥</span>
            <div>
              <h2 class="font-serif font-black text-lg text-[#2B1810]">
                Staff Members
              </h2>
              <p class="text-xs text-[#7D6E64]">Manage your team members across branches</p>
            </div>
          </div>

          <!-- Branch Filter Dropdown (Image 2) -->
          <div class="relative" v-if="!isBranchScoped">
            <select
              v-model="selectedBranchFilter"
              class="px-3 py-1.5 bg-[#FAF7F2] border border-[#E2D8CC] rounded-xl text-xs font-bold text-[#2B1810] cursor-pointer shadow-2xs"
            >
              <option value="all">All Branches ⌵</option>
              <option v-for="b in branches" :key="b.branch_id" :value="b.branch_id">
                {{ b.branch_name }}
              </option>
            </select>
          </div>
          <div v-else class="text-xs font-bold text-[#7D6E64] bg-[#FAF7F2] px-3 py-1.5 rounded-xl border border-[#E2D8CC] flex items-center space-x-1.5">
            <span>🏬</span>
            <span>{{ branches[0]?.branch_name || 'My Branch' }}</span>
          </div>
        </div>

        <!-- Table matching Image 2 Columns -->
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-[#FAF7F2] text-[#6E5D52] uppercase font-bold text-[10px] tracking-wider border-b border-[#E9E0D4]">
                <th class="py-3 px-3">Name</th>
                <th class="py-3 px-3">Email</th>
                <th class="py-3 px-3">Role</th>
                <th class="py-3 px-3">Branch</th>
                <th class="py-3 px-3">Status</th>
                <th class="py-3 px-3 text-center">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#F2ECE4]">
              <tr v-if="paginatedUsers.length === 0">
                <td colspan="6" class="py-8 text-center text-[#9E8E82] text-xs">
                  No staff members match the selected criteria.
                </td>
              </tr>
              <tr v-for="u in paginatedUsers" :key="u.user_id" class="hover:bg-[#FAF7F2]/60 transition">
                <!-- Name with Initials Avatar (Image 2) -->
                <td class="py-3 px-3">
                  <div class="flex items-center space-x-2.5">
                    <div class="w-7 h-7 rounded-full bg-[#57392C] text-amber-200 font-serif font-bold text-[11px] flex items-center justify-center shrink-0">
                      {{ getInitials(u.full_name) }}
                    </div>
                    <span class="font-bold text-[#2B1810] truncate max-w-[130px]">{{ u.full_name }}</span>
                  </div>
                </td>

                <td class="py-3 px-3 text-[#7D6E64] font-medium truncate max-w-[140px]" :title="u.email">
                  {{ u.email }}
                </td>

                <!-- Role Pill (Image 2: Purple for Manager, Cream for Staff, Pink for Seller) -->
                <td class="py-3 px-3">
                  <span
                    class="px-2.5 py-0.5 rounded-full text-[11px] font-bold border inline-block"
                    :class="getRoleBadgeClass(u.role)"
                  >
                    {{ displayTableRole(u.role) }}
                  </span>
                </td>

                <td class="py-3 px-3 text-[#6E5D52] font-medium">
                  {{ u.branch?.branch_name || 'Main Branch' }}
                </td>

                <!-- Status Pill with colored dot -->
                <td class="py-3 px-3">
                  <span
                    class="px-2.5 py-0.5 rounded-full text-[11px] font-bold inline-flex items-center space-x-1"
                    :class="getStatusBadgeClass(u.status)"
                  >
                    <span class="w-1.5 h-1.5 rounded-full inline-block" :class="getStatusDotClass(u.status)"></span>
                    <span>{{ u.status || 'Active' }}</span>
                  </span>
                </td>

                <!-- Action Button (⋮ menu or Super Admin only lock for managers) -->
                <td class="py-3 px-3 text-center">
                  <span
                    v-if="isBranchScoped && isManagerUser(u)"
                    class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-semibold text-stone-500 bg-stone-100 border border-stone-200 cursor-not-allowed select-none"
                    title="Only Super Admin can manage Branch Manager accounts and statuses"
                  >
                    🔒 Super Admin only
                  </span>
                  <button
                    v-else
                    @click="openEditModal(u)"
                    class="p-1 hover:bg-[#EFE8DE] rounded-lg transition text-[#6E5D52] hover:text-[#2B1810] font-bold cursor-pointer text-sm"
                    :title="isSuperAdmin ? 'Edit account & status' : 'Manage branch staff member'"
                  >
                    ⋮
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination (Exact Image 2: "Showing 1-5 of 24 staff members", < 1 2 3 >) -->
        <div class="flex items-center justify-between pt-3 border-t border-[#F4EFEA] text-xs text-[#7D6E64]">
          <div>
            Showing {{ paginationText }}
          </div>

          <div class="flex items-center space-x-1">
            <button
              @click="currentPage = Math.max(1, currentPage - 1)"
              :disabled="currentPage === 1"
              class="px-2.5 py-1 bg-[#FAF7F2] border border-[#E2D8CC] rounded-lg font-bold disabled:opacity-40 cursor-pointer text-xs"
            >
              &lt;
            </button>
            <button
              v-for="p in totalPages"
              :key="p"
              @click="currentPage = p"
              class="px-3 py-1 rounded-lg font-bold cursor-pointer transition text-xs"
              :class="currentPage === p ? 'bg-[#3D251E] text-white' : 'bg-[#FAF7F2] text-[#6E5D52] border border-[#E2D8CC] hover:bg-[#EFE8DE]'"
            >
              {{ p }}
            </button>
            <button
              @click="currentPage = Math.min(totalPages, currentPage + 1)"
              :disabled="currentPage >= totalPages"
              class="px-2.5 py-1 bg-[#FAF7F2] border border-[#E2D8CC] rounded-lg font-bold disabled:opacity-40 cursor-pointer text-xs"
            >
              &gt;
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- EDIT USER MODAL -->
    <div
      v-if="editingUser"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs"
      @click.self="editingUser = null"
    >
      <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-4 border border-[#E9E0D4] shadow-2xl">
        <div class="flex items-center justify-between border-b border-[#F4EFEA] pb-3">
          <h3 class="font-serif font-black text-lg text-[#2B1810]">
            Edit Staff Member Details
          </h3>
          <button @click="editingUser = null" class="text-[#7D6E64] hover:text-[#2B1810] text-xl font-bold cursor-pointer">✕</button>
        </div>

        <form @submit.prevent="submitEditUser" class="space-y-4 text-xs">
          <div>
            <label class="block text-[11px] font-bold text-[#7D6E64] uppercase mb-1">Full Name *</label>
            <input
              v-model="editForm.full_name"
              type="text"
              required
              class="w-full px-3.5 py-2.5 bg-white border border-[#E2D8CC] rounded-xl font-semibold text-[#2B1810]"
            />
          </div>

          <div>
            <label class="block text-[11px] font-bold text-[#7D6E64] uppercase mb-1">Email Address *</label>
            <input
              v-model="editForm.email"
              type="email"
              required
              class="w-full px-3.5 py-2.5 bg-white border border-[#E2D8CC] rounded-xl font-semibold text-[#2B1810]"
            />
          </div>

          <div>
            <label class="block text-[11px] font-bold text-[#7D6E64] uppercase mb-1">Phone Number</label>
            <input
              v-model="editForm.phone"
              type="text"
              class="w-full px-3.5 py-2.5 bg-white border border-[#E2D8CC] rounded-xl font-semibold text-[#2B1810]"
            />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] font-bold text-[#7D6E64] uppercase mb-1">Role</label>
              <select
                v-model="editForm.role"
                :disabled="isBranchScoped && ['Branch Manager', 'Main Branch Manager', 'Branch Admin', 'Super Admin'].includes(editingUser.role)"
                class="w-full px-3 py-2 bg-white border border-[#E2D8CC] rounded-xl font-bold text-[#2B1810] disabled:bg-gray-100 disabled:cursor-not-allowed"
              >
                <template v-if="isSuperAdmin">
                  <option value="Main Branch Manager">Main Branch Manager</option>
                  <option value="Branch Manager">Branch Manager</option>
                </template>
                <option value="Branch Seller / Cashier">Branch Seller / Cashier</option>
                <option value="Branch Staff">Branch Staff</option>
                <option value="Servant">Servant</option>
                <option value="Delivery Staff">Delivery Staff</option>
              </select>
            </div>

            <div>
              <label class="block text-[11px] font-bold text-[#7D6E64] uppercase mb-1">Branch</label>
              <select
                v-model="editForm.branch_id"
                :disabled="isBranchScoped"
                class="w-full px-3 py-2 bg-white border border-[#E2D8CC] rounded-xl font-bold text-[#2B1810] disabled:bg-gray-100 disabled:cursor-not-allowed"
              >
                <option v-if="isSuperAdmin" :value="null">None (HQ)</option>
                <option v-for="b in branches" :key="b.branch_id" :value="b.branch_id">
                  {{ b.branch_name }}
                </option>
              </select>
            </div>
          </div>

          <div>
            <div class="flex items-center justify-between mb-1">
              <label class="block text-[11px] font-bold text-[#7D6E64] uppercase">Account Status *</label>
              <span v-if="isBranchScoped && isManagerUser(editingUser)" class="text-[10px] text-amber-700 font-bold">
                🔒 Super Admin Controlled
              </span>
            </div>
            <select
              v-model="editForm.status"
              :disabled="isBranchScoped && isManagerUser(editingUser)"
              class="w-full px-3 py-2 bg-white border border-[#E2D8CC] rounded-xl font-bold text-[#2B1810] disabled:bg-stone-100 disabled:text-stone-500 disabled:cursor-not-allowed cursor-pointer"
            >
              <option value="Active">Active (Full access)</option>
              <option value="Inactive">Inactive (Deactivated)</option>
              <option value="Suspended">Suspended (Restricted / Blocked)</option>
            </select>
            <p v-if="isBranchScoped && isManagerUser(editingUser)" class="text-[10px] text-stone-500 mt-1">
              Branch Managers cannot change their own account status. Only the Super Admin can manage manager statuses.
            </p>
          </div>

          <div class="flex items-center justify-between pt-3 border-t border-[#F4EFEA]">
            <button
              v-if="(isSuperAdmin || (isBranchScoped && !isManagerUser(editingUser))) && editingUser.user_id !== authStore.user?.user_id"
              type="button"
              @click="deleteUser(editingUser.user_id)"
              class="px-3.5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-800 border border-rose-200 text-xs font-bold rounded-xl cursor-pointer"
            >
              Delete Staff
            </button>
            <div v-else></div>

            <div class="flex items-center space-x-2">
              <button
                type="button"
                @click="editingUser = null"
                class="px-4 py-2 bg-[#FAF7F2] text-[#6E5D52] font-bold rounded-xl cursor-pointer"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="savingEdit || (isBranchScoped && isManagerUser(editingUser))"
                class="px-5 py-2 bg-[#3D251E] hover:bg-[#2B1810] text-white font-bold rounded-xl transition cursor-pointer shadow-xs disabled:bg-stone-300 disabled:cursor-not-allowed"
              >
                {{ savingEdit ? 'Saving...' : 'Save Changes' }}
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useAuthStore } from '../../stores/auth';
import { useDialogStore } from '../../stores/dialog';
import { useToastStore } from '../../stores/toast';
import api from '../../api';

const authStore = useAuthStore();
const dialog = useDialogStore();
const toast = useToastStore();

const users = ref([]);
const branches = ref([]);
const searchQuery = ref('');
const roleFilterTab = ref('all');
const selectedBranchFilter = ref('all');
const currentPage = ref(1);
const perPage = 5;

const successMsg = ref('');
const errorMsg = ref('');
const savingUser = ref(false);
const savingEdit = ref(false);

const isSuperAdmin = computed(() => authStore.user?.role === 'Super Admin');
const isBranchScoped = computed(() => ['Main Branch Manager', 'Branch Manager', 'Manager', 'Branch Admin'].includes(authStore.user?.role));

// Live dynamic stats computed from loaded staff and branches
const stats = computed(() => {
  const total = users.value.length;
  const managers = users.value.filter(u => ['Main Branch Manager', 'Branch Manager', 'Manager', 'Branch Admin'].includes(u.role)).length;
  const staff = users.value.filter(u => !['Main Branch Manager', 'Branch Manager', 'Manager', 'Branch Admin', 'Super Admin'].includes(u.role)).length;
  return {
    totalStaff: total,
    totalBranches: branches.value.length,
    totalManagers: managers,
    staffMembers: staff,
  };
});

// New User Form State
const newUser = reactive({
  full_name: '',
  email: '',
  phone: '',
  role: 'Branch Seller / Cashier',
  branch_id: 1,
  is_active: true,
});

// Edit Modal State
const editingUser = ref(null);
const editForm = reactive({
  full_name: '',
  email: '',
  phone: '',
  role: '',
  branch_id: null,
  status: 'Active',
});

function getInitials(name) {
  if (!name) return 'SB';
  const parts = name.trim().split(' ');
  return parts.length > 1
    ? (parts[0][0] + parts[1][0]).toUpperCase()
    : parts[0].substring(0, 2).toUpperCase();
}

function displayTableRole(role) {
  if (role === 'Main Branch Manager' || role === 'Branch Manager') return 'Branch Manager';
  if (role === 'Branch Seller / Cashier' || role === 'Branch Seller' || role === 'Cashier') return 'Seller';
  if (role === 'Branch Staff') return 'Staff';
  return role;
}

function isManagerUser(user) {
  if (!user) return false;
  if (user.user_id === authStore.user?.user_id) return true;
  const role = user.original_role || user.role;
  return ['Super Admin', 'Main Branch Manager', 'Branch Manager', 'Branch Admin', 'Manager'].includes(role);
}

function getStatusBadgeClass(status) {
  if (status === 'Active') {
    return 'bg-emerald-50 text-emerald-800 border border-emerald-200';
  }
  if (status === 'Suspended') {
    return 'bg-amber-50 text-amber-800 border border-amber-200';
  }
  return 'bg-rose-50 text-rose-800 border border-rose-200';
}

function getStatusDotClass(status) {
  if (status === 'Active') return 'bg-emerald-600';
  if (status === 'Suspended') return 'bg-amber-600';
  return 'bg-rose-600';
}

function getRoleBadgeClass(role) {
  const norm = displayTableRole(role);
  if (norm === 'Branch Manager' || norm === 'Manager') {
    return 'bg-[#F5F3FF] text-[#6D28D9] border-[#DDD6FE]';
  }
  if (norm === 'Seller') {
    return 'bg-[#FFE4E6] text-[#9F1239] border-[#FECDD3]';
  }
  return 'bg-[#FEF7E6] text-[#B87A14] border-[#FBEAC2]';
}

// Filtering users
const filteredUsers = computed(() => {
  return users.value.filter(u => {
    // Role filter tab
    const norm = displayTableRole(u.role);
    if (roleFilterTab.value === 'staff') {
      if (norm === 'Branch Manager' || norm === 'Manager' || u.role === 'Super Admin') return false;
    } else if (roleFilterTab.value === 'managers') {
      if (norm !== 'Branch Manager' && norm !== 'Manager') return false;
    }

    // Branch filter dropdown
    if (selectedBranchFilter.value !== 'all') {
      if (Number(u.branch_id) !== Number(selectedBranchFilter.value)) return false;
    }

    // Search query
    if (searchQuery.value.trim()) {
      const q = searchQuery.value.toLowerCase();
      const matchName = u.full_name?.toLowerCase().includes(q);
      const matchEmail = u.email?.toLowerCase().includes(q);
      const matchRole = u.role?.toLowerCase().includes(q);
      const matchBranch = u.branch?.branch_name?.toLowerCase().includes(q);
      if (!matchName && !matchEmail && !matchRole && !matchBranch) return false;
    }

    return true;
  });
});

const totalPages = computed(() => Math.max(1, Math.ceil(filteredUsers.value.length / perPage)));

const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * perPage;
  return filteredUsers.value.slice(start, start + perPage);
});

const paginationText = computed(() => {
  const total = filteredUsers.value.length;
  if (total === 0) return '0 of 0 staff members';
  const start = (currentPage.value - 1) * perPage + 1;
  const end = Math.min(start + perPage - 1, total);
  return `${start}-${end} of ${total} staff members`;
});

function focusAddUser() {
  const el = document.getElementById('add-team-member-card');
  if (el) el.scrollIntoView({ behavior: 'smooth' });
}

function resetNewUser() {
  newUser.full_name = '';
  newUser.email = '';
  newUser.phone = '';
  newUser.role = 'Branch Seller / Cashier';
  newUser.branch_id = isBranchScoped.value ? (authStore.user?.branch_id || branches.value[0]?.branch_id) : (branches.value[0]?.branch_id || 1);
  newUser.is_active = true;
}

async function fetchUsers() {
  try {
    const res = await api.get('/admin/users');
    if (res.data) {
      const fetched = Array.isArray(res.data) ? res.data : (res.data.data || []);
      users.value = fetched;
    }
  } catch (e) {
    console.error('Failed to load users from API:', e);
  }
}

async function fetchBranches() {
  try {
    const res = await api.get('/branches');
    if (res.data) {
      const fetched = Array.isArray(res.data) ? res.data : (res.data.data || []);
      if (isBranchScoped.value && authStore.user?.branch_id) {
        branches.value = fetched.filter(b => b.branch_id === authStore.user.branch_id);
        selectedBranchFilter.value = authStore.user.branch_id;
        newUser.branch_id = authStore.user.branch_id;
      } else {
        branches.value = fetched;
        if (branches.value.length > 0 && !newUser.branch_id) {
          newUser.branch_id = branches.value[0].branch_id;
        }
      }
    }
  } catch (e) {
    console.error('Failed to load branches from API:', e);
  }
}

async function submitAddUser() {
  savingUser.value = true;
  errorMsg.value = '';
  successMsg.value = '';
  try {
    const payload = {
      full_name: newUser.full_name,
      email: newUser.email,
      phone: newUser.phone,
      role: newUser.role,
      branch_id: newUser.branch_id,
      status: newUser.is_active ? 'Active' : 'Inactive',
      password: 'Password123!',
    };

    const res = await api.post('/admin/users', payload);
    const added = res.data || {
      user_id: Date.now(),
      ...payload,
      branch: branches.value.find(b => b.branch_id === newUser.branch_id) || { branch_name: 'Main Branch' },
    };
    users.value.unshift(added);
    successMsg.value = `Staff member ${newUser.full_name} added successfully!`;
    resetNewUser();
  } catch (err) {
    console.error('Failed to add user:', err);
    errorMsg.value = err.response?.data?.message || 'Failed to add team member.';
  } finally {
    savingUser.value = false;
  }
}

function openEditModal(user) {
  if (isBranchScoped.value && isManagerUser(user)) {
    errorMsg.value = 'Branch Managers cannot modify manager accounts. Only the Super Admin can manage and change Branch Manager account statuses.';
    return;
  }
  editingUser.value = user;
  editForm.full_name = user.full_name;
  editForm.email = user.email;
  editForm.phone = user.phone || '';
  editForm.role = user.original_role || user.role;
  editForm.branch_id = user.branch_id;
  editForm.status = user.status || 'Active';
}

async function submitEditUser() {
  if (isBranchScoped.value && isManagerUser(editingUser.value)) {
    errorMsg.value = 'Access denied: Only the Super Admin can manage Branch Manager accounts and statuses.';
    editingUser.value = null;
    return;
  }
  savingEdit.value = true;
  errorMsg.value = '';
  successMsg.value = '';
  try {
    const res = await api.put(`/admin/users/${editingUser.value.user_id}`, editForm);
    const updated = res.data;
    const target = users.value.find(u => u.user_id === editingUser.value.user_id);
    if (target) {
      target.full_name = updated.full_name || editForm.full_name;
      target.email = updated.email || editForm.email;
      target.phone = updated.phone || editForm.phone;
      target.role = updated.role || editForm.role;
      target.branch_id = updated.branch_id || editForm.branch_id;
      target.status = updated.status || editForm.status;
      target.branch = updated.branch || branches.value.find(b => b.branch_id === editForm.branch_id) || target.branch;
    }
    successMsg.value = `Staff account ${editForm.full_name} updated successfully! Status: ${editForm.status}`;
    editingUser.value = null;
  } catch (err) {
    console.error('Failed to update user:', err);
    errorMsg.value = err.response?.data?.message || 'Failed to update user account.';
  } finally {
    savingEdit.value = false;
  }
}

async function deleteUser(id) {
  const target = users.value.find(u => u.user_id === id);
  if (isBranchScoped.value && isManagerUser(target)) {
    const msg = 'Access denied: Only the Super Admin can delete or remove Branch Manager accounts.';
    errorMsg.value = msg;
    toast.error(msg);
    return;
  }
  const confirmed = await dialog.confirm({
    title: 'Remove Staff Account',
    message: target?.full_name ? `Are you sure you want to remove the staff account for "${target.full_name}"?` : 'Are you sure you want to remove this staff account?',
    confirmText: 'Remove Account',
    variant: 'danger',
  });
  if (!confirmed) return;
  try {
    await api.delete(`/admin/users/${id}`);
    users.value = users.value.filter(u => u.user_id !== id);
    const msg = 'Staff account deleted successfully.';
    successMsg.value = msg;
    toast.success(msg);
    editingUser.value = null;
  } catch (err) {
    const msg = err.response?.data?.message || 'Failed to delete user.';
    toast.error(msg);
  }
}

onMounted(async () => {
  await fetchBranches();
  await fetchUsers();
});
</script>
