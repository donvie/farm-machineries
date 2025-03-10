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
            if (!Schema::hasColumn('maintainances', 'machinery_id')) {
                $table->foreignId('machinery_id')->nullable()->constrained('machineries')->onDelete('cascade');
            }
            if (!Schema::hasColumn('maintainances', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            }
            if (!Schema::hasColumn('maintainances', 'status')) {
                $table->string('status')->default('pending'); // Set a default value
            }
            if (!Schema::hasColumn('maintainances', 'remarks')) {
                $table->string('remarks')->nullable();
            }
            if (!Schema::hasColumns('maintainances', ['created_at', 'updated_at'])) {
                $table->timestamps();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintainances', function (Blueprint $table) {
            if (Schema::hasColumn('maintainances', 'machinery_id')) {
                $table->dropForeign(['machinery_id']);
                $table->dropColumn('machinery_id');
            }
            if (Schema::hasColumn('maintainances', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
            if (Schema::hasColumn('maintainances', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('maintainances', 'remarks')) {
                $table->dropColumn('remarks');
            }
        });
    }
};
