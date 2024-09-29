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
        Schema::table('instantmissions', function (Blueprint $table) {
            $table->foreignId('inspector_id')->nullable()->references('id')->on('inspectors')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('instantmissions', function (Blueprint $table) {
            //
            $table->dropColumn('inspector_id');
        });
    }
};
