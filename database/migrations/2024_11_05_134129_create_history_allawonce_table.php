<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('history_allawonces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sector_id')->nullable()->constrained('sectors');
            $table->foreignId('department_id')->nullable()->constrained('departements');
            $table->string('amount'); // Change to appropriate type if needed
            $table->date('date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('history_allawonce');
    }
};
