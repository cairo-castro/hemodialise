<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanExpiredSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sessions:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean expired sessions from database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sessionLifetime = config('session.lifetime');
        $expirationTime = now()->subMinutes($sessionLifetime)->timestamp;

        $deleted = DB::table(config('session.table', 'sessions'))
            ->where('last_activity', '<', $expirationTime)
            ->delete();

        $this->info("Deleted {$deleted} expired session(s) from database.");

        return 0;
    }
}
