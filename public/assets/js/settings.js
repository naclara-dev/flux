(function () {
    const modal = document.querySelector('[data-settings-modal]');
    const settingsModal = window.FluxModal ? window.FluxModal.create(modal, {
        closeSelector: '[data-close-settings-modal]',
        onClose: closeAllMenus
    }) : null;
    const openButton = document.querySelector('[data-open-settings-modal]');
    const selectNames = ['payment-method', 'wallet', 'entity'];

    if (!settingsModal || !openButton) {
        return;
    }

    openButton.addEventListener('click', function () {
        syncSelectedLabels();
        settingsModal.open();
    });

    selectNames.forEach(function (name) {
        const toggle = getToggle(name);
        const menu = getMenu(name);

        if (!toggle || !menu) {
            return;
        }

        toggle.addEventListener('click', function () {
            if (menu.classList.contains('max-h-0')) {
                openMenu(name);
                return;
            }

            closeMenu(name);
        });

        getOptions(name).forEach(function (option) {
            option.addEventListener('click', function () {
                setSelectedOption(name, option.dataset.settingsOptionId, option.dataset.settingsOptionName);
                closeMenu(name);
            });
        });
    });

    document.addEventListener('click', function (event) {
        selectNames.forEach(function (name) {
            const toggle = getToggle(name);
            const menu = getMenu(name);

            if (!toggle || !menu) {
                return;
            }

            if (!toggle.contains(event.target) && !menu.contains(event.target)) {
                closeMenu(name);
            }
        });
    });

    syncSelectedLabels();

    function syncSelectedLabels() {
        selectNames.forEach(function (name) {
            const input = getInput(name);
            const option = findOption(name, input ? input.value : '');

            if (option) {
                setSelectedOption(name, option.dataset.settingsOptionId, option.dataset.settingsOptionName);
            }
        });
    }

    function setSelectedOption(name, id, label) {
        const input = getInput(name);
        const labelElement = getLabel(name);

        if (input) {
            input.value = id || '';
        }

        if (labelElement && label) {
            labelElement.textContent = label;
        }
    }

    function findOption(name, id) {
        return getOptions(name).find(function (option) {
            return option.dataset.settingsOptionId === String(id);
        });
    }

    function openMenu(name) {
        closeAllMenus(name);

        const menu = getMenu(name);

        if (!menu) {
            return;
        }

        menu.classList.remove('max-h-0', 'border-transparent', 'opacity-0', 'overflow-hidden');
        menu.classList.add('max-h-48', 'border-[var(--yellow)]', 'opacity-100', 'overflow-y-auto');
    }

    function closeMenu(name) {
        const menu = getMenu(name);

        if (!menu) {
            return;
        }

        menu.classList.remove('max-h-48', 'border-[var(--yellow)]', 'opacity-100', 'overflow-y-auto');
        menu.classList.add('max-h-0', 'border-transparent', 'opacity-0', 'overflow-hidden');
    }

    function closeAllMenus(except) {
        selectNames.forEach(function (name) {
            if (name !== except) {
                closeMenu(name);
            }
        });
    }

    function getInput(name) {
        return document.querySelector('[data-settings-select-input="' + name + '"]');
    }

    function getToggle(name) {
        return document.querySelector('[data-settings-select-toggle="' + name + '"]');
    }

    function getLabel(name) {
        return document.querySelector('[data-settings-select-label="' + name + '"]');
    }

    function getMenu(name) {
        return document.querySelector('[data-settings-select-menu="' + name + '"]');
    }

    function getOptions(name) {
        return Array.from(document.querySelectorAll('[data-settings-select-option="' + name + '"]'));
    }
})();
