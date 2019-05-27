<?php

namespace App\Console;

use App\Console\Commands\JdStoreAudit;
use App\Console\Commands\MyBankStoreAudit;
use App\Console\Commands\NewLandStoreAudit;
use App\Console\Commands\WxStoreAudit;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
        MyBankStoreAudit::class,
        JdStoreAudit::class,
        NewLandStoreAudit::class,
        //   WxStoreAudit::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
        //$schedule->command('backup:clean')->daily()->at('01:00');
        // $schedule->command('backup:run')->daily()->at('02:00');
        $schedule->command('mybank-audit')->everyMinute();
        $schedule->command('jd-audit')->everyTenMinutes();
        $schedule->command('newland-audit')->hourly();//每小时调用
        //  $schedule->command('wx-audit')->everyMinute();

        //$schedule->command('backup:clean')->everyMinute();
        //$schedule->command('backup:run')->everyMinute();


    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
