<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('frames', function (Blueprint $table) {
            // Controls how photos are placed inside the frame slots
            // pad_ratio: extra padding inside each slot (0..0.2 typical)
            // scale: multiplier after contain-fit (0.5..1.5 typical)
            // offset_x/y: relative shift inside slot (-0.3..0.3 typical)
            $table->decimal('photo_pad_ratio', 6, 3)->default(0.070);
            $table->decimal('photo_scale', 6, 3)->default(1.000);
            $table->decimal('photo_offset_x', 6, 3)->default(0.000);
            $table->decimal('photo_offset_y', 6, 3)->default(0.000);
        });
    }

    public function down(): void
    {
        Schema::table('frames', function (Blueprint $table) {
            $table->dropColumn(['photo_pad_ratio', 'photo_scale', 'photo_offset_x', 'photo_offset_y']);
        });
    }
};
