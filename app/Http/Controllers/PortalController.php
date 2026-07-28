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

        $validated = $request->validate([
            'foto' => 'required|image|mimes:jpg,jpeg,png,webp|max:5000',
            'judul' => 'required',
            'deskripsi' => 'required'
        ]);

        $path = $request->file('foto')->storeAs(
                'profile',
                now()->format('Ymd_His') . '_' . Str::random(10) . '.' . $request->file('foto')->getClientOriginalExtension(),
                'public'
            );

        $profile = portal::create([
            'foto' => $path,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil membuat profile guru',
            'data' => $profile,
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        if ($request->user()->role != 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda bukan admin',
            ], 403);
        }
        
        $portal = portal::find($id);

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
            if ($portal->foto && Storage::disk('public')->exists($portal->foto)) {
                Storage::disk('public')->delete($portal->foto);
            }

            $path = $request->file('foto')->storeAs(
                'profile',
                now()->format('Ymd_His') . '_' . Str::random(10) . '.' . $request->file('foto')->getClientOriginalExtension(),
                'public'
            );
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
    public function destroy(Request $request, $id)
    {
        if ($request->user()->role != 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda bukan admin',
            ], 403);
        }
        
        $portal = portal::find($id);

        if(!$portal) {
            return response()->json([
                'status' => 'error',
                'message' => 'Portal tidak ditemukan'
            ], 404);
        }

        $portal->delete();

        return response()->json([
            'status' => 'succes',
            'message' => 'berhasil menghapus portal'
        ]);
    }
}
