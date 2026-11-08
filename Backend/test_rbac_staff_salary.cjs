const http = require('http');

function request(options, data) {
  return new Promise((resolve, reject) => {
    const req = http.request(options, (res) => {
      let body = '';
      res.on('data', (chunk) => body += chunk);
      res.on('end', () => {
        try {
          resolve({ status: res.statusCode, headers: res.headers, data: JSON.parse(body) });
        } catch {
          resolve({ status: res.statusCode, headers: res.headers, data: body });
        }
      });
    });
    req.on('error', reject);
    if (data) req.write(typeof data === 'string' ? data : JSON.stringify(data));
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
  return { token: res.data?.token, user: res.data?.user, status: res.status };
}

let passed = 0;
let failed = 0;

function assert(condition, description) {
  if (condition) {
    console.log(`  [PASS] ${description}`);
    passed++;
  } else {
    console.error(`  [FAIL] ${description}`);
    failed++;
  }
}

async function runTestSuite() {
  console.log('================================================================');
  console.log('RBAC, STAFF MANAGEMENT, SALARY & REPORTING TEST SUITE');
  console.log('================================================================\n');

  // --- 1. SUPER ADMIN CONTROL ---
  console.log('--- 1. Super Admin Authentication & Full Visibility ---');
  const superAdminAuth = await login('admin@goldencrust.com');
  assert(superAdminAuth.token, 'Super Admin login succeeded');
  const saToken = superAdminAuth.token;

  // Fetch branch quotas
  const quotasRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/branches-quotas',
    method: 'GET',
    headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${saToken}` }
  });
  assert(quotasRes.status === 200, 'Super Admin can fetch branch quotas (HTTP 200)');
  assert(Array.isArray(quotasRes.data) && quotasRes.data.length >= 4, 'Branch quotas list returned for all branches');
  const boleQuota = quotasRes.data.find(q => q.branch_code === 'BR001');
  assert(boleQuota && boleQuota.max_staff > 0, `Bole branch quota: max_staff=${boleQuota?.max_staff}, max_managers=${boleQuota?.max_managers}`);

  // Fetch all users
  const usersRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/users',
    method: 'GET',
    headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${saToken}` }
  });
  assert(usersRes.status === 200, 'Super Admin can fetch all users across all branches');
  const allUsers = usersRes.data?.data || usersRes.data || [];
  assert(allUsers.length > 5, `Super Admin sees all users across company (Total: ${allUsers.length})`);
  const hasAmharicName = allUsers.some(u => u.full_name_am && u.full_name_am.length > 0);
  assert(hasAmharicName, 'Users include Amharic full name (ተያዥ)');
  const hasSalary = allUsers.some(u => u.salary !== undefined && u.salary !== null);
  assert(hasSalary, 'Super Admin can see employee salary values');

  // --- 2. BRANCH MANAGER MANAGEMENT & SCOPING ---
  console.log('\n--- 2. Branch Manager Scoping & Access Restrictions ---');
  const managerAuth = await login('bole.admin@goldencrust.com');
  assert(managerAuth.token, 'Bole Branch Manager authenticated');
  const bmToken = managerAuth.token;

  // Branch Manager views users
  const bmUsersRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/users',
    method: 'GET',
    headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${bmToken}` }
  });
  assert(bmUsersRes.status === 200, 'Branch Manager can view branch users');
  const bmUsers = bmUsersRes.data?.data || bmUsersRes.data || [];
  const otherBranchUsers = bmUsers.filter(u => u.branch_id && u.branch_id !== 2); // 2 is Bole
  assert(otherBranchUsers.length === 0, 'Branch Manager CANNOT see staff from other branches (strictly scoped to Bole)');

  // Branch Manager forbidden from viewing company-wide salaries endpoint
  const bmSalaryAccess = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/salaries',
    method: 'GET',
    headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${bmToken}` }
  });
  assert(bmSalaryAccess.status === 403, 'Branch Manager is forbidden (HTTP 403) from accessing company-wide salary management');

  // Branch Manager cannot edit other branch users
  const piassaUser = allUsers.find(u => u.branch_id === 4 || u.email === 'piassa.admin@goldencrust.com');
  if (piassaUser) {
    const editOtherRes = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: `/api/admin/users/${piassaUser.user_id}`,
      method: 'PUT',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${bmToken}` }
    }, { full_name: 'Hacked Name' });
    assert(editOtherRes.status === 403, 'Branch Manager is forbidden (HTTP 403) from modifying users of another branch');
  }

  // --- 3. CREATING STAFF ROLES (Seller, Servant, Delivery) ---
  console.log('\n--- 3. Staff Role Creation & Quota Verification ---');
  const testStaffEmail = `test.staff.${Date.now()}@goldencrust.com`;
  const createStaffRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/users',
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${saToken}` }
  }, {
    full_name: 'Dawit Yohannes Test',
    full_name_am: 'ዳዊት ዮሐንስ',
    email: testStaffEmail,
    phone_number: '+251 911 888 777',
    role: 'Branch Seller / Cashier',
    branch_id: 2, // Bole
    salary: 9500.00,
    employment_status: 'Active',
    password: 'password123'
  });
  assert(createStaffRes.status === 201, 'Super Admin successfully created "Branch Seller / Cashier" with Amharic name & salary');
  const createdUser = createStaffRes.data?.user || createStaffRes.data;
  assert(createdUser && createdUser.full_name_am === 'ዳዊት ዮሐንስ', 'Amharic name saved correctly: "ዳዊት ዮሐንስ"');

  // --- 4. SALARY AND WAGE MANAGEMENT ---
  console.log('\n--- 4. Salary Management, KPIs & Correction Workflow ---');
  const salariesOverview = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/salaries',
    method: 'GET',
    headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${saToken}` }
  });
  assert(salariesOverview.status === 200, 'Super Admin retrieved salary overview (HTTP 200)');
  assert(salariesOverview.data?.kpis?.total_monthly_payroll > 0, `Total monthly payroll calculated: ETB ${salariesOverview.data?.kpis?.total_monthly_payroll}`);
  assert(Array.isArray(salariesOverview.data?.branch_breakdown), 'Branch-by-branch payroll expense breakdown returned');

  // Super Admin updates salary of newly created user
  const updateSalaryRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/admin/salaries/${createdUser.user_id}`,
    method: 'PUT',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${saToken}` }
  }, {
    salary: 10500.00,
    reason: 'Performance promotion adjustment'
  });
  assert(updateSalaryRes.status === 200, 'Super Admin successfully updated employee salary to ETB 10,500');

  // Staff member logs in to view My Account
  const staffAuth = await login(testStaffEmail);
  assert(staffAuth.token, 'Created staff member logged in successfully');
  const staffToken = staffAuth.token;

  // Staff member views My Account salary
  const staffMyAccount = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/salaries/my-account',
    method: 'GET',
    headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${staffToken}` }
  });
  assert(staffMyAccount.status === 200, 'Staff member accessed own profile via My Account endpoint');
  assert(Number(staffMyAccount.data?.user?.salary) === 10500, 'Staff member correctly sees assigned salary (ETB 10,500)');

  // Staff member cannot directly edit salary
  const staffUnauthorizedEdit = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/admin/salaries/${createdUser.user_id}`,
    method: 'PUT',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${staffToken}` }
  }, { salary: 25000 });
  assert(staffUnauthorizedEdit.status === 403, 'Staff member is strictly blocked (HTTP 403) from editing own salary');

  // Staff member submits a salary correction request
  const submitRequestRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/salary-requests',
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${staffToken}` }
  }, {
    requested_salary: 11500,
    reason: 'Overtime and weekend shift allowance adjustment per contract'
  });
  assert(submitRequestRes.status === 201, 'Staff member successfully submitted salary correction request (HTTP 201)');
  const requestId = submitRequestRes.data?.request?.request_id || submitRequestRes.data?.data?.request_id;

  // Super Admin reviews and approves the correction request
  const approveRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/admin/salary-requests/${requestId}/review`,
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${saToken}` }
  }, {
    action: 'Approved',
    admin_notes: 'Approved after review with HR'
  });
  assert(approveRes.status === 200, 'Super Admin approved salary correction request');

  // Verify employee salary updated to 11,500 automatically
  const verifySalaryUpdated = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/salaries/my-account',
    method: 'GET',
    headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${staffToken}` }
  });
  assert(Number(verifySalaryUpdated.data?.user?.salary) === 11500, 'Employee salary automatically updated to ETB 11,500 upon approval');

  // --- 5. REPORTING HIERARCHY ---
  console.log('\n--- 5. Reporting Hierarchy (Staff -> Branch Manager -> Super Admin) ---');
  // Staff submits a daily sales report to their Branch Manager
  const staffReportRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/staff-reports',
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${staffToken}` }
  }, {
    report_type: 'Daily Sales',
    title: 'Daily Sales Summary for Today',
    content: 'Total counter sales: 42 cakes, revenue: ETB 35,700. All orders delivered on time.'
  });
  assert(staffReportRes.status === 201, 'Staff submitted daily sales report to Branch Manager (HTTP 201)');
  const reportId = staffReportRes.data?.report?.report_id || staffReportRes.data?.data?.report_id;

  // Branch Manager reviews and forwards report to Super Admin
  const forwardRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/admin/staff-reports/${reportId}/review`,
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${bmToken}` }
  }, {
    action: 'forward',
    manager_notes: 'Reviewed and confirmed. Branch counter sales exceeded daily target.'
  });
  assert(forwardRes.status === 200, 'Branch Manager reviewed and forwarded report to Super Admin');

  // Super Admin reviews and marks as resolved
  const resolveRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/admin/staff-reports/${reportId}/review`,
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${saToken}` }
  }, {
    action: 'resolve',
    manager_notes: 'Acknowledged by Super Admin.'
  });
  assert(resolveRes.status === 200, 'Super Admin marked forwarded staff report as resolved');

  // --- 6. AUDIT LOG SCOPING ---
  console.log('\n--- 6. Audit Log Scoping by Role ---');
  const saLogs = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/audit-logs',
    method: 'GET',
    headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${saToken}` }
  });
  assert(saLogs.status === 200, 'Super Admin retrieved comprehensive audit logs');

  const bmLogs = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/audit-logs',
    method: 'GET',
    headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${bmToken}` }
  });
  assert(bmLogs.status === 200, 'Branch Manager retrieved audit logs');
  const bmLogItems = bmLogs.data?.logs || bmLogs.data?.data || (Array.isArray(bmLogs.data) ? bmLogs.data : []);
  assert(Array.isArray(bmLogItems), 'Branch Manager audit logs returned as array');

  // --- 7. CUSTOMER SECURITY ---
  console.log('\n--- 7. Customer Access Restrictions to Admin APIs ---');
  const custAuth = await login('customer@gmail.com');
  if (custAuth.token) {
    const custAdminAccess = await request({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/salaries',
      method: 'GET',
      headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${custAuth.token}` }
    });
    assert(custAdminAccess.status === 403 || custAdminAccess.status === 401, 'Customer is strictly blocked from /admin/salaries (HTTP 403)');
  }

  // Clean up created test user
  await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/admin/users/${createdUser.user_id}`,
    method: 'DELETE',
    headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${saToken}` }
  });

  console.log('\n================================================================');
  console.log(`RESULTS: ${passed} PASSED, ${failed} FAILED`);
  console.log('================================================================');

  if (failed > 0) process.exit(1);
}

runTestSuite().catch(err => {
  console.error('Fatal error during test run:', err);
  process.exit(1);
});
