<template>
  <div class="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 font-sans selection:bg-[#E8D8C4] selection:text-[#553B2D]">
    <div class="max-w-md w-full bg-white rounded-3xl border border-[#E6DED3] shadow-lg p-8 sm:p-10 space-y-6">
      <!-- Header -->
      <div class="text-center space-y-2">
        <div class="w-14 h-14 rounded-2xl bg-[#E8D8C4] border border-[#D5C2AD] flex items-center justify-center mx-auto text-2xl shadow-xs text-[#553B2D]">
          ✨
        </div>
        <h2 class="text-2xl sm:text-3xl font-serif font-black text-[#553B2D]">
          Join Kenean Cake & Bread
        </h2>
        <p class="text-xs text-[#786B60]">
          Create your account for faster checkout, bespoke cake quotation tracking, and seasonal member offers.
        </p>
      </div>

      <!-- Error Alert -->
      <div v-if="errorMessage" class="p-3 bg-[#FAF0F0] border border-[#E8C4C4] rounded-2xl text-xs text-[#B84B42] font-semibold flex items-center space-x-2">
        <span>⚠️</span>
        <span>{{ errorMessage }}</span>
      </div>

      <!-- Registration Form -->
      <form @submit.prevent="handleRegister" class="space-y-4">
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-[#553B2D] mb-1">
            Full Name
          </label>
          <input
            v-model="form.full_name"
            type="text"
            placeholder="e.g. Sara Ahmed"
            class="w-full bg-[#F8F3EA] border border-[#E6DED3] focus:border-[#A87552] focus:bg-white rounded-xl px-4 py-2.5 text-xs text-[#29231F] focus:outline-none transition"
            required
          />
        </div>

        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-[#553B2D] mb-1">
            Email Address
          </label>
          <input
            v-model="form.email"
            type="email"
            placeholder="sara@example.com"
            class="w-full bg-[#F8F3EA] border border-[#E6DED3] focus:border-[#A87552] focus:bg-white rounded-xl px-4 py-2.5 text-xs text-[#29231F] focus:outline-none transition"
            required
          />
        </div>

        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-[#553B2D] mb-1">
            Phone Number (Ethiopian format)
          </label>
          <input
            v-model="form.phone"
            type="text"
            placeholder="+251 9XX XXX XXX or 09XX XXX XXX"
            class="w-full bg-[#F8F3EA] border border-[#E6DED3] focus:border-[#A87552] focus:bg-white rounded-xl px-4 py-2.5 text-xs text-[#29231F] focus:outline-none transition"
            required
          />
          <span class="text-[10px] text-[#786B60]">Required for courier delivery updates</span>
        </div>

        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-[#553B2D] mb-1">
            Password (min 6 characters)
          </label>
          <input
            v-model="form.password"
            type="password"
            placeholder="••••••••"
            class="w-full bg-[#F8F3EA] border border-[#E6DED3] focus:border-[#A87552] focus:bg-white rounded-xl px-4 py-2.5 text-xs text-[#29231F] focus:outline-none transition"
            required
            minlength="6"
          />
        </div>

        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-[#553B2D] mb-1">
            Confirm Password
          </label>
          <input
            v-model="confirmPassword"
            type="password"
            placeholder="••••••••"
            class="w-full bg-[#F8F3EA] border border-[#E6DED3] focus:border-[#A87552] focus:bg-white rounded-xl px-4 py-2.5 text-xs text-[#29231F] focus:outline-none transition"
            required
          />
        </div>

        <div class="flex items-start pt-1">
          <input
            id="terms"
            type="checkbox"
            required
            class="mt-0.5 rounded text-[#A87552] focus:ring-[#A87552]"
          />
          <label for="terms" class="ml-2 text-xs text-[#786B60] leading-relaxed">
            I agree to Kenean Cake &amp; Bread <router-link to="/about" class="text-[#A87552] underline">Terms of Service</router-link> and <router-link to="/about" class="text-[#A87552] underline">Freshness Guarantee</router-link>.
          </label>
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full py-4 bg-[#553B2D] hover:bg-[#432E23] disabled:opacity-50 text-white text-xs font-bold uppercase tracking-wider rounded-2xl shadow-md hover:shadow-lg transition active:scale-98 cursor-pointer"
        >
          {{ loading ? 'Creating Account...' : 'Complete Registration →' }}
        </button>
      </form>

      <!-- Already registered link -->
      <div class="text-center text-xs text-[#786B60] pt-2 border-t border-[#E6DED3]">
        Already have an account?
        <router-link to="/login" class="font-bold text-[#A87552] hover:underline ml-1">
          Sign In
        </router-link>
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
    toastStore.success(`Welcome to Kenean Cake & Bread, ${res.data.user.full_name}!`);
    router.push('/account');
  } catch (err) {
    errorMessage.value = err.response?.data?.message || err.response?.data?.errors?.phone?.[0] || 'Registration failed. Please check your information.';
  } finally {
    loading.value = false;
  }
}
</script>
