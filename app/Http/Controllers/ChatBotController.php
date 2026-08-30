<?php

namespace App\Http\Controllers;

use App\Models\portal;
use App\Models\ProfileGuru;
use App\Models\Projek;
use Illuminate\Http\Request;

use function Laravel\Ai\agent;

class ChatBotController extends Controller
{
    public function chat(Request $request) {
        $request->validate([
            'chat' => 'required|string',
        ]);

        $projects = Projek::with('user')->get();

        $gurus = ProfileGuru::all();

        $portal = portal::all();

        $dataProject = $projects->map(function ($project) {
            $pembuat = $project->user ? $project->user->name : '-';
            return "
                Nama Project: {$project->nama_projek}
                Deskripsi: {$project->deskripsi}
                Dibuat oleh: {$pembuat}
            ";
        })->implode("\n");

        $dataGuru = $gurus->map(function ($guru) {
            return "
                Nama Guru: {$guru->nama_guru}
                Mengajar: {$guru->mengajar}
            ";
        })->implode("\n");

        $dataPortal = $portal->map(function ($p) {
            return "
                Judul Berita: {$p->judul}
                Kategori: {$p->kategori}
                Deskripsi: {$p->deskripsi}
            ";
        })->implode("\n");

        $datadiri = "
            Kamu adalah AI Assistant untuk website jurusan
            Rekayasa Perangkat Lunak (RPL).

            Tugas kamu adalah membantu pengguna mendapatkan
            informasi mengenai jurusan RPL, berita, project siswa,
            guru, dan mata pelajaran.

            - DATA PROJECT RPL :
            {$dataProject}

            - DATA GURU RPL :

            {$dataGuru}

            - DATA BERITA :
            {$dataPortal}

            - ATURAN :

            1. Jawab menggunakan bahasa Indonesia.
            2. Jawab dengan ramah dan mudah dipahami.
            3. Gunakan hanya informasi yang tersedia pada data di atas.
            4. Jangan mengarang nama siswa, guru, project,
            atau mata pelajaran.
            5. Jika informasi tidak tersedia, katakan bahwa
            informasi tersebut belum tersedia.
            6. Jika ditanya siapa pembuat suatu project,
            gunakan data user/pembuat yang tersedia.
            7. Jika ditanya guru tertentu, jelaskan mata
            pelajaran yang dia ajarkan berdasarkan data.
            8. Kamu adalah assistant website RPL, bukan
            assistant umum.

            Contoh:
            User: Siapa yang membuat project X?
            Assistant: Project X dibuat oleh [nama pembuat].

            User: Pak Budi mengajar apa?
            Assistant: Pak Budi mengajar [mata pelajaran].
        ";

        if (empty(config('ai.providers.gemini.key')) && empty(env('GEMINI_API_KEY'))) {
            return response()->json([
                'status' => 'warning',
                'message' => 'GEMINI_API_KEY belum diisi di file .env. Silakan tambahkan GEMINI_API_KEY di .env untuk mengaktifkan AI.',
            ], 200);
        }

        $modelsToTry = array_values(array_unique(array_filter([
            config('ai.providers.gemini.models.text.default', env('GEMINI_MODEL', 'gemini-3.5-flash')),
            'gemini-3.5-flash-lite',
            'gemini-3.5-flash',
        ])));

        $lastException = null;

        foreach ($modelsToTry as $model) {
            try {
                $response = agent(
                    instructions: $datadiri
                )->prompt(
                    prompt: $request->chat,
                    model: $model
                );

                return response()->json([
                    'status' => 'success',
                    'message' => $response->text,
                ]);
            } catch (\Throwable $e) {
                $lastException = $e;
                continue;
            }
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Gagal menghubungi AI: ' . ($lastException ? $lastException->getMessage() : 'Server AI sedang sibuk. Silakan coba lagi nanti.'),
        ], 500);
    }
}
