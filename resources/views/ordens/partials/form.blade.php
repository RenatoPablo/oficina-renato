{{-- Campos comuns do formulário de OS --}}
@csrf

<div class="row g-3">

  {{-- Veículo --}}
  <div class="col-md-4">
    <label class="form-label">Veículo</label>
    <select name="veiculo_id" class="form-select @error('veiculo_id') is-invalid @enderror" required>
      <option value="">-- Selecionar --</option>
      @foreach ($veiculos as $v)
        <option value="{{ $v->id }}"
          @selected(old('veiculo_id', $os->veiculo_id ?? null) == $v->id)>
          {{ $v->placa }} — {{ $v->marca }} {{ $v->modelo }}
        </option>
      @endforeach
    </select>
    @error('veiculo_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  {{-- Data do chamado --}}
  <div class="col-md-4">
    <label class="form-label">Data do Chamado</label>
    <input type="datetime-local" name="data_chamado"
      value="{{ old('data_chamado', isset($os)? \Carbon\Carbon::parse($os->data_chamado)->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}"
      class="form-control @error('data_chamado') is-invalid @enderror" required>
    @error('data_chamado') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  {{-- Previsão de entrega --}}
  <div class="col-md-4">
    <label class="form-label">Previsão de Entrega</label>
    <input type="datetime-local" name="data_previsao_entrega"
      value="{{ old('data_previsao_entrega', isset($os) && $os->data_previsao_entrega ? \Carbon\Carbon::parse($os->data_previsao_entrega)->format('Y-m-d\TH:i') : '') }}"
      class="form-control @error('data_previsao_entrega') is-invalid @enderror">
    @error('data_previsao_entrega') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  {{-- Tipo de atendimento --}}
  <div class="col-md-4">
    <label class="form-label">Tipo de Atendimento</label>
    <input type="text" name="tipo_atendimento"
      value="{{ old('tipo_atendimento', $os->tipo_atendimento ?? '') }}"
      class="form-control @error('tipo_atendimento') is-invalid @enderror" placeholder="Revisão, Funilaria, Motor…">
    @error('tipo_atendimento') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  {{-- Situação --}}
  <div class="col-md-4">
    <label class="form-label">Situação</label>
    @php
      $opts = ['Aberta','Em andamento','Finalizada','Cancelada'];
    @endphp
    <select name="situacao" class="form-select @error('situacao') is-invalid @enderror">
      @foreach ($opts as $opt)
        <option value="{{ $opt }}" @selected(old('situacao', $os->situacao ?? 'Aberta') === $opt)>{{ $opt }}</option>
      @endforeach
    </select>
    @error('situacao') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  {{-- Atendente --}}
  <div class="col-md-4">
    <label class="form-label">Atendente</label>
    <input type="text" name="atendente"
      value="{{ old('atendente', $os->atendente ?? '') }}"
      class="form-control @error('atendente') is-invalid @enderror" placeholder="Quem abriu a OS">
    @error('atendente') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  {{-- Problema reclamado --}}
  <div class="col-12">
    <label class="form-label">Problema Reclamado</label>
    <textarea name="problema_reclamado" rows="3"
      class="form-control @error('problema_reclamado') is-invalid @enderror"
      placeholder="Descrição do problema">{{ old('problema_reclamado', $os->problema_reclamado ?? '') }}</textarea>
    @error('problema_reclamado') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  {{-- Revisão até / Observações --}}
  <div class="col-md-4">
    <label class="form-label">Revisão até</label>
    <input type="text" name="revisao_ate"
      value="{{ old('revisao_ate', $os->revisao_ate ?? '') }}"
      class="form-control @error('revisao_ate') is-invalid @enderror" placeholder="Ex.: 10.000 km / 12 meses">
    @error('revisao_ate') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  {{-- Valores --}}
  <div class="col-md-2">
    <label class="form-label">Frete</label>
    <input type="number" step="0.01" name="frete"
      value="{{ old('frete', $os->frete ?? 0) }}"
      class="form-control @error('frete') is-invalid @enderror">
    @error('frete') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>
  <div class="col-md-2">
    <label class="form-label">Total Serviços</label>
    <input type="number" step="0.01" name="total_servicos"
      value="{{ old('total_servicos', $os->total_servicos ?? 0) }}"
      class="form-control @error('total_servicos') is-invalid @enderror">
    @error('total_servicos') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>
  <div class="col-md-2">
    <label class="form-label">Total Peças</label>
    <input type="number" step="0.01" name="total_pecas"
      value="{{ old('total_pecas', $os->total_pecas ?? 0) }}"
      class="form-control @error('total_pecas') is-invalid @enderror">
    @error('total_pecas') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>
  <div class="col-md-2">
    <label class="form-label">Total OS</label>
    <input type="number" step="0.01" name="total_os"
      value="{{ old('total_os', $os->total_os ?? 0) }}"
      class="form-control @error('total_os') is-invalid @enderror">
    @error('total_os') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  {{-- Observações --}}
  <div class="col-12">
    <label class="form-label">Observações</label>
    <textarea name="observacoes" rows="2"
      class="form-control @error('observacoes') is-invalid @enderror"
      placeholder="Observações gerais">{{ old('observacoes', $os->observacoes ?? '') }}</textarea>
    @error('observacoes') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

</div>

<hr class="my-3">

<div class="d-flex gap-2">
  <a href="{{ route('ordem.index') }}" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left"></i> Voltar
  </a>
  <button class="btn btn-primary">
    <i class="bi bi-save"></i> Salvar
  </button>
</div>
