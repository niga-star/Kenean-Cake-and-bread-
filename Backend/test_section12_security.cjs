// test_section12_security.cjs
// Comprehensive Automated Test Suite for Section 12: Security, Audit and Non-Functional Requirements

const http = require('http');
const https = require('https');
const fs = require('fs');
const path = require('path');
const { execFileSync } = require('child_process');

const BASE_URL = 'http://127.0.0.1:8000/api';

let totalTests = 0;
let passedTests = 0;
let failedTests = 0;

function assert(condition, message) {
  totalTests++;
  if (condition) {
    passedTests++;
    console.log(`  [PASS] ${message}`);
  } else {
    failedTests++;
    console.error(`  [FAIL] ${message}`);
  }
}

function queryDb(sql) {
  const code = `
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=kenan_cake_db', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    $stmt = $pdo->query(${JSON.stringify(sql)});
    echo json_encode($stmt->fetchAll());
  `;
  const output = execFileSync('php', ['-r', code], { encoding: 'utf-8' });
  return JSON.parse(output);
}

function executeDb(sql) {
  const code = `
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=kenan_cake_db', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec(${JSON.stringify(sql)});
    echo "OK";
  `;
  return execFileSync('php', ['-r', code], { encoding: 'utf-8' });
}

function request(method, endpoint, data = null, headers = {}) {
  return new Promise((resolve, reject) => {
    const url = new URL(BASE_URL + endpoint);
    const isMultipart = data && data.multipart;

    let postBody = '';
    const reqHeaders = { ...headers };

    if (data && !isMultipart) {
      postBody = JSON.stringify(data);
      reqHeaders['Content-Type'] = 'application/json';
      reqHeaders['Content-Length'] = Buffer.byteLength(postBody);
    }

    const options = {
      method,
      hostname: url.hostname,
      port: url.port,
      path: url.pathname + url.search,
      headers: reqHeaders,
    };

    const req = http.request(options, (res) => {
      let rawData = '';
      res.on('data', (chunk) => { rawData += chunk; });
      res.on('end', () => {
        let json = null;
        try {
          json = JSON.parse(rawData);
        } catch (e) {
          json = rawData;
        }
        resolve({
          status: res.statusCode,
          headers: res.headers,
          data: json,
        });
      });
    });

    req.on('error', (err) => reject(err));

    if (postBody) {
      req.write(postBody);
    }
    req.end();
  });
}

function uploadFile(endpoint, fieldName, filename, fileBuffer, mimeType, headers = {}) {
  return new Promise((resolve, reject) => {
    const boundary = '----WebKitFormBoundary' + Math.random().toString(36).substring(2);
    const url = new URL(BASE_URL + endpoint);

    let preBody = `--${boundary}\r\n`;
    preBody += `Content-Disposition: form-data; name="${fieldName}"; filename="${filename}"\r\n`;
    preBody += `Content-Type: ${mimeType}\r\n\r\n`;

    const postBody = `\r\n--${boundary}--\r\n`;

    const preBuffer = Buffer.from(preBody, 'utf8');
    const postBuffer = Buffer.from(postBody, 'utf8');
    const totalLength = preBuffer.length + fileBuffer.length + postBuffer.length;

    const reqHeaders = {
      ...headers,
      'Content-Type': `multipart/form-data; boundary=${boundary}`,
      'Content-Length': totalLength,
      'Accept': 'application/json',
    };

    const options = {
      method: 'POST',
      hostname: url.hostname,
      port: url.port,
      path: url.pathname + url.search,
      headers: reqHeaders,
    };

    const req = http.request(options, (res) => {
      let rawData = '';
      res.on('data', (chunk) => { rawData += chunk; });
      res.on('end', () => {
        let json = null;
        try {
          json = JSON.parse(rawData);
        } catch (e) {
          json = rawData;
        }
        resolve({
          status: res.statusCode,
          headers: res.headers,
          data: json,
        });
      });
    });

    req.on('error', (err) => reject(err));

    req.write(preBuffer);
    req.write(fileBuffer);
    req.write(postBuffer);
    req.end();
  });
}



async function runTests() {
  console.log('================================================================');
  console.log('STARTING SECTION 12 SECURITY, AUDIT & NON-FUNCTIONAL TEST SUITE');
  console.log('================================================================\n');

  try {
    // -------------------------------------------------------------
    // 1. Passwords Hashed with bcrypt & Never Stored / Logged in Plain Text
    // -------------------------------------------------------------
    console.log('--- 1. Password Hashing and Plain-Text Protection ---');
    const usersRows = queryDb("SELECT user_id, email, password FROM users");
    let allBcrypt = true;
    for (const r of usersRows) {
      if (!r.password.startsWith('$2y$')) {
        allBcrypt = false;
      }
    }
    assert(allBcrypt === true, `All ${usersRows.length} database users have passwords hashed with bcrypt ($2y$)`);

    // Verify login returns token without password field
    const loginRes = await request('POST', '/auth/login', {
      email: 'admin@goldencrust.com',
      password: 'password123',
    });
    assert(loginRes.status === 200, 'Super Admin login successful');
    assert(loginRes.data.user && !loginRes.data.user.password, 'User JSON object does NOT contain plain-text or hashed password field');
    const superAdminToken = loginRes.data.token;
    const authSuperHeaders = { Authorization: `Bearer ${superAdminToken}` };

    // Get branch admin token
    const branchLogin = await request('POST', '/auth/login', {
      email: 'bole.admin@goldencrust.com',
      password: 'password123',
    });
    const branchToken = branchLogin.data.token;
    const authBranchHeaders = { Authorization: `Bearer ${branchToken}` };

    // -------------------------------------------------------------
    // 2. Server-side Role Enforcement & Scoping
    // -------------------------------------------------------------
    console.log('\n--- 2. Server-side Role Enforcement & Scoping ---');
    // Branch Admin attempting to access Super-Admin audit logs
    const auditUnauthorizedRes = await request('GET', '/admin/audit-logs', null, authBranchHeaders);
    assert(auditUnauthorizedRes.status === 403, 'Branch Admin is blocked (403 Forbidden) from accessing Audit Logs');

    // Super Admin accessing audit logs
    const auditAuthorizedRes = await request('GET', '/admin/audit-logs', null, authSuperHeaders);
    assert(auditAuthorizedRes.status === 200, 'Super Admin is authorized (200 OK) to access Audit Logs');
    assert(Array.isArray(auditAuthorizedRes.data.logs), 'Audit logs returns structured list of log rows');

    // Audit logs immutability (No PUT or DELETE route exists)
    const auditDeleteRes = await request('DELETE', '/admin/audit-logs/1', null, authSuperHeaders);
    assert(auditDeleteRes.status === 405 || auditDeleteRes.status === 404, 'Audit logs cannot be deleted through the API (404/405 blocked)');

    // -------------------------------------------------------------
    // 3. Password Reset Lifecycle with Single-Use Hashed Tokens
    // -------------------------------------------------------------
    console.log('\n--- 3. Single-Use Password Reset Lifecycle ---');
    const forgotRes = await request('POST', '/auth/forgot-password', {
      email: 'bole.staff@goldencrust.com',
    });
    assert(forgotRes.status === 200, 'Forgot password request succeeds');
    const rawResetToken = forgotRes.data.reset_token;
    assert(typeof rawResetToken === 'string' && rawResetToken.length >= 32, 'Plain reset token returned is cryptographically secure hex string');

    // Verify token hash is stored in database
    const dbTokenCheck = queryDb("SELECT token_hash, used_at, expires_at FROM password_reset_tokens WHERE email = 'bole.staff@goldencrust.com' ORDER BY token_id DESC LIMIT 1");
    const tokenRow = dbTokenCheck[0];
    assert(tokenRow && tokenRow.token_hash.length === 64, 'Token is SHA-256 hashed in database');
    assert(tokenRow.used_at === null, 'Token starts with used_at = NULL');

    // Reset password using the valid token
    const newPassword = 'newPassword456!';
    const resetRes = await request('POST', '/auth/reset-password', {
      email: 'bole.staff@goldencrust.com',
      token: rawResetToken,
      password: newPassword,
      password_confirmation: newPassword,
    });
    assert(resetRes.status === 200, 'Password successfully reset with valid token');

    // Try reusing the same token (Must be rejected)
    const reuseRes = await request('POST', '/auth/reset-password', {
      email: 'bole.staff@goldencrust.com',
      token: rawResetToken,
      password: 'anotherPassword789!',
      password_confirmation: 'anotherPassword789!',
    });
    assert(reuseRes.status === 422, 'Reusing the same password reset token is strictly rejected (422 Unprocessable)');

    // Verify new password works
    const reloginRes = await request('POST', '/auth/login', {
      email: 'bole.staff@goldencrust.com',
      password: newPassword,
    });
    assert(reloginRes.status === 200, 'Login with updated password succeeds');

    // Revert password back for test consistency
    const resetScript = `
      $pdo = new PDO('mysql:host=127.0.0.1;dbname=kenan_cake_db', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
      $hash = password_hash('password123', PASSWORD_BCRYPT);
      $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = 'bole.staff@goldencrust.com'");
      $stmt->execute([$hash]);
      echo "OK";
    `;
    execFileSync('php', ['-r', resetScript], { encoding: 'utf-8' });

    // -------------------------------------------------------------
    // 4. Cake Image Upload Security & Validation
    // -------------------------------------------------------------
    console.log('\n--- 4. Cake Image Upload Security & Validation ---');
    // 4.1 Valid Image Upload (1x1 PNG)
    const validPngBuffer = Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==', 'base64');
    const validUploadRes = await uploadFile('/custom-cakes/upload-image', 'image', 'wedding_cake_ref.png', validPngBuffer, 'image/png');
    assert(validUploadRes.status === 201, 'Valid PNG cake image upload succeeds (201 Created)');
    assert(validUploadRes.data.image_url && validUploadRes.data.image_url.includes('custom_cakes'), 'Uploaded image path is properly scoped');

    // 4.2 Malicious / Disallowed File Type Upload (.txt / .php script)
    const maliciousBuffer = Buffer.from('<?php echo "evil"; ?>', 'utf8');
    const maliciousUploadRes = await uploadFile('/custom-cakes/upload-image', 'image', 'exploit.php', maliciousBuffer, 'application/x-php');
    assert(maliciousUploadRes.status === 422, 'Malicious/non-image upload is strictly rejected (422 Unprocessable)');

    // 4.3 Oversized File Upload (> 5MB)
    const oversizedBuffer = Buffer.alloc(6 * 1024 * 1024, 0); // 6MB dummy
    const oversizedUploadRes = await uploadFile('/custom-cakes/upload-image', 'image', 'massive_cake.jpg', oversizedBuffer, 'image/jpeg');
    assert(oversizedUploadRes.status === 422, 'Oversized file (>5MB) upload is strictly rejected (422 Unprocessable)');

    // -------------------------------------------------------------
    // 5. Order Idempotency Protection
    // -------------------------------------------------------------
    console.log('\n--- 5. Order Idempotency Protection ---');
    const idempotencyKey = 'idem-key-' + Date.now() + '-' + Math.random().toString(36).substring(2, 8);
    const orderPayload = {
      branch_id: 1, // Bole
      fulfillment_method: 'Delivery',
      delivery_address: 'Bole Medhanialem Tower, Addis Ababa',
      payment_method: 'Pay on delivery',
      items: [
        { product_id: 1, quantity: 1 }
      ]
    };

    // First submission with Idempotency-Key
    const firstOrderRes = await request('POST', '/orders', orderPayload, {
      'Idempotency-Key': idempotencyKey,
    });
    assert(firstOrderRes.status === 201, 'First order submission succeeds (201 Created)');
    const createdOrderId = firstOrderRes.data.order_id;
    const createdOrderNumber = firstOrderRes.data.order_number;

    // Immediate second submission with identical Idempotency-Key
    const secondOrderRes = await request('POST', '/orders', orderPayload, {
      'Idempotency-Key': idempotencyKey,
    });
    assert(secondOrderRes.status === 200, 'Duplicate submission with identical Idempotency-Key returns 200 OK');
    assert(secondOrderRes.headers['x-idempotent-replay'] === 'true', 'Header X-Idempotent-Replay: true is returned');
    assert(secondOrderRes.data.order_id === createdOrderId, 'Idempotent replay returns the exact same order without duplicate creation');

    // -------------------------------------------------------------
    // 6. Gateway Webhook Signature Verification
    // -------------------------------------------------------------
    console.log('\n--- 6. Gateway Webhook Signature Verification ---');
    // Create an online pending order for webhook test
    const onlineOrderRes = await request('POST', '/orders', {
      branch_id: 1,
      fulfillment_method: 'Pickup',
      payment_method: 'Chapa',
      items: [
        { product_id: 1, quantity: 1 }
      ]
    });
    assert(onlineOrderRes.status === 201, 'Online order created with Pending payment');
    const pendingPaymentTxRef = onlineOrderRes.data.payment.transaction_reference;

    // Webhook with invalid signature
    const invalidWebhookRes = await request('POST', '/payments/webhook', {
      tx_ref: pendingPaymentTxRef,
      status: 'success',
    }, {
      'x-chapa-signature': 'invalid_secret_spoofed',
    });
    assert(invalidWebhookRes.status === 401, 'Webhook with invalid signature is rejected (401 Unauthorized)');

    // Webhook with missing signature
    const missingWebhookRes = await request('POST', '/payments/webhook', {
      tx_ref: pendingPaymentTxRef,
      status: 'success',
    });
    assert(missingWebhookRes.status === 401, 'Webhook without signature is rejected (401 Unauthorized)');

    // Webhook with valid signature
    const validWebhookRes = await request('POST', '/payments/webhook', {
      tx_ref: pendingPaymentTxRef,
      status: 'success',
    }, {
      'x-chapa-signature': 'whsec_live_89f21a9chapa2026',
    });
    assert(validWebhookRes.status === 200, 'Webhook with verified signature succeeds (200 OK)');
    assert(validWebhookRes.data.order_status === 'Confirmed', 'Order status promoted to Confirmed upon verified webhook');

    // Duplicate webhook callback (Idempotency)
    const dupWebhookRes = await request('POST', '/payments/webhook', {
      tx_ref: pendingPaymentTxRef,
      status: 'success',
    }, {
      'x-chapa-signature': 'whsec_live_89f21a9chapa2026',
    });
    assert(dupWebhookRes.status === 200 && dupWebhookRes.data.message.includes('duplicate callback ignored'), 'Duplicate webhook callback safely ignored without side effects');

    // -------------------------------------------------------------
    // 7. Audit Logging of Sensitive Actions
    // -------------------------------------------------------------
    console.log('\n--- 7. Audit Logging of Sensitive Actions ---');
    const latestAuditCheck = queryDb("SELECT action, record, entity_type FROM audit_logs ORDER BY log_id DESC LIMIT 10");
    const actionNames = latestAuditCheck.map(l => l.action);
    assert(actionNames.some(a => a.includes('Order') || a.includes('Payment') || a.includes('Password')), 'Audit logs capture sensitive actions (Orders, Payments, Passwords)');

    // -------------------------------------------------------------
    // 8. Automated Database Backup & Maintenance Purge
    // -------------------------------------------------------------
    console.log('\n--- 8. Database Backup & Maintenance Purge ---');
    // Super Admin triggering database backup
    const backupRes = await request('POST', '/admin/database/backup', null, authSuperHeaders);
    assert(backupRes.status === 200, 'Super Admin initiates automated database backup successfully');
    assert(backupRes.data.output && backupRes.data.output.includes('Backup successfully written'), 'Backup output confirms SQL file generation');

    // Branch Admin forbidden from initiating backup
    const branchBackupRes = await request('POST', '/admin/database/backup', null, authBranchHeaders);
    assert(branchBackupRes.status === 403, 'Branch Admin forbidden (403) from initiating database backup');

    // Maintenance purge
    const purgeRes = await request('POST', '/admin/maintenance/purge-expired', null, authSuperHeaders);
    assert(purgeRes.status === 200, 'Maintenance purge executed successfully');

    // -------------------------------------------------------------
    // 9. Ethiopian Phone Formatting Validation
    // -------------------------------------------------------------
    console.log('\n--- 9. Ethiopian Phone Formatting Validation ---');
    // Valid Ethiopian phone formats (+251 911 123 456, 0911123456)
    const validRegisterRes = await request('POST', '/auth/register', {
      full_name: 'Test Customer Ethio',
      email: `test_ethio_${Date.now()}@sweetbakes.com`,
      phone: '+251 911 123 456',
      password: 'password123',
    });
    assert(validRegisterRes.status === 201, 'Registration with valid Ethiopian phone (+251 911 123 456) succeeds');

    // Invalid phone format
    const invalidPhoneRes = await request('POST', '/auth/register', {
      full_name: 'Bad Phone User',
      email: `test_badphone_${Date.now()}@sweetbakes.com`,
      phone: '12345',
      password: 'password123',
    });
    assert(invalidPhoneRes.status === 422, 'Registration with invalid phone format is rejected (422 Unprocessable)');

    // -------------------------------------------------------------
    // SUMMARY
    // -------------------------------------------------------------
    console.log('\n================================================================');
    console.log(`TEST RESULTS: ${passedTests} PASSED, ${failedTests} FAILED out of ${totalTests} TESTS`);
    console.log('================================================================\n');

    if (failedTests > 0) {
      process.exit(1);
    } else {
      process.exit(0);
    }

  } catch (error) {
    console.error('Test Suite encountered an unexpected error:', error);
    process.exit(1);
  }
}

runTests();
