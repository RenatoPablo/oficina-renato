<div class="card p-4 shadow-sm">
    <div class="row g-4">

        <!-- ID oculto -->
        <input type="hidden" name="id" value="{{ $estoque->id }}">

        <!-- Código -->
        <div class="col-md-4">
            <label for="codigo" class="form-label">Código</label>
            <input type="text"
                   name="codigo"
                   id="codigo"
                   class="form-control"
                   value="{{ old('codigo', $estoque->codigo) }}">
        </div>

        <!-- Descrição -->
        <div class="col-md-8">
            <label for="descricao" class="form-label">Descrição</label>
            <input type="text"
                   name="descricao"
                   id="descricao"
                   class="form-control"
                   value="{{ old('descricao', $estoque->descricao) }}">
        </div>

        <!-- Quantidade -->
        <div class="col-md-4">
            <label for="quantidade" class="form-label">Quantidade</label>
            <input type="number"
                   name="quantidade"
                   id="quantidade"
                   class="form-control"
                   step="1"
                   value="{{ old('quantidade', $estoque->quantidade) }}">
        </div>

        <!-- Preço (R$) -->
        <div class="col-md-4">
            <label for="preco_rs" class="form-label no-spin">Preço (R$)</label>
            <input type="number"
                   step="0.01"
                   name="preco_rs"
                   id="preco_rs"
                   class="form-control"
                   value="{{ old('preco_rs', $estoque->preco_rs) }}">
        </div>

        <!-- Medida -->
        <div class="col-md-4">
            <label for="medida" class="form-label">Medida</label>
            <input type="text"
                   name="medida"
                   id="medida"
                   class="form-control"
                   value="{{ old('medida', $estoque->medida) }}">
        </div>
    </div>
</div>
