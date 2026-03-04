@extends('layouts.admin')

@section('title', 'Detalle solicitud')
@section('page-title', 'Detalle de solicitud')
@section('breadcrumb', 'Inicio / Solicitudes / Detalle')

@php
    $statusStyles = [
        'BORRADOR' => 'secondary',
        'OBSERVADA' => 'warning',
        'ENVIADA' => 'primary',
        'EN_GESTION_PERSONAS' => 'info',
        'EN_RRHH' => 'info',
        'RECHAZADA' => 'danger',
        'EN_TRAMITACION_CONTRATO' => 'dark',
        'FINALIZADA' => 'success',
    ];

    $status = $requestModel->status->value;
@endphp

@section('content')
    <section class="card border-0 shadow-sm mb-4">
        <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <h1 class="h4 mb-1">Solicitud #{{ $requestModel->id }}</h1>
                <p class="text-muted mb-0">Vista de detalle con trazabilidad de acciones registradas.</p>
            </div>
            <a href="{{ route('requests.index') }}" class="btn btn-outline-secondary">Volver al listado</a>
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
                    <span id="requestStatusBadge" class="badge text-bg-{{ $statusStyles[$status] ?? 'secondary' }}">{{ $status }}</span>
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

    <section class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 pb-0">
            <h2 class="h5 mb-0">Acciones</h2>
        </div>
        <div id="workflowActionsContainer" class="card-body d-flex flex-wrap gap-2 align-items-center">
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

            <span id="noWorkflowActionsMessage" class="text-muted d-none">No tienes acciones disponibles para esta solicitud.</span>
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
                            <td colspan="5" class="text-center text-muted py-4">No hay acciones registradas.</td>
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const workflowUrl = @json(url("/requests/{$requestModel->id}/actions"));
        const statusStyles = @json($statusStyles);
        const initialCan = @json([
            'send' => auth()->user()->can('send', $requestModel),
            'take' => auth()->user()->can('take', $requestModel),
            'send_to_rrhh' => auth()->user()->can('sendToRrhh', $requestModel),
            'approve_rrhh' => auth()->user()->can('approveRrhh', $requestModel),
            'mark_contract_done' => auth()->user()->can('markContractDone', $requestModel),
            'observe' => auth()->user()->can('observe', $requestModel),
            'reject' => auth()->user()->can('reject', $requestModel),
        ]);
        const statusBadge = document.getElementById('requestStatusBadge');
        const workflowActionsContainer = document.getElementById('workflowActionsContainer');
        const noWorkflowActionsMessage = document.getElementById('noWorkflowActionsMessage');
        const actionsHistoryBody = document.getElementById('requestActionsHistoryBody');

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
            const response = await fetch(`${workflowUrl}/${action}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
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

            const style = statusStyles[status] || 'secondary';
            statusBadge.className = `badge text-bg-${style}`;
            statusBadge.textContent = status;
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

        const refreshStateFromCurrentPage = async () => {
            const response = await fetch(window.location.href, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error('No fue posible refrescar el estado de la solicitud.');
            }

            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const nextBadge = doc.querySelector('#requestStatusBadge');
            if (nextBadge && statusBadge) {
                statusBadge.className = nextBadge.className;
                statusBadge.textContent = nextBadge.textContent;
            }

            const nextHistoryBody = doc.querySelector('#requestActionsHistoryBody');
            if (nextHistoryBody && actionsHistoryBody) {
                actionsHistoryBody.innerHTML = nextHistoryBody.innerHTML;
            }
        };

        const updateWorkflowUi = async (data) => {
            updateStatusBadge(data.status);
            updateActionsVisibility(data.can || {});

            if (data.latest_action) {
                prependActionRow(data.latest_action);
                return;
            }

            await refreshStateFromCurrentPage();
        };

        updateActionsVisibility(initialCan);

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
