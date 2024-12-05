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
        Schema::table('users', function (Blueprint $table) {
            // Modify columns to allow NULL
            $table->integer('grade_id')->nullable()->change();
            // Add other columns as necessary
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert columns back to NOT NULL
            $table->integer('grade_id')->nullable(false)->change();
            // Add other columns as necessary
        });
    }
};
