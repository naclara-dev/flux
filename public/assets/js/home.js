(function () {
    const cards = document.querySelectorAll('[data-cycle-card]');
    const refreshButton = document.querySelector('[data-refresh-home]');

    if (refreshButton) {
        refreshButton.addEventListener('click', function () {
            const icon = refreshButton.querySelector('i');

            if (icon) {
                icon.classList.add('animate-spin');
            }

            window.location.reload();
        });
    }

    cards.forEach(function (card) {
        const toggle = card.querySelector('[data-cycle-toggle]');
        const content = card.querySelector('[data-cycle-content]');
        const icon = card.querySelector('[data-cycle-icon]');

        if (!toggle || !content || !icon) {
            return;
        }

        content.style.overflow = 'hidden';
        content.style.maxHeight = content.scrollHeight + 'px';

        toggle.addEventListener('click', function () {
            const isExpanded = toggle.getAttribute('aria-expanded') === 'true';

            toggle.setAttribute('aria-expanded', isExpanded ? 'false' : 'true');
            content.style.maxHeight = isExpanded ? '0px' : content.scrollHeight + 'px';
            content.classList.toggle('mt-5', !isExpanded);
            icon.classList.toggle('rotate-180', isExpanded);
        });

        window.addEventListener('resize', function () {
            if (toggle.getAttribute('aria-expanded') === 'true') {
                content.style.maxHeight = content.scrollHeight + 'px';
            }
        });
    });
})();
