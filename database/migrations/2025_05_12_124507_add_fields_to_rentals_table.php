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
        Schema::table('rentals', function (Blueprint $table) {
            $table->integer('hectare')->nullable();
            $table->integer('rentFee')->nullable();
            $table->integer('attachment')->nullable();
            $table->integer('totalHarvest')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->dropColumn('hectare');
            $table->dropColumn('rentFee');
            $table->dropColumn('attachment');
            $table->dropColumn('totalHarvest');
            //
        });
    }
};
