<style>
    .fi-data-table-wrap {
        overflow-x: auto;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
    }
    .dark .fi-data-table-wrap {
        border-color: rgb(63 63 70);
    }
    .fi-data-table {
        width: 100%;
        min-width: 560px;
        border-collapse: collapse;
        border-spacing: 0;
        table-layout: fixed;
        font-size: 0.875rem;
    }
    .fi-data-table thead { display: table-header-group; }
    .fi-data-table tbody { display: table-row-group; }
    .fi-data-table tr { display: table-row; }
    .fi-data-table th,
    .fi-data-table td {
        display: table-cell;
        vertical-align: middle;
        padding: 0.75rem 1rem;
        text-align: left;
        border-bottom: 1px solid #f3f4f6;
        word-break: break-word;
    }
    .dark .fi-data-table th,
    .dark .fi-data-table td {
        border-bottom-color: rgb(39 39 42);
    }
    .fi-data-table th {
        background: #f9fafb;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #6b7280;
    }
    .dark .fi-data-table th {
        background: rgb(39 39 42);
        color: #a1a1aa;
    }
    .fi-data-table tbody tr:hover { background: #f9fafb; }
    .dark .fi-data-table tbody tr:hover { background: rgb(39 39 42); }
    .fi-data-table tbody tr:last-child td { border-bottom: 0; }
    .fi-data-table .col-date { width: 130px; white-space: nowrap; }
    .fi-data-table .col-phone { width: 140px; font-variant-numeric: tabular-nums; }
    .fi-data-table .col-name { width: 140px; }
    .fi-data-table .col-grupo { width: 120px; }
    .fi-data-table .col-tipo { width: 90px; }
    .fi-data-table .col-status { width: 100px; }
    .fi-data-table .col-chave { width: 140px; }
    .fi-data-table .col-actions { width: 90px; text-align: right; }
    .fi-data-table .col-auto { width: auto; }
    .fi-data-badge {
        display: inline-block;
        padding: 0.2rem 0.55rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        line-height: 1.3;
        white-space: nowrap;
    }
    .fi-data-badge--ok { background: #dcfce7; color: #166534; }
    .fi-data-badge--erro { background: #fee2e2; color: #991b1b; }
    .fi-data-badge--info { background: #eff6ff; color: #1d4ed8; }
    .fi-data-empty {
        padding: 1.25rem 0;
        text-align: center;
        color: #6b7280;
        font-size: 0.875rem;
    }
    .fi-data-filter {
        margin-bottom: 1rem;
        max-width: 220px;
    }
    .fi-data-filter select {
        width: 100%;
        box-sizing: border-box;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        background: #fff;
        color: #111827;
    }
    .dark .fi-data-filter select {
        background: rgb(39 39 42);
        border-color: rgb(63 63 70);
        color: #f4f4f5;
    }
    .fi-data-pagination { margin-top: 1rem; }
</style>
