<?php
    namespace App\Services;

    use App\Models\AuditLog;

    class AuditLogService
    {
        public function log(
            string $action,
            $model = null,
            array $old = null,
            array $new = null
        ): void {
            AuditLog::create([
                'user_id'        => auth()->id(),
                'action'         => $action,
                'auditable_type' => $model ? get_class($model) : null,
                'auditable_id'   => $model?->id,
                'old_values'     => $old,
                'new_values'     => $new,
                'ip_address'     => request()->ip(),
                'user_agent'     => request()->userAgent(),
            ]);
        }
    }
