<template>
  <teleport to="body">
    <transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="localeStore.isTranslatorModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-xs overflow-y-auto"
        @click.self="localeStore.closeTranslator()"
        @keydown.esc="localeStore.closeTranslator()"
        role="dialog"
        aria-modal="true"
        aria-labelledby="translator-modal-title"
      >
        <div
          class="relative w-full max-w-2xl bg-white dark:bg-[#1E120B] rounded-3xl shadow-2xl border border-stone-200 dark:border-[#3D2619] overflow-hidden my-auto transform transition-all flex flex-col max-h-[92vh]"
        >
          <!-- 1. Header with Warm Luxury Chocolate Palette -->
          <div class="px-5 sm:px-7 py-4.5 bg-gradient-to-r from-[#3D251E] via-[#4D3025] to-[#3D251E] text-white flex items-center justify-between shrink-0 shadow-sm border-b border-[#5A382C]">
            <div class="flex items-center space-x-3">
              <span class="w-10 h-10 rounded-2xl bg-white/10 text-amber-300 flex items-center justify-center text-xl shadow-inner border border-white/10 shrink-0">
                🌐
              </span>
              <div>
                <h3 id="translator-modal-title" class="font-serif font-black text-base sm:text-lg text-[#FAF4ED] tracking-tight flex items-center gap-2">
                  <span>{{ direction === 'am-to-en' ? 'Amharic → English Translator' : 'English → Amharic Translator' }}</span>
                  <span class="text-[10px] font-sans font-bold px-2 py-0.5 rounded-full bg-amber-400/20 text-amber-200 border border-amber-300/30">
                    {{ localeStore.isAmharic ? 'ተርጓሚ' : 'Live' }}
                  </span>
                </h3>
                <p class="text-xs text-[#D8CDC5] mt-0.5">
                  SweetBakes Bilingual Engine &middot; Dessie &middot; Addis Ababa
                </p>
              </div>
            </div>

            <!-- Close Button -->
            <button
              @click="localeStore.closeTranslator()"
              class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white/80 hover:text-white flex items-center justify-center transition cursor-pointer"
              title="Close (Esc)"
              aria-label="Close translator"
            >
              ✕
            </button>
          </div>

          <!-- 2. Interactive Direction Switcher & System Language Bar -->
          <div class="px-5 sm:px-7 py-2.5 bg-[#FAF7F2] dark:bg-[#25160E] border-b border-stone-200/80 dark:border-[#382318] flex flex-wrap items-center justify-between gap-2 text-xs shrink-0">
            <!-- Two-way Direction Selector -->
            <div class="inline-flex items-center rounded-xl bg-white dark:bg-[#1E120B] p-1 border border-stone-200 dark:border-[#3D2619] shadow-2xs">
              <button
                @click="setDirection('am-to-en')"
                class="px-2.5 sm:px-3 py-1 rounded-lg font-bold transition flex items-center space-x-1.5 cursor-pointer"
                :class="direction === 'am-to-en' ? 'bg-[#DE1B54] text-white shadow-xs' : 'text-[#6B5A4E] dark:text-[#C5B7AF] hover:text-[#DE1B54]'"
              >
                <span>አማርኛ</span>
                <span class="text-[10px] opacity-80">&rarr;</span>
                <span>English</span>
              </button>

              <!-- Swap Button -->
              <button
                @click="swapDirection"
                class="px-2 py-1 text-gray-400 hover:text-[#DE1B54] transition cursor-pointer"
                title="Swap translation direction"
              >
                ⇄
              </button>

              <button
                @click="setDirection('en-to-am')"
                class="px-2.5 sm:px-3 py-1 rounded-lg font-bold transition flex items-center space-x-1.5 cursor-pointer"
                :class="direction === 'en-to-am' ? 'bg-[#DE1B54] text-white shadow-xs' : 'text-[#6B5A4E] dark:text-[#C5B7AF] hover:text-[#DE1B54]'"
              >
                <span>English</span>
                <span class="text-[10px] opacity-80">&rarr;</span>
                <span>አማርኛ</span>
              </button>
            </div>

            <!-- Global System UI Language Toggle Shortcut -->
            <div class="flex items-center space-x-2">
              <span class="text-[11px] text-[#7D6E64] dark:text-[#A8988E] hidden xs:inline">System UI:</span>
              <button
                @click="localeStore.toggleLocale()"
                class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-lg border border-stone-200 dark:border-[#3D2619] bg-white dark:bg-[#1E120B] text-[11px] font-bold text-[#3D251E] dark:text-[#FAF3EE] hover:border-[#DE1B54] transition shadow-2xs cursor-pointer"
                :title="localeStore.isAmharic ? 'Switch entire dashboard to English' : 'መላውን ገጽ ወደ አማርኛ ቀይር'"
              >
                <span :class="!localeStore.isAmharic ? 'text-[#DE1B54] font-black' : 'text-gray-400'">EN</span>
                <span class="text-gray-300">/</span>
                <span :class="localeStore.isAmharic ? 'text-[#DE1B54] font-black' : 'text-gray-400'">አማ</span>
              </button>
            </div>
          </div>

          <!-- 3. Modal Body: Scrollable Input & Output Area -->
          <div class="p-5 sm:p-7 space-y-4 sm:space-y-5 overflow-y-auto flex-1">
            <!-- Source Input Box -->
            <div class="space-y-1.5">
              <div class="flex items-center justify-between text-xs">
                <label class="font-bold text-[#2B1810] dark:text-[#FAF3EE] flex items-center space-x-1.5">
                  <span class="w-2 h-2 rounded-full bg-[#DE1B54]"></span>
                  <span>{{ direction === 'am-to-en' ? 'Enter Amharic Text (የአማርኛ ጽሁፍ)' : 'Enter English Text' }}</span>
                </label>
                <div class="flex items-center space-x-3 text-[11px] text-[#7D6E64] dark:text-[#A8988E]">
                  <span v-if="inputText.length > 0">{{ inputText.length }} chars</span>
                  <button
                    v-if="inputText.length > 0"
                    @click="clearInput"
                    class="text-rose-500 hover:text-rose-700 font-bold transition cursor-pointer"
                  >
                    Clear ✕
                  </button>
                </div>
              </div>

              <div class="relative">
                <textarea
                  ref="inputAreaRef"
                  v-model="inputText"
                  @input="handleInput"
                  rows="3"
                  class="w-full px-4 py-3 rounded-2xl border border-stone-200 dark:border-[#3D2619] bg-white dark:bg-[#25160E] text-[#2B1810] dark:text-[#FAF3EE] text-sm focus:outline-none focus:ring-2 focus:ring-[#DE1B54] focus:border-transparent transition resize-none placeholder:text-gray-400 leading-relaxed font-sans"
                  :placeholder="direction === 'am-to-en' ? 'ምሳሌ፡ ቸኮሌት ኬክ፣ ትኩስ ዳቦ፣ የልደት ኬክ፣ ደሴ ፒያሳ፣ ክፍያ ተፈጽሟል...' : 'e.g. Chocolate Cake, Fresh Bread, Birthday Cake, Dessie Piazza, Payment Completed...'"
                ></textarea>
              </div>
            </div>

            <!-- Translate Action Buttons -->
            <div class="flex items-center justify-between pt-1">
              <div class="flex items-center space-x-2">
                <button
                  @click="runTranslation"
                  class="px-5 py-2 min-h-[38px] bg-[#DE1B54] hover:bg-[#C51345] text-white font-bold text-xs sm:text-sm rounded-xl shadow-md hover:shadow-lg transition-all flex items-center space-x-1.5 cursor-pointer hover:scale-102 active:scale-98"
                >
                  <span>{{ isTranslating ? 'Translating...' : 'Translate' }}</span>
                  <span>&rarr;</span>
                </button>

                <label class="hidden xs:flex items-center space-x-1.5 text-xs text-[#7D6E64] dark:text-[#A8988E] cursor-pointer select-none">
                  <input
                    type="checkbox"
                    v-model="liveTranslate"
                    class="rounded text-[#DE1B54] focus:ring-[#DE1B54] cursor-pointer"
                  />
                  <span>Live translate</span>
                </label>
              </div>

              <!-- Character / Word stats -->
              <div v-if="translationResult.wordCount > 0" class="text-xs text-[#7D6E64] dark:text-[#A8988E]">
                <span>{{ translationResult.wordCount }} {{ translationResult.wordCount === 1 ? 'word' : 'words' }}</span>
              </div>
            </div>

            <!-- Output Box: Translation Result -->
            <div class="space-y-1.5">
              <div class="flex items-center justify-between text-xs">
                <label class="font-bold text-[#2B1810] dark:text-[#FAF3EE] flex items-center space-x-1.5">
                  <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                  <span>{{ direction === 'am-to-en' ? 'English Translation' : 'የአማርኛ ትርጉም (Amharic Translation)' }}</span>
                </label>

                <!-- Copy to Clipboard Button -->
                <button
                  v-if="outputText.length > 0"
                  @click="copyTranslation"
                  class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-lg text-xs font-bold transition cursor-pointer"
                  :class="copied ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-stone-100 hover:bg-stone-200 dark:bg-[#25160E] dark:hover:bg-[#331E13] text-[#3D251E] dark:text-[#FAF3EE]'"
                  title="Copy to clipboard"
                >
                  <span v-if="copied">✓ Copied!</span>
                  <span v-else>📋 Copy</span>
                </button>
              </div>

              <!-- Output Display Box -->
              <div
                class="w-full min-h-[96px] px-4 py-3.5 rounded-2xl border text-sm leading-relaxed transition-all flex flex-col justify-between"
                :class="outputText.length > 0 ? 'bg-emerald-50/40 dark:bg-emerald-950/20 border-emerald-200/80 dark:border-emerald-900/50 text-[#1A3826] dark:text-[#C8ECD5]' : 'bg-stone-50 dark:bg-[#25160E]/50 border-dashed border-stone-300 dark:border-[#382318] text-[#8C7D73] dark:text-[#8C7D73]'"
              >
                <div v-if="outputText.length > 0" class="font-medium whitespace-pre-wrap select-all">
                  {{ outputText }}
                </div>
                <div v-else class="text-xs italic py-4 text-center">
                  {{ direction === 'am-to-en' ? 'Enter Amharic text above or tap a quick phrase below to view translation.' : 'Enter English text above or tap a quick phrase below to view Amharic translation.' }}
                </div>
              </div>
            </div>

            <!-- Recognized Dictionary Terms / Badges -->
            <div v-if="translationResult.matchedTerms && translationResult.matchedTerms.length > 0" class="space-y-1.5 pt-1">
              <div class="text-[11px] font-bold uppercase tracking-wider text-[#7D6E64] dark:text-[#A8988E]">
                Recognized Bakery Terms:
              </div>
              <div class="flex flex-wrap gap-1.5">
                <span
                  v-for="(term, idx) in translationResult.matchedTerms"
                  :key="idx"
                  class="inline-flex items-center space-x-1 text-[11px] px-2.5 py-0.5 rounded-full bg-white dark:bg-[#25160E] border border-stone-200 dark:border-[#3D2619] shadow-2xs text-[#3D251E] dark:text-[#FAF3EE]"
                >
                  <span class="font-bold text-[#DE1B54]">{{ term.original }}</span>
                  <span class="text-gray-400">&rarr;</span>
                  <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ term.translation }}</span>
                </span>
              </div>
            </div>

            <!-- 4. Quick Sample Chips for Fast Daily Staff & Customer Use -->
            <div class="space-y-2 pt-2 border-t border-stone-200/70 dark:border-[#382318]">
              <div class="text-[11px] font-bold text-[#7D6E64] dark:text-[#A8988E] uppercase tracking-wider flex items-center space-x-1.5">
                <span>⚡</span>
                <span>Quick-Pick Common Phrases (Tap to Translate):</span>
              </div>
              <div class="flex flex-wrap gap-1.5 sm:gap-2">
                <button
                  v-for="(sample, idx) in quickSamples"
                  :key="idx"
                  @click="applySample(sample)"
                  class="text-xs px-2.5 sm:px-3 py-1 rounded-xl bg-white dark:bg-[#25160E] hover:bg-rose-50 dark:hover:bg-[#3D251E] border border-stone-200 dark:border-[#3D2619] hover:border-[#DE1B54] text-[#3D251E] dark:text-[#FAF3EE] transition shadow-2xs cursor-pointer flex items-center space-x-1"
                >
                  <span>{{ direction === 'am-to-en' ? sample.am : sample.en }}</span>
                </button>
              </div>
            </div>
          </div>

          <!-- 5. Footer with Clear Guidance -->
          <div class="px-5 sm:px-7 py-3 bg-[#FAF7F2] dark:bg-[#25160E] border-t border-stone-200/80 dark:border-[#382318] flex items-center justify-between text-xs text-[#7D6E64] dark:text-[#A8988E] shrink-0">
            <div class="flex items-center space-x-2">
              <span class="text-amber-500">★</span>
              <span class="text-[11px] truncate">
                Available to all staff roles, managers, admins, &amp; customers
              </span>
            </div>
            <button
              @click="localeStore.closeTranslator()"
              class="px-4 py-1.5 rounded-xl border border-stone-300 dark:border-[#3D2619] bg-white dark:bg-[#1E120B] hover:bg-stone-100 dark:hover:bg-[#331A12] text-[#3D251E] dark:text-[#FAF3EE] font-bold transition cursor-pointer"
            >
              Done
            </button>
          </div>
        </div>
      </div>
    </transition>
  </teleport>
</template>

<script setup>
import { ref, watch, onMounted, nextTick } from 'vue';
import { useLocaleStore } from '../stores/locale';

const localeStore = useLocaleStore();

const direction = ref('am-to-en');
const inputText = ref('');
const outputText = ref('');
const isTranslating = ref(false);
const liveTranslate = ref(true);
const copied = ref(false);
const inputAreaRef = ref(null);

const translationResult = ref({
  translatedText: '',
  matchedTerms: [],
  wordCount: 0
});

const quickSamples = [
  { am: 'ቸኮሌት ኬክ', en: 'Chocolate Cake' },
  { am: 'ትኩስ የሙሉ ስንዴ ዳቦ', en: 'Fresh Whole Wheat Bread' },
  { am: 'ደሴ ፒያሳ ቅርንጫፍ', en: 'Dessie Piazza Branch' },
  { am: 'ክፍያ በቴሌብር ተፈጽሟል', en: 'Payment Completed via Telebirr' },
  { am: 'የልደት ኬክ ለሳራ', en: 'Birthday Cake for Sara' },
  { am: 'በነፃ ቅርንጫፍ መውሰጃ', en: 'Free Shop Pickup' },
  { am: 'የጾም ቸኮሌት ኬክ', en: "Ye'Tsom Dark Chocolate Cake" },
  { am: 'መልካም 30ኛ ዓመት ልደት', en: 'Happy 30th Birthday' },
  { am: 'በፍጥነት ማድረስ', en: 'Fast Delivery' },
];

function setDirection(newDir) {
  direction.value = newDir;
  // If we have output text, swap it to input
  if (outputText.value) {
    const prevOutput = outputText.value;
    inputText.value = prevOutput;
    runTranslation();
  } else if (inputText.value) {
    runTranslation();
  }
}

function swapDirection() {
  setDirection(direction.value === 'am-to-en' ? 'en-to-am' : 'am-to-en');
}

function clearInput() {
  inputText.value = '';
  outputText.value = '';
  translationResult.value = { translatedText: '', matchedTerms: [], wordCount: 0 };
  if (inputAreaRef.value) {
    inputAreaRef.value.focus();
  }
}

function runTranslation() {
  if (!inputText.value.trim()) {
    outputText.value = '';
    translationResult.value = { translatedText: '', matchedTerms: [], wordCount: 0 };
    return;
  }

  isTranslating.value = true;
  try {
    const res = localeStore.translateText(inputText.value, direction.value);
    translationResult.value = res;
    outputText.value = res.translatedText;
  } finally {
    isTranslating.value = false;
  }
}

function handleInput() {
  if (liveTranslate.value) {
    runTranslation();
  }
}

function applySample(sample) {
  if (direction.value === 'am-to-en') {
    inputText.value = sample.am;
  } else {
    inputText.value = sample.en;
  }
  runTranslation();
}

async function copyTranslation() {
  if (!outputText.value) return;
  try {
    await navigator.clipboard.writeText(outputText.value);
    copied.value = true;
    setTimeout(() => {
      copied.value = false;
    }, 2000);
  } catch (err) {
    // Fallback for older browsers
    const ta = document.createElement('textarea');
    ta.value = outputText.value;
    document.body.appendChild(ta);
    ta.select();
    document.execCommand('copy');
    document.body.removeChild(ta);
    copied.value = true;
    setTimeout(() => {
      copied.value = false;
    }, 2000);
  }
}

// Watch modal open state to initialize values
watch(
  () => localeStore.isTranslatorModalOpen,
  (isOpen) => {
    if (isOpen) {
      if (localeStore.translatorDirection) {
        direction.value = localeStore.translatorDirection;
      }
      if (localeStore.translatorInitialText) {
        inputText.value = localeStore.translatorInitialText;
        runTranslation();
      }
      copied.value = false;
      nextTick(() => {
        if (inputAreaRef.value) {
          inputAreaRef.value.focus();
        }
      });
    }
  }
);
</script>
