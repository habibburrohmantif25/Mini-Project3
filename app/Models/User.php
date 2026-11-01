<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isPelapor(): bool
    {
        return $this->role === 'pelapor';
    }

    public function isPemantau(): bool
    {
        return $this->role === 'pemantau';
    }

    public function isKoordinator(): bool
    {
        return $this->role === 'koordinator';
    }

    public function isCommunityStaff(): bool
    {
        return in_array($this->role, ['pemantau', 'koordinator'], true);
    }

    public function incidentReports(): HasMany
    {
        return $this->hasMany(IncidentReport::class);
    }

    public function reportObserverLogs(): HasMany
    {
        return $this->hasMany(ReportObserverLog::class, 'changed_by_user_id');
    }
}
