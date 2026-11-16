<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Laporan;
use App\Models\Checklist;
use App\Models\ChecklistJadwal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LaporanController extends Controller
{
    // Menampilkan jadwal aktif dan riwayat laporan
    public function index(Request $request)
    {
        $user = $request->user();

        // Ambil jadwal aktif milik penghuni
        $jadwal = Jadwal::where('user_id', $user->id)
            ->whereIn('status', [
                'Belum Dikerjakan',
                'Menunggu Verifikasi'
            ])
            ->orderBy('tanggal')
            ->with('areaPiket')
            ->first();

        // Ambil riwayat laporan milik penghuni
        $riwayat = Laporan::where('user_id', $user->id)
            ->with('jadwal.areaPiket')
            ->latest()
            ->get()
            ->map(function ($laporan) {
                return [
                    'id' => $laporan->id,
                    'jadwal_id' => $laporan->jadwal_id,
                    'foto' => $laporan->foto,
                    'keterangan' => $laporan->keterangan,
                    'status' => $laporan->status,
                    'area' => $laporan->jadwal?->areaPiket?->nama_area,
                    'created_at' => $laporan->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Data laporan berhasil ditampilkan!',
            'data' => [
                'jadwal' => $jadwal ? [
                    'id' => $jadwal->id,
                    'area' => $jadwal->areaPiket?->nama_area,
                    'tanggal' => $jadwal->tanggal,
                    'status' => $jadwal->status,
                ] : null,
                'riwayat' => $riwayat
            ]
        ]);
    }

    // Mengirim laporan piket
    public function store(Request $request)
    {
        // Validasi foto dan keterangan
        $validated = $request->validate([
            'foto' => 'required|image|mimes:jpg,jpeg,png|max:5120',
            'keterangan' => 'nullable|string'
        ]);

        $user = $request->user();

        // Ambil jadwal yang belum dikerjakan
        $jadwal = Jadwal::where('user_id', $user->id)
            ->where('status', 'Belum Dikerjakan')
            ->orderBy('tanggal')
            ->first();

        if (!$jadwal) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal tidak ditemukan atau tidak dapat dilaporkan.'
            ], 404);
        }

        // Ambil seluruh checklist sesuai area piket
        $checklistIds = Checklist::where(
            'area_piket_id',
            $jadwal->area_piket_id
        )->pluck('id');

        $totalChecklist = $checklistIds->count();

        // Hitung checklist yang sudah selesai
        $selesaiChecklist = ChecklistJadwal::where(
            'jadwal_id',
            $jadwal->id
        )
            ->whereIn('checklist_id', $checklistIds)
            ->where('selesai', true)
            ->count();

        // Pastikan semua checklist sudah selesai
        if (
            $totalChecklist == 0 ||
            $totalChecklist != $selesaiChecklist
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Checklist harus diselesaikan terlebih dahulu.'
            ], 422);
        }

        // Cegah laporan ganda untuk jadwal yang sama
        if (Laporan::where('jadwal_id', $jadwal->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan untuk jadwal ini sudah dikirim.'
            ], 422);
        }

        // Simpan foto ke storage public
        $namaFile = $request->file('foto')->store('laporan', 'public');

        // Simpan data laporan
        $laporan = Laporan::create([
            'jadwal_id' => $jadwal->id,
            'user_id' => $user->id,
            'foto' => $namaFile,
            'keterangan' => $validated['keterangan'] ?? null,
            'status' => 'Menunggu'
        ]);

        // Ubah status jadwal menjadi menunggu verifikasi
        $jadwal->update([
            'status' => 'Menunggu Verifikasi'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil diupload!',
            'data' => [
                'id' => $laporan->id,
                'jadwal_id' => $laporan->jadwal_id,
                'foto' => $laporan->foto,
                'keterangan' => $laporan->keterangan,
                'status' => $laporan->status,
                'jadwal_status' => $jadwal->status
            ]
        ], 201);
    }
}