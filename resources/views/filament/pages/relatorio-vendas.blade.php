<x-filament-panels::page>
    @php
        $resumo = $this->resumo;
        $vendas = $this->vendas;
        $porMetodo = $this->porMetodo;
        $pedido = $this->pedidoDetalhe;
        $mp = $this->detalheMp;
    @endphp

    <style>
        .rv-page { display: flex; flex-direction: column; gap: 1.25rem; }
        .rv-filtros {
            display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: flex-end;
            padding: 1rem 1.25rem; background: #fff; border: 1px solid #e5e7eb; border-radius: 8px;
        }
        .dark .rv-filtros { background: rgb(24 24 27); border-color: rgb(63 63 70); }
        .rv-field { flex: 1 1 160px; min-width: 0; }
        .rv-field label { display: block; font-size: 0.8rem; font-weight: 600; color: #374151; margin-bottom: 0.35rem; }
        .dark .rv-field label { color: #d4d4d8; }
        .rv-select {
            width: 100%; box-sizing: border-box; border: 1px solid #d1d5db; border-radius: 6px;
            padding: 0.5rem 0.75rem; font-size: 0.875rem; background: #fff; color: #111827;
        }
        .dark .rv-select { background: rgb(39 39 42); border-color: rgb(63 63 70); color: #f4f4f5; }
        .rv-btn {
            border: 0; border-radius: 6px; padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 600;
            cursor: pointer; background: #6b7280; color: #fff;
        }
        .rv-btn:hover { background: #4b5563; }
        .rv-btn--link {
            background: transparent; color: #2563eb; padding: 0.25rem 0.4rem; font-weight: 700;
        }
        .dark .rv-btn--link { color: #93c5fd; }
        .rv-kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 0.75rem; }
        .rv-kpi {
            background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 1rem 1.15rem;
            box-shadow: 0 1px 2px rgba(0,0,0,.04);
        }
        .dark .rv-kpi { background: rgb(24 24 27); border-color: rgb(63 63 70); }
        .rv-kpi__label { font-size: 0.75rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: .03em; }
        .rv-kpi__value { font-size: 1.55rem; font-weight: 700; line-height: 1.2; margin-top: 0.25rem; color: #111827; }
        .dark .rv-kpi__value { color: #f4f4f5; }
        .rv-kpi__sub { font-size: 0.78rem; color: #6b7280; margin-top: 0.2rem; }
        .rv-kpi--green .rv-kpi__value { color: #16a34a; }
        .rv-kpi--amber .rv-kpi__value { color: #d97706; }
        .rv-kpi--red .rv-kpi__value { color: #dc2626; }
        .rv-kpi--blue .rv-kpi__value { color: #2563eb; }
        .rv-card {
            background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;
            box-shadow: 0 1px 2px rgba(0,0,0,.04);
        }
        .dark .rv-card { background: rgb(24 24 27); border-color: rgb(63 63 70); }
        .rv-card__header {
            padding: 0.875rem 1.25rem; font-weight: 700; font-size: 0.95rem;
            border-bottom: 1px solid #e5e7eb; background: #f9fafb; color: #111827;
        }
        .dark .rv-card__header { background: rgb(39 39 42); border-color: rgb(63 63 70); color: #f4f4f5; }
        .rv-card__body { padding: 1rem 1.25rem; }
        .rv-card__body--flush { padding: 0; }
        .rv-note { font-size: 0.8rem; color: #6b7280; margin: 0; }
        .dark .rv-note { color: #a1a1aa; }
        .rv-table-wrap { overflow-x: auto; }
        .rv-table { width: 100%; border-collapse: collapse; font-size: 0.84rem; }
        .rv-table th, .rv-table td { padding: 0.65rem 1rem; text-align: left; border-bottom: 1px solid #f3f4f6; vertical-align: top; }
        .dark .rv-table th, .dark .rv-table td { border-bottom-color: rgb(39 39 42); }
        .rv-table th { font-size: 0.72rem; text-transform: uppercase; letter-spacing: .04em; color: #6b7280; background: #f9fafb; font-weight: 700; }
        .dark .rv-table th { background: rgb(39 39 42); color: #a1a1aa; }
        .rv-table tbody tr:hover { background: #f9fafb; }
        .dark .rv-table tbody tr:hover { background: rgb(39 39 42); }
        .rv-table .num { text-align: right; font-variant-numeric: tabular-nums; }
        .rv-badge {
            display: inline-flex; align-items: center; padding: 0.2rem 0.55rem; border-radius: 999px;
            font-size: 0.72rem; font-weight: 700; white-space: nowrap;
        }
        .rv-badge--ok { background: #dcfce7; color: #166534; }
        .rv-badge--warn { background: #fef3c7; color: #92400e; }
        .rv-badge--danger { background: #fee2e2; color: #991b1b; }
        .rv-badge--info { background: #dbeafe; color: #1e40af; }
        .rv-badge--gray { background: #f3f4f6; color: #374151; }
        .dark .rv-badge--gray { background: rgb(63 63 70); color: #e4e4e7; }
        .rv-pager { padding: 0.85rem 1.25rem; display: flex; justify-content: flex-end; }
        .rv-modal-backdrop {
            position: fixed; inset: 0; background: rgba(15, 23, 42, 0.55); z-index: 60;
            display: flex; align-items: flex-start; justify-content: center; padding: 4rem 1rem 2rem;
            overflow-y: auto;
        }
        .rv-modal {
            width: min(720px, 100%); background: #fff; border-radius: 12px; border: 1px solid #e5e7eb;
            box-shadow: 0 20px 50px rgba(0,0,0,.25);
        }
        .dark .rv-modal { background: rgb(24 24 27); border-color: rgb(63 63 70); }
        .rv-modal__head {
            display: flex; justify-content: space-between; align-items: center; gap: 1rem;
            padding: 1rem 1.25rem; border-bottom: 1px solid #e5e7eb; font-weight: 700;
        }
        .dark .rv-modal__head { border-bottom-color: rgb(63 63 70); }
        .rv-modal__body { padding: 1.15rem 1.25rem 1.4rem; display: flex; flex-direction: column; gap: 1rem; }
        .rv-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0.75rem; }
        .rv-dl { margin: 0; }
        .rv-dl dt { font-size: 0.72rem; text-transform: uppercase; letter-spacing: .04em; color: #6b7280; font-weight: 700; }
        .rv-dl dd { margin: 0.2rem 0 0; font-size: 0.9rem; font-weight: 600; color: #111827; word-break: break-word; }
        .dark .rv-dl dd { color: #f4f4f5; }
        .rv-items { margin: 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: 0.4rem; }
        .rv-items li { display: flex; justify-content: space-between; gap: 1rem; font-size: 0.86rem; }
        .rv-empty { padding: 1.5rem; text-align: center; color: #6b7280; font-size: 0.9rem; }
    </style>

    <div class="rv-page">
        <div class="rv-filtros">
            <div class="rv-field">
                <label for="filtro_periodo">Período</label>
                <select id="filtro_periodo" class="rv-select" wire:model.live="filtro_periodo">
                    <option value="7">Últimos 7 dias</option>
                    <option value="30">Últimos 30 dias</option>
                    <option value="90">Últimos 90 dias</option>
                    <option value="365">Último ano</option>
                    <option value="0">Todo o período</option>
                </select>
            </div>
            <div class="rv-field">
                <label for="filtro_payment_status">Pagamento</label>
                <select id="filtro_payment_status" class="rv-select" wire:model.live="filtro_payment_status">
                    <option value="">Todos</option>
                    <option value="approved">Aprovado</option>
                    <option value="pending">Pendente</option>
                    <option value="rejected">Recusado</option>
                    <option value="cancelled">Cancelado</option>
                </select>
            </div>
            <div class="rv-field">
                <label for="filtro_status">Status do pedido</label>
                <select id="filtro_status" class="rv-select" wire:model.live="filtro_status">
                    <option value="">Todos</option>
                    @foreach(\App\Models\Order::STATUSES as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="rv-field" style="flex:0 0 auto;">
                <label for="perPage">Por página</label>
                <select id="perPage" class="rv-select" wire:model.live="perPage">
                    <option value="10">10</option>
                    <option value="15">15</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
            <button type="button" class="rv-btn" wire:click="limparFiltros">Limpar</button>
        </div>

        <div class="rv-kpis">
            <div class="rv-kpi rv-kpi--green">
                <div class="rv-kpi__label">Receita aprovada</div>
                <div class="rv-kpi__value">{{ $this->formatMoney($resumo['total_aprovado']) }}</div>
                <div class="rv-kpi__sub">{{ $resumo['qtd_aprovados'] }} venda(s)</div>
            </div>
            <div class="rv-kpi rv-kpi--blue">
                <div class="rv-kpi__label">Ticket médio</div>
                <div class="rv-kpi__value">{{ $this->formatMoney($resumo['ticket_medio']) }}</div>
                <div class="rv-kpi__sub">somente aprovadas</div>
            </div>
            <div class="rv-kpi rv-kpi--amber">
                <div class="rv-kpi__label">Pendentes</div>
                <div class="rv-kpi__value">{{ $resumo['qtd_pendentes'] }}</div>
                <div class="rv-kpi__sub">aguardando confirmação</div>
            </div>
            <div class="rv-kpi rv-kpi--red">
                <div class="rv-kpi__label">Recusadas / canceladas</div>
                <div class="rv-kpi__value">{{ $resumo['qtd_recusados'] }}</div>
                <div class="rv-kpi__sub">no período</div>
            </div>
            <div class="rv-kpi">
                <div class="rv-kpi__label">Pedidos no período</div>
                <div class="rv-kpi__value">{{ $resumo['qtd_total'] }}</div>
                <div class="rv-kpi__sub">total {{ $this->formatMoney($resumo['total_geral']) }}</div>
            </div>
        </div>

        @if(count($porMetodo))
            <div class="rv-card">
                <div class="rv-card__header">Aprovadas por método</div>
                <div class="rv-card__body--flush">
                    <div class="rv-table-wrap">
                        <table class="rv-table">
                            <thead>
                                <tr>
                                    <th>Método</th>
                                    <th class="num">Qtd</th>
                                    <th class="num">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($porMetodo as $row)
                                    <tr>
                                        <td>{{ $row['metodo'] }}</td>
                                        <td class="num">{{ $row['qtd'] }}</td>
                                        <td class="num">{{ $this->formatMoney($row['total']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <div class="rv-card">
            <div class="rv-card__header" style="display:flex;justify-content:space-between;gap:1rem;align-items:center;">
                <span>Vendas</span>
                <p class="rv-note" style="margin:0;font-weight:500;">
                    Clique em “Detalhes MP” para puxar status, taxas e meio de pagamento do Mercado Pago (sem dados de cartão).
                    @if(!empty($resumo['sandbox']))
                        <strong>Sandbox ativo.</strong>
                    @endif
                </p>
            </div>
            <div class="rv-card__body--flush">
                <div class="rv-table-wrap">
                    <table class="rv-table">
                        <thead>
                            <tr>
                                <th>Referência</th>
                                <th>Cliente</th>
                                <th>Itens</th>
                                <th class="num">Total</th>
                                <th>Pagamento</th>
                                <th>Pedido</th>
                                <th>Data</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vendas as $venda)
                                @php
                                    $payBadge = match ($venda->payment_status) {
                                        'approved' => 'ok',
                                        'rejected', 'cancelled', 'canceled' => 'danger',
                                        'pending', 'in_process', 'action_required' => 'warn',
                                        default => 'gray',
                                    };
                                @endphp
                                <tr wire:key="venda-{{ $venda->id }}">
                                    <td>
                                        <strong>{{ $venda->reference }}</strong>
                                        @if($venda->payment_id)
                                            <div class="rv-note">ID: {{ \Illuminate\Support\Str::limit($venda->payment_id, 28) }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $venda->customer_name }}
                                        <div class="rv-note">{{ $venda->customer_email }}</div>
                                    </td>
                                    <td>
                                        @foreach($venda->items as $item)
                                            <div>{{ $item->quantity }}× {{ $item->product_name }}@if($item->size) ({{ $item->size }})@endif</div>
                                        @endforeach
                                    </td>
                                    <td class="num">{{ $this->formatMoney($venda->total) }}</td>
                                    <td>
                                        <span class="rv-badge rv-badge--{{ $payBadge }}">{{ $this->labelPagamento($venda->payment_status) }}</span>
                                        <div class="rv-note">{{ $venda->payment_method ?: '—' }}</div>
                                    </td>
                                    <td>
                                        <span class="rv-badge rv-badge--info">{{ $this->labelStatusPedido($venda->status) }}</span>
                                    </td>
                                    <td>{{ optional($venda->created_at)->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <button type="button" class="rv-btn rv-btn--link" wire:click="abrirDetalhe({{ $venda->id }})" wire:loading.attr="disabled">
                                            Detalhes MP
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="rv-empty">Nenhuma venda no período selecionado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($vendas->hasPages())
                    <div class="rv-pager">
                        {{ $vendas->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if($pedido)
        <div class="rv-modal-backdrop" wire:click.self="fecharDetalhe">
            <div class="rv-modal" role="dialog" aria-modal="true">
                <div class="rv-modal__head">
                    <div>
                        Pedido {{ $pedido->reference }}
                        <div class="rv-note" style="font-weight:500;">Dados locais + consulta Mercado Pago</div>
                    </div>
                    <button type="button" class="rv-btn" wire:click="fecharDetalhe">Fechar</button>
                </div>
                <div class="rv-modal__body" wire:loading.flex wire:target="abrirDetalhe">
                    <div class="rv-note">Consultando Mercado Pago…</div>
                </div>
                <div class="rv-modal__body" wire:loading.remove wire:target="abrirDetalhe">
                    <div>
                        <strong style="font-size:0.85rem;">Pedido na loja</strong>
                        <div class="rv-grid" style="margin-top:0.6rem;">
                            <dl class="rv-dl"><dt>Cliente</dt><dd>{{ $pedido->customer_name }}</dd></dl>
                            <dl class="rv-dl"><dt>E-mail</dt><dd>{{ $pedido->customer_email }}</dd></dl>
                            <dl class="rv-dl"><dt>WhatsApp</dt><dd>{{ $pedido->customer_phone ?: '—' }}</dd></dl>
                            <dl class="rv-dl"><dt>Total</dt><dd>{{ $this->formatMoney($pedido->total) }}</dd></dl>
                            <dl class="rv-dl"><dt>Status pedido</dt><dd>{{ $this->labelStatusPedido($pedido->status) }}</dd></dl>
                            <dl class="rv-dl"><dt>Status pagamento</dt><dd>{{ $this->labelPagamento($pedido->payment_status) }}</dd></dl>
                        </div>
                        <ul class="rv-items" style="margin-top:0.85rem;">
                            @foreach($pedido->items as $item)
                                <li>
                                    <span>{{ $item->quantity }}× {{ $item->product_name }}@if($item->size) ({{ $item->size }})@endif</span>
                                    <strong>{{ $this->formatMoney($item->subtotal) }}</strong>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div>
                        <strong style="font-size:0.85rem;">Mercado Pago</strong>
                        @if($mp)
                            <div class="rv-grid" style="margin-top:0.6rem;">
                                <dl class="rv-dl"><dt>Fonte</dt><dd>{{ $mp['source'] === 'orders' ? 'API Orders' : 'API Payments' }}</dd></dl>
                                <dl class="rv-dl"><dt>ID pagamento</dt><dd>{{ $mp['payment_id'] ?: '—' }}</dd></dl>
                                @if(!empty($mp['mp_order_id']))
                                    <dl class="rv-dl"><dt>ID order</dt><dd>{{ $mp['mp_order_id'] }}</dd></dl>
                                @endif
                                <dl class="rv-dl"><dt>Status MP</dt><dd>{{ $mp['mp_status'] ?: '—' }} @if(!empty($mp['mp_status_detail'])) ({{ $mp['mp_status_detail'] }}) @endif</dd></dl>
                                <dl class="rv-dl"><dt>Valor</dt><dd>{{ $this->formatMoney($mp['total_amount'] ?? 0) }}</dd></dl>
                                @if(isset($mp['net_received_amount']))
                                    <dl class="rv-dl"><dt>Líquido recebido</dt><dd>{{ $this->formatMoney($mp['net_received_amount']) }}</dd></dl>
                                @endif
                                <dl class="rv-dl"><dt>Meio</dt><dd>{{ ($mp['payment_method_id'] ?: '—') }} / {{ ($mp['payment_type'] ?: '—') }}</dd></dl>
                                @if(!empty($mp['installments']))
                                    <dl class="rv-dl"><dt>Parcelas</dt><dd>{{ $mp['installments'] }}</dd></dl>
                                @endif
                                <dl class="rv-dl"><dt>Criado em</dt><dd>{{ $mp['created_date'] ?: '—' }}</dd></dl>
                                @if(!empty($mp['date_approved']))
                                    <dl class="rv-dl"><dt>Aprovado em</dt><dd>{{ $mp['date_approved'] }}</dd></dl>
                                @endif
                                @if(!empty($mp['money_release_date']))
                                    <dl class="rv-dl"><dt>Liberação</dt><dd>{{ $mp['money_release_date'] }}</dd></dl>
                                @endif
                                <dl class="rv-dl"><dt>Ambiente</dt><dd>{{ isset($mp['live_mode']) ? ($mp['live_mode'] ? 'Produção' : 'Teste') : '—' }}</dd></dl>
                                @if(!empty($mp['payer_email']))
                                    <dl class="rv-dl"><dt>E-mail no MP</dt><dd>{{ $mp['payer_email'] }}</dd></dl>
                                @endif
                            </div>
                            @if(!empty($mp['fee_details']))
                                <div style="margin-top:0.85rem;">
                                    <div class="rv-note" style="margin-bottom:0.35rem;font-weight:700;">Taxas</div>
                                    <ul class="rv-items">
                                        @foreach($mp['fee_details'] as $fee)
                                            <li>
                                                <span>{{ $fee['type'] ?? 'taxa' }} ({{ $fee['fee_payer'] ?? '—' }})</span>
                                                <strong>{{ $this->formatMoney($fee['amount'] ?? 0) }}</strong>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <p class="rv-note" style="margin-top:0.85rem;">Nenhum número de cartão, CVV ou token é exibido ou armazenado.</p>
                        @elseif(filled($pedido->payment_id))
                            <p class="rv-note" style="margin-top:0.6rem;">Não foi possível obter o detalhe no Mercado Pago para o ID <code>{{ $pedido->payment_id }}</code>. Os dados locais acima continuam válidos.</p>
                        @else
                            <p class="rv-note" style="margin-top:0.6rem;">Este pedido ainda não tem ID de pagamento do Mercado Pago.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
