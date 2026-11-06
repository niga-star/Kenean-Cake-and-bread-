import { defineStore } from 'pinia';
import api from '../api';

export const useCartStore = defineStore('cart', {
  state: () => {
    let storedItems = [];
    try {
      const parsed = JSON.parse(localStorage.getItem('cart_items'));
      if (Array.isArray(parsed)) {
        storedItems = parsed.map((item) => {
          const avail = Number(item.available ?? item.available_stock ?? 50);
          return {
            ...item,
            product_id: Number(item.product_id),
            price: Number(item.price) || 0,
            quantity: Math.max(1, Number(item.quantity) || 1),
            available: avail > 0 ? avail : 50,
            available_stock: avail > 0 ? avail : 50,
            isSoldOut: Boolean(item.isSoldOut && avail <= 0),
          };
        });
      }
    } catch {
      storedItems = [];
    }

    const savedBranch = parseInt(localStorage.getItem('cart_branch_id')) || parseInt(localStorage.getItem('selectedBranchId')) || 2;

    return {
      items: storedItems,
      branchId: savedBranch,
      fulfillmentMethod: localStorage.getItem('cart_fulfillment') || 'Delivery', // 'Delivery' or 'Pickup'
      selectedZoneId: parseInt(localStorage.getItem('cart_zone_id')) || null,
      deliveryFee: parseFloat(localStorage.getItem('cart_delivery_fee')) || 60,
      revalidationWarnings: [],
      isRevalidating: false,
      isDrawerOpen: false,
      appliedPromo: (() => {
        try {
          return JSON.parse(localStorage.getItem('cart_applied_promo')) || null;
        } catch {
          return null;
        }
      })(),
    };
  },

  getters: {
    totalItems: (state) => state.items.reduce((sum, item) => sum + (Number(item.quantity) || 0), 0),
    subtotal: (state) => state.items.reduce((sum, item) => sum + ((Number(item.price) || 0) * (Number(item.quantity) || 0)), 0),
    discountAmount: (state) => {
      if (!state.appliedPromo) return 0;
      const sub = state.items.reduce((sum, item) => sum + ((Number(item.price) || 0) * (Number(item.quantity) || 0)), 0);
      if (state.appliedPromo.discount_type === 'percentage') {
        return Math.round(((sub * Number(state.appliedPromo.discount_value)) / 100) * 100) / 100;
      }
      return Math.min(sub, Number(state.appliedPromo.discount_value) || 0);
    },
    total: (state) => {
      const delivery = state.fulfillmentMethod === 'Delivery' ? (state.subtotal >= 1000 ? 0 : state.deliveryFee) : 0;
      const discount = state.appliedPromo ? (
        state.appliedPromo.discount_type === 'percentage'
          ? Math.round(((state.subtotal * Number(state.appliedPromo.discount_value)) / 100) * 100) / 100
          : Math.min(state.subtotal, Number(state.appliedPromo.discount_value) || 0)
      ) : 0;
      return Math.max(0, (state.subtotal - discount) + delivery);
    },
    hasSoldOutItems: (state) => state.items.some((item) => item.isSoldOut === true || (item.available !== undefined && item.available !== null && Number(item.available) <= 0)),
  },

  actions: {
    addItem(product, branchId = null, requestedQty = null) {
      if (!product || !product.product_id) {
        return { success: false, message: 'Invalid product item.' };
      }

      if (branchId && !isNaN(branchId)) {
        this.branchId = Number(branchId);
      } else if (!this.branchId || isNaN(this.branchId)) {
        this.branchId = parseInt(localStorage.getItem('selectedBranchId')) || 2;
      }

      const qty = Math.max(1, Number(requestedQty ?? product.quantity ?? 1) || 1);

      // Extract stock flexibly across API and view structures
      let available = 50;
      if (typeof product.available === 'number') {
        available = product.available;
      } else if (typeof product.available_stock === 'number') {
        available = product.available_stock;
      } else if (typeof product.on_hand === 'number') {
        available = product.on_hand;
      } else if (typeof product.quantity_available === 'number') {
        available = product.quantity_available;
      } else if (product.available !== undefined && product.available !== null && !isNaN(Number(product.available))) {
        available = Number(product.available);
      } else if (product.available_stock !== undefined && product.available_stock !== null && !isNaN(Number(product.available_stock))) {
        available = Number(product.available_stock);
      }

      if (available <= 0 && product.isSoldOut) {
        return {
          success: false,
          reason: 'sold_out',
          message: `${product.product_name} is sold out at this branch.`,
        };
      }

      // Sensible fallback if stock was missing or negative without soldOut flag
      if (available <= 0) {
        available = 25;
      }

      const price = Number(product.resolved_price ?? product.price ?? product.base_price ?? 0);
      let imageUrl = product.image_url;
      if (!imageUrl && product.images && product.images.length > 0) {
        imageUrl = product.images[0].image_url;
      }
      if (!imageUrl) {
        imageUrl = 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=600&q=80';
      }

      const existing = this.items.find((i) => i.product_id === product.product_id);
      if (existing) {
        if (existing.quantity + qty > available) {
          existing.quantity = available;
          existing.available = available;
          existing.available_stock = available;
          existing.isSoldOut = false;
          this.save();
          return {
            success: true,
            reason: 'capped',
            message: `Only ${available} available in stock. Quantity adjusted to ${available}.`,
          };
        }
        existing.quantity += qty;
        existing.available = available;
        existing.available_stock = available;
        existing.isSoldOut = false;
      } else {
        const initialQty = Math.min(qty, available);
        this.items.push({
          product_id: product.product_id,
          product_name: product.product_name,
          price: price,
          quantity: initialQty,
          image_url: imageUrl,
          available: available,
          available_stock: available,
          isSoldOut: false,
        });
      }

      this.save();
      this.isDrawerOpen = true;
      return { success: true };
    },

    openDrawer() {
      this.isDrawerOpen = true;
    },

    closeDrawer() {
      this.isDrawerOpen = false;
    },

    toggleDrawer() {
      this.isDrawerOpen = !this.isDrawerOpen;
    },

    updateQuantity(productId, quantity) {
      const item = this.items.find((i) => i.product_id === productId);
      if (item) {
        if (quantity <= 0) {
          this.removeItem(productId);
          return { success: true, removed: true };
        }

        const stock = Number(item.available ?? item.available_stock ?? 50);
        if (quantity > stock) {
          item.quantity = stock;
          item.available = stock;
          item.available_stock = stock;
          this.save();
          return {
            success: false,
            message: `Requested quantity exceeds available stock (${stock} available). Adjusted to maximum available.`,
          };
        }

        item.quantity = quantity;
        item.available = stock;
        item.available_stock = stock;
        item.isSoldOut = false;
        this.save();
        return { success: true };
      }
      return { success: false };
    },

    removeItem(productId) {
      this.items = this.items.filter((i) => i.product_id !== productId);
      this.save();
    },

    setPromo(promo) {
      this.appliedPromo = promo;
      if (promo) {
        localStorage.setItem('cart_applied_promo', JSON.stringify(promo));
      } else {
        localStorage.removeItem('cart_applied_promo');
      }
    },

    removePromo() {
      this.appliedPromo = null;
      localStorage.removeItem('cart_applied_promo');
    },

    clearCart() {
      this.items = [];
      this.appliedPromo = null;
      localStorage.removeItem('cart_applied_promo');
      this.revalidationWarnings = [];
      this.save();
    },

    setFulfillment(method, fee = 0, zoneId = null) {
      this.fulfillmentMethod = method;
      this.deliveryFee = method === 'Delivery' ? fee : 0;
      this.selectedZoneId = zoneId;
      localStorage.setItem('cart_fulfillment', method);
      localStorage.setItem('cart_delivery_fee', this.deliveryFee);
      if (zoneId) {
        localStorage.setItem('cart_zone_id', zoneId);
      } else {
        localStorage.removeItem('cart_zone_id');
      }
    },

    dismissWarning(index) {
      this.revalidationWarnings.splice(index, 1);
    },

    clearWarnings() {
      this.revalidationWarnings = [];
    },

    async revalidateForBranch(newBranchId) {
      if (!newBranchId) return;
      this.branchId = Number(newBranchId);
      this.save();

      if (this.items.length === 0) return;

      this.isRevalidating = true;
      const warnings = [];

      try {
        const res = await api.get(`/catalog/products?branch_id=${newBranchId}`);
        const branchProducts = res.data || [];

        for (const item of this.items) {
          const match = branchProducts.find((p) => p.product_id === item.product_id);

          if (!match) {
            item.available = 0;
            item.available_stock = 0;
            item.isSoldOut = true;
            warnings.push({
              product_id: item.product_id,
              product_name: item.product_name,
              message: `${item.product_name} is not available at the selected branch.`,
              available: 0,
              requested: item.quantity,
              suggestions: await this.fetchAlternativeBranches(item.product_id, item.quantity, newBranchId),
            });
            continue;
          }

          const stock = Number(match.available ?? match.available_stock ?? match.on_hand ?? 50);
          item.price = Number(match.resolved_price || match.base_price || item.price);
          item.available = stock;
          item.available_stock = stock;
          item.isSoldOut = stock <= 0;

          if (stock <= 0) {
            warnings.push({
              product_id: item.product_id,
              product_name: item.product_name,
              message: `${item.product_name} is sold out at this branch.`,
              available: 0,
              requested: item.quantity,
              suggestions: await this.fetchAlternativeBranches(item.product_id, item.quantity, newBranchId),
            });
          } else if (item.quantity > stock) {
            const requested = item.quantity;
            item.quantity = stock;
            warnings.push({
              product_id: item.product_id,
              product_name: item.product_name,
              message: `Requested ${requested} units of ${item.product_name}, but only ${stock} available. Quantity adjusted to ${stock}.`,
              available: stock,
              requested: requested,
              suggestions: await this.fetchAlternativeBranches(item.product_id, requested, newBranchId),
            });
          }
        }

        this.revalidationWarnings = warnings;
        this.save();
      } catch (err) {
        console.error('Error revalidating cart for branch', err);
      } finally {
        this.isRevalidating = false;
      }
    },

    async fetchAlternativeBranches(productId, neededQty, currentBranchId) {
      try {
        const res = await api.get(`/catalog/products/${productId}/branch-availability`);
        const branches = res.data?.branches || [];
        return branches
          .filter((b) => b.branch_id !== currentBranchId && b.available > 0)
          .map((b) => ({
            branch_id: b.branch_id,
            branch_name: b.branch_name,
            city: b.city,
            available: b.available,
            price: b.price,
            canFulfillFully: b.available >= neededQty,
          }));
      } catch {
        return [];
      }
    },

    save() {
      localStorage.setItem('cart_items', JSON.stringify(this.items));
      localStorage.setItem('cart_branch_id', this.branchId);
    },
  },
});
