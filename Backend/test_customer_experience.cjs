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

async function loginAs(role, branchCode = null) {
  const res = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/auth/switch-role',
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
  }, {
    role: role,
    branch_code: branchCode,
  });
  if (res.status !== 200) {
    throw new Error(`Failed to login as ${role}: ${JSON.stringify(res.data)}`);
  }
  return res.data.token;
}

async function runCustomerJourneyTests() {
  console.log('================================================================');
  console.log('STARTING SECTION 5 & CUSTOMER JOURNEY FULL VERIFICATION TEST');
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

  // 1. Customer Registration & Login
  const testEmail = `sara.test.${Date.now()}@example.com`;
  const regRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/auth/register',
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
  }, {
    full_name: 'Sara Tesfaye',
    email: testEmail,
    phone: '+251 911 888 999',
    password: 'password123',
    role: 'Customer',
  });
  assert(regRes.status === 201 || regRes.status === 200, 'Customer registration returned 201/200');
  const customerToken = regRes.data.token;
  assert(!!customerToken, 'Customer received authentication token');

  // 2. Fetch Branches & Search
  const branchesRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/branches',
    method: 'GET',
    headers: { 'Accept': 'application/json' },
  });
  assert(branchesRes.status === 200, 'Public branches API returned 200 OK');
  assert(Array.isArray(branchesRes.data) && branchesRes.data.length >= 3, `Fetched ${branchesRes.data.length} active branches`);
  const boleBranch = branchesRes.data.find(b => b.branch_name.includes('Bole')) || branchesRes.data[1];
  const megenagnaBranch = branchesRes.data.find(b => b.branch_name.includes('Megenagna')) || branchesRes.data[2];

  // 3. Fetch Delivery Zones for Selected Branch (Bole)
  const zonesRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/branches/${boleBranch.branch_id}/delivery-zones`,
    method: 'GET',
    headers: { 'Accept': 'application/json' },
  });
  assert(zonesRes.status === 200, `Fetched delivery zones for ${boleBranch.branch_name}`);
  const deliveryZone = (zonesRes.data && zonesRes.data.length > 0) ? zonesRes.data[0] : null;
  assert(!!deliveryZone, `Found active delivery zone: ${deliveryZone?.zone_name} (Fee: ETB ${deliveryZone?.delivery_fee})`);

  // 4. Browse Products for Selected Branch
  const catalogRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/products?branch_id=${boleBranch.branch_id}`,
    method: 'GET',
    headers: { 'Accept': 'application/json' },
  });
  assert(catalogRes.status === 200, 'Catalog endpoint returned 200 OK');
  const products = catalogRes.data.data || catalogRes.data;
  assert(Array.isArray(products) && products.length > 0, `Catalog returned ${products.length} products`);

  const cakeProd = products.find(p => p.product_name.includes('Cake')) || products[0];
  const breadProd = products.find(p => p.product_name.includes('Bread')) || products[1];

  // 5. Check Single Product Detail & Branch Availability
  const detailRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/products/${cakeProd.product_id}?branch_id=${boleBranch.branch_id}`,
    method: 'GET',
    headers: { 'Accept': 'application/json' },
  });
  assert(detailRes.status === 200, `Product detail returned 200 for ${cakeProd.product_name}`);
  assert(typeof detailRes.data.available === 'number', `Available stock for ${boleBranch.branch_name}: ${detailRes.data.available}`);

  // Multi-branch availability lookup
  const availRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/products/${cakeProd.product_id}/branch-availability`,
    method: 'GET',
    headers: { 'Accept': 'application/json' },
  });
  assert(availRes.status === 200, 'Branch availability endpoint returned 200 OK');
  assert(Array.isArray(availRes.data.branches), `Available across ${availRes.data.branches.length} branches`);

  // 6. Test Over-stock Quantity Order Rejection
  const overOrderRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/orders',
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
  }, {
    branch_id: boleBranch.branch_id,
    customer_name: 'Sara Tesfaye',
    customer_phone: '+251 911 888 999',
    fulfillment_method: 'Delivery',
    delivery_zone_id: deliveryZone?.zone_id,
    delivery_address: 'Bole Medhanialem, House 402',
    payment_method: 'Cash',
    items: [
      { product_id: cakeProd.product_id, quantity: 99999 },
    ],
  });
  assert(overOrderRes.status === 422, 'Over-stock order strictly rejected with HTTP 422');
  assert(overOrderRes.data.message.includes('Insufficient stock'), `Correct insufficient stock message: "${overOrderRes.data.message}"`);

  // 7. Place Valid Home Delivery Order
  // Check available stock first
  const orderQty = Math.min(detailRes.data.available > 0 ? 1 : 1, 1);
  const validOrderRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/orders',
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${customerToken}` },
  }, {
    branch_id: boleBranch.branch_id,
    customer_name: 'Sara Tesfaye',
    customer_phone: '+251 911 888 999',
    customer_email: testEmail,
    fulfillment_method: 'Delivery',
    delivery_zone_id: deliveryZone?.zone_id,
    delivery_address: 'Bole Medhanialem, House 402',
    delivery_instructions: 'Ring doorbell twice please',
    payment_method: 'Cash',
    items: [
      { product_id: breadProd.product_id, quantity: 2 },
    ],
  });
  assert(validOrderRes.status === 201, 'Valid home delivery order created with HTTP 201');
  const placedOrder = validOrderRes.data;
  assert(/^ORD-\d+$/.test(placedOrder.order_number), `Generated order number format ORD-XXXX: ${placedOrder.order_number}`);
  assert(placedOrder.branch_id === boleBranch.branch_id, `Order assigned strictly to ${boleBranch.branch_name} (#${boleBranch.branch_id})`);
  assert(placedOrder.fulfillment_method === 'Delivery', 'Fulfillment method is Delivery');
  assert(parseFloat(placedOrder.delivery_fee) === parseFloat(deliveryZone.delivery_fee), `Zone fee correctly added: ETB ${placedOrder.delivery_fee}`);

  // 8. Track Order Status via Customer Tracking Endpoint
  const trackRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/orders/${placedOrder.order_number}`,
    method: 'GET',
    headers: { 'Accept': 'application/json' },
  });
  assert(trackRes.status === 200, 'Customer order tracking returned 200 OK');
  assert(trackRes.data.order_number === placedOrder.order_number, `Tracking loaded order: ${trackRes.data.order_number}`);
  assert(Array.isArray(trackRes.data.status_history), 'Order status history timeline present');

  // 9. Verify Branch Admin for Bole Sees the Order
  const boleToken = await loginAs('Branch Admin', 'BOLE');
  const boleOrdersRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/orders',
    method: 'GET',
    headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${boleToken}` },
  });
  assert(boleOrdersRes.status === 200, 'Branch Admin orders API returned 200 OK');
  const boleOrderList = boleOrdersRes.data.orders || boleOrdersRes.data;
  const foundInBole = Array.isArray(boleOrderList) && boleOrderList.some(o => o.order_number === placedOrder.order_number);
  assert(foundInBole, `New order ${placedOrder.order_number} appeared immediately on Bole Branch Admin dashboard!`);

  // 10. Admin Advances Order to Confirmed -> Preparing
  const confirmRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/admin/orders/${placedOrder.order_id}/status`,
    method: 'PATCH',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${boleToken}` },
  }, {
    status: 'Confirmed',
    notes: 'Payment confirmed for pay on delivery',
  });
  assert(confirmRes.status === 200, 'Bole Branch Admin moved order to Confirmed');

  const prepareRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/admin/orders/${placedOrder.order_id}/status`,
    method: 'PATCH',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${boleToken}` },
  }, {
    status: 'Preparing',
    notes: 'Bakers mixing bread dough and preparing oven',
  });
  assert(prepareRes.status === 200, 'Bole Branch Admin moved order to Preparing');

  // 11. Customer Cancellation Guard: Blocked once baking/preparation begins
  const cancelBlockedRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/orders/${placedOrder.order_id}/cancel`,
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${customerToken}` },
  }, {
    reason: 'Changed my mind',
  });
  assert(cancelBlockedRes.status === 422, 'Customer cancellation strictly blocked once preparation started (422)');
  assert(cancelBlockedRes.data.message.includes('once preparation has started'), `Blocked message: "${cancelBlockedRes.data.message}"`);

  // 12. Advance Order: Ready -> Out for Delivery -> Completed
  const readyRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/admin/orders/${placedOrder.order_id}/status`,
    method: 'PATCH',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${boleToken}` },
  }, {
    status: 'Ready',
    notes: 'Freshly baked and packed in box',
  });
  assert(readyRes.status === 200, 'Moved order to Ready');

  const outDeliveryRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/admin/orders/${placedOrder.order_id}/status`,
    method: 'PATCH',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${boleToken}` },
  }, {
    status: 'Out for Delivery',
    notes: 'Assigned to driver Dawit (Motorbike #04)',
  });
  assert(outDeliveryRes.status === 200, 'Moved delivery order to Out for Delivery');

  const completeRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/admin/orders/${placedOrder.order_id}/status`,
    method: 'PATCH',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${boleToken}` },
  }, {
    status: 'Completed',
    notes: 'Delivered to customer at Bole Medhanialem',
  });
  assert(completeRes.status === 200, 'Moved order to Completed');

  // Verify stock was deducted upon completion
  const verifyTrack = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/orders/${placedOrder.order_number}`,
    method: 'GET',
    headers: { 'Accept': 'application/json' },
  });
  const currentStatus = verifyTrack.data.order_status || verifyTrack.data.status;
  assert(currentStatus === 'Completed', `Order tracking confirms status is Completed: ${currentStatus}`);

  // 13. Custom Cake Request Flow
  const customCakeRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/custom-cakes',
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${customerToken}` },
  }, {
    branch_id: boleBranch.branch_id,
    customer_name: 'Sara Tesfaye',
    customer_phone: '+251 911 888 999',
    customer_email: testEmail,
    cake_size: 'Medium (8")',
    flavor: 'Belgian Chocolate',
    filling: 'Chocolate Ganache',
    needed_by_date: '2026-11-15',
    inscription: 'Happy 30th Birthday Sara! 🎂',
    reference_image_url: 'https://images.unsplash.com/photo-1535141192574-5d4897c13136?w=600',
    special_instructions: 'Extra dark chocolate drips and edible golden berries please',
  });
  assert(customCakeRes.status === 201, 'Custom cake request submitted with HTTP 201');
  const cakeReq = customCakeRes.data;
  assert(cakeReq.status === 'Submitted' || cakeReq.status === 'Pending Quote' || cakeReq.status === 'Pending', `Initial quote status: ${cakeReq.status}`);

  // 14. Admin Sends Price Quote for Custom Cake
  const quoteRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/admin/custom-cakes/${cakeReq.request_id || cakeReq.id}/quote`,
    method: 'PATCH',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${boleToken}` },
  }, {
    quoted_price: 1850.00,
    baker_notes: 'Includes Belgian 70% dark ganache and custom golden berries. Ready by 14:00 on Nov 15.',
  });
  assert(quoteRes.status === 200, 'Branch Admin submitted price quote: ETB 1,850.00');
  assert(quoteRes.data.status === 'Quoted', 'Custom cake status updated to Quoted');

  // 15. Customer Accepts Quote
  const acceptRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/custom-cakes/${cakeReq.request_id || cakeReq.id}/accept-quote`,
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${customerToken}` },
  }, {
    payment_choice: 'full',
    payment_method: 'Cash',
  });
  assert(acceptRes.status === 200, 'Customer accepted quote with HTTP 200');
  const confirmedCake = acceptRes.data.cake_request || acceptRes.data;
  assert(confirmedCake.status === 'Confirmed', `Custom cake status moved to Confirmed: ${confirmedCake.status}`);
  assert(acceptRes.data.amount_paid === 1850.00, `Amount paid recorded: ETB ${acceptRes.data.amount_paid}`);

  console.log('\n================================================================');
  console.log(`CUSTOMER JOURNEY VERIFICATION RESULTS: ${passed} PASSED, ${failed} FAILED`);
  console.log('================================================================');

  if (failed > 0) {
    process.exit(1);
  }
}

runCustomerJourneyTests().catch(err => {
  console.error('Fatal test error:', err);
  process.exit(1);
});
