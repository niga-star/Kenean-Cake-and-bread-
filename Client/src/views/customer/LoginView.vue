<template>
  <div class="min-h-[75vh] flex items-center justify-center py-12 font-sans selection:bg-[#FCE7F0] selection:text-[#DE1B54] text-[#29231F] dark:text-[#FAF3EE]">
    <div class="sweetbakes-container flex items-center justify-center">
      <div class="max-w-md w-full bg-white dark:bg-[#1E120B] rounded-3xl border border-gray-200 dark:border-[#382318] shadow-lg p-6 sm:p-10 space-y-6">
        <!-- Header -->
        <div class="text-center space-y-2">
          <div class="w-14 h-14 rounded-2xl bg-rose-50 dark:bg-[#341822] border border-rose-200 dark:border-[#58263A] flex items-center justify-center mx-auto text-2xl shadow-xs text-[#DE1B54]">
            🧁
          </div>
          <h2 class="text-2xl sm:text-3xl font-serif font-black text-[#2A1810] dark:text-[#FAF3EE]">
            {{ localeStore.t('welcome_back') }}
          </h2>
          <p class="text-xs text-[#6E5D53] dark:text-[#C5B7AF]">
            {{ localeStore.t('login_header_desc') }}
          </p>
        </div>

        <!-- Error / Success Alert -->
        <div v-if="errorMessage" class="p-3 bg-[#FAF0F0] dark:bg-rose-950/40 border border-[#E8C4C4] dark:border-rose-900/60 rounded-2xl text-xs text-[#B84B42] font-semibold flex items-center space-x-2">
          <span>⚠️</span>
          <span>{{ errorMessage }}</span>
        </div>

        <div v-if="successMessage" class="p-3 bg-[#F2F7F4] dark:bg-emerald-950/40 border border-[#C8DFD2] dark:border-emerald-800/60 rounded-2xl text-xs text-[#387A54] dark:text-emerald-300 font-semibold flex items-center space-x-2">
          <span>✓</span>
          <span>{{ successMessage }}</span>
        </div>

        <!-- Login Form -->
        <form @submit.prevent="handleLogin" class="space-y-4">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-[#2A1810] dark:text-[#FAF3EE] mb-1.5">
              {{ localeStore.t('email_label') }}
            </label>
            <input
              v-model="email"
              type="email"
              placeholder="you@example.com"
              class="w-full bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] focus:border-[#DE1B54] focus:bg-white dark:focus:bg-[#1E120B] rounded-xl px-4 py-2.5 text-xs text-[#29231F] dark:text-[#FAF3EE] focus:outline-none transition min-h-[40px]"
              required
            />
          </div>

          <div>
            <div class="flex items-center justify-between mb-1.5">
              <label class="block text-xs font-bold uppercase tracking-wider text-[#2A1810] dark:text-[#FAF3EE]">
                {{ localeStore.t('password_label') }}
              </label>
              <button
                type="button"
                @click="showForgotModal = true"
                class="text-[11px] text-[#DE1B54] hover:underline font-semibold cursor-pointer"
              >
                {{ localeStore.t('forgot_password_link') }}
              </button>
            </div>
            <div class="relative">
              <input
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="••••••••"
                class="w-full bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] focus:border-[#DE1B54] focus:bg-white dark:focus:bg-[#1E120B] rounded-xl px-4 py-2.5 text-xs text-[#29231F] dark:text-[#FAF3EE] focus:outline-none transition min-h-[40px]"
                required
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-3 top-2.5 text-[#786B60] dark:text-[#C5B7AF] hover:text-[#2A1810] text-xs cursor-pointer"
              >
                {{ showPassword ? (localeStore.isAmharic ? 'ደብቅ' : 'Hide') : (localeStore.isAmharic ? 'አሳይ' : 'Show') }}
              </button>
            </div>
          </div>

          <div class="flex items-center justify-between pt-1">
            <label class="flex items-center text-xs text-[#786B60] dark:text-[#C5B7AF] cursor-pointer">
              <input
                type="checkbox"
                v-model="rememberMe"
                class="rounded text-[#DE1B54] focus:ring-[#DE1B54]"
              />
              <span class="ml-2">{{ localeStore.t('remember_me') }}</span>
            </label>
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full py-3.5 min-h-[44px] bg-[#DE1B54] hover:bg-[#C51345] disabled:opacity-50 text-white text-xs font-bold uppercase tracking-wider rounded-2xl shadow-md hover:shadow-lg transition active:scale-98 cursor-pointer"
          >
            {{ loading ? localeStore.t('signing_in_btn') : localeStore.t('sign_in_sweetbakes') }}
          </button>
        </form>

        <!-- Register Link -->
        <div class="text-center text-xs text-[#786B60] dark:text-[#C5B7AF] pt-2 border-t border-gray-100 dark:border-[#382318]">
          {{ localeStore.t('dont_have_account') }}
          <router-link to="/register" class="font-bold text-[#DE1B54] hover:underline ml-1">
            {{ localeStore.t('create_account') }}
          </router-link>
        </div>
      </div>
    </div>

    <!-- Forgot Password Modal -->
    <div
      v-if="showForgotModal"
      class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4"
    >
      <div class="bg-white dark:bg-[#1E120B] rounded-3xl max-w-sm w-full p-6 space-y-4 border border-gray-200 dark:border-[#382318] shadow-2xl">
        <div class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-[#382318]">
          <h3 class="font-serif font-bold text-base text-[#2A1810] dark:text-[#FAF3EE]">{{ localeStore.t('pwd_recovery_title') }}</h3>
          <button @click="showForgotModal = false" class="text-sm text-[#786B60] dark:text-[#C5B7AF] hover:text-[#2A1810] cursor-pointer">✕</button>
        </div>

        <p class="text-xs text-[#6E5D53] dark:text-[#C5B7AF] leading-relaxed">
          {{ localeStore.t('pwd_recovery_desc') }}
        </p>

        <form @submit.prevent="handleForgotPassword" class="space-y-3">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-[#2A1810] dark:text-[#FAF3EE] mb-1">{{ localeStore.t('email_label') }}</label>
            <input
              v-model="forgotEmail"
              type="email"
              required
              placeholder="you@example.com"
              class="w-full bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] focus:border-[#DE1B54] rounded-xl px-3.5 py-2 text-xs text-[#2A1810] dark:text-[#FAF3EE] focus:outline-none"
            />
          </div>

          <div v-if="forgotMessage" class="p-2.5 bg-[#F2F7F4] dark:bg-emerald-950/40 border border-[#C8DFD2] dark:border-emerald-800 rounded-xl text-xs text-[#387A54] dark:text-emerald-300">
            {{ forgotMessage }}
          </div>

          <button
            type="submit"
            :disabled="forgotLoading"
            class="w-full py-2.5 min-h-[40px] bg-[#DE1B54] hover:bg-[#C51345] text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer"
          >
            {{ forgotLoading ? (localeStore.isAmharic ? 'በመላክ ላይ...' : 'Dispatching...') : localeStore.t('send_reset_token') }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { useToastStore } from '../../stores/toast';
import { useWishlistStore } from '../../stores/wishlist';
import { useLocaleStore } from '../../stores/locale';
import api from '../../api';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const toastStore = useToastStore();
const wishlistStore = useWishlistStore();
const localeStore = useLocaleStore();

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
    wishlistStore.fetchWishlist();
    toastStore.success(`Welcome back, ${res.data.user.full_name}!`);

    if (res.data.user.role === 'Branch Staff') {
      router.push('/admin/orders');
    } else if (['Super Admin', 'Main Branch Manager', 'Branch Admin'].includes(res.data.user.role)) {
      router.push('/admin/overview');
    } else {
      const redirectPath = route.query.redirect || '/account';
      router.push(redirectPath);
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
</script>
