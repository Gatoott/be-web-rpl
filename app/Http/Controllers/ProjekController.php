<?php

namespace App\Http\Controllers;

use App\Models\Projek;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProjekController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projeks = Projek::with('user')->get();
        foreach ($projeks as $projek) {
            if ($projek->foto) {
                $projek->foto = Storage::disk('s3')->url($projek->foto);
            }
        }

        return response()->json([
            'status' => 'success',
            'projeck' => $projeks
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        if(!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'anda belum login!'
            ], 403);
        }
        $role = $user->role;

        if($role !== 'admin' && $role !== 'siswa') {
            return response()->json([
                'status' => 'error',
                'message' => 'anda bukan admin atau siswa!'
            ], 403);
        }

        $request->validate([
            'user_id' => $role === 'admin' ? 'required|exists:users,id' : 'nullable',
            'foto' => 'required|image|mimes:jpg,jpeg,png,webp|max:5000',
            'nama_projek' => 'required',
            'deskripsi' => 'required',
            'link_web' => 'required'
        ]);

        $path = $request->file('foto')->store('projek', 's3');

        if(!$path) {
            return response()->json([
                'status' => 'error',
                'message' => 'gagal upload foto'
            ], 500);
        }

        $url = Storage::disk('s3')->url($path);

        $projek = Projek::create([
            'user_id' => $role === 'admin' ? $request->user_id : $user->id,
            'foto' => $path,
            'nama_projek' => $request->nama_projek,
            'deskripsi' => $request->deskripsi,
            'link_web' => $request->link_web
        ]);

        $projek->foto = $url;

        return response()->json([
            'status' => 'success',
            'projek' => $projek
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Projek $projek)
    {
        $projek->load('user');
        return response()->json([
            'status' => 'success',
            'projek' => $projek
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Projek $projek)
    {
        $user = $request->user();
        if(!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'anda belum login!'
            ], 401);
        }

        $role = $user->role;

        $projek->load('user');

        if($role != 'admin' && $projek->user_id != $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'anda bukan admin atau pemilik projek ini!'
            ], 403);
        }

        $request->validate([
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5000',
            'nama_projek' => 'required',
            'deskripsi' => 'required',
            'link_web' => 'required'
        ]);

        if ($request->hasFile('foto')) {
            if ($projek->foto && Storage::disk('s3')->exists($projek->foto)) {
                Storage::disk('s3')->delete($projek->foto);
            }

            $path = $request->file('foto')->store('projek', 's3');
            $projek->foto = $path;
        }

        $projek->update([
            'nama_projek' => $request->nama_projek,
            'deskirpsi' => $request->deskripsi,
            'link_web' => $request->link_web
        ]);

        $projek->foto = Storage::disk('s3')->url($projek->foto);

        return response()->json([
            'status' => 'success',
            'projek' => $projek
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Projek $projek)
    {
        $user = $request->user();
        $projek->load('user');

        if($projek->user_id != $user->id && $user->role != 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'anda bukan admin atau pemilik projek ini!'
            ], 403);
        }

        if($projek->foto && Storage::disk('s3')->exists($projek->foto)) {
            Storage::disk('s3')->delete($projek->foto);
        }

        $projek->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'berhasil menghapus projek'
        ]);
    }
}
