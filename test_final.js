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
  await page.goto('http://localhost/yixin/DocPLM.php', { waitUntil: 'networkidle' });
  await page.waitForTimeout(2000);

  const results = [];
  const check = (n, c) => results.push((c ? 'PASS' : 'FAIL') + ' ' + n);

  const root = page.locator('#plmTree li[data-folder="-1"]').first();
  const cats = page.locator('#plmTree li[data-cat]');
  check('树含物料目录根', await root.count() > 0);
  check('分类默认可见', await cats.count() >= 3);

  await root.locator(':scope > .bom-row').click();
  await page.waitForTimeout(1500);
  let rightText = await page.locator('#plmRight').innerText();
  check('物料目录总览显示', rightText.indexOf('物料目录总览') >= 0);

  const firstCat = cats.first();
  await firstCat.locator(':scope > .bom-row').click();
  await page.waitForTimeout(1500);
  rightText = await page.locator('#plmRight').innerText();
  check('分类视图显示物料列表', rightText.indexOf('物料编码') >= 0);

  await firstCat.locator(':scope > .bom-row .tw').click();
  await page.waitForTimeout(1500);
  const itemNodes = await page.locator('#plmTree li[data-item]').count();
  check('分类展开出物料节点', itemNodes > 0);

  if (itemNodes > 0) {
    await page.locator('#plmTree li[data-item]').first().locator(':scope > .bom-row').click();
    await page.waitForTimeout(1500);
    rightText = await page.locator('#plmRight').innerText();
    check('物料文档视图显示', rightText.indexOf('对应物料') >= 0 || rightText.indexOf('文件名称') >= 0);
  }

  console.log(results.join('\n'));
  await page.screenshot({ path: 'D:/wamp/www/yixin/开发文档/_item_cat_tree.png', fullPage: true });
  await browser.close();
})();
