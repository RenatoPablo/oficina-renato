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
                       value="{{ old('descricao') }}"
                       placeholder="Digite a descrição do seu serviço">
            </div>
        </div>

        <!-- valor_unit -->
        <!-- Preço (R$) -->
        <div class="col-md-4">
            <label for="preco_rs" class="form-label">Valor Unitário (R$)</label>
            <input type="text"
                   name="valor_unitario"
                   id="valor_unitario"
                   class="form-control no-spin money"
                   value="{{ old('valor_unitario') }}"
                   placeholder="0,00">
        </div>
    </div>
</div>