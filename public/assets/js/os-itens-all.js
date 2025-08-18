// public/assets/js/os-itens-all.js
(() => {
  const $ = (s, r=document) => r.querySelector(s);
  const $$ = (s, r=document) => Array.from(r.querySelectorAll(s));

  // ========== helpers ==========
  const toFloat = (v) => parseFloat((v || '').toString().replace(',', '.')) || 0;

  function recalcRow(tr){
    const qtd  = toFloat($('input[name$="[qtd]"]', tr)?.value);
    const unit = toFloat($('input[name$="[valor_unit]"]', tr)?.value);
    const total = (qtd * unit).toFixed(2);
    const totalInput = $('input[name$="[valor_total]"]', tr);
    if (totalInput) totalInput.value = total;
  }

  function calcSubtotals(){
    // serviços
    let subtotalServ = 0;
    $$('#tbl-servicos tbody tr').forEach(tr => {
      subtotalServ += toFloat($('input[name$="[valor_total]"]', tr)?.value);
    });
    $('#subtotal-servicos').value = subtotalServ.toFixed(2);

    // peças
    let subtotalPec = 0;
    $$('#tbl-pecas tbody tr').forEach(tr => {
      subtotalPec += toFloat($('input[name$="[valor_total]"]', tr)?.value);
    });
    $('#subtotal-pecas').value = subtotalPec.toFixed(2);

    // total OS
    const frete = toFloat($('#frete')?.value);
    const total = subtotalServ + subtotalPec + frete;
    $('#total-os').textContent = total.toFixed(2);
  }

  function nextIndex(tbody, prefix){
    // pega maior índice atual (servicos[X] / pecas[X])
    let max = -1;
    $$('input,select', tbody).forEach(el => {
      const n = el.name || '';
      const m = n.match(new RegExp(`^${prefix}\\[(\\d+)\\]`));
      if (m) max = Math.max(max, parseInt(m[1], 10));
    });
    return max + 1;
  }

  function optionHTML(select, valueAttr='value'){
    return Array.from(select.options).map(o => {
      const val = o.getAttribute(valueAttr);
      const preco = o.dataset.preco ?? '0.00';
      const text = o.textContent.trim();
      return `<option value="${val}" data-preco="${preco}">${text}</option>`;
    }).join('');
  }

  function buildServicoRow(idx, optionsHTML){
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>
        <select name="servicos[${idx}][servico_id]" class="form-select">
          ${optionsHTML}
        </select>
      </td>
      <td><input name="servicos[${idx}][qtd]" type="number" step="1" class="form-control" value="1"></td>
      <td><input name="servicos[${idx}][valor_unit]" type="number" step="0.01" class="form-control" value="0.00"></td>
      <td><input name="servicos[${idx}][valor_total]" type="number" step="0.01" class="form-control" value="0.00" readonly></td>
      <td class="text-end">
        <button type="button" class="btn btn-outline-danger btn-sm btn-remove-line">Remover</button>
      </td>`;
    return tr;
  }

  function buildPecaRow(idx, optionsHTML){
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>
        <select name="pecas[${idx}][estoque_id]" class="form-select">
          ${optionsHTML}
        </select>
      </td>
      <td><input name="pecas[${idx}][qtd]" type="number" step="0.01" class="form-control" value="1"></td>
      <td><input name="pecas[${idx}][valor_unit]" type="number" step="0.01" class="form-control" value="0.00"></td>
      <td><input name="pecas[${idx}][valor_total]" type="number" step="0.01" class="form-control" value="0.00" readonly></td>
      <td class="text-end">
        <button type="button" class="btn btn-outline-danger btn-sm btn-remove-line">Remover</button>
      </td>`;
    return tr;
  }

  function wireRowBehaviors(container){
    // preencher valor_unit ao trocar item
    container.addEventListener('change', (ev) => {
      const sel = ev.target;
      if (!(sel instanceof HTMLSelectElement)) return;
      if (!/\[(servico_id|estoque_id)\]$/.test(sel.name)) return;

      const opt = sel.selectedOptions[0];
      const preco = toFloat(opt?.dataset?.preco);
      const tr = sel.closest('tr');
      const unit = $('input[name$="[valor_unit]"]', tr);
      if (unit && (toFloat(unit.value) === 0)) {
        unit.value = preco.toFixed(2);
      }
      recalcRow(tr);
      calcSubtotals();
    });

    // recalc em qtd / valor_unit
    container.addEventListener('input', (ev) => {
      const el = ev.target;
      if (!(el instanceof HTMLInputElement)) return;
      if (!/\[(qtd|valor_unit)\]$/.test(el.name)) return;
      const tr = el.closest('tr');
      recalcRow(tr);
      calcSubtotals();
    });

    // remover linha
    container.addEventListener('click', (ev) => {
      const btn = ev.target.closest('.btn-remove-line');
      if (!btn) return;
      btn.closest('tr')?.remove();
      calcSubtotals();
    });
  }

  // ===== serviços =====
  const tblServ = $('#tbl-servicos'); const bodyServ = $('tbody', tblServ);
  const addServ = $('#add-servico');
  const masterServ = $('#servico_master');
  const selServ = $('#sel-servico'); const buscaServ = $('#busca-servico');

  if (tblServ && addServ && masterServ){
    addServ.addEventListener('click', () => {
      const idx = nextIndex(bodyServ, 'servicos');
      const tr = buildServicoRow(idx, optionHTML(masterServ));
      bodyServ.appendChild(tr);
      calcSubtotals();
    });

    // filtro simples: esconde options que não batem com a busca
    if (buscaServ && selServ){
      buscaServ.addEventListener('input', () => {
        const q = buscaServ.value.toLowerCase();
        Array.from(selServ.options).forEach(o => {
          if (!o.value) return; // deixa placeholder
          o.hidden = !o.textContent.toLowerCase().includes(q);
        });
      });
    }
  }

  // ===== peças =====
  const tblPec = $('#tbl-pecas'); const bodyPec = $('tbody', tblPec);
  const addPec = $('#add-peca');
  const masterPec = $('#peca_master');
  const selPec = $('#sel-peca'); const buscaPec = $('#busca-peca');

  if (tblPec && addPec && masterPec){
    addPec.addEventListener('click', () => {
      const idx = nextIndex(bodyPec, 'pecas');
      const tr = buildPecaRow(idx, optionHTML(masterPec));
      bodyPec.appendChild(tr);
      calcSubtotals();
    });

    if (buscaPec && selPec){
      buscaPec.addEventListener('input', () => {
        const q = buscaPec.value.toLowerCase();
        Array.from(selPec.options).forEach(o => {
          if (!o.value) return;
          o.hidden = !o.textContent.toLowerCase().includes(q);
        });
      });
    }
  }

  // ligar comportamentos e calcular totais da tela atual
  wireRowBehaviors(document);
  // recalcula para linhas existentes
  $$('#tbl-servicos tbody tr, #tbl-pecas tbody tr').forEach(recalcRow);
  calcSubtotals();

  // recalcular total quando mexer no frete
  $('#frete')?.addEventListener('input', calcSubtotals);
})();
