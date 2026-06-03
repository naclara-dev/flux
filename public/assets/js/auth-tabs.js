document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('.pill');
    const tabsContainer = document.querySelector('.pills');
    const stage = document.querySelector('.auth-form-stage');
    let activePanelId = null;
    let activePanelObserver = null;

    // Resolve os formularios a partir do id apontado por cada pill.
    const panels = Array.from(tabs)
        .map((tab) => document.getElementById(tab.dataset.authTarget))
        .filter(Boolean);

    const updateStageHeight = (panel) => {
        if (!stage || !panel) {
            return;
        }

        stage.style.height = `${panel.offsetHeight}px`;
    };

    const watchPanelHeight = (panel) => {
        if (activePanelObserver) {
            activePanelObserver.disconnect();
        }

        if (!window.ResizeObserver || !panel) {
            return;
        }

        activePanelObserver = new ResizeObserver(() => {
            updateStageHeight(panel);
        });

        activePanelObserver.observe(panel);
    };

    const setActiveTab = (targetId) => {
        // Busca o formulario que deve ficar visivel.
        const nextPanel = document.getElementById(targetId);

        if (!nextPanel) {
            return;
        }

        tabs.forEach((tab) => {
            // Marca visualmente a pill selecionada e atualiza acessibilidade.
            const isActive = tab.dataset.authTarget === targetId;

            tab.classList.toggle('active', isActive);
            tab.setAttribute('aria-selected', String(isActive));
        });

        // Atualiza a posicao do indicador lilas que desliza entre as pills.
        const activeIndex = Array.from(tabs).findIndex((tab) => tab.dataset.authTarget === targetId);
        tabsContainer.style.setProperty('--pill-index', String(activeIndex >= 0 ? activeIndex : 0));

        const nextIndex = activeIndex >= 0 ? activeIndex : 0;

        panels.forEach((panel) => {
            const panelIndex = Array.from(tabs).findIndex((tab) => tab.dataset.authTarget === panel.id);
            const isActive = panel.id === targetId;

            panel.hidden = false;
            panel.classList.toggle('is-active', isActive);
            panel.classList.toggle('is-before', panelIndex < nextIndex);
            panel.classList.toggle('is-after', panelIndex > nextIndex);
            panel.setAttribute('aria-hidden', String(!isActive));
        });

        updateStageHeight(nextPanel);
        watchPanelHeight(nextPanel);
        activePanelId = targetId;
    };

    tabs.forEach((tab) => {
        tab.addEventListener('click', (event) => {
            // Evita navegar para "#" e faz a troca via JS.
            event.preventDefault();
            setActiveTab(tab.dataset.authTarget);
        });
    });

    // Garante que a tela abra mostrando o formulario de login.
    setActiveTab('login-form');

    window.addEventListener('resize', () => {
        updateStageHeight(document.getElementById(activePanelId));
    });

    window.addEventListener('auth-panel-height-change', () => {
        requestAnimationFrame(() => {
            updateStageHeight(document.getElementById(activePanelId));
        });
    });
});
