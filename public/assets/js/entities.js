(function () {
    const modal = document.querySelector('[data-entity-modal]');
    const form = document.querySelector('[data-entity-form]');
    const entityModal = window.FluxModal ? window.FluxModal.create(modal, {
        closeSelector: '[data-close-entity-modal]',
        onClose: closeTypeMenu
    }) : null;
    const openButton = document.querySelector('[data-open-entity-modal]');
    const editButtons = document.querySelectorAll('[data-edit-entity]');
    const idInput = document.querySelector('[data-entity-id-input]');
    const nameInput = document.querySelector('[data-entity-name-input]');
    const typeInput = document.querySelector('[data-entity-type-input]');
    const typeToggle = document.querySelector('[data-entity-type-toggle]');
    const typeLabel = document.querySelector('[data-entity-type-label]');
    const typeMenu = document.querySelector('[data-entity-type-menu]');
    const typeOptions = document.querySelectorAll('[data-entity-type-option]');
    const modalTitle = document.querySelector('[data-entity-modal-title]');
    const findUrl = modal ? modal.dataset.entityFindUrl : '';

    if (!entityModal || !form || !openButton || !idInput || !nameInput || !typeInput || !typeToggle || !typeLabel || !typeMenu || !modalTitle || !findUrl) {
        return;
    }

    openButton.addEventListener('click', function () {
        resetForm();
        entityModal.open();
    });

    editButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            fetchEntity(button.dataset.entityId);
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
            setSelectedType(option.dataset.entityTypeId, option.dataset.entityTypeName);
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
        modalTitle.textContent = 'nova entidade';
        closeTypeMenu();
    }

    function fillForm(entity) {
        idInput.value = entity.id;
        nameInput.value = entity.name;
        modalTitle.textContent = 'editar entidade';
        setSelectedType(entity.type_id);
        closeTypeMenu();
    }

    function fetchEntity(id) {
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
                    throw new Error('entity not found');
                }

                return response.json();
            })
            .then(function (entity) {
                if (!entity) {
                    return;
                }

                fillForm(entity);
                entityModal.open();
            });
    }

    function setSelectedType(typeId, typeName) {
        const option = findTypeOption(typeId);

        typeInput.value = typeId || '';
        typeLabel.textContent = typeName || (option ? option.dataset.entityTypeName : 'tipo selecionado');
    }

    function findTypeOption(typeId) {
        return Array.from(typeOptions).find(function (option) {
            return option.dataset.entityTypeId === String(typeId);
        });
    }

    function closeTypeMenu() {
        typeMenu.classList.remove('max-h-56', 'border-[var(--yellow)]', 'opacity-100', 'overflow-y-auto');
        typeMenu.classList.add('max-h-0', 'border-transparent', 'opacity-0', 'overflow-hidden');
    }

    function openTypeMenu() {
        typeMenu.classList.remove('max-h-0', 'border-transparent', 'opacity-0', 'overflow-hidden');
        typeMenu.classList.add('max-h-56', 'border-[var(--yellow)]', 'opacity-100', 'overflow-y-auto');
    }
})();
