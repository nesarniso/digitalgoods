<?php

namespace App\Console\Commands;

use App\Domain\Digital\Services\DigitalLicenseService;
use Illuminate\Console\Command;

class ReleaseExpiredDigitalReservations extends Command
{
    protected $signature = 'digital:release-expired {--minutes=30}';

    protected $description = 'Release digital licenses reserved by unpaid orders past the reservation window.';

    public function handle(DigitalLicenseService $licenses): int
    {
        $count = $licenses->releaseExpiredReservations((int) $this->option('minutes'));
        $this->info("Released {$count} expired digital reservation(s).");

        return self::SUCCESS;
    }
}
