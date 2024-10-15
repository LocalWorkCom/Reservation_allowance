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
        // Schema::table('reservation_allowances', function (Blueprint $table) {
        //     $table->unsignedBigInteger('grade_id')->nullable();
        //     $table->foreign('grade_id')->references('id')->on('grades')->onUpdate('cascade');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::table('reservation_allowances', function (Blueprint $table) {
        //     //
        // });
    }
};
