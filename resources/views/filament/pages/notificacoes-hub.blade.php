<x-filament-panels::page>
    <style>
        .notif-hub-kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 0.75rem; margin-bottom: 1.5rem; }
        .notif-hub-kpi { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 1rem; }
        .dark .notif-hub-kpi { background: rgb(24 24 27); border-color: rgb(63 63 70); }
        .notif-hub-kpi__label { font-size: 0.75rem; color: #6b7280; font-weight: 600; text-transform: uppercase; }
        .notif-hub-kpi__value { font-size: 1.75rem; font-weight: 700; margin-top: 0.25rem; }
        .notif-hub-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; }
        .notif-hub-card {
            display: block; padding: 1.25rem; border: 1px solid #e5e7eb; border-radius: 8px;
            background: #fff; text-decoration: none; color: inherit; transition: border-color .15s, box-shadow .15s;
        }
        .dark .notif-hub-card { background: rgb(24 24 27); border-color: rgb(63 63 70); }
        .notif-hub-card:hover { border-color: #f59e0b; box-shadow: 0 4px 12px rgba(0,0,0,.06); }
        .notif-hub-card__title { font-weight: 700; font-size: 1rem; margin: 0 0 0.35rem; }
        .notif-hub-card__desc { font-size: 0.85rem; color: #6b7280; margin: 0; }
    </style>

    <div class="notif-hub-kpis">
        <div class="notif-hub-kpi">
            <div class="notif-hub-kpi__label">Grupos</div>
            <div class="notif-hub-kpi__value">{{ $totalGrupos }}</div>
        </div>
        <div class="notif-hub-kpi">
            <div class="notif-hub-kpi__label">Enquetes</div>
            <div class="notif-hub-kpi__value">{{ $totalEnquetes }}</div>
        </div>
        <div class="notif-hub-kpi">
            <div class="notif-hub-kpi__label">Envios hoje</div>
            <div class="notif-hub-kpi__value">{{ $enviosHoje }}</div>
        </div>
    </div>

    <div class="notif-hub-grid">
        @foreach($links as $link)
            <a href="{{ $link['url'] }}" class="notif-hub-card">
                <p class="notif-hub-card__title">{{ $link['label'] }}</p>
                <p class="notif-hub-card__desc">{{ $link['desc'] }}</p>
            </a>
        @endforeach
    </div>
</x-filament-panels::page>
