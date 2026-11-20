(() => {
    const closeOrResetDialog = (dialog) => {
        if (!dialog) return;
        if (dialog.dataset.autoOpen === 'true' && dialog.dataset.resetUrl) {
            window.location.replace(dialog.dataset.resetUrl);
            return;
        }
        dialog.close();
    };

    document.querySelectorAll('[data-open-dialog]').forEach((button) => {
        button.addEventListener('click', () => {
            const dialog = document.getElementById(button.dataset.openDialog);
            if (dialog?.showModal) dialog.showModal();
        });
    });

    document.querySelectorAll('[data-close-dialog]').forEach((button) => {
        button.addEventListener('click', () => {
            closeOrResetDialog(button.closest('dialog'));
        });
    });

    document.querySelectorAll('.admin-modal[data-auto-open="true"]').forEach((dialog) => {
        if (dialog.showModal) dialog.showModal();
        dialog.addEventListener('cancel', (event) => {
            event.preventDefault();
            closeOrResetDialog(dialog);
        });
    });

    document.querySelectorAll('[data-table-search]').forEach((input) => {
        const tableId = input.dataset.tableSearch;
        const table = document.getElementById(tableId);
        if (!table) return;

        const rows = Array.from(table.querySelectorAll('tbody tr'));
        const count = document.querySelector(`[data-search-count="${tableId}"]`);
        const emptyMessage = document.querySelector(`[data-search-empty="${tableId}"]`);
        const labels = tableId === 'items-table'
            ? { singular: 'produs', plural: 'produse' }
            : { singular: 'categorie', plural: 'categorii' };

        const normalize = (value) => value
            .toLocaleLowerCase('ro')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();

        const filterRows = () => {
            const query = normalize(input.value);
            let visible = 0;

            rows.forEach((row) => {
                const matches = normalize(row.textContent).includes(query);
                row.hidden = !matches;
                if (matches) visible += 1;
            });

            if (count) {
                count.textContent = query
                    ? `${visible} din ${rows.length} ${labels.plural}`
                    : `${rows.length} ${rows.length === 1 ? labels.singular : labels.plural}`;
            }
            if (emptyMessage) emptyMessage.hidden = query === '' || visible > 0;
        };

        input.addEventListener('input', filterRows);
    });
})();
