<?php

namespace App\Policies;

use App\Models\IncidentReport;
use App\Models\User;

class IncidentReportPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, IncidentReport $report): bool
    {
        return $user->id === $report->user_id || $user->isCommunityStaff();
    }

    public function create(User $user): bool
    {
        return $user->isPelapor();
    }

    public function update(User $user, IncidentReport $report): bool
    {
        return $user->id === $report->user_id && $report->status === 'draft';
    }

    public function review(User $user, IncidentReport $report): bool
    {
        return $user->isCommunityStaff();
    }

    public function escalate(User $user, IncidentReport $report): bool
    {
        return $user->isCommunityStaff();
    }

    public function resolve(User $user, IncidentReport $report): bool
    {
        return $user->isKoordinator();
    }

    public function accessRecap(User $user): bool
    {
        return $user->isCommunityStaff();
    }
}
