<?php

namespace App\Console;

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
        // Commands\WorkingDelete::class, //แก้ไขบรรนี้เพื่อ class command ที่เราได้สร้างขึ้น, กรณีเรามีการสร้าง command หลายๆ command เราก็มาใส่เรียงต่อๆ
        // '\App\Console\Commands\WorkingDelete'
        Commands\WorkingDelete::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('WorkingDelete')->everyMinute();

        // $schedule->call(function () {
        // $working_old =   DB::table('workings')->delete();

        // $date_now = date("Y/m/d H:i:s");
        // $add7_date = date('Y-m-d H:i:s', strtotime($date_now . ' +7 day'));
        // $working_old =   DB::table('workings')->where('work_status', 1)->get();
        // for ($i = 0; $i < count($working_old); $i++) {
        //     if ($working_old[$i]->created_at > $add7_date) {
        //         $del_working =  Working::find($working_old->id);
        //         $del_working->status_del = 0;
        //         $del_working->save();
        //     }
        // }
        // })->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
