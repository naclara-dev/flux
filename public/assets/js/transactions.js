(function () {
    const modal = document.querySelector('[data-transaction-modal]');
    const form = document.querySelector('[data-transaction-form]');
    const transactionModal = window.FluxModal ? window.FluxModal.create(modal, {
        closeSelector: '[data-close-transaction-modal]',
        onClose: closeMenus
    }) : null;
    const openButton = document.querySelector('[data-open-transaction-modal]');
    const paidInput = document.querySelector('[data-transaction-paid-input]');

    const selects = {
        wallet: createSelect('wallet'),
        category: createSelect('category'),
        entity: createSelect('entity'),
        template: createSelect('template'),
        'payment-method': createSelect('payment-method')
    };

    if (!transactionModal || !form || !openButton || !paidInput || Object.values(selects).some(function (select) { return !select; })) {
        return;
    }

    openButton.addEventListener('click', function () {
        resetForm();
        transactionModal.open();
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
                setSelected(name, option.dataset.transactionOptionId, option.dataset.transactionOptionName);

                if (name === 'template') {
                    applyTemplate(option);
                }

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
        form.elements.amount.value = '0,00';
        form.elements.occurrence_date.value = new Date().toISOString().slice(0, 10);
        paidInput.checked = false;

        resetSelect('wallet', 'escolha uma wallet');
        resetSelect('category', 'escolha uma categoria');
        resetSelect('entity', 'escolha uma entidade');
        resetSelect('template', 'sem template');
        resetSelect('payment-method', 'escolha uma forma');
        closeMenus();
    }

    function createSelect(name) {
        const input = document.querySelector('[data-transaction-' + name + '-input]');
        const label = document.querySelector('[data-transaction-' + name + '-label]');
        const toggle = document.querySelector('[data-transaction-select-toggle="' + name + '"]');
        const menu = document.querySelector('[data-transaction-select-menu="' + name + '"]');
        const options = document.querySelectorAll('[data-transaction-option="' + name + '"]');

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

        select.input.value = id || '';
        select.label.textContent = optionName || 'item selecionado';
    }

    function applyTemplate(option) {
        form.elements.title.value = option.dataset.transactionOptionName || '';
        form.elements.amount.value = formatMoney(option.dataset.templateAmount);
        setSelected('wallet', option.dataset.templateWalletId, option.dataset.templateWalletName);
        setSelected('category', option.dataset.templateCategoryId, option.dataset.templateCategoryName);
        setSelected('entity', option.dataset.templateEntityId, option.dataset.templateEntityName);        
    }

    function formatMoney(value) {
        const number = Number(String(value || '0').replace(',', '.'));

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
