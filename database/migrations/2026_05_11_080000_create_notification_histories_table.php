<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_histories', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['custom', 'warning'])->default('custom');
            $table->string('title');
            $table->text('body');
            $table->json('data')->nullable(); // payload tambahan (logger info, dll)
            $table->unsignedBigInteger('sent_by')->nullable(); // null = otomatis (warning)
            $table->enum('recipient_type', ['all', 'selected', 'automatic'])->default('automatic');
            $table->json('recipient_ids')->nullable(); // null = all / automatic
            $table->unsignedInteger('recipient_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_histories');
    }
};
