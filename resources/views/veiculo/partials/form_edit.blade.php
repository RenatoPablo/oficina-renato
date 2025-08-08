<div class="card p-4 shadow-sm">
    <div class="row g-4">
        {{-- Hidden ID --}}
        <input type="hidden" name="id" value="{{ $veiculo->id }}">

        {{-- tipo --}}
        <div class="col-md-6">
            <label for="tipo" class="form-label">Tipo de Veículo</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-car-front-fill"></i></span>
                <input type="text"
                       name="tipo"
                       id="tipo"
                       class="form-control"
                       value="{{ old('tipo', $veiculo->tipo) }}">
            </div>
        </div>

        {{-- marca --}}
        <div class="col-md-6">
            <label for="marca" class="form-label">Marca do Veículo</label>
            <input type="text"
                   class="form-control"
                   name="marca"
                   id="marca"
                   value="{{ old('marca', $veiculo->marca) }}">
        </div>

        {{-- modelo --}}
        <div class="col-md-6">
            <label for="modelo" class="form-label">Modelo do Veículo</label>
            <input type="text"
                   class="form-control"
                   name="modelo"
                   id="modelo"
                   value="{{ old('modelo', $veiculo->modelo) }}">
        </div>

        {{-- Placa --}}
        <div class="col-md-6">
            <label for="placa" class="form-label">Placa do Veículo</label>
            <input type="text" 
                class="form-control text-uppercase"
                name="placa"
                id="placa"
                value="{{ old('placa'), $veiculo->placa }}"
                placeholder="AAA-1234 ou AAA1A23"
                autocomplete="off">
        </div>


        {{-- km --}}
        <div class="col-md-6">
            <label for="km" class="form-label">KM do Veículo</label>
            <input type="number"
                   class="form-control no-spin"
                   name="km"
                   id="km"
                   value="{{ old('km', $veiculo->km) }}">
        </div>

        {{-- ano --}}
        <div class="col-md-6">
            <label for="ano" class="form-label">Ano do Veículo</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-calendar-fill"></i></span>
                <input type="number"
                       class="form-control no-spin"
                       name="ano"
                       id="ano"
                       value="{{ old('ano', $veiculo->ano) }}">
            </div>
        </div>

        {{-- Cliente --}}
        <div class="col-md-6">
            <label for="cliente_id" class="form-label">Cliente</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person"></i></span>
                <select name="cliente_id" id="cliente_id" class="form-select">
                    <option value="">-- Selecionar cliente --</option>
                    @foreach ($clientes as $cliente)
                        <option value="{{ $cliente->id }}"
                            @selected(
                                old('cliente_id') == $cliente->id ||
                                (empty(old('cliente_id')) &&
                                isset($clienteVeiculo) &&
                                $clienteVeiculo->cliente_id == $cliente->id)
                            )
                        >
                            {{ $cliente->nome }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>


        <div class="col-md-6">
            <label class="form-label">Status da Associação</label>

            <div class="d-flex align-items-center gap-3">
                {{-- Badge de status --}}
                @if ($clienteVeiculo && $clienteVeiculo->ativo)
                    <span class="badge bg-success px-3 py-2 fs-6 rounded-pill shadow-sm">Ativo</span>
                @else
                    <span class="badge bg-danger px-3 py-2 fs-6 rounded-pill shadow-sm">Desassociado</span>
                @endif

                {{-- Botão de histórico com ícone --}}
                <a href="{{ route('veiculo.historico.proprietario', ['id' => Crypt::encrypt($veiculo->id)])}}" class="btn btn-outline-secondary d-flex align-items-center gap-1 py-1 px-2 shadow-sm" style="font-size: 0.9rem;">
                    <i class="bi bi-eye-fill"></i>
                    Histórico de proprietários
                </a>
            </div>
        </div>



    </div>
</div>
