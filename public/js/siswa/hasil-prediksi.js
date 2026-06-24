(function () {
    'use strict';

    const SELECTORS = {
        scoreBar: '.score-bar-fill[data-score]',
        filterForm: '.filter-form',
        filterSelect: '.filter-select',
        rowsJson: 'hasilPrediksiRows',
        tableWrap: 'tabelWrap',
        tableBody: 'tabelBody',
        paginationInfo: 'paginfoText',
        pageIndicator: 'pageIndicator',
        prevButton: 'btnPrev',
        nextButton: 'btnNext'
    };

    const DEFAULT_PER_PAGE = 10;
    const MIN_PERCENT = 0;
    const MAX_PERCENT = 100;

    let currentPage = 1;
    let totalPages = 1;
    let allRows = [];
    let perPage = DEFAULT_PER_PAGE;

    function getElement(id) {
        return document.getElementById(id);
    }

    function clamp(value, min, max) {
        return Math.min(Math.max(value, min), max);
    }

    function createElement(tagName, options = {}) {
        const element = document.createElement(tagName);

        if (options.className) {
            element.className = options.className;
        }

        if (options.text !== undefined) {
            element.textContent = options.text;
        }

        if (options.href) {
            element.setAttribute('href', options.href);
        }

        if (options.title) {
            element.setAttribute('title', options.title);
        }

        if (options.dataLabel) {
            element.setAttribute('data-label', options.dataLabel);
        }

        return element;
    }

    function initScoreBars() {
        document.querySelectorAll(SELECTORS.scoreBar).forEach((bar) => {
            const score = Number.parseFloat(bar.dataset.score);
            const safeScore = Number.isFinite(score)
                ? clamp(score, MIN_PERCENT, MAX_PERCENT)
                : MIN_PERCENT;

            bar.style.width = `${safeScore}%`;
        });
    }

    function initFilterAutoSubmit() {
        const filterForm = document.querySelector(SELECTORS.filterForm);

        if (!filterForm) {
            return;
        }

        filterForm.addEventListener('change', function (event) {
            if (!event.target.classList.contains('filter-select')) {
                return;
            }

            filterForm.submit();
        });
    }

    function parseRowsData() {
        const jsonElement = getElement(SELECTORS.rowsJson);

        if (!jsonElement) {
            return [];
        }

        try {
            const parsedRows = JSON.parse(jsonElement.textContent.trim());
            return Array.isArray(parsedRows) ? parsedRows : [];
        } catch (error) {
            return [];
        }
    }

    function createBadge(text, className, title = '') {
        return createElement('span', {
            className: className,
            text: text,
            title: title
        });
    }

    function createTableCell(label, childOrText, className = '') {
        const cell = createElement('td', {
            className: className,
            dataLabel: label
        });

        if (childOrText instanceof Node) {
            cell.appendChild(childOrText);
        } else {
            cell.textContent = childOrText;
        }

        return cell;
    }

    function createStatusCell(item) {
        const isKuliah = Number(item.status_prediksi) === 1;

        const statusText = item.status_label
            ? item.status_label
            : (isKuliah
                ? 'Memenuhi Batas Rekomendasi'
                : 'Belum Memenuhi Batas Rekomendasi');

        const statusClass = isKuliah
            ? 'stat-badge badge-green'
            : 'stat-badge badge-amber';

        return createTableCell('Status', createBadge(statusText, statusClass));
    }

    function createActionCell(item) {
        const link = createElement('a', {
            className: 'btn btn-primary btn-sm',
            text: 'Detail',
            href: item.detail_url
        });

        return createTableCell('Aksi', link);
    }

    function createTableRow(item, index) {
        const row = createElement('tr', {
            className: 'table-body-row'
        });

        const jurusanBadge = createBadge(
            item.jurusan_smk || '-',
            'stat-badge badge-blue',
            item.jurusan_smk_lengkap || item.jurusan_smk || ''
        );

        row.appendChild(createTableCell('#', String(index), 'table-index'));
        row.appendChild(createTableCell('Nama Siswa', item.nama_siswa || 'Siswa', 'table-name'));
        row.appendChild(createTableCell('Jurusan', jurusanBadge));
        row.appendChild(createStatusCell(item));
        row.appendChild(createTableCell('Tanggal', item.tanggal || '-', 'table-date'));
        row.appendChild(createActionCell(item));

        return row;
    }

    function updatePaginationInfo(startIndex, endIndex) {
        const infoText = getElement(SELECTORS.paginationInfo);
        const pageIndicator = getElement(SELECTORS.pageIndicator);
        const prevButton = getElement(SELECTORS.prevButton);
        const nextButton = getElement(SELECTORS.nextButton);

        if (infoText) {
            infoText.textContent = `Menampilkan ${startIndex}–${endIndex} dari ${allRows.length} data`;
        }

        if (pageIndicator) {
            pageIndicator.textContent = `Halaman ${currentPage} / ${totalPages}`;
        }

        if (prevButton) {
            prevButton.disabled = currentPage === 1;
        }

        if (nextButton) {
            nextButton.disabled = currentPage === totalPages;
        }
    }

    function renderTable() {
        const tableBody = getElement(SELECTORS.tableBody);

        if (!tableBody) {
            return;
        }

        const start = (currentPage - 1) * perPage;
        const rows = allRows.slice(start, start + perPage);

        tableBody.replaceChildren();

        rows.forEach((item, index) => {
            tableBody.appendChild(createTableRow(item, start + index + 1));
        });

        const startIndex = allRows.length ? start + 1 : 0;
        const endIndex = Math.min(start + perPage, allRows.length);

        updatePaginationInfo(startIndex, endIndex);
    }

    function changePage(direction) {
        const nextPage = currentPage + direction;

        if (nextPage < 1 || nextPage > totalPages) {
            return;
        }

        currentPage = nextPage;
        renderTable();

        const tableWrap = getElement(SELECTORS.tableWrap);

        if (tableWrap) {
            tableWrap.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    }

    function initPaginationEvents() {
        const prevButton = getElement(SELECTORS.prevButton);
        const nextButton = getElement(SELECTORS.nextButton);

        if (prevButton) {
            prevButton.addEventListener('click', function () {
                changePage(-1);
            });
        }

        if (nextButton) {
            nextButton.addEventListener('click', function () {
                changePage(1);
            });
        }
    }

    function initPredictionTable() {
        const tableWrap = getElement(SELECTORS.tableWrap);
        const tableBody = getElement(SELECTORS.tableBody);

        if (!tableWrap || !tableBody) {
            return;
        }

        const perPageFromDataset = Number.parseInt(tableWrap.dataset.perPage, 10);

        perPage = Number.isFinite(perPageFromDataset) && perPageFromDataset > 0
            ? perPageFromDataset
            : DEFAULT_PER_PAGE;

        allRows = parseRowsData();
        totalPages = Math.max(Math.ceil(allRows.length / perPage), 1);
        currentPage = 1;

        initPaginationEvents();
        renderTable();
    }

    function init() {
        initScoreBars();
        initFilterAutoSubmit();
        initPredictionTable();
    }

    document.addEventListener('DOMContentLoaded', init);
})();