const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true, channel: 'msedge' });
  const context = await browser.newContext({ viewport: { width: 1600, height: 900 } });
  const page = await context.newPage();
  try {
    await page.goto('http://localhost/yixin/index.php', { waitUntil: 'networkidle' });
    await page.fill('input[name="UserNameEntry"]', 'admin');
    await page.fill('input[name="Password"]', '123456');
    await page.click('input[type="submit"]');
    await page.waitForLoadState('networkidle');
    await page.goto('http://localhost/yixin/BOMSetup.php?view=32941042&version=v1', { waitUntil: 'networkidle' });
    await page.waitForTimeout(2000);
    const treeVersions = await page.$$eval('#bomTree .bom-node', nodes => nodes.map(n => ({
      asm: n.getAttribute('data-assembly'),
      ver: n.getAttribute('data-version'),
      text: n.querySelector('.lbl')?.textContent?.trim() || ''
    })));
    console.log('TREE:', JSON.stringify(treeVersions, null, 2));
    const tableRows = await page.$$eval('#hierTable tbody tr', rows => rows.map(r => ({
      asm: r.getAttribute('data-asm') || '',
      itemNo: r.querySelector('.col-itemNo')?.textContent?.trim() || '',
      childVer: r.querySelector('.col-childVer')?.textContent?.trim() || ''
    })));
    console.log('TABLE:', JSON.stringify(tableRows, null, 2));
    await page.screenshot({ path: 'D:/wamp/www/yixin/bom_check.png', fullPage: true });
    console.log('screenshot saved');
  } catch (e) {
    console.error('ERROR:', e.message);
  }
  await browser.close();
})();
