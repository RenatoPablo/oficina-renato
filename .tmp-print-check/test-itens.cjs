const { chromium }=require('C:/Users/renat/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const fs=require('fs');const path=require('path');const assert=require('assert/strict');
(async()=>{
const browser=await chromium.launch({headless:true,channel:'msedge'});
const page=await browser.newPage(); const errors=[];page.on('pageerror',e=>errors.push(e.message));
await page.route('**/*',async r=>{const u=new URL(r.request().url()); const f=path.resolve('public','.'+u.pathname); if(f.startsWith(path.resolve('public')+path.sep)&&fs.existsSync(f)&&fs.statSync(f).isFile()) await r.fulfill({path:f});else await r.abort();});
await page.setContent(fs.readFileSync('.tmp-print-check/itens.html','utf8'),{waitUntil:'load'});
for (const prefix of ['servicos','pecas']) {
const row=page.locator(prefix==='servicos'?'#tbl-servicos tbody tr':'#tbl-pecas tbody tr').first();
const q=row.locator('[name$="[qtd]"]'),u=row.locator('[name$="[valor_unit]"]'),t=row.locator('[name$="[valor_total]"]');
assert.equal(await t.isEditable(),true);
await q.fill('3');await t.fill('10000');assert.equal(await t.inputValue(),'100,00');assert.equal(await u.inputValue(),'33,33333');
await q.fill('4');assert.equal(await t.inputValue(),'100,00');assert.equal(await u.inputValue(),'25,00');
await u.fill('1250');assert.equal(await t.inputValue(),'50,00');
await q.fill('2');assert.equal(await t.inputValue(),'25,00');
await t.fill('0');assert.equal(await u.inputValue(),'0,00');
await q.fill('0');assert.equal(await u.inputValue(),'0,00');
await q.fill('3');await t.fill('10000');
}
assert.equal(await page.locator('#total-os').textContent(),'270,00');
await page.locator('#sel-servico').selectOption('1'); await page.locator('#add-servico').click();
const added=page.locator('#tbl-servicos tbody tr').last();await added.locator('[name$="[qtd]"]').fill('2');await added.locator('[name$="[valor_total]"]').fill('8000');assert.equal(await added.locator('[name$="[valor_unit]"]').inputValue(),'40,00');
await page.locator('#sel-peca').selectOption('1'); await page.locator('#add-peca').click();
const addedP=page.locator('#tbl-pecas tbody tr').last();await addedP.locator('[name$="[qtd]"]').fill('2');await addedP.locator('[name$="[valor_total]"]').fill('8000');assert.equal(await addedP.locator('[name$="[valor_unit]"]').inputValue(),'40,00');
const submitted=await page.locator('#form-sync-all').evaluate(form=>{form.addEventListener('submit',e=>e.preventDefault());form.dispatchEvent(new Event('submit',{bubbles:true,cancelable:true}));return Object.fromEntries(new FormData(form));});
assert.equal(submitted['servicos[0][valor_unit]'],'33.33333');assert.equal(submitted['servicos[0][valor_total]'],'100.00');assert.equal(submitted['pecas[0][valor_unit]'],'33.33333');
assert.deepEqual(errors,[]); console.log('OK: peças e serviços existentes/novos, total, unitário, quantidade, zero, subtotais e envio com precisão.');await browser.close();
})().catch(e=>{console.error(e);process.exit(1)});
