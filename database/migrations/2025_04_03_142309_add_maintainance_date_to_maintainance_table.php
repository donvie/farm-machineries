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
            //
            $table->date('maintainance_date')->nullable()->default(null); // Default role is 'user'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintainances', function (Blueprint $table) {
            $table->dropColumn('maintainance_date');
        });
    }
};
