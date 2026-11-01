<?php

namespace App\Policies;

use App\Models\IncidentReport;
use App\Models\User;

class IncidentReportPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     * True if user owns the report OR role is pemantau/koordinator.
     */
    public function view(User $user, IncidentReport $report): bool
    {
        return $user->id === $report->user_id || $user->isCommunityStaff();
    }

    /**
     * Determine whether the user can create models.
     * Only pelapor can submit reports.
     */
    public function create(User $user): bool
    {
        return $user->isPelapor();
    }

    /**
     * Determine whether the user can update the model (BR-01).
     * Pelapor can only update if it is their report AND status is draft.
     */
    public function update(User $user, IncidentReport $report): bool
    {
        return $user->id === $report->user_id && $report->status === 'draft';
    }

    /**
     * Determine whether the user can review the model.
     * Allowed for pemantau and koordinator.
     */
    public function review(User $user, IncidentReport $report): bool
    {
        return $user->isCommunityStaff();
    }

    /**
     * Determine whether the user can escalate the report to external parties.
     */
    public function escalate(User $user, IncidentReport $report): bool
    {
        return $user->isCommunityStaff();
    }

    /**
     * Determine whether the user can mark the report as resolved.
     * Only koordinator can resolve after external follow-up is recorded.
     */
    public function resolve(User $user, IncidentReport $report): bool
    {
        return $user->isKoordinator();
    }

    /**
     * Determine whether the user can access summary / recap data.
     */
    public function accessRecap(User $user): bool
    {
        return $user->isCommunityStaff();
    }
}
