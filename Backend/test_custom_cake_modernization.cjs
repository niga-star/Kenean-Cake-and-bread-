const http = require('http');

function request(options, body = null) {
  return new Promise((resolve, reject) => {
    const req = http.request(options, (res) => {
      let data = '';
      res.on('data', (chunk) => (data += chunk));
      res.on('end', () => {
        try {
          const parsed = JSON.parse(data);
          resolve({ status: res.statusCode, data: parsed });
        } catch (e) {
          resolve({ status: res.statusCode, raw: data });
        }
      });
    });
    req.on('error', reject);
    if (body) {
      req.write(typeof body === 'string' ? body : JSON.stringify(body));
    }
    req.end();
  });
}

async function run() {
  console.log('Testing Modernized Custom Cake Studio & Flow...\n');
  let passed = 0;
  let failed = 0;

  function assert(cond, msg) {
    if (cond) {
      console.log(`[PASS] ${msg}`);
      passed++;
    } else {
      console.error(`[FAIL] ${msg}`);
      failed++;
    }
  }

  try {
    // 1. Authenticate customer
    const email = `cake_studio_test_${Date.now()}@gmail.com`;
    const regRes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/auth/register',
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
    }, {
      full_name: 'Bethlehem Tadesse',
      email: email,
      phone: '+251 922 456 789',
      password: 'password123',
      password_confirmation: 'password123',
    });

    const token = regRes.data?.token;
    assert(token, 'Customer registered and received token');

    const authHeaders = {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`,
    };

    // 2. Submit Custom Cake Brief
    const neededDate = new Date(Date.now() + 86400000 * 4).toISOString().split('T')[0];
    const submitRes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/custom-cakes',
      method: 'POST',
      headers: authHeaders,
    }, {
      branch_id: 2, // Bole branch
      cake_size: 'Two-Tier Celebration (8" + 6")',
      flavor: 'Belgian Dark Chocolate with Silky Chocolate Ganache (100% Ye\'Tsom Fasting Certified)',
      needed_by: neededDate,
      design: 'Wedding cake. Minimalist smooth ivory fondant, hand-painted gold leaf veins, and white sugar peonies.',
      message: 'Happy Anniversary Mom & Dad! 🥂',
      customer_name: 'Bethlehem Tadesse',
      customer_phone: '+251 922 456 789',
      image_urls: ['/storage/custom_cakes/sample_wedding.jpg'],
    });

    assert(submitRes.status === 201, 'Custom cake request submitted with 201');
    const reqNumber = submitRes.data?.request_number || submitRes.data?.cake_request?.request_number;
    assert(reqNumber && reqNumber.startsWith('CC-'), `Custom cake request number generated: ${reqNumber}`);
    const reqId = submitRes.data?.request_id || submitRes.data?.cake_request?.request_id;

    // 3. Customer views submitted cake request in My Account list
    const listRes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/custom-cakes',
      method: 'GET',
      headers: authHeaders,
    });
    assert(listRes.status === 200, 'Customer retrieved custom cake requests list');
    const requests = listRes.data?.requests || [];
    const found = requests.find((r) => r.request_number === reqNumber);
    assert(found !== undefined, `Found submitted custom cake ${reqNumber} in customer account`);
    assert(found?.status === 'Submitted', 'Initial status is Submitted');
    assert(found?.message === 'Happy Anniversary Mom & Dad! 🥂', 'Piped inscription message saved correctly');

    // 4. Branch Decorator logs in and submits formal price quote
    const decoratorRes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/auth/switch-role',
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
    }, {
      role: 'Branch Admin',
      branch_code: 'BR001', // Bole
    });
    const decoratorToken = decoratorRes.data?.token;
    assert(decoratorToken, 'Head decorator authenticated for Bole branch');

    const quoteRes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: `/api/admin/custom-cakes/${reqId}/quote`,
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${decoratorToken}`,
      },
    }, {
      quoted_price: 3600.00,
      baker_notes: 'Includes handcrafted sugar peony flowers and 24k edible gold leaf gilding.',
    });
    assert(quoteRes.status === 200, 'Decorator posted price quotation of ETB 3,600');
    assert(quoteRes.data?.status === 'Quoted', 'Status transitioned to Quoted');

    // 5. Customer accepts quote (50% kitchen deposit)
    const acceptRes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: `/api/custom-cakes/${reqId}/accept-quote`,
      method: 'POST',
      headers: authHeaders,
    }, {
      payment_choice: 'full',
      payment_method: 'Telebirr',
    });
    assert(acceptRes.status === 200, 'Customer accepted quote with Telebirr deposit');
    const confirmedCake = acceptRes.data?.cake_request || acceptRes.data;
    assert(confirmedCake?.status === 'Confirmed', 'Custom cake status transitioned to Confirmed');

    console.log(`\n==================================================`);
    console.log(`CUSTOM CAKE MODERNIZATION TESTS: ${passed} PASSED, ${failed} FAILED`);
    console.log(`==================================================`);
  } catch (err) {
    console.error('Test error:', err);
  }
}

run();
