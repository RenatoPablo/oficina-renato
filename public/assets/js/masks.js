document.addEventListener('DOMContentLoaded', function () {
    // Aplica uma máscara ao input com base no pattern fornecido
    const formatInput = (input, pattern) => {
        input.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            let i = 0;
            let maskedValue = pattern.replace(/#/g, () => value[i++] || '');
            e.target.value = maskedValue;
        });

        // Permite apagar caracteres de formatação
        input.addEventListener('keydown', function (e) {
            if (['Backspace', 'Delete'].includes(e.key)) {
                const pos = input.selectionStart;
                const val = input.value;
                const before = val.slice(0, pos);
                if (/\D$/.test(before)) {
                    e.preventDefault();
                    const newVal = val.slice(0, pos - 1) + val.slice(pos);
                    input.value = newVal;
                    input.setSelectionRange(pos - 1, pos - 1);
                }
            }
        });
    };

    // Remove qualquer formatação para envio limpo
    const limparMascara = valor => valor.replace(/\D/g, '');

    // CPF/CNPJ
    const cpfCnpj = document.getElementById('cnpj_cpf');
    if (cpfCnpj) {
        cpfCnpj.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                value = value.replace(/^(\d{3})(\d)/, '$1.$2');
                value = value.replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3');
                value = value.replace(/\.(\d{3})(\d)/, '.$1-$2');
            } else {
                value = value.replace(/^(\d{2})(\d)/, '$1.$2');
                value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
                value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
                value = value.replace(/(\d{4})(\d)/, '$1-$2');
            }
            e.target.value = value;
        });
    }

    // IE/RG – permite apenas letras, números e espaço
    const ieRg = document.getElementById('ie_rg');
    if (ieRg) {
        ieRg.addEventListener('input', function (e) {
            e.target.value = e.target.value.replace(/[^\w\s]/gi, '');
        });
    }

    // Telefone fixo
    const telefone = document.getElementById('telefone');
    if (telefone) {
        formatInput(telefone, '(##) ####-####');
    }

    // Celular
    const celular = document.getElementById('celular');
    if (celular) {
        formatInput(celular, '(##) #####-####');
    }

    // CEP
    const cep = document.getElementById('cep');
    if (cep) {
        formatInput(cep, '#####-###');
    }

    // Remove máscara antes de enviar
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function () {
            const campos = ['cnpj_cpf', 'ie_rg', 'telefone', 'celular', 'cep'];
            campos.forEach(id => {
                const campo = document.getElementById(id);
                if (campo) {
                    campo.value = limparMascara(campo.value);
                }
            });
        });
    }
});



document.addEventListener('DOMContentLoaded', function () {
// Força a aplicação das máscaras ao carregar os valores já existentes
const camposComMascara = ['cnpj_cpf', 'telefone', 'celular', 'cep', 'ie_rg'];

camposComMascara.forEach(id => {
    const campo = document.getElementById(id);
    if (campo && campo.value) {
        const evento = new Event('input', { bubbles: true });
        campo.dispatchEvent(evento);
    }
});
});