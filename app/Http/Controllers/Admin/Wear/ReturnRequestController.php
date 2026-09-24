<?php

namespace App\Http\Controllers\Admin\Wear;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wear\WearReturnRequest;
use App\Services\Commerce\WearReturnService;
use App\Support\AdminStepUp;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class ReturnRequestController extends Controller
{
    private function authorize(Request $request): void
    {
        abort_unless($request->user()?->hasPermission('commerce.manage'), 403);
    }

    public function index(Request $request): View
    {
        $this->authorize($request);
        $requests = WearReturnRequest::query()->with(['user:id,name,email,phone', 'order:id,order_number,total,status'])
            ->when($request->filled('q'), function ($q) use ($request): void {
                $term = trim((string) $request->string('q'));
                $q->where(function ($q) use ($term): void {
                    $q->where('id', $term)
                        ->orWhereHas('order', fn ($o) => $o->where('order_number', 'like', "%{$term}%"))
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$term}%")
            ->orWhere('email_hash', User::emailHash($term))
            ->orWhere('phone_hash', User::phoneHash($term)));
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('request_type'), fn ($q) => $q->where('request_type', $request->string('request_type')))
            ->latest()->paginate(20)->withQueryString();
        return view('admin.wear.returns.index', compact('requests'));
    }

    public function show(Request $request, WearReturnRequest $returnRequest): View
    {
        $this->authorize($request);
        $returnRequest->load(['user:id,name,email,phone', 'order.items', 'order:id,order_number,total,status,payment_status']);
        return view('admin.wear.returns.show', ['returnRequest' => $returnRequest]);
    }

    public function updateStatus(Request $request, WearReturnRequest $returnRequest, WearReturnService $service, AuditLogger $auditLogger): RedirectResponse
    {
        $this->authorize($request);
        $data = $request->validate([
            'status' => ['required', Rule::in(['approved', 'rejected', 'received', 'processed', 'completed'])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $from = $returnRequest->status;

        $updated = match ($data['status']) {
            'approved' => $service->approve($returnRequest, $request->user()->id),
            'rejected' => $service->reject($returnRequest),
            'received' => $service->receive($returnRequest),
            'processed' => $service->process($returnRequest, $request->user()->id),
            'completed' => $service->complete($returnRequest),
        };

        if (array_key_exists('notes', $data) && $data['notes'] !== null) {
            $updated->update(['notes' => $data['notes']]);
        }

        $auditLogger->log($request, 'admin.wear.return.status_changed', $updated, [
            'from' => $from,
            'to' => $updated->status,
            'notes' => $data['notes'] ?? null,
        ]);

        return back()->with('success', 'Return request updated to '.$updated->status.'.');
    }

    public function markRefunded(Request $request, WearReturnRequest $returnRequest, WearReturnService $service, AuditLogger $auditLogger, AdminStepUp $stepUp): RedirectResponse
    {
        $this->authorize($request);

        // Defense in depth: the route also requires `payments.manage`, but the
        // controller enforces it so a future routing change cannot silently
        // let a commerce-only operator record refunds.
        abort_unless($request->user()?->hasPermission('payments.manage'), 403);
        $stepUp->assert($request);

        $data = $request->validate(['refund_reference' => ['required', 'string', 'max:120']]);
        $updated = $service->markRefunded($returnRequest, $data['refund_reference'], $request->user()->id);

        $auditLogger->log($request, 'admin.wear.return.refund_recorded', $updated, [
            'refund_reference' => $updated->refund_reference,
            'refund_amount' => $updated->refund_amount,
        ]);

        return back()->with('success', 'Refund recorded successfully.');
    }
}
