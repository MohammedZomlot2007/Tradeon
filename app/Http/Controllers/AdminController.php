<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Report;
use App\Models\User;
use App\Models\Verification;
use App\Notifications\AccountStatusUpdated;
use App\Notifications\VerificationStatusUpdated;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    public function index(Request $request): Response
    {
        $userSearch = $request->query('user_search');
        $userStatus = $request->query('user_status');
        $userRole = $request->query('user_role');

        $users = User::query()
            ->when($userSearch, fn ($q) => $q->where(fn ($x) => $x->where('name', 'like', "%{$userSearch}%")->orWhere('email', 'like', "%{$userSearch}%")))
            ->when($userStatus, fn ($q) => $q->where('status', $userStatus))
            ->when($userRole, fn ($q) => $q->where('role', $userRole))
            ->latest()
            ->paginate(20, ['id', 'name', 'email', 'role', 'status', 'suspended_at', 'created_at'], 'users_page')
            ->withQueryString();
        $verifications = Verification::with(['company.user'])->latest()->paginate(20, ['*'], 'verification_page');
        $reports = Report::with('reporter')->latest()->paginate(20, ['*'], 'reports_page');

        return Inertia::render('Admin/Index', [
            'stats' => [
                'users' => User::count(),
                'active_users' => User::where('status', 'active')->count(),
                'suspended_users' => User::where('status', 'suspended')->count(),
                'companies' => Company::count(),
                'pending_verifications' => Verification::where('status', 'pending')->count(),
                'pending_reports' => Report::where('status', 'pending')->count(),
            ],
            'users' => $users,
            'verifications' => $verifications,
            'reports' => $reports,
            'auditLogs' => AuditLog::with('user:id,name')->latest()->limit(100)->get(),
            'filters' => [
                'user_search' => $userSearch,
                'user_status' => $userStatus,
                'user_role' => $userRole,
            ],
        ]);
    }

    public function approveVerification(Request $request, $id)
    {
        $validated = $request->validate(['admin_notes' => ['nullable', 'string', 'max:2000']]);
        $v = Verification::with('company.user')->findOrFail($id);
        $v->update(['status' => 'approved', 'admin_notes' => $validated['admin_notes'] ?? null]);
        $v->company->update(['verified_tier' => $v->verification_type === 'factory' ? 'factory_verified' : 'company_verified']);
        AuditLog::create(['user_id' => auth()->id(), 'action' => 'verification.approved', 'auditable_type' => Verification::class, 'auditable_id' => $v->id, 'metadata' => ['notes' => (bool) ($validated['admin_notes'] ?? null)], 'ip_address' => request()->ip(), 'user_agent' => request()->userAgent()]);
        $v->company->user?->notify(new VerificationStatusUpdated($v->fresh()));

        return redirect()->back()->with('success', __('flash.verification_approved'));
    }

    public function rejectVerification(Request $request, $id)
    {
        $validated = $request->validate(['admin_notes' => ['nullable', 'string', 'max:2000']]);
        $v = Verification::with('company.user')->findOrFail($id);
        $v->update(['status' => 'rejected', 'admin_notes' => $validated['admin_notes'] ?? null]);
        AuditLog::create(['user_id' => auth()->id(), 'action' => 'verification.rejected', 'auditable_type' => Verification::class, 'auditable_id' => $v->id, 'metadata' => ['notes' => (bool) ($validated['admin_notes'] ?? null)], 'ip_address' => request()->ip(), 'user_agent' => request()->userAgent()]);
        $v->company->user?->notify(new VerificationStatusUpdated($v->fresh()));

        return redirect()->back()->with('success', __('flash.verification_rejected'));
    }

    public function toggleUserSuspension(Request $request, User $user)
    {
        abort_if($user->id === auth()->id(), 403, 'You cannot suspend your own account.');
        abort_if($user->isAdmin() && User::where('role', 'admin')->where('status', 'active')->count() <= 1 && ! $user->isSuspended(), 403, 'This is the last active administrator.');
        $suspend = ! $user->isSuspended();
        $user->update([
            'status' => $suspend ? 'suspended' : 'active',
            'suspended_at' => $suspend ? now() : null,
        ]);
        AuditLog::create(['user_id' => auth()->id(), 'action' => $suspend ? 'user.suspended' : 'user.unsuspended', 'auditable_type' => User::class, 'auditable_id' => $user->id, 'ip_address' => $request->ip(), 'user_agent' => $request->userAgent()]);
        $user->notify(new AccountStatusUpdated($user->status));

        return back()->with('success', $suspend ? __('flash.user_suspended') : __('flash.user_restored'));
    }

    public function updateReport(Request $request, Report $report)
    {
        $validated = $request->validate(['status' => ['required', 'in:pending,reviewed,resolved,dismissed']]);
        $report->update(['status' => $validated['status']]);
        AuditLog::create(['user_id' => auth()->id(), 'action' => 'report.status_updated', 'auditable_type' => Report::class, 'auditable_id' => $report->id, 'metadata' => $validated, 'ip_address' => $request->ip(), 'user_agent' => $request->userAgent()]);

        return back()->with('success', __('flash.report_updated'));
    }
}
