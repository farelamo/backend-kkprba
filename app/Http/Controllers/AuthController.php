<?php

namespace App\Http\Controllers;

use Log;
use JWTAuth;
use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests\AuthRequest;
use Illuminate\Support\Facades\Cookie;

class AuthController extends Controller
{

    public function __construct(){
        $this->middleware('auth:api')->only('logout');
    }

    public function login(AuthRequest $request)
    {
        try {

            $check = User::where('username', $request->username)->first();
            if (!$check) {
                return $this->returnCondition(false, 404, 'username not found');
            }

            if (!$token = auth()->attempt([
                'username' => $request->username,
                'password' => $request->password,
            ])) {
                return $this->returnCondition(false, 401, 'incorrect password');
            }

            Cookie::queue('token', $token, auth()->factory()->getTTL() * 60);

            return response()->json([
                'success' => true,
                'data' => [
                    'token' => $token,
                    'user' => [
                        'name' => $check->name,
                        'username' => $check->username,
                    ],
                    'expires_in' => auth()->factory()->getTTL() * 60,
                ],
            ], 200);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return $this->returnCondition(false, 500, 'Internal Server Error');
        }
    }

    public function logout(Request $request)
    {
        try {

            $token = Cookie::get('token');
            
            if ($token) {
                auth()->logout();

                JWTAuth::setToken($token);
                JWTAuth::invalidate(true);
            }

            return $this->returnCondition(true, 200, 'Successfully logged out');
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return $this->returnCondition(false, 500, 'Internal Server Error');
        }
    }
}
