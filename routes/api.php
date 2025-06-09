v\<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\JobController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public routes (no authentication required)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Authenticated routes - Common for both roles (e.g., logout, get user info)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
});

// Candidate specific routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/jobs', [JobController::class, 'listJobs']);
    Route::post('/jobs/{job_id}/apply', [JobController::class, 'applyToJob']);
    Route::get('/my-applications', [JobController::class, 'listAppliedJobs']);
});

// Recruiter specific routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/jobs', [JobController::class, 'postJob']);
    Route::get('/my-posted-jobs/applicants', [JobController::class, 'getApplicants']);
});
