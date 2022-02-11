<?php

namespace App\Http\Controllers;

use App\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

use Carbon\Carbon;
use App\Http\Resources\Admin\UserResource;
use App\Http\Requests\UserRequest;

use Laravel\Passport\Token;


class RegisterController extends Controller
{
    
    public function register(Request $request)
    {

        $v = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users|max:255',
            'mobile' => 'required',
            'gender' => 'required',
            'age' => 'required|integer',
            'password' => ['required', 'min:8']
        ]);

        if ($v->fails()) {
            return response()->json(['validation_error' => $v->errors()->all()]);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'mobile' => $request->mobile,
            'age' => $request->age,
            'gender' => $request->gender,
            
        ]);
        $request['remember_token'] = Str::random(10);
        $token = $user->createToken('Laravel Password Grant Client')->accessToken;

        return response()->json([
            'message'=>"You are registered Successfully",
            'code'=>"200",
            'data'   => new UserResource(User::find($user->id)),
            'token'  => $token,
        ]);
    }

    public function login(Request $request)
    {

        $loginData = $request->validate([
            'email' => 'required|email|max:255',
            'password' => ['required', 'min:8']
        ]);
        if (!auth()->attempt($loginData)) {
            return response(['message' => 'Invalid Credentials phone Or Password']);
        }
        
            $accessToken = auth()->user()->createToken('authToken')->accessToken;
            $user = User::where('id', auth()->user()->id)->first();
            
            return response([
                'message'=>"Logged In Successfully",
                'code'=>"200",
                'data' => new UserResource(User::find($user->id)),
                'token' => $accessToken
            ]);
        
    }

    public function logout(Request $request)
    {
        $accessToken = auth('api')->user()->token();

        DB::table('oauth_refresh_tokens')
            ->where('access_token_id', $accessToken->id)
            ->update([
                'revoked' => true
            ]);
        
        $accessToken->revoke();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
    public function profile()
    {
        $user = User::where('id', auth()->id())->first();
        $token = request()->bearerToken();
            return response()->json([
                'message'=>"Data Returned Successfully",
                'code'=>"200",
                'data' => new UserResource($user),
                "token" => $token
            ]);
        
    }
}
