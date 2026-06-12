(function () {
    // Carrega os elementos principais do formulário de template
    const modal = document.querySelector('[data-template-modal]');
    const form = document.querySelector('[data-template-form]');
    const openButton = document.querySelector('.modal-toggle[data-modal-target="#template-modal"]');
    const editButtons = document.querySelectorAll('[data-edit-template]');
    const modalTitle = document.querySelector('[data-template-modal-title]');

    // Carrega o controlador compartilhado do modal
    const templateModal = window.FluxModal ? window.FluxModal.get(modal) : null;

    // Define a URL usada para carregar os dados da edição
    const findUrl = modal ? modal.dataset.templateFindUrl : '';

    // Carrega os campos simples do formulário
    const fields = {
        id: document.querySelector('[data-template-id-input]'),
        title: document.querySelector('[data-template-title-input]'),
        amount: document.querySelector('[data-template-amount-input]'),
        interval_value: document.querySelector('[data-template-interval-input]'),
        month_day: document.querySelector('[data-template-month-day-input]'),
        start_date: document.querySelector('[data-template-start-date-input]'),
        end_date: document.querySelector('[data-template-end-date-input]'),
        next_run_date: document.querySelector('[data-template-next-run-date-input]'),
        active: document.querySelector('[data-template-active-input]')
    };

    // Carrega os comboboxes compartilhados do formulário
    const selects = {
        wallet: getSelect('wallet'),
        category: getSelect('category'),
        entity: getSelect('entity'),
        frequency: getSelect('frequency')
    };

    // Verifica se a estrutura obrigatória está disponível
    if (!templateModal || !form || !openButton || !modalTitle || !findUrl || Object.values(fields).some(function (field) {
        return !field;
    }) || Object.values(selects).some(function (select) {
        return !select;
    })) {
        // Interrompe a inicialização quando a tela está incompleta
        return;
    }

    // Prepara o formulário antes de abrir um novo template
    openButton.addEventListener('click', function () {
        resetForm();
    });

    // Percorre os botões de edição existentes na página
    editButtons.forEach(function (button) {
        // Carrega o template correspondente ao botão
        button.addEventListener('click', function () {
            fetchTemplate(button.dataset.templateId);
        });
    });

    // Restaura os valores iniciais do formulário
    function resetForm() {
        form.reset();
        fields.id.value = '';
        fields.amount.value = '0,00';
        fields.interval_value.value = '1';
        fields.month_day.value = '1';
        fields.active.checked = true;
        modalTitle.textContent = 'novo template';

        // Percorre os comboboxes para restaurar seus placeholders
        Object.values(selects).forEach(function (select) {
            select.reset();
        });
    }

    // Preenche o formulário com os dados do template
    function fillForm(template) {
        fields.id.value = template.id;
        fields.title.value = template.title;
        fields.amount.value = formatMoney(template.amount);
        fields.interval_value.value = template.interval_value || 1;
        fields.month_day.value = template.month_day || 1;
        fields.start_date.value = template.start_date || '';
        fields.end_date.value = template.end_date || '';
        fields.next_run_date.value = template.next_run_date || '';
        fields.active.checked = template.active === true || template.active === 1 || template.active === '1';
        modalTitle.textContent = 'editar template';

        // Define os relacionamentos selecionados sem emitir eventos de usuário
        selects.wallet.set(template.wallet_id, '', false);
        selects.category.set(template.category_id, '', false);
        selects.entity.set(template.entity_id, '', false);
        selects.frequency.set(template.frequency_id, '', false);
    }

    // Carrega um template pelo endpoint JSON
    function fetchTemplate(id) {
        // Verifica se o identificador foi informado
        if (!id) {
            // Interrompe a busca sem identificador
            return;
        }

        fetch(findUrl + '?id=' + encodeURIComponent(id), {
            headers: {
                'Accept': 'application/json'
            }
        })
            .then(function (response) {
                // Verifica se a resposta foi concluída com sucesso
                if (!response.ok) {
                    throw new Error('template not found');
                }

                return response.json();
            })
            .then(function (template) {
                // Verifica se o template foi encontrado
                if (!template) {
                    // Interrompe o preenchimento sem dados
                    return;
                }

                fillForm(template);
                templateModal.open();
            });
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

    // Formata um valor para o padrão monetário do formulário
    function formatMoney(value) {
        // Define o valor numérico recebido do backend
        const number = Number(String(value).replace(',', '.'));

        // Verifica se o valor recebido é inválido
        if (Number.isNaN(number)) {
            // Retorna o valor monetário vazio
            return '0,00';
        }

        return number.toFixed(2).replace('.', ',');
    }
})();
