<div class="card p-4 shadow-sm">
    <div class="row g-4">
        <!-- Nome -->
        <div class="col-md-6">
            <label for="nome" class="form-label">Nome</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person"></i></span>
                <input type="text"
                       name="nome"
                       id="nome"
                       class="form-control"
                       value="{{ old('nome') }}">
            </div>
        </div>

        <!-- Contato -->
        <div class="col-md-6">
            <label for="contato" class="form-label">Contato</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                <input type="text"
                       name="contato"
                       id="contato"
                       class="form-control"
                       value="{{ old('contato') }}">
            </div>
        </div>

        <!-- IE/RG -->
        <div class="col-md-4">
            <label for="ie_rg" class="form-label">IE/RG</label>
            <input type="text"
                   name="ie_rg"
                   id="ie_rg"
                   class="form-control"
                   value="{{ old('ie_rg') }}">
        </div>

        <!-- CNPJ/CPF -->
        <div class="col-md-4">
            <label for="cnpj_cpf" class="form-label">CNPJ/CPF</label>
            <input type="text"
                   name="cnpj_cpf"
                   id="cnpj_cpf"
                   class="form-control"
                   value="{{ old('cnpj_cpf') }}">
        </div>

        <!-- Endereço -->
        <div class="col-md-4">
            <label for="endereco" class="form-label">Endereço</label>
            <input type="text"
                   name="endereco"
                   id="endereco"
                   class="form-control"
                   value="{{ old('endereco') }}">
        </div>

        <!-- Bairro -->
        <div class="col-md-4">
            <label for="bairro" class="form-label">Bairro</label>
            <input type="text"
                   name="bairro"
                   id="bairro"
                   class="form-control"
                   value="{{ old('bairro') }}">
        </div>

        <!-- Município -->
        <div class="col-md-4">
            <label for="municipio" class="form-label">Município</label>
            <input type="text"
                   name="municipio"
                   id="municipio"
                   class="form-control"
                   value="{{ old('municipio') }}">
        </div>

        <!-- UF -->
        <div class="col-md-2">
            <label for="uf" class="form-label">UF</label>
            <input type="text"
                   name="uf"
                   id="uf"
                   maxlength="2"
                   class="form-control"
                   value="{{ old('uf') }}">
        </div>

        <!-- CEP -->
        <div class="col-md-2">
            <label for="cep" class="form-label">CEP</label>
            <input type="text"
                   name="cep"
                   id="cep"
                   class="form-control"
                   value="{{ old('cep') }}">
        </div>

        <!-- Telefone -->
        <div class="col-md-4">
            <label for="telefone" class="form-label">Telefone (Fixo)</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-telephone-fill"></i></span>
                <input type="text"
                       name="telefone"
                       id="telefone"
                       class="form-control"
                       value="{{ old('telefone') }}">
            </div>
        </div>

        <!-- Celular -->
        <div class="col-md-4">
            <label for="celular" class="form-label">Celular</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-phone"></i></span>
                <input type="text"
                       name="celular"
                       id="celular"
                       class="form-control"
                       value="{{ old('celular') }}">
            </div>
        </div>

        <!-- Email — agora com a MESMA largura do Celular -->
        <div class="col-md-4">
            <label for="email" class="form-label">Email</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email"
                       name="email"
                       id="email"
                       class="form-control"
                       value="{{ old('email') }}">
            </div>
        </div>

        <!-- Observação -->
        <div class="col-md-12">
            <label for="observacao" class="form-label">Observação</label>
            <textarea name="observacao"
                      id="observacao"
                      class="form-control"
                      rows="3">{{ old('observacao') }}</textarea>
        </div>

        <!-- Botão removido a pedido -->
    </div>
</div>