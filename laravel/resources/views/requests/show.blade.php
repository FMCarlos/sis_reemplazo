@extends('layouts.admin')

@section('title', 'Detalle solicitud')
@section('page-title', 'Detalle de solicitud')
@section('breadcrumb', 'Inicio / Solicitudes / Detalle')

@php
    $statusClassMap = [
        'BORRADOR' => 'bg-secondary',
        'ENVIADA' => 'bg-primary',
        'EN_GESTION_PERSONAS' => 'bg-warning text-dark',
        'EN_RRHH' => 'bg-info',
        'EN_TRAMITACION_CONTRATO' => 'bg-info',
        'FINALIZADA' => 'bg-success',
        'OBSERVADA' => 'bg-warning text-dark',
        'RECHAZADA' => 'bg-danger',
    ];

    $workflowSteps = [
        'BORRADOR',
        'ENVIADA',
        'EN_GESTION_PERSONAS',
        'EN_RRHH',
        'EN_TRAMITACION_CONTRATO',
        'FINALIZADA',
    ];

    $status = $requestModel->status->value;
    $latestAction = $requestModel->actions->first();
    $isSpecialStatus = in_array($status, ['OBSERVADA', 'RECHAZADA'], true);
    $baseStatus = $isSpecialStatus
        ? ($latestAction?->from_status && in_array($latestAction->from_status, $workflowSteps, true)
            ? $latestAction->from_status
            : 'BORRADOR')
        : $status;

    $currentIndex = array_search($baseStatus, $workflowSteps, true);
    $currentIndex = $currentIndex === false ? 0 : $currentIndex;
@endphp

@section('content')
    <style>
        .workflow-stepper {
            display: flex;
            flex-wrap: wrap;
            gap: .75rem;
            padding: 0;
            margin: 0;
            list-style: none;
        }

        .workflow-step {
            display: flex;
            align-items: center;
            gap: .5rem;
            color: var(--bs-secondary-color);
        }

        .workflow-step-dot {
            width: 1.5rem;
            height: 1.5rem;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .75rem;
            font-weight: 700;
            border: 2px solid var(--bs-secondary-color);
            background: #fff;
        }

        .workflow-step.is-complete {
            color: var(--bs-success);
        }

        .workflow-step.is-complete .workflow-step-dot {
            border-color: var(--bs-success);
            background-color: var(--bs-success);
            color: #fff;
        }

        .workflow-step.is-current {
            color: var(--bs-primary);
            font-weight: 600;
        }

        .workflow-step.is-current .workflow-step-dot {
            border-color: var(--bs-primary);
            background-color: var(--bs-primary);
            color: #fff;
        }

        @media (max-width: 767.98px) {
            .workflow-step {
                width: 100%;
            }
        }
    </style>

    <section class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
                <div>
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <h1 class="h4 mb-0">Solicitud #{{ $requestModel->id }}</h1>
                        <span id="specialStatusBadge" class="badge {{ $status === 'OBSERVADA' ? 'bg-warning text-dark' : 'bg-danger' }} {{ $isSpecialStatus ? '' : 'd-none' }} border border-2 border-opacity-50">
                            {{ $status }}
                        </span>
                    </div>
                    <p class="text-muted mb-0">Vista de detalle con trazabilidad de acciones registradas.</p>
                </div>

                <div id="workflowActionsContainer" class="d-flex flex-wrap justify-content-lg-end align-items-center gap-2">
                    <a href="{{ route('requests.index') }}" class="btn btn-outline-secondary">Volver al listado</a>

                    @can('send', $requestModel)
                        <button type="button" class="btn btn-primary" data-workflow-action="send">Enviar</button>
                    @endcan
                    @can('take', $requestModel)
                        <button type="button" class="btn btn-outline-primary" data-workflow-action="take">Tomar en gestión</button>
                    @endcan
                    @can('sendToRrhh', $requestModel)
                        <button type="button" class="btn btn-outline-info" data-workflow-action="send_to_rrhh">Enviar a RRHH</button>
                    @endcan
                    @can('approveRrhh', $requestModel)
                        <button type="button" class="btn btn-success" data-workflow-action="approve_rrhh">Aprobar RRHH</button>
                    @endcan
                    @can('markContractDone', $requestModel)
                        <button type="button" class="btn btn-dark" data-workflow-action="mark_contract_done">Marcar contrato finalizado</button>
                    @endcan
                    @can('observe', $requestModel)
                        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#commentActionModal" data-workflow-comment-action="observe">Observar</button>
                    @endcan
                    @can('reject', $requestModel)
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#commentActionModal" data-workflow-comment-action="reject">Rechazar</button>
                    @endcan

                    <small id="noWorkflowActionsMessage" class="text-muted d-none">No tienes acciones disponibles para esta solicitud.</small>
                </div>
            </div>

            <hr class="my-3">

            <ul id="workflowStepper" class="workflow-stepper">
                @foreach ($workflowSteps as $index => $step)
                    @php
                        $stepClass = $index < $currentIndex ? 'is-complete' : ($index === $currentIndex ? 'is-current' : '');
                    @endphp
                    <li class="workflow-step {{ $stepClass }}" data-step-status="{{ $step }}">
                        <span class="workflow-step-dot">{{ $index + 1 }}</span>
                        <span class="small">{{ $step }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    <section class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 pb-0">
            <h2 class="h5 mb-0">Detalle solicitud</h2>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <small class="text-muted d-block">ID</small>
                    <span class="fw-semibold">#{{ $requestModel->id }}</span>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">Estado</small>
                    <span id="requestStatusBadge" class="badge {{ $requestModel->badgeClass() }}">{{ $status }}</span>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">Servicio</small>
                    <span>{{ $requestModel->service?->name ?? 'Sin servicio' }}</span>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">Creada</small>
                    <span>{{ $requestModel->created_at?->format('Y-m-d H:i') }}</span>
                </div>
                <div class="col-md-8">
                    <small class="text-muted d-block">Motivo</small>
                    <span>{{ $requestModel->motivo ?: 'Sin motivo ingresado' }}</span>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">Fecha inicio</small>
                    <span>{{ $requestModel->fecha_inicio?->format('Y-m-d') ?? '—' }}</span>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">Fecha fin</small>
                    <span>{{ $requestModel->fecha_fin?->format('Y-m-d') ?? '—' }}</span>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">Nombre reemplazo</small>
                    <span>{{ $requestModel->nombre_reemplazo ?: 'No definido' }}</span>
                </div>
            </div>
        </div>
    </section>

    <section class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pb-0">
            <h2 class="h5 mb-0">Historial / Auditoría</h2>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th>Acción</th>
                        <th>From -&gt; To</th>
                        <th>Comentario</th>
                    </tr>
                    </thead>
                    <tbody id="requestActionsHistoryBody">
                    @forelse ($requestModel->actions as $action)
                        <tr>
                            <td>{{ $action->created_at?->format('Y-m-d H:i') }}</td>
                            <td>{{ $action->user?->name ?? 'Usuario eliminado' }}</td>
                            <td>{{ $action->action }}</td>
                            <td>{{ $action->from_status }} -&gt; {{ $action->to_status }}</td>
                            <td>{{ $action->comment ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr id="emptyHistoryRow">
                            <td colspan="5" class="text-center text-muted py-4">Aún no hay movimientos registrados para esta solicitud.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <div class="modal fade" id="commentActionModal" tabindex="-1" aria-labelledby="commentActionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" id="commentActionForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="commentActionModalLabel">Registrar comentario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="commentActionName" value="">
                    <label for="actionComment" class="form-label">Comentario</label>
                    <textarea class="form-control" id="actionComment" rows="4" required></textarea>
                    <small class="text-muted">El comentario es obligatorio para observar y rechazar.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Confirmar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="workflowToast" class="toast align-items-center text-bg-dark border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>
@endsection

@php
    $initialCan = [
        'send' => auth()->user()->can('send', $requestModel),
        'take' => auth()->user()->can('take', $requestModel),
        'send_to_rrhh' => auth()->user()->can('sendToRrhh', $requestModel),
        'approve_rrhh' => auth()->user()->can('approveRrhh', $requestModel),
        'mark_contract_done' => auth()->user()->can('markContractDone', $requestModel),
        'observe' => auth()->user()->can('observe', $requestModel),
        'reject' => auth()->user()->can('reject', $requestModel),
    ];

    $workflowActionUrls = [
        'send' => route('requests.actions.send', $requestModel),
        'take' => route('requests.actions.take', $requestModel),
        'send_to_rrhh' => route('requests.actions.send_to_rrhh', $requestModel),
        'observe' => route('requests.actions.observe', $requestModel),
        'reject' => route('requests.actions.reject', $requestModel),
        'approve_rrhh' => route('requests.actions.approve_rrhh', $requestModel),
        'mark_contract_done' => route('requests.actions.mark_contract_done', $requestModel),
    ];
@endphp

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const workflowActionUrls = @json($workflowActionUrls);
        const statusClassMap = @json($statusClassMap);
        const workflowSteps = @json($workflowSteps);
        const initialCan = @json($initialCan);

        const statusBadge = document.getElementById('requestStatusBadge');
        const specialStatusBadge = document.getElementById('specialStatusBadge');
        const workflowActionsContainer = document.getElementById('workflowActionsContainer');
        const noWorkflowActionsMessage = document.getElementById('noWorkflowActionsMessage');
        const actionsHistoryBody = document.getElementById('requestActionsHistoryBody');
        const workflowStepper = document.getElementById('workflowStepper');

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const toastElement = document.getElementById('workflowToast');
        const toastBody = toastElement?.querySelector('.toast-body');
        const toast = toastElement ? new window.bootstrap.Toast(toastElement) : null;

        const showToast = (message, isError = false) => {
            if (!toastElement || !toastBody || !toast) {
                return;
            }

            toastElement.classList.toggle('text-bg-danger', isError);
            toastElement.classList.toggle('text-bg-success', !isError);
            toastBody.textContent = message;
            toast.show();
        };

        const runWorkflowAction = async (action, comment = null) => {
            const endpoint = workflowActionUrls[action];

            if (!endpoint) {
                throw new Error('La acción seleccionada no tiene endpoint configurado.');
            }

            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    Accept: 'application/json',
                },
                body: JSON.stringify({ comment }),
            });

            const payload = await response.json();

            if (!response.ok || payload.ok !== true) {
                throw new Error(payload.message || 'No fue posible aplicar la acción.');
            }

            await updateWorkflowUi(payload.data || {});
            showToast(payload.message || 'Acción ejecutada correctamente.');
            return payload;
        };

        const updateStatusBadge = (status) => {
            if (!statusBadge || !status) {
                return;
            }

            statusBadge.className = `badge ${statusClassMap[status] || 'bg-secondary'}`;
            statusBadge.textContent = status;
        };

        const updateTimeline = (status) => {
            if (!workflowStepper || !status) {
                return;
            }

            const isSpecial = ['OBSERVADA', 'RECHAZADA'].includes(status);
            const allRows = Array.from(actionsHistoryBody?.querySelectorAll('tr') || []);
            const latestRowCells = allRows[0]?.querySelectorAll('td') || [];
            const latestTransition = latestRowCells[3]?.textContent || '';
            const fromStatus = latestTransition.split('->')[0]?.trim();
            const baseStatus = isSpecial && workflowSteps.includes(fromStatus) ? fromStatus : status;
            const currentIndex = Math.max(workflowSteps.indexOf(baseStatus), 0);

            workflowStepper.querySelectorAll('[data-step-status]').forEach((item, index) => {
                item.classList.remove('is-complete', 'is-current');

                if (index < currentIndex) {
                    item.classList.add('is-complete');
                }

                if (index === currentIndex) {
                    item.classList.add('is-current');
                }
            });

            if (specialStatusBadge) {
                specialStatusBadge.classList.toggle('d-none', !isSpecial);
                if (isSpecial) {
                    specialStatusBadge.textContent = status;
                    specialStatusBadge.className = `badge border border-2 border-opacity-50 ${status === 'OBSERVADA' ? 'bg-warning text-dark' : 'bg-danger'}`;
                }
            }
        };

        const updateActionsVisibility = (can = {}) => {
            if (!workflowActionsContainer) {
                return;
            }

            const actionButtons = workflowActionsContainer.querySelectorAll('[data-workflow-action], [data-workflow-comment-action]');
            let visibleCount = 0;

            actionButtons.forEach((button) => {
                const actionName = button.dataset.workflowAction || button.dataset.workflowCommentAction;
                const allowed = Boolean(can[actionName]);
                button.classList.toggle('d-none', !allowed);

                if (allowed) {
                    visibleCount += 1;
                }
            });

            if (noWorkflowActionsMessage) {
                noWorkflowActionsMessage.classList.toggle('d-none', visibleCount > 0);
            }
        };

        const prependActionRow = (latestAction) => {
            if (!actionsHistoryBody || !latestAction) {
                return;
            }

            const emptyRow = document.getElementById('emptyHistoryRow');
            if (emptyRow) {
                emptyRow.remove();
            }

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${latestAction.created_at ?? '—'}</td>
                <td>${latestAction.user ?? 'Usuario eliminado'}</td>
                <td>${latestAction.action ?? '—'}</td>
                <td>${latestAction.from_status ?? '—'} -&gt; ${latestAction.to_status ?? '—'}</td>
                <td>${latestAction.comment ?? '—'}</td>
            `;

            actionsHistoryBody.prepend(row);
        };

        const updateWorkflowUi = async (data) => {
            updateStatusBadge(data.status);
            updateActionsVisibility(data.can || {});

            if (data.latest_action) {
                prependActionRow(data.latest_action);
            }

            updateTimeline(data.status);
        };

        updateActionsVisibility(initialCan);
        updateTimeline(statusBadge?.textContent?.trim() || '');

        document.querySelectorAll('[data-workflow-action]').forEach((button) => {
            button.addEventListener('click', async () => {
                try {
                    await runWorkflowAction(button.dataset.workflowAction);
                } catch (error) {
                    showToast(error.message, true);
                }
            });
        });

        const commentModalElement = document.getElementById('commentActionModal');
        const commentActionName = document.getElementById('commentActionName');
        const actionComment = document.getElementById('actionComment');
        const commentForm = document.getElementById('commentActionForm');

        if (commentModalElement && commentActionName && actionComment && commentForm) {
            commentModalElement.addEventListener('show.bs.modal', (event) => {
                const button = event.relatedTarget;
                commentActionName.value = button?.dataset?.workflowCommentAction || '';
                actionComment.value = '';
            });

            commentForm.addEventListener('submit', async (event) => {
                event.preventDefault();

                if (!actionComment.value.trim()) {
                    showToast('Debes ingresar un comentario.', true);
                    return;
                }

                try {
                    await runWorkflowAction(commentActionName.value, actionComment.value.trim());
                    const modal = window.bootstrap.Modal.getInstance(commentModalElement);
                    modal?.hide();
                } catch (error) {
                    showToast(error.message, true);
                }
            });
        }
    });
</script>
@endpush
