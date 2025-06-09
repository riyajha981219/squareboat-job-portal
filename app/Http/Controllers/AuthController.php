<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule; // Import Rule for enum-like validation


class AuthController extends Controller
{
    /**
     * Handle user registration.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        // 1. Validate incoming request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed', // 'confirmed' checks for 'password_confirmation' field
            'role' => ['required', 'string', Rule::in(['candidate', 'recruiter'])], // Validate role
        ]);

        // 2. Handle validation failure
        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                "message" => "Validation failed.",
                "errors" => $validator->errors() // Return specific validation errors
            ], 422);
        }

        // 3. Create the user
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password), // Hash the password
                'role' => $request->role, // Assign the role from the request
            ]);

            // 4. Generate an API token for the new user using Laravel Sanctum
            $token = $user->createToken('auth_token')->plainTextToken;

            // 5. Return success response with user data and token
            return response()->json([
                "success" => true,
                "message" => "User registered successfully.",
                "data" => [
                    "user" => $user,
                    "access_token" => $token,
                    "token_type" => "Bearer",
                ]
            ], 201); // Use 201 Created for successful resource creation

        } catch (\Exception $e) {
            // Handle any unexpected errors during user creation
            return response()->json([
                "success" => false,
                "message" => "Could not register user. Please try again.",
                "error" => $e->getMessage() // For debugging; remove in production
            ], 500); // Internal Server Error
        }
    }

    /**
     * Handle user login.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // 1. Validate incoming request data
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                "message" => "Validation failed.",
                "errors" => $validator->errors()
            ], 422);
        }

        // 2. Attempt to authenticate the user
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                "success" => false,
                "message" => "Invalid login credentials."
            ], 401); // 401 Unauthorized for bad credentials
        }

        // 3. Get the authenticated user
        $user = Auth::user();
        $user->tokens()->delete();

        // 5. Generate a new API token for the authenticated user
        $token = $user->createToken('auth_token')->plainTextToken;

        // 6. Return success response with user data and token
        return response()->json([
            "success" => true,
            "message" => "Login successful.",
            "data" => [
                "user" => $user,
                "access_token" => $token,
                "token_type" => "Bearer",
            ]
        ]);
    }

    /**
     * Handle user logout (revoke current token).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Get the current authenticated user's token and delete it
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            "success" => true,
            "message" => "Logged out successfully."
        ]);
    }

    /**
     * Get authenticated user details.
     * This method requires an authenticated token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function user(Request $request)
    {
        return response()->json([
            "success" => true,
            "data" => $request->user()
        ]);
    }
}
