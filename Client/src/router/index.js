import { createRouter, createWebHistory } from 'vue-router';
import { useToastStore } from '../stores/toast';

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
      const rawUser = localStorage.getItem('user');
      const user = rawUser ? JSON.parse(rawUser) : null;
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
      { path: 'orders', component: OrdersView, meta: { roles: ['Branch Staff', 'Branch Seller / Cashier', 'Branch Seller', 'Cashier', 'Servant', 'Delivery Staff'] } },
      { path: 'custom-cakes', component: CustomCakesView, meta: { roles: ['Branch Staff', 'Branch Seller / Cashier', 'Branch Seller', 'Cashier', 'Servant', 'Delivery Staff'] } },
      { path: 'payments', component: PaymentsView, meta: { roles: ['Super Admin'] } },
      { path: 'seller-reports', component: SellerDailyReportView, meta: { roles: ['Branch Staff', 'Branch Seller / Cashier', 'Branch Seller', 'Cashier'] } },
      { path: 'manager-reports', component: ManagerOrderReportsView, meta: { roles: ['Super Admin', 'Main Branch Manager', 'Branch Manager', 'Branch Admin'] } },
      { path: 'reports', component: ReportsView, meta: { roles: ['Super Admin'] } },
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
      { path: '', component: CustomerHomeView },
      { path: 'home', component: CustomerHomeView },
      { path: 'shop', component: ShopView },
      { path: 'cakes-and-bread', redirect: '/shop' },
      { path: 'product/:id', component: ProductDetailView },
      { path: 'cart', component: CartView },
      { path: 'checkout', component: CheckoutView },
      { path: 'custom-cake', component: CustomCakeBuilderView },
      { path: 'custom-cakes-builder', redirect: '/custom-cake' },
      { path: 'branches', component: BranchesView },
      { path: 'orders/:order_number', component: OrderStatusView },
      { path: 'track-order', component: OrderTrackingView },
      { path: 'login', component: LoginView },
      { path: 'register', component: RegisterView },
      { path: 'contact', component: ContactView },
      { path: 'about', redirect: '/#about' },
      { path: 'faq', component: FAQView },
      { path: 'account', component: AccountView },
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
  scrollBehavior(to, from, savedPosition) {
    if (to.hash) {
      return {
        el: to.hash,
        behavior: 'smooth',
      };
    }
    if (savedPosition) {
      return savedPosition;
    }
    return { top: 0, behavior: 'smooth' };
  },
});

// Navigation Guard for Role Enforcement
router.beforeEach((to, from, next) => {
  const rawUser = localStorage.getItem('user');
  const token = localStorage.getItem('token');
  const user = rawUser ? JSON.parse(rawUser) : null;

  // Protect Customer Account from unauthenticated guest access;
  // If staff/admin visits customer /account, redirect them to their staff portal /admin/my-account (Section 8)
  if (to.path === '/account') {
    if (!user || !token) {
      const redirectTarget = to.fullPath || to.path;
      return next(`/login?redirect=${encodeURIComponent(redirectTarget)}`);
    }
    if (user.role !== 'Customer') {
      return next('/admin/my-account');
    }
  }

  if (to.path === '/checkout') {
    if (!user || !token) {
      const redirectTarget = to.fullPath || to.path;
      return next(`/login?redirect=${encodeURIComponent(redirectTarget)}`);
    }
  }

  // Redirect already authenticated users away from login/register
  if ((to.path === '/login' || to.path === '/register') && token && user) {
    if (['Super Admin', 'Main Branch Manager', 'Branch Manager', 'Branch Admin'].includes(user.role)) {
      return next('/admin/overview');
    } else if (['Branch Staff', 'Branch Seller', 'Branch Seller / Cashier', 'Cashier', 'Servant', 'Delivery Staff'].includes(user.role)) {
      return next('/admin/orders');
    } else {
      const redirectPath = to.query.redirect || '/account';
      return next(redirectPath);
    }
  }

  if (to.path.startsWith('/admin')) {
    const toast = useToastStore();

    if (!user || !token) {
      toast.error('Access restricted: Please sign in with staff credentials to access the admin portal.');
      return next('/login');
    }

    const role = user.role;

    if (role === 'Customer') {
      toast.error('Access denied: Customer accounts cannot access the bakery admin portal.');
      return next('/account');
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
          return next('/admin/overview');
        }
        if (['Branch Staff', 'Branch Seller', 'Branch Seller / Cashier', 'Cashier', 'Servant', 'Delivery Staff'].includes(role)) {
          return next('/admin/orders');
        }
        return next('/admin/overview');
      }
    }
  }

  next();
});

export default router;
