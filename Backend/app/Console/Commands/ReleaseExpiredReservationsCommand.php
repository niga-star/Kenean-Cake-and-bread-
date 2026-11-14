<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\OrderController;

class ReleaseExpiredReservationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'orders:release-expired';

    /**
     * The console command description.
     */
    protected $description = 'Release unpaid online order inventory reservations that exceeded the timeout window';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expired unpaid online order reservations...');

        $releasedCount = OrderController::releaseExpiredReservations();

        if ($releasedCount > 0) {
            $this->info("Successfully released reservations for {$releasedCount} expired order(s).");
        } else {
            $this->info('No expired order reservations found.');
        }

        return Command::SUCCESS;
    }
}

