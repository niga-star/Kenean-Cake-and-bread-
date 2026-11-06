import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useLocaleStore = defineStore('locale', () => {
  const currentLocale = ref(localStorage.getItem('kenean_locale') || 'en');

  const translations = {
    en: {
      site_name: 'Kenean Cake & Bread',
      tagline: 'Artisan Bakery & Confectionery',
      free_delivery_banner: 'Free Delivery on Orders Over ETB 1,000 across Addis Ababa',
      track_order: 'Track Order',
      contact: 'Contact',
      help: 'Help',
      faq: 'FAQ',
      about: 'About Us',
      home: 'Home',
      shop: 'Shop',
      cakes_bread: 'Cakes & Bread',
      custom_cakes: 'Custom Cakes',
      branches: 'Branches',
      cart: 'Cart',
      account: 'Account',
      my_orders: 'My Orders',
      custom_requests: 'Custom Cake Requests',
      admin_portal: 'Staff Dashboard',
      logout: 'Sign Out',
      sign_in: 'Sign In / Register',
      choose_branch: 'Choose Your Branch',
      search_placeholder: 'Search artisan cakes, sourdough bread, pastries...',
      ethiopia_currency: 'ETB',
      footer_slogan: 'Handcrafted with organic wheat, slow-fermented sourdough, rich Belgian chocolate, and genuine passion. Freshly baked each morning and delivered to your doorstep in Addis Ababa.',
      copyright: '© 2026 Kenean Cake & Bread. All rights reserved.',
      lang_label: 'Language',
    },
    am: {
      site_name: 'ቀንያን ኬክ እና ዳቦ',
      tagline: 'ባለሙያ ዳቦ ቤትና ጣፋጮች',
      free_delivery_banner: 'ከ 1,000 ብር በላይ ለሆኑ ትዕዛዞች አዲስ አበባ ውስጥ ነፃ ማድረስ',
      track_order: 'ትዕዛዝ መከታተያ',
      contact: 'ያግኙን',
      help: 'እርዳታ',
      faq: 'ተደጋጋሚ ጥያቄዎች',
      about: 'ስለ እኛ',
      home: 'መነሻ',
      shop: 'ሱቅ',
      cakes_bread: 'ኬኮች እና ዳቦ',
      custom_cakes: 'የተለየ ኬክ ማዘዣ',
      branches: 'ቅርንጫፎች',
      cart: 'ቅርጫት',
      account: 'የግል መለያ',
      my_orders: 'የእኔ ትዕዛዞች',
      custom_requests: 'የተለዩ ኬክ ጥያቄዎች',
      admin_portal: 'የሰራተኞች ዳሽቦርድ',
      logout: 'ውጣ',
      sign_in: 'ግባ / ተመዝገብ',
      choose_branch: 'ቅርንጫፍዎን ይምረጡ',
      search_placeholder: 'ኬኮች፣ የኮመጠጠ ዳቦ እና ጣፋጮች ይፈልጉ...',
      ethiopia_currency: 'ብር (ETB)',
      footer_slogan: 'በተመረጠ ስንዴ፣ ባህላዊ ቦረሌ፣ ጥራት ባለው ቤልጂየም ቸኮሌት በየቀኑ በጠዋት ተጋግሮ የሚቀርብ ምርጥ ኬክ እና ዳቦ። አዲስ አበባ ውስጥ ባሉበት እናደርሳለን።',
      copyright: '© 2026 ቀንያን ኬክ እና ዳቦ። መብቱ በሕግ የተጠበቀ ነው።',
      lang_label: 'ቋንቋ',
    }
  };

  const t = (key) => {
    return translations[currentLocale.value]?.[key] || translations.en[key] || key;
  };

  const setLocale = (lang) => {
    if (['en', 'am'].includes(lang)) {
      currentLocale.value = lang;
      localStorage.setItem('kenean_locale', lang);
    }
  };

  const toggleLocale = () => {
    setLocale(currentLocale.value === 'en' ? 'am' : 'en');
  };

  const isAmharic = computed(() => currentLocale.value === 'am');

  return {
    currentLocale,
    isAmharic,
    t,
    setLocale,
    toggleLocale,
  };
});
