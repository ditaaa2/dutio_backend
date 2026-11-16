<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel users dan password_resets sudah tersedia
        // di database lama DUTIO.
        // Tabel sessions dibuat oleh migration tambahan.
    }

    public function down(): void
    {
        // Tidak menghapus tabel lama DUTIO.
    }
};
