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
  console.log('--- TESTING STEPS 4 & 5: BRANCH RBAC & SALARY MANAGEMENT ---');
  console.log('============================================================\n');

  try {
    const adminToken = await login('admin@goldencrust.com');
    const boleMgrToken = await login('bole.admin@goldencrust.com');
    const boleStaffToken = await login('bole.staff@goldencrust.com');

    console.log('1. BRANCH-BASED ACCESS CONTROL ENFORCEMENT (STEP 4)');
    
    // A. Bole Manager cannot view staff from other branches (Piassa)
    const boleUsers = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/users',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${boleMgrToken}`, 'Accept': 'application/json' }
    });
    const boleUserList = Array.isArray(boleUsers.body) ? boleUsers.body : [];
    const allBoleStaff = boleUserList.every(u => u.branch_id === 2);
    assert(boleUsers.status === 200 && allBoleStaff, `Bole Manager sees only Bole branch staff (${boleUserList.length} members)`);

    // B. Bole Manager blocked from updating Piassa staff (#5)
    const updateOtherStaff = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/users/5',
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${boleMgrToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    }, { full_name: 'Hacked Piassa Manager' });
    assert(updateOtherStaff.status === 403, `Bole Manager blocked from updating another branch staff (HTTP ${updateOtherStaff.status})`);

    // C. Bole Manager blocked from Branch Management administration endpoints
    const addBranch = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/branches',
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${boleMgrToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    }, { branch_name: 'Rogue Branch' });
    assert(addBranch.status === 403, `Bole Manager blocked from adding bakery branches (HTTP ${addBranch.status})`);

    // D. Bole Manager blocked from adjusting inventory for Piassa (branch_id 4)
    const adjustOtherInventory = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/inventory/adjust',
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${boleMgrToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    }, {
      branch_id: 4,
      product_id: 1,
      type: 'damaged',
      quantity: 5,
      reason: 'Unauthorized branch cross-adjustment'
    });
    assert(adjustOtherInventory.status === 403, `Bole Manager blocked from adjusting another branch inventory (HTTP ${adjustOtherInventory.status})`);

    // E. Bole Manager blocked from submitting stock requests for Piassa
    const reqOtherStock = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/stock-requests',
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${boleMgrToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    }, {
      branch_id: 4,
      items: [{ product_id: 1, quantity_requested: 20 }]
    });
    assert(reqOtherStock.status === 403, `Bole Manager blocked from requesting stock for another branch (HTTP ${reqOtherStock.status})`);

    // F. Branch staff blocked from user management
    const staffUserAccess = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/users',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${boleStaffToken}`, 'Accept': 'application/json' }
    });
    assert(staffUserAccess.status === 403, `Branch Staff blocked from user & staff management (HTTP ${staffUserAccess.status})`);


    console.log('\n2. SALARY AND WAGE MANAGEMENT & CLICKABLE BRANCH DETAILS (STEP 5)');
    
    // A. Super Admin retrieves salary breakdown
    const salaryData = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/salaries',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${adminToken}`, 'Accept': 'application/json' }
    });
    assert(salaryData.status === 200, `Super Admin loads salary management (HTTP ${salaryData.status})`);
    
    const summary = salaryData.body.summary;
    assert(summary && summary.total_wage_expenses > 0, `Combined salary expenses calculated (ETB ${summary?.total_wage_expenses?.toLocaleString()})`);
    assert(summary && summary.total_employees > 0, `Total employees count present (${summary?.total_employees} staff)`);

    const breakdown = salaryData.body.branch_breakdown;
    assert(Array.isArray(breakdown) && breakdown.length > 0, `Branch wage breakdown present (${breakdown.length} branches)`);
    const mainBranchBreakdown = breakdown.find(b => b.branch_code === 'MAIN01');
    assert(mainBranchBreakdown && mainBranchBreakdown.manager_name, `Branch card includes manager name (${mainBranchBreakdown?.manager_name}) and wage total (ETB ${mainBranchBreakdown?.total_wage})`);

    // B. Super Admin filters salary data by specific branch (e.g. Main Branch, ID: 1)
    const mainBranchSalaries = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/salaries?branch_id=1',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${adminToken}`, 'Accept': 'application/json' }
    });
    assert(mainBranchSalaries.status === 200, `Super Admin clicks branch to filter salaries (HTTP ${mainBranchSalaries.status})`);
    const mainEmployees = mainBranchSalaries.body.employees || [];
    const allMain = mainEmployees.every(e => e.branch_id === 1);
    assert(allMain && mainEmployees.length > 0, `Clicking branch returns only employees assigned to that branch (${mainEmployees.length} staff)`);
    
    // Check that employee record includes full English name, Amharic name, role, phone, and salary
    const firstEmp = mainEmployees[0];
    assert(firstEmp.full_name && firstEmp.role && typeof firstEmp.salary === 'number', `Employee record contains full_name, role, and salary`);
    assert('phone' in firstEmp && 'full_name_am' in firstEmp, `Employee record contains phone number and Amharic name field`);

    // C. Super Admin edits employee salary
    const targetUserId = firstEmp.user_id;
    const newSalary = Number(firstEmp.salary || 10000) + 500;
    const updateSalaryRes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: `/api/admin/salaries/${targetUserId}`,
      method: 'PATCH',
      headers: {
        'Authorization': `Bearer ${adminToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    }, {
      salary: newSalary,
      reason: 'Bi-annual performance wage increase'
    });
    assert(updateSalaryRes.status === 200, `Super Admin can edit employee salary (HTTP ${updateSalaryRes.status})`);
    assert(updateSalaryRes.body.user?.salary === newSalary, `Updated salary persisted to ETB ${newSalary}`);

    // D. Branch Manager blocked from editing salaries
    const mgrEditSalary = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: `/api/admin/salaries/${targetUserId}`,
      method: 'PATCH',
      headers: {
        'Authorization': `Bearer ${boleMgrToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    }, {
      salary: 99999
    });
    assert(mgrEditSalary.status === 403, `Branch Manager strictly blocked from modifying salaries (HTTP ${mgrEditSalary.status})`);

    // E. Branch Staff can view own salary information
    const myAccountRes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/salaries/my-account',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${boleStaffToken}`, 'Accept': 'application/json' }
    });
    assert(myAccountRes.status === 200, `Branch Staff can view own salary profile (HTTP ${myAccountRes.status})`);
    assert(typeof myAccountRes.body.user?.salary === 'number', `Staff profile includes monthly salary ETB ${myAccountRes.body.user?.salary}`);

    // F. Verify Audit Log recorded salary adjustment
    const auditRes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/audit-logs',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${adminToken}`, 'Accept': 'application/json' }
    });
    const logs = Array.isArray(auditRes.body) ? auditRes.body : (auditRes.body.logs || auditRes.body.data || []);
    const salaryLog = logs.find(l => l.action === 'Salary updated');
    assert(salaryLog !== undefined, `Audit log successfully recorded the salary update with actor and values (${salaryLog?.user}: ${salaryLog?.old_value} -> ${salaryLog?.new_value})`);

    console.log(`\nResults: ${passed} / ${total} tests passed (${Math.round((passed / total) * 100)}%)`);

    if (passed === total) {
      console.log('>>> ALL STEP 4 & STEP 5 TESTS PASSED PERFECTLY! <<<\n');
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
