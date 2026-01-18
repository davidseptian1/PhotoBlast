<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flow_runs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('status')->default('started'); // started | paid | finished
            $table->integer('package_amount')->nullable();
            $table->string('email')->nullable();
            $table->foreignId('transaction_id')->nullable()->constrained('transactions');
            $table->foreignId('code_id')->nullable()->constrained('codes');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flow_runs');
    }
};
