import { defineStore } from 'pinia';
import api from '../api';
import { useCartStore } from './cart';

export const useBranchStore = defineStore('branch', {
  state: () => {
    const saved = parseInt(localStorage.getItem('selectedBranchId')) || parseInt(localStorage.getItem('cart_branch_id')) || 2;
    return {
      branches: [],
      selectedBranchId: saved,
      loading: false,
      isPickerModalOpen: false,
    };
  },

  getters: {
    selectedBranch: (state) => {
      if (!Array.isArray(state.branches) || state.branches.length === 0) return null;
      return state.branches.find((b) => b.branch_id === state.selectedBranchId) || state.branches[0] || null;
    },
    currentBranchName: (state) => {
      const b = state.branches.find((br) => br.branch_id === state.selectedBranchId);
      return b?.branch_name || 'Bole';
    },
  },

  actions: {
    async fetchBranches() {
      this.loading = true;
      try {
        const res = await api.get('/branches');
        this.branches = res.data || [];
        if (this.branches.length > 0) {
          const exists = this.branches.some((b) => b.branch_id === this.selectedBranchId);
          if (!exists) {
            this.selectedBranchId = this.branches[0].branch_id;
            localStorage.setItem('selectedBranchId', String(this.selectedBranchId));
          }
        }
      } catch (err) {
        console.error('Failed to load branches', err);
      } finally {
        this.loading = false;
      }
    },

    setSelectedBranch(branchId) {
      const numId = Number(branchId);
      this.selectedBranchId = numId;
      localStorage.setItem('selectedBranchId', String(numId));
      localStorage.setItem('cart_branch_id', String(numId));

      // Revalidate cart items and prices for the newly selected branch
      const cartStore = useCartStore();
      cartStore.branchId = numId;
      cartStore.save();
      cartStore.revalidateForBranch(numId);
    },

    openPicker() {
      this.isPickerModalOpen = true;
    },

    closePicker() {
      this.isPickerModalOpen = false;
    },

    togglePicker() {
      this.isPickerModalOpen = !this.isPickerModalOpen;
    },
  },
});
