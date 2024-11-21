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
       // Schema::dropIfExists('table_name'); // Replace 'table_name' with your actual table name
       /**
        * absence
        */
       Schema::dropIfExists('absence_types'); // Replace 'table_name' with your actual table name
       Schema::dropIfExists('absences'); // Replace 'table_name' with your actual table name
       Schema::dropIfExists('absence_employees'); // Replace 'table_name' with your actual table name

        /**
         * Paper transactions
         */
        Schema::dropIfExists('paper_transactions'); // Replace 'table_name' with your actual table name
        /**
         * Violations
         */
        Schema::dropIfExists('violation_type'); // Replace 'table_name' with your actual table name
        Schema::dropIfExists('violations'); // Replace 'table_name' with your actual table name
       /**
        * Inspections
        */
        Schema::dropIfExists('notifications'); // Replace 'table_name' with your actual table name

        Schema::dropIfExists('instantmissions'); // Replace 'table_name' with your actual table name

        Schema::dropIfExists('inspector_mission'); // Replace 'table_name' with your actual table name
        Schema::dropIfExists('inspector_group_histories'); // Replace 'table_name' with your actual table name
        Schema::dropIfExists('group_teams'); // Replace 'table_name' with your actual table name
        Schema::dropIfExists('group_sector_history'); // Replace 'table_name' with your actual table name
        Schema::dropIfExists('group_points'); // Replace 'table_name' with your actual table name

        Schema::dropIfExists('points'); // Replace 'table_name' with your actual table name
        Schema::dropIfExists('point_days'); // Replace 'table_name' with your actual table name
       Schema::dropIfExists('inspectors'); // Replace 'table_name' with your actual table name
       Schema::dropIfExists('groups'); // Replace 'table_name' with your actual table name
        /**
        * Working tree & times
        */
        Schema::dropIfExists('working_tree_times'); // Replace 'table_name' with your actual table name

       Schema::dropIfExists('working_trees'); // Replace 'table_name' with your actual table name
       Schema::dropIfExists('working_times'); // Replace 'table_name' with your actual table name

       /**
        * Vacations
        */
        Schema::dropIfExists('employee_vacations'); // Replace 'table_name' with your actual table name

       Schema::dropIfExists('vacation_types'); // Replace 'table_name' with your actual table name


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
