<template>
  <div class="bg-white min-h-screen text-[#29231F] font-sans selection:bg-[#FCE7F0] selection:text-[#DE1B54] pb-12">
    <!-- 1. HERO BANNER: Exact SweetBakes Style Matching Shop & Home Pages -->
    <section class="relative overflow-hidden bg-gradient-to-r from-[#FFF5F2] via-[#FDF3EE] to-[#FCEEE8] min-h-[180px] sm:min-h-[220px] lg:min-h-[240px] flex items-center border-b border-[#F2E5DD]">
      <!-- Background Bakery Imagery on Right -->
      <div class="absolute right-0 top-0 bottom-0 w-full lg:w-[55%] overflow-hidden pointer-events-none">
        <img
          src="/mockup/shop_banner_right.jpg"
          alt="SweetBakes Contact Banner"
          class="w-full h-full object-cover object-center lg:object-left"
          loading="eager"
        />
        <div class="absolute inset-y-0 left-0 w-32 sm:w-48 bg-gradient-to-r from-[#FDF3EE] via-[#FDF3EE]/80 to-transparent hidden lg:block"></div>
        <div class="absolute inset-0 bg-[#FFF5F2]/80 sm:bg-[#FFF5F2]/75 lg:hidden"></div>
      </div>

      <!-- Banner Text on Left -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full py-8 relative z-10">
        <div class="max-w-xl space-y-2 text-center lg:text-left">
          <!-- Script Subtitle with Dash Flourishes -->
          <div class="flex items-center justify-center lg:justify-start space-x-2 font-script text-2xl sm:text-3xl text-[#DE1B54]">
            <span class="text-gray-300 select-none">&mdash;</span>
            <span>Get In Touch</span>
            <span class="text-gray-300 select-none">&mdash;</span>
          </div>

          <!-- Large Bold Serif Title -->
          <h1 class="text-3xl sm:text-4xl lg:text-5xl font-serif font-black text-[#2A1810] tracking-tight leading-tight">
            Contact SweetBakes
          </h1>

          <!-- Tagline with Pink Heart -->
          <p class="text-xs sm:text-sm text-[#6E5D53] flex items-center justify-center lg:justify-start space-x-1.5">
            <span>We'd love to hear from you, freshly baked with love!</span>
            <span class="text-[#DE1B54] text-base font-bold font-script">♡</span>
          </p>
        </div>
      </div>
    </section>

    <!-- 2. QUICK CONTACT INFO CARDS: 3-Column Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10 space-y-10">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Card 1: Phone -->
        <div class="bg-white rounded-2xl border border-[#EFE8E1] hover:border-[#DE1B54]/40 hover:shadow-lg transition-all p-6 text-center space-y-3 group shadow-xs">
          <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-100 text-[#DE1B54] flex items-center justify-center mx-auto text-xl shadow-2xs group-hover:scale-110 transition-transform">
            📞
          </div>
          <div>
            <h3 class="font-serif font-bold text-base text-[#2A1810]">Customer Helpline</h3>
            <p class="text-xs text-[#786B60] mt-0.5">Direct Addis Ababa bakery support</p>
          </div>
          <div class="font-bold text-xs text-[#DE1B54] pt-1 space-y-1">
            <div><a href="tel:+251911123456" class="hover:underline">+251 911 123 456</a></div>
            <div><a href="tel:+251115501111" class="hover:underline text-[#2A1810]">+251 11 550 1111</a></div>
          </div>
        </div>

        <!-- Card 2: Email -->
        <div class="bg-white rounded-2xl border border-[#EFE8E1] hover:border-[#DE1B54]/40 hover:shadow-lg transition-all p-6 text-center space-y-3 group shadow-xs">
          <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-100 text-[#DE1B54] flex items-center justify-center mx-auto text-xl shadow-2xs group-hover:scale-110 transition-transform">
            ✉️
          </div>
          <div>
            <h3 class="font-serif font-bold text-base text-[#2A1810]">Email Inquiries</h3>
            <p class="text-xs text-[#786B60] mt-0.5">Custom cakes, corporate orders &amp; quotes</p>
          </div>
          <div class="font-bold text-xs text-[#DE1B54] pt-1 space-y-1">
            <div><a href="mailto:hello@sweetbakes.com" class="hover:underline">hello@sweetbakes.com</a></div>
            <div><a href="mailto:orders@sweetbakes.com" class="hover:underline text-[#2A1810]">orders@sweetbakes.com</a></div>
          </div>
        </div>

        <!-- Card 3: Schedule -->
        <div class="bg-white rounded-2xl border border-[#EFE8E1] hover:border-[#DE1B54]/40 hover:shadow-lg transition-all p-6 text-center space-y-3 group shadow-xs">
          <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-100 text-[#DE1B54] flex items-center justify-center mx-auto text-xl shadow-2xs group-hover:scale-110 transition-transform">
            ⏰
          </div>
          <div>
            <h3 class="font-serif font-bold text-base text-[#2A1810]">Baking Hours</h3>
            <p class="text-xs text-[#786B60] mt-0.5">Fresh bread pulled from ovens twice daily</p>
          </div>
          <div class="font-bold text-xs text-[#2A1810] pt-1 space-y-1">
            <div>Mon – Sun: 6:30 AM – 9:30 PM</div>
            <div class="text-[11px] text-[#2E7D32] font-semibold">Ovens firing 7 days a week</div>
          </div>
        </div>
      </div>

      <!-- 3. MAIN SECTION: Contact Form (7 cols) + Bakehouses Directory (5 cols) -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- ================= CONTACT FORM (7 cols) ================= -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-[#EFE8E1] p-6 sm:p-8 space-y-6 shadow-xs">
          <div class="border-b border-gray-100 pb-4">
            <div class="flex items-center space-x-2 text-xs font-bold uppercase tracking-wider text-[#DE1B54] mb-1">
              <span>🧁</span>
              <span>Send Us a Note</span>
            </div>
            <h2 class="text-2xl font-serif font-black text-[#2A1810]">
              Send SweetBakes a Message
            </h2>
            <p class="text-xs text-[#786B60] mt-1 leading-relaxed">
              Have questions regarding custom wedding centerpiece cakes, large corporate catering, or today's fresh oven batches? Reach out to our bakers anytime.
            </p>
          </div>

          <form @submit.prevent="sendMessage" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-[#2A1810] mb-1.5">
                  Your Full Name <span class="text-[#DE1B54]">*</span>
                </label>
                <input
                  v-model="name"
                  type="text"
                  placeholder="e.g. Sara Ahmed"
                  class="w-full bg-[#FAF7F5] border border-gray-200 focus:border-[#DE1B54] focus:bg-white rounded-xl px-4 py-2.5 text-xs text-[#2A1810] focus:outline-none transition"
                  required
                />
              </div>

              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-[#2A1810] mb-1.5">
                  Email Address <span class="text-[#DE1B54]">*</span>
                </label>
                <input
                  v-model="email"
                  type="email"
                  placeholder="sara@example.com"
                  class="w-full bg-[#FAF7F5] border border-gray-200 focus:border-[#DE1B54] focus:bg-white rounded-xl px-4 py-2.5 text-xs text-[#2A1810] focus:outline-none transition"
                  required
                />
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-[#2A1810] mb-1.5">
                  Ethiopian Phone Number
                </label>
                <input
                  v-model="phone"
                  type="text"
                  placeholder="+251 9XX XXX XXX"
                  class="w-full bg-[#FAF7F5] border border-gray-200 focus:border-[#DE1B54] focus:bg-white rounded-xl px-4 py-2.5 text-xs text-[#2A1810] focus:outline-none transition"
                />
              </div>

              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-[#2A1810] mb-1.5">
                  Inquiry Subject
                </label>
                <select
                  v-model="subject"
                  class="w-full bg-[#FAF7F5] border border-gray-200 focus:border-[#DE1B54] focus:bg-white rounded-xl px-4 py-2.5 text-xs text-[#2A1810] focus:outline-none transition cursor-pointer"
                >
                  <option value="General Inquiry">General Bakery Inquiry</option>
                  <option value="Custom Cake Quotation">Custom Cake &amp; Wedding Centerpiece</option>
                  <option value="Corporate Catering">Corporate &amp; Event Catering</option>
                  <option value="Delivery Question">Delivery &amp; Coverage Question</option>
                  <option value="Feedback & Suggestions">Taste &amp; Service Feedback</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-[#2A1810] mb-1.5">
                Your Message <span class="text-[#DE1B54]">*</span>
              </label>
              <textarea
                v-model="message"
                rows="4"
                placeholder="Tell us what you need, your event date, flavor preferences, or any questions..."
                class="w-full bg-[#FAF7F5] border border-gray-200 focus:border-[#DE1B54] focus:bg-white rounded-xl p-3.5 text-xs text-[#2A1810] focus:outline-none transition"
                required
              ></textarea>
            </div>

            <button
              type="submit"
              :disabled="isSubmitting"
              class="w-full sm:w-auto px-8 py-3 bg-[#DE1B54] hover:bg-[#C51345] disabled:opacity-50 text-white text-xs font-bold rounded-xl shadow-xs transition active:scale-95 flex items-center justify-center space-x-2 cursor-pointer"
            >
              <span>{{ isSubmitting ? 'Sending Message...' : 'Send Message' }}</span>
              <span v-if="!isSubmitting">&rarr;</span>
            </button>
          </form>
        </div>

        <!-- ================= BAKEHOUSES DIRECTORY (5 cols) ================= -->
        <div class="lg:col-span-5 space-y-4">
          <div class="bg-white rounded-2xl border border-[#EFE8E1] shadow-xs overflow-hidden">
            <!-- Dark Chocolate Header -->
            <div class="bg-[#24150D] text-white px-5 py-3.5 flex items-center justify-between">
              <div class="flex items-center space-x-2 text-xs font-bold uppercase tracking-wider">
                <span class="text-rose-400">📍</span>
                <span>Addis Ababa Bakehouses</span>
              </div>
              <router-link to="/branches" class="text-xs text-rose-400 font-semibold hover:underline">
                View Map &rarr;
              </router-link>
            </div>

            <!-- Branch List -->
            <div class="p-5 space-y-3 max-h-[500px] overflow-y-auto">
              <div
                v-for="b in bakehouses"
                :key="b.id"
                class="p-4 rounded-xl border border-gray-100 bg-[#FAF7F5] hover:border-[#DE1B54]/40 hover:bg-white transition space-y-2 group"
              >
                <div class="flex items-center justify-between">
                  <div class="font-serif font-bold text-sm text-[#2A1810] group-hover:text-[#DE1B54] transition">
                    {{ b.name }}
                  </div>
                  <span
                    class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                    :class="b.isMain ? 'bg-[#DE1B54] text-white' : 'bg-rose-50 text-[#DE1B54] border border-rose-200'"
                  >
                    {{ b.badge }}
                  </span>
                </div>

                <p class="text-xs text-[#6E5D53] flex items-center space-x-1.5">
                  <span class="text-gray-400">📍</span>
                  <span>{{ b.address }}</span>
                </p>

                <div class="flex items-center justify-between pt-1 border-t border-gray-200/60 text-xs">
                  <a :href="`tel:${b.phone}`" class="text-[#DE1B54] font-bold hover:underline flex items-center space-x-1">
                    <span>📞</span>
                    <span>{{ b.phone }}</span>
                  </a>
                  <button
                    type="button"
                    @click="shopBranch(b.branchId)"
                    class="px-3 py-1 bg-white hover:bg-[#DE1B54] text-[#DE1B54] hover:text-white border border-[#DE1B54] rounded-lg text-[11px] font-bold transition cursor-pointer"
                  >
                    Shop Here &rarr;
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Trust Badges -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="p-3 bg-white rounded-xl border border-[#EFE8E1] text-center space-y-1">
              <div class="text-xl">🚚</div>
              <div class="text-xs font-bold text-[#2A1810]">Free Delivery</div>
              <div class="text-[10px] text-gray-500">Orders over ETB 1,000</div>
            </div>
            <div class="p-3 bg-white rounded-xl border border-[#EFE8E1] text-center space-y-1">
              <div class="text-xl">🌿</div>
              <div class="text-xs font-bold text-[#2A1810]">Ye'Tsom Vegan</div>
              <div class="text-[10px] text-gray-500">100% Fasting options</div>
            </div>
            <div class="p-3 bg-white rounded-xl border border-[#EFE8E1] text-center space-y-1">
              <div class="text-xl">🎂</div>
              <div class="text-xs font-bold text-[#2A1810]">Custom Cakes</div>
              <div class="text-[10px] text-gray-500">Weddings &amp; Birthdays</div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- 4. DECORATIVE WAVE RIBBON ABOVE FOOTER: Exact Match to Mockup -->
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
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useBranchStore } from '../../stores/branch';
import { useToastStore } from '../../stores/toast';

const router = useRouter();
const branchStore = useBranchStore();
const toast = useToastStore();

const name = ref('');
const email = ref('');
const phone = ref('');
const subject = ref('General Inquiry');
const message = ref('');
const isSubmitting = ref(false);

const bakehouses = [
  { id: 1, branchId: 1, name: 'Mexico Central Kitchen & HQ', address: 'Ras Abebe Aregay St, Mexico Square', phone: '+251 11 550 1111', badge: 'HQ', isMain: true },
  { id: 2, branchId: 2, name: 'Bole Flagship & Espresso', address: 'Cameroon St, Near Edna Mall, Bole', phone: '+251 911 123 456', badge: 'Branch 02', isMain: false },
  { id: 3, branchId: 3, name: 'Megenagna Bakehouse', address: 'Sileshi Sehin Building, Megenagna', phone: '+251 11 662 2222', badge: 'Branch 03', isMain: false },
  { id: 4, branchId: 4, name: 'Piassa Heritage Bakehouse', address: 'Churchill Avenue, Historic Piassa', phone: '+251 11 155 3333', badge: 'Branch 04', isMain: false },
  { id: 5, branchId: 5, name: 'CMC Royal Pastry Hub', address: 'CMC Michael, Near St. Michael Church', phone: '+251 11 647 4444', badge: 'Branch 05', isMain: false },
];

function shopBranch(branchId) {
  branchStore.setSelectedBranch(branchId);
  const found = bakehouses.find(b => b.branchId === branchId);
  toast.success(`Active branch set to ${found?.name || 'Selected Branch'}!`);
  router.push('/shop');
}

function sendMessage() {
  isSubmitting.value = true;
  setTimeout(() => {
    toast.success('Your message was sent to our master bakers! We will contact you shortly.');
    name.value = '';
    email.value = '';
    phone.value = '';
    message.value = '';
    isSubmitting.value = false;
  }, 600);
}
</script>
