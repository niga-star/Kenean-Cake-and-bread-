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
      <form @submit.prevent="handleRegister" novalidate class="space-y-4">
        <!-- Full Name: Required, Letters only (no numbers / special characters), 3–25 chars -->
        <div class="space-y-1">
          <div class="flex items-center justify-between">
            <label class="block text-xs font-bold uppercase tracking-wider text-[#2A1810] dark:text-[#FAF3EE]">
              {{ localeStore.t('full_name') }} <span class="text-[#DE1B54]">*</span>
            </label>
            <span class="text-[10px] text-[#8C7A6F] dark:text-[#AFA39B]">
              {{ localeStore.isAmharic ? 'ፊደላት ብቻ (3–25)' : 'Letters only (3–25 chars)' }}
            </span>
          </div>
          <div class="relative">
            <input
              v-model="form.full_name"
              type="text"
              maxlength="25"
              @input="onFieldInput('full_name')"
              @blur="onFieldBlur('full_name')"
              :placeholder="localeStore.isAmharic ? 'ሙሉ ስምዎን ያስገቡ' : 'Enter your full name'"
              class="w-full bg-[#FAF7F5] dark:bg-[#2A1810] border rounded-xl px-4 py-2.5 text-xs text-[#29231F] dark:text-[#FAF3EE] focus:outline-none transition min-h-[40px] pr-10"
              :class="getFieldClass('full_name')"
            />
            <!-- Real-time Valid / Invalid Feedback Icon -->
            <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none flex items-center justify-center">
              <span v-if="touched.full_name && !errors.full_name && form.full_name.trim()" class="text-emerald-500 font-bold text-sm">✓</span>
              <span v-else-if="touched.full_name && errors.full_name" class="text-rose-500 font-bold text-sm">!</span>
            </div>
          </div>
          <!-- Validation Error Message below field -->
          <div
            v-if="touched.full_name && errors.full_name"
            class="flex items-center space-x-1.5 text-xs text-rose-600 dark:text-rose-400 pt-0.5 font-semibold animate-in fade-in duration-200"
          >
            <svg class="w-3.5 h-3.5 shrink-0 fill-current" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <span>{{ errors.full_name }}</span>
          </div>
        </div>

        <!-- Email: Required, Valid Email, Must be @gmail.com -->
        <div class="space-y-1">
          <div class="flex items-center justify-between">
            <label class="block text-xs font-bold uppercase tracking-wider text-[#2A1810] dark:text-[#FAF3EE]">
              {{ localeStore.t('email_label') }} <span class="text-[#DE1B54]">*</span>
            </label>
            <span class="text-[10px] text-[#8C7A6F] dark:text-[#AFA39B]">@gmail.com</span>
          </div>
          <div class="relative">
            <input
              v-model="form.email"
              type="email"
              @input="onFieldInput('email')"
              @blur="onFieldBlur('email')"
              placeholder="example@gmail.com"
              class="w-full bg-[#FAF7F5] dark:bg-[#2A1810] border rounded-xl px-4 py-2.5 text-xs text-[#29231F] dark:text-[#FAF3EE] focus:outline-none transition min-h-[40px] pr-10"
              :class="getFieldClass('email')"
            />
            <!-- Real-time Valid / Invalid Feedback Icon -->
            <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none flex items-center justify-center">
              <span v-if="touched.email && !errors.email && form.email.trim()" class="text-emerald-500 font-bold text-sm">✓</span>
              <span v-else-if="touched.email && errors.email" class="text-rose-500 font-bold text-sm">!</span>
            </div>
          </div>
          <!-- Validation Error Message below field -->
          <div
            v-if="touched.email && errors.email"
            class="flex items-center space-x-1.5 text-xs text-rose-600 dark:text-rose-400 pt-0.5 font-semibold animate-in fade-in duration-200"
          >
            <svg class="w-3.5 h-3.5 shrink-0 fill-current" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <span>{{ errors.email }}</span>
          </div>
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

const touched = ref({
  full_name: false,
  email: false,
});

const errors = ref({
  full_name: '',
  email: '',
});

// Regex for letters and spaces only (English and Amharic Unicode letters)
const NAME_LETTERS_ONLY_REGEX = /^[\p{L}\s]+$/u;
// Regex for strict Gmail address
const GMAIL_REGEX = /^[a-zA-Z0-9._%+-]+@gmail\.com$/i;

function validateFullName(value) {
  const val = (value || '').trim();
  if (!val) {
    return localeStore.isAmharic ? 'እባክዎን ሙሉ ስምዎን ያስገቡ።' : 'Full name is required.';
  }
  if (val.length < 3) {
    return localeStore.isAmharic ? 'ሙሉ ስም ቢያንስ 3 ፊደላት መሆን አለበት።' : 'Full name must be at least 3 characters.';
  }
  if (val.length > 25) {
    return localeStore.isAmharic ? 'ሙሉ ስም ከ25 ፊደላት መብለጥ አይችልም።' : 'Full name cannot exceed 25 characters.';
  }
  if (!NAME_LETTERS_ONLY_REGEX.test(val)) {
    return localeStore.isAmharic
      ? 'ሙሉ ስም ፊደላትና ክፍተት ብቻ መያዝ አለበት (ቁጥር ወይም ምልክት አይፈቀድም)።'
      : 'Full name must contain only letters and spaces (no numbers or special characters).';
  }
  return '';
}

function validateEmail(value) {
  const val = (value || '').trim();
  if (!val) {
    return localeStore.isAmharic ? 'እባክዎን የኢሜይል አድራሻ ያስገቡ።' : 'Email address is required.';
  }
  if (!GMAIL_REGEX.test(val)) {
    return localeStore.isAmharic
      ? 'ኢሜይል የጂሜይል (Gmail) ቅርጸት መከተል አለበት (ምሳሌ፡ example@gmail.com)።'
      : 'Email must follow the Gmail format (e.g., example@gmail.com).';
  }
  return '';
}

function onFieldInput(field) {
  if (field === 'full_name') {
    errors.value.full_name = validateFullName(form.value.full_name);
  } else if (field === 'email') {
    errors.value.email = validateEmail(form.value.email);
  }
}

function onFieldBlur(field) {
  touched.value[field] = true;
  if (field === 'full_name') {
    errors.value.full_name = validateFullName(form.value.full_name);
  } else if (field === 'email') {
    errors.value.email = validateEmail(form.value.email);
  }
}

function getFieldClass(field) {
  if (touched.value[field] && errors.value[field]) {
    return 'border-rose-400 dark:border-rose-500 bg-rose-50/40 dark:bg-rose-950/20 focus:border-rose-500';
  }
  if (touched.value[field] && !errors.value[field] && form.value[field]?.trim()) {
    return 'border-emerald-400 dark:border-emerald-500/80 focus:border-emerald-500';
  }
  return 'border-gray-200 dark:border-[#382318] focus:border-[#DE1B54] focus:bg-white dark:focus:bg-[#1E120B]';
}

function validateAll() {
  touched.value.full_name = true;
  touched.value.email = true;

  errors.value.full_name = validateFullName(form.value.full_name);
  errors.value.email = validateEmail(form.value.email);

  return !errors.value.full_name && !errors.value.email;
}

async function handleRegister() {
  errorMessage.value = null;

  if (!validateAll()) {
    toastStore.error(errors.value.full_name || errors.value.email || 'Please correct the highlighted form errors.');
    return;
  }

  if (form.value.password !== confirmPassword.value) {
    errorMessage.value = 'Passwords do not match.';
    toastStore.error('Passwords do not match.');
    return;
  }

  loading.value = true;
  try {
    const res = await api.post('/auth/register', form.value);
    authStore.setAuth(res.data.token, res.data.user);
    wishlistStore.fetchWishlist();
    toastStore.success(`Welcome to SweetBakes Bakery, ${res.data.user.full_name}!`);
    router.push('/account');
  } catch (err) {
    const data = err.response?.data;
    if (data?.errors) {
      if (data.errors.full_name) {
        errors.value.full_name = data.errors.full_name[0];
        touched.value.full_name = true;
      }
      if (data.errors.email) {
        errors.value.email = data.errors.email[0];
        touched.value.email = true;
      }
    }
    errorMessage.value = data?.message || data?.errors?.email?.[0] || data?.errors?.full_name?.[0] || 'Registration failed. Please check your information.';
    toastStore.error(errorMessage.value);
  } finally {
    loading.value = false;
  }
}
</script>
