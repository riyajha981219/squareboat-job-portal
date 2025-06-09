<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class JobPanel extends Model
{
    use HasFactory;

    protected $table = "jobs_panel";
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'recruiter_id',
        'title',
        'description',
    ];

    /**
     * Get the recruiter that owns the job.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function recruiter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recruiter_id');
    }

    /**
     * The candidates that have applied to this job.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function applicants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'applications', 'job_id', 'candidate_id')->withTimestamps();
    }
}