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

async function runSection10Tests() {
  console.log('================================================================');
  console.log('STARTING SECTION 10: REPORTS AND ANALYTICS VERIFICATION TEST');
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

  // 1. Role Access Control - Branch Staff blocked
  const staffToken = await loginAs('Branch Staff', 'BOLE');
  const staffSummaryRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/reports/summary',
    method: 'GET',
    headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${staffToken}` },
  });
  assert(staffSummaryRes.status === 403, 'Branch Staff strictly blocked from reports summary (403 Forbidden)');
  assert(staffSummaryRes.data.message && staffSummaryRes.data.message.includes('not permitted to view financial reports'), 'Staff blocked message verified');

  const staffDataRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/reports/data?type=branch_performance',
    method: 'GET',
    headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${staffToken}` },
  });
  assert(staffDataRes.status === 403, 'Branch Staff strictly blocked from reports data (403 Forbidden)');

  // 2. Role Access Control - Customer blocked
  const custRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/auth/register',
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
  }, {
    full_name: 'Reports Guard Test',
    email: `cust.rep.${Date.now()}@example.com`,
    password: 'password123',
    role: 'Customer',
  });
  const custToken = custRes.data.token;
  const custRepRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/reports/summary',
    method: 'GET',
    headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${custToken}` },
  });
  assert(custRepRes.status === 403, 'Customer strictly blocked from admin reports (403 Forbidden)');

  // 3. Branch Admin Scoping Rule
  const boleAdminToken = await loginAs('Branch Admin', 'BOLE');
  const boleSummaryRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/reports/summary',
    method: 'GET',
    headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${boleAdminToken}` },
  });
  assert(boleSummaryRes.status === 200, 'Branch Admin can access reports summary (200 OK)');
  assert(boleSummaryRes.data.revenue_by_branch.length === 1, 'Branch Admin summary restricted to 1 branch');
  assert(boleSummaryRes.data.revenue_by_branch[0].branch === 'Bole', `Restricted strictly to assigned branch: ${boleSummaryRes.data.revenue_by_branch[0].branch}`);

  // Test that passing ?branch_id=all is overridden on server for Branch Admin
  const spoofRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/reports/data?type=branch_performance&branch_id=all',
    method: 'GET',
    headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${boleAdminToken}` },
  });
  assert(spoofRes.status === 200, 'Branch Admin reports data returned 200 OK');
  assert(spoofRes.data.rows.length === 1 && spoofRes.data.rows[0].branch === 'Bole', 'Server-side enforcement: branch_id=all query parameter ignored for Branch Admin');

  // 4. Super Admin Full Access
  const superToken = await loginAs('Super Admin');
  const superSummaryRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/reports/summary',
    method: 'GET',
    headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${superToken}` },
  });
  assert(superSummaryRes.status === 200, 'Super Admin reports summary returned 200 OK');
  assert(typeof superSummaryRes.data.metrics.revenue === 'number', `Total revenue is real numeric: ETB ${superSummaryRes.data.metrics.revenue}`);
  assert(superSummaryRes.data.revenue_by_branch.length >= 3, `Super Admin views all active branches: ${superSummaryRes.data.revenue_by_branch.length} branches`);

  // 5. Verify All 9 Section 10 Reports via Super Admin
  const reportTypes = [
    { type: 'branch_performance', name: 'Branch performance', keyCol: 'Branch' },
    { type: 'product_performance', name: 'Product performance', keyCol: 'Product' },
    { type: 'distribution', name: 'Distribution', keyCol: 'Transfer' },
    { type: 'inventory', name: 'Inventory', keyCol: 'On Hand' },
    { type: 'waste', name: 'Waste', keyCol: 'Reason' },
    { type: 'discrepancy', name: 'Discrepancy', keyCol: 'Difference' },
    { type: 'delivery', name: 'Delivery', keyCol: 'Fulfillment Method' },
    { type: 'payments', name: 'Payments', keyCol: 'Payment Method' },
    { type: 'audit', name: 'Audit', keyCol: 'Sensitive Action' },
  ];

  for (const rep of reportTypes) {
    const res = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: `/api/admin/reports/data?type=${rep.type}`,
      method: 'GET',
      headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${superToken}` },
    });
    assert(res.status === 200, `Report "${rep.name}" returned 200 OK`);
    assert(Array.isArray(res.data.columns), `Report "${rep.name}" returned column definitions`);
    const hasKeyCol = res.data.columns.some(c => c.includes(rep.keyCol));
    assert(hasKeyCol, `Report "${rep.name}" has required key column "${rep.keyCol}"`);
    assert(Array.isArray(res.data.rows), `Report "${rep.name}" returned rows array (${res.data.rows.length} rows)`);
  }

  // 6. Test Filtering by Date Range
  const filteredDateRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/reports/data?type=delivery&from_date=2026-11-01&to_date=2026-11-06',
    method: 'GET',
    headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${superToken}` },
  });
  assert(filteredDateRes.status === 200, 'Delivery report with date filters returned 200 OK');
  assert(Array.isArray(filteredDateRes.data.rows), `Filtered rows returned: ${filteredDateRes.data.rows.length} rows`);

  // 7. Test Daily Sales and Low-Stock Email Summary Dispatch to Owner
  const summaryDispatchRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/reports/send-daily-summary',
    method: 'POST',
    headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${superToken}` },
  });
  assert(summaryDispatchRes.status === 200, 'Owner daily summary dispatch returned 200 OK');
  assert(summaryDispatchRes.data.summary.recipient === 'owner@sweetbakes.com', 'Recipient is owner@sweetbakes.com');
  assert(typeof summaryDispatchRes.data.summary.revenue_today === 'number', `Daily summary revenue: ETB ${summaryDispatchRes.data.summary.revenue_today}`);
  assert(typeof summaryDispatchRes.data.summary.low_stock_count === 'number', `Low stock alerts count: ${summaryDispatchRes.data.summary.low_stock_count}`);

  console.log('\n================================================================');
  console.log(`SECTION 10 VERIFICATION RESULTS: ${passed} PASSED, ${failed} FAILED`);
  console.log('================================================================');

  if (failed > 0) {
    process.exit(1);
  }
}

runSection10Tests().catch(err => {
  console.error('Fatal test error:', err);
  process.exit(1);
});
