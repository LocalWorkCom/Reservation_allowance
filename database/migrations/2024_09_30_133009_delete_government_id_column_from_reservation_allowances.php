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
            $table->dropForeign('reservation_allowances_government_id_foreign');
            $table->dropForeign('reservation_allowances_grade_id_foreign');
            $table->dropColumn('government_id');
            $table->dropColumn('grade_id');
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
