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
            $table->string('rent')->nullable(); 
            $table->string('otherExpenses')->nullable();
            $table->string('completedDate')->nullable();
            $table->string('operator_id')->nullable();
            $table->string('condition')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->dropColumn('rent');
            $table->dropColumn('otherExpenses');
            $table->dropColumn('completedDate');
            $table->dropColumn('operator_id');
            $table->dropColumn('condition');
        });
    }
};
