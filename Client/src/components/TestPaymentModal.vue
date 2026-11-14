<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6 bg-black/60 backdrop-blur-xs transition-opacity duration-200"
    @click.self="handleCancel"
  >
    <div
      class="w-full max-w-lg bg-white dark:bg-[#1E120B] rounded-3xl shadow-2xl border border-gray-200 dark:border-[#382318] overflow-hidden text-[#29231F] dark:text-[#FAF3EE] transform transition-all animate-in fade-in zoom-in-95 duration-200"
    >
      <!-- Top Test Mode Banner -->
      <div class="bg-amber-500/10 dark:bg-amber-500/20 border-b border-amber-500/20 px-5 py-2.5 flex items-center justify-between text-xs text-amber-800 dark:text-amber-300 font-bold">
        <span class="flex items-center space-x-1.5">
          <span>🧪</span>
          <span class="uppercase tracking-wider font-mono text-[11px]">{{ isAmharic ? 'የሙከራ የክፍያ ሥርዓት (ቴስት ሞድ)' : 'SIMULATED TEST PAYMENT (SANDBOX)' }}</span>
        </span>
        <span class="bg-amber-500/20 dark:bg-amber-500/30 text-amber-800 dark:text-amber-200 px-2 py-0.5 rounded-full text-[10px]">
          {{ isAmharic ? 'እውነተኛ ገንዘብ አይቀነስም' : 'No Real Money Charged' }}
        </span>
      </div>

      <!-- Header with Gateway Specific Branding -->
      <div
        class="p-6 text-white relative transition-colors"
        :class="{
          'bg-gradient-to-r from-[#0060B2] to-[#008AE6]': resolvedMethod === 'Telebirr',
          'bg-gradient-to-r from-[#4A154B] via-[#6B21A8] to-[#9333EA]': resolvedMethod === 'CBE Birr',
          'bg-gradient-to-r from-[#007A60] to-[#00A389]': resolvedMethod === 'Chapa',
        }"
      >
        <div class="flex items-start justify-between">
          <div class="space-y-1">
            <div class="flex items-center space-x-2">
              <span class="w-7 h-7 rounded-lg bg-white/20 backdrop-blur-xs flex items-center justify-center text-sm font-bold shadow-xs">
                <span v-if="resolvedMethod === 'Telebirr'">📱</span>
                <span v-else-if="resolvedMethod === 'CBE Birr'">🏦</span>
                <span v-else>💳</span>
              </span>
              <h3 class="font-bold text-lg sm:text-xl font-serif tracking-tight">
                {{ gatewayTitle }}
              </h3>
            </div>
            <p class="text-xs text-white/80">
              {{ gatewaySubtitle }}
            </p>
          </div>

          <button
            type="button"
            @click="handleCancel"
            class="text-white/70 hover:text-white p-1 rounded-full hover:bg-white/10 transition cursor-pointer"
            title="Cancel"
          >
            <span class="text-xl leading-none">&times;</span>
          </button>
        </div>

        <!-- Order Amount Tag -->
        <div class="mt-4 pt-3 border-t border-white/15 flex items-center justify-between">
          <div>
            <div class="text-[10px] uppercase tracking-wider text-white/75 font-medium">
              {{ isAmharic ? 'የትዕዛዝ ቁጥር' : 'Order Number' }}
            </div>
            <div class="font-mono text-xs font-bold text-white">
              {{ order?.order_number || 'ORD-TEST' }}
            </div>
          </div>
          <div class="text-right">
            <div class="text-[10px] uppercase tracking-wider text-white/75 font-medium">
              {{ isAmharic ? 'የሚከፈል ጠቅላላ ሂሳብ' : 'Total Amount' }}
            </div>
            <div class="font-bold text-xl text-white font-serif">
              {{ isAmharic ? `${Number(order?.total_amount || 0).toLocaleString()} ብር` : `ETB ${Number(order?.total_amount || 0).toLocaleString()}` }}
            </div>
          </div>
        </div>
      </div>

      <!-- Main Form Body -->
      <div class="p-6 space-y-5">
        <!-- Error / Failure Alert -->
        <div
          v-if="failureMessage"
          class="p-4 bg-red-50 dark:bg-rose-950/40 border border-red-200 dark:border-rose-900 rounded-2xl text-xs text-red-700 dark:text-rose-300 space-y-1 animate-shake"
        >
          <div class="font-bold flex items-center space-x-1.5">
            <span>⚠️</span>
            <span>{{ isAmharic ? 'የክፍያ ሙከራው አልተሳካም (የተመሰለ)' : 'Simulated Payment Failed' }}</span>
          </div>
          <p class="text-red-600 dark:text-rose-200 text-[11px] leading-relaxed">
            {{ failureMessage }}
          </p>
        </div>

        <!-- 1. Telebirr Form Inputs -->
        <div v-if="resolvedMethod === 'Telebirr'" class="space-y-4">
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-[#2A1810] dark:text-[#FAF3EE] uppercase tracking-wider">
              {{ isAmharic ? 'የቴሌብር ስልክ ቁጥር' : 'Telebirr Phone Number' }} <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-[#786B60] dark:text-[#C5B7AF] font-mono">
                +251
              </span>
              <input
                v-model="telebirrForm.phone"
                type="tel"
                placeholder="0911223344"
                class="w-full pl-16 pr-4 py-2.5 bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] focus:border-[#0072CE] focus:bg-white dark:focus:bg-[#1E120B] rounded-xl text-xs font-mono text-[#2A1810] dark:text-[#FAF3EE] focus:outline-none transition"
              />
            </div>
          </div>

          <!-- Quick Test Presets -->
          <div class="flex items-center space-x-2 text-[11px]">
            <span class="text-[#786B60] dark:text-[#C5B7AF]">{{ isAmharic ? 'የሙከራ ቁጥሮች፡' : 'Quick fill:' }}</span>
            <button
              type="button"
              @click="telebirrForm.phone = '0911223344'"
              class="px-2 py-0.5 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-[#0072CE] dark:text-blue-300 font-mono text-[10px] hover:bg-blue-100 cursor-pointer transition border border-blue-200 dark:border-blue-800"
            >
              0911 22 33 44
            </button>
            <button
              type="button"
              @click="telebirrForm.phone = '0922334455'"
              class="px-2 py-0.5 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-[#0072CE] dark:text-blue-300 font-mono text-[10px] hover:bg-blue-100 cursor-pointer transition border border-blue-200 dark:border-blue-800"
            >
              0922 33 44 55
            </button>
          </div>

          <div class="p-3 bg-blue-50/70 dark:bg-blue-950/30 border border-blue-100 dark:border-blue-900/40 rounded-xl text-[11px] text-[#0060B2] dark:text-blue-300 space-y-1">
            <div class="font-bold flex items-center space-x-1">
              <span>💡</span>
              <span>{{ isAmharic ? 'የቴሌብር ሙከራ መመሪያ' : 'Telebirr Test Instructions' }}</span>
            </div>
            <p class="leading-relaxed">
              {{ isAmharic 
                ? 'በዚህ የሙከራ ገጽ ላይ ያለ እውነተኛ የቴሌብር ክፍያ የትዕዛዝ አከፋፈልን መሞከር ይችላሉ። "የተሳካ ክፍያ ሞክር" የሚለውን በመጫን ክፍያውን ወዲያውኑ ማረጋገጥ ይችላሉ።' 
                : 'In this simulation sandbox, no real Telebirr PIN or SMS is required. Use "Simulate Successful Payment" to complete the test flow.' 
              }}
            </p>
          </div>
        </div>

        <!-- 2. CBE Birr Form Inputs -->
        <div v-else-if="resolvedMethod === 'CBE Birr'" class="space-y-4">
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-[#2A1810] dark:text-[#FAF3EE] uppercase tracking-wider">
              {{ isAmharic ? 'የሲቢኢ ብር ስልክ ወይም የሂሳብ ቁጥር' : 'CBE Birr Phone or Account Number' }} <span class="text-red-500">*</span>
            </label>
            <input
              v-model="cbeForm.account"
              type="text"
              placeholder="0911223344 or 1000123456789"
              class="w-full px-4 py-2.5 bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] focus:border-[#7B2CBF] focus:bg-white dark:focus:bg-[#1E120B] rounded-xl text-xs font-mono text-[#2A1810] dark:text-[#FAF3EE] focus:outline-none transition"
            />
          </div>

          <!-- Quick Test Presets -->
          <div class="flex items-center space-x-2 text-[11px]">
            <span class="text-[#786B60] dark:text-[#C5B7AF]">{{ isAmharic ? 'የሙከራ ቁጥሮች፡' : 'Quick fill:' }}</span>
            <button
              type="button"
              @click="cbeForm.account = '0911223344'"
              class="px-2 py-0.5 rounded-lg bg-purple-50 dark:bg-purple-950/60 text-[#7B2CBF] dark:text-purple-300 font-mono text-[10px] hover:bg-purple-100 cursor-pointer transition border border-purple-200 dark:border-purple-800"
            >
              0911 22 33 44
            </button>
            <button
              type="button"
              @click="cbeForm.account = '1000198273645'"
              class="px-2 py-0.5 rounded-lg bg-purple-50 dark:bg-purple-950/60 text-[#7B2CBF] dark:text-purple-300 font-mono text-[10px] hover:bg-purple-100 cursor-pointer transition border border-purple-200 dark:border-purple-800"
            >
              1000-CBE-Acct
            </button>
          </div>

          <div class="p-3 bg-purple-50/70 dark:bg-purple-950/30 border border-purple-100 dark:border-purple-900/40 rounded-xl text-[11px] text-[#6B21A8] dark:text-purple-300 space-y-1">
            <div class="font-bold flex items-center space-x-1">
              <span>🏛️</span>
              <span>{{ isAmharic ? 'የኢትዮጵያ ንግድ ባንክ (ሲቢኢ) የሙከራ ሥርዓት' : 'CBE Birr & Mobile Banking Sandbox' }}</span>
            </div>
            <p class="leading-relaxed">
              {{ isAmharic 
                ? 'የኢትዮጵያ ንግድ ባንክ ሞባይል ባንኪንግ ወይም ሲቢኢ ብር የክፍያ ሂደት ማስመሰያ። ምንም እውነተኛ የባንክ ሂሳብ አይነካም።' 
                : 'Simulated CBE Mobile Banking environment. No real bank transfer occurs. Safe for test orders.' 
              }}
            </p>
          </div>
        </div>

        <!-- 3. Chapa Card Form Inputs -->
        <div v-else-if="resolvedMethod === 'Chapa'" class="space-y-4">
          <!-- Card Details Grid -->
          <div class="space-y-3">
            <div class="space-y-1">
              <label class="block text-[11px] font-bold text-[#2A1810] dark:text-[#FAF3EE] uppercase tracking-wider">
                {{ isAmharic ? 'የካርድ ቁጥር' : 'Test Card Number' }} <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <input
                  v-model="chapaForm.cardNumber"
                  type="text"
                  placeholder="4111 2222 3333 4444"
                  maxlength="19"
                  class="w-full px-4 py-2.5 bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] focus:border-[#00A389] focus:bg-white dark:focus:bg-[#1E120B] rounded-xl text-xs font-mono text-[#2A1810] dark:text-[#FAF3EE] focus:outline-none transition"
                />
                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-[#00A389]">
                  TEST VISA
                </span>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div class="space-y-1">
                <label class="block text-[11px] font-bold text-[#2A1810] dark:text-[#FAF3EE] uppercase tracking-wider">
                  {{ isAmharic ? 'የሚያበቃበት (MM/YY)' : 'Expiry Date' }} <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="chapaForm.expiry"
                  type="text"
                  placeholder="12/28"
                  maxlength="5"
                  class="w-full px-4 py-2.5 bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] focus:border-[#00A389] focus:bg-white dark:focus:bg-[#1E120B] rounded-xl text-xs font-mono text-[#2A1810] dark:text-[#FAF3EE] focus:outline-none transition text-center"
                />
              </div>

              <div class="space-y-1">
                <label class="block text-[11px] font-bold text-[#2A1810] dark:text-[#FAF3EE] uppercase tracking-wider">
                  CVV <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="chapaForm.cvv"
                  type="text"
                  placeholder="123"
                  maxlength="4"
                  class="w-full px-4 py-2.5 bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] focus:border-[#00A389] focus:bg-white dark:focus:bg-[#1E120B] rounded-xl text-xs font-mono text-[#2A1810] dark:text-[#FAF3EE] focus:outline-none transition text-center"
                />
              </div>
            </div>

            <div class="space-y-1">
              <label class="block text-[11px] font-bold text-[#2A1810] dark:text-[#FAF3EE] uppercase tracking-wider">
                {{ isAmharic ? 'የካርድ ባለቤት ስም' : 'Cardholder Name' }} <span class="text-red-500">*</span>
              </label>
              <input
                v-model="chapaForm.cardholderName"
                type="text"
                placeholder="Test Customer"
                class="w-full px-4 py-2.5 bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] focus:border-[#00A389] focus:bg-white dark:focus:bg-[#1E120B] rounded-xl text-xs text-[#2A1810] dark:text-[#FAF3EE] focus:outline-none transition"
              />
            </div>
          </div>

          <!-- Quick Test Credentials Hint -->
          <div class="p-3 bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/40 rounded-xl text-[11px] text-[#007A60] dark:text-emerald-300 space-y-1">
            <div class="flex items-center justify-between">
              <span class="font-bold flex items-center space-x-1">
                <span>🛡️</span>
                <span>{{ isAmharic ? 'የተፈቀደ የሙከራ ካርድ መረጃ' : 'Accepted Test Card Details' }}</span>
              </span>
              <button
                type="button"
                @click="fillTestCard"
                class="text-[10px] font-bold underline cursor-pointer hover:text-emerald-700"
              >
                {{ isAmharic ? 'በራስ-ሰር ሙላ' : 'Auto-fill test card' }}
              </button>
            </div>
            <div class="font-mono text-[10px] text-emerald-800 dark:text-emerald-200">
              Card: 4111 2222 3333 4444 &bull; Exp: 12/28 &bull; CVV: 123
            </div>
            <p class="text-[10px] text-[#557A6B] dark:text-emerald-400">
              {{ isAmharic ? 'ማስጠንቀቂያ፡ እውነተኛ የባንክ ካርድ መረጃ በጭራሽ አያስገቡ።' : 'Strict Security: Never enter real debit or credit card credentials.' }}
            </p>
          </div>
        </div>

        <!-- Simulation Actions Button Group -->
        <div class="space-y-2.5 pt-2">
          <!-- Button 1: Simulate Successful Payment -->
          <button
            type="button"
            @click="simulatePayment('success')"
            :disabled="isLoading"
            class="w-full py-3.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl font-bold text-xs uppercase tracking-wider transition shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer flex items-center justify-center space-x-2"
          >
            <span v-if="isLoading && currentAction === 'success'" class="animate-spin inline-block">⌛</span>
            <span v-else>✓</span>
            <span>
              {{ isAmharic ? 'የተሳካ ክፍያ ሞክር (Simulate Success)' : 'Simulate Successful Payment' }}
            </span>
          </button>

          <!-- Button 2: Simulate Failed Payment -->
          <button
            type="button"
            @click="simulatePayment('fail')"
            :disabled="isLoading"
            class="w-full py-3 px-4 bg-white dark:bg-[#1E120B] border-2 border-red-300 dark:border-rose-800 hover:bg-red-50 dark:hover:bg-rose-950/30 text-red-600 dark:text-rose-400 rounded-2xl font-bold text-xs uppercase tracking-wider transition disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer flex items-center justify-center space-x-2"
          >
            <span v-if="isLoading && currentAction === 'fail'" class="animate-spin inline-block">⌛</span>
            <span v-else>✕</span>
            <span>
              {{ isAmharic ? 'ያልተሳካ ክፍያ ሞክር (Simulate Failure)' : 'Simulate Failed Payment' }}
            </span>
          </button>

          <!-- Button 3: Cancel Payment -->
          <button
            type="button"
            @click="handleCancel"
            :disabled="isLoading"
            class="w-full py-2.5 px-4 text-[#786B60] dark:text-[#C5B7AF] hover:text-[#2A1810] dark:hover:text-[#FAF3EE] rounded-xl font-bold text-xs transition cursor-pointer flex items-center justify-center space-x-1"
          >
            <span>{{ isAmharic ? 'ክፍያውን ሰርዝ እና ተመለስ' : 'Cancel Payment & Return' }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import api from '../api';

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
  order: {
    type: Object,
    default: () => ({}),
  },
  paymentMethod: {
    type: String,
    default: 'telebirr',
  },
  isAmharic: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['success', 'failed', 'cancelled', 'close']);

const isLoading = ref(false);
const currentAction = ref(null);
const failureMessage = ref(null);

// Form data for each gateway
const telebirrForm = ref({
  phone: '0911223344',
});

const cbeForm = ref({
  account: '0911223344',
});

const chapaForm = ref({
  cardNumber: '4111 2222 3333 4444',
  expiry: '12/28',
  cvv: '123',
  cardholderName: 'Test Customer',
});

const resolvedMethod = computed(() => {
  const m = (props.paymentMethod || '').toLowerCase();
  if (m.includes('telebirr')) return 'Telebirr';
  if (m.includes('cbe')) return 'CBE Birr';
  if (m.includes('chapa') || m.includes('card')) return 'Chapa';
  return 'Telebirr';
});

const gatewayTitle = computed(() => {
  if (resolvedMethod.value === 'Telebirr') {
    return props.isAmharic ? 'ቴሌብር የሞባይል ክፍያ (ሲሙሌሽን)' : 'Telebirr Mobile Money (Simulated)';
  }
  if (resolvedMethod.value === 'CBE Birr') {
    return props.isAmharic ? 'ሲቢኢ ብር እና ሞባይል ባንኪንግ (ሲሙሌሽን)' : 'CBE Birr & Mobile Banking (Simulated)';
  }
  return props.isAmharic ? 'ቻፓ የካርድ ክፍያ (ሲሙሌሽን)' : 'Chapa Card Payment (Simulated)';
});

const gatewaySubtitle = computed(() => {
  if (resolvedMethod.value === 'Telebirr') {
    return props.isAmharic ? 'የኢትዮ ቴሌኮም የሞባይል ገንዘብ መተግበሪያ' : 'Ethio Telecom Mobile Payment Gateway';
  }
  if (resolvedMethod.value === 'CBE Birr') {
    return props.isAmharic ? 'የኢትዮጵያ ንግድ ባንክ ደህንነቱ የተጠበቀ ክፍያ' : 'Commercial Bank of Ethiopia Digital Banking';
  }
  return props.isAmharic ? 'የአገር ውስጥ እና ዓለም አቀፍ የዴቢት/ክሬዲት ካርዶች' : 'Debit / Credit Card Processing Sandbox';
});

watch(
  () => props.order,
  (newOrder) => {
    if (newOrder) {
      if (newOrder.customer_phone) {
        telebirrForm.value.phone = newOrder.customer_phone;
        cbeForm.value.account = newOrder.customer_phone;
      }
      if (newOrder.customer_name) {
        chapaForm.value.cardholderName = newOrder.customer_name;
      }
      failureMessage.value = null;
    }
  },
  { immediate: true }
);

function fillTestCard() {
  chapaForm.value.cardNumber = '4111 2222 3333 4444';
  chapaForm.value.expiry = '12/28';
  chapaForm.value.cvv = '123';
  chapaForm.value.cardholderName = props.order?.customer_name || 'Test Customer';
}

async function simulatePayment(action) {
  if (!props.order?.order_id && !props.order?.order_number) {
    failureMessage.value = 'Missing order reference for payment simulation.';
    return;
  }

  failureMessage.value = null;
  isLoading.value = true;
  currentAction.value = action;

  try {
    const payload = {
      order_id: props.order.order_id || props.order.order_number,
      payment_method: resolvedMethod.value,
      action,
      phone: resolvedMethod.value === 'Telebirr' ? telebirrForm.value.phone : undefined,
      account: resolvedMethod.value === 'CBE Birr' ? cbeForm.value.account : undefined,
      card_name: resolvedMethod.value === 'Chapa' ? chapaForm.value.cardholderName : undefined,
      card_last4: resolvedMethod.value === 'Chapa' ? '4444' : undefined,
      reason: action === 'fail' ? (props.isAmharic ? 'የሙከራ ክፍያ አልተሳካም (በቂ ያልሆነ ቀሪ ሒሳብ ወይም በባንኩ ውድቅ ተደረገ)' : 'Simulated payment failed (Insufficient test balance or declined by test issuer)') : undefined,
    };

    const res = await api.post('/payments/simulate', payload);

    if (action === 'success') {
      emit('success', res.data);
    }
  } catch (err) {
    if (action === 'fail') {
      failureMessage.value = err.response?.data?.message || (props.isAmharic ? 'የተመሰለው ክፍያ አልተሳካም። እባክዎ እንደገና ይሞክሩ።' : 'Simulated payment declined. You may retry or choose another payment method.');
      emit('failed', { error: failureMessage.value });
    } else {
      failureMessage.value = err.response?.data?.message || 'Failed to simulate payment. Please check your network or try again.';
      emit('failed', { error: failureMessage.value });
    }
  } finally {
    isLoading.value = false;
  }
}

async function handleCancel() {
  if (isLoading.value) return;

  if (props.order?.order_id || props.order?.order_number) {
    try {
      await api.post('/payments/simulate', {
        order_id: props.order.order_id || props.order.order_number,
        payment_method: resolvedMethod.value,
        action: 'cancel',
      });
    } catch (e) {
      console.warn('Could not record cancelled test payment', e);
    }
  }

  emit('cancelled');
  emit('close');
}
</script>
