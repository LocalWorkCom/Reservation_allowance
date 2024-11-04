<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sectors', function (Blueprint $table) {
            DB::statement("ALTER TABLE sectors MODIFY COLUMN reservation_allowance_type ENUM('1', '2', '3', '4') DEFAULT '1' COMMENT '1 for all, 2 for part, 3 for both, 4 for none'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sectors', function (Blueprint $table) {
            DB::statement("ALTER TABLE sectors MODIFY COLUMN reservation_allowance_type ENUM('1', '2', '3') DEFAULT '1' COMMENT '1 for all, 2 for part, 3 for both'");
        });
    }
};
