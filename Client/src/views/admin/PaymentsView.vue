<template>
  <div class="space-y-6 max-w-7xl mx-auto font-poppins">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <h2 class="dash-title text-xl sm:text-2xl text-[#2B1810] dark:text-[#FAF4ED]">
          {{ isAmharic ? 'ክፍያዎች እና ገንዘብ አሰባሰብ' : 'Payments & Collections' }}
        </h2>
        <p class="dash-subtitle mt-0.5 text-[#7D6E64] dark:text-[#A69284]">
          {{ isAmharic ? 'የቴሌብር፣ ንግድ ባንክ (CBE)፣ ቻፓ እና የእጅ በእጅ ክፍያዎችን በቀላሉ ይቆጣጠሩ' : 'Track multi-channel payment reconciliation across Telebirr, CBE Birr, Chapa, and Cash' }}
        </p>
      </div>
      <div class="flex items-center space-x-2">
        <button
          @click="loadPayments"
          class="dash-btn-secondary text-xs sm:text-sm flex items-center space-x-1.5 cursor-pointer"
          :title="isAmharic ? 'አድስ' : 'Refresh'"
        >
          <span :class="{ 'animate-spin': loading }">🔄</span>
          <span>{{ isAmharic ? 'አድስ' : 'Refresh' }}</span>
        </button>
        <button
          @click="exportCSV"
          class="dash-btn-secondary text-xs sm:text-sm flex items-center space-x-1.5 cursor-pointer"
        >
          <span>📥</span>
          <span>{{ isAmharic ? 'CSV አውርድ' : 'Export CSV' }}</span>
        </button>
      </div>
    </div>

    <!-- 4 Summary Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="dash-card p-5">
        <div class="dash-subtitle mb-1 text-[#7D6E64] dark:text-[#A69284]">{{ isAmharic ? 'የዛሬ የመስመር ላይ ገቢ' : 'Online revenue today' }}</div>
        <div class="dash-metric text-2xl sm:text-3xl font-black text-[#2B1810] dark:text-[#FAF4ED]">
          ETB {{ Number(summary.online_revenue_today || 0).toLocaleString() }}
        </div>
        <div class="text-xs text-[#7D6E64] dark:text-[#A69284] mt-1">{{ isAmharic ? 'ቻፓ እና ቴሌብር' : 'Chapa, Telebirr & CBE' }}</div>
      </div>

      <div class="dash-card p-5">
        <div class="dash-subtitle mb-1 text-[#7D6E64] dark:text-[#A69284]">{{ isAmharic ? 'በመቀበያ/በማድረሻ የሚከፈል' : 'Pay on pickup/delivery' }}</div>
        <div class="dash-metric text-2xl sm:text-3xl font-black text-[#2B1810] dark:text-[#FAF4ED]">
          ETB {{ Number(summary.pay_on_pickup_delivery || 0).toLocaleString() }}
        </div>
        <div class="text-xs text-[#7D6E64] dark:text-[#A69284] mt-1">{{ isAmharic ? 'ሊሰበሰብ የሚጠበቅ' : 'To be collected upon handover' }}</div>
      </div>

      <div class="dash-card p-5">
        <div class="dash-subtitle mb-1 text-[#7D6E64] dark:text-[#A69284]">{{ isAmharic ? 'ያልተሳኩ ክፍያዎች' : 'Failed transactions' }}</div>
        <div class="dash-metric text-2xl sm:text-3xl font-black text-[#C62828] dark:text-rose-400">
          {{ summary.failed_today || 0 }}
        </div>
        <div class="text-xs text-[#7D6E64] dark:text-[#A69284] mt-1">{{ isAmharic ? 'ዛሬ የተመዘገቡ' : 'Logged today' }}</div>
      </div>

      <div class="dash-card p-5">
        <div class="dash-subtitle mb-1 text-[#7D6E64] dark:text-[#A69284]">{{ isAmharic ? 'የተመለሰ ገንዘብ (ሪፈንድ)' : 'Refunds this week' }}</div>
        <div class="dash-metric text-2xl sm:text-3xl font-black text-[#2B1810] dark:text-[#FAF4ED]">
          ETB {{ Number(summary.refunds_this_week || 0).toLocaleString() }}
        </div>
        <div class="text-xs text-[#7D6E64] dark:text-[#A69284] mt-1">{{ isAmharic ? 'በዚህ ሳምንት' : 'Completed this week' }}</div>
      </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="dash-card p-4 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
      <div class="relative flex-1">
        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
          🔍
        </span>
        <input
          v-model="searchQuery"
          type="text"
          :placeholder="isAmharic ? 'በትዕዛዝ ቁጥር፣ በደንበኛ ስም ወይም በማመሳከሪያ ኮድ ፈልግ...' : 'Search by order #, customer name, transaction ref...'"
          class="w-full pl-9 pr-4 py-2 text-xs sm:text-sm bg-white dark:bg-[#160E0A] border border-[#E2D8CC] dark:border-[#382318] text-[#2B1810] dark:text-[#FAF4ED] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#BA5420]/30"
        />
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <!-- Status Filter -->
        <select
          v-model="statusFilter"
          class="bg-white dark:bg-[#160E0A] border border-[#E2D8CC] dark:border-[#382318] text-[#2B1810] dark:text-[#FAF4ED] text-xs font-semibold rounded-xl px-3 py-2 cursor-pointer focus:outline-none"
        >
          <option value="all">{{ isAmharic ? 'ሁሉም ሁኔታዎች' : 'All Statuses' }}</option>
          <option value="paid">{{ isAmharic ? 'የተከፈለ (Paid / Test)' : 'Paid / Paid (Test)' }}</option>
          <option value="pending">{{ isAmharic ? 'በመጠባበቅ ላይ (Pending)' : 'Pending' }}</option>
          <option value="failed">{{ isAmharic ? 'ያልተሳካ (Failed)' : 'Failed' }}</option>
          <option value="test_only">{{ isAmharic ? 'የሙከራ ብቻ (Test Mode Only)' : 'Test Mode Only' }}</option>
        </select>

        <!-- Method Filter -->
        <select
          v-model="methodFilter"
          class="bg-white dark:bg-[#160E0A] border border-[#E2D8CC] dark:border-[#382318] text-[#2B1810] dark:text-[#FAF4ED] text-xs font-semibold rounded-xl px-3 py-2 cursor-pointer focus:outline-none"
        >
          <option value="all">{{ isAmharic ? 'ሁሉም የመክፈያ መንገዶች' : 'All Methods' }}</option>
          <option value="Telebirr">Telebirr</option>
          <option value="CBE Birr">CBE Birr</option>
          <option value="Chapa">Chapa Card</option>
          <option value="cash">{{ isAmharic ? 'የእጅ በእጅ (Cash)' : 'Cash / POS' }}</option>
        </select>

        <button
          v-if="searchQuery || statusFilter !== 'all' || methodFilter !== 'all'"
          @click="resetFilters"
          class="text-xs text-[#BA5420] dark:text-rose-400 font-bold hover:underline px-2 py-1 cursor-pointer"
        >
          {{ isAmharic ? 'አጽዳ' : 'Reset' }}
        </button>
      </div>
    </div>

    <!-- Main Content: Transactions Table + Refund Form -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
      <!-- Transactions Table (Col span 2) -->
      <div class="lg:col-span-2 dash-card overflow-hidden">
        <div class="p-5 border-b border-[#EFE8DF] dark:border-[#382318] flex items-center justify-between">
          <h3 class="font-serif font-bold text-base sm:text-lg text-[#2A1D15] dark:text-[#FAF4ED]">
            {{ isAmharic ? 'የግብይቶች ዝርዝር' : 'Transactions Ledger' }}
          </h3>
          <span class="text-xs font-mono text-[#7D6E64] dark:text-[#A69284]">
            {{ filteredPayments.length }} {{ isAmharic ? 'ግብይቶች' : 'records' }}
          </span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full min-w-[700px] text-left text-sm">
            <thead class="bg-[#FAF6F0] dark:bg-[#160E0A] text-[11px] font-bold text-[#827165] dark:text-[#A8988E] tracking-wider uppercase border-b border-[#EFE8DF] dark:border-[#382318]">
              <tr>
                <th class="py-3 px-4">{{ isAmharic ? 'የትዕዛዝ ቁጥር' : 'Order' }}</th>
                <th class="py-3 px-4">{{ isAmharic ? 'ደንበኛ' : 'Customer' }}</th>
                <th class="py-3 px-4">{{ isAmharic ? 'መንገድ' : 'Method' }}</th>
                <th class="py-3 px-4">{{ isAmharic ? 'መጠን' : 'Amount' }}</th>
                <th class="py-3 px-4">{{ isAmharic ? 'ማመሳከሪያ ቁጥር' : 'Transaction Ref' }}</th>
                <th class="py-3 px-4">{{ isAmharic ? 'ቀን' : 'Date' }}</th>
                <th class="py-3 px-4 text-center">{{ isAmharic ? 'ሁኔታ' : 'Status' }}</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#F3EDE4] dark:divide-[#382318]">
              <tr
                v-if="filteredPayments.length === 0"
                class="text-center py-8 text-xs text-[#7D6E64] dark:text-[#A69284]"
              >
                <td colspan="7" class="py-8">
                  {{ isAmharic ? 'ምንም አይነት ክፍያ አልተገኘም።' : 'No transactions found matching your criteria.' }}
                </td>
              </tr>
              <tr
                v-for="p in filteredPayments"
                :key="p.payment_id"
                @click="populateRefund(p)"
                class="hover:bg-[#FCFAF7] dark:hover:bg-[#2A1810]/50 transition cursor-pointer"
              >
                <td class="py-3.5 px-4 font-mono text-xs font-semibold text-[#382C24] dark:text-[#FAF3EE]">
                  {{ p.order_number }}
                </td>
                <td class="py-3.5 px-4 text-xs text-[#4A3B32] dark:text-[#E8DDD4]">
                  <div class="font-bold">{{ p.customer_name || 'Customer' }}</div>
                  <div class="text-[10px] text-[#827165] dark:text-[#A8988E]">{{ p.branch_name }}</div>
                </td>
                <td class="py-3.5 px-4 text-xs font-semibold text-[#6A5A50] dark:text-[#C5B7AF]">
                  {{ p.method }}
                </td>
                <td class="py-3.5 px-4 text-xs font-bold font-mono text-[#241710] dark:text-[#FAF3EE]">
                  ETB {{ Number(p.amount).toLocaleString() }}
                </td>
                <td class="py-3.5 px-4 font-mono text-xs text-[#5A4A3E] dark:text-[#C5B7AF]">
                  <div class="flex items-center space-x-1.5 flex-wrap">
                    <span>{{ p.transaction_ref }}</span>
                    <span
                      v-if="p.is_test || p.transaction_ref?.startsWith('TEST-') || p.status?.includes('TEST')"
                      class="px-1.5 py-0.5 text-[9px] font-black uppercase rounded bg-purple-100 text-purple-700 dark:bg-purple-900/60 dark:text-purple-300 border border-purple-300 dark:border-purple-700"
                    >
                      TEST
                    </span>
                  </div>
                </td>
                <td class="py-3.5 px-4 text-[11px] font-mono text-[#7A6B60] dark:text-[#A8988E]">
                  {{ p.order_date || p.created_at }}
                </td>
                <td class="py-3.5 px-4 text-center">
                  <span
                    class="inline-block px-2.5 py-0.5 text-[11px] font-bold rounded-full"
                    :class="{
                      'bg-[#EAF5EA] text-[#2E7D32] border border-[#C8E6C9] dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800': p.status === 'Paid' || p.status === 'PAID_TEST',
                      'bg-[#FFF8E6] text-[#A67503] border border-[#FEE39E] dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800': p.status === 'Pending' || p.status === 'PENDING',
                      'bg-[#FDEAEA] text-[#C62828] border border-[#FFCDD2] dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800': p.status === 'Failed' || p.status === 'FAILED_TEST',
                      'bg-[#F3EFE9] text-[#7A6B60] border border-[#DDD3C7] dark:bg-[#251810] dark:text-[#C5B7AF] dark:border-[#382318]': p.status === 'Refunded' || p.status === 'CANCELLED_TEST'
                    }"
                  >
                    {{ p.status === 'PAID_TEST' ? 'PAID (TEST)' : (p.status === 'FAILED_TEST' ? 'FAILED (TEST)' : (p.status === 'CANCELLED_TEST' ? 'CANCELLED (TEST)' : p.status)) }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="p-4 bg-[#FCFAF7] dark:bg-[#160E0A] border-t border-[#EFE8DF] dark:border-[#382318] text-xs text-[#8A7B71] dark:text-[#A69284]">
          {{ isAmharic 
            ? 'ትዕዛዞች በክፍያ መተላለፊያ (Gateway Webhook) የተረጋገጡ ሲሆኑ፣ የሙከራ ክፍያዎች በግልጽ በTEST መለያ ተለይተዋል።' 
            : 'Orders are confirmed upon signed gateway webhooks or test simulation callbacks. Test mode transactions are clearly marked with a TEST badge.' }}
        </div>
      </div>

      <!-- Right Card: Refund Form -->
      <div class="dash-card p-6 space-y-4">
        <h3 class="font-serif font-bold text-base sm:text-lg text-[#2A1D15] dark:text-[#FAF4ED]">
          {{ isAmharic ? 'የገንዘብ ተመላሽ (ሪፈንድ)' : 'Issue Refund' }}
        </h3>

        <form @submit.prevent="submitRefund" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-[#65554B] dark:text-[#C5B7AF] mb-1">
              {{ isAmharic ? 'የትዕዛዝ ቁጥር' : 'Order Number' }} <span class="text-red-500">*</span>
            </label>
            <input
              v-model="refundForm.order_number"
              type="text"
              placeholder="e.g. ORD-1007"
              class="w-full border border-[#D8CDC0] dark:border-[#382318] rounded-xl px-3 py-2 text-sm text-[#2D231D] dark:text-[#FAF4ED] bg-[#FDFBF7] dark:bg-[#160E0A] focus:ring-1 focus:ring-[#BA5420]"
              required
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-[#65554B] dark:text-[#C5B7AF] mb-1">
              {{ isAmharic ? 'የሚመለስ መጠን (ETB)' : 'Amount (ETB)' }} <span class="text-red-500">*</span>
            </label>
            <input
              v-model.number="refundForm.amount"
              type="number"
              placeholder="850"
              class="w-full border border-[#D8CDC0] dark:border-[#382318] rounded-xl px-3 py-2 text-sm text-[#2D231D] dark:text-[#FAF4ED] bg-[#FDFBF7] dark:bg-[#160E0A] focus:ring-1 focus:ring-[#BA5420]"
              required
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-[#65554B] dark:text-[#C5B7AF] mb-1">
              {{ isAmharic ? 'የተመላሽ ምክንያት' : 'Reason for Refund' }} <span class="text-red-500">*</span>
            </label>
            <textarea
              v-model="refundForm.reason"
              rows="3"
              :placeholder="isAmharic ? 'ለምሳሌ፡ ምርቱ አልቆበት ተሰርዟል...' : 'e.g. Batch damaged or customer requested cancellation...'"
              class="w-full border border-[#D8CDC0] dark:border-[#382318] rounded-xl px-3 py-2 text-sm text-[#2D231D] dark:text-[#FAF4ED] bg-[#FDFBF7] dark:bg-[#160E0A] focus:ring-1 focus:ring-[#BA5420]"
              required
            ></textarea>
          </div>

          <div class="p-3 bg-[#FCFAF7] dark:bg-[#160E0A] border border-[#EFE8DF] dark:border-[#382318] rounded-xl text-xs text-[#7A6B60] dark:text-[#A69284]">
            {{ isAmharic ? 'የተመለሰው ገንዘብ በመጀመሪያ በተከፈለበት መንገድ ይመዘገባል እንዲሁም በሲስተም ኦዲት ሎግ ይቀመጣል።' : 'Refund goes to the original payment method and is recorded in audit logs.' }}
          </div>

          <button
            type="submit"
            :disabled="refunding"
            class="w-full py-2.5 px-4 bg-[#BA5420] hover:bg-[#9E4314] text-white font-bold text-sm rounded-xl transition shadow-xs disabled:opacity-50 cursor-pointer"
          >
            {{ refunding ? (isAmharic ? 'በማስኬድ ላይ...' : 'Processing...') : (isAmharic ? 'ተመላሽ ገንዘብ ስጥ' : 'Process Refund') }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useToastStore } from '../../stores/toast';
import { useLocaleStore } from '../../stores/locale';
import api from '../../api';

const toast = useToastStore();
const localeStore = useLocaleStore();
const isAmharic = computed(() => localeStore.isAmharic);

const loading = ref(false);
const payments = ref([]);
const summary = ref({
  online_revenue_today: 0,
  pay_on_pickup_delivery: 0,
  failed_today: 0,
  refunds_this_week: 0,
});

const searchQuery = ref('');
const statusFilter = ref('all');
const methodFilter = ref('all');

const refundForm = ref({
  order_number: '',
  amount: null,
  reason: '',
});

const refunding = ref(false);

const loadPayments = async () => {
  loading.value = true;
  try {
    const res = await api.get('/admin/payments');
    payments.value = res.data.payments || [];
    summary.value = res.data.summary || summary.value;
  } catch (err) {
    console.error('Failed to load payments', err);
    toast.error(isAmharic.value ? 'የክፍያ መረጃዎችን መጫን አልተቻለም።' : 'Failed to load payments data.');
  } finally {
    loading.value = false;
  }
};

const filteredPayments = computed(() => {
  let list = payments.value;

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase().trim();
    list = list.filter((p) => {
      return (
        p.order_number?.toLowerCase().includes(q) ||
        p.customer_name?.toLowerCase().includes(q) ||
        p.transaction_ref?.toLowerCase().includes(q) ||
        p.branch_name?.toLowerCase().includes(q)
      );
    });
  }

  if (statusFilter.value === 'paid') {
    list = list.filter((p) => p.status === 'Paid' || p.status === 'PAID_TEST');
  } else if (statusFilter.value === 'pending') {
    list = list.filter((p) => p.status === 'Pending' || p.status === 'PENDING');
  } else if (statusFilter.value === 'failed') {
    list = list.filter((p) => p.status === 'Failed' || p.status === 'FAILED_TEST');
  } else if (statusFilter.value === 'test_only') {
    list = list.filter((p) => p.is_test || p.transaction_ref?.startsWith('TEST-') || p.status?.includes('TEST'));
  }

  if (methodFilter.value !== 'all') {
    if (methodFilter.value === 'cash') {
      list = list.filter((p) => p.method?.toLowerCase().includes('cash') || p.method?.toLowerCase().includes('pickup') || p.method?.toLowerCase().includes('delivery'));
    } else {
      list = list.filter((p) => p.method?.toLowerCase().includes(methodFilter.value.toLowerCase()));
    }
  }

  return list;
});

const resetFilters = () => {
  searchQuery.value = '';
  statusFilter.value = 'all';
  methodFilter.value = 'all';
};

const populateRefund = (p) => {
  if (p.order_number && p.order_number !== '—') {
    refundForm.value.order_number = p.order_number;
    refundForm.value.amount = p.amount;
  }
};

const submitRefund = async () => {
  if (!refundForm.value.order_number || !refundForm.value.amount) {
    toast.error(isAmharic.value ? 'እባክዎ የትዕዛዝ ቁጥር እና መጠን ያስገቡ።' : 'Please provide an order number and amount.');
    return;
  }

  refunding.value = true;
  try {
    await api.post('/admin/payments/refund', refundForm.value);
    toast.success(
      isAmharic.value
        ? `ለ${refundForm.value.order_number} ተመላሽ ገንዘብ ETB ${refundForm.value.amount.toLocaleString()} በተሳካ ሁኔታ ተፈጽሟል።`
        : `Refund issued successfully for ${refundForm.value.order_number} (ETB ${refundForm.value.amount.toLocaleString()})`
    );
    refundForm.value.order_number = '';
    refundForm.value.amount = null;
    refundForm.value.reason = '';
    await loadPayments();
  } catch (err) {
    toast.error(err.response?.data?.message || (isAmharic.value ? 'ተመላሽ ማድረግ አልተቻለም።' : 'Failed to issue refund'));
  } finally {
    refunding.value = false;
  }
};

const exportCSV = () => {
  const headers = ['Order', 'Customer', 'Branch', 'Method', 'Amount', 'Transaction Ref', 'Date', 'Status', 'Is Test'];
  const rows = filteredPayments.value.map((p) => [
    `"${p.order_number}"`,
    `"${p.customer_name || 'Customer'}"`,
    `"${p.branch_name}"`,
    `"${p.method}"`,
    p.amount,
    `"${p.transaction_ref}"`,
    `"${p.order_date || p.created_at}"`,
    `"${p.status}"`,
    p.is_test ? 'YES' : 'NO',
  ]);
  const csvContent = 'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows.map((e) => e.join(','))].join('\n');
  const encodedUri = encodeURI(csvContent);
  const link = document.createElement('a');
  link.setAttribute('href', encodedUri);
  link.setAttribute('download', `kenean_payments_${new Date().toISOString().slice(0, 10)}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

onMounted(() => {
  loadPayments();
});
</script>
