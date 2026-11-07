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
  console.log('Testing Wishlist & Favorites Persistence Across Refresh...\n');
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
    const email = `wishlist_test_${Date.now()}@gmail.com`;
    const regRes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/auth/register',
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
    }, {
      full_name: 'Sara Mengistu',
      email: email,
      phone: '+251 911 345 678',
      password: 'password123',
      password_confirmation: 'password123',
    });

    const token = regRes.data?.token;
    assert(token, 'Customer registered and received token');

    const authHeaders = {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`,
    };

    // 2. Toggle item 1 into wishlist
    const toggleRes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/wishlist/toggle',
      method: 'POST',
      headers: authHeaders,
    }, {
      product_id: 1,
    });
    assert(toggleRes.status === 200, 'POST /api/wishlist/toggle returned 200');
    assert(toggleRes.data?.wishlisted === true, 'Product 1 was added to wishlist');
    assert(toggleRes.data?.product_ids?.includes(1), 'Product 1 ID is present in returned wishlist IDs');

    // 3. Emulate page refresh: fetch GET /api/wishlist with token
    const fetchRes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/wishlist',
      method: 'GET',
      headers: authHeaders,
    });
    assert(fetchRes.status === 200, 'GET /api/wishlist on page refresh returned 200');
    assert(fetchRes.data?.product_ids?.includes(1), 'Product 1 is preserved in database after refresh');
    assert(fetchRes.data?.products?.length > 0, 'Product details returned for favorites page');

    // 4. Test Sync of guest wishlist IDs (e.g. items clicked prior to login or across devices)
    const syncRes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/wishlist/sync',
      method: 'POST',
      headers: authHeaders,
    }, {
      product_ids: [2, 3],
    });
    assert(syncRes.status === 200, 'POST /api/wishlist/sync returned 200');
    assert(syncRes.data?.product_ids?.includes(1), 'Original product 1 retained after sync');
    assert(syncRes.data?.product_ids?.includes(2), 'Synced product 2 added');
    assert(syncRes.data?.product_ids?.includes(3), 'Synced product 3 added');

    // 5. Test Guest Products endpoint (when user is unauthenticated guest)
    const guestRes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/wishlist/guest-products',
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
    }, {
      product_ids: [1, 2],
    });
    assert(guestRes.status === 200, 'POST /api/wishlist/guest-products returned 200 for guest');
    assert(guestRes.data?.products?.length === 2, 'Returned 2 full product cards for guest favorites view');

    // 6. Toggle to remove item 1
    const removeRes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/wishlist/toggle',
      method: 'POST',
      headers: authHeaders,
    }, {
      product_id: 1,
    });
    assert(removeRes.status === 200, 'Remove toggle returned 200');
    assert(removeRes.data?.wishlisted === false, 'Product 1 is un-wishlisted');
    assert(!removeRes.data?.product_ids?.includes(1), 'Product 1 removed from active wishlist IDs');

    console.log(`\n==================================================`);
    console.log(`WISHLIST PERSISTENCE TESTS: ${passed} PASSED, ${failed} FAILED`);
    console.log(`==================================================`);
  } catch (err) {
    console.error('Test error:', err);
  }
}

run();

