<template>
  <div class="bg-[#FAF7F5] dark:bg-[#170E0A] min-h-screen text-[#29231F] dark:text-[#FAF3EE] font-sans selection:bg-[#FCE7F0] selection:text-[#DE1B54] w-full">
    
    <!-- Clean Breadcrumbs Navigation Bar (Harmonized with Unified Canvas) -->
    <div class="bg-[#FAF7F5] dark:bg-[#170E0A] border-b border-[#EFE8E1] dark:border-[#382318] py-3.5 w-full">
      <div class="sweetbakes-container flex items-center space-x-2 text-xs text-[#786B60] dark:text-[#AFA39B]">
        <router-link to="/" class="hover:text-[#DE1B54] transition flex items-center space-x-1">
          <span>🏠</span>
          <span>{{ localeStore.t('home') }}</span>
        </router-link>
        <span>&rsaquo;</span>
        <span class="text-[#DE1B54] font-bold">{{ localeStore.isAmharic ? 'ያግኙን' : 'Contact Us' }}</span>
      </div>
    </div>

    <!-- Contact Section with Real GPS Map & Message Form -->
    <ContactSection :standalone="true" />

    <!-- Additional SweetBakes Guarantee Badges (Bottom Trust Ribbon - Unified Canvas) -->
    <section class="bg-[#FAF7F5] dark:bg-[#170E0A] py-8 sm:py-12 border-t border-[#EFE8E1]/80 dark:border-[#382318] w-full">
      <div class="sweetbakes-container">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6 text-center">
          
          <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#1E120B] border border-[#EFE8E1] dark:border-[#382318] shadow-2xs space-y-1 hover:shadow-md transition-shadow">
            <div class="text-2xl sm:text-3xl">🚚</div>
            <h4 class="font-serif font-bold text-xs sm:text-sm text-[#2A1810] dark:text-white">
              {{ localeStore.isAmharic ? 'ነፃ ማድረሻ' : 'Free Local Delivery' }}
            </h4>
            <p class="text-[11px] text-[#786B60] dark:text-[#AFA39B]">
              {{ localeStore.isAmharic ? 'ከ 1,000 ብር በላይ' : 'Orders over ETB 1,000' }}
            </p>
          </div>

          <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#1E120B] border border-[#EFE8E1] dark:border-[#382318] shadow-2xs space-y-1 hover:shadow-md transition-shadow">
            <div class="text-2xl sm:text-3xl">🌿</div>
            <h4 class="font-serif font-bold text-xs sm:text-sm text-[#2A1810] dark:text-white">
              {{ localeStore.isAmharic ? 'የጾም ኬኮች' : "Ye'Tsom Vegan" }}
            </h4>
            <p class="text-[11px] text-[#786B60] dark:text-[#AFA39B]">
              {{ localeStore.isAmharic ? '100% የጾም ምርጫዎች' : '100% Fasting & eggless' }}
            </p>
          </div>

          <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#1E120B] border border-[#EFE8E1] dark:border-[#382318] shadow-2xs space-y-1 hover:shadow-md transition-shadow">
            <div class="text-2xl sm:text-3xl">🎂</div>
            <h4 class="font-serif font-bold text-xs sm:text-sm text-[#2A1810] dark:text-white">
              {{ localeStore.isAmharic ? 'ብጁ ኬኮች' : 'Custom Cake Studio' }}
            </h4>
            <p class="text-[11px] text-[#786B60] dark:text-[#AFA39B]">
              {{ localeStore.isAmharic ? 'ለልደት እና ሰርግ' : 'Weddings & birthdays' }}
            </p>
          </div>

          <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#1E120B] border border-[#EFE8E1] dark:border-[#382318] shadow-2xs space-y-1 hover:shadow-md transition-shadow">
            <div class="text-2xl sm:text-3xl">📍</div>
            <h4 class="font-serif font-bold text-xs sm:text-sm text-[#2A1810] dark:text-white">
              {{ branchCount }} {{ localeStore.isAmharic ? 'ቅርንጫፍ' : (branchCount === 1 ? 'Bakery Branch' : 'Bakery Branches') }}
            </h4>
            <p class="text-[11px] text-[#786B60] dark:text-[#AFA39B] truncate" :title="branchSummaryText">
              {{ branchSummaryText }}
            </p>
          </div>

        </div>
      </div>
    </section>

  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import ContactSection from '../../components/ContactSection.vue';
import { useLocaleStore } from '../../stores/locale';
import { useBranchStore } from '../../stores/branch';

const localeStore = useLocaleStore();
const branchStore = useBranchStore();

onMounted(async () => {
  if (branchStore.branches.length === 0) {
    await branchStore.fetchBranches();
  }
});

const branchCount = computed(() => {
  const active = branchStore.branches?.filter(b => b.status === 'Active') || [];
  return active.length || branchStore.branches?.length || 1;
});

const branchSummaryText = computed(() => {
  const active = branchStore.branches?.filter(b => b.status === 'Active') || [];
  const list = active.length > 0 ? active : (branchStore.branches || []);
  if (list.length === 0) {
    return localeStore.isAmharic ? 'ደሴ (ፒያሳ)' : 'Dessie (Piazza)';
  }
  return list.map(b => `${b.branch_name} (${b.city})`).join(' · ');
});
</script>
