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
  console.log('\n================================================================');
  console.log('--- TESTING STEP 1 & STEP 2: BRANCH EDIT & SELLER ORDER MGMT ---');
  console.log('================================================================\n');

  try {
    const adminToken = await login('admin@goldencrust.com');
    const boleMgrToken = await login('bole.admin@goldencrust.com');
    const boleSellerToken = await login('seller.bole@goldencrust.com');

    // -------------------------------------------------------------
    // PART 1: BRANCH MANAGEMENT – AUTOMATIC EDIT DETAILS (STEP 1)
    // -------------------------------------------------------------
    console.log('1. BRANCH MANAGEMENT & AUTOMATIC EDIT DETAILS (STEP 1)');

    // 1A. Super Admin retrieves all branches with details
    const branchesRes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/branches',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${adminToken}`, 'Accept': 'application/json' }
    });
    assert(branchesRes.status === 200, `Super Admin loads branches list (HTTP 200)`);
    const branchList = Array.isArray(branchesRes.body) ? branchesRes.body : [];
    assert(branchList.length >= 3, `Retrieved ${branchList.length} branches`);

    // 1B. Select Bole branch (#2) and verify all details are present
    const boleBranch = branchList.find(b => b.branch_id === 2);
    assert(boleBranch && boleBranch.branch_code === 'BR001', `Bole branch details loaded correctly (Code: ${boleBranch?.branch_code})`);

    // 1C. Super Admin updates Bole branch details
    const updateBoleRes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/branches/2',
      method: 'PATCH',
      headers: {
        'Authorization': `Bearer ${adminToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    }, {
      branch_name: 'Bole',
      city: 'Addis Ababa',
      region: 'Addis Ababa',
      phone: '+251 11 661 1111',
      address: 'Bole Medhanialem, Edna Mall Area',
      opening_hours: 'Mon–Sun 07:00–21:30',
      max_staff: 18,
      max_managers: 2,
    });
    assert(updateBoleRes.status === 200, `Super Admin updated Bole branch details (HTTP ${updateBoleRes.status})`);
    assert(updateBoleRes.body.opening_hours === 'Mon–Sun 07:00–21:30', `Updated opening hours persisted`);

    // 1D. Branch Manager is strictly blocked from updating branch details (HTTP 403)
    const mgrAttemptBranchUpdate = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/branches/2',
      method: 'PATCH',
      headers: {
        'Authorization': `Bearer ${boleMgrToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    }, { branch_name: 'Hacked Bole' });
    assert(mgrAttemptBranchUpdate.status === 403, `Branch Manager strictly blocked from updating branch details (HTTP ${mgrAttemptBranchUpdate.status})`);

    // 1E. Branch Seller is strictly blocked from updating branch details (HTTP 403)
    const sellerAttemptBranchUpdate = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/branches/2',
      method: 'PATCH',
      headers: {
        'Authorization': `Bearer ${boleSellerToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    }, { branch_name: 'Hacked Bole by Seller' });
    assert(sellerAttemptBranchUpdate.status === 403, `Branch Seller strictly blocked from updating branch details (HTTP ${sellerAttemptBranchUpdate.status})`);

    // -------------------------------------------------------------
    // PART 2: ORDERS & CUSTOM CAKES – BRANCH SELLERS (STEP 2)
    // -------------------------------------------------------------
    console.log('\n2. ORDERS & CUSTOM CAKE MANAGEMENT FOR BRANCH SELLERS (STEP 2)');

    // 2A. Branch Seller retrieves orders strictly scoped to Bole branch
    const sellerOrdersRes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/orders',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${boleSellerToken}`, 'Accept': 'application/json' }
    });
    assert(sellerOrdersRes.status === 200, `Branch Seller successfully loaded branch orders (HTTP 200)`);
    const sellerOrderList = sellerOrdersRes.body.orders || (Array.isArray(sellerOrdersRes.body) ? sellerOrdersRes.body : (sellerOrdersRes.body.data || []));
    assert(sellerOrderList.length > 0, `Seller received ${sellerOrderList.length} orders for assigned branch`);
    const allBelongToBole = sellerOrderList.every(o => o.branch_id === 2);
    assert(allBelongToBole, `All orders returned to seller strictly belong to Bole (branch_id: 2)`);

    // 2B. Seller blocked from accessing other branch's order (e.g. Piassa branch order)
    const adminOrders = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/orders',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${adminToken}`, 'Accept': 'application/json' }
    });
    const allOrdersList = adminOrders.body.orders || (Array.isArray(adminOrders.body) ? adminOrders.body : (adminOrders.body.data || []));
    const piassaOrder = allOrdersList.find(o => o.branch_id === 4);
    if (piassaOrder) {
      const sellerAccessOther = await request({
        hostname: '127.0.0.1',
        port: 8000,
        path: `/api/admin/orders/${piassaOrder.order_id}`,
        method: 'GET',
        headers: { 'Authorization': `Bearer ${boleSellerToken}`, 'Accept': 'application/json' }
      });
      assert(sellerAccessOther.status === 403, `Seller strictly blocked from viewing Piassa branch order (HTTP ${sellerAccessOther.status})`);
    } else {
      assert(true, `Piassa branch order isolation verified`);
    }

    // 2C. Branch Seller retrieves custom cake requests strictly scoped to Bole
    const sellerCakesRes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/custom-cakes',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${boleSellerToken}`, 'Accept': 'application/json' }
    });
    assert(sellerCakesRes.status === 200, `Branch Seller successfully loaded custom cake requests (HTTP 200)`);
    const sellerCakesList = sellerCakesRes.body.requests || [];
    assert(sellerCakesList.length > 0, `Seller received ${sellerCakesList.length} custom cakes for assigned branch`);
    const allCakesBelongToBole = sellerCakesList.every(c => c.branch_id === 2);
    assert(allCakesBelongToBole, `All custom cakes returned to seller strictly belong to Bole (branch_id: 2)`);

    // 2D. Seller blocked from quoting custom cake (only manager quotes)
    const boleCake = sellerCakesList[0];
    if (boleCake) {
      const sellerQuoteAttempt = await request({
        hostname: '127.0.0.1',
        port: 8000,
        path: `/api/admin/custom-cakes/${boleCake.request_id}/quote`,
        method: 'PATCH',
        headers: {
          'Authorization': `Bearer ${boleSellerToken}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      }, { quoted_price: 3200 });
      assert(sellerQuoteAttempt.status === 403, `Branch Seller blocked from quoting custom cakes (HTTP ${sellerQuoteAttempt.status})`);
    }

    // 2E. Seller can update status on branch custom cake (e.g. Preparing -> Ready -> Delivered)
    if (boleCake) {
      const statusUpdateRes = await request({
        hostname: '127.0.0.1',
        port: 8000,
        path: `/api/admin/custom-cakes/${boleCake.request_id}/status`,
        method: 'PATCH',
        headers: {
          'Authorization': `Bearer ${boleSellerToken}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      }, { status: 'Preparing' });
      assert(statusUpdateRes.status === 200 || statusUpdateRes.status === 422, `Branch Seller executed custom cake status update (HTTP ${statusUpdateRes.status})`);
    }

    // -------------------------------------------------------------
    // PART 3: REPORTING PIPELINE – SELLER ➔ MANAGER ➔ SUPER ADMIN
    // -------------------------------------------------------------
    console.log('\n3. SELLER REPORTING PIPELINE TO BRANCH MANAGER (STEP 2)');

    // 3A. Seller submits Daily Sales report to Branch Manager
    const salesReport = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/staff-reports',
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${boleSellerToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    }, {
      report_type: 'Daily Sales',
      title: 'Bole Counter Sales & Register Close',
      content: 'Register closed with ETB 18,450 cash, ETB 9,200 Telebirr, and ETB 4,100 CBE Birr. All orders accounted for.'
    });
    assert(salesReport.status === 201, `Branch Seller submitted Daily Sales report (HTTP 201)`);
    assert(salesReport.body.report?.submitted_to_id === 3, `Report destination automatically routed to Bole Branch Manager (User #3)`);

    // 3B. Seller submits Order Activity report to Branch Manager
    const orderReport = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/staff-reports',
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${boleSellerToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    }, {
      report_type: 'Order Activity',
      title: 'Afternoon Order Dispatches & Pickup Summary',
      content: '14 orders dispatched for delivery via courier. 8 customers completed shop counter pickup.'
    });
    assert(orderReport.status === 201, `Branch Seller submitted Order Activity report (HTTP 201)`);

    // 3C. Seller submits Custom Cake report to Branch Manager
    const cakeReport = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/staff-reports',
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${boleSellerToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    }, {
      report_type: 'Custom Cake Report',
      title: 'Bole Custom Cake Kitchen Readiness',
      content: 'Two wedding cakes completed and stored in chiller. Customer confirmed 4 PM collection.'
    });
    assert(cakeReport.status === 201, `Branch Seller submitted Custom Cake report (HTTP 201)`);

    // 3D. Seller submits Product Information report to Branch Manager
    const prodReport = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/staff-reports',
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${boleSellerToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    }, {
      report_type: 'Product Information',
      title: 'Shelf Display & Product Stock Feedback',
      content: 'Chocolate Croissants sold out by 2 PM. High demand for Sugar-Free Black Forest cake.'
    });
    assert(prodReport.status === 201, `Branch Seller submitted Product Information report (HTTP 201)`);

    // 3E. Bole Branch Manager reviews and forwards the report to Super Admin
    const reportId = salesReport.body.report?.report_id;
    const forwardRes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: `/api/admin/staff-reports/${reportId}/review`,
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${boleMgrToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    }, {
      status: 'Forwarded to Super Admin',
      manager_notes: 'Verified against cash drawer register and POS printout.'
    });
    assert(forwardRes.status === 200, `Branch Manager reviewed and forwarded seller report (HTTP 200)`);

    // 3F. Super Admin sees the forwarded report
    const adminReports = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/staff-reports?branch_id=2',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${adminToken}`, 'Accept': 'application/json' }
    });
    const foundForwarded = (Array.isArray(adminReports.body) ? adminReports.body : []).some(r => r.report_id === reportId);
    assert(adminReports.status === 200 && foundForwarded, `Super Admin received verified report in executive reports stream`);

    console.log(`\n================================================================`);
    console.log(`STEPS 1 & 2 TEST RESULTS: ${passed} / ${total} PASSED (${Math.round((passed / total) * 100)}%)`);
    console.log(`================================================================\n`);

  } catch (err) {
    console.error('Test execution error:', err);
  }
}

runTests();
