<?php

namespace App\Http\Controllers;

use App\Models\ProfileRpl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileRplController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $profileRpl = ProfileRpl::first();

        if($profileRpl != null) {
            $profileRpl->logo = Storage::disk('s3')->url($profileRpl->logo);
        } 

        return response()->json([
            'status' => 'success',
            'data' => $profileRpl
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
            'deskripsi' => 'required',
            'visi' => 'required',
            'misi' => 'required',
            'logo' => 'required|image|mimes:jpg,jpeg,png,webp|max:5000',
        ]);

        $path = $request->file('logo')->store('RPL', 's3');

        if(!$path) {
            return response()->json([
                'status' => 'error',
                'message' => 'gagal upload foto!'
            ], 500);
        }

        $profileRpl = ProfileRpl::create([
            'logo' => $path,
            'deskripsi' => $request->deskripsi,
            'visi' => $request->visi,
            'misi' => $request->misi,
        ]);

        $profileRpl->logo = Storage::disk('s3')->url($path);

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil membuat profile RPL',
            'data' => $profileRpl,
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProfileRpl $profileRpl)
    {
        if ($request->user()->role != 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda bukan admin',
            ], 403);
        }

        if(!$profileRpl) {
            return response()->json([
                'status' => 'error',
                'message' => 'profile RPL tidak ditemukan'
            ], 404);
        }

        $request->validate([
            'deskripsi' => 'required',
            'visi' => 'required',
            'misi' => 'required',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5000',
        ]);

        if ($request->hasFile('logo')) {
            if ($profileRpl->logo && Storage::disk('s3')->exists($profileRpl->logo)) {
                Storage::disk('s3')->delete($profileRpl->logo);
            }

            $path = $request->file('logo')->store('RPL', 's3');
            if(!$path) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal uplod foto'
                ], 500);
            }
            $profileRpl->logo = $path;
        }

        $profileRpl->update([
            'deskripsi' => $request->deskripsi,
            'visi' => $request->visi,
            'misi' => $request->misi,
        ]);

        return response()->json([
            'status' => 'succes',
            'message' => 'berhasil mengedit RPL'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request ,ProfileRpl $profileRpl)
    {
        if($request->user()->role != 'admin'){
            return response()->json([
                'status' => 'error',
                'message' => 'Anda bukan admin'
            ], 403);
        }

        if ($profileRpl->logo && Storage::disk('s3')->exists($profileRpl->logo)) {
            Storage::disk('s3')->delete($profileRpl->logo);
        }
        
        $profileRpl->delete();

        return response()->json([
            'status' => 'succes',
            'message' => 'berhasil menghapus profile RPL'
        ]);
    }
}
