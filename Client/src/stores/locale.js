import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useLocaleStore = defineStore('locale', () => {
  const currentLocale = ref(localStorage.getItem('sweetbakes_locale') || 'en');

  const translations = {
    en: {
      // Top bar & Header
      free_delivery: 'Free Delivery on Orders Over ETB 1,000',
      follow_us: 'Follow Us:',
      select_branch: 'Select Bakery Branch',
      choose_branch_title: 'Choose Your Addis Ababa Branch',
      search_placeholder: 'Search for cakes, bread...',
      home: 'Home',
      shop: 'Shop',
      about: 'About',
      reviews: 'Reviews',
      contact: 'Contact',
      custom_cake: 'Custom Cake Studio',
      all_products: 'All Products',
      cakes: 'Cakes',
      bread: 'Fresh Bread',
      cupcakes_croissants: 'Cupcakes & Croissants',
      yetsom: "Ye'Tsom (Fasting / ጾም)",
      account: 'Account',
      my_orders: 'My Orders & Account',
      sign_in: 'Sign In',
      create_account: 'Create Account',
      staff_portal: 'Staff Portal',
      logout: 'Log Out',
      cart: 'Cart',
      view_all: 'View All',
      currency: 'ETB',

      // Hero Section
      hero_sub: 'Freshly Baked with Love',
      hero_title_1: 'Cakes & Bread',
      hero_title_2: 'for Every Special Moment',
      hero_desc: 'Delicious cakes, fresh bread and more... crafted with premium ingredients. Handcrafted daily for Addis Ababa celebrations.',
      shop_now: 'Shop Now',
      learn_more: 'Learn More',
      delivery_badge: 'Freshly baked and delivered to your door.',

      // Popular Products
      popular_title: 'Our Popular Products',
      popular_sub: 'Handpicked favorites from our bakery',
      cat_all: 'All',
      cat_cakes: 'Cakes',
      cat_bread: 'Bread',
      cat_cupcakes: 'Cupcakes',
      cat_pastries: 'Pastries',
      add_to_cart: 'Add to Cart',
      added_cart: 'Added! ✓',
      explore_all: 'Explore All Products',
      reviews_count: 'reviews',
      yetsom_badge: "Ye'Tsom / ጾም",

      // About Section
      about_tag: 'About SweetBakes',
      about_title: 'A Bakery You Can Trust',
      about_desc: 'At SweetBakes, we believe in the joy of fresh-baked goodness. Our cakes and bread are made with love, using the finest ingredients and creative designs to make your moments sweeter.',
      premium_quality: 'Premium Quality',
      fresh_ingredients: 'Fresh Ingredients',
      custom_orders: 'Custom Cake Orders',
      multibranch_service: 'Multi-Branch Service',
      discover_more: 'Discover More',
      years_exp: 'Years of Experience',
      branches_stat: 'Branches',
      since_2015: 'SINCE 2015 ♡',

      // Reviews
      reviews_title: 'What Our Customers Say',
      reviews_sub: 'Real reviews from our valued customers',
      write_review: 'Write a Review',

      // Special Offer
      special_offer: 'Special Offer',
      offer_title_1: 'Make Every Moment',
      offer_title_2: 'Sweeter!',
      offer_desc: 'Enjoy a special discount on selected bakery products. Handcrafted daily with love.',
      shop_offer: 'Shop the Offer',
      discount_label: '15% OFF',

      // Footer
      footer_slogan: 'Cakes · Bread · Happiness',
      footer_mission: 'Making life sweeter, one cake and bread at a time.',
      quick_links: 'Quick Links',
      our_services: 'Our Services',
      our_branches: 'Our Branches',
      cake_orders: 'Cake Orders',
      bread_pastries: 'Bread & Pastries',
      delivery_service: 'Delivery',
      pickup_service: 'Pickup',
      branch_locations: 'Branch Locations',
      newsletter: 'Newsletter',
      newsletter_sub: 'Subscribe to our newsletter for special offers!',
      enter_email: 'Your email address',
      subscribe_btn: 'Subscribe',
      privacy_policy: 'Privacy Policy',
      terms_service: 'Terms of Service',
      copyright: '© 2026 SweetBakes. All rights reserved.',

      // Shop Page Specifics
      filter_products: 'Filter Products',
      clear_filter: 'Clear',
      categories_label: 'Categories',
      all_categories: 'All Categories',
      price_range: 'Price Range',
      rating_filter: 'Rating',
      and_up: '& Up',
      reset_filters: 'Reset Filters',
      sort_by: 'Sort by:',
      sort_popular: 'Most Popular',
      sort_price_low: 'Price: Low to High',
      sort_price_high: 'Price: High to Low',
      sort_rating: 'Highest Rated',
      in_stock: 'In Stock',
      sold_out: 'Sold Out',
      showing: 'Showing',
      of: 'of',
      products_count_label: 'products',

      // Contact Page
      contact_title: 'Contact SweetBakes',
      contact_sub: "We'd love to hear from you, freshly baked with love!",
      helpline: 'Customer Helpline',
      helpline_desc: 'Direct Addis Ababa bakery support',
      email_inquiry: 'Email Inquiries',
      email_desc: 'Custom cakes, corporate orders & quotes',
      opening_hours: 'Bakehouse Hours',
      hours_desc: 'Open every day 7:00 AM – 9:00 PM',
      send_message: 'Send us a Message',
      full_name: 'Full Name',
      phone_number: 'Phone Number',
      subject_label: 'Subject',
      message_label: 'Your Message',
      send_btn: 'Send Message',
      sending_btn: 'Sending...',
      addis_bakehouses: 'Addis Ababa Bakehouses',
      view_map: 'View Map',
      shop_here: 'Shop Here',

      // Cart & Checkout
      bag_empty: 'Your shopping bag is empty',
      subtotal: 'Subtotal',
      delivery_fee: 'Delivery Fee',
      total_amount: 'Total',
      checkout_btn: 'Proceed to Checkout',
      free_label: 'Free',
    },
    am: {
      // Top bar & Header
      free_delivery: 'ከ 1,000 ብር በላይ ለሆኑ ትዕዛዞች ነፃ ማድረስ',
      follow_us: 'ይከተሉን:',
      select_branch: 'ቅርንጫፍ ይምረጡ',
      choose_branch_title: 'የአዲስ አበባ ቅርንጫፍዎን ይምረጡ',
      search_placeholder: 'ኬኮች፣ ዳቦ ይፈልጉ...',
      home: 'መነሻ',
      shop: 'ሱቅ',
      about: 'ስለ እኛ',
      reviews: 'አስተያየቶች',
      contact: 'ያግኙን',
      custom_cake: 'ብጁ ኬክ ስቱዲዮ',
      all_products: 'ሁሉም ምርቶች',
      cakes: 'ኬኮች',
      bread: 'ትኩስ ዳቦ',
      cupcakes_croissants: 'ካፕኬክ እና ክሮሳን',
      yetsom: "የጾም አማራጮች (ጾም)",
      account: 'መለያ',
      my_orders: 'የእኔ ትዕዛዞች እና መለያ',
      sign_in: 'ግባ',
      create_account: 'መለያ ፍጠር',
      staff_portal: 'የሰራተኞች ፖርታል',
      logout: 'ውጣ',
      cart: 'ቅርጫት',
      view_all: 'ሁሉንም ይመልከቱ',
      currency: 'ብር',

      // Hero Section
      hero_sub: 'በፍቅር ትኩስ ሆኖ የተጋገረ',
      hero_title_1: 'ኬኮች እና ዳቦ',
      hero_title_2: 'ለእያንዳንዱ ልዩ ወቅት',
      hero_desc: 'ጣፋጭ ኬኮች፣ ትኩስ ዳቦ እና ሌሎችም... በላቀ ጥራት የተዘጋጁ። ለአዲስ አበባ በዓላት በየቀኑ የሚጋገሩ።',
      shop_now: 'አሁን ይዘዙ',
      learn_more: 'ተጨማሪ ይወቁ',
      delivery_badge: 'ትኩስ ሆኖ የተጋገረ እና እስከ ደጃፍዎ የሚደርስ።',

      // Popular Products
      popular_title: 'ተወዳጅ ምርቶቻችን',
      popular_sub: 'ከዳቦ ቤታችን የተመረጡ ምርጥ ተወዳጆች',
      cat_all: 'ሁሉም',
      cat_cakes: 'ኬኮች',
      cat_bread: 'ዳቦ',
      cat_cupcakes: 'ካፕኬኮች',
      cat_pastries: 'ፓስትሪዎች',
      add_to_cart: 'ወደ ቅርጫት ጨምር',
      added_cart: 'ተጨምሯል! ✓',
      explore_all: 'ሁሉንም ምርቶች ይመልከቱ',
      reviews_count: 'አስተያየቶች',
      yetsom_badge: "የጾም / ጾም",

      // About Section
      about_tag: 'ስለ ስዊትቤክስ',
      about_title: 'የሚተማመኑበት ዳቦ ቤት',
      about_desc: 'በስዊትቤክስ፣ በትኩስ መጋገሪያ ደስታ እናምናለን። ኬኮች እና ዳቦዎቻችን በላቀ ጥራት እና ፈጠራ የእርስዎን ልዩ ጊዜያት ለማጣፈጥ በፍቅር የተዘጋጁ ናቸው።',
      premium_quality: 'ከፍተኛ ጥራት',
      fresh_ingredients: 'ትኩስ ጥሬ እቃዎች',
      custom_orders: 'ልዩ የኬክ ትዕዛዞች',
      multibranch_service: 'የበርካታ ቅርንጫፎች አገልግሎት',
      discover_more: 'ተጨማሪ ይመልከቱ',
      years_exp: 'ዓመታት ልምድ',
      branches_stat: 'ቅርንጫፎች',
      since_2015: 'ከ 2015 ጀምሮ ♡',

      // Reviews
      reviews_title: 'ደንበኞቻችን ምን ይላሉ',
      reviews_sub: 'ከውድ ደንበኞቻችን የተሰጡ እውነተኛ አስተያየቶች',
      write_review: 'አስተያየት ይጻፉ',

      // Special Offer
      special_offer: 'ልዩ ቅናሽ',
      offer_title_1: 'እያንዳንዱን ቅጽበት',
      offer_title_2: 'ያጣፍጡ!',
      offer_desc: 'በተመረጡ የዳቦ እና ኬክ ምርቶች ላይ በልዩ ቅናሽ ይደሰቱ። በየቀኑ በፍቅር የተጋገሩ።',
      shop_offer: 'ቅናሹን ይዘዙ',
      discount_label: '15% ቅናሽ',

      // Footer
      footer_slogan: 'ኬክ · ዳቦ · ደስታ',
      footer_mission: 'ህይወትን የበለጠ ማጣፈጥ፣ በአንድ ኬክ እና ዳቦ።',
      quick_links: 'ፈጣን አገናኞች',
      our_services: 'አገልግሎቶቻችን',
      our_branches: 'ቅርንጫፎቻችን',
      cake_orders: 'የኬክ ትዕዛዞች',
      bread_pastries: 'ዳቦ እና ፓስትሪዎች',
      delivery_service: 'ማድረስ',
      pickup_service: 'በቀጥታ መውሰድ',
      branch_locations: 'የቅርንጫፍ አድራሻዎች',
      newsletter: 'የዜና መጽሔት',
      newsletter_sub: 'ለልዩ ቅናሾች በኢሜይልዎ ይመዝገቡ!',
      enter_email: 'የኢሜይል አድራሻዎ',
      subscribe_btn: 'ተመዝገብ',
      privacy_policy: 'የግላዊነት ፖሊሲ',
      terms_service: 'የአገልግሎት ውሎች',
      copyright: '© 2026 ስዊትቤክስ። መብቱ በሕግ የተጠበቀ ነው።',

      // Shop Page Specifics
      filter_products: 'ምርቶችን አጣራ',
      clear_filter: 'አጽዳ',
      categories_label: 'ምድቦች',
      all_categories: 'ሁሉም ምድቦች',
      price_range: 'የዋጋ ክልል',
      rating_filter: 'ደረጃ',
      and_up: 'እና በላይ',
      reset_filters: 'ማጣሪያውን መልስ',
      sort_by: 'ቅደም ተከተል:',
      sort_popular: 'በጣም ተወዳጅ',
      sort_price_low: 'ዋጋ: ከዝቅተኛ ወደ ከፍተኛ',
      sort_price_high: 'ዋጋ: ከከፍተኛ ወደ ዝቅተኛ',
      sort_rating: 'ከፍተኛ ደረጃ የተሰጣቸው',
      in_stock: 'አለ',
      sold_out: 'አልቋል',
      showing: 'የሚታዩት',
      of: 'ከ',
      products_count_label: 'ምርቶች',

      // Contact Page
      contact_title: 'ስዊትቤክስን ያግኙ',
      contact_sub: 'ከእርስዎ ለመስማት እንወዳለን፣ በፍቅር የተጋገረ!',
      helpline: 'የደንበኞች አገልግሎት',
      helpline_desc: 'የቀጥታ አዲስ አበባ የዳቦ ቤት ድጋፍ',
      email_inquiry: 'የኢሜይል ጥያቄዎች',
      email_desc: 'ልዩ ኬኮች፣ የድርጅት ትዕዛዞች እና ዋጋ',
      opening_hours: 'የስራ ሰዓታት',
      hours_desc: 'በየቀኑ ከጠዋቱ 1:00 – ማታ 3:00 ክፍት ነው',
      send_message: 'መልዕክት ይላኩልን',
      full_name: 'ሙሉ ስም',
      phone_number: 'ስልክ ቁጥር',
      subject_label: 'ርዕስ',
      message_label: 'መልዕክትዎ',
      send_btn: 'መልዕክት ላክ',
      sending_btn: 'በመላክ ላይ...',
      addis_bakehouses: 'የአዲስ አበባ ቅርንጫፎች',
      view_map: 'ካርታ ይመልከቱ',
      shop_here: 'እዚህ ይዘዙ',

      // Cart & Checkout
      bag_empty: 'የግዢ ቅርጫትዎ ባዶ ነው',
      subtotal: 'ድምር',
      delivery_fee: 'የማድረሻ ክፍያ',
      total_amount: 'ጠቅላላ ዋጋ',
      checkout_btn: 'ትዕዛዝ ያጠናቁ',
      free_label: 'ነፃ',
    }
  };

  // Product name translations dictionary
  const productTranslations = {
    'Chocolate Cake': 'ቸኮሌት ኬክ',
    'Vanilla Cake': 'ቫኒላ ኬክ',
    'Fresh Bread': 'ትኩስ ዳቦ',
    'Cupcakes': 'ካፕኬኮች',
    'Croissant': 'ክሮሳን',
    'Strawberry Cake': 'እንጆሪ ኬክ',
    'Cookies': 'ኩኪዎች',
    'Pastries': 'ፓስትሪዎች',
    'Whole Wheat Bread': 'የሙሉ ስንዴ ዳቦ',
    'Blueberry Muffin Box': 'ብሉቤሪ ማፊን',
    'Rustic Rye Sourdough Loaf': 'የቦረሌ ዳቦ (ሳወርዶው)',
    "Ye'Tsom Dark Chocolate Cake (የጾም ቸኮሌት)": 'የጾም ቸኮሌት ኬክ',
    "Ye'Tsom Cinnamon Apple Loaf (የጾም አፕል)": 'የጾም አፕል ዳቦ',
    "Ye'Tsom Dark Chocolate Cake": 'የጾም ቸኮሌት ኬክ',
    "Ye'Tsom Cinnamon Apple Loaf": 'የጾም አፕል ዳቦ',
  };

  // Customer testimonial quotes translations
  const quoteTranslations = {
    'The cake was amazing! Fresh, beautiful and delicious. Highly recommended!': 'ኬኩ ድንቅ ነበር! ትኩስ፣ ያማረ እና እጅግ በጣም ጣፋጭ። በጣም እመክረዋለሁ!',
    'Great service and tasty bread. Will order again soon!': 'ጥሩ አገልግሎት እና ጣፋጭ ዳቦ። በቅርቡ እንደገና አዛለሁ!',
    'Best bakery in Addis! The custom cake exceeded my expectations.': 'በአዲስ አበባ ምርጡ ዳቦ ቤት! ያዘዝኩት ልዩ ኬክ ከጠበቅኩት በላይ ሆኖልኛል።',
    'Ordered for my birthday party and everyone loved it. The crust was perfection.': 'ለልደት ድግሴ አዝዤ ሁሉም ሰው ወደደው። የዳቦው እና ኬኩ አሰራር ፍጹም ነበር።',
    'Consistently fresh and delivered right on time across Bole!': 'ሁልጊዜ ትኩስ እና በቦሌ አካባቢ በትክክለኛው ሰዓት ደርሷል!'
  };

  const nameTranslations = {
    'Sara Ahmed': 'ሳራ አህመድ',
    'Daniel Kebede': 'ዳንኤል ከበደ',
    'Mekdes Tadesse': 'መቅደስ ታደሰ',
    'Almaz Hailu': 'አልማዝ ኃይሉ',
    'Yonas Bekele': 'ዮናስ በቀለ'
  };

  const isAmharic = computed(() => currentLocale.value === 'am');

  const t = (key, fallback = '') => {
    return translations[currentLocale.value]?.[key] || translations.en[key] || fallback || key;
  };

  const tProduct = (name) => {
    if (!name) return '';
    if (isAmharic.value && productTranslations[name]) {
      return productTranslations[name];
    }
    return name;
  };

  const tQuote = (quote) => {
    if (!quote) return '';
    if (isAmharic.value && quoteTranslations[quote]) {
      return quoteTranslations[quote];
    }
    return quote;
  };

  const tName = (name) => {
    if (!name) return '';
    if (isAmharic.value && nameTranslations[name]) {
      return nameTranslations[name];
    }
    return name;
  };

  const setLocale = (lang) => {
    if (['en', 'am'].includes(lang)) {
      currentLocale.value = lang;
      localStorage.setItem('sweetbakes_locale', lang);
      document.documentElement.lang = lang;
      if (lang === 'am') {
        document.documentElement.classList.add('lang-am');
      } else {
        document.documentElement.classList.remove('lang-am');
      }
    }
  };

  const toggleLocale = () => {
    setLocale(currentLocale.value === 'en' ? 'am' : 'en');
  };

  const initLocale = () => {
    const saved = localStorage.getItem('sweetbakes_locale') || 'en';
    setLocale(saved);
  };

  return {
    currentLocale,
    isAmharic,
    t,
    tProduct,
    tQuote,
    tName,
    setLocale,
    toggleLocale,
    initLocale,
  };
});
