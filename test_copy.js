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

  // 文档工作区 folder=1：应看到副本（普通文档）
  await page.goto('http://localhost/yixin/DocPLM.php?folder=1', { waitUntil: 'networkidle' });
  await page.waitForTimeout(2000);
  const t = await page.locator('#plmRight').innerText();
  check('工作区有SOP副本文档', t.indexOf('作业指导书模板（SOP）（副本）') >= 0);
  check('工作区无模板(硬隔离)', t.indexOf('作业指导书模板（SOP）[v1]') < 0 || t.indexOf('取消模板') < 0);

  console.log(results.join('\n'));
  await browser.close();
})();
