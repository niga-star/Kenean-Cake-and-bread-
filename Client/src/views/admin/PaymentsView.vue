<template>
  <div class="space-y-6 max-w-7xl mx-auto font-poppins">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <h2 class="dash-title text-xl sm:text-2xl">Payments &amp; Collections</h2>
        <p class="dash-subtitle mt-0.5">Track multi-channel payment reconciliation across telebirr, Chapa, and cash</p>
      </div>
      <button
        @click="exportCSV"
        class="dash-btn-secondary text-xs sm:text-sm"
      >
        Export CSV
      </button>
    </div>

    <!-- 4 Summary Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="dash-card p-5">
        <div class="dash-subtitle mb-1">Online revenue today</div>
        <div class="dash-metric text-2xl sm:text-3xl">ETB {{ Number(summary.online_revenue_today || 41300).toLocaleString() }}</div>
        <div class="text-xs text-[#7D6E64] mt-1">Chapa and Telebirr</div>
      </div>

      <div class="dash-card p-5">
        <div class="dash-subtitle mb-1">Pay on pickup/delivery</div>
        <div class="dash-metric text-2xl sm:text-3xl">ETB {{ Number(summary.pay_on_pickup_delivery || 6900).toLocaleString() }}</div>
        <div class="text-xs text-[#7D6E64] mt-1">To be collected</div>
      </div>

      <div class="dash-card p-5">
        <div class="dash-subtitle mb-1">Failed</div>
        <div class="dash-metric text-2xl sm:text-3xl text-[#C62828]">{{ summary.failed_today || 3 }}</div>
        <div class="text-xs text-[#7D6E64] mt-1">Today</div>
      </div>

      <div class="dash-card p-5">
        <div class="dash-subtitle mb-1">Refunds</div>
        <div class="dash-metric text-2xl sm:text-3xl">ETB {{ Number(summary.refunds_this_week || 850).toLocaleString() }}</div>
        <div class="text-xs text-[#7D6E64] mt-1">1 refund this week</div>
      </div>
    </div>

    <!-- Main Content: Transactions Table + Refund Form -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
      <!-- Transactions Table (Col span 2) -->
      <div class="lg:col-span-2 bg-white border border-[#E9E0D4] rounded-xl shadow-sm overflow-hidden">
        <div class="p-5 border-b border-[#EFE8DF]">
          <h3 class="font-serif font-bold text-base sm:text-lg text-[#2A1D15]">Transactions</h3>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full min-w-[700px] text-left text-sm">
            <thead class="bg-[#FAF6F0] dark:bg-[#1C120C] text-[11px] font-bold text-[#827165] dark:text-[#A8988E] tracking-wider uppercase border-b border-[#EFE8DF] dark:border-[#382318]">
              <tr>
                <th class="py-3 px-4">Order</th>
                <th class="py-3 px-4">Customer</th>
                <th class="py-3 px-4">Method</th>
                <th class="py-3 px-4">Amount</th>
                <th class="py-3 px-4">Transaction Ref</th>
                <th class="py-3 px-4">Date</th>
                <th class="py-3 px-4 text-center">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#F3EDE4] dark:divide-[#382318]">
              <tr
                v-for="p in payments"
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
                <td class="py-3.5 px-4 text-xs text-[#6A5A50] dark:text-[#C5B7AF]">
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
                      'bg-[#EAF5EA] text-[#2E7D32] border border-[#C8E6C9]': p.status === 'Paid' || p.status === 'PAID_TEST',
                      'bg-[#FFF8E6] text-[#A67503] border border-[#FEE39E]': p.status === 'Pending' || p.status === 'PENDING',
                      'bg-[#FDEAEA] text-[#C62828] border border-[#FFCDD2]': p.status === 'Failed' || p.status === 'FAILED_TEST',
                      'bg-[#F3EFE9] text-[#7A6B60] border border-[#DDD3C7]': p.status === 'Refunded' || p.status === 'CANCELLED_TEST'
                    }"
                  >
                    {{ p.status === 'PAID_TEST' ? 'PAID (TEST)' : (p.status === 'FAILED_TEST' ? 'FAILED (TEST)' : (p.status === 'CANCELLED_TEST' ? 'CANCELLED (TEST)' : p.status)) }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="p-4 bg-[#FCFAF7] border-t border-[#EFE8DF] text-xs text-[#8A7B71]">
          Orders are confirmed only on a signed gateway webhook, not on the browser redirect. Duplicate callbacks are ignored by transaction reference.
        </div>
      </div>

      <!-- Right Card: Refund Form -->
      <div class="bg-white border border-[#E9E0D4] rounded-xl shadow-sm p-6 space-y-4">
        <h3 class="font-serif font-bold text-base sm:text-lg text-[#2A1D15]">Refund</h3>

        <form @submit.prevent="submitRefund" class="space-y-4">
          <div>
            <label class="block text-xs font-medium text-[#65554B] mb-1">Order</label>
            <input
              v-model="refundForm.order_number"
              type="text"
              placeholder="e.g. ORD-1007"
              class="w-full border border-[#D8CDC0] rounded-lg px-3 py-2 text-sm text-[#2D231D] bg-[#FDFBF7] focus:ring-1 focus:ring-[#BA5420]"
              required
            />
          </div>

          <div>
            <label class="block text-xs font-medium text-[#65554B] mb-1">Amount (ETB)</label>
            <input
              v-model.number="refundForm.amount"
              type="number"
              placeholder="850"
              class="w-full border border-[#D8CDC0] rounded-lg px-3 py-2 text-sm text-[#2D231D] bg-[#FDFBF7] focus:ring-1 focus:ring-[#BA5420]"
              required
            />
          </div>

          <div>
            <label class="block text-xs font-medium text-[#65554B] mb-1">Reason</label>
            <textarea
              v-model="refundForm.reason"
              rows="3"
              placeholder="Branch could not fulfil: damaged batch"
              class="w-full border border-[#D8CDC0] rounded-lg px-3 py-2 text-sm text-[#2D231D] bg-[#FDFBF7] focus:ring-1 focus:ring-[#BA5420]"
              required
            ></textarea>
          </div>

          <div class="p-3 bg-[#FCFAF7] border border-[#EFE8DF] rounded-lg text-xs text-[#7A6B60]">
            Refund goes to the original payment method and is logged.
          </div>

          <button
            type="submit"
            :disabled="refunding"
            class="w-full py-2.5 px-4 bg-[#BA5420] hover:bg-[#9E4314] text-white font-medium text-sm rounded-lg transition shadow-sm disabled:opacity-50"
          >
            {{ refunding ? 'Processing...' : 'Issue refund' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useToastStore } from '../../stores/toast';
import api from '../../api';

const toast = useToastStore();

const payments = ref([]);
const summary = ref({
  online_revenue_today: 41300,
  pay_on_pickup_delivery: 6900,
  failed_today: 3,
  refunds_this_week: 850,
});

const refundForm = ref({
  order_number: 'ORD-1007',
  amount: 850,
  reason: 'Branch could not fulfil: damaged batch',
});

const refunding = ref(false);

const loadPayments = async () => {
  try {
    const res = await api.get('/admin/payments');
    payments.value = res.data.payments;
    summary.value = res.data.summary;
  } catch (err) {
    console.error('Failed to load payments', err);
  }
};

const populateRefund = (p) => {
  if (p.order_number && p.order_number !== '—') {
    refundForm.value.order_number = p.order_number;
    refundForm.value.amount = p.amount;
  }
};

const submitRefund = async () => {
  refunding.value = true;
  try {
    await api.post('/admin/payments/refund', refundForm.value);
    toast.success(`Refund issued successfully for ${refundForm.value.order_number} (ETB ${refundForm.value.amount.toLocaleString()})`);
    await loadPayments();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to issue refund');
  } finally {
    refunding.value = false;
  }
};

const exportCSV = () => {
  const headers = ['Order', 'Customer', 'Branch', 'Method', 'Amount', 'Transaction Ref', 'Date', 'Status', 'Is Test'];
  const rows = payments.value.map(p => [
    `"${p.order_number}"`,
    `"${p.customer_name || 'Customer'}"`,
    `"${p.branch_name}"`,
    `"${p.method}"`,
    p.amount,
    `"${p.transaction_ref}"`,
    `"${p.order_date || p.created_at}"`,
    `"${p.status}"`,
    p.is_test ? 'YES' : 'NO'
  ]);
  const csvContent = 'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows.map(e => e.join(','))].join('\n');
  const encodedUri = encodeURI(csvContent);
  const link = document.createElement('a');
  link.setAttribute('href', encodedUri);
  link.setAttribute('download', `payments.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

onMounted(() => {
  loadPayments();
});
</script>
