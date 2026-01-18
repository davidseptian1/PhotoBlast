<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('frames', function (Blueprint $table) {
            $table->id();
            $table->string('layout_key');
            // Public-relative file path used by the app/session, e.g. "storage/frames/layout3/xxxx.png"
            $table->string('file');
            $table->string('original_name')->nullable();
            $table->timestamps();

            $table->index('layout_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('frames');
    }
};
