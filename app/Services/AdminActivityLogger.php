<?php

namespace App\Services;

use App\Models\AdminActivityLog;
use Illuminate\Support\Facades\Auth;

class AdminActivityLogger
{
    /**
     * Catat aktivitas admin ke tabel admin_activity_logs.
     */
    public static function log(
        string $action,
        string $description,
        ?string $targetType = null,
        ?int $targetId = null,
        ?object $admin = null
    ): ?AdminActivityLog {
        try {
            $currentAdmin = $admin ?? Auth::guard('admin')->user();

            $adminId   = $currentAdmin?->id;
            $adminName = $currentAdmin?->name ?? 'System';
            $adminRole = $currentAdmin?->role ?? 'unknown';

            $ipAddress = request()?->ip();
            $userAgent = request()?->userAgent();

            return AdminActivityLog::create([
                'admin_id'    => $adminId,
                'admin_name'  => $adminName,
                'admin_role'  => $adminRole,
                'action'      => $action,
                'description' => $description,
                'target_type' => $targetType,
                'target_id'   => $targetId,
                'ip_address'  => $ipAddress,
                'user_agent'  => $userAgent,
            ]);
        } catch (\Throwable $e) {
            \Log::warning('Gagal mencatat log aktivitas admin: ' . $e->getMessage());
            return null;
        }
    }
}