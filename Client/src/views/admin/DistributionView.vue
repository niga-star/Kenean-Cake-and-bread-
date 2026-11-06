<template>
  <div class="space-y-6 max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <h2 class="text-2xl font-serif font-bold text-[#2A1D15]">Branch Distribution and Stock Transfer Management</h2>
      <div class="flex items-center space-x-3">
        <button
          @click="scrollToRequests"
          class="px-4 py-2 text-sm font-medium bg-white border border-[#D8CDC0] hover:bg-[#F4ECE3] text-[#4A3B32] rounded-lg transition shadow-sm"
        >
          Stock requests ({{ data.stock_requests ? data.stock_requests.length : 0 }})
        </button>
        <router-link
          to="/admin/transfers/new"
          class="px-4 py-2 text-sm font-medium bg-[#BA5420] hover:bg-[#9E4314] text-white rounded-lg transition shadow-sm"
        >
          New transfer
        </router-link>
      </div>
    </div>

    <!-- Alert Banner -->
    <div v-if="successMsg" class="p-3 bg-[#EBF7EB] border border-[#C5E9C6] rounded-lg text-xs text-[#1E7422] flex items-center justify-between">
      <span>{{ successMsg }}</span>
      <button @click="successMsg = ''" class="text-[#1E7422] font-bold ml-2">&times;</button>
    </div>
    <div v-if="errorMsg" class="p-3 bg-[#FEECEC] border border-[#F5C2C2] rounded-lg text-xs text-[#B91C1C] flex items-center justify-between">
      <span>{{ errorMsg }}</span>
      <button @click="errorMsg = ''" class="text-[#B91C1C] font-bold ml-2">&times;</button>
    </div>

    <!-- 2 Rows of 4 Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Row 1 -->
      <div class="bg-white border border-[#E9E0D4] rounded-xl p-5 shadow-sm">
        <div class="text-xs font-medium text-[#7D6E64] mb-1">Main branch stock</div>
        <div class="text-3xl font-bold text-[#241710]">{{ Number(data.main_branch_stock ?? 0).toLocaleString() }}</div>
        <div class="text-xs text-[#8A7B71] mt-1">units on hand</div>
      </div>

      <div class="bg-white border border-[#E9E0D4] rounded-xl p-5 shadow-sm">
        <div class="text-xs font-medium text-[#7D6E64] mb-1">Sent today</div>
        <div class="text-3xl font-bold text-[#241710]">{{ data.sent_today_units ?? 0 }}</div>
        <div class="text-xs text-[#8A7B71] mt-1">{{ data.sent_today_count ?? 0 }} transfer{{ data.sent_today_count === 1 ? '' : 's' }}</div>
      </div>

      <div class="bg-white border border-[#E9E0D4] rounded-xl p-5 shadow-sm">
        <div class="text-xs font-medium text-[#7D6E64] mb-1">Sent this week</div>
        <div class="text-3xl font-bold text-[#241710]">{{ data.sent_week_units ?? 0 }}</div>
        <div class="text-xs text-[#8A7B71] mt-1">{{ data.sent_week_count ?? 0 }} transfers</div>
      </div>

      <div class="bg-white border border-[#E9E0D4] rounded-xl p-5 shadow-sm">
        <div class="text-xs font-medium text-[#7D6E64] mb-1">Sent this month</div>
        <div class="text-3xl font-bold text-[#241710]">{{ Number(data.sent_month_units ?? 0).toLocaleString() }}</div>
        <div class="text-xs text-[#8A7B71] mt-1">{{ data.sent_month_count ?? 0 }} transfers</div>
      </div>

      <!-- Row 2 -->
      <div class="bg-white border border-[#E9E0D4] rounded-xl p-5 shadow-sm">
        <div class="text-xs font-medium text-[#7D6E64] mb-1">Awaiting dispatch</div>
        <div class="text-3xl font-bold text-[#241710]">{{ data.awaiting_dispatch ?? 0 }}</div>
        <div class="text-xs text-[#8A7B71] mt-1">Draft or approved</div>
      </div>

      <div class="bg-white border border-[#E9E0D4] rounded-xl p-5 shadow-sm">
        <div class="text-xs font-medium text-[#7D6E64] mb-1">In transit</div>
        <div class="text-3xl font-bold text-[#241710]">{{ data.in_transit_units ?? 0 }}</div>
        <div class="text-xs text-[#8A7B71] mt-1 truncate">{{ data.in_transit_note || 'None' }}</div>
      </div>

      <div class="bg-white border border-[#E9E0D4] rounded-xl p-5 shadow-sm">
        <div class="text-xs font-medium text-[#7D6E64] mb-1">Received</div>
        <div class="text-3xl font-bold text-[#241710]">{{ Number(data.received_units ?? 0).toLocaleString() }}</div>
        <div class="text-xs text-[#8A7B71] mt-1">units confirmed</div>
      </div>

      <div class="bg-white border border-[#E9E0D4] rounded-xl p-5 shadow-sm">
        <div class="text-xs font-medium text-[#7D6E64] mb-1">Missing / damaged</div>
        <div class="text-3xl font-bold text-[#241710]">{{ data.missing_units ?? 0 }} / {{ data.damaged_units ?? 0 }}</div>
        <div class="text-xs text-[#8A7B71] mt-1 truncate">{{ data.dispute_note || 'None' }}</div>
      </div>
    </div>

    <!-- Table 1: Latest transfer per branch -->
    <div class="bg-white border border-[#E9E0D4] rounded-xl shadow-sm overflow-hidden">
      <div class="p-5 border-b border-[#EFE8DF] flex items-center justify-between">
        <h3 class="font-semibold text-base text-[#2A1D15]">Latest transfer per branch</h3>
        <button
          @click="loadDashboard"
          class="text-xs font-medium text-[#BA5420] hover:text-[#9E4314]"
        >
          Refresh
        </button>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead class="bg-[#FAF6F0] text-[11px] font-bold text-[#827165] tracking-wider uppercase border-b border-[#EFE8DF]">
            <tr>
              <th class="py-3 px-5">Branch</th>
              <th class="py-3 px-4">Transfer</th>
              <th class="py-3 px-4 text-center">Sent</th>
              <th class="py-3 px-4 text-center">Received</th>
              <th class="py-3 px-4 text-center">Missing</th>
              <th class="py-3 px-4 text-center">Damaged</th>
              <th class="py-3 px-4 text-center">Status</th>
              <th class="py-3 px-5 text-right">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#F3EDE4]">
            <tr v-for="t in data.latest_transfers" :key="t.transfer" class="hover:bg-[#FCFAF7] transition">
              <td class="py-3.5 px-5 font-medium text-[#382C24]">{{ t.branch }}</td>
              <td class="py-3.5 px-4 font-mono text-xs text-[#55463D]">{{ t.transfer }}</td>
              <td class="py-3.5 px-4 text-center text-[#2A1D15]">{{ t.sent }}</td>
              <td class="py-3.5 px-4 text-center text-[#2A1D15]">{{ t.received }}</td>
              <td class="py-3.5 px-4 text-center text-[#2A1D15]">{{ t.missing }}</td>
              <td class="py-3.5 px-4 text-center text-[#2A1D15]">{{ t.damaged }}</td>
              <td class="py-3.5 px-4 text-center">
                <span
                  class="inline-block px-2.5 py-0.5 text-xs font-semibold rounded-full"
                  :class="{
                    'bg-[#FDEAEA] text-[#C62828] border border-[#FFCDD2]': t.status === 'Disputed',
                    'bg-[#EBF3FB] text-[#1565C0] border border-[#BBDEFB]': t.status === 'In transit' || t.status === 'Dispatched',
                    'bg-[#FFF3E0] text-[#E65100] border border-[#FFE0B2]': t.status === 'Partially received' || t.status === 'Approved',
                    'bg-[#EAF5EA] text-[#2E7D32] border border-[#C8E6C9]': t.status === 'Received',
                    'bg-[#EAE2D8] text-[#55463C] border border-[#D5C7B8]': t.status === 'Draft' || t.status === 'Cancelled'
                  }"
                >
                  {{ t.status }}
                </span>
              </td>
              <td class="py-3.5 px-5 text-right space-x-2">
                <!-- Disputed: Resolve -->
                <button
                  v-if="t.status === 'Disputed'"
                  @click="openResolveModal(t)"
                  class="px-3 py-1 text-xs font-medium bg-[#BA5420] text-white hover:bg-[#9E4314] rounded-md shadow-sm transition"
                >
                  Resolve
                </button>

                <!-- In transit or Dispatched: Receive & Remind -->
                <template v-else-if="t.status === 'In transit' || t.status === 'Dispatched'">
                  <button
                    @click="openReceiveModal(t)"
                    class="px-3 py-1 text-xs font-medium bg-[#2E7D32] hover:bg-[#1B5E20] text-white rounded-md shadow-sm transition"
                  >
                    Receive
                  </button>
                  <button
                    @click="sendReminder(t)"
                    class="px-3 py-1 text-xs font-medium bg-white border border-[#D8CDC0] hover:bg-[#F4ECE3] text-[#4A3B32] rounded-md shadow-sm transition"
                  >
                    Remind
                  </button>
                </template>

                <!-- Approved: Dispatch & Cancel -->
                <template v-else-if="t.status === 'Approved'">
                  <button
                    @click="dispatchTransfer(t)"
                    class="px-3 py-1 text-xs font-medium bg-[#BA5420] hover:bg-[#9E4314] text-white rounded-md shadow-sm transition"
                  >
                    Dispatch
                  </button>
                  <button
                    @click="cancelTransfer(t)"
                    class="px-2 py-1 text-xs font-medium text-red-600 hover:text-red-800"
                  >
                    Cancel
                  </button>
                </template>

                <!-- Draft: Approve & Cancel -->
                <template v-else-if="t.status === 'Draft'">
                  <button
                    @click="approveTransfer(t)"
                    class="px-3 py-1 text-xs font-medium bg-[#2E7D32] hover:bg-[#1B5E20] text-white rounded-md shadow-sm transition"
                  >
                    Approve
                  </button>
                  <button
                    @click="cancelTransfer(t)"
                    class="px-2 py-1 text-xs font-medium text-red-600 hover:text-red-800"
                  >
                    Cancel
                  </button>
                </template>

                <!-- Partially received: Receive remaining -->
                <template v-else-if="t.status === 'Partially received'">
                  <button
                    @click="openReceiveModal(t)"
                    class="px-3 py-1 text-xs font-medium bg-[#2E7D32] hover:bg-[#1B5E20] text-white rounded-md shadow-sm transition"
                  >
                    Receive
                  </button>
                </template>

                <!-- Received or Cancelled: View -->
                <button
                  v-else
                  @click="openViewModal(t)"
                  class="px-3 py-1 text-xs font-medium bg-white border border-[#D8CDC0] hover:bg-[#F4ECE3] text-[#4A3B32] rounded-md shadow-sm transition"
                >
                  View
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Bottom Two Columns: Stock Requests + Rules -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
      <!-- Stock Requests from Branches (Col span 2) -->
      <div id="stock-requests" class="lg:col-span-2 bg-white border border-[#E9E0D4] rounded-xl shadow-sm overflow-hidden">
        <div class="p-5 border-b border-[#EFE8DF]">
          <h3 class="font-semibold text-base text-[#2A1D15]">Stock requests from branches</h3>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm">
            <thead class="bg-[#FAF6F0] text-[11px] font-bold text-[#827165] tracking-wider uppercase border-b border-[#EFE8DF]">
              <tr>
                <th class="py-3 px-5">Branch</th>
                <th class="py-3 px-4">Product</th>
                <th class="py-3 px-4 text-center">Qty</th>
                <th class="py-3 px-4">Requested</th>
                <th class="py-3 px-5 text-right">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#F3EDE4]">
              <tr v-if="!data.stock_requests || data.stock_requests.length === 0">
                <td colspan="5" class="py-6 text-center text-xs text-[#8A7B71]">No pending stock requests.</td>
              </tr>
              <tr v-for="req in data.stock_requests" :key="req.request_id" class="hover:bg-[#FCFAF7] transition">
                <td class="py-3.5 px-5 font-medium text-[#382C24]">{{ req.branch }}</td>
                <td class="py-3.5 px-4 text-[#4A3B32]">{{ req.product }}</td>
                <td class="py-3.5 px-4 text-center font-semibold text-[#241710]">{{ req.qty }}</td>
                <td class="py-3.5 px-4 text-[#7A6B60] text-xs">{{ req.requested }}</td>
                <td class="py-3.5 px-5 text-right">
                  <button
                    @click="convertRequest(req)"
                    class="px-3 py-1.5 text-xs font-medium bg-white border border-[#D8CDC0] hover:bg-[#F4ECE3] text-[#4A3B32] rounded-md shadow-sm transition"
                  >
                    Convert to transfer
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Rules Box -->
      <div class="bg-white border border-[#E9E0D4] rounded-xl shadow-sm p-6 space-y-3">
        <h3 class="font-semibold text-base text-[#2A1D15]">Rules</h3>
        <p class="text-xs leading-relaxed text-[#78695E]">
          Unreceived after 24 hours: reminder to the branch and alert to Main Branch. Dispatched transfers cannot be cancelled; they are received or written off through dispute resolution.
        </p>
        <div class="pt-2 text-[11px] text-[#8F7F74] space-y-1">
          <div>• <strong>Approval:</strong> Super Admin approves if transfer exceeds 200 units.</div>
          <div>• <strong>Dispatch:</strong> Drops Main Branch on-hand; units become in transit.</div>
          <div>• <strong>Receive:</strong> Received + damaged + missing must strictly equal sent.</div>
          <div>• <strong>Destination:</strong> Only confirmed received units increase on-hand stock.</div>
        </div>
      </div>
    </div>

    <!-- Receive Modal -->
    <div v-if="activeReceiveTransfer" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50 overflow-y-auto">
      <div class="bg-white border border-[#E7DFD5] rounded-xl max-w-2xl w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-[#EFE8DF] pb-3">
          <div>
            <h3 class="text-lg font-serif font-bold text-[#2A1D15]">
              Confirm Receipt: {{ activeReceiveTransfer.transfer }}
            </h3>
            <p class="text-xs text-[#7A6B60]">
              Destination: {{ activeReceiveTransfer.branch }} | Source: Main Branch
            </p>
          </div>
          <button @click="activeReceiveTransfer = null" class="text-gray-400 hover:text-gray-600 text-lg font-bold">&times;</button>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="text-[#88786B] uppercase border-b border-[#EFE8DF] font-semibold bg-[#FAF6F0]">
              <tr>
                <th class="py-2 px-3">Product</th>
                <th class="py-2 px-2 text-center">Sent</th>
                <th class="py-2 px-2 text-center w-20">Received</th>
                <th class="py-2 px-2 text-center w-20">Damaged</th>
                <th class="py-2 px-2 text-center w-20">Missing</th>
                <th class="py-2 px-3 text-center">Check (R + D + M = Sent)</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#F3EDE4]">
              <tr v-for="item in receiveLines" :key="item.transfer_item_id">
                <td class="py-2.5 px-3 font-medium text-[#382C24]">{{ item.product_name }}</td>
                <td class="py-2.5 px-2 text-center font-bold text-[#2A1D15]">{{ item.quantity_sent }}</td>
                <td class="py-2.5 px-2 text-center">
                  <input
                    type="number"
                    min="0"
                    :max="item.quantity_sent"
                    v-model.number="item.quantity_received"
                    class="w-16 border border-[#D8CDC0] rounded px-2 py-1 text-center font-bold text-[#2A1D15] bg-[#FDFBF7]"
                  />
                </td>
                <td class="py-2.5 px-2 text-center">
                  <input
                    type="number"
                    min="0"
                    :max="item.quantity_sent"
                    v-model.number="item.damaged_quantity"
                    class="w-16 border border-[#D8CDC0] rounded px-2 py-1 text-center font-bold text-[#2A1D15] bg-[#FDFBF7]"
                  />
                </td>
                <td class="py-2.5 px-2 text-center">
                  <input
                    type="number"
                    min="0"
                    :max="item.quantity_sent"
                    v-model.number="item.missing_quantity"
                    class="w-16 border border-[#D8CDC0] rounded px-2 py-1 text-center font-bold text-[#2A1D15] bg-[#FDFBF7]"
                  />
                </td>
                <td class="py-2.5 px-3 text-center">
                  <span
                    class="px-2 py-0.5 rounded text-[11px] font-mono font-medium"
                    :class="(item.quantity_received + item.damaged_quantity + item.missing_quantity) === item.quantity_sent ? 'bg-[#EAF5EA] text-[#2E7D32]' : 'bg-[#FDEAEA] text-[#C62828]'"
                  >
                    {{ item.quantity_received + item.damaged_quantity + item.missing_quantity }} / {{ item.quantity_sent }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="hasDiscrepancyInReceive">
          <label class="block text-xs font-semibold text-[#C62828] mb-1">
            Discrepancy Reason (Mandatory when damaged or missing > 0)
          </label>
          <input
            v-model="receiveDiscrepancyReason"
            type="text"
            placeholder="e.g. 5 units crushed during transport or not in delivery crate"
            class="w-full bg-[#FDFBF7] border border-[#E57373] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-1 focus:ring-[#BA5420]"
          />
        </div>

        <div class="text-[11px] text-[#8F7F74]">
          * Received + damaged + missing must equal sent for every line before saving.
        </div>

        <div class="flex justify-end space-x-3 pt-2 border-t border-[#EFE8DF]">
          <button
            @click="activeReceiveTransfer = null"
            class="px-4 py-2 text-xs font-medium text-[#6A5A50] hover:bg-[#F3EFE9] rounded-lg"
          >
            Cancel
          </button>
          <button
            @click="submitReceive"
            :disabled="submittingReceive"
            class="px-4 py-2 text-xs font-medium bg-[#2E7D32] hover:bg-[#1B5E20] text-white rounded-lg shadow-sm"
          >
            {{ submittingReceive ? 'Confirming...' : 'Confirm Receipt' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Dispute Resolution Modal -->
    <div v-if="activeDispute" class="fixed inset-0 bg-black/40 flex items-center justify-center p-4 z-50">
      <div class="bg-white border border-[#E7DFD5] rounded-xl max-w-md w-full p-6 shadow-xl space-y-4">
        <h3 class="text-lg font-serif font-bold text-[#2A1D15]">
          Resolve Dispute: {{ activeDispute.transfer }}
        </h3>
        <p class="text-xs text-[#7A6B60]">
          Sent to {{ activeDispute.branch }}. Missing: {{ activeDispute.missing }}, Damaged: {{ activeDispute.damaged }}.
        </p>

        <div class="space-y-3">
          <div>
            <label class="block text-xs font-medium text-[#65554B] mb-1">Resolution Decision</label>
            <select
              v-model="resolveForm.resolution"
              class="w-full border border-[#D8CDC0] rounded-lg px-3 py-2 text-sm text-[#2D231D] bg-[#FDFBF7]"
            >
              <option value="written_off">Write off discrepancy</option>
              <option value="found">Items located and confirmed (add to branch stock)</option>
              <option value="re_sent">Re-dispatch replacement units from Main Branch</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-medium text-[#65554B] mb-1">Resolution Notes</label>
            <textarea
              v-model="resolveForm.notes"
              rows="3"
              placeholder="Provide reason for audit trail..."
              class="w-full border border-[#D8CDC0] rounded-lg px-3 py-2 text-sm text-[#2D231D] bg-[#FDFBF7]"
            ></textarea>
          </div>
        </div>

        <div class="flex justify-end space-x-3 pt-2">
          <button
            @click="activeDispute = null"
            class="px-4 py-2 text-xs font-medium text-[#6A5A50] hover:bg-[#F3EFE9] rounded-lg"
          >
            Cancel
          </button>
          <button
            @click="submitResolve"
            class="px-4 py-2 text-xs font-medium bg-[#BA5420] text-white hover:bg-[#9E4314] rounded-lg shadow-sm"
          >
            Confirm Resolution
          </button>
        </div>
      </div>
    </div>

    <!-- View Transfer Modal -->
    <div v-if="activeViewTransfer" class="fixed inset-0 bg-black/40 flex items-center justify-center p-4 z-50">
      <div class="bg-white border border-[#E7DFD5] rounded-xl max-w-lg w-full p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between border-b border-[#EFE8DF] pb-2">
          <h3 class="text-base font-serif font-bold text-[#2A1D15]">
            Transfer Details: {{ activeViewTransfer.transfer }}
          </h3>
          <button @click="activeViewTransfer = null" class="text-gray-400 hover:text-gray-600 text-lg font-bold">&times;</button>
        </div>

        <div class="space-y-2 text-xs text-[#55463C]">
          <div><strong>Destination:</strong> {{ activeViewTransfer.branch }}</div>
          <div><strong>Status:</strong> {{ activeViewTransfer.status }}</div>
          <div><strong>Notes:</strong> {{ activeViewTransfer.notes || '—' }}</div>
        </div>

        <div class="overflow-x-auto pt-2">
          <table class="w-full text-left text-xs">
            <thead class="text-[#88786B] uppercase border-b border-[#EFE8DF] font-semibold bg-[#FAF6F0]">
              <tr>
                <th class="py-2 px-3">Product</th>
                <th class="py-2 px-2 text-center">Sent</th>
                <th class="py-2 px-2 text-center">Rcv</th>
                <th class="py-2 px-2 text-center">Dmg</th>
                <th class="py-2 px-2 text-center">Miss</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#F3EDE4]">
              <tr v-for="it in activeViewTransfer.items" :key="it.transfer_item_id">
                <td class="py-2 px-3">{{ it.product?.product_name || 'Product' }}</td>
                <td class="py-2 px-2 text-center">{{ it.quantity_sent }}</td>
                <td class="py-2 px-2 text-center font-semibold text-[#2E7D32]">{{ it.quantity_received }}</td>
                <td class="py-2 px-2 text-center text-[#C62828]">{{ it.damaged_quantity }}</td>
                <td class="py-2 px-2 text-center text-[#C62828]">{{ it.missing_quantity }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="flex justify-end pt-2">
          <button
            @click="activeViewTransfer = null"
            class="px-4 py-2 text-xs font-medium bg-white border border-[#D8CDC0] hover:bg-[#F4ECE3] text-[#4A3B32] rounded-lg"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '../../api';

const data = ref({
  main_branch_stock: 0,
  sent_today_units: 0,
  sent_today_count: 0,
  sent_week_units: 0,
  sent_week_count: 0,
  sent_month_units: 0,
  sent_month_count: 0,
  awaiting_dispatch: 0,
  in_transit_units: 0,
  in_transit_note: 'None',
  received_units: 0,
  missing_units: 0,
  damaged_units: 0,
  dispute_note: 'None',
  latest_transfers: [],
  stock_requests: [],
});

const activeDispute = ref(null);
const activeReceiveTransfer = ref(null);
const activeViewTransfer = ref(null);
const receiveLines = ref([]);
const receiveDiscrepancyReason = ref('');
const submittingReceive = ref(false);

const successMsg = ref('');
const errorMsg = ref('');
const resolveForm = ref({
  resolution: 'written_off',
  notes: 'Reconciled missing items during delivery count.',
});

const hasDiscrepancyInReceive = computed(() => {
  return receiveLines.value.some((l) => (l.damaged_quantity > 0 || l.missing_quantity > 0));
});

const loadDashboard = async () => {
  try {
    const res = await api.get('/admin/transfers/dashboard');
    data.value = res.data;
  } catch (err) {
    console.error('Failed to load distribution dashboard', err);
    errorMsg.value = 'Failed to load distribution data.';
  }
};

const openResolveModal = (t) => {
  activeDispute.value = t;
  resolveForm.value.notes = `Resolving discrepancy for ${t.transfer}: ${t.missing} missing, ${t.damaged} damaged.`;
};

const submitResolve = async () => {
  if (!activeDispute.value) return;
  try {
    await api.post(`/admin/transfers/${activeDispute.value.transfer_id}/resolve`, resolveForm.value);
    successMsg.value = `Dispute for ${activeDispute.value.transfer} resolved successfully!`;
    activeDispute.value = null;
    await loadDashboard();
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to resolve dispute');
  }
};

const openReceiveModal = (t) => {
  activeReceiveTransfer.value = t;
  receiveDiscrepancyReason.value = t.notes || '';
  receiveLines.value = (t.items || []).map((it) => ({
    transfer_item_id: it.transfer_item_id,
    product_name: it.product?.product_name || `Product #${it.product_id}`,
    quantity_sent: it.quantity_sent,
    quantity_received: it.quantity_received > 0 ? it.quantity_received : it.quantity_sent,
    damaged_quantity: it.damaged_quantity || 0,
    missing_quantity: it.missing_quantity || 0,
  }));
};

const submitReceive = async () => {
  if (!activeReceiveTransfer.value) return;

  // Strict check: for every line, received + damaged + missing == sent
  for (const line of receiveLines.value) {
    const sum = (line.quantity_received || 0) + (line.damaged_quantity || 0) + (line.missing_quantity || 0);
    if (sum !== line.quantity_sent) {
      alert(`For line "${line.product_name}", received (${line.quantity_received}) + damaged (${line.damaged_quantity}) + missing (${line.missing_quantity}) = ${sum}, but sent is ${line.quantity_sent}. The count must strictly equal sent before saving.`);
      return;
    }
  }

  // Check discrepancy reason
  if (hasDiscrepancyInReceive.value && !receiveDiscrepancyReason.value.trim()) {
    alert('A discrepancy reason is mandatory when received quantity differs from sent.');
    return;
  }

  submittingReceive.value = true;
  try {
    const payload = {
      items: receiveLines.value.map((l) => ({
        transfer_item_id: l.transfer_item_id,
        quantity_received: l.quantity_received,
        damaged_quantity: l.damaged_quantity,
        missing_quantity: l.missing_quantity,
        discrepancy_reason: (l.damaged_quantity > 0 || l.missing_quantity > 0) ? receiveDiscrepancyReason.value : null,
      })),
    };

    const res = await api.post(`/admin/transfers/${activeReceiveTransfer.value.transfer_id}/receive`, payload);
    successMsg.value = `Transfer ${activeReceiveTransfer.value.transfer} receipt confirmed! Status: ${res.data.status}.`;
    activeReceiveTransfer.value = null;
    await loadDashboard();
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to confirm receipt');
  } finally {
    submittingReceive.value = false;
  }
};

const openViewModal = (t) => {
  activeViewTransfer.value = t;
};

const sendReminder = async (t) => {
  try {
    const res = await api.post(`/admin/transfers/${t.transfer_id}/remind`);
    successMsg.value = res.data.message || `Reminder dispatched for transfer ${t.transfer}.`;
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to send reminder');
  }
};

const dispatchTransfer = async (t) => {
  try {
    await api.post(`/admin/transfers/${t.transfer_id}/dispatch`);
    successMsg.value = `Transfer ${t.transfer} dispatched! Stock deducted from Main Branch.`;
    await loadDashboard();
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to dispatch transfer');
  }
};

const approveTransfer = async (t) => {
  try {
    await api.post(`/admin/transfers/${t.transfer_id}/approve`);
    successMsg.value = `Transfer ${t.transfer} approved!`;
    await loadDashboard();
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to approve transfer');
  }
};

const cancelTransfer = async (t) => {
  if (!confirm(`Are you sure you want to cancel transfer ${t.transfer}?`)) return;
  try {
    await api.post(`/admin/transfers/${t.transfer_id}/cancel`);
    successMsg.value = `Transfer ${t.transfer} cancelled!`;
    await loadDashboard();
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to cancel transfer');
  }
};

const convertRequest = async (req) => {
  try {
    const res = await api.post(`/admin/stock-requests/${req.request_id}/convert`);
    successMsg.value = `Stock request #${req.request_id} converted to ${res.data.transfer_number}!`;
    await loadDashboard();
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to convert request');
  }
};

const scrollToRequests = () => {
  const el = document.getElementById('stock-requests');
  if (el) el.scrollIntoView({ behavior: 'smooth' });
};

onMounted(() => {
  loadDashboard();
});
</script>
