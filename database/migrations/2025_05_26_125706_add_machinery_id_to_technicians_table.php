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
        Schema::table('technicians', function (Blueprint $table) {
            $table->foreignId('machinery_id')->nullable()->constrained('machineries')->onDelete('cascade');
            //
        });
    }

    /**s
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('technicians', function (Blueprint $table) {
            $table->dropForeign(['machinery_id']);
            $table->dropColumn('machinery_id');
            //
        });
    }
};
