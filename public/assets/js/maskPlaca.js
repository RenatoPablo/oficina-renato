document.addEventListener("DOMContentLoaded", function () {
    const el = document.getElementById('placa');
    if (!el) return;

    el.addEventListener('input', () => {
        // Remove tudo que não for letra/número e põe em maiúsculo
        let raw = el.value.toUpperCase().replace(/[^A-Z0-9]/g, '');

        // Limita a 7 caracteres crus
        if (raw.length > 7) raw = raw.slice(0, 7);

        // Se for placa antiga (AAA1234) → insere hífen
        if (/^[A-Z]{3}\d{4}$/.test(raw)) {
            el.value = raw.slice(0, 3) + '-' + raw.slice(3);
        } else {
            // Mercosul (AAA1A23) ou incompleta → sem hífen
            el.value = raw;
        }
    });
});
