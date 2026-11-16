<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel jobs dan job_batches dibuat
        // oleh migration tambahan DUTIO.
        // Tabel failed_jobs lama sudah tersedia.
    }

    public function down(): void
    {
        // Tidak menghapus tabel lama DUTIO.
    }
};
