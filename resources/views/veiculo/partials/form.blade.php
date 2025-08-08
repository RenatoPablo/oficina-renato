<div class="card p-4 shadow-sm">
    <div class="row g-4">
        {{-- tipo --}}
        <div class="col-md-6">
            <label for="tipo" class="form-label">Tipo de Veiculo</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-car-front-fill"></i></span>
                <input type="text" 
                       name="tipo" 
                       id="tipo" 
                       class="form-control"
                       value="{{ old('tipo') }}">
            </div>
        </div>

        {{-- marca --}}
        <div class="col-md-6">
            <label for="marca" class="form-label">Marca do Veiculo</label>
            <input type="text" class="form-control"
                   name="marca"
                   id="marca"
                   value="{{ old('marca') }}">
        </div>

        {{-- modelo --}}
        <div class="col-md-6">
            <label for="modelo" class="form-label">Modelo do Veiculo</label>
            <input type="text" 
                   class="form-control"
                   name="modelo"
                   id="modelo"
                   value="{{ old('modelo') }}">
        </div>

        {{-- Placa --}}
        <div class="col-md-6">
            <label for="placa" class="form-label">Placa do Veículo</label>
            <input type="text" 
                class="form-control text-uppercase"
                name="placa"
                id="placa"
                value="{{ old('placa') }}"
                placeholder="AAA-1234 ou AAA1A23"
                autocomplete="off">
                
        </div>


        {{-- km --}}
        <div class="col-md-6">
            <label for="km" class="form-label">KM do Veiculo</label>
            <input type="number" 
                   class="form-control no-spin"
                   name="km"
                   id="km"
                   value="{{ old('km') }}">
        </div>

        {{-- ano --}}
        <div class="col-md-6">
            <label for="ano" class="form-label">Ano do Veiculo</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-calendar-fill"></i></span>
                <input type="number" 
                       class="form-control no-spin"
                       name="ano"
                       id="ano"
                       value="{{ old('ano') }}">
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
                        <option value="{{ $cliente->id }}" {{ old('cliente_id', $veiculo->cliente_id ?? '') == $cliente->id ? 'selected' : '' }}>
                            {{ $cliente->nome }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Desassociar cliente --}}
        @if (!empty($veiculo->cliente_id))
            <div class="col-md-6 d-flex align-items-end">
                <form action="{{ route('veiculo.desassociar.cliente', ['id' => Crypt::encrypt($veiculo->id)]) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="bi bi-person-dash"></i> Desassociar Cliente
                    </button>
                </form>
            </div>
        @endif

    </div>
</div>
