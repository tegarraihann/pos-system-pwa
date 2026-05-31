<style>
    .report-page {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .report-panel,
    .report-stat-card,
    .report-table-card {
        background: #111827;
        border: 1px solid rgba(148, 163, 184, 0.18);
        border-radius: 1rem;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.2);
    }

    .report-panel {
        padding: 1.25rem;
    }

    .report-filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
    }

    .report-field {
        display: flex;
        flex-direction: column;
        gap: 0.45rem;
    }

    .report-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: #cbd5e1;
    }

    .report-input,
    .report-select {
        width: 100%;
        border-radius: 0.85rem;
        border: 1px solid rgba(148, 163, 184, 0.24);
        background: #0f172a;
        color: #f8fafc;
        padding: 0.75rem 0.9rem;
        font-size: 0.95rem;
        line-height: 1.4;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .report-input:focus,
    .report-select:focus {
        outline: none;
        border-color: rgba(245, 158, 11, 0.9);
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.18);
    }

    .report-toolbar {
        margin-top: 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .report-meta {
        font-size: 0.9rem;
        color: #94a3b8;
    }

    .report-stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1rem;
    }

    .report-stat-card {
        padding: 1.1rem 1.15rem;
    }

    .report-stat-label {
        margin: 0;
        font-size: 0.85rem;
        font-weight: 600;
        color: #94a3b8;
    }

    .report-stat-value {
        margin: 0.55rem 0 0;
        font-size: 1.8rem;
        font-weight: 700;
        line-height: 1.1;
        color: #f8fafc;
    }

    .report-stat-hint {
        margin: 0.7rem 0 0;
        font-size: 0.8rem;
        line-height: 1.45;
        color: #cbd5e1;
    }

    .report-table-card {
        overflow: hidden;
    }

    .report-table-header {
        padding: 1rem 1.15rem;
        border-bottom: 1px solid rgba(148, 163, 184, 0.16);
    }

    .report-table-title {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 700;
        color: #f8fafc;
    }

    .report-table-scroller {
        overflow-x: auto;
    }

    .report-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 720px;
    }

    .report-table th,
    .report-table td {
        padding: 0.9rem 1.15rem;
        text-align: left;
        vertical-align: top;
        border-bottom: 1px solid rgba(148, 163, 184, 0.12);
    }

    .report-table th {
        font-size: 0.76rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-weight: 700;
        color: #94a3b8;
        background: rgba(15, 23, 42, 0.88);
        white-space: nowrap;
    }

    .report-table td {
        font-size: 0.92rem;
        line-height: 1.45;
        color: #e2e8f0;
    }

    .report-table tr:last-child td {
        border-bottom: none;
    }

    .report-empty {
        text-align: center;
        color: #94a3b8;
        padding: 1.5rem 1rem;
    }

    @media (max-width: 768px) {
        .report-panel {
            padding: 1rem;
        }

        .report-table {
            min-width: 620px;
        }
    }
</style>
