<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class IncidentReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_code',
        'user_id',
        'school_id',
        'meal_distribution_id',
        'incident_date',
        'symptom_description',
        'affected_count',
        'status',
        'community_notes',
        'escalation_notes',
    ];

    protected function casts(): array
    {
        return [
            'incident_date' => 'datetime',
            'affected_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function mealDistribution(): BelongsTo
    {
        return $this->belongsTo(MealDistribution::class);
    }

    public function evidenceFile(): HasOne
    {
        return $this->hasOne(EvidenceFile::class);
    }

    public function observerLogs(): HasMany
    {
        return $this->hasMany(ReportObserverLog::class);
    }
}
