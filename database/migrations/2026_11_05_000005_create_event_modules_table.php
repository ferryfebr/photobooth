<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->enum('module_key', ['photobooth', 'guest_photographer', 'voice_note', 'invitation']);
            $table->boolean('is_active')->default(true);

            $table->unique(['event_id', 'module_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_modules');
    }
};
