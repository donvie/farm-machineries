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
        Schema::create('machineries', function (Blueprint $table) {
            $table->id();
            $table->string('machine_name');
            $table->string('type');
            $table->date('year_acquired')->nullable();
            $table->enum('status', ['Available', 'In Use', 'Under Maintenance'])->default('Available');
            $table->date('last_maintenance_date')->nullable();
            $table->date('next_scheduled_maintenance')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machineries');
    }
};
