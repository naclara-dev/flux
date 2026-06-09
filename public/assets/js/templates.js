(function () {
    const modal = document.querySelector('[data-template-modal]');
    const form = document.querySelector('[data-template-form]');
    const templateModal = window.FluxModal ? window.FluxModal.create(modal, {
        closeSelector: '[data-close-template-modal]',
        onClose: closeMenus
    }) : null;
    const openButton = document.querySelector('[data-open-template-modal]');
    const editButtons = document.querySelectorAll('[data-edit-template]');
    const modalTitle = document.querySelector('[data-template-modal-title]');
    const findUrl = modal ? modal.dataset.templateFindUrl : '';

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

    const selects = {
        wallet: createSelect('wallet'),
        category: createSelect('category'),
        entity: createSelect('entity'),
        frequency: createSelect('frequency')
    };

    if (!templateModal || !form || !openButton || !modalTitle || !findUrl || Object.values(fields).some(function (field) { return !field; }) || Object.values(selects).some(function (select) { return !select; })) {
        return;
    }

    openButton.addEventListener('click', function () {
        resetForm();
        templateModal.open();
    });

    editButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            fetchTemplate(button.dataset.templateId);
        });
    });

    Object.keys(selects).forEach(function (name) {
        const select = selects[name];

        select.toggle.addEventListener('click', function () {
            if (select.menu.classList.contains('max-h-0')) {
                closeMenus();
                openMenu(select.menu);
                return;
            }

            closeMenu(select.menu);
        });

        select.options.forEach(function (option) {
            option.addEventListener('click', function () {
                setSelected(name, option.dataset.templateOptionId, option.dataset.templateOptionName);
                closeMenu(select.menu);
            });
        });
    });

    document.addEventListener('click', function (event) {
        const clickedInsideSelect = Object.values(selects).some(function (select) {
            return select.menu.contains(event.target) || select.toggle.contains(event.target);
        });

        if (!clickedInsideSelect) {
            closeMenus();
        }
    });

    function resetForm() {
        form.reset();
        fields.id.value = '';
        fields.amount.value = '0,00';
        fields.interval_value.value = '1';
        fields.month_day.value = '1';
        fields.active.checked = true;
        modalTitle.textContent = 'novo template';

        resetSelect('wallet', 'escolha uma wallet');
        resetSelect('category', 'escolha uma categoria');
        resetSelect('entity', 'escolha uma entidade');
        resetSelect('frequency', 'escolha uma frequencia');
        closeMenus();
    }

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

        setSelected('wallet', template.wallet_id);
        setSelected('category', template.category_id);
        setSelected('entity', template.entity_id);
        setSelected('frequency', template.frequency_id);
        closeMenus();
    }

    function fetchTemplate(id) {
        if (!id) {
            return;
        }

        fetch(findUrl + '?id=' + encodeURIComponent(id), {
            headers: {
                'Accept': 'application/json'
            }
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('template not found');
                }

                return response.json();
            })
            .then(function (template) {
                if (!template) {
                    return;
                }

                fillForm(template);
                templateModal.open();
            });
    }

    function createSelect(name) {
        const input = document.querySelector('[data-template-' + name + '-input]');
        const label = document.querySelector('[data-template-' + name + '-label]');
        const toggle = document.querySelector('[data-template-select-toggle="' + name + '"]');
        const menu = document.querySelector('[data-template-select-menu="' + name + '"]');
        const options = document.querySelectorAll('[data-template-option="' + name + '"]');

        if (!input || !label || !toggle || !menu) {
            return null;
        }

        return {
            input: input,
            label: label,
            toggle: toggle,
            menu: menu,
            options: Array.from(options)
        };
    }

    function resetSelect(name, label) {
        selects[name].input.value = '';
        selects[name].label.textContent = label;
    }

    function setSelected(name, id, optionName) {
        const select = selects[name];
        const option = select.options.find(function (item) {
            return item.dataset.templateOptionId === String(id);
        });

        select.input.value = id || '';
        select.label.textContent = optionName || (option ? option.dataset.templateOptionName : 'item selecionado');
    }

    function formatMoney(value) {
        const number = Number(String(value).replace(',', '.'));

        if (Number.isNaN(number)) {
            return '0,00';
        }

        return number.toFixed(2).replace('.', ',');
    }

    function closeMenus() {
        Object.values(selects).forEach(function (select) {
            closeMenu(select.menu);
        });
    }

    function closeMenu(menu) {
        menu.classList.remove('max-h-56', 'border-[var(--yellow)]', 'opacity-100', 'overflow-y-auto');
        menu.classList.add('max-h-0', 'border-transparent', 'opacity-0', 'overflow-hidden');
    }

    function openMenu(menu) {
        menu.classList.remove('max-h-0', 'border-transparent', 'opacity-0', 'overflow-hidden');
        menu.classList.add('max-h-56', 'border-[var(--yellow)]', 'opacity-100', 'overflow-y-auto');
    }
})();
