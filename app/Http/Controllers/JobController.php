<?php

namespace App\Http\Controllers;

use App\Models\JobPanel;
use App\Models\User;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\JobAppliedToCandidate;
use App\Mail\JobAppliedToRecruiter;


class JobController extends Controller
{
    /**
     * [RECRUITER] Post a new job.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postJob(Request $request)
    {

        // Direct role check: Only recruiters can post jobs
        if (!Auth::check() || !Auth::user()->isRecruiter()) {
            return response()->json([
                "success" => false,
                "message" => "Unauthorized: Only recruiters can post jobs."
            ], 403); // 403 Forbidden
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                "message" => "Validation failed.",
                "errors" => $validator->errors()
            ], 422);
        }

        try {
            $job = JobPanel::create([
                'recruiter_id' => Auth::id(),
                'title' => $request->title,
                'description' => $request->description,
            ]);

            return response()->json([
                "success" => true,
                "message" => "Job posted successfully.",
                "data" => $job
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => "Failed to post job.",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * [CANDIDATE] See a list of all available jobs.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function listJobs()
    {
        if (!Auth::check() || !Auth::user()->isCandidate()) {
            return response()->json([
                "success" => false,
                "message" => "Unauthorized: Only candidates can view all jobs."
            ], 403); // 403 Forbidden
        }
        // Get all jobs, ordered by latest first
        $jobs = JobPanel::orderBy('created_at', 'desc')->get();

        return response()->json([
            "success" => true,
            "message" => "All available jobs retrieved successfully.",
            "data" => $jobs
        ]);
    }

    /**
     * [CANDIDATE] Apply to a job.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $job_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function applyToJob(Request $request, $job_id)
    {
        if (!Auth::check() || !Auth::user()->isCandidate()) {
            return response()->json([
                "success" => false,
                "message" => "Unauthorized: Only candidates can apply to jobs."
            ], 403); // 403 Forbidden
        }
        $job = JobPanel::find($job_id);

        if (!$job) {
            return response()->json([
                "success" => false,
                "message" => "Job not found."
            ], 404);
        }

        $candidate = Auth::user();

        // Check if candidate has already applied to this job
        if ($candidate->applications()->where('job_id', $job_id)->exists()) {
            return response()->json([
                "success" => false,
                "message" => "You have already applied for this job."
            ], 409); // 409 Conflict
        }

        try {
            // Attach the job to the candidate's applications using the many-to-many relationship
            // $candidate->applications()->attach($job_id);
            Application::create([
                "candidate_id" => $candidate->id,
                "job_id" => $job->id,

            ]);

            // Send email to candidate
            Mail::to($candidate->email)->send(new JobAppliedToCandidate($job, $candidate));

            // Send email to recruiter
            $recruiter = User::find($job->recruiter_id);
            if ($recruiter) {
                Mail::to($recruiter->email)->send(new JobAppliedToRecruiter($job, $candidate, $recruiter));
            }


            return response()->json([
                "success" => true,
                "message" => "Successfully applied to the job. Confirmation emails sent."
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => "Failed to apply to job.",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * [CANDIDATE] See a list of jobs they have applied to.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function listAppliedJobs(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isCandidate()) {
            return response()->json([
                "success" => false,
                "message" => "Unauthorized: Only candidates can view their applications."
            ], 403); // 403 Forbidden
        }
        $candidate = Auth::user();
        // Get all jobs the candidate has applied to, with pivot table timestamps
        $appliedJobs = $candidate->applications()->orderBy('applications.created_at', 'desc')->get();

        return response()->json([
            "success" => true,
            "message" => "Jobs you have applied to retrieved successfully.",
            "data" => $appliedJobs
        ]);
    }

    /**
     * [RECRUITER] See a list of applicants who have applied to the jobs posted previously.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getApplicants(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isRecruiter()) {
            return response()->json([
                "success" => false,
                "message" => "Unauthorized: Only recruiters can view applicants."
            ], 403); // 403 Forbidden
        }
        $recruiter = Auth::user();

        // Get all jobs posted by the authenticated recruiter
        $jobs = $recruiter->postedJobs()->with(['applicants' => function ($query) {
            // Eager load applicants and select only necessary candidate fields
            $query->select('users.id', 'users.name', 'users.email');
        }])->get();

        // Structure the response to show jobs and their applicants
        $data = $jobs->map(function ($job) {
            return [
                'job_id' => $job->id,
                'job_title' => $job->title,
                'applicants' => $job->applicants->map(function ($applicant) {
                    return [
                        'id' => $applicant->id,
                        'name' => $applicant->name,
                        'email' => $applicant->email,
                        'applied_at' => $applicant->pivot->created_at, // Access pivot data
                    ];
                })
            ];
        });

        return response()->json([
            "success" => true,
            "message" => "Applicants for your jobs retrieved successfully.",
            "data" => $data
        ]);
    }
}
