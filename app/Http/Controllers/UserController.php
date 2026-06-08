<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function getAllUser()
    {
        $user = User::all();


        return response()->json([
            'status' => 'success',
            'data' => $user
        ], 200);
    }

    public function getUserById($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data karyawan tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $user
        ], 200);
    }

    public function createUser(Request $request)
    {
        $messages = [
            'nomor_hp.unique' => 'Nomor HP sudah pernah didaftarkan'
        ];

        $validate = Validator::make(
            $request->all(),
            [
                'nama_karyawan' => 'required|string',
                'nomor_hp' => 'required|string|unique:users',
                'password' => 'required|string'
            ],
            $messages
        );

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validate->errors()
            ], 400);
        }

        User::create([
            'nama_karyawan' => $request->nama_karyawan,
            'nomor_hp' => $request->nomor_hp,
            'password' => $request->password,
            'is_active' => 'true',
            'role_id' => '1',  //for now its manual, next use from role data
            'is_password_default' => 'true'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil mendaftar',
            // 'data' => [
            //     'token' => $user->createToken('token')->plainTextToken
            // ]
        ], 201);
    }

    public function editUser(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data karyawan tidak ditemukan'
            ], 404);
        }

        $messages = [
            'nama_karyawan.required' => 'Nama karyawan harus diisi',
            'nomor_hp.required' => 'Nomor HP harus diisi',
            'nomor_hp.unique' => 'Nomor HP sudah terdaftar',
            'password.min' => 'Password minimal 8 digit'
        ];

        $validate = Validator::make($request->all(), [
            'nama_karyawan' => 'required',
            'nomor_hp' => [
                'required',
                Rule::unique('users', 'nomor_hp')
                    ->ignore($user->user_id, 'user_id')
            ],
            'password' => 'nullable|min:8'
        ], $messages);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validate->errors()
            ], 400);
        }

        $user->update([
            'nama_karyawan' => $request->nama_karyawan,
            'nomor_hp' => $request->nomor_hp
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil ubah data karyawan',
            'data' => $user
        ], 200);
    }

    public function deleteUserById($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data karyawan tidak ditemukan'
            ], 404);
        }

        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil hapus data karyawan'
        ], 200);
    }
}
