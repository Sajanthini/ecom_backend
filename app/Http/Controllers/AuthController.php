<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'email' => 'required|string|unique:users',
                'password' => 'required|min:8',
            ]); 

            if ($validator->fails()) {
                return response()->json(['validation_errors' => $validator->errors()], 400);
            }

            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
            ]);

            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'status' => 200, 
                'name'=>$user->name,
                'token' => $token,
                'message'=> 'User Registered Sucessfully'], 200);
        } catch (Exception $e) {
            
            return response()->json(['error' => 'User registration failed.'], 500);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['validation_errors' => $validator->errors()], 400);
        }

        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            $token = $user->createToken('api-token')->plainTextToken;
            return response()->json([
                'status'=>200,
                'name' => $user->name, 
                'token' => $token,
                'message'=>'User Login Successfully'], 200);
        }

        return response()->json([
            'status' => 401, 
            'message' => 'Invalid Credentials']);
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();
        return response()->json([
            'status'=>200,
            'message'=>'Logged Out Successfully'
        ]);
    }

}
