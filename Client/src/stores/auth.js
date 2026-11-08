import { defineStore } from 'pinia';
import api from '../api';

export const useAuthStore = defineStore('auth', {
  state: () => {
    let user = null;
    try {
      const stored = localStorage.getItem('user');
      if (stored) user = JSON.parse(stored);
    } catch (e) {
      user = null;
    }

    let actualUser = null;
    try {
      const storedActual = localStorage.getItem('actual_user');
      if (storedActual) actualUser = JSON.parse(storedActual);
      else actualUser = user;
    } catch (e) {
      actualUser = user;
    }



    return {
      user,
      actualUser,
      token: localStorage.getItem('token') || '',
      availableRoles: [
        { role: 'Super Admin', label: 'Super Admin (All branches)', email: 'admin@goldencrust.com' },
      ],
    };
  },

  getters: {
    isAuthenticated: (state) => Boolean(state.user && state.token),
    isSuperAdmin: (state) => state.user?.role === 'Super Admin',
    isMainManager: (state) => state.user?.role === 'Main Branch Manager',
    isBranchManager: (state) => ['Branch Manager', 'Branch Admin'].includes(state.user?.role),
    isBranchAdmin: (state) => ['Branch Manager', 'Branch Admin'].includes(state.user?.role),
    isBranchSeller: (state) => ['Branch Seller', 'Branch Seller / Cashier', 'Cashier'].includes(state.user?.role),
    isServant: (state) => state.user?.role === 'Servant',
    isDeliveryStaff: (state) => state.user?.role === 'Delivery Staff',
    isBranchStaff: (state) => ['Branch Staff', 'Branch Seller', 'Branch Seller / Cashier', 'Cashier', 'Servant', 'Delivery Staff'].includes(state.user?.role),
    isCustomer: (state) => state.user?.role === 'Customer',
    isStaff: (state) => Boolean(state.user && state.user.role !== 'Customer'),

    // STRICT ROLE CONTROL:
    // "Simulate Persona" is ONLY accessible when authentic user is Super Admin!
    // Regular branch admins, managers, and staff CANNOT simulate personas.
    canSimulatePersona: (state) => {
      const realRole = state.actualUser?.role || state.user?.role;
      return realRole === 'Super Admin';
    },

    isSimulating: (state) => {
      if (!state.actualUser || !state.user) return false;
      return state.actualUser.role === 'Super Admin' && state.user.role !== 'Super Admin';
    },
  },

  actions: {
    async switchRole(roleItem) {
      if (!this.canSimulatePersona) {
        console.warn('Access denied: Only Super Admin can simulate other personas.');
        return;
      }

      try {
        const res = await api.post('/auth/switch-role', {
          role: roleItem.role,
          branch_code: roleItem.branch_code || null,
        });
        this.token = res.data.token;
        this.user = res.data.user;
        localStorage.setItem('token', this.token);
        localStorage.setItem('user', JSON.stringify(this.user));
        // Preserve original actual_user as Super Admin in localStorage
        if (!localStorage.getItem('actual_user') && this.actualUser) {
          localStorage.setItem('actual_user', JSON.stringify(this.actualUser));
        }
      } catch (err) {
        console.error('Role switch error', err);
      }
    },

    async exitSimulation() {
      if (this.actualUser && this.actualUser.role === 'Super Admin') {
        await this.switchRole({ role: 'Super Admin', email: this.actualUser.email });
      }
    },

    setAuth(token, user) {
      this.token = token;
      this.user = user;
      this.actualUser = user;
      localStorage.setItem('token', token);
      localStorage.setItem('user', JSON.stringify(user));
      localStorage.setItem('actual_user', JSON.stringify(user));
    },

    setUser(user, token) {
      this.user = user;
      if (!this.actualUser) {
        this.actualUser = user;
        localStorage.setItem('actual_user', JSON.stringify(user));
      }
      if (token) {
        this.token = token;
        localStorage.setItem('token', token);
      }
      localStorage.setItem('user', JSON.stringify(user));
    },

    async fetchAvailableRoles() {
      if (!this.canSimulatePersona) return;
      try {
        const res = await api.get('/auth/users');
        const roles = [
          { role: 'Super Admin', label: 'Super Admin (All branches)', email: this.actualUser?.email || 'admin@goldencrust.com' }
        ];
        if (Array.isArray(res.data)) {
          res.data
            .filter((u) => u.role !== 'Super Admin' && u.role !== 'Customer')
            .forEach((u) => {
              roles.push({
                role: u.role,
                label: `${u.role} (${u.full_name}${u.branch?.branch_name ? ' · ' + u.branch.branch_name : ''})`,
                email: u.email,
                branch_code: u.branch?.branch_code || null,
              });
            });
        }
        this.availableRoles = roles;
      } catch (e) {
        this.availableRoles = [
          { role: 'Super Admin', label: 'Super Admin (All branches)', email: this.actualUser?.email || 'admin@goldencrust.com' }
        ];
      }
    },

    logout() {
      this.user = null;
      this.actualUser = null;
      this.token = '';
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      localStorage.removeItem('actual_user');
    }
  }
});
