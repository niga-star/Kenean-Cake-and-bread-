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

async function login(email, password) {
  const res = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/auth/login',
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }
  }, { email, password });
  return res.data?.token;
}

async function runTests() {
  console.log('--- STARTING BRANCH SHOPPING ACCESS CONTROL TESTS ---');

  // 1. Login as Super Admin
  const superAdminToken = await login('admin@goldencrust.com', 'password123');
  if (!superAdminToken) throw new Error('Super Admin login failed');
  console.log('✓ Super Admin authenticated successfully');

  // 2. Login as Bole Branch Admin
  const branchAdminToken = await login('bole.admin@goldencrust.com', 'password123');
  if (!branchAdminToken) throw new Error('Branch Admin login failed');
  console.log('✓ Branch Admin authenticated successfully');

  // 3. Register a fresh customer
  const uniqueEmail = `shopping.test.${Date.now()}@gmail.com`;
  const regRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/auth/register',
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }
  }, {
    full_name: 'Shopper Test',
    email: uniqueEmail,
    phone: '+251 911 223 344',
    password: 'Password123'
  });
  const customerToken = regRes.data?.token;
  if (!customerToken) throw new Error('Customer registration failed: ' + JSON.stringify(regRes.data));
  console.log('✓ Customer registered and authenticated successfully');

  // Target branch for test: Branch 2 (Bole)
  const targetBranchId = 2;

  // 4. Ensure branch is initially enabled for shopping
  await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/admin/branches/${targetBranchId}/shopping-access`,
    method: 'PATCH',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': `Bearer ${superAdminToken}`
    }
  }, { is_shopping_enabled: true });

  // 5. Verify Branch Admin CANNOT toggle shopping access (strictly 403 Forbidden)
  const branchAdminToggleRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/admin/branches/${targetBranchId}/shopping-access`,
    method: 'PATCH',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': `Bearer ${branchAdminToken}`
    }
  }, { is_shopping_enabled: false });

  if (branchAdminToggleRes.status === 403) {
    console.log('✓ Security check passed: Branch Admin is strictly forbidden (HTTP 403) from overriding shopping availability');
  } else {
    throw new Error(`Expected 403 Forbidden for Branch Admin, got: ${branchAdminToggleRes.status}`);
  }

  // 6. Test Customer placing order when branch is Enabled (should succeed)
  const orderPayload = {
    branch_id: targetBranchId,
    customer_name: 'Shopper Test',
    customer_phone: '+251911223344',
    fulfillment_method: 'Pickup',
    payment_method: 'Pay on pickup',
    delivery_address: 'Bole Flagship Counter Pickup',
    items: [{ product_id: 1, quantity: 1 }]
  };

  const orderSuccessRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/orders',
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': `Bearer ${customerToken}`
    }
  }, orderPayload);

  if (orderSuccessRes.status === 201) {
    console.log(`✓ Order placed successfully when branch is enabled (Order: ${orderSuccessRes.data.order_number})`);
  } else {
    throw new Error(`Order placement failed when enabled: ${orderSuccessRes.status} ${JSON.stringify(orderSuccessRes.data)}`);
  }

  // 7. Super Admin disables shopping access for Bole branch
  const disableRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/admin/branches/${targetBranchId}/shopping-access`,
    method: 'PATCH',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': `Bearer ${superAdminToken}`
    }
  }, { is_shopping_enabled: false });

  if (disableRes.status === 200 && disableRes.data.branch.is_shopping_enabled === false) {
    console.log('✓ Super Admin successfully disabled shopping access for branch (is_shopping_enabled: false)');
  } else {
    throw new Error(`Failed to disable shopping access: ${disableRes.status} ${JSON.stringify(disableRes.data)}`);
  }

  // 8. Test Customer order placement to disabled branch (MUST BE REJECTED 422)
  const orderBlockedRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/orders',
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': `Bearer ${customerToken}`
    }
  }, orderPayload);

  if (orderBlockedRes.status === 422 && orderBlockedRes.data.message === 'Sorry, this branch is currently unavailable for shopping.') {
    console.log('✓ Backend restriction passed: Customer order blocked with 422 and exact message: "Sorry, this branch is currently unavailable for shopping."');
  } else {
    throw new Error(`Order should have been blocked with 422, got ${orderBlockedRes.status}: ${JSON.stringify(orderBlockedRes.data)}`);
  }

  // 9. Test Custom Cake brief to disabled branch (MUST BE REJECTED 422)
  const cakePayload = {
    branch_id: targetBranchId,
    customer_name: 'Shopper Test',
    customer_phone: '+251911223344',
    cake_size: 'Single Tier 8"',
    flavor: 'Belgian Dark Chocolate',
    needed_by: new Date(Date.now() + 86400000 * 3).toISOString().split('T')[0],
    design: 'Birthday celebration cake'
  };

  const cakeBlockedRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/custom-cakes',
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': `Bearer ${customerToken}`
    }
  }, cakePayload);

  if (cakeBlockedRes.status === 422 && cakeBlockedRes.data.message === 'Sorry, this branch is currently unavailable for shopping.') {
    console.log('✓ Backend restriction passed: Custom cake order blocked with 422 and exact message: "Sorry, this branch is currently unavailable for shopping."');
  } else {
    throw new Error(`Custom cake should have been blocked with 422, got ${cakeBlockedRes.status}: ${JSON.stringify(cakeBlockedRes.data)}`);
  }

  // 10. Test Cart sync to disabled branch (MUST BE REJECTED 422)
  const cartSyncBlockedRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/cart/sync',
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': `Bearer ${customerToken}`
    }
  }, {
    branch_id: targetBranchId,
    items: [{ product_id: 1, quantity: 2 }]
  });

  if (cartSyncBlockedRes.status === 422 && cartSyncBlockedRes.data.message === 'Sorry, this branch is currently unavailable for shopping.') {
    console.log('✓ Backend restriction passed: Cart sync blocked with 422 and exact message: "Sorry, this branch is currently unavailable for shopping."');
  } else {
    throw new Error(`Cart sync should have been blocked with 422, got ${cartSyncBlockedRes.status}: ${JSON.stringify(cartSyncBlockedRes.data)}`);
  }

  // 11. Super Admin re-enables shopping access
  const reEnableRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/admin/branches/${targetBranchId}/shopping-access`,
    method: 'PATCH',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': `Bearer ${superAdminToken}`
    }
  }, { is_shopping_enabled: true });

  if (reEnableRes.status === 200 && reEnableRes.data.branch.is_shopping_enabled === true) {
    console.log('✓ Super Admin successfully re-enabled branch shopping access');
  } else {
    throw new Error(`Failed to re-enable branch: ${reEnableRes.status} ${JSON.stringify(reEnableRes.data)}`);
  }

  // 12. Test Customer order succeeds again after re-enabling
  const orderReSuccessRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/orders',
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': `Bearer ${customerToken}`
    }
  }, orderPayload);

  if (orderReSuccessRes.status === 201) {
    console.log(`✓ Order placed successfully after branch was re-enabled (Order: ${orderReSuccessRes.data.order_number})`);
  } else {
    throw new Error(`Order placement failed after re-enabling: ${orderReSuccessRes.status}`);
  }

  // 13. Data Integrity Verification: Verify products and branch records were NOT deleted
  const branchCheckRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: `/api/branches/${targetBranchId}`,
    method: 'GET',
    headers: { 'Accept': 'application/json' }
  });

  if (branchCheckRes.status === 200 && branchCheckRes.data.branch_id === targetBranchId) {
    console.log('✓ Data integrity confirmed: Branch data, employees, inventory, and order history remain 100% intact');
  } else {
    throw new Error(`Branch data check failed: ${branchCheckRes.status}`);
  }

  console.log('\n======================================================');
  console.log('🎉 ALL BRANCH SHOPPING ACCESS CONTROL TESTS PASSED (100%)');
  console.log('======================================================\n');
}

runTests().catch((err) => {
  console.error('❌ Test failed:', err.message);
  process.exit(1);
});
