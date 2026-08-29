<?php

namespace App\Http\Controllers;

use App\Models\ProfileGuru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

        $path = $request->hasFile('foto') ? 
            Storage::disk('s3')->putFileAs('guru', $request->file('foto'), Str::slug($request->file('foto')->getClientOriginalName())) 
            : 'guru/pp.web';

        if(!$path) {
            return response()->json([
                'status' => 'error',
                'message' => 'gagal upload foto!'
            ], 500);
        }

        $profile = ProfileGuru::create([
            'foto' => $path,
            'nama_guru' => $request->nama_guru,
            'mengajar' => $request->mengajar,
        ]);

        $profile->foto = Storage::disk('s3')->url($path);

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil membuat profile guru',
            'data' => $profile,
        ], 201);
    }

    // UPDATE
    public function update(Request $request, ProfileGuru $guru) {
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

        if ($request->hasFile('foto')) {
            if ($guru->foto && Storage::disk('s3')->exists($guru->foto)) {
                Storage::disk('s3')->delete($guru->foto);
            }

            $path = Storage::disk('s3')->putFileAs('guru', $request->file('foto'), Str::slug($request->file('foto')->getClientOriginalName()));
            if(!$path) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal upload foto!'
                ], 500);
            }

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
    public function destroy(Request $request, ProfileGuru $guru){
        if($request->user()->role != 'admin'){
            return response()->json([
                'status' => 'error',
                'message' => 'Anda bukan admin'
            ], 403);
        }

        if ($guru->foto && Storage::disk('s3')->exists($guru->foto)) {
            Storage::disk('s3')->delete($guru->foto);
        }
        
        $guru->delete();

        return response()->json([
            'status' => 'succes',
            'message' => 'berhasil menghapus profile guru'
        ]);
    }
}
