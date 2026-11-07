<template>
  <div class="sweetbakes-container py-8 sm:py-12 lg:py-16 space-y-12 sm:space-y-16 lg:space-y-20 font-sans selection:bg-[#FCE7F0] selection:text-[#DE1B54] animate-fade-in-up">
    <!-- Hero Header -->
    <div class="text-center space-y-3 sm:space-y-4 max-w-3xl mx-auto">
      <div class="inline-flex items-center space-x-1.5 px-3.5 sm:px-4 py-1.5 bg-rose-50 dark:bg-[#341822] border border-rose-200 dark:border-[#58263A] text-[#DE1B54] text-xs font-bold rounded-full">
        <span>🧁</span>
        <span>{{ localeStore.isAmharic ? 'ከ 2015 ጀምሮ · ስዊትቤክስ · አዲስ አበባ' : 'Since 2015 · SweetBakes · Addis Ababa' }}</span>
      </div>
      <h1 class="text-2xl xs:text-3xl sm:text-5xl lg:text-6xl font-serif font-black text-[#2A1810] dark:text-[#FAF3EE] tracking-tight leading-[1.15]">
        {{ localeStore.isAmharic ? 'የሚተማመኑበት የዳቦና ኬክ መጋገሪያ' : 'A Bakery You Can Trust' }}
      </h1>
      <p class="text-xs sm:text-sm lg:text-base text-[#6E5D53] dark:text-[#C5B7AF] leading-relaxed">
        {{ (localeStore.isAmharic ? businessInfo?.about_story_am : businessInfo?.about_story_en) || localeStore.t('about_desc') }}
      </p>
    </div>

    <!-- Story 2-Column Section -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
      <div class="lg:col-span-6 space-y-4 sm:space-y-5 text-xs sm:text-sm text-[#6E5D53] dark:text-[#C5B7AF] leading-relaxed">
        <span class="text-xs font-bold uppercase tracking-widest text-[#DE1B54]">{{ localeStore.t('about_tag') }}</span>
        <h2 class="text-2xl sm:text-4xl font-serif font-black text-[#2A1810] dark:text-[#FAF3EE] leading-tight">
          {{ localeStore.t('about_title') }}
        </h2>
        <p>
          {{ (localeStore.isAmharic ? businessInfo?.about_story_am : businessInfo?.about_story_en) || 'At SweetBakes, we believe in the joy of fresh-baked goodness. Our cakes and bread are made with love, using the finest ingredients and creative designs to make your moments sweeter.' }}
        </p>

        <!-- 4 Core Pillars -->
        <div class="space-y-2 sm:space-y-2.5 pt-2 text-xs sm:text-sm font-semibold text-[#2A1810] dark:text-[#FAF3EE]">
          <div class="flex items-center space-x-3">
            <span class="w-5 h-5 rounded-full bg-[#2A1810] dark:bg-[#DE1B54] text-white flex items-center justify-center text-[10px] font-bold">✓</span>
            <span>{{ localeStore.t('premium_quality') }}</span>
          </div>
          <div class="flex items-center space-x-3">
            <span class="w-5 h-5 rounded-full bg-[#2A1810] dark:bg-[#DE1B54] text-white flex items-center justify-center text-[10px] font-bold">✓</span>
            <span>{{ localeStore.t('fresh_ingredients') }}</span>
          </div>
          <div class="flex items-center space-x-3">
            <span class="w-5 h-5 rounded-full bg-[#2A1810] dark:bg-[#DE1B54] text-white flex items-center justify-center text-[10px] font-bold">✓</span>
            <span>{{ localeStore.t('custom_orders') }}</span>
          </div>
          <div class="flex items-center space-x-3">
            <span class="w-5 h-5 rounded-full bg-[#2A1810] dark:bg-[#DE1B54] text-white flex items-center justify-center text-[10px] font-bold">✓</span>
            <span>{{ localeStore.t('multibranch_service') }}</span>
          </div>
        </div>

        <!-- Dynamic Real Business Stats Bar -->
        <div class="grid grid-cols-2 xs:grid-cols-4 gap-3 sm:gap-4 pt-4 text-center">
          <div
            v-for="stat in businessStats"
            :key="stat.id"
            class="p-3.5 sm:p-4 bg-white dark:bg-[#1E120B] rounded-2xl border border-gray-200 dark:border-[#382318] shadow-2xs"
          >
            <div class="text-xl sm:text-2xl font-serif font-black text-[#DE1B54]">{{ stat.value }}</div>
            <div class="text-[10px] sm:text-[11px] font-bold text-[#2A1810] dark:text-[#FAF3EE] mt-1">{{ stat.label }}</div>
          </div>
        </div>
      </div>

      <div class="lg:col-span-6 relative">
        <div class="rounded-3xl overflow-hidden shadow-xl border-4 border-white dark:border-[#2A1810] bg-amber-50 dark:bg-[#1E120B]">
          <img
            src="/mockup/baker.jpg"
            alt="SweetBakes Baker Chef"
            class="w-full h-72 sm:h-96 lg:h-112 object-cover"
            loading="lazy"
          />
        </div>
        <div class="absolute -bottom-6 -left-6 bg-white dark:bg-[#1E120B] p-4 sm:p-5 rounded-2xl border border-gray-200 dark:border-[#382318] shadow-lg hidden sm:block max-w-xs">
          <div class="text-xs font-bold text-[#2A1810] dark:text-[#FAF3EE] mb-1">“Making life sweeter, one cake and bread at a time.”</div>
          <div class="text-[10px] text-[#DE1B54] font-semibold">— SweetBakes Master Pastry Chef</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useLocaleStore } from '../../stores/locale';
import { useBranchStore } from '../../stores/branch';
import api from '../../api';

const localeStore = useLocaleStore();
const branchStore = useBranchStore();

const businessInfo = ref(null);

const businessStats = computed(() => {
  if (businessInfo.value?.stats && businessInfo.value.stats.length > 0) {
    return businessInfo.value.stats.map(s => ({
      id: s.id,
      value: s.value,
      label: localeStore.isAmharic ? (s.label_am || s.label_en) : s.label_en,
    }));
  }
  return [
    { id: 'experience', value: '12+', label: localeStore.isAmharic ? 'የስራ ልምድ' : 'Years Experience' },
    { id: 'branches', value: String(branchStore.branches.length || 4), label: localeStore.isAmharic ? 'ቅርንጫፎች' : 'Addis Branches' },
    { id: 'daily_fresh', value: '100%', label: localeStore.isAmharic ? 'ትኩስ ጥራት' : 'Fresh Daily' },
    { id: 'chefs', value: '15+', label: localeStore.isAmharic ? 'ዋና ሼፎች' : 'Pastry Chefs' },
  ];
});

async function fetchInfo() {
  try {
    const res = await api.get('/business-info');
    if (res.data) {
      businessInfo.value = res.data;
    }
  } catch (err) {
    console.error('Failed to load business info', err);
  }
}

onMounted(() => {
  fetchInfo();
  if (branchStore.branches.length === 0) {
    branchStore.fetchBranches();
  }
});
</script>
