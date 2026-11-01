<?php

namespace App\Observers;

use App\Models\IncidentReport;
use App\Models\ReportObserverLog;

class IncidentReportObserver
{
    public function updated(IncidentReport $incidentReport): void
    {
        if ($incidentReport->wasChanged('status')) {
            ReportObserverLog::create([
                'incident_report_id' => $incidentReport->id,
                'changed_by_user_id' => auth()->id(),
                'field_changed'      => 'status',
                'old_value'          => $incidentReport->getOriginal('status'),
                'new_value'          => $incidentReport->status,
            ]);
        }
    }
}
