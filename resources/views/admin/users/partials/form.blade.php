<div class="card p-4 shadow-sm">
    <div class="row g-4">
        <!-- Nome -->
        <div class="col-md-6">
            <label for="name" class="form-label">Nome</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                <input type="text"
                       name="name"
                       id="name"
                       class="form-control"
                       value="{{ old('name') }}"
                       placeholder="Digite o nome do usuário">
            </div>
        </div>

        <!-- Email -->
        <div class="col-md-6">
            <label for="email" class="form-label">Email</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope-at"></i></span>
                <input type="email"
                       name="email"
                       id="email"
                       class="form-control"
                       value="{{ old('email') }}"
                       placeholder="Digite o email do usuário">
            </div>
        </div>

        <!-- Senha -->
        <div class="col-md-6">
            <label for="password" class="form-label">Senha</label>
            <div class="input-group">
                <input type="password"
                       name="password"
                       id="password"
                       class="form-control"
                       placeholder="Digite a senha do usuário">
            </div>
        </div>

        <!-- Confirmar senha -->
        <div class="col-md-6">
            <label for="verifyPassword" class="form-label">Confirmar senha</label>
            <div class="input-group">
                <input type="password"
                       name="verifyPassword"
                       id="verifyPassword"
                       class="form-control"
                       placeholder="Confirme a senha do usuário">
                    
            </div>
        </div>

        <!-- Linha separada para is_admin e ativo -->
        <div class="col-md-6">
            <label for="permissao" class="form-label">Permissão</label>
            @php
                $permissoes = [
                    1 => 'Administrador',
                    0 => 'Usuário',
                ];
            @endphp
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person-gear"></i></span>
                <select name="permissao"
                        id="permissao"
                        class="form-select @error('permissao') is-invalid @enderror">
                    @foreach ($permissoes as $valor => $opt)
                        <option value="{{ $valor }}" @selected(old('permissao', 0) === $valor)>
                            {{ $opt }}
                        </option>
                    @endforeach
                </select>
                @error('permissao')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="col-md-6">
            <label for="ativo" class="form-label">Status</label>
            @php
                $statusOpts = [
                    1 => 'Ativo', 
                    0 => 'Inativo'
                ];
            @endphp
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-toggle-on"></i></span>
                <select name="ativo"
                        id="ativo"
                        class="form-select @error('ativo') is-invalid @enderror">
                    @foreach ($statusOpts as $value => $opt)
                        <option value="{{ $value }}" @selected(old('ativo', 1) === $value)>
                            {{ $opt }}
                        </option>
                    @endforeach
                </select>
                @error('ativo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

    </div>
</div>
