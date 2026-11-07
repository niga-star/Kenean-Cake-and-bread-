<template>
  <div class="min-h-[80vh] flex items-center justify-center py-12 font-sans selection:bg-[#FCE7F0] selection:text-[#DE1B54] text-[#29231F] dark:text-[#FAF3EE]">
    <div class="sweetbakes-container flex items-center justify-center">
      <div class="max-w-md w-full bg-white dark:bg-[#1E120B] rounded-3xl border border-gray-200 dark:border-[#382318] shadow-lg p-6 sm:p-10 space-y-6">
      <!-- Header -->
      <div class="text-center space-y-2">
        <div class="w-14 h-14 rounded-2xl bg-rose-50 dark:bg-[#341822] border border-rose-200 dark:border-[#58263A] flex items-center justify-center mx-auto text-2xl shadow-xs text-[#DE1B54]">
          ✨
        </div>
        <h2 class="text-2xl sm:text-3xl font-serif font-black text-[#2A1810] dark:text-[#FAF3EE]">
          {{ localeStore.t('join_sweetbakes_title') }}
        </h2>
        <p class="text-xs text-[#6E5D53] dark:text-[#C5B7AF]">
          {{ localeStore.t('join_sweetbakes_desc') }}
        </p>
      </div>

      <!-- Error Alert -->
      <div v-if="errorMessage" class="p-3 bg-[#FAF0F0] dark:bg-rose-950/40 border border-[#E8C4C4] dark:border-rose-900/60 rounded-2xl text-xs text-[#B84B42] font-semibold flex items-center space-x-2">
        <span>⚠️</span>
        <span>{{ errorMessage }}</span>
      </div>

      <!-- Registration Form -->
      <form @submit.prevent="handleRegister" class="space-y-4">
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-[#2A1810] dark:text-[#FAF3EE] mb-1">
            {{ localeStore.t('full_name') }}
          </label>
          <input
            v-model="form.full_name"
            type="text"
            :placeholder="localeStore.isAmharic ? 'ሙሉ ስምዎን ያስገቡ' : 'Enter your full name'"
            class="w-full bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] focus:border-[#DE1B54] focus:bg-white dark:focus:bg-[#1E120B] rounded-xl px-4 py-2.5 text-xs text-[#29231F] dark:text-[#FAF3EE] focus:outline-none transition min-h-[40px]"
            required
          />
        </div>

        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-[#2A1810] dark:text-[#FAF3EE] mb-1">
            {{ localeStore.t('email_label') }}
          </label>
          <input
            v-model="form.email"
            type="email"
            placeholder="you@example.com"
            class="w-full bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] focus:border-[#DE1B54] focus:bg-white dark:focus:bg-[#1E120B] rounded-xl px-4 py-2.5 text-xs text-[#29231F] dark:text-[#FAF3EE] focus:outline-none transition min-h-[40px]"
            required
          />
        </div>

        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-[#2A1810] dark:text-[#FAF3EE] mb-1">
            {{ localeStore.t('phone_req') }}
          </label>
          <input
            v-model="form.phone"
            type="text"
            placeholder="+251 9XX XXX XXX or 09XX XXX XXX"
            class="w-full bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] focus:border-[#DE1B54] focus:bg-white dark:focus:bg-[#1E120B] rounded-xl px-4 py-2.5 text-xs text-[#29231F] dark:text-[#FAF3EE] focus:outline-none transition min-h-[40px]"
            required
          />
          <span class="text-[10px] text-[#786B60] dark:text-[#C5B7AF]">{{ localeStore.t('phone_courier_notice') }}</span>
        </div>

        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-[#2A1810] dark:text-[#FAF3EE] mb-1">
            {{ localeStore.t('password_min_6') }}
          </label>
          <input
            v-model="form.password"
            type="password"
            placeholder="••••••••"
            class="w-full bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] focus:border-[#DE1B54] focus:bg-white dark:focus:bg-[#1E120B] rounded-xl px-4 py-2.5 text-xs text-[#29231F] dark:text-[#FAF3EE] focus:outline-none transition min-h-[40px]"
            required
            minlength="6"
          />
        </div>

        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-[#2A1810] dark:text-[#FAF3EE] mb-1">
            {{ localeStore.t('confirm_password_label') }}
          </label>
          <input
            v-model="confirmPassword"
            type="password"
            placeholder="••••••••"
            class="w-full bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] focus:border-[#DE1B54] focus:bg-white dark:focus:bg-[#1E120B] rounded-xl px-4 py-2.5 text-xs text-[#29231F] dark:text-[#FAF3EE] focus:outline-none transition min-h-[40px]"
            required
          />
        </div>

        <div class="flex items-start pt-1">
          <input
            id="terms"
            type="checkbox"
            required
            class="mt-0.5 rounded text-[#DE1B54] focus:ring-[#DE1B54]"
          />
          <label for="terms" class="ml-2 text-xs text-[#786B60] dark:text-[#C5B7AF] leading-relaxed">
            <template v-if="localeStore.isAmharic">
              በስዊትቤክስ <router-link to="/about" class="text-[#DE1B54] underline">የአገልግሎት ውሎች</router-link> እና <router-link to="/about" class="text-[#DE1B54] underline">የትኩስነት ዋስትና</router-link> እስማማለሁ።
            </template>
            <template v-else>
              I agree to SweetBakes <router-link to="/about" class="text-[#DE1B54] underline">Terms of Service</router-link> and <router-link to="/about" class="text-[#DE1B54] underline">Freshness Guarantee</router-link>.
            </template>
          </label>
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full py-3.5 min-h-[44px] bg-[#DE1B54] hover:bg-[#C51345] disabled:opacity-50 text-white text-xs font-bold uppercase tracking-wider rounded-2xl shadow-md hover:shadow-lg transition active:scale-98 cursor-pointer"
        >
          {{ loading ? localeStore.t('creating_account_btn') : localeStore.t('create_account_btn') }}
        </button>
      </form>

      <!-- Login Link -->
      <div class="text-center text-xs text-[#786B60] dark:text-[#C5B7AF] pt-2 border-t border-gray-100 dark:border-[#382318]">
        {{ localeStore.t('already_have_account') }}
        <router-link to="/login" class="font-bold text-[#DE1B54] hover:underline ml-1">
          {{ localeStore.t('sign_in') }}
        </router-link>
      </div>
    </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { useToastStore } from '../../stores/toast';
import { useWishlistStore } from '../../stores/wishlist';
import { useLocaleStore } from '../../stores/locale';
import api from '../../api';

const router = useRouter();
const authStore = useAuthStore();
const toastStore = useToastStore();
const wishlistStore = useWishlistStore();
const localeStore = useLocaleStore();

const form = ref({
  full_name: '',
  email: '',
  phone: '',
  password: '',
});

const confirmPassword = ref('');
const loading = ref(false);
const errorMessage = ref(null);

async function handleRegister() {
  errorMessage.value = null;

  if (form.value.password !== confirmPassword.value) {
    errorMessage.value = 'Passwords do not match.';
    return;
  }

  loading.value = true;
  try {
    const res = await api.post('/auth/register', form.value);
    authStore.setAuth(res.data.token, res.data.user);
    wishlistStore.fetchWishlist();
    toastStore.success(`Welcome to Kenean Cake & Bread, ${res.data.user.full_name}!`);
    router.push('/account');
  } catch (err) {
    errorMessage.value = err.response?.data?.message || err.response?.data?.errors?.phone?.[0] || 'Registration failed. Please check your information.';
  } finally {
    loading.value = false;
  }
}
</script>
