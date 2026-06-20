(function () {
    // Carrega os elementos principais do formulário de transação
    const modal = document.querySelector('[data-transaction-modal]');
    const form = document.querySelector('[data-transaction-form]');
    const openButtons = document.querySelectorAll('.modal-toggle[data-modal-target="#transaction-modal"]');
    const paidInput = document.querySelector('[data-transaction-paid-input]');
    const modalTitle = document.querySelector('[data-transaction-modal-title]');

    // Carrega o controlador compartilhado do modal
    const transactionModal = window.FluxModal ? window.FluxModal.get(modal) : null;

    // Carrega os comboboxes compartilhados do formulário
    const selects = {
        type: getSelect('type'),
        wallet: getSelect('wallet'),
        category: getSelect('category'),
        entity: getSelect('entity'),
        template: getSelect('template'),
        'payment-method': getSelect('payment-method')
    };

    // Verifica se a estrutura obrigatória está disponível
    if (!transactionModal || !form || !paidInput || Object.values(selects).some(function (select) {
        return !select;
    })) {
        // Interrompe a inicialização quando a tela está incompleta
        return;
    }

    // Percorre os toggles usados para criar uma transação
    openButtons.forEach(function (button) {
        // Prepara o formulário antes da abertura declarativa
        button.addEventListener('click', function () {
            resetForm();
        });
    });

    // Intercepta cliques nas transações editáveis
    document.addEventListener('click', function (event) {
        // Carrega o botão de edição mais próximo do clique
        const button = event.target.closest('[data-edit-transaction]');

        // Verifica se o clique ocorreu em uma transação
        if (!button) {
            // Interrompe quando não existe uma transação para editar
            return;
        }

        fillForm(button.dataset);
        transactionModal.open();
    });

    // Aplica os dados relacionados quando o usuário escolhe um template
    selects.template.element.addEventListener('flux:select-change', function (event) {
        // Verifica se a opção selecionada possui dados de template
        if (!event.detail.option) {
            // Interrompe quando o template foi limpo
            return;
        }

        applyTemplate(event.detail.option);
    });

    // Restaura os valores iniciais do formulário
    function resetForm() {
        form.reset();
        form.elements.amount.value = '0,00';
        form.elements.occurrence_date.value = new Date().toISOString().slice(0, 10);
        paidInput.checked = false;
        setModalTitle('novo registro');

        // Percorre os comboboxes para restaurar seus placeholders
        Object.values(selects).forEach(function (select) {
            select.reset();
        });

        applyDefaults();
    }

    // Preenche o formulário com os dados da transação
    function fillForm(transaction) {
        form.reset();
        form.elements.id.value = transaction.transactionId || '';
        form.elements.title.value = transaction.transactionTitle || '';
        form.elements.amount.value = transaction.transactionAmount || '0,00';
        form.elements.occurrence_date.value = transaction.transactionOccurrenceDate || '';
        form.elements.due_date.value = transaction.transactionDueDate || '';
        form.elements.paid_at.value = transaction.transactionPaidAt || '';
        paidInput.checked = transaction.transactionPaid === '1';
        setModalTitle('editar registro');

        // Define os relacionamentos selecionados sem emitir eventos de usuário
        selects.type.set(transaction.transactionType, '', false);
        selects.wallet.set(transaction.transactionWalletId, transaction.transactionWalletName || 'escolha uma wallet', false);
        selects.category.set(transaction.transactionCategoryId, transaction.transactionCategoryName || 'escolha uma categoria', false);
        selects.entity.set(transaction.transactionEntityId, transaction.transactionEntityName || 'escolha uma entidade', false);
        selects.template.set(transaction.transactionTemplateId, transaction.transactionTemplateName || 'sem template', false);
        selects['payment-method'].set(transaction.transactionPaymentMethodId, transaction.transactionPaymentMethodName || 'escolha uma forma', false);
    }

    // Define o título exibido no modal
    function setModalTitle(title) {
        // Verifica se o título está disponível
        if (modalTitle) {
            modalTitle.textContent = title;
        }
    }

    // Carrega um combobox do formulário pelo nome
    function getSelect(name) {
        // Verifica se a API compartilhada e o modal estão disponíveis
        if (!window.FluxSelect || !modal) {
            // Interrompe a busca sem a estrutura compartilhada
            return null;
        }

        return window.FluxSelect.get(modal.querySelector('[data-select-name="' + name + '"]'));
    }

    // Aplica as configurações padrão do usuário
    function applyDefaults() {
        applyDefaultSelect('type', modal.dataset.defaultType);
        applyDefaultSelect('wallet', modal.dataset.defaultWalletId);
        applyDefaultSelect('entity', modal.dataset.defaultEntityId);
        applyDefaultSelect('payment-method', modal.dataset.defaultPaymentMethodId);
    }

    // Define o valor padrão de um combobox
    function applyDefaultSelect(name, id) {
        // Verifica se existe um valor configurado
        if (!id) {
            // Interrompe quando não existe configuração padrão
            return;
        }

        selects[name].set(id, '', false);
    }

    // Preenche os campos relacionados ao template selecionado
    function applyTemplate(option) {
        form.elements.title.value = option.dataset.valueLabel || '';
        form.elements.amount.value = formatMoney(option.dataset.templateAmount);
        form.elements.occurrence_date.value = getNextDateFromMonthDay(option.dataset.templateMonthDay);
        selects.wallet.set(option.dataset.templateWalletId, option.dataset.templateWalletName, false);
        selects.category.set(option.dataset.templateCategoryId, option.dataset.templateCategoryName, false);
        selects.entity.set(option.dataset.templateEntityId, option.dataset.templateEntityName, false);
    }

    // Calcula a próxima ocorrência para o dia mensal do template
    function getNextDateFromMonthDay(monthDay) {
        // Inicializa a data atual e limita o dia informado
        const today = new Date();
        const day = Math.min(Math.max(Number(monthDay || 1), 1), 31);
        const year = today.getFullYear();
        let month = today.getMonth();
        let date = createDate(year, month, day);

        // Verifica se a ocorrência deste mês já passou
        if (date < startOfDay(today)) {
            // Define a ocorrência para o próximo mês
            month += 1;
            date = createDate(year, month, day);
        }

        return formatDate(date);
    }

    // Cria uma data respeitando o último dia do mês
    function createDate(year, month, day) {
        // Calcula o último dia disponível no mês
        const lastDay = new Date(year, month + 1, 0).getDate();

        return new Date(year, month, Math.min(day, lastDay));
    }

    // Remove o horário de uma data
    function startOfDay(date) {
        return new Date(date.getFullYear(), date.getMonth(), date.getDate());
    }

    // Formata uma data para o valor aceito pelo input
    function formatDate(date) {
        // Define as partes normalizadas da data
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        return year + '-' + month + '-' + day;
    }

    // Formata um valor para o padrão monetário do formulário
    function formatMoney(value) {
        // Define o valor numérico recebido
        const number = Number(String(value || '0').replace(',', '.'));

        // Verifica se o valor recebido é inválido
        if (Number.isNaN(number)) {
            // Retorna o valor monetário vazio
            return '0,00';
        }

        return number.toFixed(2).replace('.', ',');
    }
})();
