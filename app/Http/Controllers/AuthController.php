<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $message = [
            'nomor_hp.required' => 'Nomor HP tidak boleh kosong',
            'password.required' => 'Password tidak boleh kosong'
        ];

        $request->merge([
            'nama_karyawan' => strtolower($request->nama_karyawan)
        ]);

        $validate = Validator::make(
            $request->all(),
            [
                'nama_karyawan' => 'required|string',
                'password' => 'required|string'
            ],
            $message
        );

        if ($validate->fails()) {
            return response()->json([
                'status' => 'Error',
                'message' => $validate->errors()
            ], 400);
        }

        if (!Auth::attempt($request->only('nama_karyawan', 'password'))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Nama atau password salah'
            ], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $user->tokens()->delete();

        $token = $user->createToken('token')->plainTextToken;

        return response()->json(
            [
                'status' => 'success',
                'message' => 'Berhasil login',
                'data' => [
                    'user_id' => $user->user_id,
                    'nama_karyawan' => $user->nama_karyawan,
                    'is_password_default' => $user->is_password_default,
                    'token' => $token
                ]
            ],
            200
        );
    }

    public function editUser(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $messages = [
            'nomor_hp.unique' => 'No HP sudah terdaftar',
        ];

        $validate = Validator::make(
            $request->all(),
            [
                'nama_karyawan' => 'sometimes|string',
                'password' => 'sometimes|string|min:6',
            ],
            $messages
        );

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validate->errors()
            ], 400);
        }

        $data = [];

        if ($request->filled('nama_karyawan')) {
            $data['nama_karyawan'] = $request->nama_karyawan;
        }


        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
            $data['is_password_default'] = false;
        }

        if (empty($data)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak ada data yang diubah'
            ], 400);
        }

        $user->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil diubah'
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Logout success'
        ], 200);
    }

    public function checkUserLogin(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => $request->user()
        ], 200);
    }

    public function loginChecker(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'logged in'
        ], 200);
    }

    public function logoutWeb()
    {
        Auth::logout();

        session()->invalidate();
        session()->regenerateToken();

        return redirect('/');
    }
}
