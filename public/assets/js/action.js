const deleteConfirmModalElement = document.querySelector('[data-delete-confirm-modal]');
const deleteConfirmModal = window.FluxModal ? window.FluxModal.create(deleteConfirmModalElement, {
    closeSelector: '[data-close-delete-confirm-modal]'
}) : null;
const confirmDeleteButton = document.querySelector('[data-confirm-delete]');
let pendingDeleteForm = null;

document.addEventListener('click', (event) => {
    const deleteButton = event.target.closest('[data-delete-button]');

    if (!deleteButton || !deleteConfirmModal) {
        return;
    }

    const form = deleteButton.closest('form');

    if (!form) {
        return;
    }

    event.preventDefault();
    pendingDeleteForm = form;
    deleteConfirmModal.open();
});

if (confirmDeleteButton) {
    confirmDeleteButton.addEventListener('click', () => {
        if (!pendingDeleteForm) {
            return;
        }

        const form = pendingDeleteForm;
        pendingDeleteForm = null;
        deleteConfirmModal.close();
        form.submit();
    });
}

document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement) || !form.classList.contains('validate')) {
        return;
    }

    if (!window.validateForms(form)) {
        event.preventDefault();
        window.dispatchEvent(new CustomEvent('auth-panel-height-change'));
    }
});
