<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class sendReportToEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $content;

    public function __construct($content) {
        $this->content = $content;
    }
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {
        $htmlFilePath = $this->content['FilePath']; 
        $filename =  $this->content['FileName']; 
        $mime =  $this->content['mime']; 
        return $this->markdown('email.send-report-to-mail')
            ->from(env('MAIL_FROM_ADDRESS','support@kpmg.in'),'KPMG Team')
            ->subject('KPMG Report')
            ->attach($htmlFilePath, [
                'as' => $filename,
                'mime' => $mime,
            ])
            ->with($this->content);
    }
}
