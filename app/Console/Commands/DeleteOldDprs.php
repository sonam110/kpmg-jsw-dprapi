<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Storage;
use App\Models\DprImport;
use App\Models\DprLog;
use File;

class DeleteOldDprs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:old-dpr';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $dprImports = DprImport::withoutGlobalScope('user_id')->whereDate('data_date', '<' , date('Y-m-d',strtotime('-1 month')))->get();
        foreach ($dprImports as $key => $dprImport) {
            $dprLog = DprLog::where('dpr_import_id',$dprImport->id)->first();
            if (!empty($dprLog)) {
                // $path = $dprImport->file_path;
                $path = storage_path('app/public/import/'.$dprLog->import_file);
                if(!empty($path)){
                    unlink($path);
                }
                $dprLog->delete();
            }
            $dprImport->delete();
        }
        return;
    }
}
