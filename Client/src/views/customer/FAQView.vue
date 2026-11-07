<template>
  <div class="bg-white dark:bg-[#170E0A] min-h-screen text-[#29231F] dark:text-[#FAF3EE] font-sans selection:bg-[#FCE7F0] selection:text-[#DE1B54] pb-12">
    <!-- 1. HERO BANNER: SweetBakes Style Matching Shop & Home Pages -->
    <section class="relative overflow-hidden bg-gradient-to-r from-[#FFF5F2] via-[#FDF3EE] to-[#FCEEE8] dark:from-[#22130C] dark:via-[#1E110A] dark:to-[#170E0A] min-h-[180px] sm:min-h-[220px] lg:min-h-[240px] flex items-center border-b border-[#F2E5DD] dark:border-[#382318]">
      <div class="absolute right-0 top-0 bottom-0 w-full lg:w-[55%] overflow-hidden pointer-events-none">
        <img
          src="/mockup/shop_banner_right.jpg"
          alt="SweetBakes Help Center Banner"
          class="w-full h-full object-cover object-center lg:object-left"
          loading="eager"
        />
        <div class="absolute inset-y-0 left-0 w-32 sm:w-48 bg-gradient-to-r from-[#FDF3EE] via-[#FDF3EE]/80 to-transparent hidden lg:block dark:from-[#170E0A] dark:via-[#170E0A]/80"></div>
        <div class="absolute inset-0 bg-[#FFF5F2]/80 sm:bg-[#FFF5F2]/75 dark:bg-[#170E0A]/85 lg:hidden"></div>
      </div>

      <div class="sweetbakes-container w-full py-8 relative z-10">
        <div class="max-w-xl space-y-2 text-center lg:text-left">
          <div class="flex items-center justify-center lg:justify-start space-x-2 font-script text-2xl sm:text-3xl text-[#DE1B54]">
            <span class="text-gray-300 dark:text-gray-600 select-none">&mdash;</span>
            <span>{{ localeStore.t('help_center_sub') }}</span>
            <span class="text-gray-300 dark:text-gray-600 select-none">&mdash;</span>
          </div>

          <h1 class="text-3xl sm:text-4xl lg:text-5xl font-serif font-black text-[#2A1810] dark:text-[#FAF3EE] tracking-tight leading-tight">
            {{ localeStore.t('faq_title') }}
          </h1>

          <p class="text-xs sm:text-sm text-[#6E5D53] dark:text-[#C5B7AF] flex items-center justify-center lg:justify-start space-x-1.5">
            <span>{{ localeStore.t('faq_hero_desc') }}</span>
            <span class="text-[#DE1B54] text-base font-bold font-script">♡</span>
          </p>
        </div>
      </div>
    </section>

    <!-- 2. MAIN FAQ SECTION -->
    <div class="sweetbakes-container max-w-5xl py-8 sm:py-10 space-y-8">
      
      <!-- Category Filter Tabs -->
      <div class="flex flex-wrap items-center justify-center gap-2">
        <button
          v-for="cat in categories"
          :key="cat.key"
          @click="activeCategory = cat.key"
          class="px-4 py-2 min-h-[38px] rounded-xl text-xs font-bold transition shadow-2xs cursor-pointer active:scale-95"
          :class="activeCategory === cat.key 
            ? 'bg-[#DE1B54] text-white shadow-xs' 
            : 'bg-white dark:bg-[#1E120B] border border-[#EFE8E1] dark:border-[#382318] text-[#6E5D53] dark:text-[#C5B7AF] hover:text-[#2A1810] dark:hover:text-[#FAF3EE] hover:bg-rose-50/40 dark:hover:bg-[#341822]'"
        >
          {{ cat.label }}
        </button>
      </div>

      <!-- FAQ Accordion List -->
      <div class="space-y-3.5">
        <div
          v-for="(item, idx) in filteredFaqs"
          :key="idx"
          class="bg-white dark:bg-[#1E120B] rounded-2xl border border-[#EFE8E1] dark:border-[#382318] p-5 sm:p-6 transition shadow-2xs hover:shadow-xs"
        >
          <button
            @click="toggleItem(idx)"
            class="w-full text-left flex items-start justify-between gap-4 focus:outline-none cursor-pointer"
          >
            <span class="font-serif font-bold text-base sm:text-lg text-[#2A1810] dark:text-[#FAF3EE] hover:text-[#DE1B54] transition">
              {{ item.q }}
            </span>
            <span
              class="w-7 h-7 rounded-full bg-rose-50 dark:bg-[#341822] border border-rose-100 dark:border-[#DE1B54]/40 flex items-center justify-center text-xs font-bold text-[#DE1B54] shrink-0 transition-transform duration-200"
              :class="openIdx === idx ? 'rotate-180 bg-[#DE1B54] text-white' : ''"
            >
              ▼
            </span>
          </button>

          <div
            v-if="openIdx === idx"
            class="pt-3.5 text-xs sm:text-sm text-[#6E5D53] dark:text-[#C5B7AF] leading-relaxed border-t border-gray-100 dark:border-[#382318] mt-3.5"
          >
            {{ item.a }}
          </div>
        </div>
      </div>

      <!-- Still Have Questions Banner -->
      <div class="bg-gradient-to-br from-[#FFF5F2] via-[#FAF7F5] to-[#FFF5F2] dark:from-[#25150E] dark:via-[#1E120B] dark:to-[#25150E] rounded-2xl border border-rose-200/60 dark:border-[#382318] p-8 text-center space-y-4 shadow-xs">
        <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-[#341822] border border-rose-100 dark:border-[#DE1B54]/40 text-[#DE1B54] flex items-center justify-center mx-auto text-2xl shadow-2xs">
          💬
        </div>
        <div class="space-y-1">
          <h3 class="font-serif font-black text-xl text-[#2A1810] dark:text-[#FAF3EE]">
            {{ localeStore.t('still_have_questions') }}
          </h3>
          <p class="text-xs sm:text-sm text-[#6E5D53] dark:text-[#C5B7AF] max-w-md mx-auto leading-relaxed">
            {{ localeStore.t('support_team_ready') }}
          </p>
        </div>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
          <router-link
            to="/contact"
            class="w-full sm:w-auto px-6 py-2.5 min-h-[40px] bg-[#DE1B54] hover:bg-[#C51345] text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer flex items-center justify-center space-x-1.5 active:scale-95"
          >
            <span>{{ localeStore.t('contact_us_btn') }}</span>
            <span>&rarr;</span>
          </router-link>
          <a
            :href="`tel:${supportPhone}`"
            class="w-full sm:w-auto px-6 py-2.5 min-h-[40px] bg-white dark:bg-[#1E120B] border border-gray-200 dark:border-[#382318] hover:border-[#DE1B54] text-[#2A1810] dark:text-[#FAF3EE] text-xs font-bold rounded-xl shadow-2xs transition cursor-pointer flex items-center justify-center space-x-1.5"
          >
            <span>📞 {{ supportPhone }}</span>
          </a>
        </div>
      </div>
    </div>

    <!-- 3. DECORATIVE WAVE RIBBON ABOVE FOOTER -->
    <div class="relative overflow-hidden bg-gradient-to-r from-rose-50 via-rose-100/60 to-rose-50 dark:from-[#25150E] dark:via-[#20110A] dark:to-[#170E0A] border-t border-rose-200/60 dark:border-[#382318] py-4 mt-8">
      <div class="sweetbakes-container flex flex-col sm:flex-row items-center justify-between gap-2 text-xs">
        <div class="flex items-center space-x-1.5 font-script text-2xl text-[#DE1B54]">
          <span class="font-bold">SweetBakes</span>
          <span>♡</span>
        </div>
        <div class="font-script text-lg sm:text-xl text-[#2A1810] dark:text-[#FAF3EE] flex items-center space-x-1.5">
          <span class="italic">Because life is sweeter with cake!</span>
          <span class="text-[#DE1B54] not-italic">♡</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useLocaleStore } from '../../stores/locale';
import api from '../../api';

const localeStore = useLocaleStore();

const activeCategory = ref('all');
const openIdx = ref(0);
const supportPhone = ref('+251 911 123 456');

onMounted(async () => {
  try {
    const res = await api.get('/business-info');
    if (res.data?.phone) {
      supportPhone.value = res.data.phone;
    }
  } catch (e) {}
});

const categories = computed(() => {
  if (localeStore.isAmharic) {
    return [
      { key: 'all', label: 'ሁሉም ጥያቄዎች' },
      { key: 'branches', label: 'ቅርንጫፍ እና ትዕዛዞች' },
      { key: 'delivery', label: 'ማድረስ እና መውሰድ' },
      { key: 'custom_cakes', label: 'ልዩ ኬኮች' },
      { key: 'freshness', label: 'ትኩስነት እና አያያዝ' },
      { key: 'payments', label: 'የክፍያ አማራጮች' },
    ];
  }
  return [
    { key: 'all', label: 'All Questions' },
    { key: 'branches', label: 'Branch & Orders' },
    { key: 'delivery', label: 'Delivery & Pickup' },
    { key: 'custom_cakes', label: 'Custom Cakes' },
    { key: 'freshness', label: 'Freshness & Storage' },
    { key: 'payments', label: 'Payment Options' },
  ];
});

const faqs = computed(() => {
  if (localeStore.isAmharic) {
    return [
      {
        category: 'branches',
        q: 'ከመግዛቴ በፊት የዳቦ ቤት ቅርንጫፍ መምረጥ ለምን አስፈለገ?',
        a: 'እያንዳንዱ የስዊትቤክስ ቅርንጫፍ በቀን ሁለት ጊዜ ትኩስ ዳቦና ኬክ በራሱ ይጋግራል እንዲሁም የራሱ የምርት ክምችት አለው። ቅርንጫፍዎን መምረጥዎ ትክክለኛውን የምርት ክምችት፣ ዋጋ እና የማድረሻ አማራጮችን ለማግኘት ዋስትና ይሰጣል።',
      },
      {
        category: 'branches',
        q: 'ቅርጫት ውስጥ ዕቃዎች እያሉ ቅርንጫፍ ብቀይር ምን ይሆናል?',
        a: 'ቅርንጫፍ ሲቀይሩ፣ ቅርጫትዎ አዲሱ ቅርንጫፍ ባለው ክምችትና ዋጋ መሰረት ወዲያውኑ ይፈተሻል። በዚያ ቅርንጫፍ ያላለቀ ወይም ያለቀ ምርት ካለ ማስታወቂያ ይሰጥዎታል፤ ብዛቱን ማስተካከል ወይም ቅርንጫፉን እንዳለ ማቆየት ይችላሉ።',
      },
      {
        category: 'branches',
        q: 'ስርዓቱ ትዕዛዝን ወደ ሌላ ቅርንጫፍ በራሱ ያስተላልፋል?',
        a: 'ፈጽሞ አያስተላልፍም። በእኛ የስራ ደንብ መሰረት እያንዳንዱ ትዕዛዝ እርስዎ በመረጡት ቅርንጫፍ ብቻ ይዘጋጃል። ያለ እርስዎ ፈቃድ ትዕዛዝ ወደ ሌላ አይዘዋወርም።',
      },
      {
        category: 'delivery',
        q: 'በአዲስ አበባ ውስጥ የትኞቹን አካባቢዎች ታደርሳላችሁ?',
        a: 'አምስቱ ቅርንጫፎቻችን ቦሌ፣ ኪርቆስ፣ የካ፣ አራዳ እና ሲኤምሲ/ጉርድ ሾላን ጨምሮ ዋና ዋና የአዲስ አበባ ክፍለ ከተሞችን ይሸፍናሉ። በየቅርንጫፉ ከ 7 እስከ 12 ኪ.ሜ ርቀት ድረስ ይደርሳል። ዝርዝር የማድረሻ ዞኖችን በእኛ ቅርንጫፎች ገጽ ማየት ይችላሉ።',
      },
      {
        category: 'delivery',
        q: 'የትዕዛዝ ማድረሻ ጊዜው ምን ያህል ይፈጃል?',
        a: 'መደበኛ ትዕዛዞች በ 20 ደቂቃ ውስጥ ታሽገው እንደ ርቀቱ ሁኔታ ከ 35 እስከ 50 ደቂቃ ባለው ጊዜ ውስጥ ደጃፍዎ ይደርሳሉ።',
      },
      {
        category: 'custom_cakes',
        q: 'ለልዩ ኬኮች ምን ያህል ጊዜ ቀድሞ ማዘዝ ያስፈልጋል?',
        a: 'ልዩ የክብረ በዓል ኬኮች በዋና ኬክ አዘጋጆቻችን ከባዶ ስለሚዘጋጁ ቢያንስ የ 48 ሰዓታት ቅድመ ማስታወቂያ ያስፈልጋል። ለሰርግ ኬኮች ከ 5 እስከ 7 ቀናት ቀደም ብሎ ማዘዝ ይመረጣል።',
      },
      {
        category: 'custom_cakes',
        q: 'የልዩ ኬክ ዋጋ እና የ 50% ቅድመ ክፍያ እንዴት ይሰራል?',
        a: 'የኬክ ዲዛይን እና ሃሳብዎን በልዩ ኬክ ስቱዲዮ ውስጥ ካስገቡ በኋላ፣ የመረጡት ቅርንጫፍ ዋና አዘጋጅ ተመልክቶ ይፋዊ ዋጋ በመለያዎ ውስጥ ይልካል። ዋጋውን ተቀብለው 50% ቅድመ ክፍያ ሲፈጽሙ ትዕዛዝዎ ተረጋግጦ ስራ ይጀመራል።',
      },
      {
        category: 'custom_cakes',
        q: 'የዝግጅቴ እቅድ ከተቀየረ ያዘዝኩትን ልዩ ኬክ መሰረዝ እችላለሁ?',
        a: 'ትዕዛዙ በሂደት ላይ ወይም ገና ዋጋ በተሰጠበት ወቅት በማንኛውም ጊዜ ሙሉ በሙሉ በመሰረዝ 100% ገንዘብዎን መመለስ ይችላሉ። ከ 48 ሰዓታት በፊት ከተሰረዘ ቅድመ ክፍያው ሙሉ በሙሉ ይመለሳል።',
      },
      {
        category: 'freshness',
        q: 'ትኩስ የተፈጥሮ ዳቦዎች እና የጠዋት ፓስትሪዎች ምን ያህል ትኩስ ናቸው?',
        a: 'ሁሉም ዳቦዎቻችን በባህላዊ የተፈጥሮ እርሾ ተዘጋጅተው በየቀኑ ከጠዋቱ 12:30 እና ከሰዓት 8:30 ከእቶን ይወጣሉ። ያደረ ምርት ፈጽሞ አንሸጥም።',
      },
      {
        category: 'freshness',
        q: 'ኬክ ከደረሰኝ በኋላ እንዴት ማስቀመጥ አለብኝ?',
        a: 'ትኩስ ክሬም እና ፍራፍሬ ያላቸው ኬኮች ከ 2°C እስከ 4°C ባለው ማቀዝቀዣ ውስጥ መቀመጥ አለባቸው። ተፈጥሯዊ ዳቦዎች ደግሞ በክፍል ሙቀት በወረቀት ከረጢት ውስጥ መቀመጥ አለባቸው፤ ዳቦ በማቀዝቀዣ ውስጥ መግባት የለበትም።',
      },
      {
        category: 'payments',
        q: 'የትኞቹን የክፍያ አማራጮች ትቀበላላችሁ?',
        a: 'በቴሌብር (Telebirr)፣ በሲቢኢ ብር (CBE Birr) እና በቻፓ (Chapa) እንዲሁም በቅርንጫፍ ሲረከቡ ወይም ሲደርስዎት በጥሬ ገንዘብ መክፈል ይችላሉ።',
      },
    ];
  }

  return [
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
});

const filteredFaqs = computed(() => {
  if (activeCategory.value === 'all') return faqs.value;
  return faqs.value.filter((f) => f.category === activeCategory.value);
});

function toggleItem(idx) {
  openIdx.value = openIdx.value === idx ? -1 : idx;
}
</script>
