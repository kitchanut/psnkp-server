<?php

namespace App\Http\Controllers;

// use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Validator;
use App\Models\User;
use App\Models\UserLine;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $email = $request->email;
        $password = $request->password;
        $type = $request->type;
        try {
            if ($type == 'web') {
                if (!$token =  auth()->attempt(['email' => $email, 'password' => $password, 'user_active' => 1, 'user_del' => 1])) {
                    return response()->json(['error' => 'Unauthorized'], 401);
                }
            } else if ($type == 'liff') {
                $userLine = UserLine::where('lineUUID', $password)->first();
                if (!$token =  auth()->tokenById($userLine->user_id)) {
                    return response()->json(['error' => 'Unauthorized'], 401);
                }
            }
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'could_not_create_token',
                'data' => null
            ], 500);
        }
        return $this->respondWithToken($token);
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {

        $credentials = $request->all();
        $credentials['password'] = bcrypt($credentials['password']);
        User::create($credentials);

        return response()->json([
            'message' => 'User successfully registered',
            // 'user' => $credentials
        ], 201);
    }


    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        return response()->json(auth()->logout());
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $token = JWTAuth::getToken();
        $newToken = JWTAuth::refresh($token);
        return $this->respondWithToken($newToken);
    }


    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json([
            'me' => auth()->user(),
        ]);
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'token_type' => 'bearer',
            'access_token' => $token,
            // 'user' => auth()->user(),
            // 'expires_in' => auth()->factory()->getTTL()
        ]);
    }
}
