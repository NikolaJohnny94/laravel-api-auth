<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Repositories\AuthRepository;


class AuthController extends Controller
{
    protected $authRepository;

    public function __construct(AuthRepository $authRepository)
    {
        $this->authRepository = $authRepository;
    }
    /**
     * @OA\Post(
     *     path="/api/registration",
     *     tags={"Auth"},
     *     summary="Register a new user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", description="Name of the user"),
     *             @OA\Property(property="email", type="string", description="Email address of the user"),
     *             @OA\Property(property="password", type="string", description="Password of the user", writeOnly=true),
     *             @OA\Property(property="password_confirmation", type="string", description="Password confirmation", writeOnly=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="New user successfully registered",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed while user tried to register"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="An error occurred during registration"
     *     )
     * )
     */
    public function registration(Request $request)
    {
        try {
            $fields = $request->validate([
                'name' => 'required|string',
                'email' => 'required|string|unique:users,email',
                'password' => 'required|string|confirmed'
            ]);

            $new_user = $this->authRepository->registerNewUser($fields);

            $token = $new_user->createToken('token')->plainTextToken;

            return response(['success' => true, 'data' => $new_user, 'token' => $token], 201);
        } catch (ValidationException $e) {
            return response(['success' => false, 'message' => 'Validation failed while user tried to register', 'error_message' => $e->errors()], 422);
        } catch (Exception $e) {
            return response(['success' => false, 'message' => 'An error occurred during registration.', 'error_message' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Auth"},
     *     summary="Login a user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", description="Email address of the user"),
     *             @OA\Property(property="password", type="string", description="Password of the user", writeOnly=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User successfully logged in",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed while user tried to login"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="An error occurred during login"
     *     )
     * )
     */    public function login(Request $request)
    {
        try {
            $fields = $request->validate([
                'email' => 'required|string',
                'password' => 'required|string'
            ]);

            $user = $this->authRepository->loginUser($fields);

            if (!$user || !Hash::check($fields['password'], $user->password)) return response(['success' => false, 'message' => 'Invalid credentials'], 401);

            $token = $user->createToken('token')->plainTextToken;

            return response(['success' => true, 'user' => $user, 'token' => $token], 201);
        } catch (ValidationException $e) {
            return response(['success' => false, 'message' => 'Validation failed while user tried to login', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            return response(['success' => false, 'message' => 'An error occurred during login.', 'error_message' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     tags={"Auth"},
     *     summary="Logout a user",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User successfully logged out"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="An error occurred while user tried to logout"
     *     )
     * )
     */
    public function logout(Request $request)
    {
        try {
            $this->authRepository->logoutUser($request);
            return response(['success' => true, 'message' => 'User successfully logged out'], 200);
        } catch (Exception $e) {
            return response(['success' => false, 'message' => 'An error occurred while user tried to logout', 'error_message' => $e->getMessage()], 500);
        }
    }
}
