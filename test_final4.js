const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true, executablePath: 'D:/code-tools/Playwright/browsers/chromium-1223/chrome-win64/chrome.exe', args: ['--no-sandbox'] });
  const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
  await page.goto('http://localhost/yixin/index.php', { waitUntil: 'networkidle' });
  await page.fill('input[name="UserNameEntryField"]', 'admin');
  await page.fill('input[name="Password"]', '123456');
  await page.click('input[name="SubmitUser"]');
  await page.waitForLoadState('networkidle').catch(() => {});
  await page.waitForTimeout(2500);

  const results = [];
  const check = (n, c) => results.push((c ? 'PASS' : 'FAIL') + ' ' + n);

  // ① 模板页 folder=1：有 SOP/检验模板 + "新建文档"操作
  await page.goto('http://localhost/yixin/DocPLM.php?mode=template&folder=1', { waitUntil: 'networkidle' });
  await page.waitForTimeout(2000);
  let t = await page.locator('#plmRight').innerText();
  check('模板页(folder1)有SOP模板', t.indexOf('作业指导书模板') >= 0);
  check('模板页(folder1)有检验模板', t.indexOf('来料检验记录模板') >= 0);
  const newBtn = await page.locator('#plmRight a:has-text("新建文档")').count();
  check('模板页有"新建文档"操作', newBtn > 0);

  // ② 模板页 folder=2：ECN + 工艺卡
  await page.goto('http://localhost/yixin/DocPLM.php?mode=template&folder=2', { waitUntil: 'networkidle' });
  await page.waitForTimeout(2000);
  t = await page.locator('#plmRight').innerText();
  check('模板页(folder2)有ECN模板', t.indexOf('设计变更通知单') >= 0);
  check('模板页(folder2)有工艺卡模板', t.indexOf('装配工艺卡模板') >= 0);

  // ③ 新建文档（从模板复制）
  await page.goto('http://localhost/yixin/DocPLM.php?mode=template&folder=1', { waitUntil: 'networkidle' });
  await page.waitForTimeout(2000);
  page.once('dialog', d => d.accept());
  await page.locator('#plmRight a:has-text("新建文档")').first().click();
  await page.waitForTimeout(2500);
  const url = page.url();
  check('新建文档后URL仍在模板页', url.indexOf('mode=template') >= 0);
  t = await page.locator('#plmRight').innerText();
  check('模板页出现"副本"文档', t.indexOf('副本') >= 0);

  await page.screenshot({ path: 'D:/wamp/www/yixin/开发文档/_tpl_demo2.png', fullPage: true });
  console.log(results.join('\n'));
  await browser.close();
})();
