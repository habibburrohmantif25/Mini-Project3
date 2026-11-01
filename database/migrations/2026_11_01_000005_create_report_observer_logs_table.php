<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_observer_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_report_id')->constrained('incident_reports')->cascadeOnDelete();
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('field_changed', 100)->default('status');
            $table->string('old_value')->nullable();
            $table->string('new_value');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_observer_logs');
    }
};
