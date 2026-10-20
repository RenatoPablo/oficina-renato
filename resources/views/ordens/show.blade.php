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

      <div id="print-scope">



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

        <div class="row g-3 mb-4">

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

        <div class="row">

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



      </div>{{-- /#print-scope --}}



    </div>

  </div>

</div>

@endsection



@push('styles')
<style>
.only-print { display: none; }
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
  @page { size:A4; margin:12mm; }
  body * { visibility:hidden !important; }
  #print-scope, #print-scope * { visibility:visible !important; }
  #print-scope { position:static !important; width:auto !important; margin:0 !important; padding:0 !important; }
  header, footer, nav, .navbar, .topbar, .app-header, .app-navbar, .sidebar, .offcanvas,
  .breadcrumb, .pagination, .btn, .btn-group, .dropdown, .no-print, .card-header { display:none !important; }
  body { background:#fff !important; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
  .card, .border, .shadow, .shadow-sm { box-shadow:none !important; }
  .os-document-header { background:#fff !important; padding:8px 0; border:0; border-bottom:2px solid #122b53; border-radius:0; }
  .os-brand-logo { width:145px; max-height:90px; }
  .os-document-dates { padding:8px 0; border:0; border-radius:0; }
  .os-document-number { font-size:22px; }
  .table-wrap, .table-responsive { overflow:visible !important; max-height:none !important; height:auto !important; }
  #print-scope table { display:table !important; width:100% !important; table-layout:auto !important; border-collapse:collapse !important; }
  #print-scope thead { display:table-header-group !important; }
  #print-scope tbody { display:table-row-group !important; }
  #print-scope tr { display:table-row !important; break-inside:avoid !important; }
  #print-scope td, #print-scope th { display:table-cell !important; white-space:normal !important; }
  .avoid-break { break-inside:avoid !important; page-break-inside:avoid !important; }
}
</style>
@endpush
