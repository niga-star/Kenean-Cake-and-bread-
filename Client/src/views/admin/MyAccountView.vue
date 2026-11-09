<template>
  <div class="space-y-6 max-w-7xl mx-auto font-poppins pb-16">
    <!-- ======================================================== -->
    <!-- 1. LUXURY BAKERY HEADER BANNER                           -->
    <!-- ======================================================== -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#FFF5F2] via-[#FFF0EC] to-[#FCE7E1] border border-[#F7E1D7] p-6 sm:p-7 shadow-xs">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-10">
        <div class="flex items-center space-x-4">
          <div class="w-13 h-13 rounded-2xl bg-[#FFE4DC] text-[#BA5420] flex items-center justify-center text-2xl shrink-0 shadow-2xs border border-[#FAD2C5]">
            <svg class="w-6 h-6 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
          </div>
          <div>
            <h1 class="font-serif text-2xl sm:text-3xl font-black text-[#2B1810] tracking-tight">
              My Staff Account
            </h1>
            <p class="text-xs sm:text-sm text-[#7D6E64] font-medium mt-1">
              Your official employee profile and salary details
            </p>
          </div>
        </div>

        <div class="flex items-center space-x-2 self-start sm:self-auto">
          <!-- View Switcher (Helper for testing Seller vs Manager layout) -->
          <button
            v-if="isSuperAdmin"
            @click="forceManagerMode = !forceManagerMode"
            class="px-3 py-2 bg-white/80 hover:bg-white text-[#3D251E] text-xs font-bold rounded-xl border border-[#E8DFD5] transition shadow-2xs"
            title="Toggle between Seller View (Image 1) and Manager View (Image 3)"
          >
            Switch View: {{ currentDisplayMode === 'manager' ? '👔 Manager Mode' : '🧁 Seller Mode' }}
          </button>

          <button
            @click="openRequestModal"
            class="px-4 py-2.5 bg-[#3D251E] hover:bg-[#2B1810] text-white text-xs sm:text-sm font-bold rounded-xl transition cursor-pointer flex items-center space-x-2 shadow-xs"
          >
            <span>✏️</span>
            <span>Request Correction</span>
          </button>
        </div>
      </div>

      <!-- Cupcakes Banner Artwork on the Right (Exact Reference) -->
      <div class="absolute right-0 top-0 bottom-0 w-1/3 max-w-[340px] pointer-events-none hidden md:block overflow-hidden">
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

    <!-- ======================================================== -->
    <!-- 2. TOP PROFILE SUMMARY CARD (Exact Image 1 & Image 3)    -->
    <!-- ======================================================== -->
    <div class="bg-white border border-[#E9E0D4] rounded-3xl p-6 sm:p-7 shadow-xs">
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <!-- Left: Avatar + Identity -->
        <div class="flex items-center space-x-4 sm:space-x-5 min-w-0">
          <div class="relative shrink-0">
            <div class="w-18 h-18 sm:w-20 sm:h-20 rounded-full overflow-hidden bg-[#57392C] border-2 border-amber-200/60 shadow-xs flex items-center justify-center text-white text-xl sm:text-2xl font-bold font-serif">
              <img
                src="/mockup/avatar_mekdes.jpg"
                alt="Avatar"
                class="w-full h-full object-cover"
                onerror="this.style.display='none'"
              />
              <span v-if="!userProfile.avatar">{{ userInitials }}</span>
            </div>
            <!-- Online status dot -->
            <span class="absolute bottom-1 right-1 w-4 h-4 bg-emerald-500 border-2 border-white rounded-full shadow-2xs"></span>
          </div>

          <div class="min-w-0 space-y-1.5">
            <div class="flex flex-wrap items-center gap-2">
              <h2 class="text-lg sm:text-2xl font-serif font-black text-[#2B1810] truncate">
                {{ userProfile.full_name || authStore.user?.full_name || 'Staff Member' }}
              </h2>
              <span
                class="px-2.5 py-0.5 rounded-full text-xs font-bold border shrink-0"
                :class="currentDisplayMode === 'manager'
                  ? 'bg-[#FEF7E6] text-[#B87A14] border-[#FBEAC2]'
                  : 'bg-[#FFE4E6] text-[#9F1239] border-[#FECDD3]'"
              >
                {{ currentDisplayMode === 'manager' ? 'Main Branch Manager' : (displayRole || 'Branch Seller / Cashier') }}
              </span>
            </div>

            <div class="flex flex-wrap items-center gap-2 text-xs text-[#7D6E64] font-medium">
              <span class="flex items-center space-x-1">
                <span>✉️</span>
                <span>{{ userProfile.email || authStore.user?.email || '—' }}</span>
              </span>
              <span class="text-[#D8CDC5] hidden sm:inline">|</span>
              <span class="flex items-center space-x-1">
                <span>📞</span>
                <span>{{ userProfile.phone || authStore.user?.phone || '—' }}</span>
              </span>
            </div>

            <div class="flex flex-wrap items-center gap-2 pt-1">
              <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center space-x-1">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 inline-block"></span>
                <span>{{ userProfile.status || 'Active' }}</span>
              </span>

              <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FAF7F2] text-[#6E5D52] border border-[#E5DACD] flex items-center space-x-1">
                <span>📍</span>
                <span>{{ userProfile.branch?.branch_name || 'Main Branch' }}</span>
              </span>
            </div>
          </div>
        </div>

        <!-- Right: Wage Card (Seller View - Image 1) OR Employee ID + Join Date (Manager View - Image 3) -->
        <template v-if="currentDisplayMode === 'seller'">
          <div class="bg-[#FFF8F4] border border-[#F4E3D7] rounded-2xl p-4 sm:p-5 flex items-center justify-between sm:justify-end space-x-4 shrink-0 shadow-2xs">
            <div class="w-11 h-11 rounded-xl bg-[#FFEBDC] text-[#BA5420] flex items-center justify-center text-xl shrink-0">
              💰
            </div>
            <div class="text-right">
              <div class="text-[10px] font-bold text-[#8C7A6D] uppercase tracking-wider">
                Assigned Monthly Wage
              </div>
              <div class="text-2xl sm:text-3xl font-serif font-black text-[#2B1810] mt-0.5">
                ETB {{ Number(userProfile.salary || 0).toLocaleString() }}
              </div>
              <div class="text-[11px] text-[#7D6E64] font-medium mt-0.5">
                Managed by Super Admin
              </div>
            </div>
          </div>
        </template>
        <template v-else>
          <div class="flex items-center space-x-6 sm:space-x-8 shrink-0 bg-[#FFF8F4] sm:bg-transparent p-4 sm:p-0 rounded-2xl border sm:border-0 border-[#F4E3D7]">
            <div>
              <div class="text-[11px] font-medium text-[#7D6E64]">Employee ID</div>
              <div class="text-xl sm:text-2xl font-serif font-black text-[#2B1810] mt-0.5">
                {{ userProfile.employee_id || ('EMP-' + String(userProfile.user_id || authStore.user?.user_id || 1).padStart(3, '0')) }}
              </div>
            </div>
            <div class="border-l border-[#EADFD4] pl-6 sm:pl-8">
              <div class="text-[11px] font-medium text-[#7D6E64]">Join Date</div>
              <div class="text-sm sm:text-base font-bold text-[#2B1810] flex items-center space-x-1.5 mt-0.5">
                <span>📅</span>
                <span>{{ formattedJoinDate }}</span>
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODE A: BRANCH SELLER / CASHIER VIEW (EXACT IMAGE 1)                      -->
    <!-- ========================================================================= -->
    <div v-if="currentDisplayMode === 'seller'" class="space-y-6">
      <!-- 3 Navigation Tabs (Image 1) -->
      <div class="flex flex-wrap items-center gap-2">
        <button
          @click="sellerTab = 'profile'"
          class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition cursor-pointer flex items-center space-x-2 shadow-2xs"
          :class="sellerTab === 'profile' ? 'bg-[#3D251E] text-white shadow-xs' : 'bg-[#F4ECE3] text-[#6E5D52] hover:bg-[#EAE0D5]'"
        >
          <span>👤</span>
          <span>Profile Details</span>
        </button>

        <button
          @click="sellerTab = 'employment'"
          class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition cursor-pointer flex items-center space-x-2 shadow-2xs"
          :class="sellerTab === 'employment' ? 'bg-[#3D251E] text-white shadow-xs' : 'bg-[#F4ECE3] text-[#6E5D52] hover:bg-[#EAE0D5]'"
        >
          <span>💼</span>
          <span>Employment Record</span>
        </button>

        <button
          @click="sellerTab = 'permissions'"
          class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition cursor-pointer flex items-center space-x-2 shadow-2xs"
          :class="sellerTab === 'permissions' ? 'bg-[#3D251E] text-white shadow-xs' : 'bg-[#F4ECE3] text-[#6E5D52] hover:bg-[#EAE0D5]'"
        >
          <span>🛡️</span>
          <span>Role &amp; Permissions</span>
        </button>
      </div>

      <!-- TAB 1: PROFILE DETAILS (Exact Image 1 Two Equal Cards) -->
      <div v-show="sellerTab === 'profile'" class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
        <!-- Left Card: Staff Information -->
        <div class="bg-white border border-[#E9E0D4] rounded-3xl p-6 sm:p-7 shadow-xs space-y-5">
          <div class="flex items-center justify-between border-b border-[#F4EFEA] pb-3.5">
            <h3 class="font-serif font-black text-lg text-[#2B1810] flex items-center space-x-2">
              <span>💼</span>
              <span>Staff Information</span>
            </h3>
            <button
              @click="openEditModal"
              class="px-3 py-1.5 bg-[#FAF7F2] hover:bg-[#F0E6D8] border border-[#E2D6C6] text-xs font-bold text-[#3D251E] rounded-xl transition cursor-pointer flex items-center space-x-1 shadow-2xs"
            >
              <span>✏️</span>
              <span>Edit Profile</span>
            </button>
          </div>

          <div class="space-y-3.5 text-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between py-1.5 border-b border-[#F6F0E8] gap-1">
              <span class="text-[#7D6E64] font-medium">Full Name</span>
              <span class="font-bold text-[#2B1810] text-sm">{{ userProfile.full_name || authStore.user?.full_name || 'Staff Member' }}</span>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between py-1.5 border-b border-[#F6F0E8] gap-1">
              <span class="text-[#7D6E64] font-medium">Email Address</span>
              <span class="font-semibold text-[#2B1810]">{{ userProfile.email || authStore.user?.email || '—' }}</span>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between py-1.5 border-b border-[#F6F0E8] gap-1">
              <span class="text-[#7D6E64] font-medium">Phone Number</span>
              <span class="font-mono font-semibold text-[#2B1810]">{{ userProfile.phone || authStore.user?.phone || '—' }}</span>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between py-1.5 border-b border-[#F6F0E8] gap-1">
              <span class="text-[#7D6E64] font-medium">Branch</span>
              <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#FAF7F2] text-[#6E5D52] border border-[#E5DACD] flex items-center space-x-1">
                <span>📍</span>
                <span>{{ userProfile.branch?.branch_name || authStore.user?.branch?.branch_name || 'Main Branch - Dessie Piazza' }}</span>
              </span>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between py-1.5 border-b border-[#F6F0E8] gap-1">
              <span class="text-[#7D6E64] font-medium">Role</span>
              <span class="font-bold text-[#3D251E]">{{ displayRole || 'Branch Seller / Cashier' }}</span>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between py-1.5 border-b border-[#F6F0E8] gap-1">
              <span class="text-[#7D6E64] font-medium">Employment Status</span>
              <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                🟢 {{ userProfile.status || 'Active' }}
              </span>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between py-1.5 gap-1">
              <span class="text-[#7D6E64] font-medium">Hire Date</span>
              <span class="font-semibold text-[#2B1810] flex items-center space-x-1">
                <span>📅</span>
                <span>{{ userProfile.hire_date || '2026-11-08' }}</span>
              </span>
            </div>
          </div>
        </div>

        <!-- Right Card: Role Scope & Permissions with Cupcake Corner Graphic -->
        <div class="bg-white border border-[#E9E0D4] rounded-3xl p-6 sm:p-7 shadow-xs space-y-5 relative overflow-hidden">
          <h3 class="font-serif font-black text-lg text-[#2B1810] flex items-center space-x-2 border-b border-[#F4EFEA] pb-3.5">
            <span>🛡️</span>
            <span>Role Scope &amp; Permissions</span>
          </h3>

          <!-- Pink Branch Access Inner Card (Image 1) -->
          <div class="p-4 bg-[#FFF0F2] border border-[#FCD5DC] rounded-2xl flex items-center justify-between">
            <div class="flex items-center space-x-3">
              <div class="w-10 h-10 rounded-xl bg-white text-rose-800 flex items-center justify-center text-lg font-bold shadow-2xs border border-[#FAD2C5]">
                🏬
              </div>
              <div>
                <div class="text-[10px] font-bold text-[#8C4A5A] uppercase tracking-wider">Branch Access</div>
                <div class="font-bold text-sm text-[#2B1810]">{{ userProfile.branch?.branch_name || 'Main Branch' }}</div>
                <div class="text-[11px] text-[#7D6E64]">You have access to this branch only.</div>
              </div>
            </div>
            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 shrink-0">
              ✓ Branch Access
            </span>
          </div>

          <!-- Permissions Checkmark List (Image 1) -->
          <div class="space-y-3 pt-1">
            <div class="text-xs font-bold uppercase tracking-wider text-[#7D6E64]">Your Permissions</div>
            <ul class="space-y-2.5 text-xs text-[#2B1810] font-medium">
              <li class="flex items-center space-x-2.5">
                <span class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-black flex items-center justify-center shrink-0">✓</span>
                <span>Manage your daily orders and custom cake sales</span>
              </li>
              <li class="flex items-center space-x-2.5">
                <span class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-black flex items-center justify-center shrink-0">✓</span>
                <span>View your staff information</span>
              </li>
              <li class="flex items-center space-x-2.5">
                <span class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-black flex items-center justify-center shrink-0">✓</span>
                <span>Submit daily sales reports</span>
              </li>
              <li class="flex items-center space-x-2.5">
                <span class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-black flex items-center justify-center shrink-0">✓</span>
                <span>Access your account settings</span>
              </li>
            </ul>
          </div>

          <!-- Cupcake Graphic in Bottom-Right Corner (Exact Image 1) -->
          <div class="absolute -bottom-2 -right-2 w-32 h-32 pointer-events-none opacity-90 select-none overflow-hidden">
            <img
              src="/mockup/cupcake_corner_accent.jpg"
              alt="Cupcake Art"
              class="w-full h-full object-contain"
              onerror="this.style.display='none'"
            />
          </div>
        </div>
      </div>

      <!-- TAB 2: EMPLOYMENT RECORD -->
      <div v-show="sellerTab === 'employment'" class="bg-white border border-[#E9E0D4] rounded-3xl p-6 sm:p-7 shadow-xs space-y-5">
        <h3 class="font-serif font-black text-lg text-[#2B1810] flex items-center space-x-2 border-b border-[#F4EFEA] pb-3.5">
          <span>💼</span>
          <span>Official Employment &amp; Wage Record</span>
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
          <div class="p-4 bg-[#FAF7F2] rounded-2xl border border-[#E8DFD5]">
            <div class="text-[10px] font-bold text-[#7D6E64] uppercase">Employee Code</div>
            <div class="text-base font-serif font-black text-[#2B1810] mt-1">EMP-084</div>
            <div class="text-[11px] text-[#7D6E64] mt-0.5">Permanent Staff</div>
          </div>
          <div class="p-4 bg-[#FAF7F2] rounded-2xl border border-[#E8DFD5]">
            <div class="text-[10px] font-bold text-[#7D6E64] uppercase">Monthly Wage</div>
            <div class="text-base font-serif font-black text-[#BA5420] mt-1">ETB {{ Number(userProfile.salary || 0).toLocaleString() }}</div>
            <div class="text-[11px] text-[#7D6E64] mt-0.5">Disbursed on 30th</div>
          </div>
          <div class="p-4 bg-[#FAF7F2] rounded-2xl border border-[#E8DFD5]">
            <div class="text-[10px] font-bold text-[#7D6E64] uppercase">Department</div>
            <div class="text-base font-bold text-[#2B1810] mt-1">Retail Front Counter</div>
            <div class="text-[11px] text-[#7D6E64] mt-0.5">Branch Sales</div>
          </div>
          <div class="p-4 bg-[#FAF7F2] rounded-2xl border border-[#E8DFD5]">
            <div class="text-[10px] font-bold text-[#7D6E64] uppercase">Contract Status</div>
            <div class="text-base font-bold text-emerald-800 mt-1">Active Regular</div>
            <div class="text-[11px] text-[#7D6E64] mt-0.5">Full-time Tenured</div>
          </div>
        </div>
      </div>

      <!-- TAB 3: ROLE & PERMISSIONS BREAKDOWN -->
      <div v-show="sellerTab === 'permissions'" class="bg-white border border-[#E9E0D4] rounded-3xl p-6 sm:p-7 shadow-xs space-y-4">
        <h3 class="font-serif font-black text-lg text-[#2B1810] flex items-center space-x-2 border-b border-[#F4EFEA] pb-3.5">
          <span>🛡️</span>
          <span>Security &amp; Authorization Matrix</span>
        </h3>
        <div class="p-4 bg-[#FEF9E7] border border-[#FBEEC8] rounded-2xl text-xs text-[#8A5A00] space-y-1">
          <div class="font-bold">🔒 Multi-Branch Data Isolation Active</div>
          <div>Your account is strictly isolated to <strong>{{ userProfile.branch?.branch_name || 'Main Branch' }}</strong>. You can only record orders and sales for your designated branch counter.</div>
        </div>
      </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODE B: MAIN BRANCH MANAGER VIEW (EXACT IMAGE 3)                          -->
    <!-- ========================================================================= -->
    <div v-else class="space-y-6">
      <!-- Four KPI Stat Cards (Dynamic Real Counts) -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 text-xs">
        <!-- Total Staff -->
        <div class="bg-white border border-[#E9E0D4] rounded-2xl p-4 shadow-2xs flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-[#FFF5F2] text-rose-700 flex items-center justify-center text-lg font-bold">👥</div>
            <div>
              <div class="text-xl sm:text-2xl font-serif font-black text-[#2B1810]">{{ managerStats.totalStaff }}</div>
              <div class="text-[11px] text-[#7D6E64] font-medium">Total Staff</div>
            </div>
          </div>
          <span class="text-[11px] font-bold text-emerald-700">Active</span>
        </div>

        <!-- Branches -->
        <div class="bg-white border border-[#E9E0D4] rounded-2xl p-4 shadow-2xs flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-[#FEF7E6] text-amber-700 flex items-center justify-center text-lg font-bold">🏬</div>
            <div>
              <div class="text-xl sm:text-2xl font-serif font-black text-[#2B1810]">{{ managerStats.totalBranches }}</div>
              <div class="text-[11px] text-[#7D6E64] font-medium">Branch</div>
            </div>
          </div>
          <span class="text-[11px] font-bold text-[#7D6E64]">Active</span>
        </div>

        <!-- Managers -->
        <div class="bg-white border border-[#E9E0D4] rounded-2xl p-4 shadow-2xs flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-[#F5F3FF] text-purple-700 flex items-center justify-center text-lg font-bold">🛡️</div>
            <div>
              <div class="text-xl sm:text-2xl font-serif font-black text-[#2B1810]">{{ managerStats.totalManagers }}</div>
              <div class="text-[11px] text-[#7D6E64] font-medium">Managers</div>
            </div>
          </div>
          <span class="text-[11px] font-bold text-[#7D6E64]">Assigned</span>
        </div>

        <!-- Active Staff -->
        <div class="bg-white border border-[#E9E0D4] rounded-2xl p-4 shadow-2xs flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-[#F0FDF4] text-emerald-700 flex items-center justify-center text-lg font-bold">👤</div>
            <div>
              <div class="text-xl sm:text-2xl font-serif font-black text-[#2B1810]">{{ managerStats.activeStaff }}</div>
              <div class="text-[11px] text-[#7D6E64] font-medium">Active Staff</div>
            </div>
          </div>
          <span class="text-[11px] font-bold text-emerald-700">Team</span>
        </div>
      </div>

      <!-- Main Two-Column Layout (Exact Image 3) -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- LEFT COLUMN (7 cols / ~58%): Personal Information Edit Form -->
        <div class="lg:col-span-7 bg-white border border-[#E9E0D4] rounded-3xl p-6 sm:p-7 shadow-xs space-y-5">
          <h3 class="font-serif font-black text-lg text-[#2B1810] flex items-center space-x-2 border-b border-[#F4EFEA] pb-3.5">
            <span>👤</span>
            <span>Personal Information</span>
          </h3>

          <form @submit.prevent="updateProfile" class="space-y-4 text-xs">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <!-- Full Name -->
              <div>
                <label class="block text-[11px] font-bold text-[#7D6E64] uppercase mb-1">Full Name *</label>
                <div class="relative">
                  <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-[#7D6E64]">👤</span>
                  <input
                    v-model="editForm.full_name"
                    type="text"
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
                    v-model="editForm.email"
                    type="email"
                    required
                    class="w-full pl-9 pr-3.5 py-2.5 bg-white border border-[#E2D8CC] rounded-xl font-semibold text-[#2B1810] focus:ring-2 focus:ring-[#3D251E]/20 focus:border-[#3D251E]"
                  />
                </div>
              </div>

              <!-- Phone Number -->
              <div>
                <label class="block text-[11px] font-bold text-[#7D6E64] uppercase mb-1">Phone Number *</label>
                <div class="relative">
                  <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-[#7D6E64]">📞</span>
                  <input
                    v-model="editForm.phone"
                    type="text"
                    required
                    class="w-full pl-9 pr-3.5 py-2.5 bg-white border border-[#E2D8CC] rounded-xl font-semibold text-[#2B1810] focus:ring-2 focus:ring-[#3D251E]/20 focus:border-[#3D251E]"
                  />
                </div>
              </div>

              <!-- Role -->
              <div>
                <label class="block text-[11px] font-bold text-[#7D6E64] uppercase mb-1">Role</label>
                <div class="relative">
                  <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-[#7D6E64]">💼</span>
                  <input
                    :value="'Main Branch Manager'"
                    disabled
                    class="w-full pl-9 pr-3.5 py-2.5 bg-[#FAF7F2] border border-[#E2D8CC] rounded-xl font-bold text-[#2B1810] cursor-not-allowed"
                  />
                </div>
              </div>

              <!-- Branch -->
              <div>
                <label class="block text-[11px] font-bold text-[#7D6E64] uppercase mb-1">Branch ⌵</label>
                <div class="relative">
                  <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-[#7D6E64]">🏬</span>
                  <select
                    disabled
                    class="w-full pl-9 pr-3.5 py-2.5 bg-[#FAF7F2] border border-[#E2D8CC] rounded-xl font-bold text-[#2B1810] cursor-not-allowed"
                  >
                    <option selected>Main Branch</option>
                  </select>
                </div>
              </div>

              <!-- Status Toggle Switch -->
              <div>
                <label class="block text-[11px] font-bold text-[#7D6E64] uppercase mb-1">Status</label>
                <div class="flex items-center space-x-3 pt-1">
                  <label class="flex items-center space-x-2 cursor-pointer">
                    <input
                      v-model="managerActiveToggle"
                      type="checkbox"
                      class="sr-only peer"
                    />
                    <div class="w-10 h-5 bg-[#E5DACD] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600 relative"></div>
                    <span class="text-xs font-bold text-[#2B1810]">{{ managerActiveToggle ? 'Active' : 'Inactive' }}</span>
                  </label>
                </div>
              </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-[#F4EFEA]">
              <button
                type="button"
                @click="resetEditForm"
                class="px-5 py-2.5 bg-[#FAF7F2] hover:bg-[#F0E6D8] border border-[#E2D6C6] text-xs font-bold text-[#3D251E] rounded-xl transition cursor-pointer flex items-center space-x-1"
              >
                <span>🔄</span>
                <span>Reset</span>
              </button>

              <button
                type="submit"
                :disabled="savingProfile"
                class="px-6 py-2.5 bg-[#3D251E] hover:bg-[#2B1810] text-white text-xs font-bold rounded-xl transition cursor-pointer shadow-xs flex items-center space-x-1.5"
              >
                <span>💾</span>
                <span>{{ savingProfile ? 'Saving...' : 'Update Profile' }}</span>
              </button>
            </div>
          </form>
        </div>

        <!-- RIGHT COLUMN (5 cols / ~42%): Stacked Role & Scope Card + Recent Activity Card -->
        <div class="lg:col-span-5 space-y-6">
          <!-- Card 1: Role & Scope -->
          <div class="bg-white border border-[#E9E0D4] rounded-3xl p-6 shadow-xs space-y-4">
            <h3 class="font-serif font-black text-sm uppercase tracking-wider text-[#2B1810] flex items-center space-x-2 border-b border-[#F4EFEA] pb-3">
              <span>🛡️</span>
              <span>Role &amp; Scope</span>
            </h3>

            <!-- Crown + Manager Role Subcard -->
            <div class="p-3.5 bg-[#FFF5F2] border border-[#FAD2C5] rounded-2xl flex items-center space-x-3">
              <div class="w-10 h-10 rounded-xl bg-[#FFE4DC] text-amber-700 flex items-center justify-center text-xl shrink-0 shadow-2xs">
                👑
              </div>
              <div>
                <div class="font-bold text-sm text-[#2B1810]">Main Branch Manager</div>
                <div class="text-[11px] text-[#7D6E64]">Manage your branch staff, sales, and reports</div>
              </div>
            </div>

            <!-- Assigned Branch & Scope Split Row -->
            <div class="grid grid-cols-2 gap-3 pt-1">
              <div class="p-3 bg-[#FAF7F2] border border-[#E8DFD5] rounded-xl">
                <div class="text-[10px] font-bold text-[#7D6E64] uppercase flex items-center space-x-1">
                  <span>🏬</span>
                  <span>Assigned Branch</span>
                </div>
                <div class="text-xs font-bold text-[#2B1810] mt-1">Main Branch</div>
              </div>

              <div class="p-3 bg-[#FAF7F2] border border-[#E8DFD5] rounded-xl">
                <div class="text-[10px] font-bold text-[#7D6E64] uppercase flex items-center space-x-1">
                  <span>🟢</span>
                  <span>Scope</span>
                </div>
                <div class="text-xs font-bold text-[#2B1810] mt-1">Main Branch Only</div>
              </div>
            </div>
          </div>

          <!-- Card 2: Recent Activity Timeline -->
          <div class="bg-white border border-[#E9E0D4] rounded-3xl p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-[#F4EFEA] pb-3">
              <h3 class="font-serif font-black text-sm uppercase tracking-wider text-[#2B1810] flex items-center space-x-1.5">
                <span>🕒</span>
                <span>Recent Activity</span>
              </h3>
              <a href="#" class="text-[11px] font-bold text-[#BA5420] hover:underline flex items-center space-x-0.5">
                <span>View All</span>
                <span>→</span>
              </a>
            </div>

            <div class="space-y-3.5 text-xs">
              <div v-if="recentActivities.length === 0" class="py-6 text-center text-[#A6978B] text-xs">
                No recent system activity recorded yet.
              </div>
              <div
                v-for="(act, idx) in recentActivities"
                :key="act.log_id || idx"
                class="flex items-start space-x-3"
              >
                <div class="w-6 h-6 rounded-full bg-[#57392C]/10 text-[#57392C] text-xs font-black flex items-center justify-center shrink-0 mt-0.5">
                  {{ act.action ? act.action[0].toUpperCase() : '•' }}
                </div>
                <div class="min-w-0 flex-1">
                  <div class="flex items-center justify-between">
                    <span class="font-bold text-[#2B1810] capitalize">{{ act.action || 'Activity' }}</span>
                    <span class="text-[10px] text-[#A6978B]">{{ formatActivityTime(act.created_at) }}</span>
                  </div>
                  <div class="text-[11px] text-[#7D6E64] truncate">{{ act.record || act.new_value || act.action }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- EDIT PROFILE MODAL (When in Seller View)                 -->
    <!-- ======================================================== -->
    <div
      v-if="showEditModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs"
      @click.self="showEditModal = false"
    >
      <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-4 border border-[#E9E0D4] shadow-2xl">
        <div class="flex items-center justify-between border-b border-[#F4EFEA] pb-3">
          <h3 class="font-serif font-black text-lg text-[#2B1810]">
            Edit Staff Information
          </h3>
          <button @click="showEditModal = false" class="text-[#7D6E64] hover:text-[#2B1810] text-xl font-bold cursor-pointer">✕</button>
        </div>

        <form @submit.prevent="updateProfileModal" class="space-y-4 text-xs">
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

          <div class="flex items-center justify-end space-x-2 pt-3 border-t border-[#F4EFEA]">
            <button
              type="button"
              @click="showEditModal = false"
              class="px-4 py-2 bg-[#FAF7F2] text-[#6E5D52] font-bold rounded-xl cursor-pointer"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="savingProfile"
              class="px-5 py-2 bg-[#3D251E] text-white font-bold rounded-xl transition cursor-pointer shadow-xs"
            >
              {{ savingProfile ? 'Saving...' : 'Save Profile' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- REQUEST SALARY CORRECTION MODAL                          -->
    <!-- ======================================================== -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs"
      @click.self="showModal = false"
    >
      <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4 border border-[#E9E0D4] shadow-2xl">
        <div class="flex items-center justify-between border-b border-[#F4EFEA] pb-3">
          <h3 class="font-serif font-black text-lg text-[#2B1810]">
            Request Salary / Wage Correction
          </h3>
          <button @click="showModal = false" class="text-[#7D6E64] hover:text-[#2B1810] text-xl font-bold cursor-pointer">✕</button>
        </div>

        <form @submit.prevent="submitRequest" class="space-y-4 text-xs">
          <div>
            <label class="block text-[11px] font-bold text-[#7D6E64] uppercase mb-1">Current Registered Wage</label>
            <div class="px-3.5 py-2.5 bg-[#FAF7F2] border border-[#E2D8CC] rounded-xl font-serif font-black text-sm text-[#2B1810]">
              ETB {{ Number(userProfile.salary || 0).toLocaleString() }}
            </div>
          </div>

          <div>
            <label class="block text-[11px] font-bold text-[#7D6E64] uppercase mb-1">Requested Salary (ETB)</label>
            <input
              v-model.number="form.requested_salary"
              type="number"
              step="0.01"
              min="0"
              placeholder="e.g. 8500"
              required
              class="w-full px-3.5 py-2.5 bg-white border border-[#E2D8CC] rounded-xl font-bold text-[#2B1810] focus:ring-2 focus:ring-[#3D251E]/20"
            />
          </div>

          <div>
            <label class="block text-[11px] font-bold text-[#7D6E64] uppercase mb-1">Reason for Correction *</label>
            <textarea
              v-model="form.reason"
              rows="3"
              placeholder="Explain adjustment, promotion, or discrepancies..."
              required
              class="w-full px-3.5 py-2.5 bg-white border border-[#E2D8CC] rounded-xl font-medium text-[#2B1810] focus:ring-2 focus:ring-[#3D251E]/20"
            ></textarea>
          </div>

          <div class="flex items-center justify-end space-x-2 pt-2">
            <button
              type="button"
              @click="showModal = false"
              class="px-4 py-2 bg-[#FAF7F2] text-[#6E5D52] font-bold rounded-xl cursor-pointer"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="submitting"
              class="px-5 py-2 bg-[#3D251E] text-white font-bold rounded-xl transition cursor-pointer shadow-xs"
            >
              {{ submitting ? 'Submitting...' : 'Submit to Super Admin' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useAuthStore } from '../../stores/auth';
import api from '../../api';

const authStore = useAuthStore();

const sellerTab = ref('profile');
const forceManagerMode = ref(null);
const showModal = ref(false);
const showEditModal = ref(false);
const submitting = ref(false);
const savingProfile = ref(false);
const successMsg = ref('');
const managerActiveToggle = ref(true);

const userProfile = reactive({
  user_id: authStore.user?.user_id || null,
  full_name: authStore.user?.full_name || '',
  email: authStore.user?.email || '',
  phone: authStore.user?.phone || '',
  role: authStore.user?.role || 'Branch Seller / Cashier',
  salary: authStore.user?.salary || 0,
  employee_id: authStore.user?.employee_id || '',
  hire_date: authStore.user?.hire_date || authStore.user?.created_at || '',
  status: authStore.user?.status || 'Active',
  branch: authStore.user?.branch || null,
  avatar: null,
});

const editForm = reactive({
  full_name: authStore.user?.full_name || '',
  email: authStore.user?.email || '',
  phone: authStore.user?.phone || '',
});

const managerStats = reactive({
  totalStaff: 0,
  totalBranches: 0,
  totalManagers: 0,
  activeStaff: 0,
});

const recentActivities = ref([]);

function formatActivityTime(d) {
  if (!d) return 'Just now';
  const diff = Date.now() - new Date(d).getTime();
  const mins = Math.floor(diff / 60000);
  if (mins < 1) return 'Just now';
  if (mins < 60) return `${mins}m ago`;
  const hours = Math.floor(mins / 60);
  if (hours < 24) return `${hours}h ago`;
  const days = Math.floor(hours / 24);
  return `${days}d ago`;
}

async function loadManagerStats() {
  try {
    const [usersRes, branchesRes] = await Promise.all([
      api.get('/admin/users').catch(() => null),
      api.get('/branches').catch(() => null),
    ]);
    if (usersRes?.data) {
      const uList = Array.isArray(usersRes.data) ? usersRes.data : (usersRes.data.data || []);
      managerStats.totalStaff = uList.length;
      managerStats.totalManagers = uList.filter(u => ['Main Branch Manager', 'Branch Manager', 'Manager', 'Branch Admin'].includes(u.role)).length;
      managerStats.activeStaff = uList.filter(u => u.status === 'Active').length;
    }
    if (branchesRes?.data) {
      const bList = Array.isArray(branchesRes.data) ? branchesRes.data : (branchesRes.data.data || []);
      managerStats.totalBranches = bList.length;
    }
  } catch (e) {
    console.error('Failed to load manager stats:', e);
  }
}

async function loadRecentActivities() {
  try {
    const res = await api.get('/admin/audit-logs', { params: { limit: 5 } }).catch(() => null);
    if (res?.data) {
      const logs = res.data.logs || (Array.isArray(res.data) ? res.data : []);
      recentActivities.value = logs.slice(0, 5);
    }
  } catch (e) {
    recentActivities.value = [];
  }
}

const form = reactive({
  requested_salary: null,
  reason: '',
});

const currentRole = computed(() => authStore.user?.role || userProfile.role || 'Super Admin');

const displayRole = computed(() => {
  if (currentRole.value === 'Branch Admin') return 'Branch Manager';
  return currentRole.value;
});

const isManager = computed(() => {
  return ['Main Branch Manager', 'Branch Manager', 'Branch Admin'].includes(currentRole.value);
});

const isSuperAdmin = computed(() => currentRole.value === 'Super Admin');

// Display mode: adapts to Seller (Image 1) or Manager (Image 3)
const currentDisplayMode = computed(() => {
  if (forceManagerMode.value !== null) {
    return forceManagerMode.value ? 'manager' : 'seller';
  }
  return isManager.value || isSuperAdmin.value ? 'manager' : 'seller';
});

const userInitials = computed(() => {
  const name = userProfile.full_name || authStore.user?.full_name;
  if (!name) return 'SB';
  const parts = name.trim().split(' ');
  return parts.length > 1
    ? (parts[0][0] + parts[1][0]).toUpperCase()
    : parts[0].substring(0, 2).toUpperCase();
});

const formattedJoinDate = computed(() => {
  const d = userProfile.hire_date || userProfile.created_at || authStore.user?.created_at;
  if (!d) return 'Nov 2026';
  try {
    return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  } catch {
    return 'Nov 2026';
  }
});

async function loadProfile() {
  try {
    const res = await api.get('/auth/user');
    if (res.data) {
      Object.assign(userProfile, res.data);
      resetEditForm();
    }
  } catch (e) {
    console.error('Failed to load user profile:', e);
  }
}

function resetEditForm() {
  editForm.full_name = userProfile.full_name || authStore.user?.full_name || '';
  editForm.email = userProfile.email || authStore.user?.email || '';
  editForm.phone = userProfile.phone || authStore.user?.phone || '';
}

function openEditModal() {
  resetEditForm();
  showEditModal.value = true;
}

async function updateProfile() {
  savingProfile.value = true;
  try {
    const res = await api.patch('/auth/profile', editForm);
    if (res.data) {
      userProfile.full_name = editForm.full_name;
      userProfile.email = editForm.email;
      userProfile.phone = editForm.phone;
      if (authStore.user) {
        authStore.user.full_name = editForm.full_name;
        authStore.user.email = editForm.email;
      }
      successMsg.value = 'Profile updated successfully!';
    }
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to update profile.');
  } finally {
    savingProfile.value = false;
  }
}

async function updateProfileModal() {
  await updateProfile();
  showEditModal.value = false;
}

function openRequestModal() {
  form.requested_salary = userProfile.salary || null;
  form.reason = '';
  showModal.value = true;
}

async function submitRequest() {
  submitting.value = true;
  try {
    const res = await api.post('/admin/salary-correction-requests', form);
    if (res.data) {
      successMsg.value = 'Salary correction request submitted to Super Admin.';
      showModal.value = false;
    }
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to submit request.');
  } finally {
    submitting.value = false;
  }
}

onMounted(async () => {
  await loadProfile();
  await Promise.all([
    loadManagerStats(),
    loadRecentActivities(),
  ]);
});
</script>
