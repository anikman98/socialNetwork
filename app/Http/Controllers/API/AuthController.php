<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Auth;

class AuthController extends Controller
{



    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'username' => 'required|alpha_num',
            'password' => 'required|string|min:8',
            'confirm_password' => 'required|string|same:password'
        ]);

        if($validator->fails()){
            $response = [
                'success' => false,
                'message' => $validator->errors()
            ];
            return response()->json($response, 400);
        }

        $input = $validator->validated();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);

        $data['token'] = $user->createToken('App')->plainTextToken;
        $data['name'] = $user->name;

        $response = [
            'success' => true,
            'data' => $data,
            'message' => 'User registered!'
        ];

        return response()->json($response, 200); 

    }

    public function login(Request $request){
        if(Auth::attempt(['email'=>$request->email, 'password'=>$request->password])){
            $user = Auth::user();
            $success['token'] = $user->createToken('App')->plainTextToken;
            $success['user'] = $user;

            $response = [
                'success' => true,
                'data' => $success,
                'message' => 'User login successful!'
            ];

            return response()->json($response, 200);
        }else{
            $response = [
                'success' => false,
                'message' => 'Soemthing went wrong, Please try again!'
            ];
            return response()->json($response);
        }
    }

    public function logout(Request $request){

        $request->user()->currentAccessToken()->delete();

        Auth::logout();

        $response = [
            'success' => true,
            'message' => 'User logout successful!'
        ];

        return response()->json($response, 200);
    }
}
