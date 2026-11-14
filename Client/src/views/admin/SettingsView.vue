<template>
  <div class="space-y-6 max-w-7xl mx-auto font-poppins">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="dash-title text-xl sm:text-2xl">Settings &amp; Content Management</h2>
        <p class="dash-subtitle mt-0.5">Configure system rules, business details, homepage banners, and customer feedback</p>
      </div>

      <div class="flex items-center space-x-3 w-full sm:w-auto justify-end">
        <button
          v-if="activeTab === 'general' || activeTab === 'business'"
          @click="loadSettings"
          class="dash-btn-secondary text-xs sm:text-sm py-1.5 px-3.5"
        >
          Cancel
        </button>
        <button
          v-if="activeTab === 'general' || activeTab === 'business'"
          @click="saveSettings"
          :disabled="saving"
          class="dash-btn-primary text-xs sm:text-sm py-1.5 px-4 disabled:opacity-50"
        >
          {{ saving ? 'Saving...' : 'Save Settings' }}
        </button>
      </div>
    </div>

    <!-- Alert Banner -->
    <div v-if="successMsg" class="p-3 bg-[#EBF7EB] border border-[#C5E9C6] rounded-lg text-xs text-[#1E7422] flex items-center justify-between">
      <span>{{ successMsg }}</span>
      <button @click="successMsg = ''" class="text-[#1E7422] font-bold">&times;</button>
    </div>
    <div v-if="errorMsg" class="p-3 bg-[#FDEAEA] border border-[#FFCDD2] rounded-lg text-xs text-[#C62828] flex items-center justify-between">
      <span>{{ errorMsg }}</span>
      <button @click="errorMsg = ''" class="text-[#C62828] font-bold">&times;</button>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center space-x-2 border-b border-[#E9E0D4] overflow-x-auto pb-1 text-xs font-semibold">
      <button
        @click="activeTab = 'general'"
        class="px-4 py-2 rounded-t-lg transition border-b-2 cursor-pointer shrink-0"
        :class="activeTab === 'general' ? 'border-[#BA5420] text-[#BA5420] bg-white' : 'border-transparent text-[#7D6E64] hover:text-[#2A1D15]'"
      >
        ⚙️ System &amp; Orders
      </button>
      <button
        @click="activeTab = 'business'"
        class="px-4 py-2 rounded-t-lg transition border-b-2 cursor-pointer shrink-0"
        :class="activeTab === 'business' ? 'border-[#BA5420] text-[#BA5420] bg-white' : 'border-transparent text-[#7D6E64] hover:text-[#2A1D15]'"
      >
        🏢 Business Info &amp; About
      </button>
      <button
        @click="activeTab = 'banners'; loadBanners()"
        class="px-4 py-2 rounded-t-lg transition border-b-2 cursor-pointer shrink-0"
        :class="activeTab === 'banners' ? 'border-[#BA5420] text-[#BA5420] bg-white' : 'border-transparent text-[#7D6E64] hover:text-[#2A1D15]'"
      >
        🎨 Homepage Banners
      </button>
      <button
        @click="activeTab = 'reviews'; loadReviews()"
        class="px-4 py-2 rounded-t-lg transition border-b-2 cursor-pointer shrink-0"
        :class="activeTab === 'reviews' ? 'border-[#BA5420] text-[#BA5420] bg-white' : 'border-transparent text-[#7D6E64] hover:text-[#2A1D15]'"
      >
        💬 Customer Reviews
      </button>
      <button
        @click="activeTab = 'inquiries'; loadInquiries()"
        class="px-4 py-2 rounded-t-lg transition border-b-2 cursor-pointer shrink-0"
        :class="activeTab === 'inquiries' ? 'border-[#BA5420] text-[#BA5420] bg-white' : 'border-transparent text-[#7D6E64] hover:text-[#2A1D15]'"
      >
        ✉️ Contact Messages
      </button>
      <button
        @click="activeTab = 'subscribers'; loadSubscribers()"
        class="px-4 py-2 rounded-t-lg transition border-b-2 cursor-pointer shrink-0"
        :class="activeTab === 'subscribers' ? 'border-[#BA5420] text-[#BA5420] bg-white' : 'border-transparent text-[#7D6E64] hover:text-[#2A1D15]'"
      >
        📬 Newsletter Subscribers
      </button>
    </div>

    <!-- TAB 1: System & Orders Grid -->
    <div v-if="activeTab === 'general'" class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
      <!-- Left Column: Orders, Transfers, Custom Cakes -->
      <div class="space-y-6">
        <!-- Orders and stock -->
        <div class="bg-white border border-[#E9E0D4] rounded-xl shadow-sm p-6 space-y-4">
          <h3 class="font-serif font-bold text-base sm:text-lg text-[#2A1D15]">Orders and stock</h3>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Unpaid order reservation (minutes)</label>
              <input
                v-model="form.unpaid_reservation_minutes"
                type="number"
                class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-1 focus:ring-[#BA5420]"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Default reorder level</label>
              <input
                v-model="form.default_reorder_level"
                type="number"
                class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-1 focus:ring-[#BA5420]"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Pay on pickup or delivery</label>
              <select
                v-model="form.pay_on_pickup_delivery"
                class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-1 focus:ring-[#BA5420]"
              >
                <option value="Enabled">Enabled</option>
                <option value="Disabled">Disabled</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Cancellation allowed until</label>
              <select
                v-model="form.cancellation_allowed_until"
                class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-1 focus:ring-[#BA5420]"
              >
                <option value="Preparing starts">Preparing starts</option>
                <option value="Confirmed">Confirmed</option>
                <option value="Ready">Ready</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Transfers -->
        <div class="bg-white border border-[#E9E0D4] rounded-xl shadow-sm p-6 space-y-4">
          <h3 class="font-serif font-bold text-base sm:text-lg text-[#2A1D15]">Transfers</h3>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Super Admin approval threshold (units)</label>
              <input
                v-model="form.approval_threshold_units"
                type="number"
                class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-1 focus:ring-[#BA5420]"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Unreceived reminder after (hours)</label>
              <input
                v-model="form.unreceived_reminder_hours"
                type="number"
                class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-1 focus:ring-[#BA5420]"
              />
            </div>
          </div>
        </div>

        <!-- Custom cakes -->
        <div class="bg-white border border-[#E9E0D4] rounded-xl shadow-sm p-6 space-y-4">
          <h3 class="font-serif font-bold text-base sm:text-lg text-[#2A1D15]">Custom cakes</h3>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Default deposit</label>
              <select
                v-model="form.default_deposit_percent"
                class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-1 focus:ring-[#BA5420]"
              >
                <option value="30">30%</option>
                <option value="50">50%</option>
                <option value="100">100%</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Deposit refundable</label>
              <select
                v-model="form.deposit_refundable"
                class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-1 focus:ring-[#BA5420]"
              >
                <option value="Before branch starts work">Before branch starts work</option>
                <option value="Non-refundable">Non-refundable</option>
                <option value="Within 24 hours">Within 24 hours</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Payments, Localization, Security -->
      <div class="space-y-6">
        <!-- Payments -->
        <div class="bg-white border border-[#E9E0D4] rounded-xl shadow-sm p-6 space-y-4">
          <h3 class="font-serif font-bold text-base sm:text-lg text-[#2A1D15]">Payments</h3>

          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Gateways</label>
            <select
              v-model="form.gateways"
              class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-1 focus:ring-[#BA5420]"
            >
              <option value="Chapa, Telebirr">Chapa, Telebirr</option>
              <option value="Chapa only">Chapa only</option>
              <option value="Telebirr only">Telebirr only</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Webhook signing secret</label>
            <input
              v-model="form.webhook_secret"
              type="password"
              placeholder="••••••••••••"
              class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-1 focus:ring-[#BA5420]"
            />
          </div>
        </div>

        <!-- Language and currency -->
        <div class="bg-white border border-[#E9E0D4] rounded-xl shadow-sm p-6 space-y-4">
          <h3 class="font-serif font-bold text-base sm:text-lg text-[#2A1D15]">Language and currency</h3>

          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Languages</label>
            <select
              v-model="form.languages"
              class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-1 focus:ring-[#BA5420]"
            >
              <option value="English, Amharic">English, Amharic</option>
              <option value="English only">English only</option>
              <option value="Amharic only">Amharic only</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Currency</label>
            <select
              v-model="form.currency"
              class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-1 focus:ring-[#BA5420]"
            >
              <option value="ETB">ETB</option>
              <option value="USD">USD</option>
            </select>
          </div>
        </div>

        <!-- Security -->
        <div class="bg-white border border-[#E9E0D4] rounded-xl shadow-sm p-6 space-y-4">
          <h3 class="font-serif font-bold text-base sm:text-lg text-[#2A1D15]">Security &amp; Backups</h3>

          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Admin two-factor sign-in</label>
            <select
              v-model="form.admin_2fa"
              class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-1 focus:ring-[#BA5420]"
            >
              <option value="Optional">Optional</option>
              <option value="Enforced">Enforced</option>
              <option value="Disabled">Disabled</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Backups</label>
            <select
              v-model="form.backups"
              class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-1 focus:ring-[#BA5420]"
            >
              <option value="Daily, kept 30 days">Daily, kept 30 days</option>
              <option value="Weekly, kept 60 days">Weekly, kept 60 days</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- TAB 2: Cafe Identity, Homepage Hero & About Page Settings -->
    <div v-else-if="activeTab === 'business'" class="space-y-6">
      <!-- Section 1: Cafe Identity & Slogan -->
      <div class="bg-white border border-[#E9E0D4] rounded-xl shadow-sm p-5 sm:p-6 space-y-4">
        <div class="border-b border-[#EFE8DF] pb-3 flex items-center justify-between">
          <div>
            <h3 class="font-serif font-bold text-base sm:text-lg text-[#2A1D15] flex items-center gap-2">
              <span>🏪</span>
              <span>Cafe &amp; Bakery Identity</span>
            </h3>
            <p class="text-xs text-[#8A7B71] font-poppins">Customize your cafe name, tagline, founding year, and general description</p>
          </div>
          <span class="text-xs font-semibold px-2.5 py-1 bg-amber-50 text-amber-800 rounded-full border border-amber-200">Global Branding</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-bold text-[#4A3B32] mb-1">Bakery / Cafe Name *</label>
            <input v-model="form.site_name" type="text" placeholder="e.g. SweetBakes Bakery" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-2 focus:ring-[#DE1B54]/20 focus:border-[#DE1B54]" />
          </div>
          <div>
            <label class="block text-xs font-bold text-[#4A3B32] mb-1">Tagline / Slogan</label>
            <input v-model="form.site_tagline" type="text" placeholder="e.g. Artisanal Bakery & Celebration Cakes" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-2 focus:ring-[#DE1B54]/20 focus:border-[#DE1B54]" />
          </div>
          <div>
            <label class="block text-xs font-bold text-[#4A3B32] mb-1">Founded / Established Year</label>
            <input v-model="form.since_year" type="text" placeholder="e.g. 2015" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-2 focus:ring-[#DE1B54]/20 focus:border-[#DE1B54]" />
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-[#4A3B32] mb-1">Global Description (English)</label>
            <textarea v-model="form.site_description_en" rows="2" placeholder="Brief description of the cafe..." class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-2 focus:ring-[#DE1B54]/20 focus:border-[#DE1B54]"></textarea>
          </div>
          <div>
            <label class="block text-xs font-bold text-[#4A3B32] mb-1">Global Description (Amharic - አማርኛ)</label>
            <textarea v-model="form.site_description_am" rows="2" placeholder="የካፌው አጭር መግለጫ..." class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-2 focus:ring-[#DE1B54]/20 focus:border-[#DE1B54]"></textarea>
          </div>
        </div>
      </div>

      <!-- Section 2: Homepage Hero Banner Content -->
      <div class="bg-white border border-[#E9E0D4] rounded-xl shadow-sm p-5 sm:p-6 space-y-4">
        <div class="border-b border-[#EFE8DF] pb-3 flex items-center justify-between">
          <div>
            <h3 class="font-serif font-bold text-base sm:text-lg text-[#2A1D15] flex items-center gap-2">
              <span>✨</span>
              <span>Homepage Hero Banner &amp; Headline</span>
            </h3>
            <p class="text-xs text-[#8A7B71] font-poppins">Customize the main headline, subtitle badge, description, and button shown at the top of the homepage</p>
          </div>
          <span class="text-xs font-semibold px-2.5 py-1 bg-rose-50 text-[#DE1B54] rounded-full border border-rose-200">Homepage Top</span>
        </div>

        <!-- Hero Subtitle Badge -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-[#4A3B32] mb-1">Hero Pill Badge (English)</label>
            <input v-model="form.hero_subtitle_en" type="text" placeholder="e.g. Artisanal Cakes & Breads" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-2 focus:ring-[#DE1B54]/20 focus:border-[#DE1B54]" />
          </div>
          <div>
            <label class="block text-xs font-bold text-[#4A3B32] mb-1">Hero Pill Badge (Amharic)</label>
            <input v-model="form.hero_subtitle_am" type="text" placeholder="e.g. ልዩ የክብረ በዓል ኬኮች እና ዳቦ" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-2 focus:ring-[#DE1B54]/20 focus:border-[#DE1B54]" />
          </div>
        </div>

        <!-- Hero Main Headline (Line 1) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-[#4A3B32] mb-1">Main Headline Line 1 (English)</label>
            <input v-model="form.hero_title_en" type="text" placeholder="e.g. Baked Fresh with Love" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-2 focus:ring-[#DE1B54]/20 focus:border-[#DE1B54]" />
          </div>
          <div>
            <label class="block text-xs font-bold text-[#4A3B32] mb-1">Main Headline Line 1 (Amharic)</label>
            <input v-model="form.hero_title_am" type="text" placeholder="e.g. በፍቅር የተዘጋጀ ትኩስ ጣዕም" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-2 focus:ring-[#DE1B54]/20 focus:border-[#DE1B54]" />
          </div>
        </div>

        <!-- Hero Subheadline (Line 2) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-[#4A3B32] mb-1">Headline Line 2 / Accent (English)</label>
            <input v-model="form.hero_title2_en" type="text" placeholder="e.g. Addis Ababa's Favorite" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-2 focus:ring-[#DE1B54]/20 focus:border-[#DE1B54]" />
          </div>
          <div>
            <label class="block text-xs font-bold text-[#4A3B32] mb-1">Headline Line 2 / Accent (Amharic)</label>
            <input v-model="form.hero_title2_am" type="text" placeholder="e.g. የአዲስ አበባ ተመራጭ" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-2 focus:ring-[#DE1B54]/20 focus:border-[#DE1B54]" />
          </div>
        </div>

        <!-- Hero Description Paragraph -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-[#4A3B32] mb-1">Hero Description (English)</label>
            <textarea v-model="form.hero_desc_en" rows="2" placeholder="e.g. Handcrafted celebration cakes and warm sourdough..." class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-2 focus:ring-[#DE1B54]/20 focus:border-[#DE1B54]"></textarea>
          </div>
          <div>
            <label class="block text-xs font-bold text-[#4A3B32] mb-1">Hero Description (Amharic)</label>
            <textarea v-model="form.hero_desc_am" rows="2" placeholder="e.g. የልደትና የሰርግ ኬኮች እንዲሁም..." class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-2 focus:ring-[#DE1B54]/20 focus:border-[#DE1B54]"></textarea>
          </div>
        </div>

        <!-- Hero CTA Button & Target URL -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-bold text-[#4A3B32] mb-1">Button Text (English)</label>
            <input v-model="form.hero_button_text_en" type="text" placeholder="e.g. Order Online" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-2 focus:ring-[#DE1B54]/20 focus:border-[#DE1B54]" />
          </div>
          <div>
            <label class="block text-xs font-bold text-[#4A3B32] mb-1">Button Text (Amharic)</label>
            <input v-model="form.hero_button_text_am" type="text" placeholder="e.g. አሁን ይዘዙ" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-2 focus:ring-[#DE1B54]/20 focus:border-[#DE1B54]" />
          </div>
          <div>
            <label class="block text-xs font-bold text-[#4A3B32] mb-1">Button Target Link</label>
            <input v-model="form.hero_button_link" type="text" placeholder="e.g. /shop" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-2 focus:ring-[#DE1B54]/20 focus:border-[#DE1B54]" />
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold text-[#4A3B32] mb-1">Hero Background Image (URL or Upload from PC / Mobile)</label>
          <div class="flex flex-wrap sm:flex-nowrap items-center gap-2.5">
            <img
              v-if="form.hero_image_url"
              :src="form.hero_image_url"
              alt="Hero Preview"
              class="w-16 h-10 rounded-lg object-cover border border-[#D8CDC0] shadow-xs shrink-0 bg-[#FAF7F5]"
            />
            <input
              v-model="form.hero_image_url"
              type="text"
              placeholder="/mockup/hero_banner_hq.jpg"
              class="flex-1 min-w-[160px] bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-2 focus:ring-[#DE1B54]/20 focus:border-[#DE1B54]"
            />
            <label
              data-testid="upload-hero-image-label"
              class="px-3.5 py-2 min-h-[38px] inline-flex items-center justify-center space-x-1.5 text-xs font-semibold bg-white border border-[#D8CDC0] hover:bg-[#F4ECE3] text-[#4A3B32] rounded-lg shadow-2xs transition cursor-pointer shrink-0 whitespace-nowrap"
              :class="{ 'opacity-60 pointer-events-none': uploadingSlot === 'hero' }"
              title="Upload hero photo from PC or mobile"
            >
              <span>{{ uploadingSlot === 'hero' ? '⏳ Uploading...' : '📁 Upload' }}</span>
                <input
                  data-testid="upload-hero-image-input"
                  type="file"
                  accept="image/jpeg,image/png,image/webp,image/gif,image/bmp,image/jfif,image/avif,image/*"
                  :disabled="uploadingSlot === 'hero'"
                  class="hidden"
                  @change="e => uploadSettingImage(e, form, 'hero_image_url', 'hero', 'hero_image_url')"
                />
            </label>
          </div>
          <p class="text-[11px] text-[#8A7B71] mt-1">Upload from PC or mobile (JPG, PNG, WebP up to 10MB) or enter an image URL.</p>
        </div>
      </div>

      <!-- Section 3: About Page & Story Section -->
      <div class="bg-white border border-[#E9E0D4] rounded-xl shadow-sm p-5 sm:p-6 space-y-4">
        <div class="border-b border-[#EFE8DF] pb-3 flex items-center justify-between">
          <div>
            <h3 class="font-serif font-bold text-base sm:text-lg text-[#2A1D15] flex items-center gap-2">
              <span>📖</span>
              <span>About Page &amp; Story Section</span>
            </h3>
            <p class="text-xs text-[#8A7B71] font-poppins">Customize the About section on the homepage and the full About Us page</p>
          </div>
          <span class="text-xs font-semibold px-2.5 py-1 bg-emerald-50 text-emerald-800 rounded-full border border-emerald-200">About View &amp; Section</span>
        </div>

        <!-- About Tag & Headline -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-[#4A3B32] mb-1">About Section Tag (English)</label>
            <input v-model="form.about_tag_en" type="text" placeholder="e.g. About SweetBakes" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-2 focus:ring-[#DE1B54]/20 focus:border-[#DE1B54]" />
          </div>
          <div>
            <label class="block text-xs font-bold text-[#4A3B32] mb-1">About Section Tag (Amharic)</label>
            <input v-model="form.about_tag_am" type="text" placeholder="e.g. ስለ ስዊትቤክስ" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-2 focus:ring-[#DE1B54]/20 focus:border-[#DE1B54]" />
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-[#4A3B32] mb-1">About Main Headline (English)</label>
            <input v-model="form.about_title_en" type="text" placeholder="e.g. A Bakery You Can Trust" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-2 focus:ring-[#DE1B54]/20 focus:border-[#DE1B54]" />
          </div>
          <div>
            <label class="block text-xs font-bold text-[#4A3B32] mb-1">About Main Headline (Amharic)</label>
            <input v-model="form.about_title_am" type="text" placeholder="e.g. የሚተማመኑበት የዳቦና ኬክ መጋገሪያ" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-2 focus:ring-[#DE1B54]/20 focus:border-[#DE1B54]" />
          </div>
        </div>

        <!-- About Story Textarea -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-[#4A3B32] mb-1">About Story (English)</label>
            <textarea v-model="form.about_story_en" rows="3" placeholder="Tell the story of your bakery..." class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-2 focus:ring-[#DE1B54]/20 focus:border-[#DE1B54]"></textarea>
          </div>
          <div>
            <label class="block text-xs font-bold text-[#4A3B32] mb-1">About Story (Amharic)</label>
            <textarea v-model="form.about_story_am" rows="3" placeholder="የዳቦ መጋገሪያዎን ታሪክ ይንገሩ..." class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-2 focus:ring-[#DE1B54]/20 focus:border-[#DE1B54]"></textarea>
          </div>
        </div>

        <!-- Chef Photo & Quote -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-bold text-[#4A3B32] mb-1">Chef / Bakery About Photo (URL or Upload)</label>
            <div class="flex flex-wrap xs:flex-nowrap items-center gap-2">
              <img
                v-if="form.about_image_url"
                :src="form.about_image_url"
                alt="Chef Preview"
                class="w-11 h-11 rounded-lg object-cover border border-[#D8CDC0] shadow-xs shrink-0 bg-[#FAF7F5]"
              />
              <input
                v-model="form.about_image_url"
                type="text"
                placeholder="Image URL or upload file"
                class="flex-1 min-w-[120px] bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-2 focus:ring-[#DE1B54]/20 focus:border-[#DE1B54]"
              />
              <label
                data-testid="upload-about-image-label"
                class="px-3 py-2 min-h-[38px] inline-flex items-center justify-center space-x-1 text-xs font-semibold bg-white border border-[#D8CDC0] hover:bg-[#F4ECE3] text-[#4A3B32] rounded-lg shadow-2xs transition cursor-pointer shrink-0 whitespace-nowrap"
                :class="{ 'opacity-60 pointer-events-none': uploadingSlot === 'about' }"
                title="Upload About photo from PC or mobile"
              >
                <span>{{ uploadingSlot === 'about' ? '⏳ Uploading...' : '📁 Upload' }}</span>
                <input
                  data-testid="upload-about-image-input"
                  type="file"
                  accept="image/jpeg,image/png,image/webp,image/gif,image/bmp,image/jfif,image/avif,image/*"
                  :disabled="uploadingSlot === 'about'"
                  class="hidden"
                  @change="e => uploadSettingImage(e, form, 'about_image_url', 'about', 'about_image_url')"
                />
              </label>
            </div>
            <p class="text-[11px] text-[#8A7B71] mt-1">Select a photo from PC or mobile (JPG, PNG, WebP up to 10MB) or paste a URL.</p>
          </div>
          <div>
            <label class="block text-xs font-bold text-[#4A3B32] mb-1">Chef Quote (English)</label>
            <input v-model="form.about_chef_quote_en" type="text" placeholder="“Making life sweeter, one cake at a time.”" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-2 focus:ring-[#DE1B54]/20 focus:border-[#DE1B54]" />
          </div>
          <div>
            <label class="block text-xs font-bold text-[#4A3B32] mb-1">Chef Quote (Amharic)</label>
            <input v-model="form.about_chef_quote_am" type="text" placeholder="“እያንዳንዱን ኬክ እና ዳቦ በፍቅር...”" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D] focus:ring-2 focus:ring-[#DE1B54]/20 focus:border-[#DE1B54]" />
          </div>
        </div>

        <!-- 4 Value Bullets -->
        <div class="space-y-2 pt-2">
          <label class="block text-xs font-bold text-[#4A3B32]">4 Key Value Bullets (English &amp; Amharic)</label>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="flex items-center space-x-2">
              <span class="w-5 h-5 rounded-full bg-[#DE1B54] text-white flex items-center justify-center text-[10px] font-bold shrink-0">1</span>
              <input v-model="form.about_bullet_1_en" type="text" placeholder="Bullet 1 (EN)" class="w-1/2 bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-2.5 py-1.5 text-xs text-[#2D231D]" />
              <input v-model="form.about_bullet_1_am" type="text" placeholder="Bullet 1 (AM)" class="w-1/2 bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-2.5 py-1.5 text-xs text-[#2D231D]" />
            </div>
            <div class="flex items-center space-x-2">
              <span class="w-5 h-5 rounded-full bg-[#DE1B54] text-white flex items-center justify-center text-[10px] font-bold shrink-0">2</span>
              <input v-model="form.about_bullet_2_en" type="text" placeholder="Bullet 2 (EN)" class="w-1/2 bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-2.5 py-1.5 text-xs text-[#2D231D]" />
              <input v-model="form.about_bullet_2_am" type="text" placeholder="Bullet 2 (AM)" class="w-1/2 bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-2.5 py-1.5 text-xs text-[#2D231D]" />
            </div>
            <div class="flex items-center space-x-2">
              <span class="w-5 h-5 rounded-full bg-[#DE1B54] text-white flex items-center justify-center text-[10px] font-bold shrink-0">3</span>
              <input v-model="form.about_bullet_3_en" type="text" placeholder="Bullet 3 (EN)" class="w-1/2 bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-2.5 py-1.5 text-xs text-[#2D231D]" />
              <input v-model="form.about_bullet_3_am" type="text" placeholder="Bullet 3 (AM)" class="w-1/2 bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-2.5 py-1.5 text-xs text-[#2D231D]" />
            </div>
            <div class="flex items-center space-x-2">
              <span class="w-5 h-5 rounded-full bg-[#DE1B54] text-white flex items-center justify-center text-[10px] font-bold shrink-0">4</span>
              <input v-model="form.about_bullet_4_en" type="text" placeholder="Bullet 4 (EN)" class="w-1/2 bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-2.5 py-1.5 text-xs text-[#2D231D]" />
              <input v-model="form.about_bullet_4_am" type="text" placeholder="Bullet 4 (AM)" class="w-1/2 bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-2.5 py-1.5 text-xs text-[#2D231D]" />
            </div>
          </div>
        </div>

        <!-- Key Stats Counter -->
        <div class="pt-2">
          <label class="block text-xs font-bold text-[#4A3B32] mb-2">Bakery Experience &amp; Impact Stats</label>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div>
              <label class="block text-[11px] text-[#7D6E64] mb-1">Years of Baking</label>
              <input v-model="form.stat_experience_years" type="text" placeholder="12+" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-1.5 text-xs text-[#2D231D]" />
            </div>
            <div>
              <label class="block text-[11px] text-[#7D6E64] mb-1">Happy Customers</label>
              <input v-model="form.stat_happy_customers" type="text" placeholder="50k+" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-1.5 text-xs text-[#2D231D]" />
            </div>
            <div>
              <label class="block text-[11px] text-[#7D6E64] mb-1">Daily Fresh</label>
              <input v-model="form.stat_daily_fresh" type="text" placeholder="100%" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-1.5 text-xs text-[#2D231D]" />
            </div>
            <div>
              <label class="block text-[11px] text-[#7D6E64] mb-1">Master Chefs</label>
              <input v-model="form.stat_master_chefs" type="text" placeholder="15+" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-1.5 text-xs text-[#2D231D]" />
            </div>
          </div>
        </div>
      </div>

      <!-- Section 4: Contact, Location & Working Hours -->
      <div class="bg-white border border-[#E9E0D4] rounded-xl shadow-sm p-5 sm:p-6 space-y-4">
        <div class="border-b border-[#EFE8DF] pb-3 flex items-center justify-between">
          <div>
            <h3 class="font-serif font-bold text-base sm:text-lg text-[#2A1D15] flex items-center gap-2">
              <span>📍</span>
              <span>Contact Details, Working Hours &amp; GPS</span>
            </h3>
            <p class="text-xs text-[#8A7B71] font-poppins">Customer contact channels, physical coordinates, and social profiles</p>
          </div>
          <span class="text-xs font-semibold px-2.5 py-1 bg-blue-50 text-blue-800 rounded-full border border-blue-200">Customer Reach</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-[#4A3B32] mb-1">Phone Number</label>
            <input v-model="form.business_phone" type="text" placeholder="+251 911 123 456" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]" />
          </div>
          <div>
            <label class="block text-xs font-bold text-[#4A3B32] mb-1">Email Address</label>
            <input v-model="form.business_email" type="email" placeholder="info@sweetbakes.com" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]" />
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-[#4A3B32] mb-1">Working Hours (English)</label>
            <input v-model="form.business_hours_en" type="text" placeholder="Mon – Sun: 8:00 AM – 9:00 PM" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]" />
          </div>
          <div>
            <label class="block text-xs font-bold text-[#4A3B32] mb-1">Working Hours (Amharic)</label>
            <input v-model="form.business_hours_am" type="text" placeholder="ከሰኞ – እሑድ፡ ከጠዋቱ 2:00 – ማታ 3:00" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]" />
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-[#4A3B32] mb-1">Address (English)</label>
            <input v-model="form.business_address_en" type="text" placeholder="Bole Medhanialem..." class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]" />
          </div>
          <div>
            <label class="block text-xs font-bold text-[#4A3B32] mb-1">Address (Amharic)</label>
            <input v-model="form.business_address_am" type="text" placeholder="ቦሌ መድኃኔዓለም..." class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]" />
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-[#4A3B32] mb-1">GPS Latitude</label>
            <input v-model="form.business_lat" type="text" placeholder="9.0016" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]" />
          </div>
          <div>
            <label class="block text-xs font-bold text-[#4A3B32] mb-1">GPS Longitude</label>
            <input v-model="form.business_lng" type="text" placeholder="38.7845" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]" />
          </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2">
          <div>
            <label class="block text-[11px] text-[#7D6E64] mb-1">Facebook URL</label>
            <input v-model="form.social_facebook" type="text" placeholder="https://facebook.com/..." class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-2.5 py-1.5 text-xs text-[#2D231D]" />
          </div>
          <div>
            <label class="block text-[11px] text-[#7D6E64] mb-1">Instagram URL</label>
            <input v-model="form.social_instagram" type="text" placeholder="https://instagram.com/..." class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-2.5 py-1.5 text-xs text-[#2D231D]" />
          </div>
          <div>
            <label class="block text-[11px] text-[#7D6E64] mb-1">Telegram URL</label>
            <input v-model="form.social_telegram" type="text" placeholder="https://t.me/..." class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-2.5 py-1.5 text-xs text-[#2D231D]" />
          </div>
          <div>
            <label class="block text-[11px] text-[#7D6E64] mb-1">TikTok URL</label>
            <input v-model="form.social_tiktok" type="text" placeholder="https://tiktok.com/@..." class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-2.5 py-1.5 text-xs text-[#2D231D]" />
          </div>
        </div>
      </div>

      <!-- Bottom Quick Save Bar for Long Business & About Form -->
      <div class="flex items-center justify-end space-x-3 pt-1">
        <button
          type="button"
          @click="loadSettings"
          class="dash-btn-secondary text-xs sm:text-sm py-2 px-4"
        >
          Cancel
        </button>
        <button
          type="button"
          @click="saveSettings"
          :disabled="saving"
          class="dash-btn-primary text-xs sm:text-sm py-2 px-5 disabled:opacity-50"
        >
          {{ saving ? 'Saving...' : 'Save Settings' }}
        </button>
      </div>
    </div>

    <!-- TAB 3: Homepage Banners -->
    <div v-else-if="activeTab === 'banners'" class="space-y-5">
      <div class="flex items-center justify-between">
        <h3 class="font-serif font-bold text-base sm:text-lg text-[#2A1D15]">Active Hero Banners</h3>
        <button
          @click="openAddBannerModal"
          class="px-4 py-2 text-xs font-semibold bg-[#BA5420] hover:bg-[#9E4314] text-white rounded-lg shadow-sm transition flex items-center space-x-1.5 cursor-pointer font-poppins"
        >
          <span>+ Add Banner</span>
        </button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div
          v-for="b in banners"
          :key="b.banner_id"
          class="bg-white border border-[#E9E0D4] rounded-xl shadow-sm overflow-hidden flex flex-col justify-between"
        >
          <div class="aspect-16/9 relative overflow-hidden bg-gray-100">
            <img :src="b.image_url" :alt="b.title_en" class="w-full h-full object-cover" />
            <span class="absolute top-2 right-2 text-[10px] font-bold px-2 py-0.5 rounded-full" :class="b.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600'">
              {{ b.is_active ? 'Active' : 'Hidden' }}
            </span>
          </div>
          <div class="p-4 space-y-2 flex-1">
            <div class="text-[10px] uppercase tracking-wider text-[#BA5420] font-bold">{{ b.badge_en }}</div>
            <h4 class="font-serif font-bold text-sm text-[#2A1D15] line-clamp-1">{{ b.title_en }}</h4>
            <p class="text-xs text-[#7D6E64] line-clamp-2">{{ b.description_en }}</p>
          </div>
          <div class="p-4 pt-2 border-t border-[#EFE8DF] flex items-center justify-between">
            <span class="text-[11px] text-[#8A7B71]">Order: {{ b.sort_order }}</span>
            <div class="flex items-center space-x-3">
              <button
                @click="openEditBannerModal(b)"
                class="text-xs font-semibold text-[#BA5420] hover:text-[#9E4314] transition cursor-pointer"
              >
                Edit
              </button>
              <button
                @click="deleteBanner(b)"
                class="text-xs font-semibold text-red-600 hover:text-red-800 transition cursor-pointer"
              >
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- TAB 4: Customer Reviews Moderation -->
    <div v-else-if="activeTab === 'reviews'" class="space-y-4">
      <div class="flex items-center justify-between">
        <h3 class="font-serif font-bold text-base sm:text-lg text-[#2A1D15]">Customer Reviews</h3>
        <span class="text-xs text-[#8A7B71] font-poppins">{{ reviewsList.length }} total reviews</span>
      </div>

      <div class="bg-white border border-[#E9E0D4] rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs min-w-[560px]">
            <thead class="bg-[#FAF6F0] text-[#7A6B60] uppercase font-semibold border-b border-[#EFE8DF]">
              <tr>
                <th class="py-3 px-4">Author</th>
                <th class="py-3 px-3">Product</th>
                <th class="py-3 px-3">Rating</th>
                <th class="py-3 px-4">Comment</th>
                <th class="py-3 px-3 text-right">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#F3EDE4]">
              <tr v-for="rev in reviewsList" :key="rev.review_id" class="hover:bg-[#FCFAF7]">
                <td class="py-3 px-4 font-bold text-[#2A1D15]">{{ rev.user?.full_name || 'Customer' }}</td>
                <td class="py-3 px-3 text-[#BA5420] font-product font-bold text-xs sm:text-sm">{{ rev.product?.product_name || 'Product #' + rev.product_id }}</td>
                <td class="py-3 px-3 text-amber-500 font-bold">
                  <span v-for="s in 5" :key="s">{{ s <= rev.rating ? '★' : '☆' }}</span>
                </td>
                <td class="py-3 px-4 text-[#6A5A50] max-w-xs truncate">{{ rev.comment }}</td>
                <td class="py-3 px-3 text-right">
                  <button
                    @click="deleteReview(rev)"
                    class="text-red-600 hover:text-red-800 font-semibold text-xs transition cursor-pointer"
                  >
                    Delete
                  </button>
                </td>
              </tr>
              <tr v-if="reviewsList.length === 0">
                <td colspan="5" class="py-8 text-center text-[#8A7B71] italic">No customer reviews yet.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- TAB 5: Contact Messages -->
    <div v-else-if="activeTab === 'inquiries'" class="space-y-4">
      <div class="flex items-center justify-between">
        <h3 class="font-serif font-bold text-base sm:text-lg text-[#2A1D15]">Incoming Contact Messages</h3>
        <span class="text-xs text-[#8A7B71] font-poppins">{{ inquiriesList.length }} messages</span>
      </div>

      <div class="bg-white border border-[#E9E0D4] rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs min-w-[580px]">
            <thead class="bg-[#FAF6F0] text-[#7A6B60] uppercase font-semibold border-b border-[#EFE8DF]">
              <tr>
                <th class="py-3 px-4">Name</th>
                <th class="py-3 px-3">Contact</th>
                <th class="py-3 px-3">Subject</th>
                <th class="py-3 px-4">Message</th>
                <th class="py-3 px-3">Date</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#F3EDE4]">
              <tr v-for="inq in inquiriesList" :key="inq.inquiry_id" class="hover:bg-[#FCFAF7]">
                <td class="py-3 px-4 font-bold text-[#2A1D15]">{{ inq.name }}</td>
                <td class="py-3 px-3 text-[#6A5A50]">
                  <div>{{ inq.email }}</div>
                  <div v-if="inq.phone" class="text-[11px] text-[#8A7B71]">{{ inq.phone }}</div>
                </td>
                <td class="py-3 px-3 font-semibold text-[#BA5420]">{{ inq.subject }}</td>
                <td class="py-3 px-4 text-[#4A3B32] max-w-sm">{{ inq.message }}</td>
                <td class="py-3 px-3 text-[#8A7B71]">{{ new Date(inq.created_at).toLocaleDateString() }}</td>
              </tr>
              <tr v-if="inquiriesList.length === 0">
                <td colspan="5" class="py-8 text-center text-[#8A7B71] italic">No contact inquiries received yet.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- TAB 6: Newsletter Subscribers -->
    <div v-else-if="activeTab === 'subscribers'" class="space-y-4">
      <div class="flex items-center justify-between">
        <h3 class="font-serif font-bold text-base sm:text-lg text-[#2A1D15]">Newsletter Subscribers</h3>
        <span class="text-xs text-[#8A7B71] font-poppins">{{ subscribersList.length }} subscribers</span>
      </div>

      <div class="bg-white border border-[#E9E0D4] rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs min-w-[480px]">
            <thead class="bg-[#FAF6F0] text-[#7A6B60] uppercase font-semibold border-b border-[#EFE8DF]">
              <tr>
                <th class="py-3 px-4">ID</th>
                <th class="py-3 px-4">Subscriber Email</th>
                <th class="py-3 px-3">Status</th>
                <th class="py-3 px-3">Subscribed On</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#F3EDE4]">
              <tr v-for="sub in subscribersList" :key="sub.subscription_id" class="hover:bg-[#FCFAF7]">
                <td class="py-3 px-4 font-mono text-[#8A7B71]">#{{ sub.subscription_id }}</td>
                <td class="py-3 px-4 font-bold text-[#2A1D15]">{{ sub.email }}</td>
                <td class="py-3 px-3">
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                    {{ sub.status }}
                  </span>
                </td>
                <td class="py-3 px-3 text-[#8A7B71]">{{ new Date(sub.created_at).toLocaleDateString() }}</td>
              </tr>
              <tr v-if="subscribersList.length === 0">
                <td colspan="4" class="py-8 text-center text-[#8A7B71] italic">No newsletter subscribers yet.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- MODAL: Add Banner Modal -->
    <div v-if="isAddBannerModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
      <div class="bg-white rounded-2xl shadow-2xl border border-[#E9E0D4] max-w-xl w-full p-6 space-y-4 animate-in fade-in max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-[#EFE8DF] pb-3">
          <h3 class="font-serif font-bold text-lg text-[#2A1D15]">Add Hero Banner</h3>
          <button @click="isAddBannerModalOpen = false" class="text-xl font-bold text-[#7D6E64] cursor-pointer">&times;</button>
        </div>

        <form @submit.prevent="submitBanner" class="space-y-3">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Title (English) *</label>
              <input v-model="newBanner.title_en" type="text" required class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-1.5 text-xs text-[#2D231D]" />
            </div>
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Title (Amharic)</label>
              <input v-model="newBanner.title_am" type="text" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-1.5 text-xs text-[#2D231D]" />
            </div>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Subtitle / Badge (EN)</label>
              <input v-model="newBanner.subtitle_en" type="text" placeholder="e.g. Artisanal Cakes" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-1.5 text-xs text-[#2D231D]" />
            </div>
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Subtitle / Badge (AM)</label>
              <input v-model="newBanner.subtitle_am" type="text" placeholder="e.g. ልዩ የክብረ በዓል ኬኮች" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-1.5 text-xs text-[#2D231D]" />
            </div>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Badge Text (EN)</label>
              <input v-model="newBanner.badge_en" type="text" placeholder="e.g. Addis Ababa's Favorite" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-1.5 text-xs text-[#2D231D]" />
            </div>
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Badge Text (AM)</label>
              <input v-model="newBanner.badge_am" type="text" placeholder="e.g. የአዲስ አበባ ተመራጭ" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-1.5 text-xs text-[#2D231D]" />
            </div>
          </div>
          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Description (English)</label>
            <textarea v-model="newBanner.description_en" rows="2" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-1.5 text-xs text-[#2D231D]"></textarea>
          </div>
          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Description (Amharic)</label>
            <textarea v-model="newBanner.description_am" rows="2" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-1.5 text-xs text-[#2D231D]"></textarea>
          </div>
          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Banner Image (URL or Upload) *</label>
            <div class="flex flex-wrap xs:flex-nowrap items-center gap-2">
              <img v-if="newBanner.image_url" :src="newBanner.image_url" alt="Banner Preview" class="w-12 h-9 rounded-lg object-cover border border-[#D8CDC0] shrink-0" />
              <input v-model="newBanner.image_url" type="text" required placeholder="Image URL or upload file" class="flex-1 min-w-[140px] bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-1.5 text-xs text-[#2D231D]" />
              <label
                class="px-3 py-1.5 min-h-[34px] inline-flex items-center justify-center text-xs font-semibold bg-white border border-[#D8CDC0] hover:bg-[#F4ECE3] text-[#4A3B32] rounded-lg shadow-2xs transition cursor-pointer shrink-0 whitespace-nowrap"
                :class="{ 'opacity-60 pointer-events-none': uploadingSlot === 'banner-new' }"
              >
                <span>{{ uploadingSlot === 'banner-new' ? '⏳ Uploading...' : '📁 Upload' }}</span>
                <input type="file" accept="image/jpeg,image/png,image/webp,image/gif,image/bmp,image/jfif,image/avif,image/*" :disabled="uploadingSlot === 'banner-new'" class="hidden" @change="e => uploadSettingImage(e, newBanner, 'image_url', 'banner-new')" />
              </label>
            </div>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Button Text (EN)</label>
              <input v-model="newBanner.button_text_en" type="text" placeholder="Order Online" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-1.5 text-xs text-[#2D231D]" />
            </div>
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Target Link</label>
              <input v-model="newBanner.link_url" type="text" placeholder="/shop" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-1.5 text-xs text-[#2D231D]" />
            </div>
          </div>

          <div class="flex justify-end space-x-2 pt-3 border-t border-[#EFE8DF]">
            <button type="button" @click="isAddBannerModalOpen = false" class="px-4 py-1.5 text-xs font-semibold text-[#7D6E64] cursor-pointer">Cancel</button>
            <button type="submit" class="px-4 py-1.5 text-xs font-semibold bg-[#BA5420] hover:bg-[#9E4314] text-white rounded-lg shadow-sm cursor-pointer">Create Banner</button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL: Edit Banner Modal -->
    <div v-if="isEditBannerModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
      <div class="bg-white rounded-2xl shadow-2xl border border-[#E9E0D4] max-w-xl w-full p-6 space-y-4 animate-in fade-in max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-[#EFE8DF] pb-3">
          <h3 class="font-serif font-bold text-lg text-[#2A1D15]">Edit Hero Banner #{{ editingBanner?.banner_id }}</h3>
          <button @click="isEditBannerModalOpen = false" class="text-xl font-bold text-[#7D6E64] cursor-pointer">&times;</button>
        </div>

        <form @submit.prevent="updateBannerSubmit" class="space-y-3">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Title (English) *</label>
              <input v-model="editingBanner.title_en" type="text" required class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-1.5 text-xs text-[#2D231D]" />
            </div>
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Title (Amharic)</label>
              <input v-model="editingBanner.title_am" type="text" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-1.5 text-xs text-[#2D231D]" />
            </div>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Subtitle / Badge (EN)</label>
              <input v-model="editingBanner.subtitle_en" type="text" placeholder="e.g. Artisanal Cakes" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-1.5 text-xs text-[#2D231D]" />
            </div>
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Badge Text (EN)</label>
              <input v-model="editingBanner.badge_en" type="text" placeholder="e.g. Addis Ababa's Favorite" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-1.5 text-xs text-[#2D231D]" />
            </div>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Subtitle / Badge (AM)</label>
              <input v-model="editingBanner.subtitle_am" type="text" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-1.5 text-xs text-[#2D231D]" />
            </div>
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Badge Text (AM)</label>
              <input v-model="editingBanner.badge_am" type="text" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-1.5 text-xs text-[#2D231D]" />
            </div>
          </div>
          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Description (English)</label>
            <textarea v-model="editingBanner.description_en" rows="2" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-1.5 text-xs text-[#2D231D]"></textarea>
          </div>
          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Description (Amharic)</label>
            <textarea v-model="editingBanner.description_am" rows="2" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-1.5 text-xs text-[#2D231D]"></textarea>
          </div>
          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Banner Image (URL or Upload) *</label>
            <div class="flex flex-wrap xs:flex-nowrap items-center gap-2">
              <img v-if="editingBanner.image_url" :src="editingBanner.image_url" alt="Banner Preview" class="w-12 h-9 rounded-lg object-cover border border-[#D8CDC0] shrink-0" />
              <input v-model="editingBanner.image_url" type="text" required placeholder="Image URL or upload file" class="flex-1 min-w-[140px] bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-1.5 text-xs text-[#2D231D]" />
              <label
                class="px-3 py-1.5 min-h-[34px] inline-flex items-center justify-center text-xs font-semibold bg-white border border-[#D8CDC0] hover:bg-[#F4ECE3] text-[#4A3B32] rounded-lg shadow-2xs transition cursor-pointer shrink-0 whitespace-nowrap"
                :class="{ 'opacity-60 pointer-events-none': uploadingSlot === 'banner-edit' }"
              >
                <span>{{ uploadingSlot === 'banner-edit' ? '⏳ Uploading...' : '📁 Upload' }}</span>
                <input type="file" accept="image/jpeg,image/png,image/webp,image/gif,image/bmp,image/jfif,image/avif,image/*" :disabled="uploadingSlot === 'banner-edit'" class="hidden" @change="e => uploadSettingImage(e, editingBanner, 'image_url', 'banner-edit')" />
              </label>
            </div>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Button Text (EN)</label>
              <input v-model="editingBanner.button_text_en" type="text" placeholder="Order Online" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-1.5 text-xs text-[#2D231D]" />
            </div>
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Target Link</label>
              <input v-model="editingBanner.link_url" type="text" placeholder="/shop" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-1.5 text-xs text-[#2D231D]" />
            </div>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Sort Order</label>
              <input v-model.number="editingBanner.sort_order" type="number" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-1.5 text-xs text-[#2D231D]" />
            </div>
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Status</label>
              <select v-model="editingBanner.is_active" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-1.5 text-xs text-[#2D231D]">
                <option :value="true">Active (Visible)</option>
                <option :value="false">Hidden</option>
              </select>
            </div>
          </div>

          <div class="flex justify-end space-x-2 pt-3 border-t border-[#EFE8DF]">
            <button type="button" @click="isEditBannerModalOpen = false" class="px-4 py-1.5 text-xs font-semibold text-[#7D6E64] cursor-pointer">Cancel</button>
            <button type="submit" class="px-4 py-1.5 text-xs font-semibold bg-[#BA5420] hover:bg-[#9E4314] text-white rounded-lg shadow-sm cursor-pointer">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useDialogStore } from '../../stores/dialog';
import { useToastStore } from '../../stores/toast';
import api from '../../api';

const dialog = useDialogStore();
const toast = useToastStore();

const activeTab = ref('general');
const saving = ref(false);
const uploadingSlot = ref(null);
const successMsg = ref('');
const errorMsg = ref('');

const form = ref({
  // System & Orders
  unpaid_reservation_minutes: 15,
  default_reorder_level: 10,
  pay_on_pickup_delivery: 'Enabled',
  cancellation_allowed_until: 'Preparing starts',
  approval_threshold_units: 200,
  unreceived_reminder_hours: 24,
  default_deposit_percent: '50',
  deposit_refundable: 'Before branch starts work',
  gateways: 'Chapa, Telebirr',
  webhook_secret: 'whsec_live_99a8b7c6d5e4f3a2b1',
  languages: 'English, Amharic',
  currency: 'ETB',
  admin_2fa: 'Optional',
  backups: 'Daily, kept 30 days',

  // Cafe / Bakery Identity
  site_name: 'SweetBakes Bakery',
  site_tagline: 'Artisanal Bakery & Celebration Cakes',
  site_description_en: 'Handcrafted celebration cakes and artisanal breads baked fresh daily with love in Addis Ababa.',
  site_description_am: 'በፍቅር እና በጥንቃቄ የተዘጋጁ ልዩ የክብረ በዓል ኬኮች እና ትኩስ የተፈጥሮ ዳቦዎች በአዲስ አበባ።',
  since_year: '2015',

  // Contact & Location
  business_phone: '+251 911 123 456',
  business_email: 'info@sweetbakes.com',
  business_address_en: 'Bole Medhanialem, Next to Edna Mall, Addis Ababa, Ethiopia',
  business_address_am: 'ቦሌ መድኃኔዓለም፣ ኤድና ሞል አጠገብ፣ አዲስ አበባ፣ ኢትዮጵያ',
  business_hours_en: 'Mon – Sun: 8:00 AM – 9:00 PM',
  business_hours_am: 'ከሰኞ – እሑድ፡ ከጠዋቱ 2:00 – ማታ 3:00',
  business_lat: '9.0016',
  business_lng: '38.7845',

  // Homepage Hero Customization
  hero_subtitle_en: 'Artisanal Cakes & Breads',
  hero_subtitle_am: 'ልዩ የክብረ በዓል ኬኮች እና ዳቦ',
  hero_title_en: 'Baked Fresh with Love',
  hero_title_am: 'በፍቅር የተዘጋጀ ትኩስ ጣዕም',
  hero_title2_en: "Addis Ababa's Favorite",
  hero_title2_am: 'የአዲስ አበባ ተመራጭ',
  hero_desc_en: 'Handcrafted celebration cakes and warm sourdough delivered fresh to your doorstep.',
  hero_desc_am: 'የልደትና የሰርግ ኬኮች እንዲሁም ትኩስ የተፈጥሮ ዳቦዎች በሁሉም የአዲስ አበባ ክፍሎች ባሉበት ይደርሳሉ።',
  hero_button_text_en: 'Order Online',
  hero_button_text_am: 'አሁን ይዘዙ',
  hero_button_link: '/shop',
  hero_image_url: '/mockup/hero_banner_hq.jpg',

  // About Page & Section Customization
  about_tag_en: 'About SweetBakes',
  about_tag_am: 'ስለ ስዊትቤክስ',
  about_title_en: 'A Bakery You Can Trust',
  about_title_am: 'የሚተማመኑበት የዳቦና ኬክ መጋገሪያ',
  about_story_en: 'At SweetBakes, we believe every celebration starts with the aroma of freshly baked butter, warm vanilla, and handcrafted pastries.',
  about_story_am: 'በስዊትቤክስ እያንዳንዱ በዓል የሚጀምረው አዲስ በተጋገረ ቅቤ፣ ሞቅ ባለ ቫኒላ እና በእጅ በተዘጋጁ ኬኮች መዓዛ ነው።',
  about_image_url: '/mockup/baker_hires.jpg',
  about_chef_quote_en: '“Making life sweeter, one cake and bread at a time.”',
  about_chef_quote_am: '“እያንዳንዱን ኬክ እና ዳቦ በፍቅር እና በጥንቃቄ እያዘጋጀን ህይወትዎን እናጣፍጣለን::”',
  about_chef_title_en: '— SweetBakes Master Pastry Chef',
  about_chef_title_am: '— የስዊትቤክስ ዋና ኬክ ሼፍ',
  about_bullet_1_en: '100% Artisanal & Fresh Daily',
  about_bullet_1_am: '100% በእጅ የተዘጋጀና ትኩስ',
  about_bullet_2_en: 'Finest Natural Ingredients',
  about_bullet_2_am: 'ከምርጥ የተፈጥሮ ግብአቶች የተዘጋጀ',
  about_bullet_3_en: 'Custom Design Celebration Cakes',
  about_bullet_3_am: 'ልዩ የክብረ በዓል ኬኮች ዲዛይን',
  about_bullet_4_en: 'Multi-Branch Addis Delivery',
  about_bullet_4_am: 'በሁሉም ቅርንጫፎች ፈጣን ማድረሻ',

  // Key Statistics
  stat_experience_years: '12+',
  stat_happy_customers: '50k+',
  stat_daily_fresh: '100%',
  stat_master_chefs: '15+',

  // Social Links
  social_facebook: 'https://facebook.com/sweetbakes',
  social_instagram: 'https://instagram.com/sweetbakes',
  social_telegram: 'https://t.me/sweetbakes',
  social_tiktok: 'https://tiktok.com/@sweetbakes',
});

// Banners management
const banners = ref([]);
const isAddBannerModalOpen = ref(false);
const isEditBannerModalOpen = ref(false);
const editingBanner = ref(null);

const newBanner = ref({
  title_en: '',
  title_am: '',
  subtitle_en: '',
  subtitle_am: '',
  badge_en: 'Fresh Daily',
  badge_am: 'በየቀኑ ትኩስ',
  description_en: '',
  description_am: '',
  image_url: '/mockup/hero_banner_hq.jpg',
  button_text_en: 'Order Online',
  button_text_am: 'አሁን ይዘዙ',
  link_url: '/shop',
  is_active: true,
  sort_order: 1,
});

const loadBanners = async () => {
  try {
    const res = await api.get('/admin/banners');
    banners.value = res.data;
  } catch (err) {
    console.error('Failed to load banners', err);
  }
};

const openAddBannerModal = () => {
  newBanner.value = {
    title_en: '',
    title_am: '',
    subtitle_en: '',
    subtitle_am: '',
    badge_en: 'Fresh Daily',
    badge_am: 'በየቀኑ ትኩስ',
    description_en: '',
    description_am: '',
    image_url: '/mockup/hero_banner_hq.jpg',
    button_text_en: 'Order Online',
    button_text_am: 'አሁን ይዘዙ',
    link_url: '/shop',
    is_active: true,
    sort_order: banners.value.length + 1,
  };
  isAddBannerModalOpen.value = true;
};

const submitBanner = async () => {
  try {
    await api.post('/admin/banners', newBanner.value);
    successMsg.value = 'Banner created successfully!';
    isAddBannerModalOpen.value = false;
    await loadBanners();
  } catch (err) {
    errorMsg.value = err.response?.data?.message || 'Failed to create banner';
  }
};

const openEditBannerModal = (b) => {
  editingBanner.value = { ...b, is_active: Boolean(b.is_active) };
  isEditBannerModalOpen.value = true;
};

const updateBannerSubmit = async () => {
  if (!editingBanner.value) return;
  try {
    await api.patch(`/admin/banners/${editingBanner.value.banner_id}`, editingBanner.value);
    successMsg.value = 'Banner updated successfully!';
    isEditBannerModalOpen.value = false;
    await loadBanners();
  } catch (err) {
    errorMsg.value = err.response?.data?.message || 'Failed to update banner';
  }
};

const deleteBanner = async (b) => {
  const confirmed = await dialog.confirm({
    title: 'Delete Banner',
    message: `Are you sure you want to delete banner "${b.title_en}"?`,
    confirmText: 'Delete Banner',
    variant: 'danger',
  });
  if (!confirmed) return;
  try {
    await api.delete(`/admin/banners/${b.banner_id}`);
    const msg = 'Banner deleted.';
    successMsg.value = msg;
    toast.success(msg);
    await loadBanners();
  } catch (err) {
    const msg = err.response?.data?.message || 'Failed to delete banner';
    errorMsg.value = msg;
    toast.error(msg);
  }
};

// Reviews management
const reviewsList = ref([]);
const loadReviews = async () => {
  try {
    const res = await api.get('/admin/reviews');
    reviewsList.value = res.data?.data || res.data || [];
  } catch (err) {
    console.error('Failed to load reviews', err);
  }
};

const deleteReview = async (rev) => {
  const confirmed = await dialog.confirm({
    title: 'Delete Customer Review',
    message: 'Are you sure you want to delete this customer review? This cannot be undone.',
    confirmText: 'Delete Review',
    variant: 'danger',
  });
  if (!confirmed) return;
  try {
    await api.delete(`/admin/reviews/${rev.review_id}`);
    const msg = 'Review deleted successfully.';
    successMsg.value = msg;
    toast.success(msg);
    await loadReviews();
  } catch (err) {
    const msg = err.response?.data?.message || 'Failed to delete review';
    errorMsg.value = msg;
    toast.error(msg);
  }
};

// Contact inquiries management
const inquiriesList = ref([]);
const loadInquiries = async () => {
  try {
    const res = await api.get('/admin/contact-inquiries');
    inquiriesList.value = res.data?.data || res.data || [];
  } catch (err) {
    console.error('Failed to load inquiries', err);
  }
};

// Newsletter subscribers management
const subscribersList = ref([]);
const loadSubscribers = async () => {
  try {
    const res = await api.get('/admin/newsletter-subscribers');
    subscribersList.value = res.data?.data || res.data || [];
  } catch (err) {
    console.error('Failed to load subscribers', err);
  }
};

const optimizeImageBeforeUpload = (file, maxDimension = 1920, quality = 0.90) => {
  return new Promise((resolve) => {
    // Preserve animated gifs or svg vector graphics
    if (file.type === 'image/gif' || file.type === 'image/svg+xml') {
      return resolve(file);
    }
    const reader = new FileReader();
    reader.onload = (e) => {
      const img = new Image();
      img.onload = () => {
        let width = img.width;
        let height = img.height;
        if (width > maxDimension || height > maxDimension) {
          if (width > height) {
            height = Math.round((height * maxDimension) / width);
            width = maxDimension;
          } else {
            width = Math.round((width * maxDimension) / height);
            height = maxDimension;
          }
        }
        const canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0, width, height);

        canvas.toBlob(
          (blob) => {
            if (blob && (blob.size < file.size || !file.type || file.type.includes('octet-stream') || file.name?.toLowerCase().endsWith('.jfif'))) {
              const safeName = (file.name || 'photo').replace(/\.[^/.]+$/, '') + '.jpg';
              resolve(new File([blob], safeName, { type: 'image/jpeg' }));
            } else {
              resolve(file);
            }
          },
          'image/jpeg',
          quality
        );
      };
      img.onerror = () => resolve(file);
      img.src = e.target.result;
    };
    reader.onerror = () => resolve(file);
    reader.readAsDataURL(file);
  });
};

const uploadSettingImage = async (event, targetObj, fieldKey, slot, persistSettingKey = null) => {
  const originalFile = event?.target?.files?.[0];
  if (!originalFile) return;

  const fileName = (originalFile.name || '').toLowerCase();
  const validExtensions = ['.jpg', '.jpeg', '.png', '.webp', '.gif', '.bmp', '.jfif', '.avif', '.svg'];
  const hasValidExt = validExtensions.some(ext => fileName.endsWith(ext));
  const isImageMime = originalFile.type ? originalFile.type.startsWith('image/') : false;

  if (!isImageMime && !hasValidExt) {
    const msg = 'Please select a valid image file (JPG, PNG, WebP, GIF, or BMP).';
    errorMsg.value = msg;
    toast.error(msg);
    event.target.value = '';
    return;
  }

  uploadingSlot.value = slot;
  errorMsg.value = '';

  try {
    // Automatically optimize large photos from phone or desktop before upload
    const file = await optimizeImageBeforeUpload(originalFile);

    const formData = new FormData();
    formData.append('image', file);
    formData.append('slot', slot.replace(/-.+$/, ''));
    if (persistSettingKey) {
      formData.append('setting_key', persistSettingKey);
    }

    const res = await api.post('/admin/settings/upload-image', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    const target = targetObj?.value ?? targetObj;
    if (target && res.data?.image_url) {
      target[fieldKey] = res.data.image_url;
    }
    const msg = persistSettingKey
      ? 'Photo uploaded and updated on the live website!'
      : 'Banner photo uploaded successfully!';
    successMsg.value = msg;
    toast.success(msg);
    setTimeout(() => {
      if (successMsg.value === msg) successMsg.value = '';
    }, 3500);
  } catch (err) {
    console.error('Settings photo upload error:', err);
    const serverErrors = err.response?.data?.errors;
    const firstFieldErr = serverErrors ? Object.values(serverErrors)[0]?.[0] : null;
    const msg = firstFieldErr || err.response?.data?.message || 'Failed to upload photo. Please try a different image.';
    errorMsg.value = msg;
    toast.error(msg);
  } finally {
    uploadingSlot.value = null;
    if (event?.target) {
      event.target.value = '';
    }
  }
};

const loadSettings = async () => {
  try {
    const res = await api.get('/admin/settings');
    Object.assign(form.value, res.data);
  } catch (err) {
    console.error('Failed to load settings', err);
  }
};

const saveSettings = async () => {
  saving.value = true;
  successMsg.value = '';
  errorMsg.value = '';
  try {
    await api.post('/admin/settings', form.value);
    const msg = 'Settings saved successfully! Homepage and About page have been updated.';
    successMsg.value = msg;
    toast.success(msg);
    setTimeout(() => { successMsg.value = ''; }, 3500);
  } catch (err) {
    const msg = err.response?.data?.message || 'Failed to save settings';
    errorMsg.value = msg;
    toast.error(msg);
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  loadSettings();
});
</script>
