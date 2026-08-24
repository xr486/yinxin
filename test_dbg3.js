const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true, executablePath: 'D:/code-tools/Playwright/browsers/chromium-1223/chrome-win64/chrome.exe', args: ['--no-sandbox'] });
  const page = await browser.newPage();
  await page.goto('http://localhost/yixin/index.php', { waitUntil: 'networkidle' });
  await page.fill('input[name="UserNameEntryField"]', 'admin');
  await page.fill('input[name="Password"]', '123456');
  await page.click('input[name="SubmitUser"]');
  await page.waitForLoadState('networkidle').catch(() => {});
  await page.waitForTimeout(2000);
  await page.goto('http://localhost/yixin/DocPLM.php?mode=template&folder=2', { waitUntil: 'networkidle' });
  const body = await page.evaluate(() => document.body.innerText);
  console.log('页面文本前400:', body.replace(/\s+/g, ' ').substring(0, 400));
  await browser.close();
})();
