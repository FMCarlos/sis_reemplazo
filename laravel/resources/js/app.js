import './bootstrap';

import * as bootstrap from 'bootstrap';
import Alpine from 'alpinejs';

window.bootstrap = bootstrap;
window.Alpine = Alpine;

Alpine.data('adminUi', () => ({
    modalTitle: 'Observar solicitud',
    modalMessage: '',
    toastMessage: 'Acción realizada',
    selectedRequest: null,

    init() {
        window.addEventListener('admin-toast', (event) => {
            const { message, type } = event.detail ?? {};
            this.showToast(message ?? 'Acción realizada', type ?? 'success');
        });
    },

    openObservationModal(request) {
        this.selectedRequest = request;
        this.modalTitle = `Observar solicitud #${request.id}`;
        this.modalMessage = request.servicio ? `Servicio: ${request.servicio}` : '';

        const modalElement = document.getElementById('actionModal');
        if (!modalElement) {
            return;
        }

        const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
        modal.show();
    },

    confirmAction() {
        const modalElement = document.getElementById('actionModal');
        if (modalElement) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
            modal.hide();
        }

        const requestId = this.selectedRequest?.id ?? 'N/A';
        this.showToast(`Acción realizada en solicitud #${requestId}`);
    },

    showToast(message, type = 'success') {
        this.toastMessage = message;

        const toastElement = document.getElementById('feedbackToast');
        const toastHeader = document.querySelector('#feedbackToast .toast-header');
        if (!toastElement || !toastHeader) {
            return;
        }

        toastHeader.classList.remove('bg-success', 'bg-danger');
        toastHeader.classList.add(type === 'error' ? 'bg-danger' : 'bg-success');

        const toast = bootstrap.Toast.getOrCreateInstance(toastElement);
        toast.show();
    },
}));

Alpine.start();
