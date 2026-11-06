<template>
  <div class="bg-white min-h-screen text-[#29231F] font-sans selection:bg-[#FCE7F0] selection:text-[#DE1B54] pb-12">
    <!-- 1. HERO BANNER: SweetBakes Style Matching Shop & Home Pages -->
    <section class="relative overflow-hidden bg-gradient-to-r from-[#FFF5F2] via-[#FDF3EE] to-[#FCEEE8] min-h-[180px] sm:min-h-[220px] lg:min-h-[240px] flex items-center border-b border-[#F2E5DD]">
      <div class="absolute right-0 top-0 bottom-0 w-full lg:w-[55%] overflow-hidden pointer-events-none">
        <img
          src="/mockup/shop_banner_right.jpg"
          alt="SweetBakes Help Center Banner"
          class="w-full h-full object-cover object-center lg:object-left"
          loading="eager"
        />
        <div class="absolute inset-y-0 left-0 w-32 sm:w-48 bg-gradient-to-r from-[#FDF3EE] via-[#FDF3EE]/80 to-transparent hidden lg:block"></div>
        <div class="absolute inset-0 bg-[#FFF5F2]/80 sm:bg-[#FFF5F2]/75 lg:hidden"></div>
      </div>

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full py-8 relative z-10">
        <div class="max-w-xl space-y-2 text-center lg:text-left">
          <div class="flex items-center justify-center lg:justify-start space-x-2 font-script text-2xl sm:text-3xl text-[#DE1B54]">
            <span class="text-gray-300 select-none">&mdash;</span>
            <span>Help Center</span>
            <span class="text-gray-300 select-none">&mdash;</span>
          </div>

          <h1 class="text-3xl sm:text-4xl lg:text-5xl font-serif font-black text-[#2A1810] tracking-tight leading-tight">
            Frequently Asked Questions
          </h1>

          <p class="text-xs sm:text-sm text-[#6E5D53] flex items-center justify-center lg:justify-start space-x-1.5">
            <span>Everything you need to know about our bakery!</span>
            <span class="text-[#DE1B54] text-base font-bold font-script">♡</span>
          </p>
        </div>
      </div>
    </section>

    <!-- 2. MAIN FAQ SECTION -->
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10 space-y-8">
      
      <!-- Category Filter Tabs -->
      <div class="flex flex-wrap items-center justify-center gap-2">
        <button
          v-for="cat in categories"
          :key="cat.key"
          @click="activeCategory = cat.key"
          class="px-4 py-2 rounded-xl text-xs font-bold transition shadow-2xs cursor-pointer"
          :class="activeCategory === cat.key 
            ? 'bg-[#DE1B54] text-white shadow-xs' 
            : 'bg-white border border-[#EFE8E1] text-[#6E5D53] hover:text-[#2A1810] hover:bg-rose-50/40'"
        >
          {{ cat.label }}
        </button>
      </div>

      <!-- FAQ Accordion List -->
      <div class="space-y-3.5">
        <div
          v-for="(item, idx) in filteredFaqs"
          :key="idx"
          class="bg-white rounded-2xl border border-[#EFE8E1] p-5 sm:p-6 transition shadow-2xs hover:shadow-xs"
        >
          <button
            @click="toggleItem(idx)"
            class="w-full text-left flex items-start justify-between gap-4 focus:outline-none cursor-pointer"
          >
            <span class="font-serif font-bold text-base sm:text-lg text-[#2A1810] hover:text-[#DE1B54] transition">
              {{ item.q }}
            </span>
            <span
              class="w-7 h-7 rounded-full bg-rose-50 border border-rose-100 flex items-center justify-center text-xs font-bold text-[#DE1B54] shrink-0 transition-transform duration-200"
              :class="openIdx === idx ? 'rotate-180 bg-[#DE1B54] text-white' : ''"
            >
              ▼
            </span>
          </button>

          <div
            v-if="openIdx === idx"
            class="pt-3.5 text-xs sm:text-sm text-[#6E5D53] leading-relaxed border-t border-gray-100 mt-3.5"
          >
            {{ item.a }}
          </div>
        </div>
      </div>

      <!-- Still Have Questions Banner -->
      <div class="bg-gradient-to-br from-[#FFF5F2] via-[#FAF7F5] to-[#FFF5F2] rounded-2xl border border-rose-200/60 p-8 text-center space-y-4 shadow-xs">
        <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-100 text-[#DE1B54] flex items-center justify-center mx-auto text-2xl shadow-2xs">
          💬
        </div>
        <div class="space-y-1">
          <h3 class="font-serif font-black text-xl text-[#2A1810]">
            Still Have Questions?
          </h3>
          <p class="text-xs sm:text-sm text-[#6E5D53] max-w-md mx-auto leading-relaxed">
            Our bakery support team and branch master bakers are ready to assist you anytime.
          </p>
        </div>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
          <router-link
            to="/contact"
            class="w-full sm:w-auto px-6 py-2.5 bg-[#DE1B54] hover:bg-[#C51345] text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer active:scale-95"
          >
            Contact SweetBakes &rarr;
          </router-link>
          <a
            href="tel:+251911123456"
            class="w-full sm:w-auto px-6 py-2.5 bg-white border border-[#DE1B54] hover:bg-rose-50 text-[#DE1B54] text-xs font-bold rounded-xl shadow-2xs transition cursor-pointer"
          >
            📞 Call +251 911 123 456
          </a>
        </div>
      </div>

    </div>

    <!-- 3. DECORATIVE WAVE RIBBON ABOVE FOOTER -->
    <div class="relative overflow-hidden bg-gradient-to-r from-rose-50 via-rose-100/60 to-rose-50 border-t border-rose-200/60 py-4 mt-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs">
        <div class="flex items-center space-x-1.5 font-script text-2xl text-[#DE1B54]">
          <span class="font-bold">SweetBakes</span>
          <span>♡</span>
        </div>
        <div class="font-script text-lg sm:text-xl text-[#2A1810] flex items-center space-x-1.5">
          <span class="italic">Because life is sweeter with cake!</span>
          <span class="text-[#DE1B54] not-italic">♡</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const activeCategory = ref('all');
const openIdx = ref(0);

const categories = [
  { key: 'all', label: 'All Questions' },
  { key: 'branches', label: 'Branch & Orders' },
  { key: 'delivery', label: 'Delivery & Pickup' },
  { key: 'custom_cakes', label: 'Custom Cakes' },
  { key: 'freshness', label: 'Freshness & Storage' },
  { key: 'payments', label: 'Payment Options' },
];

const faqs = [
  {
    category: 'branches',
    q: 'Why do I have to select a bakery branch before shopping?',
    a: 'Every SweetBakes branch bakes its bread and pastries fresh on-site twice daily and manages its own localized inventory. Selecting your branch first guarantees that the stock, prices, and delivery options you see accurately represent what can be delivered immediately or collected warm from the oven.',
  },
  {
    category: 'branches',
    q: 'What happens if I change my branch with items already in my cart?',
    a: 'When you switch branches, the cart automatically revalidates availability and prices against the new kitchen. If an item is out of stock at the new branch, you will see a clear warning prompting you to adjust quantities or keep your original branch.',
  },
  {
    category: 'branches',
    q: 'Does the system ever transfer an order to another branch automatically?',
    a: 'Never. In accordance with our strict operational policy, every order belongs exclusively to the branch you selected. We never move an order without your explicit approval.',
  },
  {
    category: 'delivery',
    q: 'Which areas of Addis Ababa do you deliver to?',
    a: 'Our 5 branches cover all primary sub-cities in Addis Ababa: Bole, Kirkos, Yeka, Arada, and CMC/Gurd Shola. Each branch delivers within a 7–12 km radius. You can view precise delivery zones and fees on the Our Branches page.',
  },
  {
    category: 'delivery',
    q: 'How fast will my bakery order arrive?',
    a: 'Standard orders placed for immediate dispatch are packaged within 20 minutes and delivered within 35–50 minutes, depending on your distance from the preparing branch.',
  },
  {
    category: 'custom_cakes',
    q: 'How much notice is needed for a custom celebration cake?',
    a: 'Because all custom sponge cakes, fillings, and edible fondant decorations are made completely from scratch by our head decorator, we require at least 48 hours notice. For multi-tier wedding centerpieces, we recommend 5–7 days.',
  },
  {
    category: 'custom_cakes',
    q: 'How does the custom cake price quote and 50% deposit work?',
    a: 'Once you submit your design brief and reference photos in the Custom Cake Studio, the head decorator at your chosen branch reviews it and posts an official quotation to your Account. Once you accept and pay the 50% deposit, your order status changes to Confirmed and the kitchen schedule is locked in.',
  },
  {
    category: 'custom_cakes',
    q: 'Can I cancel my custom cake if my event plans change?',
    a: 'You can cancel anytime while the order is Quoted or Submitted for a 100% refund. If cancelled more than 48 hours before the event date, your deposit is fully refunded. Once artisan decorating has commenced inside the 48-hour window, the deposit covers perishable ingredients.',
  },
  {
    category: 'freshness',
    q: 'How fresh is your artisanal sourdough bread and morning pastries?',
    a: 'All breads are crafted using traditional slow fermentation and pulled from the stone hearths at 6:30 AM and 2:30 PM every single day. We never sell day-old baked goods online.',
  },
  {
    category: 'freshness',
    q: 'How should I store my cake after delivery?',
    a: 'Fresh cream, fruit compote, and mascarpone cakes should be refrigerated between 2°C and 4°C and brought to room temperature 30 minutes before serving. Sourdough breads should be kept at room temperature in a paper bag or bread box; never refrigerate bread.',
  },
  {
    category: 'payments',
    q: 'Which payment options are supported?',
    a: 'We accept digital payments through Chapa, Telebirr, and CBE Birr. In addition, customers collecting in person can choose Pay-on-Pickup at any branch counter.',
  },
];

const filteredFaqs = computed(() => {
  if (activeCategory.value === 'all') return faqs;
  return faqs.filter((f) => f.category === activeCategory.value);
});

function toggleItem(idx) {
  openIdx.value = openIdx.value === idx ? -1 : idx;
}
</script>
