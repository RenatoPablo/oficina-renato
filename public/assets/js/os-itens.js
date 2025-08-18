// public/assets/js/os-itens.js

(function () {
  // ===== helpers =====
  const q = (sel, root = document) => root.querySelector(sel);
  const qa = (sel, root = document) => Array.from(root.querySelectorAll(sel));

  function recalcRow(tr) {
    const qtd = parseFloat(q('input[name$="[qtd]"]', tr)?.value || '0');
    const unit = parseFloat(q('input[name$="[valor_unit]"]', tr)?.value || '0');
    const total = (qtd * unit) || 0;
    const totalInput = q('input[name$="[valor_total]"]', tr);
    if (totalInput) totalInput.value = total.toFixed(2);
  }

  function nextIndex(tbody) {
    // pega o maior índice [itens[X]] da tabela e soma 1
    let max = -1;
    qa('input, select', tbody).forEach(el => {
      const n = el.name || '';
      const m = n.match(/\[([0-9]+)\]/);
      if (m) max = Math.max(max, parseInt(m[1], 10));
    });
    return max + 1;
  }

  function buildServicoRow(idx, optionsHTML) {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>
        <select name="itens[${idx}][servico_id]" class="form-select">
          ${optionsHTML}
        </select>
      </td>
      <td><input name="itens[${idx}][qtd]" type="number" step="1" class="form-control" value="1"></td>
      <td><input name="itens[${idx}][valor_unit]" type="number" step="0.01" class="form-control" value="0"></td>
      <td><input name="itens[${idx}][valor_total]" type="number" step="0.01" class="form-control" value="0" readonly></td>
      <td class="text-end">
        <button type="button" class="btn btn-outline-danger btn-sm btn-remove-line">Remover</button>
      </td>
    `;
    return tr;
  }

  function buildPecaRow(idx, optionsHTML) {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>
        <select name="itens[${idx}][estoque_id]" class="form-select">
          ${optionsHTML}
        </select>
      </td>
      <td><input name="itens[${idx}][qtd]" type="number" step="0.01" class="form-control" value="1"></td>
      <td><input name="itens[${idx}][valor_unit]" type="number" step="0.01" class="form-control" value="0"></td>
      <td><input name="itens[${idx}][valor_total]" type="number" step="0.01" class="form-control" value="0" readonly></td>
      <td class="text-end">
        <button type="button" class="btn btn-outline-danger btn-sm btn-remove-line">Remover</button>
      </td>
    `;
    return tr;
  }

  // Preenche valor unitário quando selecionar item (usa data-preco)
  function hookSelectPrice(container, selectNameEndsWith) {
    container.addEventListener('change', (ev) => {
      const sel = ev.target;
      if (!(sel instanceof HTMLSelectElement)) return;
      if (!sel.name.endsWith(selectNameEndsWith)) return;

      const opt = sel.selectedOptions[0];
      const preco = parseFloat(opt?.dataset?.preco || '0');
      const tr = sel.closest('tr');
      const unit = q('input[name$="[valor_unit]"]', tr);
      if (unit && (unit.value === '' || parseFloat(unit.value) === 0)) {
        unit.value = preco.toFixed(2);
      }
      recalcRow(tr);
    });
  }

  // Recalcula quando mudar qtd ou unit
  function hookRecalcOnInputs(container) {
    container.addEventListener('input', (ev) => {
      const el = ev.target;
      if (!(el instanceof HTMLInputElement)) return;
      if (!/\[(qtd|valor_unit)\]$/.test(el.name)) return;
      const tr = el.closest('tr');
      recalcRow(tr);
    });
  }

  // Remoção de linha (DOM). Para itens já persistidos, o botão original com name="remove[]" continua funcionando no submit.
  function hookRemoveButtons(container) {
    container.addEventListener('click', (ev) => {
      const btn = ev.target.closest('.btn-remove-line');
      if (!btn) return;
      const tr = btn.closest('tr');
      tr?.remove();
    });
  }

  // ====== Serviços ======
  const tblServ = q('#tbl-servicos');
  const btnAddServ = q('#add-servico');
  const servOpts = q('#servico_master'); // hidden select com options (ver instrução abaixo)

  if (tblServ && btnAddServ && servOpts) {
    const tbody = q('tbody', tblServ);

    btnAddServ.addEventListener('click', () => {
      const idx = nextIndex(tbody);
      const tr = buildServicoRow(idx, servOpts.innerHTML);
      tbody.appendChild(tr);
    });

    hookSelectPrice(tblServ, '[servico_id]');
    hookRecalcOnInputs(tblServ);
    hookRemoveButtons(tblServ);

    // recalcula linhas existentes ao carregar
    qa('tbody tr', tblServ).forEach(recalcRow);
  }

  // ====== Peças ======
  const tblPec = q('#tbl-pecas');
  const btnAddPec = q('#add-peca');
  const pecOpts = q('#peca_master'); // hidden select com options (ver instrução abaixo)

  if (tblPec && btnAddPec && pecOpts) {
    const tbody = q('tbody', tblPec);

    btnAddPec.addEventListener('click', () => {
      const idx = nextIndex(tbody);
      const tr = buildPecaRow(idx, pecOpts.innerHTML);
      tbody.appendChild(tr);
    });

    hookSelectPrice(tblPec, '[estoque_id]');
    hookRecalcOnInputs(tblPec);
    hookRemoveButtons(tblPec);

    qa('tbody tr', tblPec).forEach(recalcRow);
  }
})();
