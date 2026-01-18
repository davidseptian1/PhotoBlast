<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Try to drop existing foreign key (if exists), then make column nullable and re-create FK
        try {
            Schema::table('codes', function (Blueprint $table) {
                $table->dropForeign(['transaction_id']);
            });
        } catch (\Exception $e) {
            // ignore if foreign doesn't exist
        }

        // Alter column to nullable. Note: this may require doctrine/dbal package installed.
        Schema::table('codes', function (Blueprint $table) {
            $table->unsignedBigInteger('transaction_id')->nullable()->change();
        });

        // Re-add foreign key with null on delete
        Schema::table('codes', function (Blueprint $table) {
            $table->foreign('transaction_id')->references('id')->on('transactions')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('codes', function (Blueprint $table) {
                $table->dropForeign(['transaction_id']);
            });
        } catch (\Exception $e) {}

        Schema::table('codes', function (Blueprint $table) {
            $table->unsignedBigInteger('transaction_id')->nullable(false)->change();
            $table->foreign('transaction_id')->references('id')->on('transactions');
        });
    }
};
