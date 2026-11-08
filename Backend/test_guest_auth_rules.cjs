const http = require('http');

function request(options, body = null) {
  return new Promise((resolve, reject) => {
    const req = http.request(options, (res) => {
      let data = '';
      res.on('data', (chunk) => (data += chunk));
      res.on('end', () => {
        try {
          const parsed = JSON.parse(data);
          resolve({ status: res.statusCode, headers: res.headers, data: parsed });
        } catch (e) {
          resolve({ status: res.statusCode, headers: res.headers, raw: data });
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

async function runGuestAuthRulesTests() {
  console.log('================================================================');
  console.log('GUEST BROWSING & AUTHENTICATION ENFORCEMENT VERIFICATION');
  console.log('================================================================\n');

  let passed = 0;
  let failed = 0;

  function assert(condition, message) {
    if (condition) {
      console.log(`[PASS] ${message}`);
      passed++;
    } else {
      console.error(`[FAIL] ${message}`);
      failed++;
    }
  }

  // ==========================================================================
  // RULE 1: Guests can view products, browse categories, search & view details
  // ==========================================================================
  console.log('\n--- 1. Guest Free Browsing (No Token Required) ---');

  const productsRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/catalog/products',
    method: 'GET',
    headers: { 'Accept': 'application/json' },
  });
  assert(productsRes.status === 200, 'Guest can view product catalog freely (HTTP 200)');
  assert(Array.isArray(productsRes.data) && productsRes.data.length > 0, `Catalog returned ${productsRes.data?.length} products`);

  const sampleProduct = productsRes.data[0];

  const categoriesRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/categories',
    method: 'GET',
    headers: { 'Accept': 'application/json' },
  });
  assert(categoriesRes.status === 200, 'Guest can view product categories freely (HTTP 200)');

  const searchRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/products?search=${encodeURIComponent(sampleProduct.product_name.slice(0, 4))}`,
    method: 'GET',
    headers: { 'Accept': 'application/json' },
  });
  assert(searchRes.status === 200, `Guest can search products freely (HTTP 200)`);
  assert(Array.isArray(searchRes.data) && searchRes.data.length > 0, `Search returned ${searchRes.data?.length} matches`);

  const detailRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/products/${sampleProduct.product_id}`,
    method: 'GET',
    headers: { 'Accept': 'application/json' },
  });
  assert(detailRes.status === 200, `Guest can read product details freely for "${sampleProduct.product_name}" (HTTP 200)`);

  const branchesRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/branches',
    method: 'GET',
    headers: { 'Accept': 'application/json' },
  });
  assert(branchesRes.status === 200, 'Guest can browse bakery branches freely (HTTP 200)');

  const reviewsRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/reviews/featured',
    method: 'GET',
    headers: { 'Accept': 'application/json' },
  });
  assert(reviewsRes.status === 200, 'Guest can browse customer testimonials freely (HTTP 200)');

  // ==========================================================================
  // RULE 2 & IMPORTANT: Never allow unauthenticated guest to place order via API
  // ==========================================================================
  console.log('\n--- 2. Unauthenticated Order Placement Rejection ---');

  const guestOrderRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/orders',
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
  }, {
    branch_id: 2,
    fulfillment_method: 'Pickup',
    payment_method: 'Pay on pickup',
    items: [{ product_id: sampleProduct.product_id, quantity: 1 }],
  });
  assert(guestOrderRes.status === 401, 'Unauthenticated order placement strictly rejected with HTTP 401');
  assert(
    guestOrderRes.data?.message && guestOrderRes.data.message.toLowerCase().includes('authentication required'),
    `Clear auth required message returned: "${guestOrderRes.data?.message}"`
  );

  const guestCustomCakeRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/custom-cakes',
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
  }, {
    branch_id: 2,
    cake_size: '8 inch',
    flavor: 'Chocolate',
    needed_by: '2026-12-01',
  });
  assert(guestCustomCakeRes.status === 401, 'Unauthenticated custom cake submission strictly rejected with HTTP 401');
  assert(
    guestCustomCakeRes.data?.message && guestCustomCakeRes.data.message.toLowerCase().includes('authentication required'),
    `Clear auth required message returned for custom cake: "${guestCustomCakeRes.data?.message}"`
  );

  // ==========================================================================
  // RULE 5: Failed Login / Sign Up displays error and does not allow proceed
  // ==========================================================================
  console.log('\n--- 3. Failed Login & Registration Error Handling ---');

  const failedLoginRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/auth/login',
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
  }, {
    email: 'nonexistent.user.123@gmail.com',
    password: 'wrongpassword',
  });
  assert(failedLoginRes.status === 401 || failedLoginRes.status === 422, 'Failed login returns error status (401/422)');
  assert(!failedLoginRes.data?.token, 'No authentication token issued on failed login');

  const failedRegRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/auth/register',
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
  }, {
    full_name: 'Sara999!', // Invalid: numbers & symbol
    email: 'invalid-email-format', // Invalid: not a valid email or gmail
    password: 'short',
  });
  assert(failedRegRes.status === 422, 'Invalid registration data strictly rejected with HTTP 422');
  assert(!failedRegRes.data?.token, 'No authentication token issued on invalid registration');
  assert(failedRegRes.data?.errors?.full_name || failedRegRes.data?.errors?.email, 'Validation errors returned for form fields');

  // ==========================================================================
  // RULE 4 & EXPECTED OUTCOMES: Successful Sign Up & Login
  // ==========================================================================
  console.log('\n--- 4. Successful Customer Registration & Login ---');

  const uniqueEmail = `guest.shopper.${Date.now()}@gmail.com`;
  const regRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/auth/register',
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
  }, {
    full_name: 'Bethlehem Tadesse',
    email: uniqueEmail,
    phone: '+251 912 345 678',
    password: 'SecurePassword123',
    role: 'Customer',
  });
  assert(regRes.status === 201, 'Customer account successfully created (HTTP 201)');
  const customerToken = regRes.data?.token;
  assert(!!customerToken, 'Customer received authentication token on sign up');
  assert(regRes.data?.user?.email === uniqueEmail, 'Registered user email matches');

  // Login with created credentials
  const loginRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/auth/login',
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
  }, {
    email: uniqueEmail,
    password: 'SecurePassword123',
  });
  assert(loginRes.status === 200, 'Customer login succeeded (HTTP 200)');
  const loggedInToken = loginRes.data?.token;
  assert(!!loggedInToken, 'Customer received token on login');

  // ==========================================================================
  // RULE 6: Authenticated customer places order and custom cake without interruption
  // ==========================================================================
  console.log('\n--- 5. Authenticated Customer Places Order Without Interruption ---');

  const authOrderRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/orders',
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': `Bearer ${loggedInToken}`,
    },
  }, {
    branch_id: 2,
    customer_name: 'Bethlehem Tadesse',
    customer_phone: '+251 912 345 678',
    customer_email: uniqueEmail,
    fulfillment_method: 'Pickup',
    payment_method: 'Pay on pickup',
    items: [
      { product_id: sampleProduct.product_id, quantity: 1 },
    ],
  });
  assert(authOrderRes.status === 201, 'Authenticated customer successfully placed order (HTTP 201)');
  assert(authOrderRes.data?.order_number && authOrderRes.data.order_number.startsWith('ORD-'), `Valid order number generated: ${authOrderRes.data?.order_number}`);
  assert(authOrderRes.data?.user_id === loginRes.data?.user?.user_id, 'Order is properly linked to customer account user_id');

  // Authenticated custom cake request
  const authCakeRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/custom-cakes',
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': `Bearer ${loggedInToken}`,
    },
  }, {
    branch_id: 2,
    customer_name: 'Bethlehem Tadesse',
    customer_phone: '+251 912 345 678',
    cake_size: 'Medium 8 Inch',
    flavor: 'Vanilla Bean & Berry',
    design: 'Floral Anniversary Cake',
    needed_by: '2026-11-20',
  });
  assert(authCakeRes.status === 201, 'Authenticated customer successfully submitted custom cake request (HTTP 201)');
  assert(authCakeRes.data?.request_number && authCakeRes.data.request_number.startsWith('CC-'), `Custom cake request number generated: ${authCakeRes.data?.request_number}`);

  // ==========================================================================
  // LOGOUT: Customer becomes a guest again and cannot place orders
  // ==========================================================================
  console.log('\n--- 6. Logout Behavior (Returns Customer to Guest State) ---');

  const logoutRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/auth/logout',
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': `Bearer ${loggedInToken}`,
    },
  });
  assert(logoutRes.status === 200, 'Customer logged out successfully (HTTP 200)');

  // Attempting to place order with revoked token
  const postLogoutOrderRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/orders',
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': `Bearer ${loggedInToken}`,
    },
  }, {
    branch_id: 2,
    fulfillment_method: 'Pickup',
    payment_method: 'Pay on pickup',
    items: [{ product_id: sampleProduct.product_id, quantity: 1 }],
  });
  assert(postLogoutOrderRes.status === 401, 'Order placement rejected after logout (HTTP 401)');

  console.log('\n================================================================');
  console.log(`GUEST AUTH RULES TEST SUITE RESULTS: ${passed} PASSED, ${failed} FAILED`);
  console.log('================================================================');

  if (failed > 0) {
    process.exit(1);
  }
}

runGuestAuthRulesTests().catch((err) => {
  console.error('Fatal test error:', err);
  process.exit(1);
});
