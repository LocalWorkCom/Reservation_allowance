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
        Schema::table('reservation_allowances', function (Blueprint $table) {
            $table->integer('mandate')->default(0)->comment('0 no mandate, 1 have mandate');
            $table->unsignedBigInteger('sector_mandate')->nullable();
            $table->unsignedBigInteger('department_mandate')->nullable();

            $table->foreign('sector_mandate')->references('id')->on('sectors')->onUpdate('cascade');
            $table->foreign('department_mandate')->references('id')->on('departements')->onUpdate('cascade');        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservation_allowances', function (Blueprint $table) {
            //
        });
    }
};
