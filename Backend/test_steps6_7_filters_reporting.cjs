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
  console.log('--- TESTING STEPS 6 & 7: DATE FILTERS & BRANCH REPORTING ---');
  console.log('============================================================\n');

  try {
    const adminToken = await login('admin@goldencrust.com');
    const boleMgrToken = await login('bole.admin@goldencrust.com');
    const boleStaffToken = await login('bole.staff@goldencrust.com');
    const piassaMgrToken = await login('piassa.admin@goldencrust.com');

    console.log('1. MY ORDERS DATE FILTERS & BRANCH TENANCY (STEP 6)');

    // A. Filter: Today
    const todayOrders = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/orders?date=today',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${boleMgrToken}`, 'Accept': 'application/json' }
    });
    const todayList = todayOrders.body.orders || [];
    const allTodayBole = Array.isArray(todayList) && todayList.every(o => o.branch_id === 2);
    assert(todayOrders.status === 200 && allTodayBole, `Bole Manager loads "Today" orders (${todayList.length} orders strictly for Bole)`);

    // B. Filter: This Week
    const weekOrders = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/orders?date=this_week',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${boleMgrToken}`, 'Accept': 'application/json' }
    });
    const weekList = weekOrders.body.orders || [];
    const allWeekBole = Array.isArray(weekList) && weekList.every(o => o.branch_id === 2);
    assert(weekOrders.status === 200 && allWeekBole, `Bole Manager loads "This Week" orders (${weekList.length} orders strictly for Bole)`);

    // C. Filter: This Month
    const monthOrders = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/orders?date=this_month',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${boleMgrToken}`, 'Accept': 'application/json' }
    });
    const monthList = monthOrders.body.orders || [];
    const allMonthBole = Array.isArray(monthList) && monthList.every(o => o.branch_id === 2);
    assert(monthOrders.status === 200 && allMonthBole, `Bole Manager loads "This Month" orders (${monthList.length} orders strictly for Bole)`);

    // D. Filter: Custom Date Range
    const customOrders = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/orders?date=custom&start_date=2026-11-01&end_date=2026-11-30',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${boleMgrToken}`, 'Accept': 'application/json' }
    });
    const customList = customOrders.body.orders || [];
    const allCustomBole = Array.isArray(customList) && customList.every(o => o.branch_id === 2);
    assert(customOrders.status === 200 && allCustomBole, `Bole Manager loads "Custom Date Range" orders (${customList.length} orders strictly for Bole)`);

    // E. Filter: All Orders
    const allOrders = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/orders?date=all',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${boleMgrToken}`, 'Accept': 'application/json' }
    });
    const allList = allOrders.body.orders || [];
    const allBoleOrders = Array.isArray(allList) && allList.length > 0 && allList.every(o => o.branch_id === 2);
    assert(allOrders.status === 200 && allBoleOrders, `Bole Manager loads "All Orders" (${allList.length} orders strictly for Bole)`);

    // F. Branch Staff Date Filter Scoping
    const staffWeekOrders = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/orders?date=this_week',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${boleStaffToken}`, 'Accept': 'application/json' }
    });
    const staffWeekList = staffWeekOrders.body.orders || [];
    const allStaffWeekBole = Array.isArray(staffWeekList) && staffWeekList.every(o => o.branch_id === 2);
    assert(staffWeekOrders.status === 200 && allStaffWeekBole, `Bole Staff loads "This Week" orders strictly scoped to Bole`);


    console.log('\n2. CUSTOM CAKE ORDERS DATE FILTERS (STEP 6)');

    // A. Custom Cakes: Today
    const todayCakes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/custom-cakes?date=today',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${boleMgrToken}`, 'Accept': 'application/json' }
    });
    const todayCakesList = todayCakes.body.requests || [];
    const allTodayCakesBole = Array.isArray(todayCakesList) && todayCakesList.every(c => c.branch_id === 2);
    assert(todayCakes.status === 200 && allTodayCakesBole, `Bole Manager loads "Today" custom cakes (${todayCakesList.length} cakes for Bole)`);

    // B. Custom Cakes: This Month
    const monthCakes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/custom-cakes?date=this_month',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${boleMgrToken}`, 'Accept': 'application/json' }
    });
    const monthCakesList = monthCakes.body.requests || [];
    const allMonthCakesBole = Array.isArray(monthCakesList) && monthCakesList.every(c => c.branch_id === 2);
    assert(monthCakes.status === 200 && allMonthCakesBole, `Bole Manager loads "This Month" custom cakes (${monthCakesList.length} cakes for Bole)`);

    // C. Custom Cakes: All Orders
    const allCakes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/custom-cakes?date=all',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${boleMgrToken}`, 'Accept': 'application/json' }
    });
    const allCakesList = allCakes.body.requests || [];
    const allCakesBole = Array.isArray(allCakesList) && allCakesList.every(c => c.branch_id === 2);
    assert(allCakes.status === 200 && allCakesBole, `Bole Manager loads "All" custom cakes (${allCakesList.length} cakes for Bole)`);


    console.log('\n3. BRANCH REPORTING SYSTEM HIERARCHY (STEP 7)');
    // Hierarchy: Staff -> Branch Manager -> Super Admin

    // A. Branch Staff submits daily sales report to their Branch Manager
    const staffReportRes = await request({
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
      report_type: 'Daily Sales',
      title: 'Bole Afternoon Counter Sales Report',
      content: 'Total counter sales: 52 pastries, 14 celebration cakes. Cash collected: ETB 46,200. Register balanced with zero discrepancies.'
    });
    assert(staffReportRes.status === 201, `Branch Staff submits Daily Sales report to Branch Manager (HTTP 201)`);
    const staffReportId = staffReportRes.body.report?.report_id;
    assert(staffReportRes.body.report?.submitted_to_id === 3, `Report destination automatically routed to Bole Branch Manager (User #3)`);

    // B. Branch Manager reviews staff report
    const boleManagerReports = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/staff-reports',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${boleMgrToken}`, 'Accept': 'application/json' }
    });
    const mgrReportsList = Array.isArray(boleManagerReports.body) ? boleManagerReports.body : [];
    const foundStaffReport = mgrReportsList.find(r => r.report_id === staffReportId);
    assert(foundStaffReport !== undefined, `Bole Branch Manager sees the submitted staff report in their review log`);

    // C. Piassa Manager CANNOT see Bole staff report (Multi-branch tenancy)
    const piassaReports = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/staff-reports',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${piassaMgrToken}`, 'Accept': 'application/json' }
    });
    const piassaList = Array.isArray(piassaReports.body) ? piassaReports.body : [];
    const leakedToPiassa = piassaList.some(r => r.report_id === staffReportId);
    assert(!leakedToPiassa, `Piassa Manager strictly blocked from viewing Bole staff report`);

    // D. Branch Manager verifies and forwards the report to Super Admin
    const forwardRes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: `/api/admin/staff-reports/${staffReportId}/review`,
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${boleMgrToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    }, {
      action: 'forward',
      status: 'Forwarded to Super Admin',
      manager_notes: 'Verified against cash register tape and bank deposit voucher.'
    });
    assert(forwardRes.status === 200, `Branch Manager verifies & forwards report to Super Admin (HTTP 200)`);
    assert(forwardRes.body.report?.status === 'Forwarded to Super Admin', `Report status transitioned to "Forwarded to Super Admin"`);

    // E. Branch Manager submits consolidated performance report directly to Super Admin
    const managerConsolidatedRes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/staff-reports',
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${boleMgrToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    }, {
      report_type: 'Branch Performance',
      title: 'Bole Weekly Branch Performance Summary',
      content: 'Consolidated report: Completed 105 customer orders this week. Total branch revenue: ETB 312,400. Stock levels healthy; courier dispatch on time 98%.'
    });
    assert(managerConsolidatedRes.status === 201, `Branch Manager submits consolidated report to Super Admin (HTTP 201)`);
    const consolidatedReportId = managerConsolidatedRes.body.report?.report_id;
    assert(managerConsolidatedRes.body.report?.submitted_to_id === 1, `Consolidated report destination routed to Super Admin (User #1)`);

    // F. Super Admin views reports across all branches
    const superAdminReports = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/staff-reports',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${adminToken}`, 'Accept': 'application/json' }
    });
    const saReportsList = Array.isArray(superAdminReports.body) ? superAdminReports.body : [];
    const sawForwarded = saReportsList.some(r => r.report_id === staffReportId);
    const sawConsolidated = saReportsList.some(r => r.report_id === consolidatedReportId);
    assert(sawForwarded && sawConsolidated, `Super Admin monitors reports from all branches (${saReportsList.length} total reports available)`);

    // G. Super Admin filters reports by branch and date
    const filteredReports = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/staff-reports?branch_id=2&report_type=Daily%20Sales',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${adminToken}`, 'Accept': 'application/json' }
    });
    const filteredList = Array.isArray(filteredReports.body) ? filteredReports.body : [];
    const allFilteredMatch = filteredList.every(r => r.branch_id === 2 && r.report_type === 'Daily Sales');
    assert(filteredReports.status === 200 && allFilteredMatch, `Super Admin can filter reports by branch and report type`);

    // H. Super Admin marks report as Resolved
    const resolveRes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: `/api/admin/staff-reports/${consolidatedReportId}/review`,
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${adminToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    }, {
      action: 'resolve',
      status: 'Resolved',
      manager_notes: 'Reviewed and acknowledged by Super Admin.'
    });
    assert(resolveRes.status === 200, `Super Admin marks report as Resolved (HTTP 200)`);
    assert(resolveRes.body.report?.status === 'Resolved', `Report status confirmed as "Resolved"`);

    console.log(`\nResults: ${passed} / ${total} tests passed (${Math.round((passed / total) * 100)}%)`);

    if (passed === total) {
      console.log('>>> ALL STEP 6 & STEP 7 TESTS PASSED PERFECTLY! <<<\n');
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
