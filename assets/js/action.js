const confirmModalElement = document.querySelector('[data-confirm-modal]');
// Carrega o controlador compartilhado do modal de confirmacao
const confirmModal = window.FluxModal ? window.FluxModal.get(confirmModalElement) : null;
const confirmDeleteButton = document.querySelector('[data-confirm-delete]');
const confirmTitle = document.querySelector('[data-confirm-title]');
const confirmMessage = document.querySelector('[data-confirm-message]');
const confirmIcon = document.querySelector('[data-confirm-icon]');
const confirmLabel = document.querySelector('[data-confirm-label]');
let pendingConfirmForm = null;

// Define os textos originais do modal compartilhado
const defaultConfirmContent = {
    title: confirmTitle ? confirmTitle.textContent : 'excluir registro?',
    message: confirmMessage ? confirmMessage.textContent : 'cuidado! essa acao e permanente.',
    icon: confirmIcon ? confirmIcon.className : 'fa-regular fa-trash-can',
    label: confirmLabel ? confirmLabel.textContent : 'excluir'
};

// Define conteudos conhecidos para formularios de confirmacao
const confirmPresets = {
    periodic: {
        title: 'inserir previs\u00f5es?',
        message: 'Ser\u00e3o criadas transa\u00e7\u00f5es para cada template ativo com pr\u00f3xima execu\u00e7\u00e3o dentro do ciclo atual.',
        icon: 'fa-solid fa-calendar-plus',
        label: 'inserir'
    }
};

/**
 * Exibe um modal de confirmacao ao tentar excluir um registro.
 */
document.addEventListener('click', (event) => {
    // Carrega o botao de exclusao acionado
    const deleteButton = event.target.closest('[data-delete-button]');

    // Verifica se o clique pertence a uma exclusao confirmavel
    if (!deleteButton || !confirmModal) {
        // Interrompe cliques que nao precisam do modal
        return;
    }

    // Carrega o formulario que sera enviado apos a confirmacao
    const form = deleteButton.closest('form');

    // Verifica se existe um formulario para confirmar
    if (!form) {
        // Interrompe quando nao ha formulario relacionado
        return;
    }

    // Interrompe o envio imediato do formulario
    event.preventDefault();
    pendingConfirmForm = form;

    // Define o conteudo padrao para exclusao
    setConfirmContent(defaultConfirmContent);
    confirmModal.open();
});

/**
 * Exibe um modal de confirmacao antes de enviar um formulario sensivel.
 */
document.addEventListener('submit', (event) => {
    // Carrega o formulario enviado
    const form = event.target;

    // Verifica se o formulario precisa do modal compartilhado
    if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-confirm-form') || !confirmModal) {
        // Interrompe formularios que nao precisam de confirmacao
        return;
    }

    // Interrompe o envio ate o usuario confirmar a acao
    event.preventDefault();
    pendingConfirmForm = form;

    // Define o conteudo do modal a partir do preset ou do formulario atual
    setConfirmContent(getConfirmContent(form));

    confirmModal.open();
});

/**
 * Faz o submit do formulario pendente quando o usuario confirma.
 */
if (confirmDeleteButton) {
    confirmDeleteButton.addEventListener('click', () => {
        // Verifica se existe um formulario aguardando confirmacao
        if (!pendingConfirmForm) {
            // Interrompe quando nao ha acao pendente
            return;
        }

        // Define o formulario que sera enviado
        const form = pendingConfirmForm;
        pendingConfirmForm = null;

        // Desativa o botao para evitar envios duplicados durante o redirecionamento
        confirmDeleteButton.disabled = true;
        confirmModal.close();
        form.submit();
    });
}

/**
 * Atualiza os textos e icones do modal de confirmacao compartilhado.
 */
function setConfirmContent(content) {
    // Define o titulo da confirmacao
    if (confirmTitle) {
        confirmTitle.textContent = content.title;
    }

    // Define a mensagem da confirmacao
    if (confirmMessage) {
        confirmMessage.textContent = content.message;
    }

    // Define o icone da acao principal
    if (confirmIcon) {
        confirmIcon.className = content.icon;
    }

    // Define o texto da acao principal
    if (confirmLabel) {
        confirmLabel.textContent = content.label;
    }

    // Reativa o botao quando o modal e preparado para uma nova acao
    if (confirmDeleteButton) {
        confirmDeleteButton.disabled = false;
    }
}

/**
 * Obtem o conteudo de confirmacao definido para um formulario.
 */
function getConfirmContent(form) {
    // Define o preset informado pelo formulario
    const preset = confirmPresets[form.dataset.confirmPreset] || {};

    return {
        title: form.dataset.confirmTitle || preset.title || defaultConfirmContent.title,
        message: form.dataset.confirmMessage || preset.message || defaultConfirmContent.message,
        icon: form.dataset.confirmIcon || preset.icon || defaultConfirmContent.icon,
        label: form.dataset.confirmLabel || preset.label || defaultConfirmContent.label
    };
}

/**
 * Intercepta o submit dos formularios para validar os campos.
 */
document.addEventListener('submit', (event) => {
    // Carrega o formulario enviado
    const form = event.target;

    // Verifica se o formulario usa a validacao compartilhada
    if (!(form instanceof HTMLFormElement) || !form.classList.contains('validate')) {
        // Interrompe formularios sem validacao automatica
        return;
    }

    // Verifica se os campos informados sao validos
    if (!window.validateForms(form)) {
        // Interrompe o envio quando existem campos invalidos
        event.preventDefault();
        window.dispatchEvent(new CustomEvent('auth-tab-height-change'));
    }
});
