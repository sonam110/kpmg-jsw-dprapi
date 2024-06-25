<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\DprUploadReminder;
use App\Console\Commands\DeleteOldDprs;


class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected $commands = [
        Commands\DprUploadReminder::class,
         Commands\DeleteOldDprs::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('reminder:dpr-upload')
        ->dailyAt('09:00 AM')
        ->timezone(env('TIME_ZONE', 'Asia/Calcutta'));
        $schedule->command('delete:old-dpr')
        ->dailyAt('06:00 AM')
        ->timezone(env('TIME_ZONE', 'Asia/Calcutta'));
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
