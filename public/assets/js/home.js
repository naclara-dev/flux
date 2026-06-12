(function () {
    const card = document.querySelector('[data-cycle-card]');
    const previousButton = document.querySelector('[data-cycle-previous]');
    const nextButton = document.querySelector('[data-cycle-next]');
    const refreshButton = document.querySelector('[data-refresh-home]');
    const cache = new Map();

    if (refreshButton) {
        refreshButton.addEventListener('click', function () {
            const icon = refreshButton.querySelector('i');

            if (icon) {
                icon.classList.add('animate-spin');
            }

            window.location.reload();
        });
    }

    if (!card) {
        return;
    }

    const toggle = card.querySelector('[data-cycle-toggle]');
    const content = card.querySelector('[data-cycle-content]');
    const icon = card.querySelector('[data-cycle-icon]');
    const cycleUrl = card.dataset.cycleUrl;
    let previousReference = subtractDay(card.dataset.cycleStart);
    let nextReference = card.dataset.cycleEnd;

    initializeCollapse();

    if (!previousButton || !nextButton || !cycleUrl) {
        return;
    }

    previousButton.addEventListener('click', function () {
        loadCycle(previousReference);
    });

    nextButton.addEventListener('click', function () {
        loadCycle(nextReference);
    });

    function loadCycle(referenceDate) {
        if (!referenceDate) {
            return;
        }

        setLoading(true);

        if (cache.has(referenceDate)) {
            renderCycle(cache.get(referenceDate));
            setLoading(false);
            return;
        }

        fetch(cycleUrl + '?date=' + encodeURIComponent(referenceDate), {
            headers: {
                'Accept': 'application/json'
            }
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('cycle not found');
                }

                return response.json();
            })
            .then(function (cycle) {
                rememberCycle(referenceDate, cycle);
                renderCycle(cycle);
            })
            .catch(function () {
                window.location.reload();
            })
            .finally(function () {
                setLoading(false);
            });
    }

    function renderCycle(cycle) {
        card.dataset.cycleStart = cycle.start;
        card.dataset.cycleEnd = cycle.end;
        previousReference = cycle.previousReference;
        nextReference = cycle.nextReference;

        setText('[data-cycle-start-day]', cycle.startDayLabel);
        setText('[data-cycle-label]', cycle.label);
        setText('[data-cycle-description]', cycle.description);
        setText('[data-cycle-income]', cycle.incomeLabel);
        setText('[data-cycle-expenses]', cycle.expensesLabel);
        setText('[data-cycle-balance]', cycle.balanceLabel);
        setText('[data-viewed-cycle-balance]', cycle.balanceLabel);
        setText('[data-viewed-cycle-progress-label]', cycle.label);
        setText('[data-viewed-cycle-progress-value]', cycle.progress + '%');

        const progressBar = document.querySelector('[data-viewed-cycle-progress-bar]');

        if (progressBar) {
            progressBar.style.width = cycle.progress + '%';
        }

        // Atualiza as transações e os resumos do ciclo selecionado
        renderTransactions(cycle.transactions || []);
        renderSummary('[data-entity-summary]', cycle.entitySummary || []);
        renderSummary('[data-category-summary]', cycle.categorySummary || []);

        // Verifica se o conteúdo do ciclo está expandido
        if (toggle && toggle.getAttribute('aria-expanded') === 'true') {
            // Atualiza a altura do conteúdo após a troca de ciclo
            requestAnimationFrame(function () {
                content.style.maxHeight = content.scrollHeight + 'px';
            });
        }
    }

    // Renderiza o resumo de despesas agrupadas
    function renderSummary(selector, items) {
        // Carrega o container correspondente ao tipo de resumo
        const container = document.querySelector(selector);

        // Verifica se o container existe na tela
        if (!container) {
            // Interrompe a renderização quando o container não existe
            return;
        }

        // Limpa os dados do ciclo exibido anteriormente
        container.replaceChildren();

        // Verifica se o ciclo não possui despesas agrupadas
        if (items.length === 0) {
            // Inicializa a mensagem de estado vazio
            const empty = document.createElement('p');
            empty.className = 'text-sm text-secondary';
            empty.textContent = 'nenhuma saída neste ciclo.';

            // Salva a mensagem no container do resumo
            container.appendChild(empty);
            return;
        }

        // Percorre os grupos de despesas do ciclo
        items.forEach(function (item) {
            // Inicializa a linha do resumo
            const wrapper = document.createElement('div');

            // Inicializa a linha com o nome e o valor do grupo
            const row = document.createElement('div');
            row.className = 'flex items-center justify-between gap-4 text-sm';

            // Define o nome do grupo
            const label = document.createElement('span');
            label.className = 'truncate';
            label.textContent = item.label;

            // Define o valor total do grupo
            const amount = document.createElement('strong');
            amount.textContent = item.amountLabel;

            // Inicializa o fundo da barra percentual
            const track = document.createElement('div');
            track.className = 'mt-1.5 h-1.5 overflow-hidden rounded-full bg-[var(--yellow)]';

            // Define o preenchimento percentual da barra
            const fill = document.createElement('div');
            fill.className = 'h-full rounded-full bg-[var(--lilac)] transition-all duration-300';
            fill.style.width = item.percentage + '%';

            // Salva os elementos da linha no container
            row.append(label, amount);
            track.appendChild(fill);
            wrapper.append(row, track);
            container.appendChild(wrapper);
        });
    }

    function renderTransactions(transactions) {
        content.replaceChildren();

        if (transactions.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'rounded border border-[var(--yellow)] bg-[var(--light)] p-4 text-sm text-secondary';
            empty.textContent = 'nenhum lancamento neste ciclo.';
            content.appendChild(empty);
            return;
        }

        transactions.forEach(function (transaction) {
            content.appendChild(createTransactionButton(transaction));
        });
    }

    function createTransactionButton(transaction) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'grid w-full gap-3 rounded border border-[var(--yellow)] bg-[var(--light)] p-3 text-left transition hover:-translate-y-0.5 hover:shadow-md sm:grid-cols-[1fr_auto] sm:items-center';
        button.setAttribute('data-edit-transaction', '');

        if (transaction.paid) {
            button.classList.add('opacity-80');
        }

        setTransactionData(button, transaction);

        const identity = document.createElement('div');
        identity.className = 'flex items-center gap-3';

        const categoryIcon = document.createElement('div');
        categoryIcon.className = 'flex h-10 w-10 items-center justify-center rounded-full text-light';
        categoryIcon.style.backgroundColor = transaction.category.color;

        const iconElement = document.createElement('i');
        String(transaction.category.icon || 'fa-solid fa-tag').split(' ').forEach(function (className) {
            if (className) {
                iconElement.classList.add(className);
            }
        });
        categoryIcon.appendChild(iconElement);

        const text = document.createElement('div');
        const title = document.createElement('h3');
        title.className = 'font-medium';
        title.textContent = transaction.title;

        const meta = document.createElement('p');
        meta.className = 'text-xs text-secondary';
        meta.textContent = transaction.meta_label;
        text.append(title, meta);
        identity.append(categoryIcon, text);

        const summary = document.createElement('div');
        summary.className = 'flex items-center justify-between gap-4 sm:block sm:text-right';

        const date = document.createElement('span');
        date.className = 'text-xs text-secondary';
        date.textContent = transaction.date_label;

        const amount = document.createElement('strong');
        amount.className = 'block text-sm';
        amount.classList.add(Number(transaction.amount) > 0 ? 'text-primary' : 'text-[var(--dark)]');
        amount.textContent = transaction.amount_label;

        const status = document.createElement('span');
        status.className = 'badge';
        status.textContent = transaction.status_label;
        summary.append(date, amount, status);

        button.append(identity, summary);

        return button;
    }

    function setTransactionData(button, transaction) {
        button.dataset.transactionId = transaction.id || '';
        button.dataset.transactionTitle = transaction.title || '';
        button.dataset.transactionAmount = transaction.form_amount_label || '0,00';
        button.dataset.transactionOccurrenceDate = transaction.occurrence_date || '';
        button.dataset.transactionDueDate = transaction.due_date || '';
        button.dataset.transactionPaidAt = transaction.paid_at || '';
        button.dataset.transactionPaid = String(transaction.paid || 0);
        button.dataset.transactionWalletId = transaction.wallet.id || '';
        button.dataset.transactionWalletName = transaction.wallet.name || '';
        button.dataset.transactionCategoryId = transaction.category.id || '';
        button.dataset.transactionCategoryName = transaction.category.name || '';
        button.dataset.transactionEntityId = transaction.entity.id || '';
        button.dataset.transactionEntityName = transaction.entity.name || '';
        button.dataset.transactionTemplateId = transaction.template.id || '';
        button.dataset.transactionTemplateName = transaction.template.title || '';
        button.dataset.transactionPaymentMethodId = transaction.payment_method.id || '';
        button.dataset.transactionPaymentMethodName = transaction.payment_method.name || '';
    }

    function initializeCollapse() {
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
    }

    function setText(selector, value) {
        const element = document.querySelector(selector);

        if (element) {
            element.textContent = value;
        }
    }

    function setLoading(isLoading) {
        previousButton.disabled = isLoading;
        nextButton.disabled = isLoading;
        card.classList.toggle('opacity-60', isLoading);
    }

    function rememberCycle(referenceDate, cycle) {
        if (cache.size >= 3) {
            cache.delete(cache.keys().next().value);
        }

        cache.set(referenceDate, cycle);
    }

    function subtractDay(date) {
        if (!date) {
            return '';
        }

        const value = new Date(date + 'T12:00:00');
        value.setDate(value.getDate() - 1);

        return [
            value.getFullYear(),
            String(value.getMonth() + 1).padStart(2, '0'),
            String(value.getDate()).padStart(2, '0')
        ].join('-');
    }
})();
