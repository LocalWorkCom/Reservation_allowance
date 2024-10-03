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
            $table->enum('type_departement', [1,2])->default(1)->comment('1 for department, 2 for sector');
        });
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
