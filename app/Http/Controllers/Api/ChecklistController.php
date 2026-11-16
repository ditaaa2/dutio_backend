<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Jadwal;
use App\Models\Checklist;
use App\Models\ChecklistJadwal;

class ChecklistController extends Controller
{
    // Menampilkan checklist sesuai jadwal aktif penghuni
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

        // Jika penghuni tidak memiliki jadwal aktif
        if (!$jadwal) {
            return response()->json([
                'success' => true,
                'message' => 'Tidak ada jadwal piket aktif.',
                'data' => [
                    'area' => '-',
                    'status' => 'Tidak Ada Jadwal',
                    'checklists' => [],
                    'total' => 0,
                    'selesai' => 0,
                    'persen' => 0,
                    'allDone' => false
                ]
            ]);
        }

        // Ambil daftar checklist sesuai area piket
        $checklists = Checklist::where('area_piket_id', $jadwal->area_piket_id)
            ->get();

        // Ambil progres yang sudah tersimpan
        $progress = ChecklistJadwal::where('jadwal_id', $jadwal->id)
            ->get()
            ->keyBy('checklist_id');

        // Gabungkan daftar tugas dengan progresnya
        $dataChecklist = $checklists->map(function ($checklist) use ($progress) {
            $dataProgress = $progress->get($checklist->id);

            return [
                'id' => $checklist->id,
                'nama' => $checklist->aktivitas,
                'selesai' => $dataProgress
                    ? (bool) $dataProgress->selesai
                    : false
            ];
        });

        // Hitung progres checklist
        $total = $dataChecklist->count();
        $selesai = $dataChecklist->where('selesai', true)->count();
        $persen = $total > 0
            ? round(($selesai / $total) * 100)
            : 0;

        return response()->json([
            'success' => true,
            'message' => 'Data checklist berhasil ditampilkan!',
            'data' => [
                'area' => $jadwal->areaPiket->nama_area,
                'status' => $jadwal->status,
                'checklists' => $dataChecklist,
                'total' => $total,
                'selesai' => $selesai,
                'persen' => $persen,
                'allDone' => $total > 0 && $selesai == $total
            ]
        ]);
    }

    // Mengubah status checklist
    public function toggle(Request $request, $id)
    {
        // Validasi status checklist
        $validated = $request->validate([
            'selesai' => ['required', 'boolean']
        ]);

        $user = $request->user();

        // Ambil jadwal yang masih bisa dikerjakan
        $jadwal = Jadwal::where('user_id', $user->id)
            ->where('status', 'Belum Dikerjakan')
            ->orderBy('tanggal')
            ->first();

        if (!$jadwal) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal tidak ditemukan atau tidak dapat dikerjakan.'
            ], 404);
        }

        // Pastikan checklist termasuk area piket jadwal tersebut
        $checklist = Checklist::where('id', $id)
            ->where('area_piket_id', $jadwal->area_piket_id)
            ->first();

        if (!$checklist) {
            return response()->json([
                'success' => false,
                'message' => 'Checklist tidak ditemukan.'
            ], 404);
        }

        // Cari progres yang sudah ada atau buat jika belum tersedia
        $progress = ChecklistJadwal::firstOrNew([
            'jadwal_id' => $jadwal->id,
            'checklist_id' => $checklist->id
        ]);

        $progress->selesai = $validated['selesai'];
        $progress->save();

        // Hitung ulang progres seluruh checklist pada area tersebut
        $checklistIds = Checklist::where('area_piket_id', $jadwal->area_piket_id)
            ->pluck('id');

        $total = $checklistIds->count();

        $selesai = ChecklistJadwal::where('jadwal_id', $jadwal->id)
            ->whereIn('checklist_id', $checklistIds)
            ->where('selesai', true)
            ->count();

        $persen = $total > 0
            ? round(($selesai / $total) * 100)
            : 0;

        return response()->json([
            'success' => true,
            'message' => 'Checklist berhasil diperbarui!',
            'data' => [
                'id' => $checklist->id,
                'nama' => $checklist->aktivitas,
                'selesai' => (bool) $progress->selesai,
                'total' => $total,
                'selesai_total' => $selesai,
                'persen' => $persen,
                'allDone' => $total > 0 && $selesai == $total
            ]
        ]);
    }
}