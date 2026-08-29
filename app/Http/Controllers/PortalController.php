<?php

namespace App\Http\Controllers;

use App\Models\portal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PortalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $portals = portal::orderBy('created_at')->get();
        foreach ($portals as $item) {
            if($item->foto) {
                $item->foto = Storage::disk('s3')->url($item->foto);
            }
        }

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

        $path = $request->file('foto')->store('portal', 's3');

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
     * Display the specified resource.
     */
    public function show(portal $portal)
    {
        return response()->json([
            'status' => 'success',
            'portal' => $portal
        ], 200);
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
            'judul' => 'required',
            'deskripsi' => 'required',
        ]);

        if ($request->hasFile('foto')) {
            if ($portal->foto && Storage::disk('s3')->exists($portal->foto)) {
                Storage::disk('s3')->delete($portal->foto);
            }

            $path = $request->file('foto')->store('portal', 's3');
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
