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
