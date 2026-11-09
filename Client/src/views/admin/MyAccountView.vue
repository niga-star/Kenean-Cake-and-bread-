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

        <button
          @click="openRequestModal"
          class="self-start sm:self-auto px-4 py-2.5 bg-[#3D251E] hover:bg-[#2B1810] text-white text-xs sm:text-sm font-bold rounded-xl transition cursor-pointer flex items-center space-x-2 shadow-xs"
        >
          <span>✏️</span>
          <span>Request Correction</span>
        </button>
      </div>

      <!-- Cupcakes Banner Artwork on the Right -->
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
      <button @click="successMsg = ''" class="text-emerald-800 font-bold ml-2">&times;</button>
    </div>

    <!-- ======================================================== -->
    <!-- 2. TOP PROFILE SUMMARY CARD                              -->
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
                {{ userProfile.full_name }}
              </h2>
              <span
                class="px-2.5 py-0.5 rounded-full text-xs font-bold border shrink-0"
                :class="isManager ? 'bg-[#FEF7E6] text-[#B87A14] border-[#FBEAC2]' : 'bg-[#FFE4E6] text-[#9F1239] border-[#FECDD3]'"
              >
                {{ displayRole }}
              </span>
            </div>

            <div class="flex flex-wrap items-center gap-2 text-xs text-[#7D6E64] font-medium">
              <span class="flex items-center space-x-1">
                <span>✉️</span>
                <span>{{ userProfile.email }}</span>
              </span>
              <span class="text-[#D8CDC5] hidden sm:inline">|</span>
              <span class="flex items-center space-x-1">
                <span>📞</span>
                <span>{{ userProfile.phone || '+251 91 123 4567' }}</span>
              </span>
            </div>

            <div class="flex flex-wrap items-center gap-2 pt-1">
              <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center space-x-1">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 inline-block"></span>
                <span>{{ userProfile.status || 'Active' }}</span>
              </span>

              <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FAF7F2] text-[#6E5D52] border border-[#E5DACD] flex items-center space-x-1">
                <span>📍</span>
                <span>{{ userProfile.branch?.branch_name || 'All Branches (HQ)' }}</span>
              </span>
            </div>
          </div>
        </div>

        <!-- Right: Wage / Employee Record Highlight Badge -->
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
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- 3. TOP 4 STAT CARDS (FOR BRANCH MANAGERS & ADMINS)        -->
    <!-- ======================================================== -->
    <div v-if="isManager || isSuperAdmin" class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 text-xs">
      <div class="bg-white border border-[#E9E0D4] rounded-2xl p-4 shadow-2xs flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 rounded-xl bg-[#FFF5F2] text-rose-700 flex items-center justify-center text-lg font-bold">👥</div>
          <div>
            <div class="text-xl sm:text-2xl font-serif font-black text-[#2B1810]">{{ branchStats.totalStaff }}</div>
            <div class="text-[11px] text-[#7D6E64] font-medium">Total Staff</div>
          </div>
        </div>
        <span class="text-[11px] font-bold text-emerald-700">↑ +12%</span>
      </div>

      <div class="bg-white border border-[#E9E0D4] rounded-2xl p-4 shadow-2xs flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 rounded-xl bg-[#FEF7E6] text-amber-700 flex items-center justify-center text-lg font-bold">🏬</div>
          <div>
            <div class="text-xl sm:text-2xl font-serif font-black text-[#2B1810]">{{ isSuperAdmin ? '6' : '1' }}</div>
            <div class="text-[11px] text-[#7D6E64] font-medium">Branch</div>
          </div>
        </div>
        <span class="text-[11px] font-bold text-[#7D6E64]">↑ +0%</span>
      </div>

      <div class="bg-white border border-[#E9E0D4] rounded-2xl p-4 shadow-2xs flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 rounded-xl bg-[#F5F3FF] text-purple-700 flex items-center justify-center text-lg font-bold">🛡️</div>
          <div>
            <div class="text-xl sm:text-2xl font-serif font-black text-[#2B1810]">3</div>
            <div class="text-[11px] text-[#7D6E64] font-medium">Managers</div>
          </div>
        </div>
        <span class="text-[11px] font-bold text-[#7D6E64]">↑ +0%</span>
      </div>

      <div class="bg-white border border-[#E9E0D4] rounded-2xl p-4 shadow-2xs flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 rounded-xl bg-[#F0FDF4] text-emerald-700 flex items-center justify-center text-lg font-bold">👤</div>
          <div>
            <div class="text-xl sm:text-2xl font-serif font-black text-[#2B1810]">{{ branchStats.activeStaff }}</div>
            <div class="text-[11px] text-[#7D6E64] font-medium">Active Staff</div>
          </div>
        </div>
        <span class="text-[11px] font-bold text-emerald-700">↑ +5%</span>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- 4. SEGMENTED TABS                                        -->
    <!-- ======================================================== -->
    <div class="flex flex-wrap items-center gap-2">
      <button
        @click="activeTab = 'details'"
        class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition cursor-pointer flex items-center space-x-1.5 shadow-2xs"
        :class="activeTab === 'details' ? 'bg-[#3D251E] text-white shadow-xs' : 'bg-[#F4ECE3] text-[#6E5D52] hover:bg-[#EAE0D5]'"
      >
        <span>👤</span>
        <span>Profile Details</span>
      </button>

      <button
        @click="activeTab = 'edit'"
        class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition cursor-pointer flex items-center space-x-1.5 shadow-2xs"
        :class="activeTab === 'edit' ? 'bg-[#3D251E] text-white shadow-xs' : 'bg-[#F4ECE3] text-[#6E5D52] hover:bg-[#EAE0D5]'"
      >
        <span>💼</span>
        <span>Edit Personal Info</span>
      </button>

      <button
        @click="activeTab = 'permissions'"
        class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition cursor-pointer flex items-center space-x-1.5 shadow-2xs"
        :class="activeTab === 'permissions' ? 'bg-[#3D251E] text-white shadow-xs' : 'bg-[#F4ECE3] text-[#6E5D52] hover:bg-[#EAE0D5]'"
      >
        <span>🛡️</span>
        <span>Role &amp; Permissions</span>
      </button>

      <button
        @click="activeTab = 'requests'"
        class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition cursor-pointer flex items-center space-x-1.5 shadow-2xs"
        :class="activeTab === 'requests' ? 'bg-[#3D251E] text-white shadow-xs' : 'bg-[#F4ECE3] text-[#6E5D52] hover:bg-[#EAE0D5]'"
      >
        <span>📝</span>
        <span>Salary Requests ({{ requests.length }})</span>
      </button>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 1: PROFILE DETAILS                                   -->
    <!-- ======================================================== -->
    <div v-show="activeTab === 'details'" class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
      <!-- Left: Staff Information -->
      <div class="bg-white border border-[#E9E0D4] rounded-3xl p-6 sm:p-7 shadow-xs space-y-5">
        <div class="flex items-center justify-between border-b border-[#F4EFEA] pb-3.5">
          <h3 class="font-serif font-black text-lg text-[#2B1810] flex items-center space-x-2">
            <span>💼</span>
            <span>Staff Information</span>
          </h3>
          <button
            @click="activeTab = 'edit'"
            class="text-xs font-bold text-[#3D251E] hover:underline flex items-center space-x-1 cursor-pointer"
          >
            <span>✏️</span>
            <span>Edit Profile</span>
          </button>
        </div>

        <div class="space-y-3.5 text-xs">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between py-1 border-b border-[#F6F0E8] gap-1">
            <span class="text-[#7D6E64] font-medium">Full Name</span>
            <span class="font-bold text-[#2B1810] text-sm">{{ userProfile.full_name }}</span>
          </div>

          <div class="flex flex-col sm:flex-row sm:items-center justify-between py-1 border-b border-[#F6F0E8] gap-1">
            <span class="text-[#7D6E64] font-medium">Email Address</span>
            <span class="font-semibold text-[#2B1810]">{{ userProfile.email }}</span>
          </div>

          <div class="flex flex-col sm:flex-row sm:items-center justify-between py-1 border-b border-[#F6F0E8] gap-1">
            <span class="text-[#7D6E64] font-medium">Phone Number</span>
            <span class="font-mono font-semibold text-[#2B1810]">{{ userProfile.phone || '+251 91 123 4567' }}</span>
          </div>

          <div class="flex flex-col sm:flex-row sm:items-center justify-between py-1 border-b border-[#F6F0E8] gap-1">
            <span class="text-[#7D6E64] font-medium">Branch</span>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#FAF7F2] text-[#6E5D52] border border-[#E5DACD]">
              📍 {{ userProfile.branch?.branch_name || 'Main Branch' }}
            </span>
          </div>

          <div class="flex flex-col sm:flex-row sm:items-center justify-between py-1 border-b border-[#F6F0E8] gap-1">
            <span class="text-[#7D6E64] font-medium">Role</span>
            <span class="font-bold text-[#3D251E]">{{ displayRole }}</span>
          </div>

          <div class="flex flex-col sm:flex-row sm:items-center justify-between py-1 border-b border-[#F6F0E8] gap-1">
            <span class="text-[#7D6E64] font-medium">Employment Status</span>
            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
              🟢 {{ userProfile.employment_status || 'Active' }}
            </span>
          </div>

          <div class="flex flex-col sm:flex-row sm:items-center justify-between py-1 gap-1">
            <span class="text-[#7D6E64] font-medium">Hire Date</span>
            <span class="font-semibold text-[#2B1810] flex items-center space-x-1">
              <span>📅</span>
              <span>{{ userProfile.hire_date || '2026-11-08' }}</span>
            </span>
          </div>
        </div>
      </div>

      <!-- Right: Role Scope & Permissions with Cupcake Graphic -->
      <div class="bg-white border border-[#E9E0D4] rounded-3xl p-6 sm:p-7 shadow-xs space-y-5 relative overflow-hidden">
        <h3 class="font-serif font-black text-lg text-[#2B1810] flex items-center space-x-2 border-b border-[#F4EFEA] pb-3.5">
          <span>🛡️</span>
          <span>Role Scope &amp; Permissions</span>
        </h3>

        <!-- Pink Branch Access Inner Card -->
        <div class="p-4 bg-[#FFF0F2] border border-[#FCD5DC] rounded-2xl flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-white/90 text-rose-800 flex items-center justify-center text-lg font-bold shadow-2xs">
              🏬
            </div>
            <div>
              <div class="text-[11px] font-bold text-[#8C4A5A] uppercase">Branch Access</div>
              <div class="font-bold text-sm text-[#2B1810]">{{ userProfile.branch?.branch_name || 'Main Branch' }}</div>
              <div class="text-[11px] text-[#7D6E64]">You have access to this branch only.</div>
            </div>
          </div>
          <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
            ✓ Branch Access
          </span>
        </div>

        <!-- Permissions Checkmark List -->
        <div class="space-y-3 pt-1">
          <div class="text-xs font-bold uppercase tracking-wider text-[#7D6E64]">Your Permissions</div>
          <ul class="space-y-2.5 text-xs text-[#2B1810] font-medium">
            <li class="flex items-center space-x-2">
              <span class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-black flex items-center justify-center">✓</span>
              <span>Manage your daily orders and custom cake sales</span>
            </li>
            <li class="flex items-center space-x-2">
              <span class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-black flex items-center justify-center">✓</span>
              <span>View your staff information</span>
            </li>
            <li class="flex items-center space-x-2">
              <span class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-black flex items-center justify-center">✓</span>
              <span>Submit daily sales reports</span>
            </li>
            <li class="flex items-center space-x-2">
              <span class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-black flex items-center justify-center">✓</span>
              <span>Access your account settings</span>
            </li>
          </ul>
        </div>

        <!-- Cupcake Accent in Bottom-Right Corner -->
        <div class="absolute -bottom-2 -right-2 w-28 h-28 pointer-events-none opacity-85 select-none overflow-hidden">
          <img
            src="/mockup/cupcake_corner_accent.jpg"
            alt="Cupcake"
            class="w-full h-full object-contain"
            onerror="this.style.display='none'"
          />
        </div>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 2: EDIT PERSONAL INFORMATION                         -->
    <!-- ======================================================== -->
    <div v-show="activeTab === 'edit'" class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
      <!-- Edit Form (2 Cols) -->
      <div class="lg:col-span-2 bg-white border border-[#E9E0D4] rounded-3xl p-6 sm:p-7 shadow-xs space-y-5">
        <h3 class="font-serif font-black text-lg text-[#2B1810] flex items-center space-x-2 border-b border-[#F4EFEA] pb-3.5">
          <span>👤</span>
          <span>Personal Information</span>
        </h3>

        <form @submit.prevent="updateProfile" class="space-y-4 text-xs">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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

            <div>
              <label class="block text-[11px] font-bold text-[#7D6E64] uppercase mb-1">Phone Number *</label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-[#7D6E64]">📞</span>
                <input
                  v-model="editForm.phone"
                  type="text"
                  placeholder="+251 91 123 4567"
                  class="w-full pl-9 pr-3.5 py-2.5 bg-white border border-[#E2D8CC] rounded-xl font-semibold text-[#2B1810] focus:ring-2 focus:ring-[#3D251E]/20 focus:border-[#3D251E]"
                />
              </div>
            </div>

            <div>
              <label class="block text-[11px] font-bold text-[#7D6E64] uppercase mb-1">Role</label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-[#7D6E64]">🛡️</span>
                <input
                  :value="displayRole"
                  disabled
                  class="w-full pl-9 pr-3.5 py-2.5 bg-[#FAF7F2] border border-[#E2D8CC] rounded-xl font-bold text-[#2B1810] cursor-not-allowed"
                />
              </div>
            </div>

            <div>
              <label class="block text-[11px] font-bold text-[#7D6E64] uppercase mb-1">Branch</label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-[#7D6E64]">🏬</span>
                <input
                  :value="userProfile.branch?.branch_name || 'Main Branch'"
                  disabled
                  class="w-full pl-9 pr-3.5 py-2.5 bg-[#FAF7F2] border border-[#E2D8CC] rounded-xl font-bold text-[#2B1810] cursor-not-allowed"
                />
              </div>
            </div>

            <div>
              <label class="block text-[11px] font-bold text-[#7D6E64] uppercase mb-1">Status</label>
              <div class="flex items-center space-x-2 pt-2">
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                  🟢 Active Account
                </span>
              </div>
            </div>
          </div>

          <div class="flex items-center justify-end space-x-3 pt-4 border-t border-[#F4EFEA]">
            <button
              type="button"
              @click="resetEditForm"
              class="px-4 py-2.5 bg-[#FAF7F2] hover:bg-[#F0E6D8] border border-[#E2D6C6] text-xs font-bold text-[#3D251E] rounded-xl transition cursor-pointer"
            >
              🔄 Reset
            </button>
            <button
              type="submit"
              :disabled="savingProfile"
              class="px-5 py-2.5 bg-[#3D251E] hover:bg-[#2B1810] text-white text-xs font-bold rounded-xl transition cursor-pointer shadow-xs flex items-center space-x-1.5"
            >
              <span>💾</span>
              <span>{{ savingProfile ? 'Saving...' : 'Update Profile' }}</span>
            </button>
          </div>
        </form>
      </div>

      <!-- Right Column: Recent Activity Timeline -->
      <div class="bg-white border border-[#E9E0D4] rounded-3xl p-6 shadow-xs space-y-4">
        <div class="flex items-center justify-between border-b border-[#F4EFEA] pb-3">
          <h3 class="font-serif font-black text-sm uppercase tracking-wider text-[#2B1810] flex items-center space-x-1.5">
            <span>🕒</span>
            <span>Recent Activity</span>
          </h3>
          <span class="text-[11px] text-[#7D6E64]">Live</span>
        </div>

        <div class="space-y-3 text-xs">
          <div class="flex items-start space-x-3">
            <span class="w-2 h-2 rounded-full bg-emerald-500 mt-1.5 shrink-0"></span>
            <div>
              <div class="font-bold text-[#2B1810]">Report Approved</div>
              <div class="text-[11px] text-[#7D6E64]">Daily sales report for today verified by Manager</div>
              <div class="text-[10px] text-[#A6978B] mt-0.5">2 hours ago</div>
            </div>
          </div>

          <div class="flex items-start space-x-3">
            <span class="w-2 h-2 rounded-full bg-blue-500 mt-1.5 shrink-0"></span>
            <div>
              <div class="font-bold text-[#2B1810]">Profile Access</div>
              <div class="text-[11px] text-[#7D6E64]">Logged into portal successfully</div>
              <div class="text-[10px] text-[#A6978B] mt-0.5">5 hours ago</div>
            </div>
          </div>

          <div class="flex items-start space-x-3">
            <span class="w-2 h-2 rounded-full bg-amber-500 mt-1.5 shrink-0"></span>
            <div>
              <div class="font-bold text-[#2B1810]">Shift Started</div>
              <div class="text-[11px] text-[#7D6E64]">Signed into POS &amp; Order register</div>
              <div class="text-[10px] text-[#A6978B] mt-0.5">8 hours ago</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 3: SALARY CORRECTION REQUESTS HISTORY                -->
    <!-- ======================================================== -->
    <div v-show="activeTab === 'requests'" class="bg-white border border-[#E9E0D4] rounded-3xl p-6 sm:p-7 shadow-xs space-y-4">
      <div class="flex items-center justify-between border-b border-[#F4EFEA] pb-3">
        <h3 class="font-serif font-black text-lg text-[#2B1810]">
          My Salary Correction Requests to Super Admin
        </h3>
        <button
          @click="openRequestModal"
          class="px-3.5 py-1.5 bg-[#3D251E] text-white text-xs font-bold rounded-xl cursor-pointer"
        >
          + New Request
        </button>
      </div>

      <div class="overflow-x-auto border border-[#EFE8DF] rounded-2xl">
        <table class="w-full text-left text-xs">
          <thead class="bg-[#FAF6F0] text-[#7D6E64] uppercase font-bold text-[10px]">
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
            <tr v-if="requests.length === 0">
              <td colspan="6" class="py-8 text-center text-[#9E8E82] text-xs">
                No salary correction requests submitted yet.
              </td>
            </tr>
            <tr v-for="r in requests" :key="r.request_id" class="hover:bg-[#FAF7F2]/60">
              <td class="py-3 px-4 font-mono font-semibold">{{ new Date(r.created_at).toLocaleDateString() }}</td>
              <td class="py-3 px-4 font-bold text-[#2B1810]">ETB {{ Number(r.current_salary).toLocaleString() }}</td>
              <td class="py-3 px-4 font-bold text-amber-800">
                {{ r.requested_salary ? `ETB ${Number(r.requested_salary).toLocaleString()}` : '—' }}
              </td>
              <td class="py-3 px-4 max-w-xs truncate" :title="r.reason">{{ r.reason }}</td>
              <td class="py-3 px-4 text-center">
                <span
                  class="px-2.5 py-1 rounded-full text-[11px] font-bold inline-block"
                  :class="{
                    'bg-amber-100 text-amber-800': r.status === 'Pending',
                    'bg-emerald-100 text-emerald-800': r.status === 'Approved',
                    'bg-rose-100 text-rose-800': r.status === 'Rejected'
                  }"
                >
                  {{ r.status }}
                </span>
              </td>
              <td class="py-3 px-4 text-[#7D6E64] italic">{{ r.admin_remarks || '—' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- REQUEST CORRECTION MODAL -->
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

const activeTab = ref('details');
const showModal = ref(false);
const submitting = ref(false);
const savingProfile = ref(false);
const successMsg = ref('');

const userProfile = reactive({
  full_name: '',
  full_name_am: '',
  email: '',
  phone: '',
  role: '',
  salary: 0,
  employment_status: 'Active',
  status: 'Active',
  hire_date: '',
  branch: null,
  avatar: null,
});

const editForm = reactive({
  full_name: '',
  email: '',
  phone: '',
});

const branchStats = reactive({
  totalStaff: 24,
  activeStaff: 21,
});

const requests = ref([]);
const form = reactive({
  requested_salary: null,
  reason: '',
});

const userInitials = computed(() => {
  if (!userProfile.full_name) return 'SB';
  const parts = userProfile.full_name.trim().split(' ');
  return parts.length > 1
    ? (parts[0][0] + parts[1][0]).toUpperCase()
    : parts[0].substring(0, 2).toUpperCase();
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
  editForm.full_name = userProfile.full_name || '';
  editForm.email = userProfile.email || '';
  editForm.phone = userProfile.phone || '';
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
      activeTab.value = 'details';
    }
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to update profile.');
  } finally {
    savingProfile.value = false;
  }
}

async function loadRequests() {
  try {
    const res = await api.get('/admin/my-salary-requests');
    if (res.data && Array.isArray(res.data)) {
      requests.value = res.data;
    }
  } catch (e) {
    // optional endpoint
  }
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
      await loadRequests();
    }
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to submit request.');
  } finally {
    submitting.value = false;
  }
}

onMounted(async () => {
  await loadProfile();
  await loadRequests();
});
</script>
