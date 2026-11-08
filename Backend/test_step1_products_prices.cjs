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

async function runStep1Tests() {
  console.log('================================================================');
  console.log('STEP 1: PRODUCT AND PRICE MANAGEMENT VERIFICATION');
  console.log('================================================================\n');

  // --- 1. SUPER ADMIN RESTRICTIONS ---
  console.log('--- 1. Super Admin Removed from Direct Product & Price Management ---');
  const superAdminAuth = await login('admin@goldencrust.com');
  assert(superAdminAuth.token, 'Super Admin authenticated');
  const saToken = superAdminAuth.token;

  // Super Admin tries to create a product -> MUST BE 403
  const saCreateProduct = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/products',
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${saToken}` }
  }, {
    product_name: 'Super Admin Disallowed Cake',
    category_id: 1,
    base_price: 1000
  });
  assert(saCreateProduct.status === 403, 'Super Admin is strictly blocked (HTTP 403) from creating products');

  // Super Admin tries to update product price -> MUST BE 403
  const saSetPrice = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/products/1/branch-price',
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${saToken}` }
  }, {
    branch_id: 2,
    price: 999
  });
  assert(saSetPrice.status === 403, 'Super Admin is strictly blocked (HTTP 403) from setting branch prices');

  // Super Admin tries to update product -> MUST BE 403
  const saUpdateProd = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/products/1',
    method: 'PATCH',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${saToken}` }
  }, { product_name: 'Hacked Base Product' });
  assert(saUpdateProd.status === 403, 'Super Admin is strictly blocked (HTTP 403) from directly modifying products');

  // --- 2. BRANCH MANAGER FULL CONTROL OF OWN BRANCH ---
  console.log('\n--- 2. Branch Manager Independent Product & Price Control ---');
  const boleManagerAuth = await login('bole.admin@goldencrust.com');
  assert(boleManagerAuth.token, 'Bole Branch Manager authenticated (Branch ID: 2)');
  const bmToken = boleManagerAuth.token;

  // Bole Manager sets price for Bole branch (branch_id: 2) -> SHOULD SUCCEED
  const setBolePriceRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/products/1/branch-price',
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${bmToken}` }
  }, {
    branch_id: 2,
    price: 920
  });
  assert(setBolePriceRes.status === 200 || setBolePriceRes.status === 201, 'Bole Branch Manager successfully updated price for Bole Branch (ETB 920)');

  // Bole Manager tries to set price for Piassa branch (branch_id: 4) -> MUST BE 403
  const boleSetsPiassaPrice = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/products/1/branch-price',
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${bmToken}` }
  }, {
    branch_id: 4,
    price: 1500
  });
  assert(boleSetsPiassaPrice.status === 403, 'Bole Manager is strictly forbidden (HTTP 403) from setting prices for another branch (Piassa)');

  // Verify changes made by Bole manager did NOT affect other branches
  const catalogBole = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/products?branch_id=2',
    method: 'GET',
    headers: { 'Accept': 'application/json' }
  });
  const boleProduct1 = (catalogBole.data || []).find(p => p.product_id === 1);
  assert(Number(boleProduct1?.price) === 920, 'Bole branch catalog shows updated price: ETB 920');

  const catalogPiassa = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/products?branch_id=4',
    method: 'GET',
    headers: { 'Accept': 'application/json' }
  });
  const piassaProduct1 = (catalogPiassa.data || []).find(p => p.product_id === 1);
  assert(Number(piassaProduct1?.price) !== 920, 'Piassa branch price is completely unaffected by Bole manager change');

  // Bole Manager creates a branch product
  const testProductName = `Bole Special Cake ${Date.now()}`;
  const createProductRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/products',
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${bmToken}` }
  }, {
    product_name: testProductName,
    category_id: 1,
    base_price: 780,
    shelf_life_hours: 48,
    description: 'Freshly baked specialty cake by Bole branch team'
  });
  assert(createProductRes.status === 201, 'Bole Branch Manager successfully created a new product (HTTP 201)');
  const newProdId = createProductRes.data?.product_id;

  // --- 3. BRANCH SELLER READ-ONLY PERMISSIONS ---
  console.log('\n--- 3. Branch Seller Read-Only Verification ---');
  const sellerAuth = await login('seller.bole@goldencrust.com');
  assert(sellerAuth.token, 'Branch Seller (Bole) authenticated');
  const sellerToken = sellerAuth.token;

  // Seller can view products & prices freely
  const sellerCatalog = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/products?branch_id=2',
    method: 'GET',
    headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${sellerToken}` }
  });
  assert(sellerCatalog.status === 200, 'Branch Seller can view products and prices (HTTP 200)');
  assert(Array.isArray(sellerCatalog.data) && sellerCatalog.data.length > 0, 'Branch Seller receives product list');

  // Seller tries to modify price -> MUST BE 403
  const sellerPriceEdit = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/admin/products/${newProdId}/branch-price`,
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${sellerToken}` }
  }, {
    branch_id: 2,
    price: 50
  });
  assert(sellerPriceEdit.status === 403, 'Branch Seller is blocked (HTTP 403) from modifying product prices');

  // Seller tries to create a product -> MUST BE 403
  const sellerCreate = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/products',
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${sellerToken}` }
  }, { product_name: 'Seller Fake Product', category_id: 1, base_price: 100 });
  assert(sellerCreate.status === 403, 'Branch Seller is blocked (HTTP 403) from creating products');

  // Seller tries to delete a product -> MUST BE 403
  const sellerDelete = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/admin/products/${newProdId}`,
    method: 'DELETE',
    headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${sellerToken}` }
  });
  assert(sellerDelete.status === 403, 'Branch Seller is blocked (HTTP 403) from deleting products');

  // --- 4. AUDIT LOG RECORDING ---
  console.log('\n--- 4. Audit Log Verification for Product & Price Changes ---');
  const auditRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/audit-logs',
    method: 'GET',
    headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${saToken}` }
  });
  const auditLogs = auditRes.data?.logs || auditRes.data?.data || [];
  const priceChangeLog = auditLogs.find(l => l.action === 'Price change' && l.record?.includes('Bole'));
  assert(priceChangeLog !== undefined, 'Audit log recorded Bole Branch price change with actor and old/new values');

  // --- 5. BRANCH MANAGER SUBMITS PRICE UPDATE REPORT TO SUPER ADMIN ---
  console.log('\n--- 5. Branch Manager Price Update Report to Super Admin ---');
  const reportRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/products/report-price-update',
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${bmToken}` }
  }, {
    title: 'Weekly Price Adjustment Notice - Bole Branch',
    content: 'Adjusted prices for Chocolate Cake (ETB 920) and seasonal pastries due to fresh strawberry cost inflation. Inventory levels healthy.'
  });
  assert(reportRes.status === 201, 'Branch Manager submitted Product & Price Update Report to Super Admin (HTTP 201)');

  // Super Admin checks received reports
  const saReports = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/staff-reports?report_type=' + encodeURIComponent('Price Update'),
    method: 'GET',
    headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${saToken}` }
  });
  const priceReports = saReports.data || [];
  const foundReport = (Array.isArray(priceReports) ? priceReports : priceReports.data || []).find(r => r.report_type === 'Price Update');
  assert(foundReport !== undefined, 'Super Admin received the submitted price update report in executive reporting');

  // Clean up created test product using Bole Branch Manager
  await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/admin/products/${newProdId}`,
    method: 'DELETE',
    headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${bmToken}` }
  });

  console.log('\n================================================================');
  console.log(`STEP 1 RESULTS: ${passed} PASSED, ${failed} FAILED`);
  console.log('================================================================');

  if (failed > 0) process.exit(1);
}

runStep1Tests().catch(err => {
  console.error('Fatal error during test run:', err);
  process.exit(1);
});
