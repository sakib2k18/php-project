<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Records the admin actions that matter for the audit trail shown on the
 * dashboard: campaign CRUD, donation reviews, settings changes and so on.
 */
class ActivityLogger
{
    public function log(string $action, string $description, ?Model $subject = null): ActivityLog
    {
        return ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'ip_address' => Request::ip(),
        ]);
    }

    public function created(Model $subject, string $label): ActivityLog
    {
        return $this->log($this->action($subject, 'created'), "Created {$label}", $subject);
    }

    public function updated(Model $subject, string $label): ActivityLog
    {
        return $this->log($this->action($subject, 'updated'), "Updated {$label}", $subject);
    }

    public function deleted(Model $subject, string $label): ActivityLog
    {
        return $this->log($this->action($subject, 'deleted'), "Deleted {$label}", $subject);
    }

    protected function action(Model $subject, string $verb): string
    {
        return str(class_basename($subject))->snake()->toString().'.'.$verb;
    }
}
