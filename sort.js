function makeSortable(tableId, skipLastColumns = 1) {
    const table = document.getElementById(tableId);
    if (!table) return;

    const headers = table.querySelectorAll('thead th');
    const sortState = {};

    headers.forEach((th, colIndex) => {
        if (colIndex >= headers.length - skipLastColumns) return;

        th.classList.add('sortable-header');
        th.setAttribute('aria-sort', 'none');

        const indicator = document.createElement('span');
        indicator.className = 'sort-indicator';
        indicator.textContent = '↕';
        th.appendChild(indicator);

        th.addEventListener('click', () => {
            const asc = sortState[colIndex] !== true;
            sortState[colIndex] = asc;

            // Reset all headers
            headers.forEach((h, i) => {
                if (i >= headers.length - skipLastColumns) return;
                h.classList.remove('sort-asc', 'sort-desc');
                h.setAttribute('aria-sort', 'none');
                h.querySelector('.sort-indicator').textContent = '↕';
            });

            th.classList.add(asc ? 'sort-asc' : 'sort-desc');
            th.setAttribute('aria-sort', asc ? 'ascending' : 'descending');
            indicator.textContent = asc ? '↑' : '↓';

            sortTableByColumn(table, colIndex, asc);
        });
    });
}

function parseCellValue(text) {
    const trimmed = text.trim();

    // Finnish date DD.MM.YYYY
    const finnishDate = trimmed.match(/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/);
    if (finnishDate) {
        return { type: 'date', value: new Date(finnishDate[3], finnishDate[2] - 1, finnishDate[1]) };
    }

    // ISO date YYYY-MM-DD
    const isoDate = trimmed.match(/^\d{4}-\d{2}-\d{2}/);
    if (isoDate) {
        return { type: 'date', value: new Date(trimmed) };
    }

    // Number: strip €, %, spaces, commas as thousands separator
    const numeric = trimmed.replace(/[€%\s]/g, '').replace(/,/g, '.');
    if (numeric !== '' && !isNaN(Number(numeric))) {
        return { type: 'number', value: Number(numeric) };
    }

    return { type: 'string', value: trimmed.toLowerCase() };
}

function sortTableByColumn(table, colIndex, asc) {
    const tbody = table.querySelector('tbody');
    if (!tbody) return;

    const rows = Array.from(tbody.querySelectorAll('tr'));

    rows.sort((a, b) => {
        const cellA = a.cells[colIndex] ? a.cells[colIndex].textContent : '';
        const cellB = b.cells[colIndex] ? b.cells[colIndex].textContent : '';

        const valA = parseCellValue(cellA);
        const valB = parseCellValue(cellB);

        let cmp = 0;
        if (valA.type === 'date' && valB.type === 'date') {
            cmp = valA.value - valB.value;
        } else if (valA.type === 'number' && valB.type === 'number') {
            cmp = valA.value - valB.value;
        } else {
            cmp = String(valA.value).localeCompare(String(valB.value), 'fi');
        }

        return asc ? cmp : -cmp;
    });

    rows.forEach(row => tbody.appendChild(row));
}
