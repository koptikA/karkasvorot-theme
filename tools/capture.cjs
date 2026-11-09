/* Local-only visual evidence and browser behavior checks. */
const fs = require('fs');
const path = require('path');
const {chromium} = require(process.env.KV_PLAYWRIGHT_MODULE || 'playwright');
const base = (process.argv[2] || 'http://127.0.0.1:9425').replace(/\/$/, '');
if (!['127.0.0.1','localhost','[::1]'].includes(new URL(base).hostname)) throw Error('Local Playground only');
const root = path.resolve(__dirname, '..');
const out = path.join(root,'docs','screens');
fs.mkdirSync(out,{recursive:true});
(async()=>{
 const browser=await chromium.launch({executablePath:process.env.CHROME_PATH || 'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true});
 const context=await browser.newContext();
 const external=[];
 await context.route('**/*',route=>{
  const u=new URL(route.request().url());
  if(u.origin===new URL(base).origin || ['data:','blob:'].includes(u.protocol)) return route.continue();
  external.push(u.origin);return route.abort();
 });
 const page=await context.newPage();
 const errors=[];page.on('pageerror',e=>errors.push(String(e)));
 const pages=[['home','/'],['otkatnye','/karkas-otkatnyh-vorot/'],['raspashnye','/karkas-raspashnyh-vorot/'],['kalitki','/kalitki/'],['prochie','/prochie-izdeliya/'],['raboty','/nashi-raboty/'],['kontakty','/kontakty/'],['politika','/politika-konfidencialnosti/'],['poisk','/?s=каркас'],['404','/net-takoj-stranicy-kv/']];
 await page.goto(base+'/nashi-raboty/');
 const work=await page.locator('.kv-work__caption a').first().getAttribute('href');
 pages.push(['rabota',new URL(work).pathname]);
 const index=[];
 for(const width of [390,1440]){
  await page.setViewportSize({width,height:900});
  for(const [name,url] of pages){
   const response=await page.goto(base+url,{waitUntil:'networkidle'});
   await page.evaluate(()=>document.fonts.ready);
   // Resolve lazy images before full-page capture.
   await page.evaluate(async()=>{for(let y=0;y<document.body.scrollHeight;y+=700){scrollTo(0,y);await new Promise(r=>setTimeout(r,80));}scrollTo(0,0);});
   await page.waitForTimeout(350);
   const overflow=await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1);
   if(overflow) throw Error('Horizontal overflow: '+name+' '+width);
   const file=name+'-'+width+'.png';
   await page.screenshot({path:path.join(out,file),fullPage:true});
   index.push({file,url:base+url,width,status:response.status()});
   console.log(file);
  }
 }
 // Keyboard menu and analytics behavior without ever requesting Yandex.
 await page.setViewportSize({width:390,height:900});await page.goto(base);
 const burger=page.locator('.kv-header__burger');await burger.click();
 if(await burger.getAttribute('aria-expanded')!=='true')throw Error('Menu did not open');
 await page.keyboard.press('Escape');
 if(await burger.getAttribute('aria-expanded')!=='false')throw Error('Escape did not close menu');
 const token=fs.readFileSync(path.join(root,'tools','stand','.token'),'utf8').trim();
 async function php(code){const r=await context.request.post(base+'/kv-run.php',{headers:{'X-KV-Token':token},data:code});if(!r.ok())throw Error('PHP helper '+r.status());return r.text();}
 const old=JSON.parse(await php('$m=get_theme_mods();echo wp_json_encode(array("exists"=>is_array($m)&&array_key_exists("kv_metrika_id",$m),"value"=>get_theme_mod("kv_metrika_id","")));'));
 try{
  await php('set_theme_mod("kv_metrika_id","12345678");');
  // Stub analytics before site.js: records calls locally and prevents external script loading.
  await context.addInitScript(()=>{window.kvCalls=[];window.ym=(...args)=>window.kvCalls.push(args);});
  await page.goto(base);await page.evaluate(()=>localStorage.removeItem('kv_consent'));await page.reload();
  if(!await page.locator('.kv-cookie').isVisible())throw Error('Consent banner missing');
  await page.locator('[data-consent="no"]').click();
  await page.waitForLoadState('networkidle');
  await page.locator('[data-consent-open]').click();
  await page.locator('[data-consent="yes"]').click();
  await page.goto(base+'/?zayavka=ok#zayavka');
  if(page.url().includes('zayavka=ok'))throw Error('Success parameter retained');
  let goals=await page.evaluate(()=>window.kvCalls.filter(x=>x[1]==='reachGoal'&&x[2]==='form_submit').length);
  if(goals!==1)throw Error('Success goal count '+goals);
  await page.reload();
  goals=await page.evaluate(()=>window.kvCalls.filter(x=>x[1]==='reachGoal').length);
  if(goals)throw Error('Repeated success goal');
  await page.locator('[data-consent-open]').click();await page.locator('[data-consent="no"]').click();await page.waitForLoadState('networkidle');
  if(await page.evaluate(()=>localStorage.getItem('kv_consent'))!=='no')throw Error('Withdrawal not saved');
 }finally{
  const val="'"+String(old.value).replace(/\\/g,'\\\\').replace(/'/g,"\\'")+"'";
  await php(old.exists?'set_theme_mod("kv_metrika_id",'+val+');':'remove_theme_mod("kv_metrika_id");');
 }
 if(errors.length)throw Error(errors.join('\n'));
 if(external.length)throw Error('Unexpected external requests (blocked): '+external.join(','));
 fs.writeFileSync(path.join(out,'index.json'),JSON.stringify(index,null,2)+'\n');
 fs.writeFileSync(path.join(root,'docs','reports','browser.json'),JSON.stringify({screens:index.length,widths:[390,1440],horizontalOverflow:false,jsErrors:errors,externalRequests:external,menuEscape:true,consentWithdrawal:true,successGoalOnce:true},null,2)+'\n');

 // Compact header: keyboard disclosure, outside click, mobile Escape, narrow widths.
 await page.setViewportSize({width:1440,height:900});await page.goto(base);
 const products=page.locator('.kv-products__dropdown');
 await products.locator('summary').focus();await page.keyboard.press('Enter');
 if(!await products.evaluate(e=>e.open))throw Error('Product menu did not open by keyboard');
 await page.screenshot({path:path.join(out,'header-products-1440.png')});
 await page.keyboard.press('Escape');
 if(await products.evaluate(e=>e.open))throw Error('Product menu Escape failed');
 await products.locator('summary').click();await page.locator('h1').click();
 if(await products.evaluate(e=>e.open))throw Error('Product menu outside click failed');
 for(const width of [320,390,900,1024]){
  await page.setViewportSize({width,height:900});
  if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1))throw Error('Header overflow '+width);
 }
 await page.setViewportSize({width:390,height:900});
 await burger.click();await products.locator('summary').click();
 await page.screenshot({path:path.join(out,'header-products-390.png')});
 await page.keyboard.press('Escape');
 if(await products.evaluate(e=>e.open) || await burger.getAttribute('aria-expanded')!=='false')throw Error('Mobile menu Escape failed');
 fs.writeFileSync(path.join(root,'docs','reports','header.json'),JSON.stringify({keyboardOpen:true,escapeClose:true,outsideClick:true,mobileEscape:true,widths:[320,390,900,1024,1440]},null,2)+'\n');
 await browser.close();console.log('Browser checks passed.');
})().catch(e=>{console.error(e);process.exit(1);});
