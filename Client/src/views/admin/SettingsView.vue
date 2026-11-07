<template>
  <div class="space-y-6 max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-xl sm:text-2xl font-serif font-bold text-[#2A1D15]">Settings & Content Management</h2>
        <p class="text-xs text-[#8A7B71]">Configure system rules, business details, homepage banners, and customer feedback</p>
      </div>

      <div class="flex items-center space-x-3 w-full sm:w-auto justify-end">
        <button
          v-if="activeTab === 'general' || activeTab === 'business'"
          @click="loadSettings"
          class="flex-1 sm:flex-none px-4 py-2 text-xs sm:text-sm font-medium bg-white border border-[#D8CDC0] hover:bg-[#F4ECE3] text-[#4A3B32] rounded-lg transition shadow-xs cursor-pointer"
        >
          Cancel
        </button>
        <button
          v-if="activeTab === 'general' || activeTab === 'business'"
          @click="saveSettings"
          :disabled="saving"
          class="flex-1 sm:flex-none px-5 py-2 text-xs sm:text-sm font-medium bg-[#BA5420] hover:bg-[#9E4314] text-white rounded-lg transition shadow-xs disabled:opacity-50 cursor-pointer"
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
          <h3 class="font-semibold text-base text-[#2A1D15]">Orders and stock</h3>

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
          <h3 class="font-semibold text-base text-[#2A1D15]">Transfers</h3>

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
          <h3 class="font-semibold text-base text-[#2A1D15]">Custom cakes</h3>

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
          <h3 class="font-semibold text-base text-[#2A1D15]">Payments</h3>

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
          <h3 class="font-semibold text-base text-[#2A1D15]">Language and currency</h3>

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
          <h3 class="font-semibold text-base text-[#2A1D15]">Security &amp; Backups</h3>

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

    <!-- TAB 2: Business Info & Story -->
    <div v-else-if="activeTab === 'business'" class="bg-white border border-[#E9E0D4] rounded-xl shadow-sm p-6 space-y-6">
      <div class="border-b border-[#EFE8DF] pb-3">
        <h3 class="font-semibold text-base text-[#2A1D15]">Bakery Contact Details, Location &amp; Story</h3>
        <p class="text-xs text-[#8A7B71]">All customer-facing contact info, GPS coordinates, about story, and statistics</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Contact details -->
        <div class="space-y-4">
          <h4 class="font-bold text-xs uppercase tracking-wider text-[#BA5420]">Contact Information</h4>
          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Bakery Name</label>
            <input v-model="form.site_name" type="text" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]" />
          </div>
          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Phone Number</label>
            <input v-model="form.business_phone" type="text" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]" />
          </div>
          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Email Address</label>
            <input v-model="form.business_email" type="email" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]" />
          </div>
          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Address (English)</label>
            <input v-model="form.business_address_en" type="text" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]" />
          </div>
          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Address (Amharic)</label>
            <input v-model="form.business_address_am" type="text" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]" />
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">GPS Latitude</label>
              <input v-model="form.business_lat" type="text" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]" />
            </div>
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">GPS Longitude</label>
              <input v-model="form.business_lng" type="text" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]" />
            </div>
          </div>
        </div>

        <!-- Story & Stats -->
        <div class="space-y-4">
          <h4 class="font-bold text-xs uppercase tracking-wider text-[#BA5420]">Story &amp; Statistics</h4>
          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Working Hours (English)</label>
            <input v-model="form.business_hours_en" type="text" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]" />
          </div>
          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Working Hours (Amharic)</label>
            <input v-model="form.business_hours_am" type="text" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]" />
          </div>
          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">About Story (English)</label>
            <textarea v-model="form.about_story_en" rows="3" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"></textarea>
          </div>
          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">About Story (Amharic)</label>
            <textarea v-model="form.about_story_am" rows="3" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]"></textarea>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Experience Years Stat</label>
              <input v-model="form.stat_experience_years" type="text" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]" />
            </div>
            <div>
              <label class="block text-xs font-medium text-[#7D6E64] mb-1">Happy Customers Stat</label>
              <input v-model="form.stat_happy_customers" type="text" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-2 text-xs text-[#2D231D]" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- TAB 3: Homepage Banners -->
    <div v-else-if="activeTab === 'banners'" class="space-y-5">
      <div class="flex items-center justify-between">
        <h3 class="font-semibold text-base text-[#2A1D15]">Active Hero Banners</h3>
        <button
          @click="openAddBannerModal"
          class="px-4 py-2 text-xs font-semibold bg-[#BA5420] hover:bg-[#9E4314] text-white rounded-lg shadow-sm transition flex items-center space-x-1.5 cursor-pointer"
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

    <!-- TAB 4: Customer Reviews Moderation -->
    <div v-else-if="activeTab === 'reviews'" class="space-y-4">
      <div class="flex items-center justify-between">
        <h3 class="font-semibold text-base text-[#2A1D15]">Customer Reviews</h3>
        <span class="text-xs text-[#8A7B71]">{{ reviewsList.length }} total reviews</span>
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
                <td class="py-3 px-3 text-[#BA5420] font-semibold">{{ rev.product?.product_name || 'Product #' + rev.product_id }}</td>
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
        <h3 class="font-semibold text-base text-[#2A1D15]">Incoming Contact Messages</h3>
        <span class="text-xs text-[#8A7B71]">{{ inquiriesList.length }} messages</span>
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
        <h3 class="font-semibold text-base text-[#2A1D15]">Newsletter Subscribers</h3>
        <span class="text-xs text-[#8A7B71]">{{ subscribersList.length }} subscribers</span>
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
          <button @click="isAddBannerModalOpen = false" class="text-xl font-bold text-[#7D6E64]">&times;</button>
        </div>

        <form @submit.prevent="submitBanner" class="space-y-3">
          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Title (English) *</label>
            <input v-model="newBanner.title_en" type="text" required class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-1.5 text-xs text-[#2D231D]" />
          </div>
          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Title (Amharic)</label>
            <input v-model="newBanner.title_am" type="text" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-1.5 text-xs text-[#2D231D]" />
          </div>
          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Badge Text</label>
            <input v-model="newBanner.badge_en" type="text" placeholder="e.g. Fresh Daily" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-1.5 text-xs text-[#2D231D]" />
          </div>
          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Description</label>
            <textarea v-model="newBanner.description_en" rows="2" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-1.5 text-xs text-[#2D231D]"></textarea>
          </div>
          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Banner Image URL *</label>
            <input v-model="newBanner.image_url" type="text" required placeholder="/mockup/hero_cake.jpg" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-1.5 text-xs text-[#2D231D]" />
          </div>
          <div>
            <label class="block text-xs font-medium text-[#7D6E64] mb-1">Target Link</label>
            <input v-model="newBanner.link_url" type="text" placeholder="/shop" class="w-full bg-[#FDFBF7] border border-[#D8CDC0] rounded-lg px-3 py-1.5 text-xs text-[#2D231D]" />
          </div>

          <div class="flex justify-end space-x-2 pt-3 border-t border-[#EFE8DF]">
            <button type="button" @click="isAddBannerModalOpen = false" class="px-4 py-1.5 text-xs font-semibold text-[#7D6E64]">Cancel</button>
            <button type="submit" class="px-4 py-1.5 text-xs font-semibold bg-[#BA5420] hover:bg-[#9E4314] text-white rounded-lg shadow-sm">Create Banner</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../../api';

const activeTab = ref('general');
const saving = ref(false);
const successMsg = ref('');
const errorMsg = ref('');

const form = ref({
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
  site_name: 'SweetBakes Bakery',
  business_phone: '+251 911 123 456',
  business_email: 'info@sweetbakes.com',
  business_address_en: 'Bole Medhanialem, Next to Edna Mall, Addis Ababa, Ethiopia',
  business_address_am: 'ቦሌ መድኃኔዓለም፣ ኤድና ሞል አጠገብ፣ አዲስ አበባ፣ ኢትዮጵያ',
  business_hours_en: 'Mon – Sun: 8:00 AM – 9:00 PM',
  business_hours_am: 'ከሰኞ – እሑድ፡ ከጠዋቱ 2:00 – ማታ 3:00',
  business_lat: '9.0016',
  business_lng: '38.7845',
  about_story_en: 'At SweetBakes, we believe every celebration starts with the aroma of freshly baked butter, warm vanilla, and handcrafted pastries.',
  about_story_am: 'በስዊትቤክስ እያንዳንዱ በዓል የሚጀምረው አዲስ በተጋገረ ቅቤ፣ ሞቅ ባለ ቫኒላ እና በእጅ በተዘጋጁ ኬኮች መዓዛ ነው።',
  stat_experience_years: '12+',
  stat_happy_customers: '50k+',
});

// Banners management
const banners = ref([]);
const isAddBannerModalOpen = ref(false);
const newBanner = ref({
  title_en: '',
  title_am: '',
  badge_en: 'Fresh Daily',
  description_en: '',
  image_url: '/mockup/hero_cake.jpg',
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
    badge_en: 'Fresh Daily',
    description_en: '',
    image_url: '/mockup/hero_cake.jpg',
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

const deleteBanner = async (b) => {
  if (!confirm(`Delete banner "${b.title_en}"?`)) return;
  try {
    await api.delete(`/admin/banners/${b.banner_id}`);
    successMsg.value = 'Banner deleted.';
    await loadBanners();
  } catch (err) {
    errorMsg.value = err.response?.data?.message || 'Failed to delete banner';
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
  if (!confirm('Are you sure you want to delete this review?')) return;
  try {
    await api.delete(`/admin/reviews/${rev.review_id}`);
    successMsg.value = 'Review deleted successfully.';
    await loadReviews();
  } catch (err) {
    errorMsg.value = err.response?.data?.message || 'Failed to delete review';
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
    successMsg.value = 'Settings saved successfully.';
    setTimeout(() => { successMsg.value = ''; }, 3500);
  } catch (err) {
    errorMsg.value = err.response?.data?.message || 'Failed to save settings';
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  loadSettings();
});
</script>
