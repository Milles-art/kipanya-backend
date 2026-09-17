<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Administration\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);

        $search = trim((string) $request->query('q', ''));
        $action = trim((string) $request->query('action', ''));
        $actor = trim((string) $request->query('actor', ''));
        $from = trim((string) $request->query('from', ''));
        $to = trim((string) $request->query('to', ''));

        $logs = AuditLog::query()
            ->with('actor:id,name,email,phone')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($q) use ($search): void {
                    $q->where('action', 'like', "%{$search}%")
                        ->orWhere('auditable_type', 'like', "%{$search}%")
                        ->orWhere('request_id', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%")
                        ->orWhereHas('actor', function ($actorQuery) use ($search): void {
                            $actorQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            })
            ->when($action !== '', fn ($query) => $query->where('action', 'like', "%{$action}%"))
            ->when($actor !== '', function ($query) use ($actor): void {
                $query->whereHas('actor', function ($actorQuery) use ($actor): void {
                    $actorQuery->where('name', 'like', "%{$actor}%")
                        ->orWhere('email', 'like', "%{$actor}%");
                });
            })
            ->when($from !== '', fn ($query) => $query->whereDate('created_at', '>=', $from))
            ->when($to !== '', fn ($query) => $query->whereDate('created_at', '<=', $to))
            ->latest('created_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.audit.index', compact('logs', 'search', 'action', 'actor', 'from', 'to'));
    }

    public function show(Request $request, AuditLog $auditLog): View
    {
        $this->authorizeAdmin($request);

        $auditLog->load('actor:id,name,email,phone');

        return view('admin.audit.show', compact('auditLog'));
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless(
            $request->user()?->isAdmin() && $request->user()->hasPermission('admin.dashboard.view'),
            403,
        );
    }
}
