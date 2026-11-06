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

async function runTests() {
  console.log('====================================================');
  console.log('STARTING SECTION 7: STOCK TRANSFER VERIFICATION TEST');
  console.log('====================================================\n');

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

  // 1. Fetch Dashboard Metrics
  const dashRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/transfers/dashboard',
    method: 'GET',
    headers: { 'Accept': 'application/json' },
  });
  assert(dashRes.status === 200, 'Transfers dashboard returned 200 OK');
  assert(typeof dashRes.data.main_branch_stock === 'number', `Main branch stock is real numeric: ${dashRes.data.main_branch_stock}`);
  assert(Array.isArray(dashRes.data.latest_transfers), `Latest transfers array present: ${dashRes.data.latest_transfers.length} branches`);

  // 2. Test Transfer Creation under threshold (200 units) -> Auto-Approved
  const underThresholdRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/transfers',
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
  }, {
    destination_branch_id: 2, // Bole
    notes: 'Integration test regular replenishment',
    items: [
      { product_id: 2, quantity_sent: 50 }, // White bread
    ],
  });
  assert(underThresholdRes.status === 201, 'Created transfer under threshold returned 201');
  const tUnder = underThresholdRes.data;
  assert(/^TRF-\d{4}-\d{4}$/.test(tUnder.transfer_number), `Transfer number formatted TRF-YYYY-XXXX: ${tUnder.transfer_number}`);
  assert(tUnder.status === 'Approved', `Total 50 units <= 200 auto-approved: status is ${tUnder.status}`);

  // 3. Test Transfer Creation over threshold (> 200 units) -> Draft status
  const overThresholdRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/transfers',
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
  }, {
    destination_branch_id: 3, // Megenagna
    notes: 'Large shipment requiring Super Admin approval',
    items: [
      { product_id: 2, quantity_sent: 250 },
    ],
  });
  assert(overThresholdRes.status === 201, 'Created transfer over threshold returned 201');
  const tOver = overThresholdRes.data;
  assert(tOver.status === 'Draft', `Total 250 units > 200 requires approval: status is ${tOver.status}`);

  // 4. Test Super Admin Approval on Draft Transfer
  const approveRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/admin/transfers/${tOver.transfer_id}/approve`,
    method: 'POST',
    headers: { 'Accept': 'application/json' },
  });
  assert(approveRes.status === 200, 'Approve endpoint returned 200 OK');
  assert(approveRes.data.status === 'Approved', `Transfer moved from Draft to Approved: status is ${approveRes.data.status}`);

  // 5. Test Pre-dispatch Cancellation
  const cancelDraftRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/admin/transfers/${tOver.transfer_id}/cancel`,
    method: 'POST',
    headers: { 'Accept': 'application/json' },
  });
  assert(cancelDraftRes.status === 200, 'Cancel approved transfer before dispatch returned 200');
  assert(cancelDraftRes.data.status === 'Cancelled', `Transfer status changed to Cancelled: ${cancelDraftRes.data.status}`);

  // 6. Test Dispatch with Stock Reduction
  const dashBeforeDispatch = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/transfers/dashboard',
    method: 'GET',
    headers: { 'Accept': 'application/json' },
  });
  const stockBefore = dashBeforeDispatch.data.main_branch_stock;

  const dispatchRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/admin/transfers/${tUnder.transfer_id}/dispatch`,
    method: 'POST',
    headers: { 'Accept': 'application/json' },
  });
  assert(dispatchRes.status === 200, 'Dispatch transfer returned 200 OK');
  assert(dispatchRes.data.status === 'In transit', `Transfer status changed to In transit: ${dispatchRes.data.status}`);

  const dashAfterDispatch = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/transfers/dashboard',
    method: 'GET',
    headers: { 'Accept': 'application/json' },
  });
  const stockAfter = dashAfterDispatch.data.main_branch_stock;
  assert(stockAfter === stockBefore - 50, `Main branch stock dropped by exactly 50 units (from ${stockBefore} to ${stockAfter})`);

  // 7. Test Dispatched Transfer CANNOT be cancelled rule
  const cancelDispatchedRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/admin/transfers/${tUnder.transfer_id}/cancel`,
    method: 'POST',
    headers: { 'Accept': 'application/json' },
  });
  assert(cancelDispatchedRes.status === 422, 'Cancelling dispatched transfer was strictly blocked (422 Unprocessable)');
  assert(
    cancelDispatchedRes.data.message && cancelDispatchedRes.data.message.includes('dispatched transfer cannot be cancelled'),
    `Correct error message returned: "${cancelDispatchedRes.data.message}"`
  );

  // 8. Test Receiving Line Validation: Received + Damaged + Missing != Sent
  const transferItem = dispatchRes.data.items[0];
  const invalidReceiveRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/admin/transfers/${tUnder.transfer_id}/receive`,
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
  }, {
    items: [
      {
        transfer_item_id: transferItem.transfer_item_id,
        quantity_received: 40,
        damaged_quantity: 0,
        missing_quantity: 5, // 40 + 0 + 5 = 45 != 50
        discrepancy_reason: 'Count mismatch',
      },
    ],
  });
  assert(invalidReceiveRes.status === 422, 'Strict validation blocked receive when R + D + M (45) != Sent (50) (422)');
  assert(
    invalidReceiveRes.data.message && invalidReceiveRes.data.message.includes('must equal sent'),
    `Validation error message: "${invalidReceiveRes.data.message}"`
  );

  // 9. Test Discrepancy without Reason
  const noReasonReceiveRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/admin/transfers/${tUnder.transfer_id}/receive`,
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
  }, {
    items: [
      {
        transfer_item_id: transferItem.transfer_item_id,
        quantity_received: 45,
        damaged_quantity: 0,
        missing_quantity: 5,
        discrepancy_reason: '', // Empty reason!
      },
    ],
  });
  assert(noReasonReceiveRes.status === 422, 'Discrepancy without reason was strictly blocked (422)');

  // 10. Test Valid Receiving with Discrepancy (45 received, 5 missing, reason given)
  const validDiscrepancyRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/admin/transfers/${tUnder.transfer_id}/receive`,
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
  }, {
    items: [
      {
        transfer_item_id: transferItem.transfer_item_id,
        quantity_received: 45,
        damaged_quantity: 0,
        missing_quantity: 5,
        discrepancy_reason: '5 loaves missing from delivery crate',
      },
    ],
  });
  assert(validDiscrepancyRes.status === 200, 'Valid receipt with discrepancy confirmed (200 OK)');
  assert(validDiscrepancyRes.data.status === 'Disputed', `Transfer status changed to Disputed: ${validDiscrepancyRes.data.status}`);

  // 11. Test Dispute Resolution: 'found' (missing 5 units located and added to destination stock)
  const resolveFoundRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/admin/transfers/${tUnder.transfer_id}/resolve`,
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
  }, {
    resolution: 'found',
    notes: 'Missing 5 loaves found in adjacent storage rack at Bole branch',
  });
  assert(resolveFoundRes.status === 200, 'Resolve dispute returned 200 OK');
  assert(resolveFoundRes.data.status === 'Received', `After dispute resolution, transfer is Received: ${resolveFoundRes.data.status}`);

  // 12. Test Reminder Endpoint
  const remindRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/admin/transfers/4/remind`, // Transfer 4 is In transit to Megenagna
    method: 'POST',
    headers: { 'Accept': 'application/json' },
  });
  assert(remindRes.status === 200 || remindRes.status === 422, `Remind endpoint handled cleanly: ${remindRes.status}`);

  // 13. Test Stock Request Creation and Conversion
  const createStockReqRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/stock-requests',
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
  }, {
    branch_id: 2, // Bole
    items: [
      { product_id: 2, quantity_requested: 35 },
    ],
  });
  assert(createStockReqRes.status === 201, 'Created pending branch stock request (201)');
  const newReqId = createStockReqRes.data.request_id;

  const stockReqRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/admin/stock-requests/${newReqId}/convert`,
    method: 'POST',
    headers: { 'Accept': 'application/json' },
  });
  assert(stockReqRes.status === 200, `Stock request #${newReqId} successfully converted into transfer: status is 200`);
  assert(
    stockReqRes.data.transfer_number && /^TRF-\d{4}-\d{4}$/.test(stockReqRes.data.transfer_number),
    `Converted transfer auto-numbered: ${stockReqRes.data.transfer_number}`
  );

  console.log('\n====================================================');
  console.log(`SECTION 7 VERIFICATION RESULTS: ${passed} PASSED, ${failed} FAILED`);
  console.log('====================================================');

  if (failed > 0) process.exit(1);
}

runTests().catch((err) => {
  console.error('Fatal test error:', err);
  process.exit(1);
});
