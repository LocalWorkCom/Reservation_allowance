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
        Schema::table('departements', function (Blueprint $table) {
            $table->decimal('reservation_allowance_amount', 8,2)->default(0);
            $table->enum('reservation_allowance_type', [1,2,3])->default(1)->comment('1 for all, 2 for part, 3for both');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departements', function (Blueprint $table) {
            //
        });
    }
};
