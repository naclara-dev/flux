document.addEventListener('DOMContentLoaded', () => {
    // Menu
    const tabMenu = document.querySelector('.tab-menu');  
    // Itens do menu   
    const tabItems = document.querySelectorAll('.tab-item');
    // Container do menu
    const stage = document.querySelector('.tab-stage');
    // Armazena a tab ativa
    let activeTab = null;
    let activeTabObserver = null;

    // Obtem o formulario alvo de cada item.
    const tabs = Array.from(tabItems)
        .map((tab) => document.getElementById(tab.dataset.tabTarget))
        .filter(Boolean);

    // Atualiza a altura do container dos formulários.
    const updateStageHeight = (tab) => {
        if (!stage || !tab) {
            return;
        }

        stage.style.height = `${tab.offsetHeight}px`;
    };

    const watchTabHeight = (tab) => {
        if (activeTabObserver) {
            activeTabObserver.disconnect();
        }

        if (!window.ResizeObserver || !tab) {
            return;
        }

        activeTabObserver = new ResizeObserver(() => {
            updateStageHeight(tab);
        });

        activeTabObserver.observe(tab);
    };

    const setActiveTab = (targetId) => {
        // Busca o formulario que deve ficar visivel.
        const nextTab = document.getElementById(targetId);

        if (!nextTab) {
            return;
        }

        tabItems.forEach((tab) => {
            // Marca visualmente o item selecionado e atualiza acessibilidade.
            const isActive = tab.dataset.tabTarget === targetId;

            tab.classList.toggle('active', isActive);
            tab.setAttribute('aria-selected', String(isActive));
        });

        // Atualiza a posicao do indicador lilas que desliza entre as tabs.
        const activeIndex = Array.from(tabItems).findIndex((tab) => tab.dataset.tabTarget === targetId);
        tabMenu.style.setProperty('--tab-index', String(activeIndex >= 0 ? activeIndex : 0));

        const nextIndex = activeIndex >= 0 ? activeIndex : 0;

        tabs.forEach((tab) => {
            const tabIndex = Array.from(tabItems).findIndex((item) => item.dataset.tabTarget === tab.id);
            const isActive = tab.id === targetId;

            tab.hidden = false;
            tab.classList.toggle('is-active', isActive);
            tab.classList.toggle('is-before', tabIndex < nextIndex);
            tab.classList.toggle('is-after', tabIndex > nextIndex);
            tab.setAttribute('aria-hidden', String(!isActive));
        });

        updateStageHeight(nextTab);
        watchTabHeight(nextTab);
        activeTab = targetId;
    };

    tabItems.forEach((tab) => {
        tab.addEventListener('click', (event) => {
            // Evita navegar para "#" e faz a troca via JS.
            event.preventDefault();
            setActiveTab(tab.dataset.tabTarget);
        });
    });

    // Garante que a tela abra mostrando o formulario de login.
    setActiveTab('login-form');

    window.addEventListener('resize', () => {
        updateStageHeight(document.getElementById(activeTab));
    });

    window.addEventListener('auth-tab-height-change', () => {
        requestAnimationFrame(() => {
            updateStageHeight(document.getElementById(activeTab));
        });
    });
});
