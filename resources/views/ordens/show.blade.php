@extends('layouts.main_layout')



@section('content')

<div class="container mt-4">



  {{-- Cartão principal --}}

  <div class="card shadow-sm border-0">

    {{-- Cabeçalho (visível só na tela) --}}

    <div class="card-header bg-primary text-white fw-bold d-flex justify-content-between align-items-center">

      <span><i class="bi bi-receipt"></i> Ordem de Serviço #{{ $os->id }}</span>



      {{-- AÇÕES (não imprime) --}}

      <div class="d-flex align-items-center gap-2 no-print">

        @php

          $map = ['Aberta'=>'success','Em andamento'=>'warning','Finalizada'=>'secondary','Cancelada'=>'danger'];

          $badge = $map[$os->situacao] ?? 'light';

        @endphp



        <span class="badge rounded-pill text-bg-{{ $badge }}">{{ $os->situacao }}</span>



        <button type="button" class="btn btn-outline-light btn-sm" onclick="window.print()">

          <i class="bi bi-printer"></i>Imprimir
        </button>



        <a href="javascript:history.back()" class="btn btn-light btn-sm">

          <i class="bi bi-arrow-left"></i> Voltar

        </a>

      </div>

    </div>



    <div class="card-body">



      {{-- Infos de criação (só na tela) --}}

      <div class="d-flex justify-content-between flex-wrap mb-3 no-print">

        <h4 class="mb-2">Resumo da OS</h4>

        <div class="text-muted small">

          Criada em:

          {{ \Carbon\Carbon::parse($os->data_chamado)->format('d/m/Y H:i') }}

          @if($os->data_previsao_entrega)

            · Prev. entrega:

            {{ \Carbon\Carbon::parse($os->data_previsao_entrega)->format('d/m/Y H:i') }}

          @endif

        </div>

      </div>



      {{-- =========================================================

           APENAS O BLOCO ABAIXO (#print-scope) VAI PARA A IMPRESSÃO

         ========================================================= --}}

      @php
        $quantidadeLinhas = $os->servicosItens->count() + $os->pecasItens->count();
        $densidadeImpressao = $quantidadeLinhas <= 8 ? 'spacious' : ($quantidadeLinhas <= 18 ? 'regular' : 'compact');
      @endphp
      <div id="print-scope" class="os-print-{{ $densidadeImpressao }}">
        <div class="os-print-original">
        <div class="os-document-intro">



        {{-- Identidade da oficina: altere apenas o caminho abaixo --}}
        @php
            $logoOficina = asset('images/logo-oficina.png');
        @endphp

        <div class="os-document-header avoid-break">
          <div class="os-brand">
            <img src="{{ $logoOficina }}" alt="Logo Auto Mecânica Renato" class="os-brand-logo">
            <div class="os-brand-details">
              <div class="os-brand-name">AUTO MECÂNICA RENATO</div>
              <div>Av. Mangará, 150 · Jd. Paulista</div>
              <div>Santa Fé do Sul - SP · CEP 15777-080</div>
              <div>Telefone: (17) 3641-1541</div>
              <div>Celular: (17) 99722-9613</div>
            </div>
          </div>
          <div class="os-document-meta">
            <div class="os-document-label">ORDEM DE SERVIÇO</div>
            <div class="os-document-number">Nº {{ $os->id }}</div>
            <div class="os-document-status">{{ $os->situacao }}</div>
          </div>
        </div>
        <div class="os-document-dates avoid-break">
          <span><strong>Emissão:</strong> {{ \Carbon\Carbon::parse($os->data_chamado)->format('d/m/Y H:i') }}</span>
          @if($os->data_previsao_entrega)
            <span><strong>Previsão de entrega:</strong> {{ \Carbon\Carbon::parse($os->data_previsao_entrega)->format('d/m/Y H:i') }}</span>
          @endif
        </div>

        {{-- Blocos: Cliente / Veículo / Dados --}}

        <div class="row g-3 mb-4 os-identification">

          <div class="col-md-4">

            <div class="border rounded p-3 h-100 avoid-break">

              <div class="fw-semibold mb-2"><i class="bi bi-person"></i> Cliente</div>

              @php

                // Prioriza os snapshots congelados; se não tiver, cai pro relacionamento $os->cliente

                $nome = $os->cliente_nome_snapshot ?? ($os->cliente->nome ?? 'Desassociado');

                $fone = $os->cliente_telefone_snapshot ?? ($os->cliente->telefone ?? null);

              @endphp



              <div>{{ $nome }}</div>

              @if($fone)

                <div class="text-muted small">{{ $fone }}</div>

              @endif

            </div>

          </div>



          <div class="col-md-4">

            <div class="border rounded p-3 h-100 avoid-break">

              <div class="fw-semibold mb-2"><i class="bi bi-car-front"></i> Veículo</div>

              <div class="text-uppercase fw-semibold">{{ $os->veiculo->placa ?? '—' }}</div>

              <div class="text-muted small">

                {{ $os->veiculo->marca ?? '' }} {{ $os->veiculo->modelo ?? '' }}

                @if(!empty($os->veiculo?->ano)) · {{ $os->veiculo->ano }} @endif

              </div>

            </div>

          </div>



          <div class="col-md-4">

            <div class="border rounded p-3 h-100 avoid-break">

              <div class="fw-semibold mb-2"><i class="bi bi-gear"></i> Dados</div>

              <div>Tipo de atendimento: <span class="text-muted">{{ $os->tipo_atendimento ?: '—' }}</span></div>

              <div>Atendente: <span class="text-muted">{{ $os->atendente ?: '—' }}</span></div>

              <div>Revisão até: <span class="text-muted">{{ $os->revisao_ate ?: '—' }}</span></div>

            </div>

          </div>

        </div>



        {{-- Problema / Observações --}}

        <div class="row g-3 mb-4">

          <div class="col-md-6">

            <div class="border rounded p-3 h-100 avoid-break">

              <div class="fw-semibold mb-2"><i class="bi bi-exclamation-triangle"></i> Problema reclamado</div>

              <div class="text-muted" style="white-space: pre-wrap;">{{ $os->problema_reclamado ?: '—' }}</div>

            </div>

          </div>

          <div class="col-md-6">

            <div class="border rounded p-3 h-100 avoid-break">

              <div class="fw-semibold mb-2"><i class="bi bi-journal-text"></i> Observações</div>

              <div class="text-muted" style="white-space: pre-wrap;">{{ $os->observacoes ?: '—' }}</div>

            </div>

          </div>

        </div>



        </div>{{-- /.os-document-intro --}}

        {{-- Serviços --}}

        <h5 class="mb-2">Serviços</h5>

        <div class="table-wrap mb-4">

          <table class="table table-hover align-middle table-padrao print-table">

              <colgroup>

                <col style="width:60%">  {{-- Descrição / Serviço / Peça --}}

                <col style="width:10%">  {{-- Qtd --}}

                <col style="width:15%">  {{-- Vlr Unit --}}

                <col style="width:15%">  {{-- Total --}}

              </colgroup>

            <thead class="table-dark">

              <tr>

                <th>Serviço</th>

                <th class="text-end">Qtd</th>

                <th class="text-end">Vlr Unit</th>

                <th class="text-end">Total</th>

              </tr>

            </thead>

            <tbody>

              @forelse($os->servicosItens as $item)

                <tr>

                  <td>{{ $item->servico?->descricao ?? '—' }}</td>

                  <td class="text-end">{{ number_format($item->qtd, 0, ',', '.') }}</td>

                  <td class="text-end">{{ number_format($item->valor_unit, 2, ',', '.') }}</td>

                  <td class="text-end fw-semibold">{{ number_format($item->valor_total, 2, ',', '.') }}</td>

                </tr>

              @empty

                <tr><td colspan="4" class="text-center text-muted">Nenhum serviço adicionado.</td></tr>

              @endforelse

            </tbody>

          </table>

        </div>



        {{-- Peças --}}

        <h5 class="mb-2">Peças</h5>

        <div class="table-wrap mb-4">

          <table class="table table-hover align-middle table-padrao print-table">

              <colgroup>

                <col style="width:60%">  {{-- Descrição / Serviço / Peça --}}

                <col style="width:10%">  {{-- Qtd --}}

                <col style="width:15%">  {{-- Vlr Unit --}}

                <col style="width:15%">  {{-- Total --}}

              </colgroup>

            <thead class="table-dark">

              <tr>

                <th>Peça</th>

                <th class="text-end">Qtd</th>

                <th class="text-end">Vlr Unit</th>

                <th class="text-end">Total</th>

              </tr>

            </thead>

            <tbody>

              @forelse($os->pecasItens as $item)

                <tr>

                  <td>{{ $item->estoque?->descricao ?? '—' }}</td>

                  <td class="text-end">{{ number_format($item->qtd, 0, ',', '.') }}</td>

                  <td class="text-end">{{ number_format($item->valor_unit, 2, ',', '.') }}</td>

                  <td class="text-end fw-semibold">{{ number_format($item->valor_total, 2, ',', '.') }}</td>

                </tr>

              @empty

                <tr><td colspan="4" class="text-center text-muted">Nenhuma peça adicionada.</td></tr>

              @endforelse

            </tbody>

          </table>

        </div>



        {{-- Totais --}}

        <div class="row os-totals">

          <div class="col-12">

            <div class="border rounded p-3 w-100 avoid-break">

              <div class="d-flex justify-content-between mb-1">

                <span class="text-muted">Subtotal Serviços</span>

                <strong>{{ number_format($os->total_servicos, 2, ',', '.') }}</strong>

              </div>

              <div class="d-flex justify-content-between mb-1">

                <span class="text-muted">Subtotal Peças</span>

                <strong>{{ number_format($os->total_pecas, 2, ',', '.') }}</strong>

              </div>

              <div class="d-flex justify-content-between mb-1">

                <span class="text-muted">Frete</span>

                <strong>{{ number_format($os->frete, 2, ',', '.') }}</strong>

              </div>

              <hr class="my-2">

              <div class="d-flex justify-content-between fs-5">

                <span>Total da OS</span>

                <strong>{{ number_format($os->total_os, 2, ',', '.') }}</strong>

              </div>

            </div>

          </div>

        </div>



        </div>{{-- /.os-print-original --}}
      </div>{{-- /#print-scope --}}



    </div>

  </div>

</div>

<script src="{{ asset('assets/js/os/os-print.js') }}?v={{ filemtime(public_path('assets/js/os/os-print.js')) }}" defer></script>
@endsection



@push('styles')
<style>
.only-print, .os-print-pages { display: none; }
.avoid-break { break-inside: avoid; page-break-inside: avoid; }
.os-document-header { display:flex; justify-content:space-between; align-items:center; gap:20px; padding:16px 18px; background:#f7faff; border:1px solid #dce6f2; border-radius:10px 10px 0 0; }
.os-brand { display:flex; align-items:center; gap:16px; min-width:0; }
.os-brand-logo { width:165px; max-height:105px; object-fit:contain; flex-shrink:0; }
.os-brand-details { color:#526074; font-size:12px; line-height:1.65; }
.os-brand-name { font-size:16px; font-weight:800; color:#122b53; letter-spacing:.3px; }
.os-document-meta { text-align:right; flex-shrink:0; }
.os-document-label { font-size:11px; font-weight:700; color:#526074; letter-spacing:1px; }
.os-document-number { font-size:25px; font-weight:800; color:#122b53; }
.os-document-status { display:inline-block; padding:3px 9px; background:#e4ebf5; border-radius:20px; color:#233c61; font-size:11px; font-weight:600; }
.os-document-dates { display:flex; flex-wrap:wrap; justify-content:space-between; gap:8px 20px; border:1px solid #dce6f2; border-top:0; border-radius:0 0 10px 10px; padding:10px 18px; margin-bottom:20px; font-size:12px; color:#526074; }
@media (max-width:700px) { .os-document-header { flex-wrap:wrap; } .os-brand-logo { width:110px; } .os-document-meta { text-align:left; } }
@media print {
  @page { size:A4; margin:10mm; }
  #print-scope.os-paginated { width:190mm !important; max-width:100% !important; }
  #print-scope.os-paginated > .os-print-original { display:none !important; }
  #print-scope.os-paginated > .os-print-pages { display:block !important; }
  #print-scope .os-print-page { display:flex; flex-direction:column; min-height:276mm; box-sizing:border-box; break-after:page; page-break-after:always; }
  #print-scope .os-print-page:last-child { break-after:auto; page-break-after:auto; }
  #print-scope .os-print-page-content { display:flow-root; flex:0 0 auto; }
  #print-scope .os-print-page > .os-totals { margin-top:auto !important; padding-top:4mm; }

  body * { visibility:hidden !important; }
  #print-scope, #print-scope * { visibility:visible !important; }
  #print-scope { position:static !important; width:auto !important; margin:0 !important; padding:0 !important; }
  header, footer, nav, .navbar, .topbar, .app-header, .app-navbar, .sidebar, .offcanvas,
  .breadcrumb, .pagination, .btn, .btn-group, .dropdown, .no-print, .card-header { display:none !important; }
  body { background:#fff !important; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
  /* Remove o espaço do painel e usa somente as margens A4 definidas acima. */
  .app-wrapper, body > .d-flex { display:block !important; }
  /* Elementos ocultos fora da nota não devem reservar altura no papel. */
  body *:not(:has(#print-scope)):not(#print-scope):not(#print-scope *) { display:none !important; }
  .app-main { margin:0 !important; padding:0 !important; min-height:0 !important; }
  .container { max-width:none !important; width:100% !important; margin:0 !important; padding:0 !important; }
  .card { display:block !important; border:0 !important; }
  .card-body { padding:0 !important; }
  #print-scope { font-size:11.5pt; line-height:1.35; --os-cell-padding:8px; }
  #print-scope.os-print-spacious { --os-cell-padding:14px; }
  #print-scope.os-print-compact { --os-cell-padding:5px; }
  /* Cerca de um terço da área útil A4, podendo crescer com textos longos. */
  #print-scope .os-document-intro { min-height:88mm; }
  #print-scope .os-document-intro > .row.g-3 .border { min-height:21mm !important; }
  #print-scope .os-document-intro > .os-identification .border { min-height:24mm !important; }
  #print-scope .os-document-intro > .os-identification { margin-bottom:4mm !important; }
  /* O breakpoint de tela não deve empilhar os blocos na folha A4. */
  #print-scope .row.g-3 { display:table !important; table-layout:fixed; width:100%; margin:0 0 3mm !important; border-spacing:3px 0; }
  #print-scope .row.g-3 > .col-md-4,
  #print-scope .row.g-3 > .col-md-6 { display:table-cell !important; float:none !important; vertical-align:top; padding:0 !important; margin:0 !important; }
  #print-scope .row.g-3 > .col-md-4 { width:33.333333% !important; }
  #print-scope .row.g-3 > .col-md-6 { width:50% !important; }
  #print-scope .h-100 { height:auto !important; min-height:0 !important; }
  #print-scope .os-totals { display:block !important; margin:0 !important; break-inside:avoid; page-break-inside:avoid; }
  #print-scope .os-totals > .col-12 { width:100% !important; padding:0 !important; }
  #print-scope .mb-4 { margin-bottom:5mm !important; }
  #print-scope .mb-2 { margin-bottom:5px !important; }
  #print-scope .mb-1 { margin-bottom:3px !important; }
  #print-scope .p-3 { padding:9px 10px !important; }
  #print-scope .my-2 { margin-top:6px !important; margin-bottom:6px !important; }
  #print-scope .border { border-radius:0 !important; }
  #print-scope h5 { margin:3mm 0 2mm !important; font-size:13pt; break-after:avoid; page-break-after:avoid; }
  #print-scope .fs-5 { font-size:14pt !important; }
  #print-scope th, #print-scope td { padding:var(--os-cell-padding) 9px !important; font-size:10.5pt !important; line-height:1.35 !important; }
  #print-scope thead th { padding:7px 9px !important; }
  #print-scope .os-totals > .col-12 > .border { padding:12px !important; }
  /* As tabelas podem continuar na próxima folha, mantendo cada item inteiro. */
  #print-scope .table-wrap, #print-scope table, #print-scope tbody { break-inside:auto; page-break-inside:auto; }
  #print-scope .table { margin-bottom:0 !important; }
  .card, .border, .shadow, .shadow-sm { box-shadow:none !important; }
  .os-document-header { flex-wrap:nowrap; background:#fff !important; min-height:27mm; padding:6px 0; border:0; border-bottom:2px solid #122b53; border-radius:0; }
  .os-brand-logo { width:145px; max-height:85px; }
  .os-brand { gap:14px; }
  .os-brand-details { font-size:9.25pt; line-height:1.5; }
  .os-brand-name { font-size:13pt; }
  .os-document-dates { padding:8px 0; margin-bottom:3mm; gap:4px 12px; font-size:8.5pt; border:0; border-radius:0; }
  .os-document-number { font-size:19pt; }
  .os-document-label, .os-document-status { font-size:9.25pt; }
  .os-document-status { padding:1px 5px; }
  .table-wrap, .table-responsive { overflow:visible !important; max-height:none !important; height:auto !important; }
  #print-scope table { display:table !important; width:100% !important; min-width:0 !important; table-layout:fixed !important; border-collapse:collapse !important; }
  #print-scope thead { display:table-header-group !important; }
  #print-scope tbody { display:table-row-group !important; }
  #print-scope tr { display:table-row !important; break-inside:avoid !important; }
  #print-scope td, #print-scope th { display:table-cell !important; width:auto !important; white-space:normal !important; }
  .avoid-break { break-inside:avoid !important; page-break-inside:avoid !important; }
}
</style>
@endpush
