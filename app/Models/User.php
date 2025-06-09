<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Define relationship for jobs posted by this user (if they are a recruiter).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function postedJobs(): HasMany
    {
        return $this->hasMany(JobPanel::class, 'recruiter_id');
    }

    /**
     * Define many-to-many relationship for jobs a candidate has applied to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function applications(): BelongsToMany
    {
        return $this->belongsToMany(JobPanel::class, 'applications', 'candidate_id', 'job_id')->withTimestamps();
    }

    /**
     * Check if the user is a recruiter.
     *
     * @return bool
     */
    public function isRecruiter(): bool
    {
        return $this->role === 'recruiter';
    }

    /**
     * Check if the user is a candidate.
     *
     * @return bool
     */
    public function isCandidate(): bool
    {
        return $this->role === 'candidate';
    }
}