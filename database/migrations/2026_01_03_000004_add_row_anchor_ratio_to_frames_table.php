<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('frames', function (Blueprint $table) {
            // 0.0 = centered (old behavior)
            // 1.0 = pull rows toward the center (reduces middle band for layout2)
            $table->double('row_anchor_ratio')->default(1.000)->after('grid_gap_ratio');
        });
    }

    public function down(): void
    {
        Schema::table('frames', function (Blueprint $table) {
            $table->dropColumn('row_anchor_ratio');
        });
    }
};
