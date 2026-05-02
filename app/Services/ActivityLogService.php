<?php

namespace App\Services;

use App\Models\ActivityLog;

class ActivityLogService
{
    public function log(?int $userId, string $module, string $action, ?string $description = null, ?string $ipAddress = null): void
    {
        ActivityLog::create([
            'user_id' => $userId,
            'module' => $module,
            'action' => $action,
            'description' => $description,
            'ip_address' => $ipAddress,
            'created_at' => now(),
        ]);
    }
}
