<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ColocationInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $colocationName,
        public string $token
    ) {}

    public function build()
    {
        return $this->subject('Invitation to join a colocation')
            ->view('emails.invitation');
    }
}