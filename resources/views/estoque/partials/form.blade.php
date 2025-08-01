<div class="card p-4 shadow-sm">
    <div class="row g-4">
        <!-- Código -->
        <div class="col-md-6">
            <label for="codigo" class="form-label">Código</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
                <input type="text"
                       name="codigo"
                       id="codigo"
                       class="form-control"
                       value="{{ old('codigo') }}">
            </div>
        </div>

        <!-- Descrição -->
        <div class="col-md-6">
            <label for="descricao" class="form-label">Descrição</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                <input type="text"
                       name="descricao"
                       id="descricao"
                       class="form-control"
                       value="{{ old('descricao') }}">
            </div>
        </div>

        <!-- Quantidade -->
        <div class="col-md-4">
            <label for="quantidade" class="form-label">Quantidade</label>
            <input type="number"
                   name="quantidade"
                   id="quantidade"
                   class="form-control"
                   value="{{ old('quantidade') }}">
        </div>

        <!-- Preço (R$) -->
        <div class="col-md-4">
            <label for="preco_rs" class="form-label">Preço (R$)</label>
            <input type="number"
                   step="0.01"
                   name="preco_rs"
                   id="preco_rs"
                   class="form-control no-spin"
                   value="{{ old('preco_rs') }}">
        </div>

        <!-- Medida -->
        <div class="col-md-4">
            <label for="medida" class="form-label">Medida</label>
            <input type="text"
                   name="medida"
                   id="medida"
                   class="form-control"
                   value="{{ old('medida') }}">
        </div>
    </div>
</div>
