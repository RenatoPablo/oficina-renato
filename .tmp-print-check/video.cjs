const {chromium}=require('C:/Users/renat/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const fs=require('fs');
(async()=>{
 const browser=await chromium.launch({headless:true,channel:"msedge"});
 const page=await browser.newPage({viewport:{width:1280,height:800}});
 const video=fs.readFileSync('C:/Users/renat/AppData/Local/Packages/Microsoft.ScreenSketch_8wekyb3d8bbwe/TempState/Recordings/20261022-1904-52.6058128.mp4').toString('base64');
 await page.setContent(`<video id="v" style="width:100%;height:760px" src="data:video/mp4;base64,${video}"></video>`);
 await page.waitForFunction(()=>document.querySelector('video').readyState>=2);
 console.log('duration',await page.locator('video').evaluate(v=>v.duration));
 for (const t of [1,5,10,15]) {
 await page.locator('video').evaluate((v,t)=>new Promise(r=>{v.onseeked=r;v.currentTime=Math.min(t,v.duration-.1)}),t);
 await page.screenshot({path:`.tmp-print-check/video-${t}.png`});
 }
 await browser.close();
})();

