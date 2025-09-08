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
                       value="{{ old('codigo') }}"
                       placeholder="Digite o código do produto">
            </div>
        </div>

        <!-- Descrição -->
        <div class="col-md-6">
            <label for="descricao" class="form-label">Nome do produto</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                <input type="text"
                       name="descricao"
                       id="descricao"
                       class="form-control"
                       value="{{ old('descricao') }}"
                       placeholder="Digite o nome do produto">
            </div>
        </div>

        <!-- Quantidade -->
        <div class="col-md-4">
            <label for="quantidade" class="form-label">Quantidade</label>
            <input type="number"
                   name="quantidade"
                   id="quantidade"
                   class="form-control no-spin"
                   value="{{ old('quantidade') }}"
                   placeholder="0">
        </div>

        <!-- Preço (R$) -->
        <div class="col-md-4">
            <label for="preco_rs" class="form-label">Preço (R$)</label>
            <input type="text"
                   name="preco_rs"
                   id="preco_rs"
                   class="form-control money"
                   value="{{ old('preco_rs') }}"
                   placeholder="0,00">
        </div>

        <!-- Medida -->
        <div class="col-md-4">
            <label for="medida" class="form-label">Medida</label>
            <input type="text"
                   name="medida"
                   id="medida"
                   class="form-control"
                   value="{{ old('medida') }}"
                   placeholder="Unidade, Peso, Litro, etc...">
        </div>
    </div>
</div>
