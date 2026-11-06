const { execFileSync } = require('child_process');
const http = require('http');

function queryDb(sql) {
  const code = `
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=kenan_cake_db', 'root', '', [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    $stmt = $pdo->query(${JSON.stringify(sql)});
    echo json_encode($stmt->fetchAll());
  `;
  const output = execFileSync('php', ['-r', code], { encoding: 'utf-8' });
  return JSON.parse(output);
}

function executeDb(sql) {
  const code = `
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=kenan_cake_db', 'root', '', [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $pdo->exec(${JSON.stringify(sql)});
    echo "OK";
  `;
  return execFileSync('php', ['-r', code], { encoding: 'utf-8' });
}

function request(options, body = null) {
  return new Promise((resolve, reject) => {
    const req = http.request(options, (res) => {
      let data = '';
      res.on('data', (chunk) => (data += chunk));
      res.on('end', () => {
        try {
          const parsed = JSON.parse(data);
          resolve({ status: res.statusCode, data: parsed });
        } catch (e) {
          resolve({ status: res.statusCode, raw: data });
        }
      });
    });
    req.on('error', reject);
    if (body) {
      req.write(typeof body === 'string' ? body : JSON.stringify(body));
    }
    req.end();
  });
}

async function runSection11Tests() {
  console.log('================================================================');
  console.log('STARTING SECTION 11: DATABASE DESIGN VERIFICATION TEST');
  console.log('================================================================\n');

  let passed = 0;
  let failed = 0;

  function assert(condition, message) {
    if (condition) {
      console.log(`[PASS] ${message}`);
      passed++;
    } else {
      console.error(`[FAIL] ${message}`);
      failed++;
    }
  }

  // 1. Verify 28 Domain Tables Presence
  const expectedTables = [
    'users', 'branches', 'addresses', 'delivery_zones',
    'categories', 'products', 'product_images', 'branch_product_prices',
    'branch_inventory', 'inventory_transactions',
    'stock_transfers', 'stock_transfer_items', 'stock_requests', 'stock_request_items',
    'carts', 'cart_items', 'orders', 'order_items', 'order_status_history', 'payments',
    'custom_cake_requests', 'custom_cake_images',
    'reviews', 'promotions', 'promotion_products', 'notifications', 'password_reset_tokens', 'audit_logs'
  ];

  const tableRows = queryDb("SELECT table_name FROM information_schema.tables WHERE table_schema = 'kenan_cake_db'");
  const presentTableNames = tableRows.map(r => r.table_name || r.TABLE_NAME);

  assert(expectedTables.length === 28, 'Specification defines exactly 28 domain tables');

  let allTablesPresent = true;
  for (const t of expectedTables) {
    if (!presentTableNames.includes(t)) {
      console.error(`Missing expected table: ${t}`);
      allTablesPresent = false;
    }
  }
  assert(allTablesPresent, 'All 28 domain tables exist in MySQL kenan_cake_db');

  // 2. Verify Auto-Increment Integer Primary Keys on all 28 tables
  const pkRows = queryDb(`
    SELECT TABLE_NAME, COLUMN_NAME, EXTRA, DATA_TYPE
    FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = 'kenan_cake_db' AND COLUMN_KEY = 'PRI' AND TABLE_NAME IN (${expectedTables.map(t => `'${t}'`).join(',')})
  `);
  
  const pkMap = {};
  pkRows.forEach(r => {
    const t = r.TABLE_NAME || r.table_name;
    const col = r.COLUMN_NAME || r.column_name;
    const extra = r.EXTRA || r.extra;
    const dtype = r.DATA_TYPE || r.data_type;
    pkMap[t] = { col, extra, dtype };
  });

  assert(Object.keys(pkMap).length === 28, `Found primary keys on all 28 tables (${Object.keys(pkMap).length}/28)`);
  
  let allAutoIncrement = true;
  for (const t of expectedTables) {
    if (!pkMap[t] || !pkMap[t].extra.includes('auto_increment')) {
      console.error(`Table ${t} PK is not auto_increment:`, pkMap[t]);
      allAutoIncrement = false;
    }
  }
  assert(allAutoIncrement, '100% of all 28 tables use auto-increment integer primary keys');

  // 3. Verify Unique Constraints
  const uqRows = queryDb(`
    SELECT TABLE_NAME, CONSTRAINT_NAME 
    FROM information_schema.TABLE_CONSTRAINTS 
    WHERE TABLE_SCHEMA = 'kenan_cake_db' AND CONSTRAINT_TYPE = 'UNIQUE'
  `);
  const uqList = uqRows.map(r => `${r.TABLE_NAME || r.table_name}.${r.CONSTRAINT_NAME || r.constraint_name}`);

  assert(uqList.some(c => c.includes('branches') && c.includes('branch_code')), 'Unique constraint on branches.branch_code');
  assert(uqList.some(c => c.includes('users') && c.includes('email')), 'Unique constraint on users.email');
  assert(uqList.some(c => c.includes('orders') && c.includes('order_number')), 'Unique constraint on orders.order_number');
  assert(uqList.some(c => c.includes('stock_transfers') && c.includes('transfer_number')), 'Unique constraint on stock_transfers.transfer_number');
  assert(uqList.some(c => c.includes('payments') && c.includes('transaction_reference')), 'Unique constraint on payments.transaction_reference');
  assert(uqList.some(c => c.includes('branch_inventory') && c.includes('branch_id')), 'Unique pair on branch_inventory (branch_id, product_id)');
  assert(uqList.some(c => c.includes('branch_product_prices') && c.includes('branch_id')), 'Unique pair on branch_product_prices (branch_id, product_id)');

  // 4. Verify Performance Indexes
  const idxRows = queryDb(`
    SELECT TABLE_NAME, INDEX_NAME, GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) AS COLS
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = 'kenan_cake_db'
    GROUP BY TABLE_NAME, INDEX_NAME
  `);

  const indexes = idxRows.map(r => ({
    table: r.TABLE_NAME || r.table_name,
    index: r.INDEX_NAME || r.index_name,
    cols: r.COLS || r.cols,
  }));

  const hasOrdersIdx = indexes.some(i => i.table === 'orders' && i.cols === 'branch_id,order_status,placed_at');
  assert(hasOrdersIdx, 'Index on orders (branch_id, order_status, placed_at) for branch dashboards');

  const hasTxIdx = indexes.some(i => i.table === 'inventory_transactions' && i.cols === 'branch_id,product_id,created_at');
  assert(hasTxIdx, 'Index on inventory_transactions (branch_id, product_id, created_at) for stock history');

  const hasTransfersIdx = indexes.some(i => i.table === 'stock_transfers' && i.cols === 'destination_branch_id,status');
  assert(hasTransfersIdx, 'Index on stock_transfers (destination_branch_id, status) for pending receipts');

  const hasNotifIdx = indexes.some(i => i.table === 'notifications' && i.cols === 'user_id,is_read');
  assert(hasNotifIdx, 'Index on notifications (user_id, is_read)');

  // 5. Verify Check Constraints in Database Engine
  const chkRows = queryDb(`
    SELECT TABLE_NAME, CONSTRAINT_NAME, CHECK_CLAUSE 
    FROM information_schema.CHECK_CONSTRAINTS 
    WHERE CONSTRAINT_SCHEMA = 'kenan_cake_db'
  `);
  const checkClauses = chkRows.map(r => `${r.TABLE_NAME || r.table_name}.${r.CONSTRAINT_NAME || r.constraint_name}: ${r.CHECK_CLAUSE || r.check_clause}`);

  assert(checkClauses.some(c => c.includes('branch_inventory') && c.includes('chk_on_hand_non_negative')), 'Engine CHECK constraint: quantity_on_hand >= 0');
  assert(checkClauses.some(c => c.includes('branch_inventory') && c.includes('chk_reserved_non_negative')), 'Engine CHECK constraint: reserved_quantity >= 0');
  assert(checkClauses.some(c => c.includes('branch_inventory') && c.includes('chk_reserved_lte_on_hand')), 'Engine CHECK constraint: reserved_quantity <= quantity_on_hand');
  assert(checkClauses.some(c => c.includes('stock_transfers') && c.includes('chk_source_diff_dest')), 'Engine CHECK constraint: source_branch_id <> destination_branch_id');
  assert(checkClauses.some(c => c.includes('stock_transfer_items') && c.includes('chk_quantity_sent_positive')), 'Engine CHECK constraint: quantity_sent > 0');

  // Test triggering CHECK constraint violation directly at MySQL engine level
  let checkBlocked = false;
  try {
    executeDb("UPDATE branch_inventory SET quantity_on_hand = -5 LIMIT 1");
  } catch (err) {
    checkBlocked = true;
    assert(err.message.includes('chk_on_hand_non_negative') || err.message.includes('CONSTRAINT') || err.message.includes('23000'), `MySQL engine strictly blocked negative on-hand: ${err.message.split('\n')[0]}`);
  }
  assert(checkBlocked, 'Database engine rejected negative stock update');

  // 6. Verify Only One Branch has branch_type = 'Main' Rule
  const mainBranches = queryDb("SELECT branch_id, branch_name, branch_code FROM branches WHERE branch_type = 'Main'");
  assert(mainBranches.length === 1, `Exactly one branch is branch_type = 'Main': ${mainBranches[0].branch_name} (${mainBranches[0].branch_code})`);

  let duplicateMainBlocked = false;
  try {
    executeDb("UPDATE branches SET branch_type = 'Main' WHERE branch_id = 2");
  } catch (err) {
    duplicateMainBlocked = true;
    assert(err.message.includes('Duplicate entry') || err.message.includes('unique_main_branch'), 'Duplicate Main branch blocked by unique virtual column index');
  }
  assert(duplicateMainBlocked, 'Database engine strictly blocked setting a second branch as Main');

  // 7. Verify Price Resolution Hierarchy (Active Branch Override vs Base Price)
  // Set branch override price for branch 2 (Bole)
  const setOverrideRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/products/1/branch-price',
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
  }, {
    branch_id: 2,
    price: 920.00,
    active: true,
  });
  assert(setOverrideRes.status === 200, 'Created active branch price override: ETB 920.00');

  // Query product for Bole branch (where override was set to 920.00)
  const boleProductRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/products/1?branch_id=2',
    method: 'GET',
    headers: { 'Accept': 'application/json' },
  });
  assert(boleProductRes.status === 200, 'Loaded product detail for Bole branch');
  assert(parseFloat(boleProductRes.data.resolved_price) === 920.00, `Bole branch resolves active override price: ETB ${boleProductRes.data.resolved_price}`);
  assert(boleProductRes.data.has_branch_override === true, 'has_branch_override flag is true');

  // Query product for Megenagna branch (branch_id 3) where no override exists
  const megenagnaProductRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/products/1?branch_id=3',
    method: 'GET',
    headers: { 'Accept': 'application/json' },
  });
  assert(parseFloat(megenagnaProductRes.data.resolved_price) === 850.00, `Megenagna falls back to base price: ETB ${megenagnaProductRes.data.resolved_price}`);
  assert(megenagnaProductRes.data.has_branch_override === false, 'has_branch_override flag is false for un-overridden branch');

  // 8. Verify User-Branch Role Constraint
  const invalidUserRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/users',
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
  }, {
    full_name: 'No Branch Admin',
    email: `nobranch.${Date.now()}@example.com`,
    role: 'Branch Admin',
    branch_id: null,
  });
  assert(invalidUserRes.status === 422, 'Creating Branch Admin without branch_id strictly rejected with HTTP 422');
  assert(invalidUserRes.data.message.includes('must have an assigned branch'), 'Correct validation message: "Branch Admin and Branch Staff must have an assigned branch."');

  // Super Admin branch_id must be null
  const superUserRes = await request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/users',
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
  }, {
    full_name: 'Super Admin Test',
    email: `super.${Date.now()}@example.com`,
    role: 'Super Admin',
    branch_id: 2, // Even if branch_id is passed, it must be normalized to null
  });
  assert(superUserRes.status === 201, 'Super Admin created with HTTP 201');
  assert(superUserRes.data.branch_id === null, 'Super Admin branch_id enforced as null');

  console.log('\n================================================================');
  console.log(`SECTION 11 VERIFICATION RESULTS: ${passed} PASSED, ${failed} FAILED`);
  console.log('================================================================');

  if (failed > 0) {
    process.exit(1);
  }
}

runSection11Tests().catch(err => {
  console.error('Fatal test error:', err);
  process.exit(1);
});
