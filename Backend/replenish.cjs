const { execFileSync } = require('child_process');
const code = `
  $pdo = new PDO('mysql:host=127.0.0.1;dbname=kenan_cake_db', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
  $pdo->exec("UPDATE branch_inventory SET quantity_on_hand = 500, reserved_quantity = 0");
  echo "Stock replenished across all branches.\\n";
`;
console.log(execFileSync('php', ['-r', code], { encoding: 'utf-8' }));
