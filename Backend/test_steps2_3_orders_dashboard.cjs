const http = require('http');

function request(options, data = null) {
  return new Promise((resolve, reject) => {
    const req = http.request(options, (res) => {
      let body = '';
      res.on('data', chunk => body += chunk);
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
  return res.body.token;
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
  console.log('\n============================================================');
  console.log('--- TESTING STEPS 2 & 3: ORDERS, CUSTOM CAKES & CENTRAL DASHBOARD ---');
  console.log('============================================================\n');

  try {
    const adminToken = await login('admin@goldencrust.com');
    const boleMgrToken = await login('bole.admin@goldencrust.com');
    const boleStaffToken = await login('bole.staff@goldencrust.com');
    const piassaMgrToken = await login('piassa.admin@goldencrust.com');

    console.log('1. SUPER ADMIN RESTRICTIONS (STEP 2 & 3)');
    // A. Super Admin cannot directly update order status
    const saOrderUpdate = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/orders/1/status',
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${adminToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    }, { status: 'Confirmed' });
    assert(saOrderUpdate.status === 403, `Super Admin cannot update order status (HTTP ${saOrderUpdate.status})`);

    // B. Super Admin cannot directly quote custom cake
    const saCakeQuote = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/custom-cakes/1/quote',
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${adminToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    }, { estimated_price: 1500 });
    assert(saCakeQuote.status === 403, `Super Admin cannot quote custom cake (HTTP ${saCakeQuote.status})`);

    // C. Super Admin cannot directly update custom cake status
    const saCakeStatus = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/custom-cakes/1/status',
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${adminToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    }, { status: 'Confirmed' });
    assert(saCakeStatus.status === 403, `Super Admin cannot update custom cake status (HTTP ${saCakeStatus.status})`);


    console.log('\n2. BRANCH ISOLATION FOR ORDERS (STEP 2)');
    // A. Bole Manager sees only Bole orders
    const boleOrders = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/orders',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${boleMgrToken}`, 'Accept': 'application/json' }
    });
    const boleOrdersList = boleOrders.body.orders || boleOrders.body.data || boleOrders.body;
    const allBole = Array.isArray(boleOrdersList) && boleOrdersList.length > 0 && boleOrdersList.every(o => o.branch_id === 2);
    assert(boleOrders.status === 200 && allBole, `Bole Manager index returns strictly Bole orders (${boleOrdersList.length} items)`);

    // B. Bole Manager updates Bole order #1 status
    const boleUpdateOwn = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/orders/1/status',
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${boleMgrToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    }, { status: 'Preparing' });
    assert(boleUpdateOwn.status === 200, `Bole Manager can update Bole order status (HTTP ${boleUpdateOwn.status})`);

    // C. Bole Manager blocked from updating Piassa order #3
    const boleUpdateOther = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/orders/3/status',
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${boleMgrToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    }, { status: 'Preparing' });
    assert(boleUpdateOther.status === 403, `Bole Manager blocked from updating other branch order (HTTP ${boleUpdateOther.status})`);

    // D. Bole Manager blocked from viewing Piassa order #3 details
    const boleViewOther = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/orders/3',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${boleMgrToken}`, 'Accept': 'application/json' }
    });
    assert(boleViewOther.status === 403, `Bole Manager blocked from viewing other branch order (HTTP ${boleViewOther.status})`);


    console.log('\n3. BRANCH STAFF ORDER & CUSTOM CAKE PERMISSIONS (STEP 2)');
    // A. Bole Staff can view Bole orders
    const staffOrders = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/orders',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${boleStaffToken}`, 'Accept': 'application/json' }
    });
    const staffOrdersList = staffOrders.body.orders || staffOrders.body.data || staffOrders.body;
    const allStaffBole = Array.isArray(staffOrdersList) && staffOrdersList.length > 0 && staffOrdersList.every(o => o.branch_id === 2);
    assert(staffOrders.status === 200 && allStaffBole, `Bole Staff index returns strictly Bole orders (${staffOrdersList.length} items)`);

    // B. Bole Staff blocked from viewing Piassa order #3
    const staffViewOther = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/orders/3',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${boleStaffToken}`, 'Accept': 'application/json' }
    });
    assert(staffViewOther.status === 403, `Bole Staff blocked from viewing other branch order (HTTP ${staffViewOther.status})`);

    // C. Bole Staff can update status on Bole order (operational execution)
    const staffUpdateOwn = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/orders/1/status',
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${boleStaffToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    }, { status: 'Ready' });
    assert(staffUpdateOwn.status === 200, `Bole Staff can update Bole order status to Ready (HTTP ${staffUpdateOwn.status})`);

    // D. Bole Staff BLOCKED from quoting custom cake (only manager quotes)
    const staffQuoteCake = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/custom-cakes/1/quote',
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${boleStaffToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    }, { estimated_price: 1800 });
    assert(staffQuoteCake.status === 403, `Bole Staff blocked from quoting custom cake (HTTP ${staffQuoteCake.status})`);


    console.log('\n4. BRANCH MANAGER CUSTOM CAKE MANAGEMENT (STEP 2)');
    // A. Bole Manager can quote custom cake for Bole
    const boleQuoteOwn = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/custom-cakes/1/quote',
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${boleMgrToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    }, { estimated_price: 1450, admin_notes: 'Approved 2-tier vanilla with gold foil.' });
    assert(boleQuoteOwn.status === 200, `Bole Manager can quote Bole custom cake (HTTP ${boleQuoteOwn.status})`);

    // B. Bole Manager blocked from quoting Piassa custom cake #3
    const boleQuoteOther = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/custom-cakes/3/quote',
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${boleMgrToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    }, { estimated_price: 1900 });
    assert(boleQuoteOther.status === 403, `Bole Manager blocked from quoting other branch custom cake (HTTP ${boleQuoteOther.status})`);


    console.log('\n5. REPORTING HIERARCHY: STAFF -> MANAGER -> SUPER ADMIN (STEP 2)');
    // A. Staff submits Order Activity report to Bole Branch Manager
    const staffReport = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/staff-reports',
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${boleStaffToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    }, {
      report_type: 'Order Activity',
      title: 'Daily Rush Hour Delivery Log',
      content: 'Handled 14 delivery pickups and 6 in-store pick-ups without incident.'
    });
    assert(staffReport.status === 201, `Bole Staff submitted Order Activity report (HTTP ${staffReport.status})`);
    const reportId = staffReport.body.report?.report_id;

    // B. Bole Manager reviews & forwards the report to Super Admin
    if (reportId) {
      const managerReview = await request({
        hostname: '127.0.0.1',
        port: 8000,
        path: `/api/admin/staff-reports/${reportId}/review`,
        method: 'PUT',
        headers: {
          'Authorization': `Bearer ${boleMgrToken}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      }, {
        action: 'forward',
        manager_notes: 'Verified against cash register and delivery courier timestamps.'
      });
      assert(managerReview.status === 200, `Bole Manager reviewed & forwarded report to Super Admin (HTTP ${managerReview.status})`);
    }


    console.log('\n6. CENTRAL EXECUTIVE OVERVIEW FOR SUPER ADMIN (STEP 3)');
    // A. Super Admin accesses whole business central dashboard
    const centralOverview = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/overview',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${adminToken}`, 'Accept': 'application/json' }
    });
    assert(centralOverview.status === 200, `Super Admin loads Central Overview (HTTP ${centralOverview.status})`);
    const ovData = centralOverview.body;
    assert(ovData.is_branch_view === false, `Central overview has is_branch_view = false`);
    assert(Array.isArray(ovData.submitted_reports) && ovData.submitted_reports.length > 0, `Submitted reports list present (${ovData.submitted_reports?.length} reports)`);
    assert(ovData.custom_cakes_summary && typeof ovData.custom_cakes_summary.total_active === 'number', `Custom cakes summary present with active pipeline`);
    assert(Array.isArray(ovData.branches_today) && ovData.branches_today.length > 0, `Branch performance comparison table present (${ovData.branches_today?.length} branches)`);
    assert(Array.isArray(ovData.recent_price_changes), `Recent price adjustments audit feed present`);

    // B. Super Admin filters Central Overview by branch
    const filteredOverview = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/overview?branch_id=2',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${adminToken}`, 'Accept': 'application/json' }
    });
    assert(filteredOverview.status === 200, `Super Admin can filter Central Overview by branch (HTTP ${filteredOverview.status})`);
    assert(filteredOverview.body.is_branch_view === false, `Filtered overview maintains central executive perspective`);

    console.log(`\nResults: ${passed} / ${total} tests passed (${Math.round((passed / total) * 100)}%)`);

    if (passed === total) {
      console.log('>>> ALL STEP 2 & STEP 3 TESTS PASSED PERFECTLY! <<<\n');
      process.exit(0);
    } else {
      console.error('>>> SOME TESTS FAILED! <<<\n');
      process.exit(1);
    }
  } catch (err) {
    console.error('Test execution error:', err);
    process.exit(1);
  }
}

runTests();
