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

    showToast(message) {
        this.toastMessage = message;

        const toastElement = document.getElementById('feedbackToast');
        if (!toastElement) {
            return;
        }

        const toast = bootstrap.Toast.getOrCreateInstance(toastElement);
        toast.show();
    },
}));

Alpine.start();
