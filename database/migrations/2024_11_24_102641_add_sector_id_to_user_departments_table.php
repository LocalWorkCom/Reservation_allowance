<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSectorIdToUserDepartmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_departments', function (Blueprint $table) {
            $table->unsignedBigInteger('sector_id')->nullable(); // add the new sector_id column
            $table->foreign('sector_id')->references('id')->on('sectors'); // foreign key relation, if applicable
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_departments', function (Blueprint $table) {
            $table->dropForeign(['sector_id']); // drop the foreign key if it exists
            $table->dropColumn('sector_id'); // drop the column
        });
    }
}
