<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $module = trim((string) $request->string('module'));
        $action = trim((string) $request->string('action'));
        $userId = trim((string) $request->string('user_id'));
        $dateFrom = trim((string) $request->string('date_from'));
        $dateTo = trim((string) $request->string('date_to'));

        $logs = ActivityLog::query()
            ->with('user')
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $inner) use ($search) {
                    $inner->where('description', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%")
                        ->orWhereHas('user', function (Builder $userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('username', 'like', "%{$search}%");
                        });
                });
            })
            ->when($module !== '', fn (Builder $query) => $query->where('module', $module))
            ->when($action !== '', fn (Builder $query) => $query->where('action', $action))
            ->when($userId !== '', fn (Builder $query) => $query->where('user_id', $userId))
            ->when($dateFrom !== '', fn (Builder $query) => $query->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo !== '', fn (Builder $query) => $query->whereDate('created_at', '<=', $dateTo))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $summaryQuery = ActivityLog::query()
            ->when($module !== '', fn (Builder $query) => $query->where('module', $module))
            ->when($action !== '', fn (Builder $query) => $query->where('action', $action))
            ->when($userId !== '', fn (Builder $query) => $query->where('user_id', $userId))
            ->when($dateFrom !== '', fn (Builder $query) => $query->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo !== '', fn (Builder $query) => $query->whereDate('created_at', '<=', $dateTo));

        $summary = [
            'total_logs' => (clone $summaryQuery)->count(),
            'today_logs' => ActivityLog::query()->whereDate('created_at', now()->toDateString())->count(),
            'unique_users' => (clone $summaryQuery)->whereNotNull('user_id')->distinct('user_id')->count('user_id'),
            'unique_modules' => (clone $summaryQuery)->distinct('module')->count('module'),
        ];

        $users = User::query()->orderBy('name')->get(['id', 'name', 'username']);
        $modules = ActivityLog::query()->select('module')->distinct()->orderBy('module')->pluck('module');
        $actions = ActivityLog::query()->select('action')->distinct()->orderBy('action')->pluck('action');

        return view('activity-logs.index', compact(
            'logs',
            'summary',
            'users',
            'modules',
            'actions',
            'search',
            'module',
            'action',
            'userId',
            'dateFrom',
            'dateTo'
        ));
    }
}
