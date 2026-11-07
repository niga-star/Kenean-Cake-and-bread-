<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$users = User::select('user_id', 'full_name', 'email', 'role')->get();
echo "Remaining Users Count: " . $users->count() . "\n";
foreach ($users as $u) {
    echo "ID {$u->user_id}: {$u->full_name} ({$u->email}) - Role: {$u->role}\n";
}
