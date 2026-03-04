<?php

namespace App\Http\Controllers;

use App\Enums\RequestStatus;
use App\Enums\UserRole;
use App\Http\Requests\StoreRequestRequest;
use App\Models\Request as WorkflowRequest;
use App\Models\Service;
use App\Services\RequestWorkflowService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Validation\Rule;

class RequestController extends Controller
{
    public function __construct(private readonly RequestWorkflowService $requestWorkflowService) {}

    public function index(HttpRequest $request): View
    {
        $this->authorize('viewAny', WorkflowRequest::class);

        $user = $request->user();
        $statusCases = RequestStatus::cases();
        $allowedStatuses = array_map(static fn (RequestStatus $status) => $status->value, $statusCases);
        $statusLabels = collect($statusCases)
            ->mapWithKeys(fn (RequestStatus $status) => [$status->value => $status->label()])
            ->all();

        $validated = $request->validate([
            'tab' => ['nullable', 'string'],
            'estado' => ['nullable', Rule::in($allowedStatuses)],
            'servicio' => ['nullable', 'integer', 'exists:services,id'],
            'created_from' => ['nullable', 'date'],
            'created_to' => ['nullable', 'date', 'after_or_equal:created_from'],
            'q' => ['nullable', 'string', 'max:120'],
        ]);

        $tabs = $this->tabConfigForRole($user->role);
        $activeTab = $validated['tab'] ?? array_key_first($tabs);

        if (! isset($tabs[$activeTab])) {
            $activeTab = array_key_first($tabs);
        }

        $canFilterService = in_array($user->role, [UserRole::ADMIN, UserRole::GESTION_PERSONAS, UserRole::RRHH], true);

        $baseQuery = WorkflowRequest::query()
            ->with('service')
            ->visibleTo($user);

        $this->applyCommonFilters(
            $baseQuery,
            $validated,
            $canFilterService
        );

        $requests = (clone $baseQuery)
            ->whereIn('status', $tabs[$activeTab]['statuses']);

        $this->applyStatusFilter($requests, $validated);

        $requests = $requests
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $countBaseQuery = WorkflowRequest::query()->visibleTo($user);

        $this->applyCommonFilters(
            $countBaseQuery,
            $validated,
            $canFilterService
        );

        $this->applyStatusFilter($countBaseQuery, $validated);

        $statusCounts = (clone $countBaseQuery)
            ->selectRaw('status, COUNT(*) as aggregate_count')
            ->groupBy('status')
            ->pluck('aggregate_count', 'status');

        $tabCounts = [];

        foreach ($tabs as $tabKey => $tab) {
            $tabCounts[$tabKey] = collect($tab['statuses'])
                ->sum(fn (string $status): int => (int) ($statusCounts[$status] ?? 0));
        }

        $services = $canFilterService
            ? Service::query()->orderBy('name')->get()
            : collect();

        return view('requests.index', [
            'requests' => $requests,
            'tabs' => $tabs,
            'activeTab' => $activeTab,
            'tabCounts' => $tabCounts,
            'filters' => [
                'estado' => $validated['estado'] ?? null,
                'servicio' => $validated['servicio'] ?? null,
                'created_from' => $validated['created_from'] ?? null,
                'created_to' => $validated['created_to'] ?? null,
                'q' => $validated['q'] ?? null,
            ],
            'services' => $services,
            'canFilterService' => $canFilterService,
            'availableStatuses' => $allowedStatuses,
            'statusLabels' => $statusLabels,
        ]);
    }

    private function tabConfigForRole(UserRole $role): array
    {
        return match ($role) {
            UserRole::JEFE_SERVICIO => [
                'pendientes' => [
                    'label' => 'Pendientes',
                    'statuses' => [
                        RequestStatus::BORRADOR->value,
                        RequestStatus::OBSERVADA->value,
                    ],
                ],
                'en_revision' => [
                    'label' => 'En revisión',
                    'statuses' => [
                        RequestStatus::ENVIADA->value,
                        RequestStatus::EN_GESTION_PERSONAS->value,
                        RequestStatus::EN_RRHH->value,
                    ],
                ],
                'cerradas' => [
                    'label' => 'Cerradas',
                    'statuses' => [
                        RequestStatus::RECHAZADA->value,
                        RequestStatus::EN_TRAMITACION_CONTRATO->value,
                        RequestStatus::FINALIZADA->value,
                    ],
                ],
            ],
            UserRole::GESTION_PERSONAS => [
                'en_gestion' => [
                    'label' => 'En gestión',
                    'statuses' => [
                        RequestStatus::ENVIADA->value,
                        RequestStatus::EN_GESTION_PERSONAS->value,
                    ],
                ],
                'en_rrhh' => [
                    'label' => 'En RRHH',
                    'statuses' => [
                        RequestStatus::EN_RRHH->value,
                    ],
                ],
                'en_contrato' => [
                    'label' => 'En contrato',
                    'statuses' => [
                        RequestStatus::EN_TRAMITACION_CONTRATO->value,
                    ],
                ],
                'cerradas' => [
                    'label' => 'Cerradas',
                    'statuses' => [
                        RequestStatus::FINALIZADA->value,
                        RequestStatus::RECHAZADA->value,
                        RequestStatus::OBSERVADA->value,
                    ],
                ],
            ],
            UserRole::RRHH => [
                'pendientes' => [
                    'label' => 'Pendientes',
                    'statuses' => [
                        RequestStatus::EN_RRHH->value,
                    ],
                ],
                'en_contrato' => [
                    'label' => 'En contrato',
                    'statuses' => [
                        RequestStatus::EN_TRAMITACION_CONTRATO->value,
                    ],
                ],
                'cerradas' => [
                    'label' => 'Cerradas',
                    'statuses' => [
                        RequestStatus::FINALIZADA->value,
                        RequestStatus::RECHAZADA->value,
                        RequestStatus::OBSERVADA->value,
                    ],
                ],
            ],
            UserRole::ADMIN => [
                'pendientes' => [
                    'label' => 'Pendientes',
                    'statuses' => [
                        RequestStatus::BORRADOR->value,
                    ],
                ],
                'en_revision' => [
                    'label' => 'En revisión',
                    'statuses' => [
                        RequestStatus::ENVIADA->value,
                        RequestStatus::EN_GESTION_PERSONAS->value,
                    ],
                ],
                'en_rrhh' => [
                    'label' => 'En RRHH',
                    'statuses' => [
                        RequestStatus::EN_RRHH->value,
                        RequestStatus::OBSERVADA->value,
                    ],
                ],
                'en_contrato' => [
                    'label' => 'En contrato',
                    'statuses' => [
                        RequestStatus::EN_TRAMITACION_CONTRATO->value,
                    ],
                ],
                'cerradas' => [
                    'label' => 'Cerradas',
                    'statuses' => [
                        RequestStatus::RECHAZADA->value,
                        RequestStatus::FINALIZADA->value,
                    ],
                ],
            ],
        };
    }

    private function applyCommonFilters($query, array $validated, bool $canFilterService): void
    {
        $query
            ->when(
                $canFilterService && filled($validated['servicio'] ?? null),
                fn ($builder) => $builder->where('service_id', $validated['servicio'])
            )
            ->when(
                filled($validated['created_from'] ?? null),
                fn ($builder) => $builder->whereDate('created_at', '>=', $validated['created_from'])
            )
            ->when(
                filled($validated['created_to'] ?? null),
                fn ($builder) => $builder->whereDate('created_at', '<=', $validated['created_to'])
            )
            ->when(filled($validated['q'] ?? null), function ($builder) use ($validated) {
                $term = trim((string) $validated['q']);

                $builder->where(function ($searchQuery) use ($term) {
                    if (is_numeric($term)) {
                        $searchQuery->orWhere('id', (int) $term);
                    }

                    $searchQuery
                        ->orWhere('motivo', 'like', "%{$term}%")
                        ->orWhere('nombre_reemplazo', 'like', "%{$term}%");
                });
            });
    }

    private function applyStatusFilter(Builder $query, array $validated): void
    {
        $query->when(
            filled($validated['estado'] ?? null),
            fn (Builder $builder) => $builder->where('status', $validated['estado'])
        );
    }

    public function create(HttpRequest $request): View
    {
        $this->authorize('create', WorkflowRequest::class);

        return view('requests.create');
    }

    public function show(WorkflowRequest $request): View
    {
        $this->authorize('view', $request);

        $request->load([
            'service',
            'creator',
            'actions' => fn ($query) => $query->with('user')->latest(),
            'attachments',
        ]);

        return view('requests.show', [
            'requestModel' => $request,
        ]);
    }

    public function store(StoreRequestRequest $request): JsonResponse
    {
        $this->authorize('create', WorkflowRequest::class);

        $user = $request->user();
        $createdRequest = $this->requestWorkflowService->createDraft($user, $request->validated());

        return response()->json([
            'ok' => true,
            'message' => 'Solicitud creada en borrador.',
            'data' => [
                'id' => $createdRequest->id,
                'status' => $createdRequest->status->value,
            ],
        ], 201);
    }
}
