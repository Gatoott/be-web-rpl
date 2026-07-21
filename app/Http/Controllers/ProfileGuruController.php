<?php

namespace App\Http\Controllers;

use App\Models\ProfileGuru;
use Illuminate\Http\Request;

class ProfileGuruController extends Controller
{
    // READ
    public function index() {
        $guru = ProfileGuru::get()->all();

        return response()->json([
            'status' => 'succes',
            'data' => $guru
        ]);
    }

    // CRETE
    public function store(Request $request) {
        if($request->user()->role != 'admin'){
            return response()->json([
                'status' => 'error',
                'message' => 'Anda bukan admin'
            ], 403);
        }

        $request->validate([
            'nama_guru' => 'required',
            'mengajar' => 'required',
        ]);

        ProfileGuru::create([
            'nama_guru' => $request->nama_guru,
            'mengajar' => $request->mengajar,
        ]);

        return response()->json([
            'status' => 'succes',
            'message' => 'berhasil membuat profile guru'
        ]);
    }

    // UPDATE
    public function update(Request $request, $id) {
        if($request->user()->role != 'admin'){
            return response()->json([
                'status' => 'error',    
                'message' => 'Anda bukan admin'
            ], 403);
        }

        $guru = ProfileGuru::where('id', $id);

        if(!$guru) {
            return response()->json([
                'status' => 'error',
                'message' => 'Profile guru tidak ditemukan'
            ], 404);
        }

        $request->validate([
            'nama_guru' => 'required',
            'mengajar' => 'required',
        ]);

        $guru->update([
            'nama_guru' => $request->nama_guru,
            'mengajar' => $request->mengajar,
        ]);

        return response()->json([
            'status' => 'succes',
            'message' => 'berhasil mengedit profile guru'
        ]);
    }

    // DELETE
    public function destroy(Request $request, $id) {
        if($request->user()->role != 'admin'){
            return response()->json([
                'status' => 'error',
                'message' => 'Anda bukan admin'
            ], 403);
        }

        $guru = ProfileGuru::where('id', $id);

        if(!$guru) {
            return response()->json([
                'status' => 'error',
                'message' => 'Profile guru tidak ditemukan'
            ], 404);
        }

        $guru->delete();

        return response()->json([
            'status' => 'succes',
            'message' => 'berhasil menghapus profile guru'
        ]);
    }
}
