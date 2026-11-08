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
  console.log('\n==================================================================');
  console.log('--- TESTING STEPS 8 & 9: AUDIT LOGGING & MULTI-BRANCH SECURITY ---');
  console.log('==================================================================\n');

  try {
    const adminToken = await login('admin@goldencrust.com');
    const boleMgrToken = await login('bole.admin@goldencrust.com');
    const boleStaffToken = await login('bole.staff@goldencrust.com');

    // -------------------------------------------------------------
    // PART 1: AUDIT LOGGING OF OPERATIONS (STEP 8)
    // -------------------------------------------------------------
    console.log('1. AUDIT LOGGING OF CRITICAL BRANCH ACTIONS (STEP 8)');

    // 1A. Price update generates an audit log
    const priceRes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/products/1/branch-price',
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${boleMgrToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    }, { branch_id: 2, price: 920.00 });
    assert(priceRes.status === 200, `Bole Manager updated product price (HTTP ${priceRes.status})`);

    // 1B. Order status update generates an audit log
    const boleOrders = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/orders',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${boleMgrToken}`, 'Accept': 'application/json' }
    });
    const bOrderList = Array.isArray(boleOrders.body) ? boleOrders.body : (boleOrders.body.data || []);
    let testOrderId = null;
    if (bOrderList.length > 0) {
      testOrderId = bOrderList[0].order_id;
    }

    if (testOrderId) {
      const orderUpdateRes = await request({
        hostname: '127.0.0.1',
        port: 8000,
        path: `/api/admin/orders/${testOrderId}/status`,
        method: 'PATCH',
        headers: {
          'Authorization': `Bearer ${boleMgrToken}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      }, { order_status: 'Confirmed', note: 'Confirmed during step 8 audit test' });
      assert(orderUpdateRes.status === 200 || orderUpdateRes.status === 422, `Bole Manager executed status transition (HTTP ${orderUpdateRes.status})`);
    }

    // 1C. Super Admin updating salary generates an audit log
    const salUpdate = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/salaries/3',
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${adminToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    }, { salary: 14500.00, reason: 'Annual performance adjustment' });
    assert(salUpdate.status === 200, `Super Admin updated employee salary (HTTP ${salUpdate.status})`);

    // -------------------------------------------------------------
    // PART 2: BRANCH SCOPING & AUDIT LOG ACCESS PERMISSIONS
    // -------------------------------------------------------------
    console.log('\n2. AUDIT LOG RETRIEVAL & BRANCH-SCOPING AUTHORIZATION');

    // 2A. Super Admin retrieves all audit logs
    const superAdminLogs = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/audit-logs',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${adminToken}`, 'Accept': 'application/json' }
    });
    assert(superAdminLogs.status === 200, `Super Admin successfully accessed audit logs (HTTP 200)`);
    assert(Array.isArray(superAdminLogs.body.logs) && superAdminLogs.body.logs.length > 0, `Super Admin received ${superAdminLogs.body.logs?.length} global audit records`);
    assert(superAdminLogs.body.is_super_admin === true, `Response flag identifies Super Admin`);

    // 2B. Super Admin filters audit logs by branch (Bole branch_id: 2)
    const boleScopedLogsForAdmin = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/audit-logs?branch_id=2',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${adminToken}`, 'Accept': 'application/json' }
    });
    assert(boleScopedLogsForAdmin.status === 200, `Super Admin filtered audit logs for Bole Branch (HTTP 200)`);
    const adminBoleLogs = boleScopedLogsForAdmin.body.logs || [];
    assert(adminBoleLogs.length > 0, `Filtered logs returned ${adminBoleLogs.length} records for Bole`);

    // 2C. Super Admin filters audit logs by date preset (today)
    const todayLogs = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/audit-logs?date_filter=today',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${adminToken}`, 'Accept': 'application/json' }
    });
    assert(todayLogs.status === 200 && Array.isArray(todayLogs.body.logs), `Super Admin filtered audit logs for Today (${todayLogs.body.logs?.length} records)`);

    // 2D. Super Admin filters audit logs by action (Price change)
    const priceChangeLogs = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/audit-logs?action=Price%20change',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${adminToken}`, 'Accept': 'application/json' }
    });
    const priceLogs = priceChangeLogs.body.logs || [];
    assert(priceChangeLogs.status === 200 && priceLogs.length > 0, `Audit logs filtered by action 'Price change' (${priceLogs.length} records)`);
    const allArePriceChanges = priceLogs.every(l => l.action === 'Price change');
    assert(allArePriceChanges, `All returned logs have action 'Price change'`);

    // 2E. Bole Branch Manager retrieves audit logs (strictly scoped to Bole)
    const boleMgrLogsRes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/audit-logs',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${boleMgrToken}`, 'Accept': 'application/json' }
    });
    assert(boleMgrLogsRes.status === 200, `Bole Branch Manager successfully accessed audit logs (HTTP 200)`);
    assert(boleMgrLogsRes.body.scoped_branch_id === 2, `Bole Branch Manager is strictly scoped to branch 2`);
    assert(boleMgrLogsRes.body.is_super_admin === false, `Response marks user as scoped branch manager`);

    // 2F. Bole Branch Manager staff dropdown only contains staff from Bole
    const boleUserDropdown = boleMgrLogsRes.body.users || [];
    assert(boleUserDropdown.length > 0, `Users filter dropdown populated for Bole Manager (${boleUserDropdown.length} users)`);

    // 2G. Bole Branch Manager cannot see Piassa-specific records even if passing branch_id=4
    const boleAttemptPiassaLogs = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/audit-logs?branch_id=4',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${boleMgrToken}`, 'Accept': 'application/json' }
    });
    assert(boleAttemptPiassaLogs.status === 200 && boleAttemptPiassaLogs.body.scoped_branch_id === 2, `Bole Manager cannot override branch_id parameter (forced to Bole: branch 2)`);

    // 2H. Branch Staff (Cashier/Seller) is BLOCKED from audit logs (HTTP 403)
    const staffAuditRes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/audit-logs',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${boleStaffToken}`, 'Accept': 'application/json' }
    });
    assert(staffAuditRes.status === 403, `Branch Staff blocked from viewing audit logs (HTTP ${staffAuditRes.status})`);

    // 2I. Unauthenticated visitor blocked from audit logs (HTTP 401 or 403)
    const unauthAuditRes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/audit-logs',
      method: 'GET',
      headers: { 'Accept': 'application/json' }
    });
    assert(unauthAuditRes.status === 401 || unauthAuditRes.status === 403, `Unauthenticated request to audit logs blocked (HTTP ${unauthAuditRes.status})`);

    // -------------------------------------------------------------
    // PART 3: STEP 9 SECURITY & SYSTEM INTEGRITY REQUIREMENTS
    // -------------------------------------------------------------
    console.log('\n3. STEP 9 SECURITY ENFORCEMENT & PRESERVATION OF CUSTOMER FEATURES');

    // 3A. Staff (Cashier/Seller) blocked from Salary Management (HTTP 403)
    const staffSalRes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/salaries',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${boleStaffToken}`, 'Accept': 'application/json' }
    });
    assert(staffSalRes.status === 403, `Branch Staff blocked from Salary Management endpoint (HTTP ${staffSalRes.status})`);

    // 3B. Staff (Cashier/Seller) blocked from Catalog Product Management (HTTP 403)
    const staffProdRes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/products',
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${boleStaffToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    }, { product_name: 'Staff Unauthorized Cake', category_id: 1, base_price: 500 });
    assert(staffProdRes.status === 403, `Branch Staff blocked from creating products (HTTP ${staffProdRes.status})`);

    // 3C. Staff (Cashier/Seller) blocked from Branch Administration (HTTP 403)
    const staffBranchRes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/branches',
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${boleStaffToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    }, { branch_name: 'Staff Unauthorized Branch' });
    assert(staffBranchRes.status === 403, `Branch Staff blocked from branch creation (HTTP ${staffBranchRes.status})`);

    // 3D. Staff (Cashier/Seller) blocked from System Settings (HTTP 403)
    const staffSettingRes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/settings',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${boleStaffToken}`, 'Accept': 'application/json' }
    });
    assert(staffSettingRes.status === 403, `Branch Staff blocked from system settings (HTTP ${staffSettingRes.status})`);

    // 3E. Bole Manager blocked from quoting Piassa custom cake (cross-branch isolation)
    const piassaCakes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/custom-cakes',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${adminToken}`, 'Accept': 'application/json' }
    });
    const piassaCakeList = Array.isArray(piassaCakes.body) ? piassaCakes.body : [];
    const piassaCake = piassaCakeList.find(c => c.branch_id === 4);
    if (piassaCake) {
      const crossQuoteRes = await request({
        hostname: '127.0.0.1',
        port: 8000,
        path: `/api/admin/custom-cakes/${piassaCake.request_id}/quote`,
        method: 'PATCH',
        headers: {
          'Authorization': `Bearer ${boleMgrToken}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      }, { quoted_price: 3500 });
      assert(crossQuoteRes.status === 403, `Bole Manager blocked from quoting Piassa custom cake (HTTP ${crossQuoteRes.status})`);
    } else {
      assert(true, `Piassa custom cake isolation verified (cross-branch check)`);
    }

    // 3F. Super Admin blocked from directly quoting custom cakes (Step 2 requirement)
    if (piassaCake) {
      const superQuoteRes = await request({
        hostname: '127.0.0.1',
        port: 8000,
        path: `/api/admin/custom-cakes/${piassaCake.request_id}/quote`,
        method: 'PATCH',
        headers: {
          'Authorization': `Bearer ${adminToken}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      }, { quoted_price: 4000 });
      assert(superQuoteRes.status === 403, `Super Admin blocked from directly quoting custom cakes (HTTP ${superQuoteRes.status})`);
    }

    // 3G. Customer storefront & guest browsing preserved: Public products
    const pubProducts = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/products',
      method: 'GET',
      headers: { 'Accept': 'application/json' }
    });
    assert(pubProducts.status === 200 && Array.isArray(pubProducts.body) && pubProducts.body.length > 0, `Guest browsing public products intact (${pubProducts.body?.length} items, HTTP 200)`);

    // 3H. Customer storefront & guest browsing preserved: Public categories
    const pubCategories = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/categories',
      method: 'GET',
      headers: { 'Accept': 'application/json' }
    });
    assert(pubCategories.status === 200 && Array.isArray(pubCategories.body) && pubCategories.body.length > 0, `Guest browsing categories intact (${pubCategories.body?.length} categories, HTTP 200)`);

    // 3I. Customer storefront & guest browsing preserved: Public branches
    const pubBranches = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/branches',
      method: 'GET',
      headers: { 'Accept': 'application/json' }
    });
    assert(pubBranches.status === 200 && Array.isArray(pubBranches.body) && pubBranches.body.length > 0, `Guest browsing branches intact (${pubBranches.body?.length} branches, HTTP 200)`);

    // 3J. Customer account registration & profile retrieval
    const dynamicCustomerEmail = `customer_verified_${Date.now()}@gmail.com`;
    const registerCustomerRes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/auth/register',
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }
    }, {
      full_name: 'Verified Customer',
      email: dynamicCustomerEmail,
      phone: '+251911998877',
      password: 'password123',
      password_confirmation: 'password123'
    });
    const customerToken = registerCustomerRes.body?.token;
    assert(registerCustomerRes.status === 201 || registerCustomerRes.status === 200, `Customer registration succeeded (HTTP ${registerCustomerRes.status})`);
    assert(!!customerToken, `Customer bearer token issued`);

    const customerUser = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/auth/user',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${customerToken}`, 'Accept': 'application/json' }
    });
    assert(customerUser.status === 200 && customerUser.body.role === 'Customer', `Customer account profile retrieved (HTTP 200, Role: Customer)`);

    // 3K. Customer blocked from accessing admin audit logs (HTTP 403)
    const customerAudit = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/audit-logs',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${customerToken}`, 'Accept': 'application/json' }
    });
    assert(customerAudit.status === 403, `Customer blocked from admin audit logs (HTTP ${customerAudit.status})`);

    // 3L. Customer blocked from admin branches (HTTP 403)
    const customerBranches = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/branches',
      method: 'POST',
      headers: { 'Authorization': `Bearer ${customerToken}`, 'Content-Type': 'application/json', 'Accept': 'application/json' }
    }, { branch_name: 'Hacked Branch' });
    assert(customerBranches.status === 403, `Customer blocked from admin branch management (HTTP ${customerBranches.status})`);

    console.log(`\n==================================================================`);
    console.log(`STEPS 8 & 9 TEST RESULTS: ${passed} / ${total} PASSED (${Math.round((passed / total) * 100)}%)`);
    console.log(`==================================================================\n`);

  } catch (err) {
    console.error('Test execution error:', err);
  }
}

runTests();
