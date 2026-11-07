const http = require('http');

function postContact(data) {
  return new Promise((resolve, reject) => {
    const payload = JSON.stringify(data);
    const req = http.request(
      {
        hostname: '127.0.0.1',
        port: 8000,
        path: '/api/contact',
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Content-Length': Buffer.byteLength(payload),
        },
      },
      (res) => {
        let body = '';
        res.on('data', (chunk) => (body += chunk));
        res.on('end', () => {
          try {
            resolve({ status: res.statusCode, body: JSON.parse(body || '{}') });
          } catch (e) {
            resolve({ status: res.statusCode, raw: body });
          }
        });
      }
    );
    req.on('error', reject);
    req.write(payload);
    req.end();
  });
}

function assert(condition, message) {
  if (!condition) {
    console.error(`[FAIL] ${message}`);
    process.exit(1);
  }
  console.log(`[PASS] ${message}`);
}

async function runTests() {
  console.log('========================================================');
  console.log('RUNNING CONTACT FORM VALIDATION VERIFICATION SUITE');
  console.log('========================================================\n');

  // 1. Required fields test
  console.log('TEST GROUP 1: All Fields Required');
  let res = await postContact({});
  assert(res.status === 422, 'Empty form submission rejected with HTTP 422');
  assert(res.body.errors?.name !== undefined, 'Name is marked required');
  assert(res.body.errors?.email !== undefined, 'Email is marked required');
  assert(res.body.errors?.phone !== undefined, 'Phone is marked required');
  assert(res.body.errors?.message !== undefined, 'Message is marked required');

  // 2. Full Name validation (min 3, max 25, letters and spaces only - no numbers or special characters)
  console.log('\nTEST GROUP 2: Full Name (min 3, max 25 chars, letters & spaces only)');
  res = await postContact({
    name: 'Al',
    email: 'valid@gmail.com',
    phone: '0911223344',
    message: 'Valid message for testing.',
  });
  assert(res.status === 422, 'Name with 2 chars rejected with HTTP 422');
  assert(
    res.body.errors?.name?.[0]?.includes('3 characters'),
    'Error message states minimum 3 characters required'
  );

  res = await postContact({
    name: 'A'.repeat(26),
    email: 'valid@gmail.com',
    phone: '0911223344',
    message: 'Valid message for testing.',
  });
  assert(res.status === 422, 'Name with 26 chars rejected with HTTP 422');
  assert(
    res.body.errors?.name?.[0]?.includes('25 characters'),
    'Error message states maximum 25 characters allowed'
  );

  // Test numbers in name
  res = await postContact({
    name: 'Sara123',
    email: 'valid@gmail.com',
    phone: '0911223344',
    message: 'Valid message for testing.',
  });
  assert(res.status === 422, 'Name with numbers "Sara123" rejected with HTTP 422');
  assert(
    res.body.errors?.name?.[0]?.toLowerCase().includes('letters'),
    'Error message states name must contain only letters and spaces'
  );

  // Test special characters in name
  const invalidNames = ['Sara@Cake', 'John_Doe', 'Alice!', 'Abebe#1', 'Cake$Shop'];
  for (const n of invalidNames) {
    res = await postContact({
      name: n,
      email: 'valid@gmail.com',
      phone: '0911223344',
      message: 'Valid message for testing.',
    });
    assert(res.status === 422, `Name with special character "${n}" strictly rejected with HTTP 422`);
  }

  // Test valid Amharic letters in name
  res = await postContact({
    name: 'ሳራ መንግስቱ',
    email: 'sara.amharic@gmail.com',
    phone: '0911223344',
    message: 'Valid message for testing.',
  });
  assert(res.status === 201, 'Amharic name "ሳራ መንግስቱ" successfully accepted with HTTP 201');

  // 3. Email validation (valid email + must follow @gmail.com)
  console.log('\nTEST GROUP 3: Email (Gmail format requirement)');
  res = await postContact({
    name: 'Sara Mengistu',
    email: 'notanemail',
    phone: '0911223344',
    message: 'Valid message for testing.',
  });
  assert(res.status === 422, 'Malformed email rejected with HTTP 422');

  res = await postContact({
    name: 'Sara Mengistu',
    email: 'sara@yahoo.com',
    phone: '0911223344',
    message: 'Valid message for testing.',
  });
  assert(res.status === 422, 'Non-gmail email (yahoo.com) rejected with HTTP 422');
  assert(
    res.body.errors?.email?.[0]?.toLowerCase().includes('gmail'),
    'Error specifies Gmail format requirement'
  );

  res = await postContact({
    name: 'Sara Mengistu',
    email: 'sara@outlook.com',
    phone: '0911223344',
    message: 'Valid message for testing.',
  });
  assert(res.status === 422, 'Non-gmail email (outlook.com) rejected with HTTP 422');

  // 4. Phone Number validation (Ethiopian mobile phone formats)
  console.log('\nTEST GROUP 4: Phone Number (Ethiopian mobile formats: 09, 07, +2519, +2517)');
  const invalidPhones = ['0811223344', '12345', '091122334', '09112233445', '+1234567890', 'abcdefg'];
  for (const phone of invalidPhones) {
    res = await postContact({
      name: 'Sara Mengistu',
      email: 'sara.sweetbakes@gmail.com',
      phone: phone,
      message: 'Valid message for testing.',
    });
    assert(res.status === 422, `Invalid phone "${phone}" strictly rejected with HTTP 422`);
  }

  // 5. Message validation (min 10, max 300)
  console.log('\nTEST GROUP 5: Message (min 10, max 300 chars)');
  res = await postContact({
    name: 'Sara Mengistu',
    email: 'sara.sweetbakes@gmail.com',
    phone: '0911223344',
    message: 'Short',
  });
  assert(res.status === 422, 'Message under 10 chars rejected with HTTP 422');
  assert(
    res.body.errors?.message?.[0]?.includes('10 characters'),
    'Error states minimum 10 characters required'
  );

  res = await postContact({
    name: 'Sara Mengistu',
    email: 'sara.sweetbakes@gmail.com',
    phone: '0911223344',
    message: 'X'.repeat(301),
  });
  assert(res.status === 422, 'Message over 300 chars rejected with HTTP 422');
  assert(
    res.body.errors?.message?.[0]?.includes('300 characters'),
    'Error states maximum 300 characters allowed'
  );

  // 6. Valid submissions for all 4 Ethiopian formats
  console.log('\nTEST GROUP 6: Valid Submissions with All 4 Ethiopian Phone Formats');
  const validCases = [
    { name: 'Sara Mengistu', email: 'sara.cake@gmail.com', phone: '0911234567', message: 'Inquiry for chocolate fudge cake.' },
    { name: 'Dawit Abebe', email: 'dawit.ethio@gmail.com', phone: '0711234567', message: 'Would like to order 5 sourdough loaves.' },
    { name: 'Helina Tadesse', email: 'helina.t@gmail.com', phone: '+251911234567', message: 'Inquiring about custom birthday celebration cake.' },
    { name: 'Abel Tesfaye', email: 'abel.pastry@gmail.com', phone: '+251711234567', message: 'Do you deliver to Bole Medhanialem daily?' },
    { name: 'Mekdes Alazar', email: 'mekdes.a@gmail.com', phone: '+251 911 223 344', message: 'Checking wedding cake booking availability for next month.' },
  ];

  for (const c of validCases) {
    res = await postContact(c);
    assert(res.status === 201, `Valid submission for "${c.name}" with phone "${c.phone}" accepted with HTTP 201`);
    assert(res.body.message !== undefined, 'Success response message returned from server');
  }

  console.log('\n========================================================');
  console.log('ALL CONTACT FORM VALIDATION TESTS PASSED PERFECTLY!');
  console.log('========================================================');
}

runTests();

