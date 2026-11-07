<template>
  <div class="bg-white dark:bg-[#170E0A] min-h-screen text-[#29231F] dark:text-[#FAF3EE] font-sans selection:bg-[#FCE7F0] selection:text-[#DE1B54]">
    
    <!-- Clean Breadcrumbs Navigation Bar -->
    <div class="bg-[#FAF7F5] dark:bg-[#1C110C] border-b border-[#F0EAE3] dark:border-[#352117] py-3">
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

    <!-- Additional SweetBakes Guarantee Badges (Bottom Trust Ribbon) -->
    <section class="border-t border-rose-100 dark:border-[#331A24] bg-white dark:bg-[#1A100B] py-8 sm:py-10">
      <div class="sweetbakes-container">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6 text-center">
          
          <div class="p-4 rounded-2xl bg-rose-50/50 dark:bg-[#25150E] border border-rose-100/80 dark:border-[#3E2319] space-y-1">
            <div class="text-2xl sm:text-3xl">🚚</div>
            <h4 class="font-serif font-bold text-xs sm:text-sm text-[#2A1810] dark:text-white">
              {{ localeStore.isAmharic ? 'ነፃ ማድረሻ' : 'Free Addis Delivery' }}
            </h4>
            <p class="text-[11px] text-[#786B60] dark:text-[#AFA39B]">
              {{ localeStore.isAmharic ? 'ከ 1,000 ብር በላይ' : 'Orders over ETB 1,000' }}
            </p>
          </div>

          <div class="p-4 rounded-2xl bg-rose-50/50 dark:bg-[#25150E] border border-rose-100/80 dark:border-[#3E2319] space-y-1">
            <div class="text-2xl sm:text-3xl">🌿</div>
            <h4 class="font-serif font-bold text-xs sm:text-sm text-[#2A1810] dark:text-white">
              {{ localeStore.isAmharic ? 'የጾም ኬኮች' : "Ye'Tsom Vegan" }}
            </h4>
            <p class="text-[11px] text-[#786B60] dark:text-[#AFA39B]">
              {{ localeStore.isAmharic ? '100% የጾም ምርጫዎች' : '100% Fasting & eggless' }}
            </p>
          </div>

          <div class="p-4 rounded-2xl bg-rose-50/50 dark:bg-[#25150E] border border-rose-100/80 dark:border-[#3E2319] space-y-1">
            <div class="text-2xl sm:text-3xl">🎂</div>
            <h4 class="font-serif font-bold text-xs sm:text-sm text-[#2A1810] dark:text-white">
              {{ localeStore.isAmharic ? 'ብጁ ኬኮች' : 'Custom Cake Studio' }}
            </h4>
            <p class="text-[11px] text-[#786B60] dark:text-[#AFA39B]">
              {{ localeStore.isAmharic ? 'ለልደት እና ሰርግ' : 'Weddings & birthdays' }}
            </p>
          </div>

          <div class="p-4 rounded-2xl bg-rose-50/50 dark:bg-[#25150E] border border-rose-100/80 dark:border-[#3E2319] space-y-1">
            <div class="text-2xl sm:text-3xl">📍</div>
            <h4 class="font-serif font-bold text-xs sm:text-sm text-[#2A1810] dark:text-white">
              {{ localeStore.isAmharic ? '4 ቅርንጫፎች' : '4 Addis Branches' }}
            </h4>
            <p class="text-[11px] text-[#786B60] dark:text-[#AFA39B]">
              {{ localeStore.isAmharic ? 'ቦሌ፣ መገናኛ፣ ፒያሳ፣ ሜክሲኮ' : 'Bole, Megenagna, Piassa, Mexico' }}
            </p>
          </div>

        </div>
      </div>
    </section>

  </div>
</template>

<script setup>
import ContactSection from '../../components/ContactSection.vue';
import { useLocaleStore } from '../../stores/locale';

const localeStore = useLocaleStore();
</script>
