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
        Schema::table('loans', function (Blueprint $table) {
            if (!Schema::hasColumn('loans', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('loans', 'amount')) {
                $table->decimal('amount', 10, 2)->default(0)->after('user_id');
            }
            if (!Schema::hasColumn('loans', 'purpose')) {
                $table->string('purpose')->default('Unknown')->after('amount'); // ✅ FIXED
            }
            if (!Schema::hasColumn('loans', 'loanDate')) {
                $table->date('loanDate')->nullable()->after('purpose'); // ✅ FIXED
            }
            if (!Schema::hasColumn('loans', 'repaymentDate')) {
                $table->date('repaymentDate')->nullable()->after('loanDate'); // ✅ FIXED
            }
            if (!Schema::hasColumn('loans', 'status')) {
                $table->string('status')->default('pending')->after('repaymentDate');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            if (Schema::hasColumn('loans', 'user_id')) {
                $table->dropColumn('user_id');
            }
            if (Schema::hasColumn('loans', 'amount')) {
                $table->dropColumn('amount');
            }
            if (Schema::hasColumn('loans', 'purpose')) {
                $table->dropColumn('purpose');
            }
            if (Schema::hasColumn('loans', 'loanDate')) {
                $table->dropColumn('loanDate');
            }
            if (Schema::hasColumn('loans', 'repaymentDate')) {
                $table->dropColumn('repaymentDate');
            }
            if (Schema::hasColumn('loans', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
