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

class JobAppliedToRecruiter extends Mailable
{
    use Queueable, SerializesModels;

    public Job $job;
    public User $candidate;
    public User $recruiter;

    /**
     * Create a new message instance.
     */
    public function __construct(JobPanel $job, User $candidate, User $recruiter)
    {
        $this->job = $job;
        $this->candidate = $candidate;
        $this->recruiter = $recruiter;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Applicant for Your Job: ' . $this->job->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.jobs.applied_recruiter',
            with: [
                'jobTitle' => $this->job->title,
                'jobDescription' => $this->job->description,
                'candidateName' => $this->candidate->name,
                'candidateEmail' => $this->candidate->email,
                'recruiterName' => $this->recruiter->name,
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