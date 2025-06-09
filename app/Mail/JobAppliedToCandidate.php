<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\JobPanel;
use App\Models\User;

class JobAppliedToCandidate extends Mailable
{
    use Queueable, SerializesModels;

    public JobPanel $job;
    public User $candidate;

    /**
     * Create a new message instance.
     */
    public function __construct(JobPanel $job, User $candidate)
    {
        $this->job = $job;
        $this->candidate = $candidate;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmation: You Applied for ' . $this->job->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.jobs.applied_candidate', // Points to resources/views/emails/jobs/applied_candidate.blade.php
            with: [
                'jobTitle' => $this->job->title,
                'jobDescription' => $this->job->description,
                'candidateName' => $this->candidate->name,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}