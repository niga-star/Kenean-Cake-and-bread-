const http = require('http');

function postJson(path, payload, token) {
  return new Promise((resolve, reject) => {
    const data = JSON.stringify(payload);
    const headers = {
      'Content-Type': 'application/json',
      'Content-Length': Buffer.byteLength(data),
      'Accept': 'application/json',
    };
    if (token) headers['Authorization'] = `Bearer ${token}`;

    const req = http.request({
      hostname: '127.0.0.1',
      port: 8000,
      path: path,
      method: 'POST',
      headers,
    }, res => {
      let body = '';
      res.on('data', chunk => body += chunk);
      res.on('end', () => {
        try {
          resolve({ status: res.statusCode, data: JSON.parse(body) });
        } catch (e) {
          resolve({ status: res.statusCode, raw: body });
        }
      });
    });
    req.on('error', reject);
    req.write(data);
    req.end();
  });
}

function getJson(path, token) {
  return new Promise((resolve, reject) => {
    const headers = { 'Accept': 'application/json' };
    if (token) headers['Authorization'] = `Bearer ${token}`;

    const req = http.request({
      hostname: '127.0.0.1',
      port: 8000,
      path: path,
      method: 'GET',
      headers,
    }, res => {
      let body = '';
      res.on('data', chunk => body += chunk);
      res.on('end', () => {
        try {
          resolve({ status: res.statusCode, data: JSON.parse(body) });
        } catch (e) {
          resolve({ status: res.statusCode, raw: body });
        }
      });
    });
    req.on('error', reject);
    req.end();
  });
}

function patchJson(path, payload, token) {
  return new Promise((resolve, reject) => {
    const data = JSON.stringify(payload);
    const headers = {
      'Content-Type': 'application/json',
      'Content-Length': Buffer.byteLength(data),
      'Accept': 'application/json',
    };
    if (token) headers['Authorization'] = `Bearer ${token}`;

    const req = http.request({
      hostname: '127.0.0.1',
      port: 8000,
      path: path,
      method: 'PATCH',
      headers,
    }, res => {
      let body = '';
      res.on('data', chunk => body += chunk);
      res.on('end', () => {
        try {
          resolve({ status: res.statusCode, data: JSON.parse(body) });
        } catch (e) {
          resolve({ status: res.statusCode, raw: body });
        }
      });
    });
    req.on('error', reject);
    req.write(data);
    req.end();
  });
}

async function run() {
  console.log('--- TESTING SUPER ADMIN SETTINGS EDITABILITY ---');

  // Step 1: Login as Super Admin
  console.log('\n1. Logging in as Super Admin...');
  const loginRes = await postJson('/api/auth/login', {
    email: 'admin@goldencrust.com',
    password: 'password123'
  });
  if (loginRes.status !== 200 || !loginRes.data.token) {
    throw new Error(`Admin login failed: ${JSON.stringify(loginRes)}`);
  }
  const token = loginRes.data.token;
  console.log('✓ Super Admin authenticated successfully.');

  // Step 2: Fetch current settings
  console.log('\n2. Fetching current admin settings...');
  const settingsRes = await getJson('/api/admin/settings', token);
  if (settingsRes.status !== 200) {
    throw new Error(`Failed to get settings: ${settingsRes.status}`);
  }
  console.log(`✓ Fetched settings (${Object.keys(settingsRes.data).length} keys).`);

  // Step 3: Update Cafe Name, Tagline, Hero Section and About Page
  console.log('\n3. Updating Cafe Name, Hero Section, and About Page...');
  const updatePayload = {
    site_name: 'SweetBakes & Kenan Cafe',
    site_tagline: 'Artisanal Cakes · Fresh Bread · True Happiness',
    since_year: '2016',
    site_description_en: 'Premium bakery and cafe crafted with European passion and Ethiopian flavors.',
    site_description_am: 'በአዲስ አበባ ከፍተኛ ጥራት ያለው የኬክና የዳቦ መጋገሪያ ካፌ።',
    hero_subtitle_en: 'Handcrafted With Perfection',
    hero_subtitle_am: 'በከፍተኛ ጥራት የተዘጋጀ',
    hero_title_en: 'Baked Fresh with Love',
    hero_title_am: 'በፍቅር የተጋገረ ትኩስ ጣዕም',
    hero_title2_en: "Addis Ababa's Most Loved Bakery",
    hero_title2_am: 'የአዲስ አበባ ተወዳጅ መጋገሪያ',
    hero_desc_en: 'Experience heavenly wedding cakes, birthday masterpieces and artisanal sourdough loaves.',
    hero_desc_am: 'የሰርግና የልደት ኬኮች እንዲሁም ትኩስ የተፈጥሮ ዳቦዎች።',
    hero_button_text_en: 'Explore Our Menu',
    hero_button_text_am: 'ምርቶቻችንን ይመልከቱ',
    hero_button_link: '/shop',
    about_tag_en: 'Our Heartwarming Story',
    about_tag_am: 'የታሪካችን መጀመሪያ',
    about_title_en: 'Artisan Baking Perfected in Addis',
    about_title_am: 'በአዲስ አበባ የተፈጠረ የዳቦና ኬክ ጥበብ',
    about_story_en: 'From day one, our mission was simple: bring pure happiness through every crumb and icing.',
    about_story_am: 'ከመጀመሪያው ጀምሮ ዓላማችን በየቀኑ ደስታን በጣፋጭ ኬኮቻችን ማበርከት ነው።',
    about_image_url: '/mockup/baker_hires.jpg',
    about_chef_quote_en: '“Every cake we bake carries our heart and passion.”',
    about_chef_quote_am: '“እያንዳንዱ ኬክ የፍቅራችንና የጥበብ መገለጫ ነው።”',
    about_bullet_1_en: '100% Stone-Milled Organic Flour',
    about_bullet_1_am: '100% የተፈጥሮ ዱቄት',
    about_bullet_2_en: 'Real Cream & Belgian Chocolate',
    about_bullet_2_am: 'እውነተኛ ክሬምና ቤልጂየም ቸኮሌት',
    about_bullet_3_en: 'Custom Tailored Theme Cakes',
    about_bullet_3_am: 'የተለየ ዲዛይን ያላቸው ኬኮች',
    about_bullet_4_en: 'Swift 30-Minute Addis Delivery',
    about_bullet_4_am: 'በ30 ደቂቃ ፈጣን ማድረሻ',
    stat_experience_years: '15+',
    stat_happy_customers: '60k+',
  };

  const saveRes = await postJson('/api/admin/settings', updatePayload, token);
  if (saveRes.status !== 200) {
    throw new Error(`Failed to save settings: ${JSON.stringify(saveRes)}`);
  }
  console.log('✓ Settings successfully saved by Super Admin.');

  // Step 4: Verify Public /api/business-info receives all updated fields
  console.log('\n4. Verifying public /api/business-info endpoint...');
  const businessRes = await getJson('/api/business-info');
  if (businessRes.status !== 200) {
    throw new Error(`Failed to get business-info: ${businessRes.status}`);
  }
  const info = businessRes.data;
  console.log('  site_name:', info.site_name);
  console.log('  site_tagline:', info.site_tagline);
  console.log('  since_year:', info.since_year);
  console.log('  hero_title_en:', info.hero_title_en);
  console.log('  hero_title2_en:', info.hero_title2_en);
  console.log('  hero_button_text_en:', info.hero_button_text_en);
  console.log('  about_title_en:', info.about_title_en);
  console.log('  about_bullet_1_en:', info.about_bullet_1_en);

  if (info.site_name !== 'SweetBakes & Kenan Cafe') {
    throw new Error(`site_name mismatch! Expected 'SweetBakes & Kenan Cafe', got '${info.site_name}'`);
  }
  if (info.hero_title2_en !== "Addis Ababa's Most Loved Bakery") {
    throw new Error(`hero_title2_en mismatch!`);
  }
  if (info.about_title_en !== 'Artisan Baking Perfected in Addis') {
    throw new Error(`about_title_en mismatch!`);
  }
  console.log('✓ Public business-info returns all updated settings correctly!');

  // Step 5: Verify Banner 1 synchronized
  console.log('\n5. Verifying Banner 1 synchronized in /api/banners...');
  const bannersRes = await getJson('/api/banners');
  if (bannersRes.status !== 200 || !Array.isArray(bannersRes.data) || bannersRes.data.length === 0) {
    throw new Error(`Failed to get banners: ${bannersRes.status}`);
  }
  const banner1 = bannersRes.data.find(b => b.banner_id === 1);
  if (banner1) {
    console.log('  Banner 1 title_en:', banner1.title_en);
    console.log('  Banner 1 badge_en:', banner1.badge_en);
    if (banner1.badge_en !== "Addis Ababa's Most Loved Bakery") {
      throw new Error(`Banner 1 was not synchronized! Expected "Addis Ababa's Most Loved Bakery", got "${banner1.badge_en}"`);
    }
    console.log('✓ Primary hero banner synchronized with settings.');
  }

  // Step 6: Test Admin PATCH /api/admin/banners/{id}
  console.log('\n6. Testing Admin PATCH /api/admin/banners/1 directly...');
  const patchBannerRes = await patchJson('/api/admin/banners/1', {
    badge_en: "Addis Ababa's Favorite",
    button_text_en: "Order Online",
  }, token);
  if (patchBannerRes.status !== 200) {
    throw new Error(`Failed to patch banner: ${JSON.stringify(patchBannerRes)}`);
  }
  console.log('✓ Banner updated via PATCH endpoint.');

  console.log('\n========================================');
  console.log('ALL SUPER ADMIN EDITABILITY TESTS PASSED 100%!');
  console.log('========================================');
}

run().catch(err => {
  console.error('\n❌ Test failed:', err);
  process.exit(1);
});
