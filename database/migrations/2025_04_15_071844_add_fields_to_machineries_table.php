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
        Schema::table('machineries', function (Blueprint $table) {
            //
            $table->string('brand')->nullable();
            $table->string('serial')->nullable();
            $table->string('capacity')->nullable();
            $table->string('accessories')->nullable();
            $table->string('supplier')->nullable();
            $table->string('branchAddress')->nullable();
            $table->string('primeMoverYearAcquired')->nullable();
            $table->string('primeMoverBrand')->nullable();
            $table->string('primeMoverSerial')->nullable();
            $table->string('primeMoverRatedPower')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('machineries', function (Blueprint $table) {
            $table->dropColumn('brand');
            $table->dropColumn('serial');
            $table->dropColumn('capacity');
            $table->dropColumn('accessories');
            $table->dropColumn('supplier');
            $table->dropColumn('branchAddress');
            $table->dropColumn('primeMoverYearAcquired');
            $table->dropColumn('primeMoverBrand');
            $table->dropColumn('primeMoverSerial');
            $table->dropColumn('primeMoverRatedPower');
            //
        });
    }
};
