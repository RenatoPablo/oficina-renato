document.addEventListener('input', (e) => {
  const el = e.target;
  if (!el.classList.contains('money')) return; // genérico agora

  let raw = el.value.replace(/\D/g, '');
  if (raw === '') {
    el.value = '0,00';
  } else {
    const int = raw.slice(0, -2) || '0';
    const dec = raw.slice(-2);
    el.value = parseInt(int, 10).toString() + ',' + dec;
  }
});
