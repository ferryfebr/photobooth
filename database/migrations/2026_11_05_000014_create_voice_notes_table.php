<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voice_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('sender_name')->nullable();
            $table->string('file_path');
            $table->string('original_file_path')->nullable();
            $table->string('mime_type')->nullable();
            $table->integer('duration_seconds')->default(0);
            $table->string('device_fingerprint')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();

            $table->index('event_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voice_notes');
    }
};
