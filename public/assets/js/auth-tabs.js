document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('.pill');
    const tabsContainer = document.querySelector('.pills');

    // Resolve os formularios a partir do id apontado por cada pill.
    const panels = Array.from(tabs)
        .map((tab) => document.getElementById(tab.dataset.authTarget))
        .filter(Boolean);

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

        panels.forEach((panel) => {
            // Mantem apenas o formulario alvo visivel.
            panel.hidden = panel.id !== targetId;
        });

        // Atualiza a posicao do indicador lilas que desliza entre as pills.
        const activeIndex = Array.from(tabs).findIndex((tab) => tab.dataset.authTarget === targetId);
        tabsContainer.style.setProperty('--pill-index', String(activeIndex >= 0 ? activeIndex : 0));
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
});
