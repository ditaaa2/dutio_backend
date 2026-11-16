<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use Carbon\Carbon;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    public function index(Request $request)
    {
        // Mengatur bahasa hari menjadi Indonesia
        Carbon::setLocale('id');

        // Mengambil tanggal hari ini
        $today = Carbon::today();

        // Mengambil jadwal milik pengguna yang sedang login
        $jadwals = Jadwal::with(['user', 'areaPiket'])
            ->where('user_id', $request->user()->id)
            ->orderBy('tanggal')
            ->get()
            ->map(function ($jadwal) use ($today) {

                // Mengambil tanggal jadwal
                $tanggal = Carbon::parse($jadwal->tanggal);

                return [
                    'kamar' => $jadwal->user?->kamar,
                    'area' => $jadwal->areaPiket?->nama_area,
                    'tanggal' => $tanggal->toDateString(),
                    'hari' => $tanggal->translatedFormat('l'),
                    'status' => $jadwal->status,
                    'keterangan_tanggal' => $tanggal->isSameDay($today)
                        ? 'Hari Ini'
                        : null,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Data jadwal berhasil ditampilkan!',
            'data' => $jadwals,
        ]);
    }
}
