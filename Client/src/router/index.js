import { createRouter, createWebHistory } from 'vue-router';
import { nextTick } from 'vue';
import { useToastStore } from '../stores/toast';
import { beginPageTransition, waitForPageLeave, prefersReducedMotion } from '../composables/usePageTransition';

async function waitForHashElement(hash, maxFrames = 15) {
  if (typeof document === 'undefined' || !hash) return false;
  for (let i = 0; i < maxFrames; i++) {
    if (document.querySelector(hash)) return true;
    await new Promise((resolve) => requestAnimationFrame(resolve));
  }
  return !!document.querySelector(hash);
}

function getStoredUser() {
  try {
    const rawUser = localStorage.getItem('user');
    return rawUser ? JSON.parse(rawUser) : null;
  } catch {
    localStorage.removeItem('user');
    return null;
  }
}

// Core Layouts (Lazy Loaded)
const AdminLayout = () => import('../layouts/AdminLayout.vue');
const CustomerLayout = () => import('../layouts/CustomerLayout.vue');

// Admin views (Lazy Loaded)
const OverviewView = () => import('../views/admin/OverviewView.vue');
const BranchManagementView = () => import('../views/admin/BranchManagementView.vue');
const UsersRolesView = () => import('../views/admin/UsersRolesView.vue');
const ProductsPricesView = () => import('../views/admin/ProductsPricesView.vue');
const InventoryView = () => import('../views/admin/InventoryView.vue');
const DistributionView = () => import('../views/admin/DistributionView.vue');
const NewTransferView = () => import('../views/admin/NewTransferView.vue');
const OrdersView = () => import('../views/admin/OrdersView.vue');
const CustomCakesView = () => import('../views/admin/CustomCakesView.vue');
const PaymentsView = () => import('../views/admin/PaymentsView.vue');
const ReportsView = () => import('../views/admin/ReportsView.vue');
const NotificationsView = () => import('../views/admin/NotificationsView.vue');
const SettingsView = () => import('../views/admin/SettingsView.vue');
const AuditLogView = () => import('../views/admin/AuditLogView.vue');
const MyAccountView = () => import('../views/admin/MyAccountView.vue');
const SalariesView = () => import('../views/admin/SalariesView.vue');
const StaffReportsView = () => import('../views/admin/StaffReportsView.vue');
const SellerDailyReportView = () => import('../views/admin/SellerDailyReportView.vue');
const ManagerOrderReportsView = () => import('../views/admin/ManagerOrderReportsView.vue');

// Customer views (Lazy Loaded)
const CustomerHomeView = () => import('../views/customer/CustomerHomeView.vue');
const ShopView = () => import('../views/customer/ShopView.vue');
const ProductDetailView = () => import('../views/customer/ProductDetailView.vue');
const CartView = () => import('../views/customer/CartView.vue');
const CheckoutView = () => import('../views/customer/CheckoutView.vue');
const CustomCakeBuilderView = () => import('../views/customer/CustomCakeBuilderView.vue');
const BranchesView = () => import('../views/customer/BranchesView.vue');
const OrderStatusView = () => import('../views/customer/OrderStatusView.vue');
const OrderTrackingView = () => import('../views/customer/OrderTrackingView.vue');
const LoginView = () => import('../views/customer/LoginView.vue');
const RegisterView = () => import('../views/customer/RegisterView.vue');
const ContactView = () => import('../views/customer/ContactView.vue');
const AccountView = () => import('../views/customer/AccountView.vue');
const FAQView = () => import('../views/customer/FAQView.vue');

const routes = [
  // Admin Portal Routes
  {
    path: '/admin',
    component: AdminLayout,
    redirect: () => {
      const user = getStoredUser();
      if (user?.role === 'Branch Staff') {
        return '/admin/orders';
      }
      return '/admin/overview';
    },
    children: [
      { path: 'overview', component: OverviewView, meta: { roles: ['Super Admin', 'Main Branch Manager', 'Branch Manager', 'Branch Admin'] } },
      { path: 'branches', component: BranchManagementView, meta: { roles: ['Super Admin'] } },
      { path: 'users', component: UsersRolesView, meta: { roles: ['Super Admin', 'Main Branch Manager', 'Branch Manager', 'Branch Admin'] } },
      { path: 'salaries', component: SalariesView, meta: { roles: ['Super Admin'] } },
      { path: 'staff-reports', redirect: '/admin/seller-reports' },
      { path: 'my-account', component: MyAccountView, meta: { roles: ['Super Admin', 'Main Branch Manager', 'Branch Manager', 'Branch Admin', 'Branch Staff', 'Branch Seller / Cashier', 'Branch Seller', 'Servant', 'Delivery Staff'] } },
      { path: 'products', component: ProductsPricesView, meta: { roles: ['Main Branch Manager', 'Branch Manager', 'Branch Admin', 'Branch Staff', 'Branch Seller / Cashier', 'Branch Seller', 'Cashier'] } },
      { path: 'inventory', component: InventoryView, meta: { roles: ['Super Admin', 'Main Branch Manager', 'Branch Manager', 'Branch Admin', 'Branch Staff', 'Branch Seller / Cashier', 'Branch Seller', 'Servant'] } },
      { path: 'distribution', component: DistributionView, meta: { roles: ['Super Admin', 'Main Branch Manager', 'Branch Manager', 'Branch Admin', 'Branch Staff'] } },
      { path: 'transfers/new', component: NewTransferView, meta: { roles: ['Super Admin', 'Main Branch Manager'] } },
      { path: 'orders', component: OrdersView, meta: { roles: ['Main Branch Manager', 'Branch Manager', 'Branch Admin', 'Branch Staff', 'Branch Seller / Cashier', 'Branch Seller', 'Cashier', 'Servant', 'Delivery Staff'] } },
      { path: 'custom-cakes', component: CustomCakesView, meta: { roles: ['Main Branch Manager', 'Branch Manager', 'Branch Admin', 'Branch Staff', 'Branch Seller / Cashier', 'Branch Seller', 'Cashier', 'Servant', 'Delivery Staff'] } },
      { path: 'payments', component: PaymentsView, meta: { roles: ['Super Admin'] } },
      { path: 'seller-reports', component: SellerDailyReportView, meta: { roles: ['Branch Staff', 'Branch Seller / Cashier', 'Branch Seller', 'Cashier'] } },
      { path: 'manager-reports', component: ManagerOrderReportsView, meta: { roles: ['Super Admin', 'Main Branch Manager', 'Branch Manager', 'Branch Admin'] } },
      { path: 'reports', redirect: '/admin/overview' },
      { path: 'notifications', component: NotificationsView, meta: { roles: ['Super Admin', 'Main Branch Manager', 'Branch Manager', 'Branch Admin', 'Branch Staff', 'Branch Seller / Cashier', 'Branch Seller', 'Servant', 'Delivery Staff'] } },
      { path: 'settings', component: SettingsView, meta: { roles: ['Super Admin'] } },
      { path: 'audit-logs', redirect: '/admin/overview' },
    ],
  },

  // Customer SweetBakes Storefront Routes
  {
    path: '/',
    component: CustomerLayout,
    children: [
      { path: '', component: CustomerHomeView, meta: { title: 'Kenean Cake & Bread | Artisan Bakery in Dessie, Ethiopia', description: 'Fresh custom celebration cakes, sourdough breads, pastries, and same-day branch delivery across Dessie.' } },
      { path: 'home', component: CustomerHomeView, meta: { title: 'Kenean Cake & Bread | Artisan Bakery in Dessie, Ethiopia', description: 'Fresh custom celebration cakes, sourdough breads, pastries, and same-day branch delivery across Dessie.' } },
      { path: 'shop', component: ShopView, meta: { title: 'Shop Cakes, Bread & Pastries | Kenean Cake', description: 'Browse fresh daily pastries, birthday cakes, wedding cakes, and artisanal bread by branch.' } },
      { path: 'cakes-and-bread', redirect: '/shop' },
      { path: 'product/:id', component: ProductDetailView, meta: { title: 'Product Details | Kenean Cake' } },
      { path: 'cart', component: CartView, meta: { title: 'Shopping Bag | Kenean Cake' } },
      { path: 'checkout', component: CheckoutView, meta: { title: 'Checkout | Kenean Cake' } },
      { path: 'custom-cake', component: CustomCakeBuilderView, meta: { title: '3D Custom Cake Studio | Kenean Cake', description: 'Design your custom birthday or wedding cake in 3D and request a bespoke bakery quote.' } },
      { path: 'custom-cakes-builder', redirect: '/custom-cake' },
      { path: 'branches', component: BranchesView, meta: { title: 'Bakery Branches in Dessie | Kenean Cake', description: 'Explore interactive Dessie branch locations, delivery coverage zones, and opening hours.' } },
      { path: 'orders/:order_number', component: OrderStatusView, meta: { title: 'Order Status & Receipt | Kenean Cake' } },
      { path: 'track-order', component: OrderTrackingView, meta: { title: 'Track Your Order | Kenean Cake', description: 'Check live preparation, baking, and delivery progress for your bakery order.' } },
      { path: 'login', component: LoginView, meta: { title: 'Sign In | Kenean Cake' } },
      { path: 'register', component: RegisterView, meta: { title: 'Create Account | Kenean Cake' } },
      { path: 'contact', component: ContactView, meta: { title: 'Contact Us | Kenean Cake', description: 'Reach our Dessie bakery branches for custom cake inquiries, catering, and customer support.' } },
      { path: 'about', redirect: '/#about' },
      { path: 'faq', component: FAQView, meta: { title: 'Frequently Asked Questions | Kenean Cake', description: 'Answers on cake customization, delivery zones in Dessie, ordering lead times, and payments.' } },
      { path: 'account', component: AccountView, meta: { title: 'My Account | Kenean Cake' } },
      { path: 'my-orders', redirect: '/account' },
    ],
  },

  // Legacy fallback
  { path: '/store', redirect: '/' },

  // Catch-all
  {
    path: '/:pathMatch(.*)*',
    redirect: '/',
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  async scrollBehavior(to, from, savedPosition) {
    const scrollStyle = prefersReducedMotion() ? 'auto' : 'smooth';
    const isRoutePathChange = from.matched.length > 0 && to.path !== from.path;

    // Wait until the exiting page finishes its fade-out so it stays stationary while leaving
    if (isRoutePathChange) {
      await waitForPageLeave();
      await nextTick();
    }

    if (savedPosition) {
      return {
        ...savedPosition,
        behavior: isRoutePathChange ? 'auto' : scrollStyle,
      };
    }

    if (to.hash) {
      await waitForHashElement(to.hash);
      return {
        el: to.hash,
        top: 84,
        behavior: isRoutePathChange ? 'auto' : scrollStyle,
      };
    }

    return {
      top: 0,
      behavior: isRoutePathChange ? 'auto' : scrollStyle,
    };
  },
});

router.beforeResolve((to, from) => {
  if (from.matched.length > 0 && to.path !== from.path) {
    beginPageTransition();
  }
});

// Navigation Guard for Role Enforcement
router.beforeEach((to, from) => {
  const token = localStorage.getItem('token');
  const user = getStoredUser();

  // Protect Customer Account from unauthenticated guest access;
  // If staff/admin visits customer /account, redirect them to their staff portal /admin/my-account (Section 8)
  if (to.path === '/account') {
    if (!user || !token) {
      const redirectTarget = to.fullPath || to.path;
      return `/login?redirect=${encodeURIComponent(redirectTarget)}`;
    }
    if (user.role !== 'Customer') {
      return '/admin/my-account';
    }
  }

  if (to.path === '/checkout') {
    if (!user || !token) {
      const redirectTarget = to.fullPath || to.path;
      return `/login?redirect=${encodeURIComponent(redirectTarget)}`;
    }
  }

  // Redirect already authenticated users away from login/register
  if ((to.path === '/login' || to.path === '/register') && token && user) {
    if (['Super Admin', 'Main Branch Manager', 'Branch Manager', 'Branch Admin'].includes(user.role)) {
      return '/admin/overview';
    } else if (['Branch Staff', 'Branch Seller', 'Branch Seller / Cashier', 'Cashier', 'Servant', 'Delivery Staff'].includes(user.role)) {
      return '/admin/orders';
    } else {
      const redirectPath = to.query.redirect || '/account';
      return redirectPath;
    }
  }

  if (to.path.startsWith('/admin')) {
    const toast = useToastStore();

    if (!user || !token) {
      toast.error('Access restricted: Please sign in with staff credentials to access the admin portal.');
      return '/login';
    }

    const role = user.role;

    if (role === 'Customer') {
      toast.error('Access denied: Customer accounts cannot access the bakery admin portal.');
      return '/account';
    }

    if (to.meta?.roles) {
      // Check role with equivalence mapping
      const allowed = to.meta.roles;
      let hasAccess = allowed.includes(role);

      if (!hasAccess) {
        if (role === 'Super Admin' && !['/admin/products', '/admin/orders', '/admin/custom-cakes', '/admin/staff-reports'].includes(to.path)) hasAccess = true;
        if (allowed.includes('Branch Manager') && role === 'Branch Admin') hasAccess = true;
        if (allowed.includes('Branch Admin') && role === 'Branch Manager') hasAccess = true;
        if (allowed.includes('Branch Staff') && [
          'Branch Staff',
          'Branch Seller',
          'Branch Seller / Cashier',
          'Cashier',
          'Servant',
          'Delivery Staff',
        ].includes(role)) hasAccess = true;
      }

      if (!hasAccess) {
        toast.error(`Access restricted: The "${to.path.replace('/admin/', '')}" page is only accessible to: ${to.meta.roles.join(', ')}.`);
        if (role === 'Super Admin') {
          return '/admin/overview';
        }
        if (['Branch Staff', 'Branch Seller', 'Branch Seller / Cashier', 'Cashier', 'Servant', 'Delivery Staff'].includes(role)) {
          return '/admin/orders';
        }
        return '/admin/overview';
      }
    }
  }

  return true;
});

router.afterEach((to) => {
  if (typeof document === 'undefined') return;
  const pageTitle = to.meta?.title || (to.path.startsWith('/admin') ? 'Management Portal | Kenean Cake' : 'Kenean Cake & Bread | Artisan Bakery');
  document.title = pageTitle;

  const defaultDesc = 'Kenean Cake & Bread (SweetBakes) in Dessie, Ethiopia — freshly baked celebration cakes, custom 3D cake studio, pastries, and branch delivery.';
  const metaDesc = document.querySelector('meta[name="description"]');
  if (metaDesc) {
    metaDesc.setAttribute('content', to.meta?.description || defaultDesc);
  }
});

export default router;
