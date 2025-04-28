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
        Schema::table('maintainances', function (Blueprint $table) {
            $table->string('condition')->nullable();
            $table->string('workDone')->nullable();
            $table->integer('expenses')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintainances', function (Blueprint $table) {
            $table->dropColumn('condition');
            $table->dropColumn('workDone');
            $table->dropColumn('expenses');
        });
    }
};
