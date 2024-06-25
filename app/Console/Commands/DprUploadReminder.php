<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Mail;
use App\Mail\DprUploadReminderMail;
use App\Models\User;
use App\Models\DprImport;
use Illuminate\Contracts\Encryption\DecryptException;

class DprUploadReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminder:dpr-upload';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command informs all the vendors who have not uploaded the DPR report till 09:00 AM.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $vendors = User::where('role_id', 2)
            ->where('status', 1)
            ->get();
        foreach ($vendors as $key => $vendor) {
            $dprImport = DprImport::whereDate('data_date', date('Y-m-d'))
            ->where('user_id', $vendor->id)
            ->first();
            if(empty($dprImport))
            {
                $content = [
                    "name" => $vendor->name,
                    "body" => 'Please submit your DPR report before 9:30 AM.',
                ];
                if (env('IS_MAIL_ENABLE', false) == true) 
                {
                    $recevier = Mail::to(@$vendor->email)->send(new DprUploadReminderMail($content));
                }
            }
        }
        return;
    }
}
