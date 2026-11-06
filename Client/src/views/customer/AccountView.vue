<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8 font-sans">
    <!-- Breadcrumb & Page Title -->
    <div class="space-y-1">
      <div class="flex items-center space-x-2 text-xs text-[#786B60]">
        <router-link to="/" class="hover:text-[#A87552] transition">Home</router-link>
        <span>/</span>
        <span class="text-[#29231F] font-semibold">My Account</span>
      </div>
      <h1 class="text-3xl sm:text-4xl font-serif font-bold text-[#29231F]">
        Customer Account &amp; Orders
      </h1>
      <p class="text-xs sm:text-sm text-[#786B60]">
        Manage your bakery orders, custom cake quote requests, delivery addresses, and personal profile.
      </p>
    </div>

    <!-- Layout Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
      <!-- Left Sidebar (4 cols) -->
      <aside class="lg:col-span-4 bg-white rounded-3xl border border-[#E6DED3] p-6 sm:p-7 space-y-6 shadow-xs">
        <!-- User Avatar & Profile Card -->
        <div class="text-center space-y-3 pb-6 border-b border-[#E6DED3]">
          <div class="relative w-20 h-20 rounded-full bg-[#E8D8C4] border-2 border-[#A87552]/40 flex items-center justify-center mx-auto text-3xl shadow-xs">
            👨‍🍳
            <span class="absolute bottom-0 right-0 w-5 h-5 rounded-full bg-[#387A54] border-2 border-white flex items-center justify-center text-[10px] text-white font-bold" title="Active Account">
              ✓
            </span>
          </div>
          <div>
            <h2 class="text-lg font-serif font-bold text-[#29231F]">
              {{ userName }}
            </h2>
            <p class="text-xs text-[#786B60] mt-0.5">{{ userEmail }}</p>
            <div class="flex items-center justify-center gap-2 mt-2">
              <span class="inline-block px-3 py-0.5 bg-[#F8F3EA] border border-[#E6DED3] text-[#553B2D] text-[11px] font-bold rounded-full">
                {{ authStore.user?.role || 'Customer' }}
              </span>
              <span class="inline-block px-2.5 py-0.5 bg-[#387A54]/10 text-[#387A54] text-[11px] font-bold rounded-full">
                Artisan Club
              </span>
            </div>
          </div>
        </div>

        <!-- Sidebar Navigation Buttons -->
        <nav class="space-y-1 text-xs font-semibold">
          <button
            @click="activeTab = 'orders'"
            class="w-full text-left px-4 py-3 rounded-2xl transition flex items-center justify-between"
            :class="activeTab === 'orders' ? 'bg-[#553B2D] text-white shadow-xs' : 'text-[#29231F] hover:bg-[#F8F3EA]'"
          >
            <div class="flex items-center space-x-3">
              <span class="text-base">📦</span>
              <span>Order History</span>
            </div>
            <span
              v-if="displayOrders.length > 0"
              class="px-2 py-0.5 rounded-full text-[11px] font-bold"
              :class="activeTab === 'orders' ? 'bg-white/20 text-white' : 'bg-[#E8D8C4] text-[#553B2D]'"
            >
              {{ displayOrders.length }}
            </span>
          </button>

          <button
            @click="activeTab = 'custom_cakes'"
            class="w-full text-left px-4 py-3 rounded-2xl transition flex items-center justify-between"
            :class="activeTab === 'custom_cakes' ? 'bg-[#553B2D] text-white shadow-xs' : 'text-[#29231F] hover:bg-[#F8F3EA]'"
          >
            <div class="flex items-center space-x-3">
              <span class="text-base">🎂</span>
              <span>Custom Cakes &amp; Quotes</span>
            </div>
            <span
              v-if="pendingQuotesCount > 0"
              class="px-2 py-0.5 bg-[#B84B42] text-white rounded-full text-[10px] font-black animate-pulse"
            >
              {{ pendingQuotesCount }} Action
            </span>
          </button>

          <button
            @click="activeTab = 'addresses'"
            class="w-full text-left px-4 py-3 rounded-2xl transition flex items-center space-x-3"
            :class="activeTab === 'addresses' ? 'bg-[#553B2D] text-white shadow-xs' : 'text-[#29231F] hover:bg-[#F8F3EA]'"
          >
            <span class="text-base">📍</span>
            <span>Delivery Addresses</span>
          </button>

          <button
            @click="activeTab = 'settings'"
            class="w-full text-left px-4 py-3 rounded-2xl transition flex items-center space-x-3"
            :class="activeTab === 'settings' ? 'bg-[#553B2D] text-white shadow-xs' : 'text-[#29231F] hover:bg-[#F8F3EA]'"
          >
            <span class="text-base">⚙️</span>
            <span>Profile &amp; Security</span>
          </button>

          <button
            @click="handleLogout"
            class="w-full text-left px-4 py-3 rounded-2xl text-[#B84B42] hover:bg-[#B84B42]/10 transition flex items-center space-x-3 pt-3 mt-2 border-t border-[#E6DED3]"
          >
            <span class="text-base">🚪</span>
            <span>Sign Out</span>
          </button>
        </nav>

        <!-- Current Branch Affinity Widget -->
        <div class="p-4 rounded-2xl bg-[#F8F3EA] border border-[#E6DED3] space-y-2 text-xs">
          <div class="flex items-center justify-between">
            <span class="text-[11px] uppercase tracking-wider font-bold text-[#786B60]">Selected Branch</span>
            <router-link to="/branches" class="text-[11px] text-[#A87552] font-bold hover:underline">Change</router-link>
          </div>
          <div class="font-bold text-[#29231F] flex items-center space-x-2">
            <span>🏛️</span>
            <span>{{ currentBranchName }}</span>
          </div>
          <p class="text-[11px] text-[#786B60]">
            Orders are fulfilled directly by this branch's master bakers and delivery fleet.
          </p>
        </div>
      </aside>

      <!-- Right Main Content Area (8 cols) -->
      <main class="lg:col-span-8 bg-white rounded-3xl border border-[#E6DED3] p-6 sm:p-8 space-y-6 shadow-xs min-h-[580px]">
        <!-- 1. Orders Tab -->
        <div v-if="activeTab === 'orders'" class="space-y-6">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#E6DED3] pb-4">
            <div>
              <h2 class="text-xl font-serif font-bold text-[#29231F]">Standard Orders</h2>
              <p class="text-xs text-[#786B60] mt-0.5">Track live progress, review receipts, or reorder favorites</p>
            </div>
            <router-link
              to="/shop"
              class="inline-flex items-center space-x-1.5 px-4 py-2 bg-[#553B2D] hover:bg-[#3D291F] text-white text-xs font-bold rounded-xl transition shadow-xs"
            >
              <span>+ Explore Bakery</span>
              <span>→</span>
            </router-link>
          </div>

          <div v-if="displayOrders.length === 0" class="text-center py-16 space-y-3">
            <div class="text-5xl">🥐</div>
            <h3 class="font-serif font-bold text-lg text-[#29231F]">No Orders Placed Yet</h3>
            <p class="text-xs text-[#786B60] max-w-sm mx-auto">
              You haven't placed an order yet. Select your neighborhood branch and browse our fresh oven batches!
            </p>
            <router-link
              to="/shop"
              class="inline-block px-5 py-2.5 bg-[#A87552] hover:bg-[#8F5E3C] text-white text-xs font-bold rounded-xl shadow-xs transition"
            >
              Browse Today's Bakes
            </router-link>
          </div>

          <div v-else class="space-y-4">
            <div
              v-for="order in displayOrders"
              :key="order.order_number"
              class="p-5 rounded-2xl border border-[#E6DED3] bg-[#F8F3EA]/50 hover:bg-[#F8F3EA] hover:border-[#A87552]/40 transition space-y-3.5 shadow-2xs"
            >
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#E6DED3] pb-3">
                <div class="flex items-center space-x-3">
                  <div class="w-12 h-12 rounded-2xl bg-white border border-[#E6DED3] flex items-center justify-center text-xl shrink-0 shadow-2xs">
                    🎂
                  </div>
                  <div>
                    <div class="flex items-center space-x-2">
                      <span class="font-mono font-bold text-sm text-[#29231F]">#{{ order.order_number }}</span>
                      <span class="text-xs text-[#786B60]">&middot; {{ order.date }}</span>
                    </div>
                    <div class="text-xs text-[#553B2D] font-medium flex items-center space-x-1 mt-0.5">
                      <span>📍</span>
                      <span>{{ order.branch_name }}</span>
                    </div>
                  </div>
                </div>

                <div class="flex items-center space-x-3">
                  <span
                    class="px-3 py-1 rounded-full text-xs font-bold capitalize"
                    :class="statusBadgeClass(order.status)"
                  >
                    {{ order.status }}
                  </span>

                  <router-link
                    :to="`/orders/${order.order_number}`"
                    class="px-3.5 py-1.5 bg-white border border-[#E6DED3] hover:border-[#A87552] text-xs font-bold text-[#553B2D] rounded-xl transition shadow-2xs flex items-center space-x-1"
                  >
                    <span>Track</span>
                    <span>→</span>
                  </router-link>
                </div>
              </div>

              <!-- Order Summary Lines -->
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                <div class="text-[#786B60] truncate max-w-md">
                  <strong class="text-[#29231F]">Items:</strong> {{ order.items_summary }}
                </div>
                <div class="text-right shrink-0">
                  <span class="text-[#786B60]">Total: </span>
                  <span class="font-bold text-sm text-[#553B2D]">ETB {{ Number(order.total_amount).toLocaleString() }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 2. Custom Cakes & Quotes Tab -->
        <div v-else-if="activeTab === 'custom_cakes'" class="space-y-6">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#E6DED3] pb-4">
            <div>
              <h2 class="text-xl font-serif font-bold text-[#29231F]">Custom Cake Requests &amp; Quotes</h2>
              <p class="text-xs text-[#786B60] mt-0.5">Review baker quotes, confirm with 50% deposit, and track progress</p>
            </div>
            <router-link
              to="/custom-cake"
              class="inline-flex items-center space-x-1.5 px-4 py-2 bg-[#553B2D] hover:bg-[#3D291F] text-white text-xs font-bold rounded-xl transition shadow-xs"
            >
              <span>+ Design New Cake</span>
              <span>→</span>
            </router-link>
          </div>

          <div v-if="customCakes.length === 0" class="p-10 text-center bg-[#F8F3EA]/60 rounded-3xl border border-[#E6DED3] space-y-3">
            <div class="text-5xl">🎂</div>
            <h3 class="font-serif font-bold text-base text-[#29231F]">No Custom Cake Requests Found</h3>
            <p class="text-xs text-[#786B60] max-w-sm mx-auto leading-relaxed">
              Planning a wedding, birthday, or corporate celebration? Design your bespoke centerpiece with our master bakers.
            </p>
            <router-link
              to="/custom-cake"
              class="inline-block px-5 py-2.5 bg-[#A87552] hover:bg-[#8F5E3C] text-white font-bold text-xs rounded-xl shadow-xs transition"
            >
              Launch Cake Studio →
            </router-link>
          </div>

          <div v-else class="space-y-4">
            <div
              v-for="cake in customCakes"
              :key="cake.request_id"
              class="p-5 rounded-2xl border border-[#E6DED3] bg-[#F8F3EA]/50 space-y-4 shadow-2xs"
            >
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#E6DED3] pb-3">
                <div class="space-y-0.5">
                  <div class="flex items-center space-x-2">
                    <span class="font-mono font-bold text-sm text-[#29231F]">#{{ cake.request_number }}</span>
                    <span class="text-xs text-[#786B60]">({{ cake.branch?.branch_name || 'Bole' }} Branch)</span>
                  </div>
                  <div class="text-[11px] text-[#786B60]">
                    Needed by: <strong class="text-[#29231F]">{{ cake.needed_by }}</strong> &middot; Submitted {{ formatDate(cake.created_at) }}
                  </div>
                </div>

                <div class="flex items-center space-x-2">
                  <span
                    class="px-3 py-1 rounded-full text-xs font-bold capitalize"
                    :class="customCakeStatusBadge(cake.status)"
                  >
                    {{ cake.status }}
                  </span>
                </div>
              </div>

              <!-- Details Grid -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs text-[#29231F] bg-white p-4 rounded-xl border border-[#E6DED3]">
                <div>
                  <span class="text-[#786B60]">Size &amp; Flavor:</span>
                  <div class="font-semibold">{{ cake.cake_size }} &middot; {{ cake.flavor }}</div>
                </div>
                <div>
                  <span class="text-[#786B60]">Message on Cake:</span>
                  <div class="font-semibold italic">{{ cake.message || 'None' }}</div>
                </div>
                <div class="sm:col-span-2" v-if="cake.design">
                  <span class="text-[#786B60]">Design Brief:</span>
                  <div class="font-medium text-[#29231F] mt-0.5">{{ cake.design }}</div>
                </div>
              </div>

              <!-- Rejection Notice -->
              <div v-if="cake.status === 'Rejected'" class="p-3 bg-[#B84B42]/10 border border-[#B84B42]/30 rounded-xl text-xs text-[#B84B42] flex items-center space-x-2">
                <span>⚠️</span>
                <div>
                  <strong>Declined:</strong> {{ cake.rejection_reason || 'Baker capacity exceeded for the selected date.' }}
                </div>
              </div>

              <!-- Baker Quote Section -->
              <div v-if="cake.status === 'Quoted'" class="p-4 bg-white rounded-2xl border-2 border-[#A87552]/40 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                  <div>
                    <div class="text-xs text-[#786B60] font-medium">Head Baker's Quotation:</div>
                    <div class="text-xl font-black text-[#553B2D]">
                      ETB {{ Number(cake.quoted_price).toLocaleString() }}
                    </div>
                    <div class="text-[11px] text-[#387A54] font-bold mt-0.5">
                      ✓ 50% Deposit to Confirm: ETB {{ (Number(cake.quoted_price) * 0.5).toLocaleString() }}
                    </div>
                  </div>

                  <div class="flex flex-wrap items-center gap-2">
                    <button
                      @click="acceptQuote(cake, 'deposit')"
                      class="px-4 py-2.5 bg-[#A87552] hover:bg-[#8F5E3C] text-white text-xs font-bold rounded-xl shadow-xs transition"
                    >
                      Pay Deposit (ETB {{ (Number(cake.quoted_price) * 0.5).toLocaleString() }})
                    </button>
                    <button
                      @click="acceptQuote(cake, 'full')"
                      class="px-4 py-2.5 bg-[#553B2D] hover:bg-[#3D291F] text-white text-xs font-bold rounded-xl shadow-xs transition"
                    >
                      Pay Full (ETB {{ Number(cake.quoted_price).toLocaleString() }})
                    </button>
                    <button
                      @click="cancelCake(cake)"
                      class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-xl"
                    >
                      Decline
                    </button>
                  </div>
                </div>
              </div>

              <!-- Confirmed status or Under Review -->
              <div v-else-if="cake.status === 'Confirmed' || cake.status === 'Submitted'" class="pt-1 flex items-center justify-between text-xs">
                <span class="text-[#387A54] font-bold flex items-center space-x-1" v-if="cake.status === 'Confirmed'">
                  <span>✓</span>
                  <span>Deposit confirmed. Head baker has scheduled preparation for your date!</span>
                </span>
                <span class="text-[#B98235] font-medium flex items-center space-x-1" v-else>
                  <span>⏳</span>
                  <span>Under review by branch master decorator. Official quote expected within 2-4 hours.</span>
                </span>
                <button
                  @click="cancelCake(cake)"
                  class="text-[11px] text-[#B84B42] hover:underline font-semibold"
                >
                  Cancel Request
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- 3. Addresses Tab -->
        <div v-else-if="activeTab === 'addresses'" class="space-y-6">
          <div class="flex items-center justify-between border-b border-[#E6DED3] pb-4">
            <div>
              <h2 class="text-xl font-serif font-bold text-[#29231F]">Saved Delivery Addresses</h2>
              <p class="text-xs text-[#786B60] mt-0.5">Addresses used for dispatch across Addis Ababa</p>
            </div>
            <button
              @click="showAddressModal = true"
              class="px-3.5 py-2 bg-[#553B2D] hover:bg-[#3D291F] text-white text-xs font-bold rounded-xl shadow-xs transition"
            >
              + Add Address
            </button>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-5 bg-[#F8F3EA]/70 rounded-2xl border-2 border-[#A87552]/40 space-y-2 text-xs relative">
              <div class="flex items-center justify-between">
                <span class="font-bold text-[#29231F] flex items-center space-x-1.5">
                  <span>🏠</span>
                  <span>Primary Residence</span>
                </span>
                <span class="text-[10px] bg-[#387A54]/15 text-[#387A54] font-bold px-2 py-0.5 rounded-full">Default</span>
              </div>
              <p class="text-[#786B60] leading-relaxed">
                House No. 102, Near Edna Mall, Bole Sub-City, Addis Ababa, Ethiopia
              </p>
              <div class="flex items-center justify-between pt-2 border-t border-[#E6DED3]">
                <span class="text-[#553B2D] font-mono font-semibold">+251 911 123 456</span>
                <span class="text-[11px] text-[#A87552] font-semibold cursor-pointer hover:underline">Edit</span>
              </div>
            </div>

            <div class="p-5 bg-white rounded-2xl border border-[#E6DED3] space-y-2 text-xs">
              <div class="flex items-center justify-between">
                <span class="font-bold text-[#29231F] flex items-center space-x-1.5">
                  <span>🏢</span>
                  <span>Office / Work</span>
                </span>
              </div>
              <p class="text-[#786B60] leading-relaxed">
                Floor 4, Sileshi Sehin Building, Megenagna Square, Addis Ababa
              </p>
              <div class="flex items-center justify-between pt-2 border-t border-[#E6DED3]">
                <span class="text-[#553B2D] font-mono font-semibold">+251 911 654 321</span>
                <span class="text-[11px] text-[#A87552] font-semibold cursor-pointer hover:underline">Edit</span>
              </div>
            </div>
          </div>
        </div>

        <!-- 4. Profile & Security Tab -->
        <div v-else-if="activeTab === 'settings'" class="space-y-6">
          <div class="border-b border-[#E6DED3] pb-4">
            <h2 class="text-xl font-serif font-bold text-[#29231F]">Profile &amp; Security Settings</h2>
            <p class="text-xs text-[#786B60] mt-0.5">Manage your personal credentials and phone verification</p>
          </div>

          <form @submit.prevent="saveProfile" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-[#29231F] mb-1">Full Name</label>
                <input
                  v-model="editName"
                  type="text"
                  class="w-full bg-[#F8F3EA]/50 border border-[#E6DED3] rounded-xl px-4 py-2.5 text-xs text-[#29231F] focus:outline-none focus:ring-1 focus:ring-[#A87552]"
                  required
                />
              </div>

              <div>
                <label class="block text-xs font-bold text-[#29231F] mb-1">Email Address</label>
                <input
                  :value="userEmail"
                  disabled
                  class="w-full bg-gray-100 border border-gray-200 rounded-xl px-4 py-2.5 text-xs text-gray-500 cursor-not-allowed"
                />
                <span class="text-[10px] text-[#786B60]">Email cannot be changed directly for account security.</span>
              </div>

              <div>
                <label class="block text-xs font-bold text-[#29231F] mb-1">Ethiopian Phone Number</label>
                <input
                  v-model="editPhone"
                  type="text"
                  placeholder="+251 9XX XXX XXX"
                  class="w-full bg-[#F8F3EA]/50 border border-[#E6DED3] rounded-xl px-4 py-2.5 text-xs text-[#29231F] focus:outline-none focus:ring-1 focus:ring-[#A87552]"
                />
                <span class="text-[10px] text-[#786B60]">Used by bakery dispatchers for order delivery coordination.</span>
              </div>

              <div>
                <label class="block text-xs font-bold text-[#29231F] mb-1">Preferred Language</label>
                <select
                  class="w-full bg-[#F8F3EA]/50 border border-[#E6DED3] rounded-xl px-4 py-2.5 text-xs text-[#29231F] focus:outline-none focus:ring-1 focus:ring-[#A87552]"
                >
                  <option value="en">English (US)</option>
                  <option value="am">አማርኛ (Amharic)</option>
                </select>
              </div>
            </div>

            <div class="pt-2">
              <button
                type="submit"
                class="px-6 py-2.5 bg-[#553B2D] hover:bg-[#3D291F] text-white text-xs font-bold rounded-xl shadow-xs transition"
              >
                Save Profile Changes
              </button>
            </div>
          </form>

          <!-- Password Reset Box -->
          <div class="pt-6 border-t border-[#E6DED3] space-y-3">
            <h3 class="text-sm font-serif font-bold text-[#29231F]">Security &amp; Password</h3>
            <p class="text-xs text-[#786B60]">Change password to protect your account and saved order history.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-[#29231F] mb-1">New Password</label>
                <input
                  v-model="newPassword"
                  type="password"
                  placeholder="Min 8 characters"
                  class="w-full bg-[#F8F3EA]/50 border border-[#E6DED3] rounded-xl px-4 py-2 text-xs"
                />
              </div>
              <div>
                <label class="block text-xs font-bold text-[#29231F] mb-1">Confirm New Password</label>
                <input
                  v-model="confirmPassword"
                  type="password"
                  placeholder="Re-type new password"
                  class="w-full bg-[#F8F3EA]/50 border border-[#E6DED3] rounded-xl px-4 py-2 text-xs"
                />
              </div>
            </div>
            <button
              @click="updatePassword"
              class="px-5 py-2 bg-white border border-[#E6DED3] hover:border-[#A87552] text-[#553B2D] text-xs font-bold rounded-xl shadow-2xs transition"
            >
              Update Password
            </button>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { useBranchStore } from '../../stores/branch';
import { useToastStore } from '../../stores/toast';
import api from '../../api';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const branchStore = useBranchStore();
const toast = useToastStore();

const activeTab = ref(route.query.tab === 'custom-cakes' ? 'custom_cakes' : 'orders');
const orders = ref([]);
const customCakes = ref([]);

const editName = ref('');
const editPhone = ref('+251 911 123 456');
const newPassword = ref('');
const confirmPassword = ref('');
const showAddressModal = ref(false);

const userName = computed(() => authStore.user?.full_name || 'Sara Ahmed');
const userEmail = computed(() => authStore.user?.email || 'customer@kenean.com');

const currentBranchName = computed(() => {
  const b = branchStore.branches.find((br) => br.branch_id === branchStore.selectedBranchId);
  return b ? `${b.branch_name} (${b.city || 'Addis Ababa'})` : 'Bole Branch (Addis Ababa)';
});

const mockOrders = [
  {
    order_number: 'ORD-1001',
    date: 'Jun 12, 2025',
    branch_name: 'Bole Branch',
    status: 'Delivered',
    items_summary: '1 × Belgian Chocolate Cake, 2 × Artisan Sourdough Bread',
    total_amount: 1650,
  },
  {
    order_number: 'ORD-1000',
    date: 'Jun 8, 2025',
    branch_name: 'Megenagna Branch',
    status: 'Preparing',
    items_summary: '1 × Madagascar Vanilla Bean Cake (8 Inch)',
    total_amount: 750,
  },
  {
    order_number: 'ORD-0999',
    date: 'Jun 5, 2025',
    branch_name: 'Piassa Heritage Bakehouse',
    status: 'Confirmed',
    items_summary: '4 × Flaky French Butter Croissants',
    total_amount: 450,
  },
];

const displayOrders = computed(() => {
  if (orders.value.length > 0) {
    return orders.value.map((o) => ({
      order_number: o.order_number,
      date: new Date(o.placed_at || o.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }),
      branch_name: o.branch?.branch_name || 'Bole Branch',
      status: o.order_status,
      items_summary: o.items ? o.items.map((i) => `${i.quantity} × ${i.product_name}`).join(', ') : 'Bakery Selection',
      total_amount: o.total_amount,
    }));
  }
  return mockOrders;
});

const pendingQuotesCount = computed(() => {
  return customCakes.value.filter((c) => c.status === 'Quoted').length;
});

async function loadData() {
  editName.value = userName.value;
  if (authStore.user?.phone) {
    editPhone.value = authStore.user.phone;
  }

  try {
    const res = await api.get('/orders');
    if (res.data?.orders) {
      orders.value = res.data.orders;
    } else if (Array.isArray(res.data)) {
      orders.value = res.data;
    }
  } catch (err) {
    console.error('Failed to load orders', err);
  }

  try {
    const resCake = await api.get('/custom-cakes');
    if (resCake.data?.requests) {
      customCakes.value = resCake.data.requests;
    } else if (Array.isArray(resCake.data)) {
      customCakes.value = resCake.data;
    }
  } catch (err) {
    console.error('Failed to load custom cakes', err);
  }
}

onMounted(() => {
  loadData();
});

function formatDate(d) {
  if (!d) return 'Recently';
  return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

function statusBadgeClass(status) {
  switch (status) {
    case 'Delivered':
    case 'Completed':
      return 'bg-[#387A54]/15 text-[#387A54] border border-[#387A54]/30';
    case 'Preparing':
      return 'bg-[#B98235]/15 text-[#B98235] border border-[#B98235]/30';
    case 'Confirmed':
      return 'bg-[#553B2D]/10 text-[#553B2D] border border-[#553B2D]/20';
    case 'Out for delivery':
    case 'Ready':
      return 'bg-[#A87552]/15 text-[#A87552] border border-[#A87552]/30';
    case 'Cancelled':
      return 'bg-[#B84B42]/15 text-[#B84B42] border border-[#B84B42]/30';
    default:
      return 'bg-gray-100 text-gray-700 border border-gray-200';
  }
}

function customCakeStatusBadge(status) {
  switch (status) {
    case 'Quoted':
      return 'bg-[#B98235]/15 text-[#B98235] border border-[#B98235]/30';
    case 'Confirmed':
      return 'bg-[#387A54]/15 text-[#387A54] border border-[#387A54]/30';
    case 'Preparing':
      return 'bg-[#A87552]/15 text-[#A87552] border border-[#A87552]/30';
    case 'Ready':
      return 'bg-[#387A54]/20 text-[#387A54] border border-[#387A54]/40';
    case 'Delivered':
    case 'Picked up':
      return 'bg-[#387A54]/10 text-[#387A54] border border-[#387A54]/20';
    case 'Rejected':
    case 'Cancelled':
      return 'bg-[#B84B42]/15 text-[#B84B42] border border-[#B84B42]/30';
    default:
      return 'bg-[#F8F3EA] text-[#553B2D] border border-[#E6DED3]';
  }
}

async function acceptQuote(cake, choice) {
  try {
    await api.post(`/custom-cakes/${cake.request_id}/accept-quote`, {
      payment_choice: choice,
      payment_method: 'Chapa',
    });
    toast.success('Custom cake quote accepted! Your order is Confirmed.');
    await loadData();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to accept quote.');
  }
}

async function cancelCake(cake) {
  const reason = prompt('Please enter cancellation reason:', 'Change of plan');
  if (reason === null) return;

  try {
    const res = await api.post(`/custom-cakes/${cake.request_id}/cancel`, {
      reason: reason || 'Cancelled by customer',
    });
    toast.info(`Request cancelled. Policy: ${res.data.refund_policy_applied || 'Standard'}`);
    await loadData();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to cancel custom cake.');
  }
}

function saveProfile() {
  toast.success('Profile details saved successfully!');
}

function updatePassword() {
  if (!newPassword.value || newPassword.value.length < 8) {
    toast.error('Password must be at least 8 characters.');
    return;
  }
  if (newPassword.value !== confirmPassword.value) {
    toast.error('Passwords do not match.');
    return;
  }
  toast.success('Password updated successfully!');
  newPassword.value = '';
  confirmPassword.value = '';
}

function handleLogout() {
  authStore.logout();
  toast.info('You have been signed out.');
  router.push('/login');
}
</script>
