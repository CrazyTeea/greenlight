<?php

namespace App\Http\Controllers;

use App\DTO\CreateRequestData;
use App\DTO\DecisionData;
use App\Enums\RequestCategory;
use App\Enums\RequestStatus;
use App\Http\Actions\CancelRequestAction;
use App\Http\Actions\CreateRequestAction;
use App\Http\Actions\DecideRequestAction;
use App\Http\Actions\SubmitRequestAction;
use App\Http\Requests\DecideRequestRequest;
use App\Http\Requests\StoreRequestRequest;
use App\Http\Resources\RequestDetailResource;
use App\Http\Resources\RequestResource;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class RequestController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $filters = $request->validate([
            'status' => ['nullable', Rule::enum(RequestStatus::class)],
            'category' => ['nullable', Rule::enum(RequestCategory::class)],
            'q' => ['nullable', 'string', 'max:80'],
        ]);

        $requests = PurchaseRequest::query()
            ->visibleTo($user)
            ->with(['requester', 'currentApprover'])
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->where('category', $category))
            ->when($filters['q'] ?? null, function ($query, string $term) {
                $like = '%'.addcslashes($term, '%_\\').'%';

                $query->where(function ($query) use ($like) {
                    $query->where('title', 'like', $like)
                        ->orWhere('code', 'like', $like)
                        ->orWhere('description', 'like', $like);
                });
            })
            ->latest('created_at')
            ->paginate(9)
            ->withQueryString();

        return Inertia::render('Requests/Index', [
            'requests' => RequestResource::collection($requests),
            'filters' => [
                'status' => $filters['status'] ?? null,
                'category' => $filters['category'] ?? null,
                'q' => $filters['q'] ?? null,
            ],
            'counts' => $this->statusCounts($user),
            'categories' => collect(RequestCategory::cases())->map(fn (RequestCategory $category) => [
                'value' => $category->value,
                'label' => $category->label(),
            ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Requests/Create', [
            'categories' => collect(RequestCategory::cases())->map(fn (RequestCategory $category) => [
                'value' => $category->value,
                'label' => $category->label(),
            ]),
            'cfoThreshold' => PurchaseRequest::CFO_THRESHOLD,
        ]);
    }

    public function store(
        StoreRequestRequest $request,
        CreateRequestAction $create,
        SubmitRequestAction $submit,
    ): RedirectResponse {
        $purchaseRequest = $create->handle(
            $request->user(),
            CreateRequestData::fromArray($request->validated()),
        );

        if ($request->boolean('send_now')) {
            $submit->handle($purchaseRequest, $request->user());

            return redirect()
                ->route('requests.show', $purchaseRequest)
                ->with('success', 'Заявка отправлена на согласование.');
        }

        return redirect()
            ->route('requests.show', $purchaseRequest)
            ->with('success', 'Черновик сохранён.');
    }

    public function show(PurchaseRequest $purchaseRequest): Response
    {
        $this->authorize('view', $purchaseRequest);

        $purchaseRequest->load([
            'requester',
            'currentApprover',
            'approvals.approver',
            'auditEvents.actor',
        ]);

        return Inertia::render('Requests/Show', [
            'request' => RequestDetailResource::make($purchaseRequest)->resolve(),
            'cfoThreshold' => PurchaseRequest::CFO_THRESHOLD,
        ]);
    }

    public function submit(PurchaseRequest $purchaseRequest, SubmitRequestAction $submit, Request $request): RedirectResponse
    {
        $this->authorize('submit', $purchaseRequest);

        $submit->handle($purchaseRequest, $request->user());

        return back()->with('success', 'Заявка отправлена на согласование.');
    }

    public function cancel(PurchaseRequest $purchaseRequest, CancelRequestAction $cancel, Request $request): RedirectResponse
    {
        $this->authorize('cancel', $purchaseRequest);

        $cancel->handle($purchaseRequest, $request->user());

        return back()->with('success', 'Заявка отменена.');
    }

    public function decide(
        DecideRequestRequest $request,
        PurchaseRequest $purchaseRequest,
        DecideRequestAction $decide,
    ): RedirectResponse {
        $this->authorize('decide', $purchaseRequest);

        $updated = $decide->handle(
            $purchaseRequest,
            $request->user(),
            DecisionData::fromArray($request->validated()),
        );

        return back()->with(
            'success',
            match (true) {
                $updated->status === RequestStatus::Approved => 'Заявка согласована.',
                $updated->status === RequestStatus::Rejected => 'Заявка отклонена.',
                default => 'Решение принято, заявка ушла следующему согласующему.',
            },
        );
    }

    /**
     * @return array<string, int>
     */
    private function statusCounts(User $user): array
    {
        $query = DB::table('purchase_requests')->selectRaw('status, count(*) as total')->groupBy('status');

        if (! $user->role->canSeeAllRequests()) {
            $query->where('requester_id', $user->id);
        }

        $counts = $query->pluck('total', 'status');

        $result = ['all' => (int) $counts->sum()];

        foreach (RequestStatus::cases() as $status) {
            $result[$status->value] = (int) ($counts[$status->value] ?? 0);
        }

        return $result;
    }
}
