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
    throw new Error(`Failed to login as ${role} (${branchCode}): ${JSON.stringify(res.data)}`);
  }
  return res.data.token;
}

async function runSection9Tests() {
  console.log('==================================================');
  console.log('STARTING SECTION 9: DASHBOARDS VERIFICATION');
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

  // 1. Super Admin: Central Dashboard
  const superToken = await loginAs('Super Admin');
  const centralRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/overview',
    method: 'GET',
    headers: { 'Authorization': `Bearer ${superToken}`, 'Accept': 'application/json' },
  });

  assert(centralRes.status === 200, 'Super Admin central overview returned HTTP 200');
  assert(centralRes.data.is_branch_view === false, 'Central dashboard flag is_branch_view === false');
  assert(typeof centralRes.data.revenue_today === 'number', `Real revenue_today numeric: ${centralRes.data.revenue_today}`);
  assert(centralRes.data.active_branches >= 4, `Active branches count: ${centralRes.data.active_branches}`);
  assert(Array.isArray(centralRes.data.sales_by_branch), `Sales by branch array present (${centralRes.data.sales_by_branch.length} branches)`);
  assert(Array.isArray(centralRes.data.branches_today), `Branches today table present (${centralRes.data.branches_today.length} branches)`);

  // Super Admin can access Settings, Audit Log, Branches
  const settingsRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/settings',
    method: 'GET',
    headers: { 'Authorization': `Bearer ${superToken}`, 'Accept': 'application/json' },
  });
  assert(settingsRes.status === 200, 'Super Admin can access Settings (200 OK)');

  const auditRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/audit-logs',
    method: 'GET',
    headers: { 'Authorization': `Bearer ${superToken}`, 'Accept': 'application/json' },
  });
  assert(auditRes.status === 200, 'Super Admin can access Audit Logs (200 OK)');

  // 2. Main Branch Manager: Central Dashboard
  const mainMgrToken = await loginAs('Main Branch Manager', 'MAIN01');
  const mainMgrRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/overview',
    method: 'GET',
    headers: { 'Authorization': `Bearer ${mainMgrToken}`, 'Accept': 'application/json' },
  });
  assert(mainMgrRes.status === 200, 'Main Manager central overview returned HTTP 200');
  assert(mainMgrRes.data.is_branch_view === false, 'Main Manager sees central dashboard');

  // Main Branch Manager CANNOT access Settings or Audit Log
  const mainMgrSettingsRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/settings',
    method: 'GET',
    headers: { 'Authorization': `Bearer ${mainMgrToken}`, 'Accept': 'application/json' },
  });
  assert(mainMgrSettingsRes.status === 403, 'Main Manager blocked from Settings (403 Forbidden)');

  const mainMgrAuditRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/audit-logs',
    method: 'GET',
    headers: { 'Authorization': `Bearer ${mainMgrToken}`, 'Accept': 'application/json' },
  });
  assert(mainMgrAuditRes.status === 403, 'Main Manager blocked from Audit Log (403 Forbidden)');

  // 3. Branch Admin: Branch Dashboard (Locked to Bole)
  const boleAdminToken = await loginAs('Branch Admin', 'BR001');
  const branchAdminRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/overview',
    method: 'GET',
    headers: { 'Authorization': `Bearer ${boleAdminToken}`, 'Accept': 'application/json' },
  });

  assert(branchAdminRes.status === 200, 'Branch Admin overview returned HTTP 200');
  assert(branchAdminRes.data.is_branch_view === true, 'Branch Admin locked to branch dashboard (is_branch_view === true)');
  assert(branchAdminRes.data.branch.branch_name === 'Bole', `Locked strictly to Bole branch (${branchAdminRes.data.branch.branch_name})`);
  assert(branchAdminRes.data.stock_summary && typeof branchAdminRes.data.stock_summary.available === 'number', `Branch stock summary available: ${branchAdminRes.data.stock_summary?.available}`);
  assert(Array.isArray(branchAdminRes.data.recent_orders), `Branch recent orders list present: ${branchAdminRes.data.recent_orders.length} orders`);

  // Branch Admin Orders scoping check: Can only see Bole orders
  const branchOrdersRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/orders',
    method: 'GET',
    headers: { 'Authorization': `Bearer ${boleAdminToken}`, 'Accept': 'application/json' },
  });
  assert(branchOrdersRes.status === 200, 'Branch Admin orders endpoint returned 200 OK');
  const allBoleOrders = branchOrdersRes.data.orders.every((o) => o.branch_id === 2);
  assert(allBoleOrders, 'Server-side enforcement: 100% of returned orders belong to assigned branch (branch_id 2)');

  // Branch Admin CANNOT access Branch Management or Settings
  const branchAdminBranchRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/branches',
    method: 'POST',
    headers: { 'Authorization': `Bearer ${boleAdminToken}`, 'Accept': 'application/json', 'Content-Type': 'application/json' },
  }, { branch_name: 'Hacker Branch' });
  assert(branchAdminBranchRes.status === 403, 'Branch Admin blocked from creating branches (403 Forbidden)');

  // 4. Branch Staff: Reduced View & Server-side restrictions
  const boleStaffToken = await loginAs('Branch Staff', 'BR001');
  const staffOverviewRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/overview',
    method: 'GET',
    headers: { 'Authorization': `Bearer ${boleStaffToken}`, 'Accept': 'application/json' },
  });
  assert(staffOverviewRes.status === 200, 'Branch Staff overview returned HTTP 200');
  assert(staffOverviewRes.data.is_branch_view === true, 'Branch Staff sees branch dashboard');

  // Branch Staff CANNOT view financial reports (403 Forbidden)
  const staffReportRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/reports/summary',
    method: 'GET',
    headers: { 'Authorization': `Bearer ${boleStaffToken}`, 'Accept': 'application/json' },
  });
  assert(staffReportRes.status === 403, 'Branch Staff blocked from financial reports (403 Forbidden)');

  // Branch Staff CANNOT adjust stock (403 Forbidden)
  const staffAdjustRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/inventory/adjust',
    method: 'POST',
    headers: { 'Authorization': `Bearer ${boleStaffToken}`, 'Accept': 'application/json', 'Content-Type': 'application/json' },
  }, {
    branch_id: 2,
    product_id: 1,
    type: 'manual_adjustment',
    quantity: 5,
    reason: 'Staff unauthorized adjustment attempt',
  });
  assert(staffAdjustRes.status === 403, 'Branch Staff blocked from manual stock adjustment (403 Forbidden)');

  // 5. Customer cannot access any admin endpoint (403 Forbidden)
  const customerToken = await loginAs('Customer');
  const customerAdminRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/overview',
    method: 'GET',
    headers: { 'Authorization': `Bearer ${customerToken}`, 'Accept': 'application/json' },
  });
  assert(customerAdminRes.status === 403, 'Customer strictly blocked from admin overview (403 Forbidden)');

  console.log('\n==================================================');
  console.log(`SECTION 9 VERIFICATION RESULTS: ${passed} PASSED, ${failed} FAILED`);
  console.log('==================================================');

  if (failed > 0) process.exit(1);
}

runSection9Tests().catch((err) => {
  console.error('Fatal test error:', err);
  process.exit(1);
});
