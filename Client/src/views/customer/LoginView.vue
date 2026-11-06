<template>
  <div class="min-h-[75vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 font-sans selection:bg-[#E8D8C4] selection:text-[#553B2D]">
    <div class="max-w-md w-full bg-white rounded-3xl border border-[#E6DED3] shadow-lg p-8 sm:p-10 space-y-6">
      <!-- Header -->
      <div class="text-center space-y-2">
        <div class="w-14 h-14 rounded-2xl bg-[#E8D8C4] border border-[#D5C2AD] flex items-center justify-center mx-auto text-2xl shadow-xs text-[#553B2D]">
          🎂
        </div>
        <h2 class="text-2xl sm:text-3xl font-serif font-black text-[#553B2D]">
          Welcome Back
        </h2>
        <p class="text-xs text-[#786B60]">
          Sign in to your Kenean account to track your bakery orders, manage deliveries, and save favorite creations.
        </p>
      </div>

      <!-- Error / Success Alert -->
      <div v-if="errorMessage" class="p-3 bg-[#FAF0F0] border border-[#E8C4C4] rounded-2xl text-xs text-[#B84B42] font-semibold flex items-center space-x-2">
        <span>⚠️</span>
        <span>{{ errorMessage }}</span>
      </div>

      <div v-if="successMessage" class="p-3 bg-[#F2F7F4] border border-[#C8DFD2] rounded-2xl text-xs text-[#387A54] font-semibold flex items-center space-x-2">
        <span>✓</span>
        <span>{{ successMessage }}</span>
      </div>

      <!-- Login Form -->
      <form @submit.prevent="handleLogin" class="space-y-4">
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-[#553B2D] mb-1.5">
            Email Address
          </label>
          <input
            v-model="email"
            type="email"
            placeholder="you@example.com"
            class="w-full bg-[#F8F3EA] border border-[#E6DED3] focus:border-[#A87552] focus:bg-white rounded-xl px-4 py-2.5 text-xs text-[#29231F] focus:outline-none transition"
            required
          />
        </div>

        <div>
          <div class="flex items-center justify-between mb-1.5">
            <label class="block text-xs font-bold uppercase tracking-wider text-[#553B2D]">
              Password
            </label>
            <button
              type="button"
              @click="showForgotModal = true"
              class="text-[11px] text-[#A87552] hover:underline font-semibold cursor-pointer"
            >
              Forgot password?
            </button>
          </div>
          <div class="relative">
            <input
              v-model="password"
              :type="showPassword ? 'text' : 'password'"
              placeholder="••••••••"
              class="w-full bg-[#F8F3EA] border border-[#E6DED3] focus:border-[#A87552] focus:bg-white rounded-xl px-4 py-2.5 text-xs text-[#29231F] focus:outline-none transition"
              required
            />
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute right-3 top-2.5 text-[#786B60] hover:text-[#29231F] text-xs cursor-pointer"
            >
              {{ showPassword ? 'Hide' : 'Show' }}
            </button>
          </div>
        </div>

        <div class="flex items-center justify-between pt-1">
          <label class="flex items-center text-xs text-[#786B60] cursor-pointer">
            <input
              type="checkbox"
              v-model="rememberMe"
              class="rounded text-[#A87552] focus:ring-[#A87552]"
            />
            <span class="ml-2">Remember me</span>
          </label>
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full py-4 bg-[#553B2D] hover:bg-[#432E23] disabled:opacity-50 text-white text-xs font-bold uppercase tracking-wider rounded-2xl shadow-md hover:shadow-lg transition active:scale-98 cursor-pointer"
        >
          {{ loading ? 'Signing in...' : 'Sign In to Kenean →' }}
        </button>
      </form>

      <!-- Register Link -->
      <div class="text-center text-xs text-[#786B60] pt-2 border-t border-[#E6DED3]">
        Don't have an account yet?
        <router-link to="/register" class="font-bold text-[#A87552] hover:underline ml-1">
          Create an Account
        </router-link>
      </div>

      <!-- Quick Demo Login Helpers -->
      <div class="pt-2 space-y-2">
        <div class="flex items-center justify-between text-[10px] uppercase font-bold tracking-widest text-[#786B60]">
          <span>🔑 Quick Demo Credentials</span>
          <span class="text-[#A87552] font-semibold">Click to auto-fill</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
          <!-- Super Admin -->
          <button
            type="button"
            @click="fillDemo('admin@goldencrust.com', 'password123')"
            class="p-2.5 bg-[#F8F3EA] border border-[#E6DED3] hover:border-[#A87552] rounded-xl text-left transition hover:shadow-xs cursor-pointer group"
          >
            <div class="flex items-center justify-between">
              <span class="font-bold text-[#553B2D]">Super Admin</span>
              <span class="text-[9px] bg-[#553B2D] text-white px-1.5 py-0.2 rounded-full font-semibold">Full Access</span>
            </div>
            <div class="text-[10px] text-[#A87552] font-mono mt-0.5 truncate">admin@goldencrust.com</div>
          </button>

          <!-- Branch Admin (Bole) -->
          <button
            type="button"
            @click="fillDemo('bole.admin@goldencrust.com', 'password123')"
            class="p-2.5 bg-[#F8F3EA] border border-[#E6DED3] hover:border-[#A87552] rounded-xl text-left transition hover:shadow-xs cursor-pointer group"
          >
            <div class="flex items-center justify-between">
              <span class="font-bold text-[#553B2D]">Branch Admin</span>
              <span class="text-[9px] bg-[#BA5420] text-white px-1.5 py-0.2 rounded-full font-semibold">Bole Branch</span>
            </div>
            <div class="text-[10px] text-[#A87552] font-mono mt-0.5 truncate">bole.admin@goldencrust.com</div>
          </button>

          <!-- Branch Staff (Bole) -->
          <button
            type="button"
            @click="fillDemo('bole.staff@goldencrust.com', 'password123')"
            class="p-2.5 bg-[#F8F3EA] border border-[#E6DED3] hover:border-[#A87552] rounded-xl text-left transition hover:shadow-xs cursor-pointer group"
          >
            <div class="flex items-center justify-between">
              <span class="font-bold text-[#553B2D]">Branch Staff</span>
              <span class="text-[9px] bg-[#7A6B60] text-white px-1.5 py-0.2 rounded-full font-semibold">Orders & Stock</span>
            </div>
            <div class="text-[10px] text-[#A87552] font-mono mt-0.5 truncate">bole.staff@goldencrust.com</div>
          </button>

          <!-- Customer -->
          <button
            type="button"
            @click="fillDemo('customer@gmail.com', 'password123')"
            class="p-2.5 bg-[#F8F3EA] border border-[#E6DED3] hover:border-[#A87552] rounded-xl text-left transition hover:shadow-xs cursor-pointer group"
          >
            <div class="flex items-center justify-between">
              <span class="font-bold text-[#553B2D]">Customer</span>
              <span class="text-[9px] bg-[#E8D8C4] text-[#553B2D] px-1.5 py-0.2 rounded-full font-semibold">Storefront</span>
            </div>
            <div class="text-[10px] text-[#A87552] font-mono mt-0.5 truncate">customer@gmail.com</div>
          </button>
        </div>
      </div>
    </div>

    <!-- Forgot Password Modal -->
    <div
      v-if="showForgotModal"
      class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4"
    >
      <div class="bg-white rounded-3xl max-w-sm w-full p-6 space-y-4 border border-[#E6DED3] shadow-2xl">
        <div class="flex items-center justify-between pb-2 border-b border-[#E6DED3]">
          <h3 class="font-serif font-bold text-base text-[#553B2D]">Password Recovery</h3>
          <button @click="showForgotModal = false" class="text-sm text-[#786B60] hover:text-[#29231F] cursor-pointer">✕</button>
        </div>

        <p class="text-xs text-[#786B60] leading-relaxed">
          Enter your registered email address. We will generate and dispatch a secure 30-minute password reset token.
        </p>

        <form @submit.prevent="handleForgotPassword" class="space-y-3">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-[#553B2D] mb-1">Email</label>
            <input
              v-model="forgotEmail"
              type="email"
              required
              placeholder="you@example.com"
              class="w-full bg-[#F8F3EA] border border-[#E6DED3] focus:border-[#A87552] rounded-xl px-3.5 py-2 text-xs text-[#29231F] focus:outline-none"
            />
          </div>

          <div v-if="forgotMessage" class="p-2.5 bg-[#F2F7F4] border border-[#C8DFD2] rounded-xl text-xs text-[#387A54]">
            {{ forgotMessage }}
          </div>

          <button
            type="submit"
            :disabled="forgotLoading"
            class="w-full py-2.5 bg-[#A87552] hover:bg-[#916140] text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer"
          >
            {{ forgotLoading ? 'Dispatching...' : 'Send Recovery Token' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { useToastStore } from '../../stores/toast';
import api from '../../api';

const router = useRouter();
const authStore = useAuthStore();
const toastStore = useToastStore();

const email = ref('');
const password = ref('');
const showPassword = ref(false);
const rememberMe = ref(true);
const loading = ref(false);
const errorMessage = ref(null);
const successMessage = ref(null);

const showForgotModal = ref(false);
const forgotEmail = ref('');
const forgotLoading = ref(false);
const forgotMessage = ref(null);

async function handleLogin() {
  errorMessage.value = null;
  loading.value = true;
  try {
    const res = await api.post('/auth/login', {
      email: email.value,
      password: password.value,
    });

    authStore.setAuth(res.data.token, res.data.user);
    toastStore.success(`Welcome back, ${res.data.user.full_name}!`);

    if (res.data.user.role === 'Branch Staff') {
      router.push('/admin/orders');
    } else if (['Super Admin', 'Main Branch Manager', 'Branch Admin'].includes(res.data.user.role)) {
      router.push('/admin/overview');
    } else {
      router.push('/account');
    }
  } catch (err) {
    errorMessage.value = err.response?.data?.message || 'Invalid email or password.';
  } finally {
    loading.value = false;
  }
}

async function handleForgotPassword() {
  forgotMessage.value = null;
  forgotLoading.value = true;
  try {
    const res = await api.post('/auth/forgot-password', {
      email: forgotEmail.value,
    });
    forgotMessage.value = res.data?.message || 'Reset token dispatched if account exists.';
    toastStore.info('Password reset instructions dispatched.');
  } catch (err) {
    forgotMessage.value = err.response?.data?.message || 'Failed to dispatch reset token.';
  } finally {
    forgotLoading.value = false;
  }
}

function fillDemo(e, p) {
  email.value = e;
  password.value = p;
}
</script>
