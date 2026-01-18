<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('flow_runs', function (Blueprint $table) {
            $table->string('layout_key')->nullable();
            $table->integer('layout_count')->nullable();
            $table->string('frame_file')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('flow_runs', function (Blueprint $table) {
            $table->dropColumn(['layout_key', 'layout_count', 'frame_file']);
        });
    }
};
