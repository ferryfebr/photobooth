<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photobooth_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('frame_id')->nullable()->constrained()->nullOnDelete();
            $table->string('device_fingerprint')->nullable();
            $table->enum('status', ['capturing', 'composed', 'downloaded'])->default('composed');
            $table->string('result_file_path')->nullable();
            $table->timestamps();

            $table->index('event_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photobooth_sessions');
    }
};
