<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\OnboardingCall;
use App\Models\Lead;

class OnboardingCallScheduled extends Mailable
{
    use Queueable, SerializesModels;

    public $call;
    public $lead;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(OnboardingCall $call, Lead $lead)
    {
        $this->call = $call;
        $this->lead = $lead;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Llamada de Onboarding Programada - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'emails.onboarding-call-scheduled',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
