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

async function runSection8Tests() {
  console.log('==================================================');
  console.log('STARTING SECTION 8: ORDER MANAGEMENT VERIFICATION');
  console.log('==================================================\n');

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

  // 1. Test Order Placement with Online Payment (Chapa)
  const placeRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/orders',
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
  }, {
    branch_id: 2, // Bole
    fulfillment_method: 'Pickup',
    payment_method: 'Chapa',
    items: [
      { product_id: 1, quantity: 2 }, // Chocolate Cake
    ],
  });

  assert(placeRes.status === 201, 'Online order created with HTTP 201');
  const order1 = placeRes.data;
  assert(order1.branch_id === 2, `Order assigned strictly to Bole branch (#${order1.branch_id})`);
  assert(order1.order_status === 'Pending', `Online order initial status is Pending: ${order1.order_status}`);
  assert(order1.payment && order1.payment.payment_status === 'Pending', `Payment record created with Pending status`);
  assert(order1.payment && /^CH-[A-F0-9]{6}$/.test(order1.payment.transaction_reference), `Chapa transaction reference generated: ${order1.payment?.transaction_reference}`);
  assert(order1.items[0].product_name === 'Chocolate Cake', `Order item stored product snapshot: ${order1.items[0].product_name}`);

  // 2. Test Stock Availability Guard (attempting to order more than branch has)
  const overStockRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/orders',
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
  }, {
    branch_id: 2,
    fulfillment_method: 'Pickup',
    payment_method: 'Chapa',
    items: [
      { product_id: 1, quantity: 99999 }, // Far exceeds available
    ],
  });
  assert(overStockRes.status === 422, 'Over-stock order rejected with HTTP 422');
  assert(
    overStockRes.data.message && overStockRes.data.message.includes('Insufficient stock'),
    `Insufficient stock message: "${overStockRes.data.message}"`
  );

  // 3. Test Signed Webhook Confirmation
  const txRef1 = order1.payment.transaction_reference;
  const webhookRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/payments/webhook',
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'x-chapa-signature': 'whsec_live_89f21a9chapa2026',
    },
  }, {
    tx_ref: txRef1,
    status: 'success',
  });
  assert(webhookRes.status === 200, 'Signed webhook processed with HTTP 200');
  assert(webhookRes.data.order_status === 'Confirmed', `Order moved from Pending to Confirmed on verified webhook: ${webhookRes.data.order_status}`);
  assert(webhookRes.data.payment_status === 'Paid', `Payment status moved to Paid: ${webhookRes.data.payment_status}`);

  // 4. Test Webhook Idempotency (duplicate callback ignored)
  const dupWebhookRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/payments/webhook',
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'x-chapa-signature': 'whsec_live_89f21a9chapa2026',
    },
  }, {
    tx_ref: txRef1,
    status: 'success',
  });
  assert(dupWebhookRes.status === 200, 'Duplicate webhook handled with HTTP 200');
  assert(
    dupWebhookRes.data.message && dupWebhookRes.data.message.includes('duplicate callback ignored'),
    `Idempotency verified: "${dupWebhookRes.data.message}"`
  );

  // 5. Test Status Transitions: Confirmed -> Preparing -> Ready
  const prepRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/admin/orders/${order1.order_id}/status`,
    method: 'PATCH',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
  }, {
    order_status: 'Preparing',
    note: 'Baker started ganache layering',
  });
  assert(prepRes.status === 200, 'Order moved to Preparing (200 OK)');
  assert(prepRes.data.order_status === 'Preparing', `Status is Preparing: ${prepRes.data.order_status}`);

  const readyRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/admin/orders/${order1.order_id}/status`,
    method: 'PATCH',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
  }, {
    order_status: 'Ready',
    note: 'Packed in display box with ribbon',
  });
  assert(readyRes.status === 200, 'Order moved to Ready (200 OK)');

  // 6. Test Out-For-Delivery restriction on Pickup order
  const invalidDeliveryRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/admin/orders/${order1.order_id}/status`,
    method: 'PATCH',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
  }, {
    order_status: 'Out for delivery',
  });
  assert(invalidDeliveryRes.status === 422, 'Out for delivery blocked for Pickup order (422)');
  assert(
    invalidDeliveryRes.data.message && invalidDeliveryRes.data.message.includes('only valid for Delivery orders'),
    `Correct restriction error: "${invalidDeliveryRes.data.message}"`
  );

  // 7. Complete Order (Physical stock deduction)
  const completeRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/admin/orders/${order1.order_id}/status`,
    method: 'PATCH',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
  }, {
    order_status: 'Completed',
    note: 'Customer picked up at counter',
  });
  assert(completeRes.status === 200, 'Order Completed (200 OK)');
  assert(completeRes.data.order_status === 'Completed', `Order is Completed: ${completeRes.data.order_status}`);

  // 8. Test Customer Cancellation when Pending/Confirmed vs Blocked when Preparing
  // Create order 2
  const placeRes2 = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/orders',
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
  }, {
    branch_id: 2,
    fulfillment_method: 'Pickup',
    payment_method: 'Pay on pickup',
    items: [
      { product_id: 2, quantity: 4 }, // White Bread
    ],
  });
  assert(placeRes2.status === 201, 'Order 2 created with Pay on pickup (201)');
  const order2 = placeRes2.data;

  // Cancel order 2 while Confirmed (Allowed)
  const cancelRes2 = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/orders/${order2.order_number}/cancel`,
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
  }, {
    reason: 'Customer cancelled prior to baking',
  });
  assert(cancelRes2.status === 200, 'Customer cancelled Confirmed order (200 OK)');
  assert(cancelRes2.data.order.order_status === 'Cancelled', `Order status is Cancelled: ${cancelRes2.data.order.order_status}`);

  // Create order 3 and advance to Preparing
  const placeRes3 = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/orders',
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
  }, {
    branch_id: 2,
    fulfillment_method: 'Pickup',
    payment_method: 'Pay on pickup',
    items: [
      { product_id: 2, quantity: 2 },
    ],
  });
  const order3 = placeRes3.data;
  await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/admin/orders/${order3.order_id}/status`,
    method: 'PATCH',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
  }, { order_status: 'Preparing' });

  // Customer tries to cancel while in Preparing (MUST FAIL)
  const cancelBlockedRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/orders/${order3.order_number}/cancel`,
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
  });
  assert(cancelBlockedRes.status === 422, 'Cancellation blocked once preparation started (422)');
  assert(
    cancelBlockedRes.data.message && cancelBlockedRes.data.message.includes('blocked once preparation has started'),
    `Cancellation blocked message: "${cancelBlockedRes.data.message}"`
  );

  // 9. Test Super Admin Branch Reassignment
  const placeRes4 = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/orders',
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
  }, {
    branch_id: 2, // Bole
    fulfillment_method: 'Pickup',
    payment_method: 'Pay on pickup',
    items: [
      { product_id: 2, quantity: 3 },
    ],
  });
  const order4 = placeRes4.data;

  const reassignRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/admin/orders/${order4.order_id}/branch`,
    method: 'PATCH',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
  }, {
    new_branch_id: 3, // Reassign to Megenagna
    reason: 'Equipment failure at Bole; customer consented to Megenagna fulfillment',
  });
  assert(reassignRes.status === 200, 'Super Admin reassign order branch returned 200 OK');
  assert(reassignRes.data.branch_id === 3, `Order reassigned to Megenagna (branch_id: ${reassignRes.data.branch_id})`);

  // Verify status history recorded the branch shift
  const historyRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/orders/${order4.order_number}`,
    method: 'GET',
    headers: { 'Accept': 'application/json' },
  });
  const hist = historyRes.data.status_history || historyRes.data.statusHistory || [];
  const reassignNote = hist.find((h) => h.note && h.note.includes('Branch reassigned'));
  assert(reassignNote !== undefined, `Order status history logged branch reassignment: "${reassignNote?.note}"`);

  // 10. Test Refund Issue via PaymentController
  // Create paid order
  const placeRes5 = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/orders',
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
  }, {
    branch_id: 2,
    fulfillment_method: 'Pickup',
    payment_method: 'Chapa',
    items: [
      { product_id: 2, quantity: 2 },
    ],
  });
  const order5 = placeRes5.data;
  // Confirm via webhook
  await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/payments/webhook',
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'x-chapa-signature': 'whsec_live_89f21a9chapa2026',
    },
  }, {
    tx_ref: order5.payment.transaction_reference,
    status: 'success',
  });

  const refundRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/payments/refund',
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
  }, {
    order_number: order5.order_number,
    amount: order5.total_amount,
    reason: 'Damaged batch, customer chose full refund',
  });
  assert(refundRes.status === 200, 'Refund issued with HTTP 200');
  assert(refundRes.data.payment.payment_status === 'Refunded', `Payment marked Refunded: ${refundRes.data.payment.payment_status}`);
  assert(refundRes.data.order.order_status === 'Cancelled', `Order moved to Cancelled on refund: ${refundRes.data.order.order_status}`);

  console.log('\n==================================================');
  console.log(`SECTION 8 VERIFICATION RESULTS: ${passed} PASSED, ${failed} FAILED`);
  console.log('==================================================');

  if (failed > 0) process.exit(1);
}

runSection8Tests().catch((err) => {
  console.error('Fatal test error:', err);
  process.exit(1);
});
