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

  // ① 模板页：WHTEST项目下应看到"隔离测试模板"
  await page.goto('http://localhost/yixin/DocPLM.php?mode=template&folder=2', { waitUntil: 'networkidle' });
  await page.waitForTimeout(2000);
  let right = await page.locator('#plmRight').innerText();
  check('模板页看到"隔离测试模板"', right.indexOf('隔离测试模板') >= 0);

  // ② 文档工作区：WHTEST项目下看不到模板文档
  await page.goto('http://localhost/yixin/DocPLM.php?folder=2', { waitUntil: 'networkidle' });
  await page.waitForTimeout(2000);
  right = await page.locator('#plmRight').innerText();
  check('文档工作区看不到模板', right.indexOf('隔离测试模板') < 0);

  // ③ 模板页点多个目录不跳变（右侧始终模板视图）
  await page.goto('http://localhost/yixin/DocPLM.php?mode=template', { waitUntil: 'networkidle' });
  await page.waitForTimeout(2000);
  for (const fid of ['2', '10', '11']) {
    const li = page.locator('#plmTree li[data-folder="' + fid + '"]').first();
    if (await li.count() === 0) continue;
    await li.locator(':scope > .bom-row').click();
    await page.waitForTimeout(1800);
    right = await page.locator('#plmRight').innerText();
    check('点目录' + fid + '仍模板视图', right.indexOf('文档模板') >= 0);
  }
  // URL 仍是 mode=template
  const url = page.url();
  check('URL 始终 mode=template', url.indexOf('mode=template') >= 0);

  // ④ 文档工作区点目录不出现模板
  await page.goto('http://localhost/yixin/DocPLM.php', { waitUntil: 'networkidle' });
  await page.waitForTimeout(1500);
  const li2 = page.locator('#plmTree li[data-folder="2"]').first();
  if (await li2.count() > 0) {
    await li2.locator(':scope > .bom-row').click();
    await page.waitForTimeout(1800);
    right = await page.locator('#plmRight').innerText();
    check('工作区点目录无模板', right.indexOf('隔离测试模板') < 0);
  }

  console.log(results.join('\n'));
  await page.screenshot({ path: 'D:/wamp/www/yixin/开发文档/_iso.png', fullPage: true });
  await browser.close();
})();
