(function () {
    function create(modal, options) {
        const settings = Object.assign({
            closeSelector: '[data-close-modal]',
            onClose: null
        }, options || {});

        if (!modal) {
            return null;
        }

        modal.querySelectorAll(settings.closeSelector).forEach(function (button) {
            button.addEventListener('click', function () {
                close();
            });
        });

        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                close();
            }
        });

        function open() {
            modal.showModal();

            requestAnimationFrame(function () {
                modal.classList.remove('scale-95', 'opacity-0');
                modal.classList.add('scale-100', 'opacity-100');
            });
        }

        function close() {
            modal.classList.remove('scale-100', 'opacity-100');
            modal.classList.add('scale-95', 'opacity-0');

            setTimeout(function () {
                modal.close();

                if (typeof settings.onClose === 'function') {
                    settings.onClose();
                }
            }, 180);
        }

        return {
            open: open,
            close: close
        };
    }

    window.FluxModal = {
        create: create
    };
})();
