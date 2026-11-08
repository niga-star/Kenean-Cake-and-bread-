<template>
  <div class="space-y-6 max-w-7xl mx-auto font-poppins relative">
    <!-- Decorative Background Botanical Wheat Accent (Top Right) -->
    <div class="absolute -top-3 right-0 pointer-events-none opacity-40 select-none overflow-hidden pr-2 hidden md:block">
      <svg class="w-40 h-24 text-[#D9A371]" viewBox="0 0 160 100" fill="currentColor">
        <path d="M10,85 Q60,65 140,20" stroke="#C88E57" stroke-width="1.8" fill="none" opacity="0.6"/>
        <ellipse cx="140" cy="20" rx="8" ry="4" transform="rotate(-30 140 20)" fill="#D9A371"/>
        <ellipse cx="126" cy="28" rx="8" ry="4" transform="rotate(-40 126 28)" fill="#D9A371"/>
        <ellipse cx="120" cy="37" rx="8" ry="4" transform="rotate(-15 120 37)" fill="#D9A371"/>
        <ellipse cx="106" cy="42" rx="7.5" ry="3.8" transform="rotate(-45 106 42)" fill="#D9A371"/>
        <ellipse cx="100" cy="51" rx="7.5" ry="3.8" transform="rotate(-20 100 51)" fill="#D9A371"/>
        <ellipse cx="86" cy="56" rx="7" ry="3.5" transform="rotate(-45 86 56)" fill="#D9A371"/>
        <ellipse cx="80" cy="65" rx="7" ry="3.5" transform="rotate(-25 80 65)" fill="#D9A371"/>
        <ellipse cx="66" cy="70" rx="6.5" ry="3" transform="rotate(-45 66 70)" fill="#D9A371"/>
      </svg>
    </div>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 relative z-10">
      <div class="flex items-center space-x-3.5">
        <div class="w-12 h-12 rounded-2xl bg-[#FAF0E6] text-[#BA5420] flex items-center justify-center shrink-0 border border-[#F0DFD0] shadow-xs">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
        </div>
        <div>
          <h2 class="text-xl sm:text-2xl font-serif font-bold text-[#2A1D15]">Branch Management</h2>
          <p class="text-xs text-[#7A6B60] mt-0.5 font-poppins">Manage your bakery branches efficiently</p>
        </div>
      </div>
      <button
        @click="openAddBranchModal"
        class="w-full sm:w-auto px-4 py-2 text-xs sm:text-sm font-semibold bg-[#BA5420] hover:bg-[#9E4314] text-white rounded-lg transition shadow-sm flex items-center justify-center space-x-1.5 cursor-pointer font-poppins"
      >
        <svg class="w-4 h-4 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
        </svg>
        <span>Add Branch</span>
      </button>
    </div>

    <!-- Alert / Message -->
    <div v-if="successMsg" class="p-3 bg-[#EBF7EB] border border-[#C5E9C6] rounded-lg text-xs text-[#1E7422] flex items-center justify-between">
      <span>{{ successMsg }}</span>
      <button @click="successMsg = ''" class="text-[#1E7422] font-bold ml-2">&times;</button>
    </div>
    <div v-if="errorMsg" class="p-3 bg-[#FEECEC] border border-[#F5C2C2] rounded-lg text-xs text-[#B91C1C] flex items-center justify-between">
      <span>{{ errorMsg }}</span>
      <button @click="errorMsg = ''" class="text-[#B91C1C] font-bold ml-2">&times;</button>
    </div>

    <!-- Branches List Table -->
    <div class="bg-white border border-[#E9E0D4] rounded-xl shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full min-w-[580px] text-left text-sm">
          <thead class="bg-[#FAF6F0] text-[11px] font-bold text-[#827165] tracking-wider uppercase border-b border-[#EFE8DF]">
            <tr>
              <th class="py-3 px-5">Code</th>
              <th class="py-3 px-4">Branch</th>
              <th class="py-3 px-4">Type</th>
              <th class="py-3 px-4">Manager</th>
              <th class="py-3 px-4">City</th>
              <th class="py-3 px-4">Phone</th>
              <th class="py-3 px-4 text-center">Status</th>
              <th class="py-3 px-4 text-center">Shopping Access</th>
              <th class="py-3 px-5 text-right">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#F3EDE4]">
            <tr v-if="!branches || branches.length === 0">
              <td colspan="9" class="py-12 text-center text-sm text-[#7A6B60]">
                No branches registered yet. Click <button @click="openAddBranchModal" class="font-bold text-[#BA5420] underline cursor-pointer">+ Add Branch</button> to add your first bakery branch.
              </td>
            </tr>
            <tr
              v-for="b in branches"
              :key="b.branch_id"
              class="hover:bg-[#FCFAF7] transition cursor-pointer"
              :class="editingBranch?.branch_id === b.branch_id ? 'bg-[#FAF3EC]' : ''"
              @click="selectBranchForEdit(b)"
            >
              <td class="py-3.5 px-5 font-mono text-xs font-semibold text-[#55463C]">{{ b.branch_code }}</td>
              <td class="py-3.5 px-4 font-bold text-[#2A1D15]">{{ b.branch_name }}</td>
              <td class="py-3.5 px-4">
                <span
                  class="px-2 py-0.5 text-xs font-semibold rounded-full"
                  :class="b.branch_type === 'Main' ? 'bg-[#FFF3E0] text-[#E65100]' : 'bg-[#EAE2D8] text-[#55463C]'"
                >
                  {{ b.branch_type }}
                </span>
              </td>
              <td class="py-3.5 px-4 text-xs text-[#5A4B41]">{{ b.manager?.full_name || 'Assigned' }}</td>
              <td class="py-3.5 px-4 text-xs text-[#5A4B41]">{{ b.city }}</td>
              <td class="py-3.5 px-4 text-xs text-[#6A5A50] font-mono">{{ b.phone }}</td>
              <td class="py-3.5 px-4 text-center">
                <span
                  class="inline-block px-2.5 py-0.5 text-xs font-semibold rounded-full border"
                  :class="b.status === 'Active' ? 'bg-[#EAF5EA] text-[#2E7D32] border-[#C8E6C9]' : 'bg-[#F3EFE9] text-[#7A6B60] border-[#DDD3C7]'"
                >
                  {{ b.status }}
                </span>
              </td>
              <td class="py-3.5 px-4 text-center" @click.stop>
                <div class="inline-flex items-center gap-1.5">
                  <span
                    class="inline-flex items-center px-2 py-0.5 text-[11px] font-bold rounded-full border"
                    :class="b.is_shopping_enabled !== false ? 'bg-[#EAF5EA] text-[#2E7D32] border-[#C8E6C9]' : 'bg-[#FAF0F0] text-[#B84B42] border-[#E8C4C4]'"
                  >
                    <span class="w-1.5 h-1.5 rounded-full mr-1" :class="b.is_shopping_enabled !== false ? 'bg-[#2E7D32]' : 'bg-[#B84B42]'"></span>
                    {{ b.is_shopping_enabled !== false ? 'Enabled' : 'Disabled' }}
                  </span>
                  <button
                    @click="toggleShopping(b)"
                    :disabled="togglingBranchId === b.branch_id"
                    class="px-2.5 py-1 text-[11px] font-bold rounded-lg border transition shadow-2xs cursor-pointer disabled:opacity-50"
                    :class="b.is_shopping_enabled !== false
                      ? 'border-rose-300 text-rose-700 bg-rose-50 hover:bg-rose-100 hover:border-rose-400'
                      : 'border-emerald-400 text-white bg-[#2E7D32] hover:bg-[#256628] shadow-xs'"
                    :title="b.is_shopping_enabled !== false ? 'Click to disable customer shopping for this branch' : 'Click to enable customer shopping for this branch'"
                  >
                    <span v-if="togglingBranchId === b.branch_id">...</span>
                    <span v-else>{{ b.is_shopping_enabled !== false ? 'Disable' : 'Enable' }}</span>
                  </button>
                </div>
              </td>
              <td class="py-3 px-5 text-right" @click.stop>
                <div class="inline-flex items-center space-x-1.5 justify-end">
                  <button
                    @click="selectBranchForEdit(b)"
                    class="inline-flex items-center space-x-1 px-3 py-1 text-xs font-semibold bg-white border border-[#D8CDC0] hover:bg-[#F4ECE3] text-[#3E3027] rounded-md shadow-2xs transition cursor-pointer"
                  >
                    <svg class="w-3 h-3 text-[#7A6B60]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                    <span>Edit</span>
                  </button>
                  <button
                    type="button"
                    class="p-1 text-[#7A6B60] hover:text-[#2A1D15] hover:bg-[#F4ECE3] rounded transition cursor-pointer"
                    title="More actions"
                    @click="selectBranchForEdit(b)"
                  >
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Bottom Two Columns: Branch Details Editor + Delivery Zones -->
    <div v-if="editingBranch" id="branch-details-editor" class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start scroll-mt-6">
      <!-- Left: Branch Details (Col span 2) -->
      <div class="lg:col-span-2 bg-white border border-[#E9E0D4] rounded-2xl shadow-sm p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-[#EFE8DF] pb-3">
          <div class="flex items-center space-x-2">
            <span class="text-base">🏪</span>
            <h3 class="font-serif font-bold text-base sm:text-lg text-[#2A1D15]">Branch Details</h3>
          </div>
          <span class="text-xs text-[#8A7B71]">Editing: <strong class="text-[#BA5420]">{{ editingBranch.branch_name }}</strong></span>
        </div>

        <form @submit.prevent="saveBranch" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Branch name</label>
              <input
                v-model="editForm.branch_name"
                type="text"
                class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-1 focus:ring-[#BA5420]"
                required
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Branch code</label>
              <input
                v-model="editForm.branch_code"
                type="text"
                readonly
                class="w-full bg-[#F7F2EA] border border-[#DCD0C2] rounded-lg px-3 py-2 text-xs font-mono text-[#5A4A3E]"
              />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Branch type</label>
              <input
                :value="editingBranch.branch_type === 'Main' ? 'Main branch (Central Kitchen)' : 'Regular branch'"
                type="text"
                readonly
                class="w-full bg-[#F7F2EA] border border-[#DCD0C2] rounded-lg px-3 py-2 text-xs text-[#5A4A3E]"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Assigned Branch Manager</label>
              <select
                v-model="editForm.manager_id"
                class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-1 focus:ring-[#BA5420] cursor-pointer"
              >
                <option :value="null">-- Select / Unassigned --</option>
                <option v-for="m in managersList" :key="m.user_id" :value="m.user_id">
                  {{ m.full_name }} ({{ m.role }})
                </option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Region</label>
              <input
                v-model="editForm.region"
                type="text"
                class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">City</label>
              <input
                v-model="editForm.city"
                type="text"
                class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
              />
            </div>
          </div>

          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Phone</label>
            <input
              v-model="editForm.phone"
              type="text"
              class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
            />
          </div>

          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Address</label>
            <input
              v-model="editForm.address"
              type="text"
              class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
            />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">GPS Latitude (Optional)</label>
              <input
                v-model.number="editForm.latitude"
                type="number"
                step="any"
                placeholder="e.g. 11.1292"
                class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">GPS Longitude (Optional)</label>
              <input
                v-model.number="editForm.longitude"
                type="number"
                step="any"
                placeholder="e.g. 39.6387"
                class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
              />
            </div>
          </div>

          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Opening hours</label>
            <input
              v-model="editForm.opening_hours"
              type="text"
              class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
            />
          </div>

          <!-- Staff & Manager Quotas (Super Admin Authority, Section 1) -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Staff Quota (Max Staff)</label>
              <input
                v-model.number="editForm.max_staff"
                type="number"
                min="1"
                class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Manager Quota (Max Managers)</label>
              <input
                v-model.number="editForm.max_managers"
                type="number"
                min="1"
                class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
              />
            </div>
          </div>

          <!-- Shopping Access Control Card -->
          <div class="p-4 bg-[#FAF7F2] border border-[#E8DDD0] rounded-xl space-y-2">
            <div class="flex items-center justify-between">
              <div>
                <h4 class="font-serif font-bold text-xs sm:text-sm text-[#2A1D15]">Branch Shopping Access Control</h4>
                <p class="text-[11px] text-[#7D6E64] font-poppins">Controls whether customers can view products, add items to cart, and place orders from this branch.</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input
                  type="checkbox"
                  v-model="editForm.isShoppingEnabled"
                  class="sr-only peer"
                />
                <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#2E7D32]"></div>
              </label>
            </div>
            <div class="text-[11px] flex items-center space-x-1.5" :class="editForm.isShoppingEnabled ? 'text-[#2E7D32] font-semibold' : 'text-[#B84B42] font-semibold'">
              <span>{{ editForm.isShoppingEnabled ? '● Shopping Enabled:' : '○ Shopping Disabled:' }}</span>
              <span>{{ editForm.isShoppingEnabled ? 'Customers can shop and place orders normally.' : 'Customers cannot shop or place orders. Message: "Sorry, this branch is currently unavailable for shopping."' }}</span>
            </div>
          </div>

          <!-- Active Toggle -->
          <div class="flex items-center justify-between pt-2">
            <label class="flex items-center space-x-2 cursor-pointer">
              <input
                type="checkbox"
                v-model="editForm.isActive"
                class="w-4 h-4 text-[#BA5420] rounded border-[#D8CDC0]"
              />
              <span class="text-xs font-semibold text-[#2D231D]">Branch is active</span>
            </label>

            <div class="flex space-x-3">
              <button
                type="button"
                @click="editingBranch = null"
                class="px-4 py-2 text-xs font-medium text-[#6A5A50] hover:bg-[#F3EFE9] rounded-lg transition"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="isSavingBranch"
                class="px-4 py-2 text-xs font-medium bg-[#BA5420] hover:bg-[#9E4314] text-white rounded-lg shadow-sm transition disabled:opacity-50"
              >
                {{ isSavingBranch ? 'Saving...' : 'Save branch' }}
              </button>
            </div>
          </div>

          <div class="p-3 bg-[#FCFAF7] border border-[#EFE8DF] rounded-lg text-xs text-[#8A7B71]">
            A branch with open orders or transfers in transit cannot be deactivated.
          </div>
        </form>
      </div>

      <!-- Right: Delivery Zones -->
      <div class="bg-white border border-[#E9E0D4] rounded-xl shadow-sm p-6 space-y-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-2.5">
            <div class="w-8 h-8 rounded-full bg-[#FAF0E6] text-[#BA5420] flex items-center justify-center shrink-0 border border-[#F0DFD0]">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </div>
            <div>
              <h3 class="font-serif font-bold text-base sm:text-lg text-[#2A1D15]">Delivery zones</h3>
              <p class="text-[11px] text-[#8C7D73] font-poppins">Delivery coverage for {{ editingBranch.branch_name }}</p>
            </div>
          </div>
          <button
            @click="isAddZoneModalOpen = true"
            class="text-xs font-semibold text-[#BA5420] hover:text-[#9E4314] px-2.5 py-1 bg-[#FAF3EC] border border-[#E0D2C2] rounded-lg transition font-poppins cursor-pointer"
          >
            + Add zone
          </button>
        </div>

        <div class="overflow-x-auto border border-[#EFE8DF] rounded-xl">
          <table class="w-full text-left text-xs">
            <thead class="bg-[#FAF6F0] text-[#88786B] uppercase border-b border-[#EFE8DF] font-semibold">
              <tr>
                <th class="py-2.5 px-3">Zone</th>
                <th class="py-2.5 px-3 text-center">Fee</th>
                <th class="py-2.5 px-3 text-center">Status</th>
                <th class="py-2.5 px-3 text-right">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#F3EDE4]">
              <tr v-for="z in editingBranch.delivery_zones" :key="z.zone_id" class="hover:bg-[#FCFAF7]">
                <td class="py-2.5 px-3 font-medium text-[#382C24]">{{ z.zone_name }}</td>
                <td class="py-2.5 px-3 text-center font-bold text-[#2A1D15]">ETB {{ Number(z.delivery_fee).toFixed(0) }}</td>
                <td class="py-2.5 px-3 text-center">
                  <span
                    class="px-2 py-0.5 rounded-full text-[10px] font-semibold"
                    :class="z.status === 'Active' ? 'bg-[#EAF5EA] text-[#2E7D32]' : 'bg-[#FFF3E0] text-[#E65100]'"
                  >
                    {{ z.status }}
                  </span>
                </td>
                <td class="py-2.5 px-3 text-right">
                  <button
                    @click="deleteDeliveryZone(z)"
                    class="p-1 text-[#E53935] hover:text-red-700 hover:bg-red-50 rounded transition font-bold cursor-pointer"
                    title="Remove zone"
                  >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </td>
              </tr>
              <tr v-if="!editingBranch.delivery_zones || editingBranch.delivery_zones.length === 0">
                <td colspan="4" class="py-4 text-center text-xs text-[#8A7B71]">No delivery zones defined yet.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <p class="text-[11px] text-[#8F7F74] pt-1">
          Zone delivery fees are added dynamically to customer orders based on selected neighborhood.
        </p>
      </div>
    </div>

    <!-- MODAL 1: Add New Branch Modal -->
    <div v-if="isAddBranchModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-3 sm:p-4">
      <div class="bg-white rounded-2xl shadow-2xl border border-[#E9E0D4] max-w-lg w-full p-4 sm:p-6 space-y-4 sm:space-y-5 animate-in fade-in duration-200 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-[#EFE8DF] pb-3">
          <div>
            <h3 class="text-lg font-serif font-bold text-[#2A1D15]">Register New Bakery Branch</h3>
            <p class="text-xs text-[#8A7B71]">Expand bakery operations with a new branch location</p>
          </div>
          <button @click="isAddBranchModalOpen = false" class="text-[#8A7B71] hover:text-[#2A1D15] text-xl font-bold p-1">
            &times;
          </button>
        </div>

        <form @submit.prevent="submitNewBranch" class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Branch Name *</label>
              <input
                v-model="newBranch.branch_name"
                type="text"
                placeholder="e.g. Kazanchis Branch"
                required
                class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Branch Code *</label>
              <input
                v-model="newBranch.branch_code"
                type="text"
                placeholder="e.g. KAZ-01"
                required
                class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs font-mono uppercase text-[#2D231D]"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Branch Type *</label>
              <select
                v-model="newBranch.branch_type"
                required
                class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
              >
                <option value="Regular">Regular Branch</option>
                <option value="Main">Main Branch (Central Kitchen)</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Phone *</label>
              <input
                v-model="newBranch.phone"
                type="text"
                placeholder="+251 11 555 1234"
                required
                class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">City *</label>
              <input
                v-model="newBranch.city"
                type="text"
                placeholder="Dessie"
                required
                class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Region *</label>
              <input
                v-model="newBranch.region"
                type="text"
                placeholder="Amhara"
                required
                class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
              />
            </div>
          </div>

          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Full Street Address *</label>
            <input
              v-model="newBranch.address"
              type="text"
              placeholder="e.g. Piazza, Near Arada"
              required
              class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
            />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">GPS Latitude (Optional)</label>
              <input
                v-model.number="newBranch.latitude"
                type="number"
                step="any"
                placeholder="e.g. 11.1292"
                class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">GPS Longitude (Optional)</label>
              <input
                v-model.number="newBranch.longitude"
                type="number"
                step="any"
                placeholder="e.g. 39.6387"
                class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
              />
            </div>
          </div>

          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Opening Hours</label>
            <input
              v-model="newBranch.opening_hours"
              type="text"
              placeholder="Mon–Sun 07:00–21:00"
              class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
            />
          </div>

          <div class="flex justify-end space-x-3 pt-3 border-t border-[#EFE8DF]">
            <button
              type="button"
              @click="isAddBranchModalOpen = false"
              class="px-4 py-2 text-xs font-medium text-[#6A5A50] hover:bg-[#F3EFE9] rounded-lg transition"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="isSubmittingBranch"
              class="px-5 py-2 text-xs font-semibold bg-[#BA5420] hover:bg-[#9E4314] text-white rounded-lg shadow-sm transition disabled:opacity-50"
            >
              {{ isSubmittingBranch ? 'Creating...' : 'Register Branch' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL 2: Add Delivery Zone Modal -->
    <div v-if="isAddZoneModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
      <div class="bg-white rounded-2xl shadow-2xl border border-[#E9E0D4] max-w-md w-full p-6 space-y-5 animate-in fade-in duration-200">
        <div class="flex items-center justify-between border-b border-[#EFE8DF] pb-3">
          <div>
            <h3 class="text-lg font-serif font-bold text-[#2A1D15]">Add Delivery Zone</h3>
            <p class="text-xs text-[#8A7B71]">Add zone to {{ editingBranch?.branch_name }}</p>
          </div>
          <button @click="isAddZoneModalOpen = false" class="text-[#8A7B71] hover:text-[#2A1D15] text-xl font-bold p-1">
            &times;
          </button>
        </div>

        <form @submit.prevent="submitNewDeliveryZone" class="space-y-4">
          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Zone Name *</label>
            <input
              v-model="newZone.zone_name"
              type="text"
              placeholder="e.g. Bole Medhanialem"
              required
              class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
            />
          </div>

          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Delivery Fee (ETB) *</label>
            <input
              v-model.number="newZone.delivery_fee"
              type="number"
              placeholder="75"
              min="0"
              required
              class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"
            />
          </div>

          <div class="flex justify-end space-x-3 pt-3 border-t border-[#EFE8DF]">
            <button
              type="button"
              @click="isAddZoneModalOpen = false"
              class="px-4 py-2 text-xs font-medium text-[#6A5A50] hover:bg-[#F3EFE9] rounded-lg transition"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="isSubmittingZone"
              class="px-5 py-2 text-xs font-semibold bg-[#BA5420] hover:bg-[#9E4314] text-white rounded-lg shadow-sm transition disabled:opacity-50"
            >
              {{ isSubmittingZone ? 'Adding...' : 'Save Zone' }}
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
const isSuperAdmin = computed(() => authStore.user?.role === 'Super Admin');

const branches = ref([]);
const editingBranch = ref(null);
const managersList = ref([]);
const successMsg = ref('');
const errorMsg = ref('');
const isSavingBranch = ref(false);

// Modals
const isAddBranchModalOpen = ref(false);
const isSubmittingBranch = ref(false);
const isAddZoneModalOpen = ref(false);
const isSubmittingZone = ref(false);

const newBranch = ref({
  branch_name: '',
  branch_code: '',
  branch_type: 'Regular',
  city: 'Dessie',
  region: 'Amhara',
  address: '',
  latitude: null,
  longitude: null,
  phone: '',
  opening_hours: 'Mon–Sun 07:00–21:00',
});

const newZone = ref({
  zone_name: '',
  delivery_fee: 50,
});

const togglingBranchId = ref(null);

const editForm = ref({
  branch_name: '',
  branch_code: '',
  branch_type: 'Regular',
  manager_id: null,
  region: '',
  city: '',
  phone: '',
  address: '',
  latitude: null,
  longitude: null,
  opening_hours: '',
  max_staff: 15,
  max_managers: 1,
  isActive: true,
  isShoppingEnabled: true,
});

const loadManagers = async () => {
  try {
    const res = await api.get('/admin/users');
    const allUsers = Array.isArray(res.data) ? res.data : [];
    managersList.value = allUsers.filter(u => ['Branch Manager', 'Branch Admin', 'Main Branch Manager'].includes(u.role));
  } catch (err) {
    console.error('Failed to load managers list', err);
  }
};

const loadBranches = async () => {
  try {
    const res = await api.get('/branches');
    branches.value = res.data;
    if (!editingBranch.value && branches.value.length > 0) {
      // Automatically load the first branch details
      selectBranchForEdit(branches.value[0], false);
    } else if (editingBranch.value) {
      const refreshed = branches.value.find(b => b.branch_id === editingBranch.value.branch_id);
      if (refreshed) {
        editingBranch.value = refreshed;
      }
    }
  } catch (err) {
    console.error('Failed to load branches', err);
  }
};

const selectBranchForEdit = (b, scroll = true) => {
  editingBranch.value = b;
  editForm.value = {
    branch_name: b.branch_name || '',
    branch_code: b.branch_code || '',
    branch_type: b.branch_type || 'Regular',
    manager_id: b.manager_id || (b.manager?.user_id ?? null),
    region: b.region || 'Amhara',
    city: b.city || 'Dessie',
    phone: b.phone || '',
    address: b.address || '',
    latitude: b.latitude ?? null,
    longitude: b.longitude ?? null,
    opening_hours: b.opening_hours || 'Mon–Sun 07:00–21:00',
    max_staff: b.max_staff || 15,
    max_managers: b.max_managers || 1,
    isActive: b.status === 'Active',
    isShoppingEnabled: b.is_shopping_enabled !== false,
  };

  if (scroll) {
    setTimeout(() => {
      const el = document.getElementById('branch-details-editor');
      if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    }, 60);
  }
};

const toggleShopping = async (b) => {
  togglingBranchId.value = b.branch_id;
  successMsg.value = '';
  errorMsg.value = '';
  try {
    const nextState = b.is_shopping_enabled === false;
    const res = await api.patch(`/admin/branches/${b.branch_id}/shopping-access`, {
      is_shopping_enabled: nextState,
    });
    b.is_shopping_enabled = res.data.branch.is_shopping_enabled;
    if (editingBranch.value && editingBranch.value.branch_id === b.branch_id) {
      editForm.value.isShoppingEnabled = b.is_shopping_enabled;
    }
    successMsg.value = res.data.message || `Customer shopping access updated for ${b.branch_name}.`;
    await loadBranches();
  } catch (err) {
    errorMsg.value = err.response?.data?.message || 'Failed to toggle branch shopping access.';
  } finally {
    togglingBranchId.value = null;
  }
};

const saveBranch = async () => {
  if (!editingBranch.value) return;
  successMsg.value = '';
  errorMsg.value = '';
  isSavingBranch.value = true;

  try {
    await api.patch(`/admin/branches/${editingBranch.value.branch_id}`, {
      branch_name: editForm.value.branch_name,
      region: editForm.value.region,
      city: editForm.value.city,
      phone: editForm.value.phone,
      address: editForm.value.address,
      latitude: editForm.value.latitude ? Number(editForm.value.latitude) : null,
      longitude: editForm.value.longitude ? Number(editForm.value.longitude) : null,
      opening_hours: editForm.value.opening_hours,
      manager_id: editForm.value.manager_id,
      max_staff: editForm.value.max_staff,
      max_managers: editForm.value.max_managers,
      status: editForm.value.isActive ? 'Active' : 'Inactive',
      is_shopping_enabled: editForm.value.isShoppingEnabled,
    });
    successMsg.value = `Branch ${editForm.value.branch_name} details saved successfully!`;
    await loadBranches();
  } catch (err) {
    errorMsg.value = err.response?.data?.message || 'Failed to update branch';
  } finally {
    isSavingBranch.value = false;
  }
};

const openAddBranchModal = () => {
  newBranch.value = {
    branch_name: '',
    branch_code: '',
    branch_type: 'Regular',
    city: 'Dessie',
    region: 'Amhara',
    address: '',
    latitude: null,
    longitude: null,
    phone: '',
    opening_hours: 'Mon–Sun 07:00–21:00',
  };
  isAddBranchModalOpen.value = true;
};

const submitNewBranch = async () => {
  isSubmittingBranch.value = true;
  successMsg.value = '';
  errorMsg.value = '';
  try {
    const res = await api.post('/admin/branches', newBranch.value);
    successMsg.value = `Branch "${res.data.branch_name}" (${res.data.branch_code}) successfully created!`;
    isAddBranchModalOpen.value = false;
    await loadBranches();
    selectBranchForEdit(res.data);
  } catch (err) {
    errorMsg.value = err.response?.data?.message || 'Failed to register branch';
  } finally {
    isSubmittingBranch.value = false;
  }
};

const submitNewDeliveryZone = async () => {
  if (!editingBranch.value) return;
  isSubmittingZone.value = true;
  successMsg.value = '';
  errorMsg.value = '';
  try {
    const res = await api.post(`/admin/branches/${editingBranch.value.branch_id}/delivery-zones`, newZone.value);
    if (!editingBranch.value.delivery_zones) editingBranch.value.delivery_zones = [];
    editingBranch.value.delivery_zones.push(res.data);
    successMsg.value = `Delivery zone "${res.data.zone_name}" added with fee ETB ${res.data.delivery_fee}`;
    isAddZoneModalOpen.value = false;
    newZone.value = { zone_name: '', delivery_fee: 50 };
    await loadBranches();
  } catch (err) {
    errorMsg.value = err.response?.data?.message || 'Failed to add delivery zone';
  } finally {
    isSubmittingZone.value = false;
  }
};

const deleteDeliveryZone = async (zone) => {
  if (!confirm(`Are you sure you want to remove delivery zone "${zone.zone_name}"?`)) return;
  try {
    await api.delete(`/admin/branches/${editingBranch.value.branch_id}/delivery-zones/${zone.zone_id}`);
    editingBranch.value.delivery_zones = editingBranch.value.delivery_zones.filter(z => z.zone_id !== zone.zone_id);
    successMsg.value = `Delivery zone "${zone.zone_name}" removed.`;
    await loadBranches();
  } catch (err) {
    errorMsg.value = err.response?.data?.message || 'Failed to remove delivery zone';
  }
};

onMounted(async () => {
  await Promise.all([loadBranches(), loadManagers()]);
});
</script>
