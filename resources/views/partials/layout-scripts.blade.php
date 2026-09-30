    <script src="{{ asset('assets/js/alerts.js') }}"></script>
    <script src="{{ asset('assets/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>
    <script src="{{ asset('assets/js/masks.js') }}"></script>
    <script src="{{ asset('assets/js/maskPlaca.js') }}"></script>
    <script src="{{ asset('assets/js/os/os-itens-all.js') }}"></script>
    <script src="{{ asset('assets/js/input_valor.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const cepInput = document.getElementById('cep');
            if (!cepInput) return;

            cepInput.addEventListener('input', function () {
                let cep = this.value.replace(/\D/g, '').slice(0, 8);

                if (cep.length > 5) {
                    cep = cep.replace(/^(\d{5})(\d{1,3})$/, '$1-$2');
                }

                this.value = cep;
            });

            cepInput.addEventListener('blur', async function () {
                const cep = this.value.replace(/\D/g, '');

                if (cep.length !== 8) {
                    this.classList.add('is-invalid');
                    return;
                }

                this.classList.remove('is-invalid');

                try {
                    const response = await fetch(
                        `https://viacep.com.br/ws/${cep}/json/`
                    );

                    if (!response.ok) {
                        throw new Error('Erro ao consultar o CEP');
                    }

                    const dados = await response.json();

                    if (dados.erro) {
                        alert('CEP não encontrado.');
                        this.classList.add('is-invalid');
                        return;
                    }

                    document.getElementById('endereco').value =
                        dados.logradouro ?? '';

                    document.getElementById('bairro').value =
                        dados.bairro ?? '';

                    document.getElementById('municipio').value =
                        dados.localidade ?? '';

                    document.getElementById('uf').value =
                        dados.uf ?? '';
                } catch (error) {
                    console.error(error);
                    alert('Não foi possível consultar o CEP.');
                }
            });
        });
    </script>
