<?php

namespace App\Http\Controllers;

use App\Models\ProfileGuru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
    public function store(Request $request){
        if ($request->user()->role != 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda bukan admin',
            ], 403);
        }

        $request->validate([
            'nama_guru' => 'required|string|max:255',
            'mengajar' => 'required|string|max:255',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $path = $request->hasFile('foto')
            ? $request->file('foto')->store('profile', 'public')
            : 'foto/pp.webp';

        $profile = ProfileGuru::create([
            'foto' => $path,
            'nama_guru' => $request->nama_guru,
            'mengajar' => $request->mengajar,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil membuat profile guru',
            'data' => $profile,
        ], 201);
    }

    // UPDATE
    public function update(Request $request, $id) {
        if($request->user()->role != 'admin'){
            return response()->json([
                'status' => 'error',    
                'message' => 'Anda bukan admin'
            ], 403);
        }

        $guru = ProfileGuru::find($id);

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

        if ($request->hasFile('foto')) {
            // Hapus foto lama kalau ada
            if ($guru->foto && Storage::disk('public')->exists($guru->foto)) {
                Storage::disk('public')->delete($guru->foto);
            }

            // Upload foto baru
            $path = $request->file('foto')->store('produk', 'public');
            $guru->foto = $path;
        }

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
