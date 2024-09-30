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
        Schema::create('reservation_allowances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('government_id');
            $table->unsignedBigInteger('main_departement_id');
            $table->unsignedBigInteger('sub_departement_id');
            $table->unsignedBigInteger('grade_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('type', [1,2])->default(1)->comment('1 for all, 2 for part');
            $table->decimal('amount')->default(0);
            $table->date('date');
            $table->string('day')->nullable();
            $table->integer('sort')->default(0);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('government_id')->references('id')->on('governments')->onUpdate('cascade');
            $table->foreign('main_departement_id')->references('id')->on('departements')->onUpdate('cascade');
            $table->foreign('sub_departement_id')->references('id')->on('departements')->onUpdate('cascade');
            $table->foreign('grade_id')->references('id')->on('grades')->onUpdate('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onUpdate('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onUpdate('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservation_allowances');
    }
};
