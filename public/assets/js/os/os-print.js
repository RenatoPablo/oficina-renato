(() => {
  const scope = document.getElementById('print-scope');
  const original = scope?.querySelector('.os-print-original');
  if (!original) return;

  function reset() {
    scope.querySelector(':scope > .os-print-pages')?.remove();
    scope.classList.remove('os-paginated');
  }

  // Mede o conteúdo com as fontes e dimensões de impressão antes de dividir A4.
  // O total é colocado somente na última folha, usando o espaço restante dela.
  function paginate() {
    reset();
    const pages = document.createElement('div');
    pages.className = 'os-print-pages';
    scope.append(pages);
    scope.classList.add('os-paginated');

    let sheet;
    let content;
    let pageHeight;
    const total = original.querySelector('.os-totals');
    const tables = Array.from(original.querySelectorAll(':scope > .table-wrap'));
    function newPage() {
      sheet = document.createElement('div');
      sheet.className = 'os-print-page';
      content = document.createElement('div');
      content.className = 'os-print-page-content';
      sheet.append(content);
      pages.append(sheet);
      pageHeight = sheet.getBoundingClientRect().height;
    }
    function fits() {
      return sheet.getBoundingClientRect().height <= pageHeight + 0.5;
    }
    function addTable(source, title) {
      const heading = title?.cloneNode(true);
      const wrap = source.cloneNode(true);
      wrap.querySelector('tbody').replaceChildren();
      if (heading) content.append(heading);
      content.append(wrap);
      return { heading, wrap, body: wrap.querySelector('tbody') };
    }

    try {
      newPage();
      const intro = original.querySelector('.os-document-intro');
      if (intro) content.append(intro.cloneNode(true));
      // Textos de identificação excepcionalmente extensos continuam em fluxo
      // natural, sem altura fixa nem corte de conteúdo.
      if (!fits()) {
        reset();
        return;
      }

      tables.forEach((table, tableIndex) => {
        const title = table.previousElementSibling?.matches('h5') ? table.previousElementSibling : null;
        const rows = Array.from(table.querySelectorAll('tbody > tr'));
        let section = addTable(table, title);

        rows.forEach((sourceRow, rowIndex) => {
          const row = sourceRow.cloneNode(true);
          section.body.append(row);
          const lastRow = tableIndex === tables.length - 1 && rowIndex === rows.length - 1;
          // Reserva espaço para os totais junto com o último item, evitando
          // uma folha adicional contendo somente o resumo financeiro.
          const reserve = lastRow && total ? total.cloneNode(true) : null;
          if (reserve) sheet.append(reserve);
          const overflow = !fits();
          reserve?.remove();

          if (overflow) {
            row.remove();
            if (!section.body.children.length) {
              section.heading?.remove();
              section.wrap.remove();
            }
            // Evita uma folha vazia se uma descrição única for muito longa.
            if (content.children.length) newPage();
            else {
              section.heading?.remove();
              section.wrap.remove();
            }
            section = addTable(table, title);
            section.body.append(row);
            if (!fits()) throw new Error('Item maior que a área útil A4.');
          }
        });
      });

      if (total) {
        const footer = total.cloneNode(true);
        sheet.append(footer);
        if (!fits()) {
          footer.remove();
          newPage();
          sheet.append(footer);
        }
      }
    } catch (error) {
      // Preserva a impressão integral em conteúdo excepcional ou erro de medição.
      reset();
      console.warn('Usando paginação natural da OS:', error);
    }
  }

  window.addEventListener('beforeprint', paginate);
  window.addEventListener('afterprint', reset);
})();
