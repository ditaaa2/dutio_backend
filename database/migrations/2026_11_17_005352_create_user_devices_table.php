
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();

            // Menghubungkan perangkat dengan akun pengguna
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Firebase Installation ID perangkat
            $table->string('fid')->unique();

            // Waktu terakhir perangkat terdaftar ke Firebase
            $table->timestamp('last_registered_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_devices');
    }
};
