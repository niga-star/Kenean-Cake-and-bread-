<template>
  <div class="flex h-screen bg-[#F8F4EE] text-[#2D231D] font-poppins antialiased overflow-hidden relative">
    <!-- Mobile Backdrop Overlay (< lg screens) -->
    <transition
      enter-active-class="transition-opacity duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="sidebarOpen"
        @click="sidebarOpen = false"
        class="fixed inset-0 z-40 bg-black/60 backdrop-blur-xs lg:hidden"
        aria-hidden="true"
      ></div>
    </transition>

    <!-- Dark Sidebar: Responsive Slide-Over Drawer on Mobile/Tablet (< lg), Static on Desktop (lg+) -->
    <aside
      class="fixed inset-y-0 left-0 z-50 w-72 sm:w-64 bg-[#1C130D] flex flex-col justify-between shrink-0 select-none border-r border-[#2A1D15] shadow-2xl lg:shadow-none transition-transform duration-300 ease-in-out lg:static lg:translate-x-0"
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    >
      <div>
        <!-- Brand Logo Header + Mobile Close Button -->
        <div class="px-5 sm:px-6 py-5 sm:py-6 border-b border-[#2C1F16]/60 flex items-center justify-between">
          <div class="flex items-center space-x-2.5">
            <span class="w-8 h-8 rounded-lg bg-[#DE1B54] text-white flex items-center justify-center font-bold text-lg shadow-sm">🧁</span>
            <router-link :to="homePath" @click="sidebarOpen = false" class="block">
              <h1 class="text-2xl font-logo font-bold text-[#DE1B54] tracking-normal">Sweet<span class="text-[#FAF4ED]">Bakes</span></h1>
            </router-link>
          </div>
          <!-- Close Button on Mobile (< lg) -->
          <button
            @click="sidebarOpen = false"
            class="lg:hidden p-1.5 rounded-lg text-gray-400 hover:text-white hover:bg-white/10 transition cursor-pointer"
            aria-label="Close sidebar"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Navigation Menu with Touch-Friendly Links -->
        <nav class="px-3 py-4 space-y-4 sm:space-y-5 overflow-y-auto max-h-[calc(100vh-160px)]">
          <!-- Overview Direct Link (Super Admin, Main Manager, Branch Admin) -->
          <div v-if="canAccess('overview')">
            <router-link
              to="/admin/overview"
              @click="sidebarOpen = false"
              class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors"
              :class="$route.path === '/admin/overview' ? 'bg-[#BA5420] text-white shadow-sm' : 'text-[#B8A79B] hover:bg-[#2A1D15] hover:text-white'"
            >
              {{ isBranchScoped ? 'Branch Overview' : 'Overview' }}
            </router-link>
          </div>

          <!-- BUSINESS Section (Super Admin & Main Manager) -->
          <div v-if="canAccess('branches') || canAccess('users') || canAccess('products')">
            <div class="px-3 mb-2 text-[11px] font-bold tracking-wider text-[#7E6A5C] uppercase">Business</div>
            <div class="space-y-1">
              <router-link
                v-if="canAccess('branches')"
                to="/admin/branches"
                @click="sidebarOpen = false"
                class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors"
                :class="$route.path.startsWith('/admin/branches') ? 'bg-[#BA5420] text-white shadow-sm' : 'text-[#B8A79B] hover:bg-[#2A1D15] hover:text-white'"
              >
                Branches
              </router-link>

              <router-link
                v-if="canAccess('users')"
                to="/admin/users"
                @click="sidebarOpen = false"
                class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors"
                :class="$route.path.startsWith('/admin/users') ? 'bg-[#BA5420] text-white shadow-sm' : 'text-[#B8A79B] hover:bg-[#2A1D15] hover:text-white'"
              >
                Users and roles
              </router-link>

              <router-link
                v-if="canAccess('products')"
                to="/admin/products"
                @click="sidebarOpen = false"
                class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors"
                :class="$route.path.startsWith('/admin/products') ? 'bg-[#BA5420] text-white shadow-sm' : 'text-[#B8A79B] hover:bg-[#2A1D15] hover:text-white'"
              >
                Products and prices
              </router-link>
            </div>
          </div>

          <!-- STOCK Section -->
          <div>
            <div class="px-3 mb-2 text-[11px] font-bold tracking-wider text-[#7E6A5C] uppercase">Stock</div>
            <div class="space-y-1">
              <router-link
                v-if="canAccess('inventory')"
                to="/admin/inventory"
                @click="sidebarOpen = false"
                class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors"
                :class="$route.path === '/admin/inventory' ? 'bg-[#BA5420] text-white shadow-sm' : 'text-[#B8A79B] hover:bg-[#2A1D15] hover:text-white'"
              >
                {{ isBranchScoped ? 'My Inventory' : 'Inventory by branch' }}
              </router-link>

              <router-link
                v-if="canAccess('distribution')"
                to="/admin/distribution"
                @click="sidebarOpen = false"
                class="flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg transition-colors"
                :class="$route.path === '/admin/distribution' ? 'bg-[#BA5420] text-white shadow-sm' : 'text-[#B8A79B] hover:bg-[#2A1D15] hover:text-white'"
              >
                <span>{{ isBranchScoped ? 'Stock Receiving' : 'Distribution' }}</span>
                <span class="px-1.5 py-0.2 text-[11px] bg-[#33241A] text-[#D8C7BA] rounded font-bold">2</span>
              </router-link>

              <router-link
                v-if="canAccess('transfers/new')"
                to="/admin/transfers/new"
                @click="sidebarOpen = false"
                class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors"
                :class="$route.path.startsWith('/admin/transfers/new') ? 'bg-[#BA5420] text-white shadow-sm' : 'text-[#B8A79B] hover:bg-[#2A1D15] hover:text-white'"
              >
                New transfer
              </router-link>
            </div>
          </div>

          <!-- SALES Section -->
          <div>
            <div class="px-3 mb-2 text-[11px] font-bold tracking-wider text-[#7E6A5C] uppercase">Sales</div>
            <div class="space-y-1">
              <router-link
                v-if="canAccess('orders')"
                to="/admin/orders"
                @click="sidebarOpen = false"
                class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors"
                :class="$route.path === '/admin/orders' ? 'bg-[#BA5420] text-white shadow-sm' : 'text-[#B8A79B] hover:bg-[#2A1D15] hover:text-white'"
              >
                {{ currentRole === 'Branch Staff' ? 'Order Preparation' : (isBranchScoped ? 'My Orders' : 'All orders') }}
              </router-link>

              <router-link
                v-if="canAccess('custom-cakes')"
                to="/admin/custom-cakes"
                @click="sidebarOpen = false"
                class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors"
                :class="$route.path === '/admin/custom-cakes' ? 'bg-[#BA5420] text-white shadow-sm' : 'text-[#B8A79B] hover:bg-[#2A1D15] hover:text-white'"
              >
                {{ isBranchScoped ? 'Custom Cake Orders' : 'Custom cakes' }}
              </router-link>

              <router-link
                v-if="canAccess('payments')"
                to="/admin/payments"
                @click="sidebarOpen = false"
                class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors"
                :class="$route.path === '/admin/payments' ? 'bg-[#BA5420] text-white shadow-sm' : 'text-[#B8A79B] hover:bg-[#2A1D15] hover:text-white'"
              >
                {{ isBranchScoped ? 'Branch Sales' : 'Payments' }}
              </router-link>

              <router-link
                v-if="canAccess('reports')"
                to="/admin/reports"
                @click="sidebarOpen = false"
                class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors"
                :class="$route.path === '/admin/reports' ? 'bg-[#BA5420] text-white shadow-sm' : 'text-[#B8A79B] hover:bg-[#2A1D15] hover:text-white'"
              >
                {{ isBranchScoped ? 'Branch Reports' : 'Reports' }}
              </router-link>
            </div>
          </div>

          <!-- SYSTEM Section -->
          <div v-if="canAccess('notifications') || canAccess('audit-logs') || canAccess('settings')">
            <div class="px-3 mb-2 text-[11px] font-bold tracking-wider text-[#7E6A5C] uppercase">System</div>
            <div class="space-y-1">
              <router-link
                v-if="canAccess('notifications')"
                to="/admin/notifications"
                @click="sidebarOpen = false"
                class="flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg transition-colors"
                :class="$route.path === '/admin/notifications' ? 'bg-[#BA5420] text-white shadow-sm' : 'text-[#B8A79B] hover:bg-[#2A1D15] hover:text-white'"
              >
                <span>Notifications</span>
                <span class="px-1.5 py-0.2 text-[11px] bg-[#33241A] text-[#D8C7BA] rounded font-bold">5</span>
              </router-link>

              <router-link
                v-if="canAccess('audit-logs')"
                to="/admin/audit-logs"
                @click="sidebarOpen = false"
                class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors"
                :class="$route.path === '/admin/audit-logs' ? 'bg-[#BA5420] text-white shadow-sm' : 'text-[#B8A79B] hover:bg-[#2A1D15] hover:text-white'"
              >
                Audit log
              </router-link>

              <router-link
                v-if="canAccess('settings')"
                to="/admin/settings"
                @click="sidebarOpen = false"
                class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors"
                :class="$route.path === '/admin/settings' ? 'bg-[#BA5420] text-white shadow-sm' : 'text-[#B8A79B] hover:bg-[#2A1D15] hover:text-white'"
              >
                Settings
              </router-link>
            </div>
          </div>
        </nav>
      </div>

      <!-- Bottom User Profile with Role & Branch Tag -->
      <div class="p-3.5 sm:p-4 border-t border-[#2C1F16]/60 bg-[#160E0A] flex items-center justify-between">
        <div class="min-w-0 pr-2">
          <div class="text-sm font-medium text-[#F4EDE4] truncate max-w-[150px]">
            {{ authStore.user?.full_name || 'Admin User' }}
          </div>
          <div class="text-xs text-[#9B897D] truncate">
            <span class="font-bold text-[#E5B869]">{{ currentRole }}</span>
            <span v-if="authStore.user?.branch"> · {{ authStore.user.branch.branch_name }}</span>
            <span v-else> · All Branches</span>
          </div>
        </div>

        <button
          @click="handleLogout"
          class="p-2 text-gray-400 hover:text-red-400 hover:bg-white/5 rounded-lg transition cursor-pointer shrink-0"
          title="Sign out of portal"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
        </button>
      </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
      <!-- Quick Role Switcher & Header Bar with Responsive Flow -->
      <header class="min-h-[3.5rem] bg-white border-b border-[#E7DFD5] px-3 sm:px-5 lg:px-6 py-2 flex flex-wrap items-center justify-between gap-2.5 shrink-0 shadow-[0_1px_3px_rgba(0,0,0,0.02)]">
        <!-- Left: Mobile Hamburger Toggle + Persona Simulator or Role Scope -->
        <div class="flex items-center space-x-2 sm:space-x-3 flex-1 min-w-0">
          <!-- Hamburger Button (visible on < lg screens) -->
          <button
            @click="sidebarOpen = !sidebarOpen"
            type="button"
            class="lg:hidden p-2 rounded-lg text-[#4A3B32] hover:bg-[#F4ECE3] hover:text-[#BA5420] transition cursor-pointer shrink-0 focus:outline-none focus:ring-2 focus:ring-[#BA5420]"
            title="Open navigation menu"
            aria-label="Open navigation menu"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>

          <!-- 1. Super Admin Only: Persona Simulator -->
          <div v-if="authStore.canSimulatePersona" class="flex flex-wrap items-center gap-2 text-sm min-w-0">
            <span class="text-[#88786B] font-medium text-xs hidden sm:inline shrink-0">Simulate Persona:</span>
            <select
              :value="selectedRoleKey"
              @change="handleRoleChange($event.target.value)"
              class="bg-[#F8F4EE] border border-[#DACDC0] text-[#3A2E26] text-xs font-semibold rounded-lg px-2.5 py-1.5 focus:outline-none focus:ring-1 focus:ring-[#BA5420] cursor-pointer max-w-[160px] xs:max-w-[200px] sm:max-w-none truncate"
            >
              <option
                v-for="(r, idx) in authStore.availableRoles"
                :key="idx"
                :value="idx"
              >
                {{ r.label }}
              </option>
            </select>

            <!-- Simulating Indicator with Exit Button -->
            <span
              v-if="authStore.isSimulating"
              class="text-xs px-2 sm:px-2.5 py-0.5 rounded-full font-bold bg-amber-50 text-amber-900 border border-amber-300 flex items-center space-x-1 shrink-0"
            >
              <span class="truncate max-w-[100px] sm:max-w-none">Simulating: {{ currentRole }}</span>
              <button
                @click="handleExitSimulation"
                class="text-[10px] bg-amber-200 hover:bg-amber-300 text-amber-950 font-bold px-1.5 py-0.2 rounded transition cursor-pointer"
                title="Return to Super Admin"
              >
                Exit ✕
              </button>
            </span>

            <span
              v-else
              class="hidden md:inline-flex text-xs px-2.5 py-0.5 rounded-full font-semibold border bg-[#FFF3E0] text-[#E65100] border-[#FFE0B2] truncate"
            >
              Scope: {{ roleScope }}
            </span>
          </div>

          <!-- 2. Non-Super Admin: Strictly Read-Only Role & Scope Badge -->
          <div v-else class="flex flex-wrap items-center gap-2 text-sm min-w-0">
            <div class="flex items-center space-x-1.5 min-w-0">
              <span class="text-xs text-[#88786B] font-medium hidden sm:inline">Logged in as:</span>
              <span class="text-xs font-bold text-[#2A1810] truncate max-w-[120px] sm:max-w-none">{{ authStore.user?.full_name || 'Staff' }}</span>
            </div>

            <span
              class="text-xs px-2 py-0.5 rounded-full font-semibold border shrink-0"
              :class="{
                'bg-[#EBF3FB] text-[#1565C0] border-[#BBDEFB]': currentRole === 'Main Branch Manager',
                'bg-[#FAF3EC] text-[#BA5420] border-[#EACBB8]': currentRole === 'Branch Admin',
                'bg-[#F3EFE9] text-[#7A6B60] border-[#DDD3C7]': currentRole === 'Branch Staff',
                'bg-gray-100 text-gray-800 border-gray-200': !['Main Branch Manager', 'Branch Admin', 'Branch Staff'].includes(currentRole)
              }"
            >
              {{ currentRole }}
            </span>

            <span class="hidden md:inline-flex text-xs px-2.5 py-0.5 rounded-full font-medium bg-[#FAF6F2] text-[#635347] border border-[#E7DFD5] truncate">
              Scope: {{ roleScope }}
            </span>
          </div>
        </div>

        <!-- Right: Storefront Link -->
        <div class="flex items-center space-x-2 shrink-0">
          <router-link
            to="/"
            class="inline-flex items-center text-xs font-semibold text-[#BA5420] bg-[#FAF2EC] border border-[#EACBB8] hover:bg-[#F6E6DC] px-2.5 sm:px-3.5 py-1.5 rounded-lg transition"
            title="Open customer storefront"
          >
            <span>🛍️</span>
            <span class="hidden sm:inline ml-1.5">SweetBakes Storefront</span>
            <span class="sm:hidden ml-1">Store</span>
          </router-link>
        </div>
      </header>

      <!-- View Slot with Responsive Padding for Mobile, Tablet & Desktop -->
      <main class="flex-1 overflow-y-auto p-3.5 sm:p-5 md:p-6 lg:p-8 bg-[#F8F4EE]">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

// Mobile sidebar drawer state
const sidebarOpen = ref(false);

// Auto-close sidebar on route change for seamless mobile navigation
watch(() => route.path, () => {
  sidebarOpen.value = false;
});

const currentRole = computed(() => authStore.user?.role || 'Super Admin');

const isBranchScoped = computed(() => {
  return currentRole.value === 'Branch Admin' || currentRole.value === 'Branch Staff';
});

const homePath = computed(() => {
  if (currentRole.value === 'Branch Staff') return '/admin/orders';
  return '/admin/overview';
});

const roleScope = computed(() => {
  if (currentRole.value === 'Super Admin') return 'Whole business (All branches)';
  if (currentRole.value === 'Main Branch Manager') return 'Main Branch & Distribution';
  if (currentRole.value === 'Branch Admin') return `${authStore.user?.branch?.branch_name || 'One assigned'} Branch`;
  if (currentRole.value === 'Branch Staff') return `${authStore.user?.branch?.branch_name || 'One assigned'} Branch (Staff)`;
  return 'Customer Storefront';
});

// Role-based permission map
const permissions = {
  'Super Admin': ['overview', 'branches', 'users', 'products', 'inventory', 'distribution', 'transfers/new', 'orders', 'custom-cakes', 'payments', 'reports', 'notifications', 'settings', 'audit-logs'],
  'Main Branch Manager': ['overview', 'users', 'inventory', 'distribution', 'transfers/new', 'orders', 'custom-cakes', 'payments', 'reports', 'notifications'],
  'Branch Admin': ['overview', 'inventory', 'distribution', 'orders', 'custom-cakes', 'reports', 'notifications'],
  'Branch Staff': ['inventory', 'distribution', 'orders', 'notifications'],
  'Customer': [],
};

const canAccess = (feature) => {
  const allowed = permissions[currentRole.value] || [];
  return allowed.includes(feature);
};

const selectedRoleKey = computed(() => {
  const index = authStore.availableRoles.findIndex(
    (r) => r.role === authStore.user?.role && (!r.branch_code || r.branch_code === authStore.user?.branch?.branch_code)
  );
  return index >= 0 ? index : 0;
});

const handleRoleChange = async (index) => {
  const roleItem = authStore.availableRoles[index];
  if (roleItem) {
    await authStore.switchRole(roleItem);
    if (roleItem.role === 'Customer') {
      router.push('/');
    } else if (roleItem.role === 'Branch Staff') {
      router.push('/admin/orders');
    } else {
      router.push('/admin/overview');
    }
  }
};

async function handleExitSimulation() {
  await authStore.exitSimulation();
  router.push('/admin/overview');
}

function handleLogout() {
  authStore.logout();
  router.push('/login');
}
</script>
