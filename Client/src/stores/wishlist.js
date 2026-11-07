import { defineStore } from 'pinia';
import api from '../api';

export const useWishlistStore = defineStore('wishlist', {
  state: () => {
    let initialIds = [];
    try {
      const stored = localStorage.getItem('sweetbakes_wishlist');
      if (stored) {
        const parsed = JSON.parse(stored);
        if (Array.isArray(parsed)) {
          initialIds = parsed.map(id => Number(id)).filter(id => !isNaN(id) && id > 0);
        }
      }
    } catch (e) {
      initialIds = [];
    }

    return {
      productIds: initialIds,
      items: [],
      loading: false,
      initialized: false,
    };
  },

  getters: {
    count: (state) => state.productIds.length,
    isWishlisted: (state) => (productId) => {
      const id = Number(productId);
      return state.productIds.includes(id);
    },
    hasItems: (state) => state.productIds.length > 0,
  },

  actions: {
    saveLocal() {
      try {
        localStorage.setItem('sweetbakes_wishlist', JSON.stringify(this.productIds));
      } catch (e) {
        console.error('Failed saving wishlist to localStorage', e);
      }
    },

    async init() {
      if (this.initialized) return;
      this.initialized = true;
      await this.fetchWishlist();
    },

    async fetchWishlist() {
      const token = localStorage.getItem('token');
      this.loading = true;

      try {
        if (token) {
          // If we have guest items in localStorage, sync them first to the account
          if (this.productIds.length > 0) {
            try {
              const syncRes = await api.post('/wishlist/sync', {
                product_ids: this.productIds,
              });
              if (syncRes.data?.product_ids) {
                this.productIds = syncRes.data.product_ids.map(id => Number(id));
                this.items = syncRes.data.products || [];
                this.saveLocal();
                return;
              }
            } catch (syncErr) {
              // Proceed to regular index fetch if sync fails
            }
          }

          // Fetch authenticated wishlist from MySQL
          const res = await api.get('/wishlist');
          if (Array.isArray(res.data?.product_ids)) {
            const dbIds = res.data.product_ids.map(id => Number(id));
            // Keep union of local & DB items so favorites never vanish on refresh
            const merged = Array.from(new Set([...this.productIds, ...dbIds]));
            this.productIds = merged;
            this.items = res.data.products || [];
            this.saveLocal();

            // If there were local IDs not yet in DB, sync them up
            const missingInDb = merged.filter(id => !dbIds.includes(id));
            if (missingInDb.length > 0) {
              api.post('/wishlist/sync', { product_ids: missingInDb }).catch(() => {});
            }
          }
        } else {
          // Guest mode: If there are IDs in localStorage, fetch product details
          if (this.productIds.length > 0) {
            try {
              const res = await api.post('/wishlist/guest-products', {
                product_ids: this.productIds,
              });
              if (Array.isArray(res.data?.products)) {
                this.items = res.data.products;
              }
            } catch (guestErr) {
              // Network error or offline
            }
          } else {
            this.items = [];
          }
        }
      } catch (err) {
        console.error('Failed to fetch wishlist', err);
      } finally {
        this.loading = false;
      }
    },

    async toggleWishlist(productOrId) {
      const productId = Number(typeof productOrId === 'object' && productOrId !== null
        ? (productOrId.product_id || productOrId.id)
        : productOrId);

      if (isNaN(productId) || productId <= 0) return { wishlisted: false };

      const isCurrentlyWishlisted = this.productIds.includes(productId);

      if (isCurrentlyWishlisted) {
        // Remove
        this.productIds = this.productIds.filter(id => id !== productId);
        this.items = this.items.filter(item => (Number(item.product_id) !== productId));
      } else {
        // Add
        this.productIds.push(productId);
        if (typeof productOrId === 'object' && productOrId !== null) {
          this.items.unshift(productOrId);
        }
      }

      // 1. Immediately persist to localStorage so page refresh never loses it!
      this.saveLocal();

      // 2. If authenticated, sync change directly with backend MySQL
      const token = localStorage.getItem('token');
      if (token) {
        try {
          const res = await api.post('/wishlist/toggle', { product_id: productId });
          if (Array.isArray(res.data?.product_ids)) {
            this.productIds = res.data.product_ids.map(id => Number(id));
            this.saveLocal();
          }
        } catch (apiErr) {
          console.warn('Could not sync wishlist toggle with backend', apiErr);
        }
      }

      return {
        wishlisted: !isCurrentlyWishlisted,
        productId,
      };
    },

    async removeFromWishlist(productId) {
      const id = Number(productId);
      if (this.productIds.includes(id)) {
        return await this.toggleWishlist(id);
      }
      return { wishlisted: false };
    },

    clear() {
      this.productIds = [];
      this.items = [];
      try {
        localStorage.removeItem('sweetbakes_wishlist');
      } catch (e) {}
    },
  },
});

