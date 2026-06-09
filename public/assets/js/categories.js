(function () {
    const modal = document.querySelector('[data-category-modal]');
    const form = document.querySelector('[data-category-form]');
    const openButton = document.querySelector('[data-open-category-modal]');
    const editButtons = document.querySelectorAll('[data-edit-category]');
    const closeButtons = document.querySelectorAll('[data-close-category-modal]');
    const picker = document.querySelector('[data-icon-picker]');
    const pickerToggle = document.querySelector('[data-toggle-icon-picker]');
    const grid = document.querySelector('[data-icon-grid]');
    const search = document.querySelector('[data-icon-search]');
    const idInput = document.querySelector('[data-category-id-input]');
    const nameInput = document.querySelector('[data-category-name-input]');
    const colorInput = document.querySelector('[data-category-color-input]');
    const iconInput = document.querySelector('[data-category-icon-input]');
    const selectedIconPreview = document.querySelector('[data-selected-icon-preview]');
    const selectedIconLabel = document.querySelector('[data-selected-icon-label]');
    const modalTitle = document.querySelector('[data-category-modal-title]');
    const iconsUrl = modal ? modal.dataset.iconsUrl : '';
    let icons = [];

    if (!modal || !form || !openButton || !picker || !pickerToggle || !grid || !search || !idInput || !nameInput || !colorInput || !iconInput || !selectedIconPreview || !selectedIconLabel || !modalTitle || !iconsUrl) {
        return;
    }

    openButton.addEventListener('click', function () {
        resetForm();
        openModal();
    });

    editButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            fillForm({
                id: button.dataset.categoryId || '',
                name: button.dataset.categoryName || '',
                color: button.dataset.categoryColor || '#c17fd7',
                icon: button.dataset.categoryIcon || ''
            });

            openModal();
        });
    });

    closeButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            closeModal();
        });
    });

    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            closeModal();
        }
    });

    pickerToggle.addEventListener('click', function () {
        picker.classList.toggle('hidden');
        search.focus();
    });

    search.addEventListener('input', function () {
        renderIcons(search.value);
    });

    fetch(iconsUrl)
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            icons = data;
            renderIcons('');

            if (iconInput.value) {
                setSelectedIcon(iconInput.value);
            }
        });

    function openModal() {
        modal.showModal();

        requestAnimationFrame(function () {
            modal.classList.remove('scale-95', 'opacity-0');
            modal.classList.add('scale-100', 'opacity-100');
        });
    }

    function closeModal() {
        modal.classList.remove('scale-100', 'opacity-100');
        modal.classList.add('scale-95', 'opacity-0');

        setTimeout(function () {
            modal.close();
        }, 180);
    }

    function resetForm() {
        form.reset();
        idInput.value = '';
        colorInput.value = '#c17fd7';
        iconInput.value = '';
        modalTitle.textContent = 'nova categoria';
        selectedIconPreview.innerHTML = '<i class="fa-solid fa-icons"></i>';
        selectedIconLabel.textContent = 'escolha um icone';
        picker.classList.add('hidden');
        search.value = '';
        renderIcons('');
    }

    function fillForm(category) {
        idInput.value = category.id;
        nameInput.value = category.name;
        colorInput.value = category.color || '#c17fd7';
        modalTitle.textContent = 'editar categoria';
        picker.classList.add('hidden');
        search.value = '';
        setSelectedIcon(category.icon);
        renderIcons('');
    }

    function renderIcons(filter) {
        const term = String(filter).trim().toLowerCase();
        const visibleIcons = icons.filter(function (icon) {
            return icon.label.toLowerCase().includes(term) || icon.style.toLowerCase().includes(term);
        });

        grid.innerHTML = '';

        visibleIcons.forEach(function (icon) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'flex h-12 items-center justify-center rounded border border-transparent text-lg text-primary transition hover:border-[var(--lilac)] hover:bg-[#fffff5]';
            button.title = icon.label;
            button.setAttribute('aria-label', icon.label);
            button.innerHTML = '<i class="' + icon.style + '"></i>';

            button.addEventListener('click', function () {
                setSelectedIcon(icon.style);
                picker.classList.add('hidden');
            });

            grid.appendChild(button);
        });
    }

    function setSelectedIcon(iconStyle) {
        const icon = icons.find(function (item) {
            return item.style === iconStyle;
        });

        iconInput.value = iconStyle;
        selectedIconPreview.innerHTML = iconStyle ? '<i class="' + iconStyle + '"></i>' : '<i class="fa-solid fa-icons"></i>';
        selectedIconLabel.textContent = icon ? icon.label : 'icone selecionado';
    }
})();
