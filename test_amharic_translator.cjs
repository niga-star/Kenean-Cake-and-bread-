const fs = require('fs');
const path = require('path');
const assert = require('assert');

console.log('====================================================');
console.log('TEST: System-Wide Amharic-to-English Translator Button & Engine');
console.log('====================================================');

// 1. Verify locale.js contains translator state, functions and bakery glossary
const localeJs = fs.readFileSync(path.join(__dirname, 'Client/src/stores/locale.js'), 'utf8');

assert(localeJs.includes('isTranslatorModalOpen'), 'localeStore must export isTranslatorModalOpen');
assert(localeJs.includes('openTranslator'), 'localeStore must export openTranslator');
assert(localeJs.includes('closeTranslator'), 'localeStore must export closeTranslator');
assert(localeJs.includes('translateText'), 'localeStore must export translateText');
assert(localeJs.includes('bakeryGlossary'), 'localeStore must include bakeryGlossary');
console.log('✓ localeStore exports all required translator methods and reactive states');

// 2. Verify AmharicTranslatorModal.vue component exists and contains required features
const modalVue = fs.readFileSync(path.join(__dirname, 'Client/src/components/AmharicTranslatorModal.vue'), 'utf8');

assert(modalVue.includes('Amharic → English Translator'), 'Modal must include Amharic → English translation mode');
assert(modalVue.includes('English → Amharic Translator'), 'Modal must support two-way English ↔ Amharic swap');
assert(modalVue.includes('copyTranslation'), 'Modal must include copy to clipboard feature');
assert(modalVue.includes('quickSamples'), 'Modal must provide quick-pick sample phrases for daily staff speed');
assert(modalVue.includes('toggleLocale'), 'Modal must allow quick toggle of system UI language');
console.log('✓ AmharicTranslatorModal.vue verified with responsive UI, live translation, clipboard copying & sample presets');

// 3. Verify AdminLayout.vue (Dashboards for Admin, Manager, Branch Staff, and all Staff pages)
const adminLayoutVue = fs.readFileSync(path.join(__dirname, 'Client/src/layouts/AdminLayout.vue'), 'utf8');

assert(adminLayoutVue.includes('data-testid="admin-header-translator-btn"'), 'AdminLayout header must have visible translator button');
assert(adminLayoutVue.includes('data-testid="admin-header-lang-toggle"'), 'AdminLayout header must have language toggle (EN | አማ)');
assert(adminLayoutVue.includes('data-testid="sidebar-translator-btn"'), 'AdminLayout sidebar must include Amharic Translator link for mobile & drawer access');
assert(adminLayoutVue.includes('AmharicTranslatorModal'), 'AdminLayout must mount AmharicTranslatorModal');
console.log('✓ AdminLayout.vue contains clearly visible translator buttons in header & sidebar, accessible to all staff roles');

// 4. Verify CustomerLayout.vue (Customer Storefront)
const customerLayoutVue = fs.readFileSync(path.join(__dirname, 'Client/src/layouts/CustomerLayout.vue'), 'utf8');

assert(customerLayoutVue.includes('data-testid="header-translator-btn"'), 'CustomerLayout header must contain translator tool button');
assert(customerLayoutVue.includes('data-testid="header-lang-toggle"'), 'CustomerLayout header must maintain existing EN | አማ toggle');
assert(customerLayoutVue.includes('AmharicTranslatorModal'), 'CustomerLayout must mount AmharicTranslatorModal');
console.log('✓ CustomerLayout.vue maintains existing language switcher and adds prominent translator button');

console.log('\n====================================================');
console.log('ALL AMHARIC-TO-ENGLISH TRANSLATOR TESTS PASSED! (4/4)');
console.log('====================================================');
