<template>
  <transition
    enter-active-class="transition duration-200 ease-out"
    enter-from-class="opacity-0 scale-95"
    enter-to-class="opacity-100 scale-100"
    leave-active-class="transition duration-150 ease-in"
    leave-from-class="opacity-100 scale-100"
    leave-to-class="opacity-0 scale-95"
  >
    <div
      v-if="isOpen"
      class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-3 sm:p-6 font-sans selection:bg-[#FCE7F0] selection:text-[#DE1B54]"
      role="dialog"
      aria-modal="true"
    >
      <!-- Backdrop Blur Overlay -->
      <div
        class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity"
        @click="close"
      ></div>

      <!-- Modal Card -->
      <div
        class="relative w-full max-w-2xl bg-white dark:bg-[#1E120B] rounded-3xl shadow-2xl border border-gray-200 dark:border-[#382318] overflow-hidden flex flex-col max-h-[90vh] z-10"
      >
        <!-- Modal Header -->
        <div class="px-5 sm:px-7 py-4 bg-[#FAF7F5] dark:bg-[#2A1810] border-b border-gray-100 dark:border-[#382318] flex items-center justify-between">
          <div class="flex items-center space-x-2.5">
            <span class="text-2xl">📍</span>
            <div>
              <h2 class="font-serif font-black text-base sm:text-lg text-[#2A1810] dark:text-[#FAF3EE] leading-tight">
                {{ localeStore.t('choose_branch_title') || 'Choose Your Bakery Branch' }}
              </h2>
              <p class="text-[11px] text-[#786B60] dark:text-[#C5B7AF] mt-0.5">
                {{ localeStore.isAmharic ? 'እያንዳንዱ ትዕዛዝ እና የማድረሻ ክፍያ በቀጥታ ከተመረጠው ቅርንጫፍ ጋር የተገናኘ ነው::' : 'Every order, oven batch, and delivery fee strictly belongs to your chosen location.' }}
              </p>
            </div>
          </div>

          <button
            @click="close"
            class="w-8 h-8 rounded-full bg-white dark:bg-[#1E120B] border border-gray-200 dark:border-[#382318] hover:bg-rose-50 dark:hover:bg-[#341822] text-[#786B60] dark:text-gray-300 hover:text-[#DE1B54] flex items-center justify-center text-sm cursor-pointer transition"
            title="Close"
          >
            ✕
          </button>
        </div>

        <!-- Search Bar & Filter -->
        <div class="p-4 sm:p-5 bg-white dark:bg-[#1E120B] border-b border-gray-100 dark:border-[#382318] space-y-3">
          <div class="relative">
            <span class="absolute left-3.5 top-2.5 text-gray-400">🔍</span>
            <input
              v-model="searchQuery"
              type="text"
              :placeholder="localeStore.isAmharic ? 'ቅርንጫፍ በስም ወይም አድራሻ ይፈልጉ (ለምሳሌ: መገናኛ፣ ቦሌ፣ ፒያሳ)...' : 'Search branch by name, city, or address (e.g. Megenagna, Bole, Piassa)...'"
              class="w-full bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-200 dark:border-[#382318] focus:border-[#DE1B54] focus:bg-white dark:focus:bg-[#1E120B] rounded-2xl py-2.5 pl-10 pr-4 text-xs text-[#2A1810] dark:text-[#FAF3EE] placeholder:text-[#A8988E] focus:outline-none transition min-h-[42px]"
            />
            <button
              v-if="searchQuery"
              @click="searchQuery = ''"
              class="absolute right-3.5 top-2.5 text-xs text-gray-400 hover:text-[#DE1B54] cursor-pointer"
            >
              {{ localeStore.isAmharic ? 'አጽዳ' : 'Clear' }}
            </button>
          </div>

          <!-- Active branch pill badge -->
          <div class="flex items-center justify-between text-xs px-1">
            <div class="flex items-center space-x-1.5 text-[#786B60] dark:text-[#C5B7AF]">
              <span>{{ localeStore.isAmharic ? 'የተመረጠ ቅርንጫፍ:' : 'Currently Shopping:' }}</span>
              <strong class="text-[#DE1B54] dark:text-rose-400">{{ localeStore.tBranch(activeBranchName) }}</strong>
            </div>
            <span class="text-[11px] text-[#387A54] dark:text-emerald-400 font-bold flex items-center space-x-1">
              <span class="w-2 h-2 rounded-full bg-[#387A54] animate-pulse"></span>
              <span>{{ localeStore.isAmharic ? 'ትኩስ የምድጃ ክምችት' : 'Live Kitchen Stock' }}</span>
            </span>
          </div>
        </div>

        <!-- Branches List Container -->
        <div class="flex-1 overflow-y-auto p-4 sm:p-5 space-y-3">
          <div v-if="filteredBranches.length === 0" class="py-12 text-center space-y-2">
            <div class="text-3xl">🔍</div>
            <div class="font-serif font-bold text-sm text-[#2A1810] dark:text-[#FAF3EE]">{{ localeStore.isAmharic ? 'ምንም ቅርንጫፍ አልተገኘም' : 'No branches found' }}</div>
            <p class="text-xs text-[#786B60] dark:text-[#C5B7AF]">
              {{ localeStore.isAmharic ? `ከ"${searchQuery}" ጋር የሚዛመድ ቅርንጫፍ የለም። "መገናኛ"፣ "ቦሌ" ወይም "ፒያሳ" ብለው ይሞክሩ::` : `No location matched "${searchQuery}". Try searching for "Megenagna", "Bole", or "Piassa".` }}
            </p>
          </div>

          <!-- Branch Card -->
          <div
            v-for="branch in filteredBranches"
            :key="branch.branch_id"
            class="p-4 sm:p-4.5 rounded-2xl border transition-all duration-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4"
            :class="branchStore.selectedBranchId === branch.branch_id
              ? 'bg-rose-50/60 dark:bg-[#341822]/40 border-[#DE1B54] shadow-xs'
              : 'bg-white dark:bg-[#24160E] border-gray-200 dark:border-[#382318] hover:border-rose-300 dark:hover:border-rose-900/50 hover:bg-[#FAF7F5] dark:hover:bg-[#2A1810]'"
          >
            <div class="space-y-1.5 flex-1 min-w-0">
              <div class="flex items-center space-x-2 flex-wrap">
                <h3 class="font-serif font-bold text-sm sm:text-base text-[#2A1810] dark:text-[#FAF3EE] truncate">
                  {{ localeStore.tBranch(branch.branch_name) }}
                </h3>
                <span
                  class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                  :class="branch.branch_type === 'Main'
                    ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300'
                    : 'bg-rose-100 text-[#DE1B54] dark:bg-rose-950/60 dark:text-rose-300'"
                >
                  {{ branch.branch_type === 'Main' ? (localeStore.isAmharic ? 'ዋና ማዕከል' : 'Central HQ') : (branch.branch_code || 'Bakehouse') }}
                </span>
                <span v-if="branch.is_shopping_enabled === false" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FAF0F0] text-[#B84B42] border border-[#E8C4C4]">
                  {{ localeStore.isAmharic ? 'ለግዢ ዝግ ነው' : 'Shopping Unavailable' }}
                </span>
                <span v-else-if="branchStore.selectedBranchId === branch.branch_id" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#387A54] text-white">
                  {{ localeStore.isAmharic ? '✓ ንቁ ቅርንጫፍ' : '✓ Active' }}
                </span>
              </div>

              <!-- Shopping Unavailable Warning Notice -->
              <div v-if="branch.is_shopping_enabled === false" class="p-2 rounded-xl bg-rose-50/80 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900/40 text-[11px] text-[#B84B42] dark:text-rose-400 font-semibold flex items-center space-x-1.5">
                <span>⚠️</span>
                <span>{{ localeStore.isAmharic ? 'ይቅርታ፣ ይህ ቅርንጫፍ በአሁኑ ጊዜ ለግዢ ዝግ ነው።' : 'Sorry, this branch is currently unavailable for shopping.' }}</span>
              </div>

              <!-- Address & City -->
              <div class="text-xs text-[#786B60] dark:text-[#C5B7AF] flex items-center space-x-1.5">
                <span class="text-rose-500 shrink-0">📍</span>
                <span class="truncate">{{ branch.address || branch.city || (localeStore.isAmharic ? 'ደሴ' : 'Dessie') }}</span>
              </div>

              <!-- Phone & Opening Hours -->
              <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] text-[#786B60] dark:text-[#C5B7AF] pt-0.5">
                <div class="flex items-center space-x-1">
                  <span>🕒</span>
                  <span>{{ branch.opening_hours || (localeStore.isAmharic ? 'ሰኞ – እሁድ: 1:00 – 3:00' : 'Mon – Sun: 07:00 – 21:00') }}</span>
                </div>
                <div class="flex items-center space-x-1">
                  <span>📞</span>
                  <a :href="`tel:${branch.phone || '+251911123456'}`" class="hover:text-[#DE1B54] font-medium underline">
                    {{ branch.phone || '+251 911 123 456' }}
                  </a>
                </div>
              </div>

              <!-- Fast fulfillment tags -->
              <div class="flex flex-wrap gap-1.5 pt-1">
                <span class="px-2 py-0.5 rounded-md bg-[#FAF7F5] dark:bg-[#1E120B] border border-gray-200 dark:border-[#382318] text-[10px] text-[#786B60] dark:text-[#C5B7AF]">
                  🚚 {{ localeStore.isAmharic ? 'ማድረስ' : 'Home Delivery' }}
                </span>
                <span class="px-2 py-0.5 rounded-md bg-[#FAF7F5] dark:bg-[#1E120B] border border-gray-200 dark:border-[#382318] text-[10px] text-[#786B60] dark:text-[#C5B7AF]">
                  🏪 {{ localeStore.isAmharic ? 'በነፃ መውሰድ' : 'Free Shop Pickup' }}
                </span>
                <span class="px-2 py-0.5 rounded-md bg-[#FAF7F5] dark:bg-[#1E120B] border border-gray-200 dark:border-[#382318] text-[10px] text-[#786B60] dark:text-[#C5B7AF]">
                  🎂 {{ localeStore.isAmharic ? 'ብጁ ኬክ' : 'Custom Cakes' }}
                </span>
              </div>
            </div>

            <!-- Action Button -->
            <div class="sm:shrink-0 flex sm:flex-col items-center justify-end">
              <button
                v-if="branch.is_shopping_enabled === false"
                disabled
                class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500 text-xs font-bold border border-gray-200 dark:border-gray-700 cursor-not-allowed flex items-center justify-center space-x-1.5"
              >
                <span>✕</span>
                <span>{{ localeStore.isAmharic ? 'ለግዢ ዝግ ነው' : 'Unavailable' }}</span>
              </button>
              <button
                v-else-if="branchStore.selectedBranchId === branch.branch_id"
                disabled
                class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-[#387A54] text-white text-xs font-bold shadow-xs cursor-default flex items-center justify-center space-x-1.5"
              >
                <span>✓</span>
                <span>{{ localeStore.isAmharic ? 'ንቁ ቅርንጫፍ' : 'Active Branch' }}</span>
              </button>
              <button
                v-else
                @click="chooseBranch(branch)"
                class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-[#DE1B54] hover:bg-[#C51345] text-white text-xs font-bold transition shadow-xs cursor-pointer active:scale-95 flex items-center justify-center space-x-1.5"
              >
                <span>{{ localeStore.isAmharic ? 'ቅርንጫፉን ምረጥ' : 'Select Branch' }}</span>
                <span>→</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Modal Footer: Help / Notice -->
        <div class="px-5 sm:px-7 py-3 bg-[#FAF7F5] dark:bg-[#2A1810] border-t border-gray-100 dark:border-[#382318] flex flex-col sm:flex-row items-center justify-between text-[11px] text-[#786B60] dark:text-[#C5B7AF] gap-2">
          <div class="flex items-center space-x-1.5">
            <span>💡</span>
            <span>{{ localeStore.isAmharic ? 'ቅርንጫፍ መቀየር የምርቶችን ዋጋ እና ክምችት በራስ-ሰር ያድሳል::' : 'Switching branches automatically revalidates all prices and live kitchen stock.' }}</span>
          </div>
          <router-link
            to="/branches"
            @click="close"
            class="font-bold text-[#DE1B54] hover:underline"
          >
            {{ localeStore.isAmharic ? 'የከተማውን ካርታ ይመልከቱ →' : 'View Interactive City Map →' }}
          </router-link>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useBranchStore } from '../stores/branch';
import { useLocaleStore } from '../stores/locale';
import { useToastStore } from '../stores/toast';

const branchStore = useBranchStore();
const localeStore = useLocaleStore();
const toastStore = useToastStore();

const searchQuery = ref('');

const isOpen = computed(() => branchStore.isPickerModalOpen);

const branchesList = computed(() => {
  return Array.isArray(branchStore.branches) ? branchStore.branches : [];
});

const activeBranchName = computed(() => {
  const current = branchesList.value.find((b) => b.branch_id === branchStore.selectedBranchId);
  return current?.branch_name || (branchesList.value[0]?.branch_name || 'Select Branch');
});

const filteredBranches = computed(() => {
  const q = searchQuery.value.toLowerCase().trim();
  if (!q) return branchesList.value;
  return branchesList.value.filter((b) => {
    return (
      (b.branch_name && b.branch_name.toLowerCase().includes(q)) ||
      (b.city && b.city.toLowerCase().includes(q)) ||
      (b.address && b.address.toLowerCase().includes(q)) ||
      (b.branch_code && b.branch_code.toLowerCase().includes(q))
    );
  });
});

function close() {
  branchStore.closePicker();
}

function chooseBranch(branch) {
  branchStore.setSelectedBranch(branch.branch_id);
  branchStore.closePicker();
  toastStore.success(`Active bakery branch set to ${branch.branch_name}! Stock and prices updated.`);
}

onMounted(async () => {
  if (!branchStore.branches || branchStore.branches.length === 0) {
    await branchStore.fetchBranches();
  }
});
</script>
