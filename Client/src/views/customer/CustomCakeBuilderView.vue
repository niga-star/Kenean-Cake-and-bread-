<template>
  <div class="bg-white dark:bg-[#170E0A] min-h-screen text-[#29231F] dark:text-[#FAF3EE] font-sans selection:bg-[#FCE7F0] selection:text-[#DE1B54] pb-12">
    <!-- 1. HERO BANNER: SweetBakes Style Matching Shop & Home Pages -->
    <section class="relative overflow-hidden bg-gradient-to-r from-[#FFF5F2] via-[#FDF3EE] to-[#FCEEE8] dark:from-[#22130C] dark:via-[#1E110A] dark:to-[#170E0A] min-h-[180px] sm:min-h-[220px] lg:min-h-[240px] flex items-center border-b border-[#F2E5DD] dark:border-[#382318]">
      <div class="absolute right-0 top-0 bottom-0 w-full lg:w-[55%] overflow-hidden pointer-events-none">
        <img
          src="/mockup/shop_banner_right.jpg"
          alt="SweetBakes Custom Cake Studio"
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
            <span>Custom Cake Studio</span>
            <span class="text-gray-300 dark:text-gray-600 select-none">&mdash;</span>
          </div>

          <h1 class="text-3xl sm:text-4xl lg:text-5xl font-serif font-black text-[#2A1810] dark:text-[#FAF3EE] tracking-tight leading-tight">
            Design Your Dream Cake
          </h1>

          <p class="text-xs sm:text-sm text-[#6E5D53] dark:text-[#C5B7AF] flex items-center justify-center lg:justify-start space-x-1.5">
            <span>Bespoke celebration centerpieces handcrafted with love!</span>
            <span class="text-[#DE1B54] text-base font-bold font-script">♡</span>
          </p>
        </div>
      </div>
    </section>

    <!-- 2. QUICK INSPIRATION PRESET CHIPS -->
    <div class="sweetbakes-container pt-8">
      <div class="bg-gradient-to-r from-rose-50/50 via-white to-rose-50/50 dark:from-[#25150E] dark:via-[#1E120B] dark:to-[#25150E] border border-rose-100 dark:border-[#382318] rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row items-center justify-between gap-3 shadow-xs">
        <div class="flex items-center space-x-2 text-xs text-[#2A1810] dark:text-[#FAF3EE] font-bold">
          <span class="text-rose-500 text-base">✨</span>
          <span>Quick Inspiration Presets:</span>
        </div>
        <div class="flex flex-wrap items-center justify-center gap-2">
          <button
            v-for="preset in inspirationPresets"
            :key="preset.name"
            type="button"
            @click="applyPreset(preset)"
            class="px-3.5 py-1.5 bg-white dark:bg-[#1E120B] hover:bg-[#DE1B54] text-[#2A1810] dark:text-[#FAF3EE] hover:text-white border border-gray-200 dark:border-[#382318] hover:border-[#DE1B54] rounded-full text-xs font-semibold transition-all shadow-2xs hover:shadow-xs active:scale-95 cursor-pointer flex items-center space-x-1.5"
          >
            <span>{{ preset.icon }}</span>
            <span>{{ preset.name }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- 3. TWO-COLUMN BUILDER FORM -->
    <div class="sweetbakes-container py-8">
      <form @submit.prevent="handleSubmitCustomCake" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- ================= LEFT COLUMN: CAKE ARCHITECTURE & FLAVORS (7 cols) ================= -->
        <div class="lg:col-span-7 bg-white dark:bg-[#1E120B] rounded-2xl border border-[#EFE8E1] dark:border-[#382318] p-6 sm:p-8 space-y-6 shadow-xs">
          <div class="flex items-center justify-between border-b border-gray-100 dark:border-[#382318] pb-3">
            <div class="flex items-center space-x-2">
              <span class="w-6 h-6 rounded-full bg-[#DE1B54] text-white flex items-center justify-center text-xs font-bold">1</span>
              <h2 class="text-lg font-serif font-bold text-[#2A1810] dark:text-[#FAF3EE]">
                Cake Architecture &amp; Flavors
              </h2>
            </div>
            <span class="text-xs text-[#DE1B54] font-bold">Step 1 of 2</span>
          </div>

          <!-- Preparing Branch Selector -->
          <div class="space-y-1.5 pb-2">
            <label class="block text-xs font-bold uppercase tracking-wider text-[#2A1810]">
              Preparing Branch (Addis Ababa Kitchen)
            </label>
            <select
              v-model="preparingBranchId"
              class="w-full bg-[#FAF7F5] border border-gray-200 focus:border-[#DE1B54] focus:bg-white rounded-xl px-4 py-2.5 text-xs text-[#2A1810] focus:outline-none transition font-semibold cursor-pointer"
            >
              <option
                v-for="b in branchStore.branches"
                :key="b.branch_id"
                :value="b.branch_id"
              >
                {{ b.branch_name }} — {{ b.address || b.city }}
              </option>
            </select>
            <p class="text-[11px] text-[#6E5D53]">
              The master cake artist at this specific branch will personally review your brief and bake your centerpiece.
            </p>
          </div>

          <!-- Size & Tier Selection Cards -->
          <div class="space-y-2.5">
            <label class="block text-xs font-bold uppercase tracking-wider text-[#2A1810]">
              Size &amp; Tier Configuration
            </label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div
                v-for="s in sizeOptions"
                :key="s.name"
                @click="cakeSize = s.name"
                class="p-4 rounded-xl border transition-all duration-200 cursor-pointer text-left flex items-start space-x-3 shadow-2xs hover:shadow-xs group"
                :class="cakeSize === s.name 
                  ? 'border-2 border-[#DE1B54] bg-rose-50/30 ring-2 ring-[#DE1B54]/20' 
                  : 'border-gray-200 bg-white hover:border-[#DE1B54]/40 hover:bg-[#FAF7F5]'"
              >
                <span class="text-2xl shrink-0 mt-0.5 group-hover:scale-110 transition-transform">{{ s.icon }}</span>
                <div>
                  <div class="text-xs font-bold text-[#2A1810] group-hover:text-[#DE1B54] transition-colors">
                    {{ s.name }}
                  </div>
                  <div class="text-[11px] text-gray-500">{{ s.serves }}</div>
                  <div class="text-[11px] text-[#DE1B54] font-black mt-0.5">Est: {{ s.baseEstimate }}</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Ye'Tsom (Ethiopian Fasting) Friendly Toggle -->
          <div class="p-3.5 rounded-xl border border-emerald-200 bg-emerald-50/70 flex items-center justify-between cursor-pointer" @click="isFasting = !isFasting">
            <div class="flex items-center space-x-2.5">
              <input
                type="checkbox"
                v-model="isFasting"
                class="w-4 h-4 text-emerald-600 rounded focus:ring-emerald-500 cursor-pointer"
              />
              <div>
                <div class="text-xs font-bold text-emerald-900 flex items-center space-x-1.5">
                  <span>🌿 100% Ye'Tsom Certified (የጾም)</span>
                  <span class="text-[9px] bg-emerald-100 text-emerald-800 font-semibold px-2 py-0.2 rounded-full border border-emerald-300">Vegan &amp; Dairy-Free</span>
                </div>
                <div class="text-[10px] text-emerald-700">Strictly prepared without dairy, butter, honey, or eggs.</div>
              </div>
            </div>
            <span class="text-xs font-bold text-emerald-800">{{ isFasting ? 'Active ✓' : 'Add' }}</span>
          </div>

          <!-- Sponge Flavor & Filling Selection -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="space-y-1.5">
              <label class="block text-xs font-bold uppercase tracking-wider text-[#2A1810]">Sponge Flavor</label>
              <select
                v-model="flavor"
                class="w-full bg-[#FAF7F5] border border-gray-200 focus:border-[#DE1B54] focus:bg-white rounded-xl px-3.5 py-2.5 text-xs text-[#2A1810] focus:outline-none transition cursor-pointer"
              >
                <option value="Belgian Dark Chocolate">Belgian Dark Chocolate 🍫</option>
                <option value="Madagascar Bourbon Vanilla">Madagascar Bourbon Vanilla 🍦</option>
                <option value="Classic Red Velvet">Classic Red Velvet 🍰</option>
                <option value="Ethiopian Mocha Espresso">Ethiopian Mocha Espresso ☕</option>
                <option value="Fresh Strawberry Chiffon">Fresh Strawberry Chiffon 🍓</option>
                <option value="Spiced Carrot & Walnut">Spiced Carrot &amp; Walnut 🥕</option>
                <option v-if="isFasting" value="Ye'Tsom Vegan Chocolate">Ye'Tsom Vegan Chocolate 🌿</option>
                <option v-if="isFasting" value="Ye'Tsom Vegan Vanilla Citrus">Ye'Tsom Vegan Vanilla Citrus 🌿</option>
              </select>
            </div>

            <div class="space-y-1.5">
              <label class="block text-xs font-bold uppercase tracking-wider text-[#2A1810]">Filling &amp; Frosting</label>
              <select
                v-model="filling"
                class="w-full bg-[#FAF7F5] border border-gray-200 focus:border-[#DE1B54] focus:bg-white rounded-xl px-3.5 py-2.5 text-xs text-[#2A1810] focus:outline-none transition cursor-pointer"
              >
                <option value="Silky Chocolate Ganache">Silky Chocolate Ganache</option>
                <option value="Whipped Vanilla Buttercream">Whipped Vanilla Buttercream</option>
                <option value="Wild Berry Compote & Mascarpone">Wild Berry Compote &amp; Mascarpone</option>
                <option value="Salted Caramel Praline">Salted Caramel Praline</option>
                <option value="Cream Cheese Frosting">Cream Cheese Frosting</option>
                <option v-if="isFasting" value="Vegan Dark Choco Whip">Vegan Dark Choco Whip 🌿</option>
                <option v-if="isFasting" value="Whipped Coconut Cream Frosting">Whipped Coconut Cream Frosting 🌿</option>
              </select>
            </div>
          </div>

          <!-- Occasion & Date Needed -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="space-y-1.5">
              <label class="block text-xs font-bold uppercase tracking-wider text-[#2A1810]">Occasion</label>
              <select
                v-model="occasion"
                class="w-full bg-[#FAF7F5] border border-gray-200 focus:border-[#DE1B54] focus:bg-white rounded-xl px-3.5 py-2.5 text-xs text-[#2A1810] focus:outline-none transition cursor-pointer"
              >
                <option value="Birthday">Birthday Celebration 🎂</option>
                <option value="Wedding">Wedding Centerpiece 💍</option>
                <option value="Anniversary">Anniversary Celebration 🥂</option>
                <option value="Corporate Event">Corporate / Office Gala 🏢</option>
                <option value="Graduation">Graduation Ceremony 🎓</option>
                <option value="Baby Shower">Baby Shower / Gender Reveal 🍼</option>
                <option value="Other">Special Celebration 🎉</option>
              </select>
            </div>

            <div class="space-y-1.5">
              <label class="block text-xs font-bold uppercase tracking-wider text-[#2A1810]">Event Date Needed</label>
              <input
                v-model="neededBy"
                type="date"
                :min="minDate"
                class="w-full bg-[#FAF7F5] border border-gray-200 focus:border-[#DE1B54] focus:bg-white rounded-xl px-3.5 py-2 text-xs text-[#2A1810] focus:outline-none transition font-medium"
                required
              />
              <span class="text-[10px] text-gray-400">Requires min. 48 hours notice for handcrafted tiers.</span>
            </div>
          </div>

          <!-- Piped Inscription Message & Live Ribbon Preview -->
          <div class="space-y-2">
            <div class="flex items-center justify-between">
              <label class="block text-xs font-bold uppercase tracking-wider text-[#2A1810]">
                Inscription on Cake (Piped Writing)
              </label>
              <span class="text-[10px] text-[#DE1B54] font-semibold">Complimentary</span>
            </div>

            <input
              v-model="message"
              type="text"
              maxlength="45"
              placeholder="e.g. Happy 30th Birthday Sara! 🎉"
              class="w-full bg-[#FAF7F5] border border-gray-200 focus:border-[#DE1B54] focus:bg-white rounded-xl px-4 py-2.5 text-xs text-[#2A1810] focus:outline-none transition"
            />

            <!-- Live Piped Ribbon Visualizer -->
            <div v-if="message.trim()" class="p-3 bg-gradient-to-r from-rose-50 via-pink-50 to-rose-50 rounded-xl border border-rose-200/60 flex items-center justify-center text-center shadow-2xs">
              <div class="font-script text-lg sm:text-xl text-[#DE1B54] tracking-wide">
                &ldquo;{{ message }}&rdquo;
              </div>
            </div>
          </div>

          <!-- Detailed Theme Instructions -->
          <div class="space-y-1.5">
            <label class="block text-xs font-bold uppercase tracking-wider text-[#2A1810]">
              Theme &amp; Decoration Details
            </label>
            <textarea
              v-model="description"
              rows="3"
              placeholder="Describe color palette (e.g. blush pink & gold leaf), edible floral style, custom fondant toppers, chocolate drip..."
              class="w-full bg-[#FAF7F5] border border-gray-200 focus:border-[#DE1B54] focus:bg-white rounded-xl p-3 text-xs text-[#2A1810] focus:outline-none transition"
            ></textarea>
          </div>
        </div>

        <!-- ================= RIGHT COLUMN: DESIGN INSPIRATION & QUOTE (5 cols) ================= -->
        <div class="lg:col-span-5 bg-white dark:bg-[#1E120B] rounded-2xl border border-[#EFE8E1] dark:border-[#382318] p-6 sm:p-8 space-y-6 shadow-xs">
          <div class="flex items-center justify-between border-b border-gray-100 dark:border-[#382318] pb-3">
            <div class="flex items-center space-x-2">
              <span class="w-6 h-6 rounded-full bg-[#DE1B54] text-white flex items-center justify-center text-xs font-bold">2</span>
              <h2 class="text-lg font-serif font-bold text-[#2A1810] dark:text-[#FAF3EE]">
                Design Inspiration &amp; Quote
              </h2>
            </div>
            <span class="text-xs text-[#DE1B54] font-bold">Step 2 of 2</span>
          </div>

          <!-- Upload Dropzone -->
          <div
            @click="triggerUpload"
            @dragover.prevent
            @drop.prevent="onFileDrop"
            class="border-2 border-dashed border-[#DE1B54]/40 hover:border-[#DE1B54] bg-[#FAF7F5] dark:bg-[#2A1810] hover:bg-rose-50/20 dark:hover:bg-[#341822]/40 rounded-2xl p-6 text-center cursor-pointer transition-all space-y-2 group"
          >
            <input ref="fileInput" type="file" multiple accept="image/*" class="hidden" @change="onFileChange" />
            <div v-if="isUploadingImage" class="space-y-2 py-2">
              <div class="w-10 h-10 border-3 border-[#DE1B54] border-t-transparent rounded-full animate-spin mx-auto"></div>
              <div class="text-xs font-bold text-[#DE1B54]">Uploading &amp; validating reference photo(s)...</div>
            </div>
            <template v-else>
              <div class="w-12 h-12 rounded-full bg-rose-50 dark:bg-[#341822] text-[#DE1B54] flex items-center justify-center mx-auto text-xl shadow-2xs group-hover:scale-110 transition-transform">
                📸
              </div>
              <div class="text-xs font-bold text-[#2A1810] dark:text-[#FAF3EE]">Upload Reference Photos or Drag &amp; Drop</div>
              <div class="text-[10px] text-gray-500 dark:text-gray-400">PNG, JPG, WebP up to 5MB (Pinterest, Instagram, or sketch)</div>
            </template>
          </div>

          <!-- Attached Photo Gallery -->
          <div class="space-y-2">
            <div class="flex items-center justify-between text-xs font-bold text-[#2A1810] dark:text-[#FAF3EE]">
              <span>Inspiration References ({{ referenceSamples.length }}):</span>
              <span v-if="referenceSamples.length > 0" class="text-[10px] text-gray-400 dark:text-gray-500">Click photo to zoom</span>
            </div>
            <div v-if="referenceSamples.length > 0" class="grid grid-cols-3 gap-2.5">
              <div
                v-for="(img, idx) in referenceSamples"
                :key="idx"
                class="aspect-square rounded-xl overflow-hidden border border-gray-200 dark:border-[#382318] shadow-2xs relative group cursor-pointer"
                @click="previewImage(img)"
              >
                <img :src="img" alt="Reference Cake" class="w-full h-full object-cover group-hover:scale-110 transition duration-300" loading="lazy" />
                <button
                  type="button"
                  @click.stop="removeReference(idx)"
                  class="absolute top-1 right-1 w-5 h-5 rounded-full bg-black/70 hover:bg-[#DE1B54] text-white text-[10px] font-bold flex items-center justify-center transition opacity-0 group-hover:opacity-100"
                  title="Remove reference"
                >
                  ✕
                </button>
              </div>
            </div>
            <div v-else class="p-3 bg-[#FAF7F5] dark:bg-[#2A1810] rounded-xl border border-dashed border-gray-200 dark:border-[#382318] text-[11px] text-[#786B60] dark:text-[#C5B7AF] text-center">
              Optional: Drag or upload reference images, or simply describe your design below.
            </div>
          </div>

          <!-- Live Price Estimate Range Box -->
          <div class="p-4 bg-gradient-to-br from-rose-50/80 to-[#FAF7F5] dark:from-[#2A1810] dark:to-[#1E120B] rounded-xl border border-rose-100 dark:border-[#382318] space-y-2 shadow-2xs">
            <div class="flex items-center justify-between text-xs">
              <span class="text-[#6E5D53] dark:text-[#C5B7AF] font-medium">Estimated Price Range:</span>
              <span class="font-black text-[#DE1B54] text-base">{{ estimatedPriceRange }}</span>
            </div>
            <p class="text-[11px] text-[#6E5D53] dark:text-[#C5B7AF] leading-snug">
              Final price depends on decorative complexity, edible gold leaf, and fresh florals. You will receive an official quotation from <strong>{{ chosenBranchName }}</strong> to review before committing.
            </p>
          </div>

          <!-- How Custom Cake Ordering Works: 4 Steps -->
          <div class="p-4 rounded-xl bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-100 dark:border-[#382318] text-xs space-y-2.5">
            <div class="font-bold text-[#2A1810] dark:text-[#FAF3EE] flex items-center space-x-1.5 text-xs">
              <span class="text-rose-500">🛡️</span>
              <span>How Bespoke Cake Ordering Works:</span>
            </div>
            <div class="space-y-2 text-[11px] text-[#6E5D53] dark:text-[#C5B7AF]">
              <div class="flex items-start space-x-2">
                <span class="w-4 h-4 rounded-full bg-[#DE1B54] text-white flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">1</span>
                <span>Submit your dream cake brief and reference photos.</span>
              </div>
              <div class="flex items-start space-x-2">
                <span class="w-4 h-4 rounded-full bg-[#DE1B54] text-white flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">2</span>
                <span>Head decorator reviews and posts a formal price quote within 2–4 hours.</span>
              </div>
              <div class="flex items-start space-x-2">
                <span class="w-4 h-4 rounded-full bg-[#DE1B54] text-white flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">3</span>
                <span>Review quote in My Account and pay 50% deposit to lock in kitchen schedule.</span>
              </div>
              <div class="flex items-start space-x-2">
                <span class="w-4 h-4 rounded-full bg-[#DE1B54] text-white flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">4</span>
                <span>Bakehouse prepares and safely delivers your centerpiece on event day.</span>
              </div>
            </div>
          </div>

          <!-- Contact Information -->
          <div class="p-4 rounded-xl bg-[#FAF7F5] dark:bg-[#2A1810] border border-gray-100 dark:border-[#382318] space-y-3">
            <div class="font-bold text-[#2A1810] dark:text-[#FAF3EE] text-xs flex items-center space-x-1.5">
              <span>👤</span>
              <span>Contact Information (For Quotation Updates)</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block text-[11px] font-bold text-[#2A1810] dark:text-[#FAF3EE] mb-1">Your Full Name *</label>
                <input
                  v-model="customerName"
                  type="text"
                  required
                  placeholder="Enter your full name"
                  class="w-full bg-white dark:bg-[#1E120B] border border-gray-200 dark:border-[#382318] focus:border-[#DE1B54] rounded-xl px-3 py-2 text-xs text-[#2A1810] dark:text-[#FAF3EE] focus:outline-none"
                />
              </div>
              <div>
                <label class="block text-[11px] font-bold text-[#2A1810] dark:text-[#FAF3EE] mb-1">Phone Number *</label>
                <input
                  v-model="customerPhone"
                  type="text"
                  required
                  placeholder="+251 9XX XXX XXX"
                  class="w-full bg-white dark:bg-[#1E120B] border border-gray-200 dark:border-[#382318] focus:border-[#DE1B54] rounded-xl px-3 py-2 text-xs text-[#2A1810] dark:text-[#FAF3EE] focus:outline-none"
                />
              </div>
            </div>
          </div>

          <!-- Submit Request Button -->
          <button
            type="submit"
            :disabled="isSubmitting"
            class="w-full py-3.5 bg-[#DE1B54] hover:bg-[#C51345] disabled:opacity-50 text-white text-xs font-bold rounded-xl shadow-md hover:shadow-lg transition active:scale-98 flex items-center justify-center space-x-2 cursor-pointer"
          >
            <span>{{ isSubmitting ? 'Transmitting Design Brief...' : 'Submit Design Request' }}</span>
            <span v-if="!isSubmitting">&rarr;</span>
          </button>
        </div>

      </form>
    </div>

    <!-- 4. SUCCESS SUBMISSION MODAL -->
    <div
      v-if="submittedModal"
      class="fixed inset-0 z-50 bg-black/60 backdrop-blur-2xs flex items-center justify-center p-4 cursor-pointer"
      @click="submittedModal = false"
    >
      <div
        class="bg-white rounded-2xl border border-gray-100 max-w-md w-full p-6 sm:p-8 text-center space-y-4 shadow-2xl relative cursor-default"
        @click.stop
      >
        <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center mx-auto text-2xl font-bold shadow-xs">
          ✓
        </div>
        <div class="space-y-1">
          <h3 class="font-serif font-black text-2xl text-[#2A1810]">
            Brief Received!
          </h3>
          <p class="text-xs text-[#2A1810] leading-relaxed">
            Your custom cake brief <strong class="text-[#DE1B54]">#{{ requestNumber }}</strong> has been forwarded to the head pastry decorator at <strong>{{ chosenBranchName }}</strong>.
          </p>
        </div>
        <p class="text-xs text-[#6E5D53] leading-relaxed">
          We will review your design brief and post an official quotation to your account within 2–4 hours.
        </p>
        <div class="pt-3 flex flex-col sm:flex-row items-center justify-center gap-2.5">
          <router-link
            to="/account?tab=custom-cakes"
            class="w-full sm:w-auto px-6 py-2.5 bg-[#DE1B54] hover:bg-[#C51345] text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer"
          >
            Track in My Account &rarr;
          </router-link>
          <router-link
            to="/shop"
            class="w-full sm:w-auto px-6 py-2.5 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 text-xs font-bold rounded-xl transition cursor-pointer"
          >
            Shop Today's Bakes
          </router-link>
        </div>
      </div>
    </div>

    <!-- 5. ZOOM PREVIEW MODAL -->
    <div
      v-if="previewZoomImg"
      class="fixed inset-0 z-50 bg-black/80 backdrop-blur-xs flex items-center justify-center p-4 cursor-pointer"
      @click="previewZoomImg = null"
    >
      <div class="relative max-w-xl w-full flex items-center justify-center" @click.stop>
        <img :src="previewZoomImg" alt="Inspiration Preview" class="max-w-full max-h-[80vh] object-contain rounded-2xl shadow-2xl" />
        <button
          type="button"
          @click="previewZoomImg = null"
          class="absolute top-3 right-3 w-8 h-8 rounded-full bg-white/90 text-black font-bold flex items-center justify-center shadow-lg cursor-pointer"
        >
          ✕
        </button>
      </div>
    </div>

    <!-- 6. DECORATIVE WAVE RIBBON ABOVE FOOTER -->
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
import { ref, computed, onMounted, watch } from 'vue';
import { useBranchStore } from '../../stores/branch';
import { useAuthStore } from '../../stores/auth';
import { useToastStore } from '../../stores/toast';
import api from '../../api';

const branchStore = useBranchStore();
const authStore = useAuthStore();
const toast = useToastStore();

const fileInput = ref(null);
const isSubmitting = ref(false);
const isUploadingImage = ref(false);
const submittedModal = ref(false);
const previewZoomImg = ref(null);
const isFasting = ref(false);
const requestNumber = ref('CC-0840');

const customerName = ref(authStore.user?.full_name || '');
const customerPhone = ref(authStore.user?.phone || '');

const preparingBranchId = ref(2); // Bole Flagship default
const cakeSize = ref('Single Tier 8"');
const flavor = ref('Belgian Dark Chocolate');
const filling = ref('Silky Chocolate Ganache');
const occasion = ref('Birthday');
const neededBy = ref(new Date(Date.now() + 86400000 * 3).toISOString().split('T')[0]);
const message = ref('Happy Birthday! 🎉');
const description = ref('Warm ivory buttercream with blush rose accents, 24k edible gold leaf, and fresh berries.');

const minDate = computed(() => {
  const d = new Date(Date.now() + 86400000 * 2);
  return d.toISOString().split('T')[0];
});

const sizeOptions = [
  { name: 'Single Tier 6"', serves: 'Serves 6–8 guests', icon: '🎂', baseEstimate: 'ETB 1,200 – 1,800' },
  { name: 'Single Tier 8"', serves: 'Serves 12–16 guests', icon: '🎂', baseEstimate: 'ETB 1,800 – 2,600' },
  { name: 'Two-Tier Celebration (8" + 6")', serves: 'Serves 24–30 guests', icon: '🍰', baseEstimate: 'ETB 3,200 – 4,800' },
  { name: 'Grand Three-Tier Gala (10" + 8" + 6")', serves: 'Serves 45–60 guests', icon: '👑', baseEstimate: 'ETB 6,000 – 9,500' },
];

const inspirationPresets = [
  {
    name: 'Blush Floral Birthday',
    icon: '🌸',
    occasion: 'Birthday',
    flavor: 'Madagascar Bourbon Vanilla',
    filling: 'Wild Berry Compote & Mascarpone',
    description: 'Blush pink and ivory palette with fresh spray roses, edible pearls, and gold drip.',
  },
  {
    name: 'Luxury Wedding Tier',
    icon: '💍',
    occasion: 'Wedding',
    size: 'Two-Tier Celebration (8" + 6")',
    flavor: 'Belgian Dark Chocolate',
    filling: 'Silky Chocolate Ganache',
    description: 'Minimalist smooth ivory fondant, hand-painted gold leaf veins, and white sugar peonies.',
  },
  {
    name: 'Graduation Centerpiece',
    icon: '🎓',
    occasion: 'Graduation',
    flavor: 'Classic Red Velvet',
    filling: 'Cream Cheese Frosting',
    description: 'Black and gold celebration theme with custom acrylic graduate silhouette topper.',
  },
  {
    name: 'Choco Decadence Drip',
    icon: '🍫',
    occasion: 'Birthday',
    flavor: 'Belgian Dark Chocolate',
    filling: 'Salted Caramel Praline',
    description: 'Triple Belgian chocolate ganache drip with macarons, chocolate curls, and fresh raspberries.',
  },
];

const estimatedPriceRange = computed(() => {
  const opt = sizeOptions.find((o) => o.name === cakeSize.value);
  return opt ? opt.baseEstimate : 'ETB 1,800 – 2,800';
});

const referenceSamples = ref([]);

const chosenBranchName = computed(() => {
  const b = branchStore.branches.find((br) => br.branch_id === preparingBranchId.value);
  return b ? b.branch_name : 'Bole Flagship & Espresso';
});

watch(() => branchStore.selectedBranchId, (newId) => {
  if (newId) {
    preparingBranchId.value = newId;
  }
});

onMounted(async () => {
  if (branchStore.branches.length === 0) {
    await branchStore.fetchBranches();
  }
  if (branchStore.selectedBranchId) {
    preparingBranchId.value = branchStore.selectedBranchId;
  }
});

function applyPreset(preset) {
  occasion.value = preset.occasion;
  if (preset.size) cakeSize.value = preset.size;
  flavor.value = preset.flavor;
  filling.value = preset.filling;
  description.value = preset.description;
  toast.success(`Applied "${preset.name}" inspiration!`);
}

function triggerUpload() {
  fileInput.value?.click();
}

async function onFileDrop(e) {
  const files = e.dataTransfer?.files;
  if (files && files.length > 0) {
    await processFiles(files);
  }
}

async function onFileChange(e) {
  const files = e.target.files;
  if (files && files.length > 0) {
    await processFiles(files);
  }
  if (fileInput.value) {
    fileInput.value.value = '';
  }
}

async function processFiles(files) {
  isUploadingImage.value = true;
  let successCount = 0;

  for (let i = 0; i < files.length; i++) {
    const file = files[i];
    if (!file.type.startsWith('image/')) {
      toast.error(`"${file.name}" is not an image file.`);
      continue;
    }

    const formData = new FormData();
    formData.append('image', file);

    try {
      const res = await api.post('/custom-cakes/upload-image', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });

      if (res.data?.image_url) {
        referenceSamples.value.unshift(res.data.image_url);
        successCount++;
      }
    } catch (err) {
      console.error('Failed to upload image', err);
      toast.error(err.response?.data?.message || `Failed to upload "${file.name}". Ensure it is a valid image under 5MB.`);
    }
  }

  isUploadingImage.value = false;
  if (successCount > 0) {
    toast.success(`Successfully uploaded ${successCount} design photo(s) to bakery!`);
  }
}

function removeReference(idx) {
  referenceSamples.value.splice(idx, 1);
  toast.info('Removed photo.');
}

function previewImage(img) {
  previewZoomImg.value = img;
}

async function handleSubmitCustomCake() {
  isSubmitting.value = true;
  try {
    const payload = {
      branch_id: preparingBranchId.value,
      customer_name: customerName.value ? customerName.value.trim() : '',
      customer_phone: customerPhone.value ? customerPhone.value.trim() : '',
      cake_size: cakeSize.value,
      flavor: `${flavor.value} with ${filling.value}${isFasting.value ? ' (100% Ye\'Tsom Fasting Certified)' : ''}`,
      needed_by: neededBy.value,
      design: `${occasion.value} cake. ${description.value}`,
      message: message.value,
      image_urls: referenceSamples.value,
    };

    const res = await api.post('/custom-cakes', payload);
    requestNumber.value = res.data?.request_number || res.data?.cake_request?.request_number;
    submittedModal.value = true;
    toast.success('Custom cake brief forwarded to head baker!');
  } catch (err) {
    console.error('Failed submitting custom cake', err);
    toast.error(err.response?.data?.message || 'Failed to submit custom cake brief. Please check all required fields.');
  } finally {
    isSubmitting.value = false;
  }
}
</script>
