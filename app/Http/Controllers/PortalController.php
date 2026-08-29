<?php

namespace App\Http\Controllers;

use App\Models\portal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PortalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $portals = portal::orderBy('created_at');

        return response()->json([
            'status' => 'success',
            'data' => $portals
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if ($request->user()->role != 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda bukan admin',
            ], 403);
        }

        $request->validate([
            'foto' => 'required|image|mimes:jpg,jpeg,png,webp|max:5000',
            'judul' => 'required',
            'deskripsi' => 'required'
        ]);

        $path = Storage::disk('s3')->putFileAs('portal', $request->file('foto'), now() . '-' . Str::slug($request->file('foto')->getClientOriginalPath()));

        if(!$path) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal uplod foto'
            ], 500);
        }

        $profile = portal::create([
            'foto' => $path,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
        ]);

        $profile->foto = Storage::disk('s3')->url($path);

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil membuat profile guru',
            'data' => $profile,
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, portal $portal)
    {
        if ($request->user()->role != 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda bukan admin',
            ], 403);
        }

        if(!$portal) {
            return response()->json([
                'status' => 'error',
                'message' => 'Portal tidak ditemukan'
            ], 404);
        }

        $request->validate([
            'nama_guru' => 'required',
            'mengajar' => 'required',
        ]);

        if ($request->hasFile('foto')) {
            if ($portal->foto && Storage::disk('s3')->exists($portal->foto)) {
                Storage::disk('s3')->delete($portal->foto);
            }

            $path = Storage::disk('s3')->putFileAs('portal', $request->file('foto'), Str::slug($request->file('foto')->getClientOriginalName()));
            if(!$path) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal uplod foto'
                ], 500);
            }
            $portal->foto = $path;
        }

        $portal->update([
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
        ]);

        return response()->json([
            'status' => 'succes',
            'message' => 'berhasil mengedit portal'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, portal $portal)
    {
        if ($request->user()->role != 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda bukan admin',
            ], 403);
        }

        if(!$portal) {
            return response()->json([
                'status' => 'error',
                'message' => 'Portal tidak ditemukan'
            ], 404);
        }

        if($portal->foto && Storage::disk('s3')->exists($portal->foto)) {
            Storage::disk('s3')->delete($portal->foto);
        }

        $portal->delete();

        return response()->json([
            'status' => 'succes',
            'message' => 'berhasil menghapus portal'
        ]);
    }
}
