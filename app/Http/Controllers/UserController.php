<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index() {
        $users = User::all();

        return response()->json([
            'status' => 'success',
            'users'=> $users
        ]);
    }

    public function store(Request $request) {
        $role = $request->user()->role;
        if(!$role && $role != 'admin') {
            return response()->json([
                'status' => 'error',
                'message'=> 'anda bukan admin!'
            ], 403);
        }

        $request->validate([
            'username' => 'required',
            'email' => 'required|email|unique:users,email',
            'password'=> 'required',
            'role' => 'required|in:admin,murid,user'
        ]);

        $user = User::create([
            'username'=> $request->username,
            'email'=> $request->email,
            'password'=> bcrypt($request->password),
            'role'=> $request->role
        ]);

        return response()->json([
            'status'=> 'success',
            'message' => 'Berhasil membuat user',
            'user'=> $user
        ], 201);
    }

    public function show(User $user) {
        $user->load('projeks');
        return response()->json([
            'status'=> 'success',
            'user'=> $user
        ],200);
    }

    public function update(Request $request, User $user) {
        $role = $request->user()->role;
        if(!$role && $role != 'admin') {
            return response()->json([
                'status' => 'error',
                'message'=> 'anda bukan admin!'
            ], 403);
        }

        $validated = $request->validate([
            'username' => 'sometimes|required|string',
            'email'    => 'sometimes|required|email',
            'role'     => 'sometimes|required|in:admin,user,murid',
            'password' => 'sometimes|nullable|string|min:6'
        ]);

        if ($request->filled('password')) {
            $validated['password'] = bcrypt($request->password);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'status'=> 'success',
            'message' => 'Berhasil update user',
            'user'=> $user
        ], 200);
    }

    public function destroy(User $user) {
        $user->delete();

        return response()->json([
            'status'=> 'success',
            'message'=> 'Berhasil mengahapus user'
        ], 200);
    }
        
    public function regis(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'username' => 'required|unique:users,username',
            'password' => 'required|confirmed'
        ]);

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        $token = $user->createToken('auth-sanctum')->plainTextToken;

        return response()->json([
            'status' => 'succes',
            'message' => 'Berhasil membuat akun',
            'data' => $user,
            'token' => $token
        ], 201);
    }

    public function login(Request $request) {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        if(!Auth::attempt($credentials)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Username atau password salah'
            ]);
        }

        $user = Auth::user();

        $token = $user->createToken('auth-sanctum')->plainTextToken;
        
        return response()->json([
            'status' => 'succes',
            'message' => 'Berhasil login',
            'data' => $user,
            'token' => $token
        ], 200);
    }
}
