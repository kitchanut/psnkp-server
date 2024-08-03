<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Working;

class WorkingDelete extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:WorkingDelete';

    /**
     * The console command description.
     *
     * @var string
     */
    // protected $description = 'Command description';
    protected $description = 'Working delete every minute using cron job.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // return 0;
        $date_now = date("Y/m/d H:i:s");
        $add7_date = date('Y-m-d H:i:s', strtotime($date_now . ' +7 day'));
        $working_old =   DB::table('workings')->where('work_status', 1)->get();
        for ($i = 0; $i < count($working_old); $i++) {
            if ($working_old[$i]->created_at > $add7_date) {
                $del_working =  Working::find($working_old->id);
                $del_working->status_del = 0;
                $del_working->save();
            }
        }
    }
}
