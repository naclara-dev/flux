(function () {
    const modal = document.querySelector('[data-wallet-modal]');
    const form = document.querySelector('[data-wallet-form]');
    const walletModal = window.FluxModal ? window.FluxModal.create(modal, {
        closeSelector: '[data-close-wallet-modal]',
        onClose: closeTypeMenu
    }) : null;
    const openButton = document.querySelector('[data-open-wallet-modal]');
    const editButtons = document.querySelectorAll('[data-edit-wallet]');
    const idInput = document.querySelector('[data-wallet-id-input]');
    const nameInput = document.querySelector('[data-wallet-name-input]');
    const typeInput = document.querySelector('[data-wallet-type-input]');
    const typeToggle = document.querySelector('[data-wallet-type-toggle]');
    const typeLabel = document.querySelector('[data-wallet-type-label]');
    const typeMenu = document.querySelector('[data-wallet-type-menu]');
    const typeOptions = document.querySelectorAll('[data-wallet-type-option]');
    const balanceInput = document.querySelector('[data-wallet-balance-input]');
    const activeInput = document.querySelector('[data-wallet-active-input]');
    const modalTitle = document.querySelector('[data-wallet-modal-title]');

    if (!walletModal || !form || !openButton || !idInput || !nameInput || !typeInput || !typeToggle || !typeLabel || !typeMenu || !balanceInput || !activeInput || !modalTitle) {
        return;
    }

    openButton.addEventListener('click', function () {
        resetForm();
        walletModal.open();
    });

    editButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            fillForm({
                id: button.dataset.walletId || '',
                name: button.dataset.walletName || '',
                typeId: button.dataset.walletTypeId || '',
                initialBalance: button.dataset.walletInitialBalance || '0.00',
                active: button.dataset.walletActive !== '0'
            });

            walletModal.open();
        });
    });

    typeToggle.addEventListener('click', function () {
        if (typeMenu.classList.contains('max-h-0')) {
            openTypeMenu();
            return;
        }

        closeTypeMenu();
    });

    typeOptions.forEach(function (option) {
        option.addEventListener('click', function () {
            setSelectedType(option.dataset.walletTypeId, option.dataset.walletTypeName);
            closeTypeMenu();
        });
    });

    document.addEventListener('click', function (event) {
        if (!typeMenu.contains(event.target) && !typeToggle.contains(event.target)) {
            closeTypeMenu();
        }
    });

    function resetForm() {
        form.reset();
        idInput.value = '';
        typeInput.value = '';
        typeLabel.textContent = 'escolha um tipo';
        balanceInput.value = '0,00';
        activeInput.checked = true;
        modalTitle.textContent = 'nova wallet';
        closeTypeMenu();
    }

    function fillForm(wallet) {
        idInput.value = wallet.id;
        nameInput.value = wallet.name;
        balanceInput.value = formatBalance(wallet.initialBalance);
        activeInput.checked = wallet.active;
        modalTitle.textContent = 'editar wallet';
        setSelectedType(wallet.typeId);
        closeTypeMenu();
    }

    function setSelectedType(typeId, typeName) {
        const option = findTypeOption(typeId);

        typeInput.value = typeId || '';
        typeLabel.textContent = typeName || (option ? option.dataset.walletTypeName : 'tipo selecionado');
    }

    function findTypeOption(typeId) {
        return Array.from(typeOptions).find(function (option) {
            return option.dataset.walletTypeId === String(typeId);
        });
    }

    function formatBalance(value) {
        const number = Number(String(value).replace(',', '.'));

        if (Number.isNaN(number)) {
            return '0,00';
        }

        return number.toFixed(2).replace('.', ',');
    }

    function closeTypeMenu() {
        if (typeMenu) {
            typeMenu.classList.remove('max-h-56', 'border-[var(--yellow)]', 'opacity-100');
            typeMenu.classList.add('max-h-0', 'border-transparent', 'opacity-0');
        }
    }

    function openTypeMenu() {
        typeMenu.classList.remove('max-h-0', 'border-transparent', 'opacity-0');
        typeMenu.classList.add('max-h-56', 'border-[var(--yellow)]', 'opacity-100');
    }
})();
