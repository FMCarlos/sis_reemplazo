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

    $statusProgressMap = [
        'BORRADOR' => 0,
        'ENVIADA' => 20,
        'EN_GESTION_PERSONAS' => 40,
        'EN_RRHH' => 60,
        'EN_TRAMITACION_CONTRATO' => 80,
        'FINALIZADA' => 100,
    ];

    $progressValue = $statusProgressMap[$baseStatus] ?? 0;

    $actionLabelMap = [
        'send' => 'Enviada',
        'approve_rrhh' => 'Aprobada por RRHH',
        'observe' => 'Observada',
        'reject' => 'Rechazada',
        'create_draft' => 'Borrador creado',
    ];
@endphp

@section('content')
    <style>
        .status-progress-wrapper .progress {
            height: .45rem;
        }

        .activity-timeline {
            position: relative;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .activity-item {
            display: flex;
            gap: .85rem;
            position: relative;
            padding-bottom: 1.25rem;
        }

        .activity-item:last-child {
            padding-bottom: 0;
        }

        .activity-dot-wrap {
            position: relative;
            min-height: 2.2rem;
        }

        .activity-dot {
            width: .85rem;
            height: .85rem;
            border-radius: 50%;
            margin-top: .45rem;
        }

        .activity-line {
            position: absolute;
            top: 1.35rem;
            left: .35rem;
            bottom: -.2rem;
            width: 2px;
            background-color: var(--bs-border-color);
        }

        .activity-item:last-child .activity-line {
            display: none;
        }

        .activity-content {
            flex: 1;
            background-color: var(--bs-light);
            border: 1px solid var(--bs-border-color);
            border-radius: .5rem;
            padding: .75rem;
        }

        .empty-activity {
            border: 1px dashed var(--bs-border-color);
            border-radius: .5rem;
            padding: 2rem 1rem;
        }

        @media (max-width: 767.98px) {
            .request-detail-header {
                flex-direction: column;
                align-items: flex-start !important;
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
                </div>
            </div>
        </div>
    </section>

    <section class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 pb-0 d-flex justify-content-between align-items-center gap-3 request-detail-header">
            <h2 class="h5 mb-0">Detalle solicitud</h2>
            <div id="detailWorkflowActionsContainer" class="d-flex flex-wrap justify-content-lg-end align-items-center gap-2">
                @can('send', $requestModel)
                        <button type="button" class="btn btn-primary" data-workflow-action="send">Enviar</button>
                @endcan
                @can('approveRrhh', $requestModel)
                        <button type="button" class="btn btn-success" data-workflow-action="approve_rrhh">Aprobar RRHH</button>
                @endcan
                @can('observe', $requestModel)
                        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#commentActionModal" data-workflow-comment-action="observe">Observar</button>
                @endcan
                @can('reject', $requestModel)
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#commentActionModal" data-workflow-comment-action="reject">Rechazar</button>
                @endcan

                <small id="noDetailWorkflowActionsMessage" class="text-muted d-none">No tienes acciones disponibles.</small>
            </div>
        </div>
        <div class="card-body">
            <div class="status-progress-wrapper mb-4">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <small class="text-muted">Estado actual:</small>
                    <span id="requestStatusBadge" class="badge {{ $requestModel->badgeClass() }}">{{ $status }}</span>
                    <span id="specialStatusNote" class="small text-muted {{ $isSpecialStatus ? '' : 'd-none' }}">
                        {{ $status === 'OBSERVADA' ? 'Observada (requiere ajustes).' : 'Rechazada.' }}
                    </span>
                </div>
                <div class="progress" role="progressbar" aria-label="Progreso de solicitud" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $progressValue }}">
                    <div id="requestProgressBar" class="progress-bar bg-primary" style="width: {{ $progressValue }}%"></div>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <small class="text-muted d-block">ID</small>
                    <span class="fw-semibold">#{{ $requestModel->id }}</span>
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
            <div id="requestActionsHistoryBody" class="activity-timeline">
                @forelse ($requestModel->actions as $action)
                    @php
                        $toStatusClass = $statusClassMap[$action->to_status] ?? 'bg-secondary';
                        $actionLabel = $actionLabelMap[$action->action] ?? ucfirst(str_replace('_', ' ', $action->action));
                    @endphp
                    <article class="activity-item">
                        <div class="activity-dot-wrap">
                            <span class="activity-dot d-block {{ $toStatusClass }}"></span>
                            <span class="activity-line"></span>
                        </div>
                        <div class="activity-content">
                            <div class="d-flex flex-column flex-md-row justify-content-between gap-1">
                                <strong>{{ $actionLabel }}</strong>
                                <small class="text-muted">{{ $action->created_at?->format('Y-m-d H:i') }} · {{ $action->created_at?->diffForHumans() }}</small>
                            </div>
                            <small class="text-muted d-block">Por {{ $action->user?->name ?? 'Usuario eliminado' }}</small>
                            <small class="text-muted d-block" data-transition>{{ $action->from_status ?: '—' }} → {{ $action->to_status ?: '—' }}</small>
                            @if ($action->comment)
                                <p class="mb-0 mt-2 small">{{ $action->comment }}</p>
                            @endif
                        </div>
                    </article>
                @empty
                    <div id="emptyHistoryRow" class="empty-activity text-center text-muted">
                        <div class="fs-4 mb-2">🕒</div>
                        <p class="mb-0">Aún no hay movimientos registrados para esta solicitud.</p>
                    </div>
                @endforelse
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
        'approve_rrhh' => auth()->user()->can('approveRrhh', $requestModel),
        'observe' => auth()->user()->can('observe', $requestModel),
        'reject' => auth()->user()->can('reject', $requestModel),
    ];

    $workflowActionUrls = [
        'send' => route('requests.actions.send', $requestModel),
        'observe' => route('requests.actions.observe', $requestModel),
        'reject' => route('requests.actions.reject', $requestModel),
        'approve_rrhh' => route('requests.actions.approve_rrhh', $requestModel),
    ];
@endphp

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const workflowActionUrls = @json($workflowActionUrls);
        const statusClassMap = @json($statusClassMap);
        const workflowSteps = @json($workflowSteps);
        const statusProgressMap = @json($statusProgressMap);
        const actionLabelMap = @json($actionLabelMap);
        const initialCan = @json($initialCan);

        const statusBadge = document.getElementById('requestStatusBadge');
        const specialStatusBadge = document.getElementById('specialStatusBadge');
        const workflowActionsContainer = document.getElementById('workflowActionsContainer');
        const detailWorkflowActionsContainer = document.getElementById('detailWorkflowActionsContainer');
        const noDetailWorkflowActionsMessage = document.getElementById('noDetailWorkflowActionsMessage');
        const actionsHistoryBody = document.getElementById('requestActionsHistoryBody');
        const requestProgressBar = document.getElementById('requestProgressBar');
        const specialStatusNote = document.getElementById('specialStatusNote');

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

        const updateProgress = (status) => {
            if (!requestProgressBar || !status) {
                return;
            }

            const isSpecial = ['OBSERVADA', 'RECHAZADA'].includes(status);
            const latestActionItem = actionsHistoryBody?.querySelector('.activity-item');
            const transitionText = latestActionItem?.querySelector('[data-transition]')?.textContent || '';
            const fromStatus = transitionText.split('→')[0]?.trim();
            const baseStatus = isSpecial && workflowSteps.includes(fromStatus) ? fromStatus : status;
            const progressValue = statusProgressMap[baseStatus] ?? 0;
            requestProgressBar.style.width = `${progressValue}%`;
            requestProgressBar.parentElement?.setAttribute('aria-valuenow', String(progressValue));

            if (specialStatusBadge) {
                specialStatusBadge.classList.toggle('d-none', !isSpecial);
                if (isSpecial) {
                    specialStatusBadge.textContent = status;
                    specialStatusBadge.className = `badge border border-2 border-opacity-50 ${status === 'OBSERVADA' ? 'bg-warning text-dark' : 'bg-danger'}`;
                }
            }

            if (specialStatusNote) {
                specialStatusNote.classList.toggle('d-none', !isSpecial);
                if (isSpecial) {
                    specialStatusNote.textContent = status === 'OBSERVADA' ? 'Observada (requiere ajustes).' : 'Rechazada.';
                }
            }
        };

        const updateActionsVisibility = (can = {}) => {
            if (!workflowActionsContainer || !detailWorkflowActionsContainer) {
                return;
            }

            const actionButtons = detailWorkflowActionsContainer.querySelectorAll('[data-workflow-action], [data-workflow-comment-action]');
            let visibleCount = 0;

            actionButtons.forEach((button) => {
                const actionName = button.dataset.workflowAction || button.dataset.workflowCommentAction;
                const allowed = Boolean(can[actionName]);
                button.classList.toggle('d-none', !allowed);

                if (allowed) {
                    visibleCount += 1;
                }
            });

            if (noDetailWorkflowActionsMessage) {
                noDetailWorkflowActionsMessage.classList.toggle('d-none', visibleCount > 0);
            }
        };

        const prependActionRow = (latestAction, currentStatus) => {
            if (!actionsHistoryBody || !latestAction) {
                return;
            }

            const emptyRow = document.getElementById('emptyHistoryRow');
            if (emptyRow) {
                emptyRow.remove();
            }

            const item = document.createElement('article');
            const dotClass = statusClassMap[latestAction.to_status] || statusClassMap[currentStatus] || 'bg-secondary';
            const actionLabel = actionLabelMap[latestAction.action] || latestAction.action || '—';

            item.className = 'activity-item';
            item.innerHTML = `
                <div class="activity-dot-wrap">
                    <span class="activity-dot d-block ${dotClass}"></span>
                    <span class="activity-line"></span>
                </div>
                <div class="activity-content">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-1">
                        <strong>${actionLabel}</strong>
                        <small class="text-muted">${latestAction.created_at ?? '—'}</small>
                    </div>
                    <small class="text-muted d-block">Por ${latestAction.user ?? 'Usuario eliminado'}</small>
                    <small class="text-muted d-block" data-transition>${latestAction.from_status ?? '—'} → ${latestAction.to_status ?? '—'}</small>
                    ${latestAction.comment && latestAction.comment !== '—' ? `<p class="mb-0 mt-2 small">${latestAction.comment}</p>` : ''}
                </div>
            `;

            actionsHistoryBody.prepend(item);
        };

        const updateWorkflowUi = async (data) => {
            updateStatusBadge(data.status);
            updateActionsVisibility(data.can || {});

            if (data.latest_action) {
                prependActionRow(data.latest_action, data.status);
            }

            updateProgress(data.status);
        };

        updateActionsVisibility(initialCan);
        updateProgress(statusBadge?.textContent?.trim() || '');

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
