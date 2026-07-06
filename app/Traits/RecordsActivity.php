<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait RecordsActivity
{
    public static function bootRecordsActivity(): void
    {
        static::created(function ($model) {
            $model->recordActivity('created');
        });

        static::updated(function ($model) {
            $model->recordActivity('updated');
        });

        static::deleted(function ($model) {
            $model->recordActivity('deleted');
        });
    }

    protected function recordActivity(string $event): void
    {
        $changes = null;
        $oldValues = null;
        $newValues = null;

        if ($event === 'created') {
            $newValues = $this->sanitizeAttributes($this->getAttributes());
            $changes = $newValues;
        }

        if ($event === 'updated') {
            $changes = $this->sanitizeAttributes($this->getChanges());
            $oldValues = $this->sanitizeAttributes($this->getOriginal());
            $newValues = $this->sanitizeAttributes($this->getAttributes());
        }

        if ($event === 'deleted') {
            $oldValues = $this->sanitizeAttributes($this->getOriginal());
        }

        $request = null;
        try {
            $request = Request::instance();
        } catch (\Throwable $e) {
            // Ignore non-HTTP contexts.
        }

        // Prepare human-readable subject name when available (e.g. item name)
        $subjectName = $this->getAttribute('name') ?? $this->getAttribute('sku') ?? null;

        $subjectClass = class_basename(static::class);

        // Build a readable description / keterangan
        if ($event === 'created') {
            $description = "Membuat {$subjectClass}" . ($subjectName ? " '{$subjectName}'" : " #{$this->getKey()}");
        } elseif ($event === 'updated') {
            $changedFields = $changes ? implode(', ', array_keys((array) $changes)) : '';
            $description = "Mengubah {$subjectClass}" . ($subjectName ? " '{$subjectName}'" : " #{$this->getKey()}") . ($changedFields ? " (fields: {$changedFields})" : '');
        } else {
            $description = "Menghapus {$subjectClass}" . ($subjectName ? " '{$subjectName}'" : " #{$this->getKey()}");
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'subject_type' => static::class,
            'subject_id' => $this->getKey(),
            'subject_name' => $subjectName,
            'action' => $event,
            'changes' => $changes,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            // As requested: store what the user did in `url` (human readable)
            'url' => $description,
            'description' => $description,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->header('User-Agent'),
        ]);
    }

    protected function sanitizeAttributes(array $attributes): array
    {
        return collect($attributes)
            ->except($this->getHidden())
            ->all();
    }
}
