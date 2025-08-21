// public/assets/js/os-itens-all.js
(() => {
  // ===== helpers =====
  const $  = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));

  const toNumber = (str) => {
    if (str == null) return 0;
    let s = String(str).replace(/[^\d,\.]/g, '');
    if (s.includes(',')) s = s.replace(/\./g, '').replace(',', '.');
    const n = parseFloat(s);
    return Number.isFinite(n) ? n : 0;
  };

  const fmtBR = (n, dec = 2) =>
    new Intl.NumberFormat('pt-BR', { minimumFractionDigits: dec, maximumFractionDigits: dec }).format(n);

  // ===== cálculo de linha / totais =====
  function recalcRow(tr) {
    const qtdEl   = $('input[name$="[qtd]"]', tr);
    const unitEl  = $('input[name$="[valor_unit]"]', tr);
    const totalEl = $('input[name$="[valor_total]"]', tr);
    if (!qtdEl || !unitEl || !totalEl) return;

    // blindagem: total sempre text
    if (totalEl.type === 'number') totalEl.type = 'text';

    const qtd  = toNumber(qtdEl.value);
    const unit = toNumber(unitEl.value);
    const tot  = qtd * unit;

    totalEl.value = fmtBR(tot, 2); // exibe com vírgula, 2 casas
  }

  function calcSubtotals() {
    let subtotalServ = 0, subtotalPec = 0;

    $$('.servicos-section [name$="[valor_total]"]').forEach(el => subtotalServ += toNumber(el.value));
    $$('.pecas-section    [name$="[valor_total]"]').forEach(el => subtotalPec  += toNumber(el.value));

    const frete = toNumber($('#frete')?.value || 0);

    const subServEl  = $('#subtotal-servicos');
    const subPecasEl = $('#subtotal-pecas');
    const totalOsEl  = $('#total-os');

    if (subServEl)  subServEl.value  = fmtBR(subtotalServ, 2);
    if (subPecasEl) subPecasEl.value = fmtBR(subtotalPec , 2);
    if (totalOsEl)  totalOsEl.textContent = fmtBR(subtotalServ + subtotalPec + frete, 2);
  }

  // ===== util: próximo índice para name="servicos[idx][...]" / "pecas[idx][...]"
  function nextIndex(tbody, prefix) {
    let max = -1;
    $$('input,select', tbody).forEach(el => {
      const n = el.name || '';
      const m = n.match(new RegExp(`^${prefix}\\[(\\d+)\\]`));
      if (m) max = Math.max(max, parseInt(m[1], 10));
    });
    return max + 1;
  }

  // ===== util: options HTML a partir do select template (com data-preco)
  function optionHTML(select, valueAttr = 'value') {
    return Array.from(select.options).map(o => {
      const val   = o.getAttribute(valueAttr);
      const preco = o.dataset.preco ?? '0.00';
      const text  = o.textContent.trim();
      return `<option value="${val}" data-preco="${preco}">${text}</option>`;
    }).join('');
  }

  // ===== builders de linha (já com types corretos + .money) =====
  function buildServicoRow(idx, optionsHTML) {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>
        <select name="servicos[${idx}][servico_id]" class="form-select">
          ${optionsHTML}
        </select>
      </td>
      <td><input name="servicos[${idx}][qtd]" type="number" step="1" class="form-control" value="1"></td>
      <td><input name="servicos[${idx}][valor_unit]" type="text" class="form-control money" inputmode="decimal" value="0,00"></td>
      <td><input name="servicos[${idx}][valor_total]" type="text" class="form-control money" inputmode="decimal" value="0,00" readonly></td>
      <td class="text-end">
        <button type="button" class="btn btn-outline-danger btn-sm btn-remove-line">Remover</button>
      </td>`;
    return tr;
  }

  function buildPecaRow(idx, optionsHTML) {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>
        <select name="pecas[${idx}][estoque_id]" class="form-select">
          ${optionsHTML}
        </select>
      </td>
      <td><input name="pecas[${idx}][qtd]" type="number" step="1" class="form-control" value="1"></td>
      <td><input name="pecas[${idx}][valor_unit]" type="text" class="form-control money" inputmode="decimal" value="0,00"></td>
      <td><input name="pecas[${idx}][valor_total]" type="text" class="form-control money" inputmode="decimal" value="0,00" readonly></td>
      <td class="text-end">
        <button type="button" class="btn btn-outline-danger btn-sm btn-remove-line">Remover</button>
      </td>`;
    return tr;
  }

  // ===== máscara em tempo real (SOMENTE valor_unit) =====
  document.addEventListener('input', (e) => {
    const el = e.target;
    if (!el.name || !el.name.endsWith('[valor_unit]')) return;

    // só dígitos
    let raw = el.value.replace(/\D/g, '');
    if (raw === '') {
      el.value = '0,00';
    } else {
      const int = raw.slice(0, -2) || '0';
      const dec = raw.slice(-2);
      el.value = parseInt(int, 10).toString() + ',' + dec; // sempre vírgula + 2 casas
    }

    const tr = el.closest('tr');
    if (tr) recalcRow(tr);
    calcSubtotals();
  });

  // ===== eventos de UI (change/input/click/submit) =====
  function wireBehaviors(root) {
    // troca de item → preenche preço padrão do option (data-preco)
    root.addEventListener('change', (ev) => {
      const sel = ev.target;
      if (!(sel instanceof HTMLSelectElement)) return;
      if (!/\[(servico_id|estoque_id)\]$/.test(sel.name)) return;

      const opt   = sel.selectedOptions[0];
      const preco = opt?.dataset?.preco;
      if (preco == null) return;

      const tr     = sel.closest('tr');
      const unitEl = $('input[name$="[valor_unit]"]', tr);
      if (unitEl) {
        // aplica com vírgula
        unitEl.value = fmtBR(toNumber(preco), 2).replace(/\./g, '').replace(',', ','); // garante 2 casas
        recalcRow(tr);
        calcSubtotals();
      }
    });

    // recalc quando mexer em quantidade manualmente
    root.addEventListener('input', (ev) => {
      const el = ev.target;
      if (!(el instanceof HTMLInputElement)) return;
      if (!/\[(qtd)\]$/.test(el.name)) return;
      const tr = el.closest('tr');
      if (tr) recalcRow(tr);
      calcSubtotals();
    });

    // remover linha
    root.addEventListener('click', (ev) => {
      const btn = ev.target.closest('.btn-remove-line');
      if (!btn) return;
      btn.closest('tr')?.remove();
      calcSubtotals();
    });
  }

  // ===== inicialização das seções =====
  const tblServ    = $('#tbl-servicos');
  const bodyServ   = $('tbody', tblServ);
  const addServBtn = $('#add-servico');
  const masterServ = $('#servico_master');

  const tblPec    = $('#tbl-pecas');
  const bodyPec   = $('tbody', tblPec);
  const addPecBtn = $('#add-peca');
  const masterPec = $('#peca_master');

  // adicionar linha serviço
  if (tblServ && addServBtn && masterServ) {
    addServBtn.addEventListener('click', () => {
      const idx = nextIndex(bodyServ, 'servicos');
      const tr  = buildServicoRow(idx, optionHTML(masterServ));
      bodyServ.appendChild(tr);
      recalcRow(tr);
      calcSubtotals();
    });
  }

  // adicionar linha peça
  if (tblPec && addPecBtn && masterPec) {
    addPecBtn.addEventListener('click', () => {
      const idx = nextIndex(bodyPec, 'pecas');
      const tr  = buildPecaRow(idx, optionHTML(masterPec));
      bodyPec.appendChild(tr);
      recalcRow(tr);
      calcSubtotals();
    });
  }

  // ligar comportamentos gerais
  wireBehaviors(document);

  // primeira passada: alinhar linhas que vieram do servidor
  $$('#tbl-servicos tbody tr, #tbl-pecas tbody tr').forEach(recalcRow);
  calcSubtotals();

  // frete recalcula total
  $('#frete')?.addEventListener('input', calcSubtotals);

  // normalização antes de enviar (backend recebe com ponto)
  document.addEventListener('submit', (e) => {
    const form = e.target;
    if (!(form instanceof HTMLFormElement)) return;

    form.querySelectorAll('input.money').forEach((el) => {
      const isUnit = /\[valor_unit\]$/.test(el.name);
      const n = toNumber(el.value);
      const dec = isUnit ? 2 : 2; // unitário: 2 casas (se quiser 5 pra peça, mude aqui)
      el.value = n.toFixed(dec);
    });
  }, true);
})();
