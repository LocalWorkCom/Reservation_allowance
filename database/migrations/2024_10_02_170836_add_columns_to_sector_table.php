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
        Schema::table('sectors', function (Blueprint $table) {
            $table->decimal('reservation_allowance_amount', 8,2)->default(0);
            $table->enum('reservation_allowance_type', [1,2,3])->default(1)->comment('1 for all, 2 for part, 3for both');
            $table->unsignedBigInteger('manager')->nullable();
            $table->foreign('manager')->references('id')->on('users')->onDelete('set null');

       });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sectors', function (Blueprint $table) {
            //
        });
    }
};
