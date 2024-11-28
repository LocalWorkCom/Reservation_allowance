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
        //
        Schema::create('user_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();

            $table->foreignId('grade_id')->nullable();

            $table->enum('flag', ['0', '1'])->default('1');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
