const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true, executablePath: 'D:/code-tools/Playwright/browsers/chromium-1223/chrome-win64/chrome.exe', args: ['--no-sandbox'] });
  const ctx = await browser.newContext();
  const page = await ctx.newPage();
  await page.goto('http://localhost/yixin/index.php', { waitUntil: 'networkidle' });
  await page.fill('input[name="UserNameEntryField"]', 'admin');
  await page.fill('input[name="Password"]', '123456');
  await page.click('input[name="SubmitUser"]');
  await page.waitForLoadState('networkidle').catch(() => {});
  await page.waitForTimeout(2500);
  await page.goto('http://localhost/yixin/DocPLM.php', { waitUntil: 'networkidle' });
  await page.evaluate(() => { localStorage.clear(); });
  await page.reload({ waitUntil: 'networkidle' });
  await page.waitForTimeout(2000);

  // 直接 AJAX 调用物料文档视图测试
  const r = await page.evaluate(async () => {
    const r = await fetch('/yixin/DocPLM.php?ajax=1&folder=-1&item=78000001', { credentials: 'include' });
    const t = await r.text();
    return { len: t.length, has: t.indexOf('质量手册') >= 0 || t.indexOf('说明书') >= 0 };
  });
  console.log('item=78000001 文档视图:', JSON.stringify(r));

  await page.screenshot({ path: 'D:/wamp/www/yixin/开发文档/_item_clean.png', fullPage: true });
  await browser.close();
})();
