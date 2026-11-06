<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cart;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PurgeExpiredRecordsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:purge-expired';

    /**
     * The console command description.
     */
    protected $description = 'Purge inactive user carts (older than 7 days) and expired password reset tokens';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting automated purge of expired records...');

        // 1. Purge inactive carts older than 7 days
        $cartCutoff = Carbon::now()->subDays(7);
        $expiredCarts = Cart::where('updated_at', '<', $cartCutoff)->get();
        $cartsCount = $expiredCarts->count();

        foreach ($expiredCarts as $cart) {
            $cart->items()->delete();
            $cart->delete();
        }

        $this->info("Purged {$cartsCount} inactive user cart(s) older than 7 days.");

        // 2. Purge expired password reset tokens
        $tokensPurged = DB::table('password_reset_tokens')
            ->where('expires_at', '<', Carbon::now())
            ->orWhereNotNull('used_at')
            ->delete();

        $this->info("Purged {$tokensPurged} expired or used password reset token(s).");

        // 3. Log audit action
        AuditLog::create([
            'user_id' => null,
            'user_name' => 'System / Cron',
            'action' => 'Expired records purged',
            'record' => 'Carts & Reset Tokens',
            'entity_type' => 'Maintenance',
            'entity_id' => (string) Carbon::now()->timestamp,
            'old_value' => null,
            'new_value' => "Carts purged: {$cartsCount}, Tokens purged: {$tokensPurged}",
            'created_at' => Carbon::now(),
        ]);

        $this->info('Automated maintenance purge completed successfully.');
        return Command::SUCCESS;
    }
}
