<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('package_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('theme_slug')->nullable();
            $table->date('event_date');
            $table->dateTime('photobooth_active_from')->nullable();
            $table->dateTime('photobooth_active_until')->nullable();
            $table->dateTime('storage_expires_at');
            $table->enum('status', ['draft', 'active', 'completed', 'archived'])->default('draft');
            $table->timestamps();

            $table->index('client_id');
            $table->index('package_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
