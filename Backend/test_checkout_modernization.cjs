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
  console.log('Testing Modernized Checkout & Payment Flow...\n');
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
    // 1. Authenticate / Register customer
    const email = `checkout_test_${Date.now()}@gmail.com`;
    const regRes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/auth/register',
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
    }, {
      full_name: 'Abebe Kebede',
      email: email,
      phone: '+251 911 234 567',
      password: 'password123',
      password_confirmation: 'password123',
    });

    const token = regRes.data?.token;
    assert(token, 'Customer registered and received token');

    const authHeaders = {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`,
    };

    // 2. Test Saved Addresses endpoint
    const saveAddrRes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/addresses',
      method: 'POST',
      headers: authHeaders,
    }, {
      street: 'Bole Medhanialem, Street 14, House 102',
      city: 'Addis Ababa',
      region: 'Bole',
      notes: 'Call before delivery',
      is_default: true,
    });
    assert(saveAddrRes.status === 201, 'Address saved via POST /api/addresses');
    const savedAddrId = saveAddrRes.data?.address?.address_id;

    const listAddrRes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/addresses',
      method: 'GET',
      headers: authHeaders,
    });
    assert(listAddrRes.data?.length > 0, `Retrieved ${listAddrRes.data?.length} saved address(es) for customer`);
    assert(listAddrRes.data?.[0]?.address_id === savedAddrId, 'Saved address matches created address ID');

    // 3. Test Order with Telebirr
    const telebirrRes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/orders',
      method: 'POST',
      headers: authHeaders,
    }, {
      branch_id: 2,
      fulfillment_method: 'Delivery',
      payment_method: 'Telebirr',
      address_id: savedAddrId,
      customer_name: 'Abebe Kebede',
      customer_phone: '+251 911 234 567',
      delivery_address: 'Bole Medhanialem, Street 14, House 102',
      items: [{ product_id: 1, quantity: 1 }],
    });
    if (telebirrRes.status !== 201) console.log('Telebirr failed:', telebirrRes.status, telebirrRes.data);
    assert(telebirrRes.status === 201, 'Telebirr delivery order created successfully');
    assert(telebirrRes.data?.order_number, `Order number: ${telebirrRes.data?.order_number}`);

    // Verify order details via GET /api/orders/{order_number}
    const orderDetails = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: `/api/orders/${telebirrRes.data?.order_number}`,
      method: 'GET',
      headers: authHeaders,
    });
    assert(orderDetails.data?.payment?.payment_method === 'Telebirr', `Payment method stored as Telebirr (got: ${orderDetails.data?.payment?.payment_method})`);
    assert(orderDetails.data?.payment?.transaction_reference?.startsWith('TB-'), `Telebirr transaction reference prefix TB-: ${orderDetails.data?.payment?.transaction_reference}`);

    // 4. Test Order with CBE Birr
    const cbeRes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/orders',
      method: 'POST',
      headers: authHeaders,
    }, {
      branch_id: 2,
      fulfillment_method: 'Delivery',
      payment_method: 'CBE Birr',
      customer_name: 'Abebe Kebede',
      customer_phone: '+251 911 234 567',
      delivery_address: 'Bole Olympia, Building 5',
      items: [{ product_id: 1, quantity: 1 }],
    });
    if (cbeRes.status !== 201) console.log('CBE failed:', cbeRes.status, cbeRes.data);
    assert(cbeRes.status === 201, 'CBE Birr delivery order created successfully');
    const cbeDetails = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: `/api/orders/${cbeRes.data?.order_number}`,
      method: 'GET',
      headers: authHeaders,
    });
    assert(cbeDetails.data?.payment?.payment_method === 'CBE Birr', `Payment method stored as CBE Birr (got: ${cbeDetails.data?.payment?.payment_method})`);
    assert(cbeDetails.data?.payment?.transaction_reference?.startsWith('CBE-'), `CBE Birr transaction reference prefix CBE-: ${cbeDetails.data?.payment?.transaction_reference}`);

    // 5. Test Order with Chapa
    const chapaRes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/orders',
      method: 'POST',
      headers: authHeaders,
    }, {
      branch_id: 2,
      fulfillment_method: 'Delivery',
      payment_method: 'Chapa',
      customer_name: 'Abebe Kebede',
      customer_phone: '+251 911 234 567',
      delivery_address: 'Bole Rwanda',
      items: [{ product_id: 1, quantity: 1 }],
    });
    if (chapaRes.status !== 201) console.log('Chapa failed:', chapaRes.status, chapaRes.data);
    assert(chapaRes.status === 201, 'Chapa card delivery order created successfully');
    const chapaDetails = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: `/api/orders/${chapaRes.data?.order_number}`,
      method: 'GET',
      headers: authHeaders,
    });
    assert(chapaDetails.data?.payment?.payment_method === 'Chapa', `Payment method stored as Chapa (got: ${chapaDetails.data?.payment?.payment_method})`);
    assert(chapaDetails.data?.payment?.transaction_reference?.startsWith('CH-'), `Chapa transaction reference prefix CH-: ${chapaDetails.data?.payment?.transaction_reference}`);

    // 6. Test Order with Pay on pickup
    const pickupRes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/orders',
      method: 'POST',
      headers: authHeaders,
    }, {
      branch_id: 2,
      fulfillment_method: 'Pickup',
      payment_method: 'Pay on pickup',
      customer_name: 'Abebe Kebede',
      customer_phone: '+251 911 234 567',
      delivery_address: 'Bole Branch Counter Pickup',
      items: [{ product_id: 1, quantity: 1 }],
    });
    if (pickupRes.status !== 201) console.log('Pickup failed:', pickupRes.status, pickupRes.data);
    assert(pickupRes.status === 201, 'Pay on pickup order created successfully');
    const pickupDetails = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: `/api/orders/${pickupRes.data?.order_number}`,
      method: 'GET',
      headers: authHeaders,
    });
    assert(pickupDetails.data?.fulfillment_method === 'Pickup', `Fulfillment method is Pickup (got: ${pickupDetails.data?.fulfillment_method})`);
    assert(Number(pickupDetails.data?.delivery_fee) === 0, `Pickup delivery fee is 0 (got: ${pickupDetails.data?.delivery_fee})`);
    assert(pickupDetails.data?.order_status === 'Confirmed', `Pay on pickup order initial status is Confirmed (got: ${pickupDetails.data?.order_status})`);

    console.log(`\n==================================================`);
    console.log(`CHECKOUT MODERNIZATION TESTS: ${passed} PASSED, ${failed} FAILED`);
    console.log(`==================================================`);
  } catch (err) {
    console.error('Test error:', err);
  }
}

run();
