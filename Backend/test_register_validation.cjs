const http = require('http');

function postJson(path, payload) {
  return new Promise((resolve, reject) => {
    const data = JSON.stringify(payload);
    const headers = {
      'Content-Type': 'application/json',
      'Content-Length': Buffer.byteLength(data),
      'Accept': 'application/json',
    };

    const req = http.request({
      hostname: '127.0.0.1',
      port: 8000,
      path: path,
      method: 'POST',
      headers,
    }, res => {
      let body = '';
      res.on('data', chunk => body += chunk);
      res.on('end', () => {
        try {
          resolve({ status: res.statusCode, data: JSON.parse(body) });
        } catch (e) {
          resolve({ status: res.statusCode, raw: body });
        }
      });
    });
    req.on('error', reject);
    req.write(data);
    req.end();
  });
}

async function run() {
  console.log('========================================================');
  console.log('RUNNING REGISTER VALIDATION (NAME & GMAIL) TEST SUITE');
  console.log('========================================================');

  let passed = 0;
  let failed = 0;

  function assert(cond, msg) {
    if (cond) {
      console.log(`[PASS] ${msg}`);
      passed++;
    } else {
      console.error(`[FAIL] ${msg}`);
      failed++;
    }
  }

  const basePayload = () => ({
    full_name: 'Sara Mengistu',
    email: `valid.user.${Date.now()}@gmail.com`,
    phone: '0911223344',
    password: 'password123',
  });

  // 1. Full Name Required
  console.log('\n--- 1. Full Name Required ---');
  let res = await postJson('/api/auth/register', { ...basePayload(), full_name: '' });
  assert(res.status === 422, 'Empty full name rejected with HTTP 422');
  assert(!!res.data?.errors?.full_name, 'Full name validation error present in response');

  // 2. Full Name Min 3 Chars
  console.log('\n--- 2. Full Name Min Length (3) ---');
  res = await postJson('/api/auth/register', { ...basePayload(), full_name: 'Ab' });
  assert(res.status === 422, 'Full name with 2 characters rejected with HTTP 422');
  assert(res.data?.errors?.full_name?.[0]?.includes('3 characters'), 'Error states minimum 3 characters required');

  // 3. Full Name Max 25 Chars
  console.log('\n--- 3. Full Name Max Length (25) ---');
  res = await postJson('/api/auth/register', { ...basePayload(), full_name: 'Abebe Kebede Wolde Yohannes Smith' });
  assert(res.status === 422, 'Full name exceeding 25 characters rejected with HTTP 422');
  assert(res.data?.errors?.full_name?.[0]?.includes('25 characters'), 'Error states maximum 25 characters allowed');

  // 4. Full Name Letters Only (No Numbers, No Symbols)
  console.log('\n--- 4. Full Name Letters & Spaces Only ---');
  const invalidNames = ['Sara123', 'John_Doe', 'Alice!', 'Bob@Cake', 'Cake#Bake', 'Dan$iel'];
  for (const inv of invalidNames) {
    res = await postJson('/api/auth/register', { ...basePayload(), full_name: inv });
    assert(res.status === 422, `Invalid name "${inv}" strictly rejected with HTTP 422`);
  }

  // 5. Full Name Supports Amharic Letters
  console.log('\n--- 5. Full Name with Amharic Letters ---');
  res = await postJson('/api/auth/register', { ...basePayload(), full_name: 'ሳራ መንግስቱ' });
  assert(res.status === 201, 'Amharic name "ሳራ መንግስቱ" successfully accepted with HTTP 201');

  // 6. Email Required
  console.log('\n--- 6. Email Required ---');
  res = await postJson('/api/auth/register', { ...basePayload(), email: '' });
  assert(res.status === 422, 'Empty email rejected with HTTP 422');
  assert(!!res.data?.errors?.email, 'Email validation error present in response');

  // 7. Non-Gmail format strictly rejected
  console.log('\n--- 7. Email Must Follow Gmail Format ---');
  const invalidEmails = [
    'admin@goldencrust.com', // Exactly from user screenshot
    'test@yahoo.com',
    'hello@outlook.com',
    'user@domain.org',
    'notanemail',
    'someone@gmail.co',
  ];
  for (const inv of invalidEmails) {
    res = await postJson('/api/auth/register', { ...basePayload(), email: inv });
    assert(res.status === 422, `Non-Gmail email "${inv}" strictly rejected with HTTP 422`);
    const emailErr = res.data?.errors?.email?.[0] || '';
    assert(emailErr.includes('Gmail format') || emailErr.includes('valid email'), `Error message mentions Gmail or valid email for "${inv}"`);
  }

  // 8. Valid Registration
  console.log('\n--- 8. Valid Registration with Letters-Only Name and Gmail ---');
  const validEmail = `success.register.${Date.now()}@gmail.com`;
  res = await postJson('/api/auth/register', {
    full_name: 'Dawit Abebe',
    email: validEmail,
    phone: '0911223344',
    password: 'password123',
  });
  assert(res.status === 201, 'Valid registration created with HTTP 201');
  assert(!!res.data?.token, 'Received authentication token');
  assert(res.data?.user?.full_name === 'Dawit Abebe', 'User full_name is correctly saved');
  assert(res.data?.user?.email === validEmail, 'User email is correctly saved');

  console.log('\n========================================================');
  console.log(`REGISTER VALIDATION RESULTS: ${passed} PASSED, ${failed} FAILED`);
  console.log('========================================================');

  if (failed > 0) {
    process.exit(1);
  }
}

run().catch(err => {
  console.error('Test execution error:', err);
  process.exit(1);
});
