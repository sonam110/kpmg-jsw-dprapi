<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Mail;
use App\Mail\UpdatePasswordMail;
use Hash;

class UpdatePassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:password';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Here the password of the user will be changed automatically.
        1: Admin password update every 46 days. 
        2: Normal users password update every 91 days
    ';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $otherThenAdmin91Days = date("Y-m-d",strtotime('-91 days'));

        $admin46Days = date("Y-m-d",strtotime('-46 days'));
        $users = User::where('status', 1)
            ->where(function($q) use ($otherThenAdmin91Days, $admin46Days) {
                $q->whereNull('password_last_updated')
                ->orWhere('password_last_updated', $otherThenAdmin91Days)
                ->orWhere('password_last_updated', $admin46Days);
            })
        ->get();
        foreach ($users as $key => $user) 
        {
            User::where('id',$user->id)
            ->update([
                'password' => Hash::make(generateRandomString(12)),
                'password_last_updated'=> date('Y-m-d')
            ]);

            // for email template please check common-mail.blade.php file.
            $content = [
                "name" => $user->name,
                "body" => 'Your password has been changed due to our password changed policy. Please click on forgot password link from login screen and set your new password.',
            ];

            if (env('IS_MAIL_ENABLE', false) == true) 
            {
                $recevier = Mail::to(@$user->email)->send(new UpdatePasswordMail($content));
            }
        }
        return;
    }
}
