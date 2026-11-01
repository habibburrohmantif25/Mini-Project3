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
        Schema::create('incident_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_code', 50)->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('meal_distribution_id')->constrained('meal_distributions')->cascadeOnDelete();
            $table->dateTime('incident_date');
            $table->text('symptom_description');
            $table->integer('affected_count')->default(1);
            $table->enum('status', ['draft', 'reviewed', 'escalated', 'resolved'])->default('draft');
            $table->text('community_notes')->nullable();
            $table->text('escalation_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incident_reports');
    }
};
