<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CrewPointController extends Controller
{
    public function index(Request $request)
    {
        // Mengambil penghuni yang sedang login
        $user = $request->user();

        // Mengambil riwayat poin milik penghuni tersebut
        $crewPoints = $user->crewPoints()
            ->with(['penilaian.laporan'])
            ->latest()
            ->get();

        // Menghitung total poin
        $totalPoin = $crewPoints->sum('poin');

        // Menyiapkan data riwayat poin
        $riwayat = $crewPoints->map(function ($crewPoint) {
            $penilaian = $crewPoint->penilaian;

            return [
                'id' => $crewPoint->id,
                'poin' => $crewPoint->poin,
                'evaluasi' => $penilaian?->evaluasi,
                'laporan_id' => $penilaian?->laporan_id,
                'tanggal' => $crewPoint->created_at?->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Data Crew Points berhasil ditampilkan!',
            'data' => [
                'total_poin' => $totalPoin,
                'riwayat' => $riwayat,
            ],
        ]);
    }
}
