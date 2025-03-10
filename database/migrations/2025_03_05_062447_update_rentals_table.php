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
            if (!Schema::hasColumn('rentals', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            }
            if (!Schema::hasColumn('rentals', 'machinery')) {
                $table->string('machinery')->nullable();
            }
            if (!Schema::hasColumn('rentals', 'status')) {
                $table->string('status')->nullable();
            }
            if (!Schema::hasColumn('rentals', 'date_of_rent')) {
                $table->date('date_of_rent')->nullable();
            }
            if (!Schema::hasColumn('rentals', 'remarks')) {
                $table->text('remarks')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            if (Schema::hasColumn('rentals', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
            if (Schema::hasColumn('rentals', 'machinery')) {
                $table->dropColumn('machinery');
            }
            if (Schema::hasColumn('rentals', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('rentals', 'date_of_rent')) {
                $table->dropColumn('date_of_rent');
            }
            if (Schema::hasColumn('rentals', 'remarks')) {
                $table->dropColumn('remarks');
            }
        });
    }
};
