<div class="card p-4 shadow-sm">
    <div class="row g-4">
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

        <!-- valor_unit -->
        <!-- Preço (R$) -->
        <div class="col-md-4">
            <label for="preco_rs" class="form-label">Valor Unitário (R$)</label>
            <input type="number"
                   step="0.01"
                   name="valor_unitario"
                   id="valor_unitario"
                   class="form-control no-spin"
                   value="{{ old('valor_unitario') }}">
        </div>
    </div>
</div>