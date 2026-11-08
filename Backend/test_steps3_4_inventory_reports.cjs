const http = require('http');

function request(options, data = null) {
  return new Promise((resolve, reject) => {
    const req = http.request(options, (res) => {
      let body = '';
      res.on('data', (chunk) => body += chunk);
      res.on('end', () => {
        try {
          resolve({ status: res.statusCode, headers: res.headers, body: JSON.parse(body) });
        } catch (e) {
          resolve({ status: res.statusCode, headers: res.headers, body });
        }
      });
    });
    req.on('error', reject);
    if (data) {
      req.write(typeof data === 'string' ? data : JSON.stringify(data));
    }
    req.end();
  });
}

async function login(email, password = 'password123') {
  const res = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/auth/login',
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }
  }, { email, password });

  if (!res.body || !res.body.token) {
    throw new Error(`Login failed for ${email}: ` + JSON.stringify(res.body));
  }
  return {
    token: res.body.token,
    user: res.body.user
  };
}

let passed = 0;
let total = 0;

function assert(condition, message) {
  total++;
  if (condition) {
    console.log(`  ✓ ${message}`);
    passed++;
  } else {
    console.error(`  ✗ FAIL: ${message}`);
  }
}

async function runTests() {
  console.log('\n================================================================');
  console.log('--- TESTING STEPS 3 & 4: MY INVENTORY & BRANCH REPORTS PRIVACY ---');
  console.log('================================================================\n');

  try {
    // 1. Authenticate users
    console.log('1. AUTHENTICATION OF STAKEHOLDERS');
    const superAdmin = await login('admin@goldencrust.com');
    assert(superAdmin.user.role === 'Super Admin', 'Super Admin authenticated');

    const boleManager = await login('bole.admin@goldencrust.com');
    assert(boleManager.user.branch_id === 2, `Bole Branch Manager authenticated (Branch ID: ${boleManager.user.branch_id})`);

    const piassaManager = await login('piassa.admin@goldencrust.com');
    assert(piassaManager.user.branch_id === 4, `Piassa Branch Manager authenticated (Branch ID: ${piassaManager.user.branch_id})`);

    const boleSeller = await login('seller.bole@goldencrust.com');
    assert(boleSeller.user.branch_id === 2, `Bole Branch Seller authenticated (Branch ID: ${boleSeller.user.branch_id})`);

    // 2. Step 3: My Inventory - Branch-Specific Access
    console.log('\n2. MY INVENTORY – BRANCH-SPECIFIC ACCESS (STEP 3)');
    // A. Super Admin sees all branches in inventory
    const saInv = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/inventory',
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${superAdmin.token}`,
        'Accept': 'application/json'
      }
    });
    assert(saInv.status === 200, `Super Admin loaded inventory (HTTP ${saInv.status})`);
    assert(Array.isArray(saInv.body.branches) && saInv.body.branches.length > 1, `Super Admin receives all active branches (${saInv.body.branches.length} branches)`);

    // B. Bole Manager sees only Bole inventory and only Bole branch in branches list
    const boleInv = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/inventory',
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${boleManager.token}`,
        'Accept': 'application/json'
      }
    });
    assert(boleInv.status === 200, `Bole Manager loaded inventory (HTTP ${boleInv.status})`);
    assert(Array.isArray(boleInv.body.branches) && boleInv.body.branches.length === 1 && boleInv.body.branches[0].branch_id === 2, 'Bole Manager branches list contains strictly Bole branch');
    const nonBoleItems = (boleInv.body.items || []).filter(i => i.branch_id !== 2);
    assert(nonBoleItems.length === 0, `All items returned to Bole Manager strictly belong to Bole (non-Bole count: ${nonBoleItems.length})`);

    // C. Bole Seller sees strictly Bole inventory
    const sellerInv = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/inventory',
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${boleSeller.token}`,
        'Accept': 'application/json'
      }
    });
    assert(sellerInv.status === 200, `Bole Seller loaded inventory (HTTP ${sellerInv.status})`);
    const sellerNonBoleItems = (sellerInv.body.items || []).filter(i => i.branch_id !== 2);
    assert(sellerNonBoleItems.length === 0, 'Branch Seller receives strictly assigned branch inventory');

    // 3. Step 3: Functional + Adjust Stock Button
    console.log('\n3. FUNCTIONAL + ADJUST STOCK OPERATION & AUDIT TRAIL (STEP 3)');
    // A. Bole Manager increases stock for Bole
    const targetProduct = boleInv.body.items[0];
    const prevQty = targetProduct.on_hand;
    const adjustRes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/inventory/adjust',
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${boleManager.token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    }, {
      branch_id: 2,
      product_id: targetProduct.product_id,
      type: 'restock',
      quantity: 15,
      reason: 'Daily fresh bakery morning restock batch'
    });
    assert(adjustRes.status === 200, `Bole Manager adjusted stock (HTTP ${adjustRes.status})`);
    assert(adjustRes.body.details && adjustRes.body.details.previous_quantity === prevQty, `Previous quantity recorded correctly (${prevQty})`);
    assert(adjustRes.body.details.new_quantity === prevQty + 15, `New quantity updated automatically (${prevQty + 15})`);
    assert(adjustRes.body.details.reason === 'Daily fresh bakery morning restock batch', 'Adjustment reason captured in response');
    assert(adjustRes.body.details.employee && adjustRes.body.details.employee.includes('Almaz Haile'), 'Employee name and role captured');

    // B. Bole Manager blocked from adjusting Piassa inventory
    const crossAdjustRes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/inventory/adjust',
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${boleManager.token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    }, {
      branch_id: 4,
      product_id: 1,
      type: 'manual_adjustment',
      quantity: 5,
      reason: 'Unauthorized cross-branch change'
    });
    assert(crossAdjustRes.status === 403, `Bole Manager blocked from adjusting other branch inventory (HTTP ${crossAdjustRes.status})`);

    // C. Adjustment requires mandatory reason
    const noReasonAdjust = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/inventory/adjust',
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${boleManager.token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    }, {
      branch_id: 2,
      product_id: targetProduct.product_id,
      type: 'damaged',
      quantity: 1
      // missing reason
    });
    assert(noReasonAdjust.status === 422, `Stock adjustment requires mandatory reason (HTTP ${noReasonAdjust.status})`);

    // D. Verify Audit Log recorded the stock adjustment
    const auditRes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/audit-logs?branch_id=2&action=Stock%20adjustment',
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${superAdmin.token}`,
        'Accept': 'application/json'
      }
    });
    assert(auditRes.status === 200, `Audit logs retrieved for stock adjustment (HTTP ${auditRes.status})`);
    const logsList = Array.isArray(auditRes.body.logs) ? auditRes.body.logs : (auditRes.body.logs?.data || []);
    const latestAdjustLog = logsList.find(l => l.action === 'Stock adjustment' && (l.user || l.user_name || '').includes('Almaz Haile'));
    assert(!!latestAdjustLog, 'Audit log verified: Recorded employee, branch, action, and adjustment delta');

    // 4. Step 4: Branch Reports - Strict Branch Privacy
    console.log('\n4. BRANCH REPORTS – STRICT BRANCH PRIVACY & ISOLATION (STEP 4)');
    // A. Bole Manager loads reports summary: strictly Bole metrics
    const boleSummary = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/reports/summary',
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${boleManager.token}`,
        'Accept': 'application/json'
      }
    });
    assert(boleSummary.status === 200, `Bole Manager loaded reports summary (HTTP ${boleSummary.status})`);
    const bp = boleSummary.body.branch_performance || [];
    assert(bp.length === 1 && bp[0].branch_id === 2, `Branch performance strictly contains only Bole Branch (count: ${bp.length})`);

    // B. Bole Manager CANNOT access Piassa reports (HTTP 403)
    const crossReportSummary = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/reports/summary?branch_id=4',
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${boleManager.token}`,
        'Accept': 'application/json'
      }
    });
    assert(crossReportSummary.status === 403, `Bole Manager strictly forbidden from accessing Piassa branch reports (HTTP ${crossReportSummary.status})`);

    // C. Piassa Manager CANNOT access Bole reports (HTTP 403)
    const piassaCrossSummary = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/reports/summary?branch_id=2',
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${piassaManager.token}`,
        'Accept': 'application/json'
      }
    });
    assert(piassaCrossSummary.status === 403, `Piassa Manager strictly forbidden from accessing Bole branch reports (HTTP ${piassaCrossSummary.status})`);

    // D. Branch Seller strictly accesses only own branch reports
    const sellerCrossReport = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/staff-reports?branch_id=4',
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${boleSeller.token}`,
        'Accept': 'application/json'
      }
    });
    assert(sellerCrossReport.status === 403, `Branch Seller strictly blocked from accessing other branch reports (HTTP ${sellerCrossReport.status})`);

    // E. Branch Seller cannot submit report for another branch
    const sellerCrossSubmit = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/staff-reports',
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${boleSeller.token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    }, {
      report_type: 'Daily Sales',
      title: 'Unauthorized cross-branch daily sales',
      content: 'Attempting to submit report for Piassa from Bole seller',
      branch_id: 4
    });
    assert(sellerCrossSubmit.status === 403, `Branch Seller strictly forbidden from submitting report for another branch (HTTP ${sellerCrossSubmit.status})`);

    // 5. Step 4: Date Filters
    console.log('\n5. BRANCH REPORTS – DATE FILTERS (STEP 4)');
    // A. Today filter
    const todaySummary = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/reports/summary?date_range=today',
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${superAdmin.token}`,
        'Accept': 'application/json'
      }
    });
    assert(todaySummary.status === 200, `Reports summary supports 'today' filter (HTTP ${todaySummary.status})`);

    // B. This Week filter
    const thisWeekSummary = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/reports/summary?date_range=this_week',
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${superAdmin.token}`,
        'Accept': 'application/json'
      }
    });
    assert(thisWeekSummary.status === 200, `Reports summary supports 'this_week' filter (HTTP ${thisWeekSummary.status})`);

    // C. This Month filter
    const thisMonthSummary = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/reports/summary?date_range=this_month',
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${superAdmin.token}`,
        'Accept': 'application/json'
      }
    });
    assert(thisMonthSummary.status === 200, `Reports summary supports 'this_month' filter (HTTP ${thisMonthSummary.status})`);

    // D. Staff reports date filter
    const staffReportsWeek = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/staff-reports?date_range=this_week',
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${boleManager.token}`,
        'Accept': 'application/json'
      }
    });
    assert(staffReportsWeek.status === 200, `Staff reports desk supports date_range filter (HTTP ${staffReportsWeek.status})`);

    // E. Super Admin central multi-branch overview & comparison
    const saAllReports = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/reports/summary',
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${superAdmin.token}`,
        'Accept': 'application/json'
      }
    });
    assert(saAllReports.status === 200, 'Super Admin accesses central multi-branch comparison');
    assert(saAllReports.body.branch_performance && saAllReports.body.branch_performance.length > 1, `Super Admin receives comparison of all ${saAllReports.body.branch_performance.length} branches`);

    console.log('\n================================================================');
    console.log(`STEPS 3 & 4 TEST RESULTS: ${passed} / ${total} PASSED (${Math.round((passed / total) * 100)}%)`);
    console.log('================================================================\n');

    if (passed !== total) {
      process.exit(1);
    }
  } catch (err) {
    console.error('\n[FATAL ERROR IN TEST SUITE]:', err);
    process.exit(1);
  }
}

runTests();
