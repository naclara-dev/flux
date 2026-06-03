document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement) || !form.classList.contains('validate')) {
        return;
    }

    if (!window.validateForms(form)) {
        event.preventDefault();
    }
});

