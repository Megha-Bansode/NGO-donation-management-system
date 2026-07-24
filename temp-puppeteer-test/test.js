const puppeteer = require('puppeteer');
const fs = require('fs');

(async () => {
    let browser;
    let page;
    try {
        browser = await puppeteer.launch({
            headless: true,
            executablePath: 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
            args: ['--no-sandbox', '--disable-setuid-sandbox', '--window-size=1280,800']
        });
        page = await browser.newPage();
        await page.setViewport({ width: 800, height: 600 });

        console.log('Navigating directly to bypass.php...');
        await page.goto('http://localhost/NGO-donation-management-system/bypass.php', { waitUntil: 'networkidle2' });

        console.log('Current URL is:', page.url());

        // We will test multiple coordinates: Center of the button, and Center of the first card.
        // First, the button
        const buttonSelector = '.page-actions .btn-primary';
        await page.waitForSelector(buttonSelector);
        const buttonBox = await page.evaluate((sel) => {
            const el = document.querySelector(sel);
            if (!el) return null;
            const rect = el.getBoundingClientRect();
            return { x: rect.x + rect.width / 2, y: rect.y + rect.height / 2 };
        }, buttonSelector);

        // Second, the card
        const cardSelector = '.kpi-card';
        await page.waitForSelector(cardSelector);
        const cardBox = await page.evaluate((sel) => {
            const el = document.querySelector(sel);
            if (!el) return null;
            const rect = el.getBoundingClientRect();
            return { x: rect.x + rect.width / 2, y: rect.y + rect.height / 2 };
        }, cardSelector);

        // Use elementFromPoint directly in JS to get the top-most element!
        const result = await page.evaluate((btnX, btnY, cardX, cardY) => {
            const getReport = (el) => {
                if(!el) return null;
                const comp = window.getComputedStyle(el);
                return {
                    tag: el.tagName,
                    className: el.className,
                    id: el.id,
                    zIndex: comp.zIndex,
                    pointerEvents: comp.pointerEvents,
                    position: comp.position
                };
            };

            return {
                buttonCoords: {x: btnX, y: btnY},
                buttonTarget: getReport(document.elementFromPoint(btnX, btnY)),
                cardCoords: {x: cardX, y: cardY},
                cardTarget: getReport(document.elementFromPoint(cardX, cardY)),
                overlayStats: getReport(document.querySelector('.sidebar-overlay')),
                allOverlays: Array.from(document.querySelectorAll('*')).filter(el => {
                   const style = window.getComputedStyle(el);
                   return (style.position === 'fixed' || style.position === 'absolute') && 
                          style.width === window.innerWidth + 'px' && 
                          style.height === window.innerHeight + 'px' &&
                          style.pointerEvents !== 'none' &&
                          style.display !== 'none' &&
                          style.visibility !== 'hidden';
                }).map(getReport)
            };
        }, buttonBox.x, buttonBox.y, cardBox.x, cardBox.y);

        console.log('--- BLOCKING ELEMENT REPORT ---');
        console.log(JSON.stringify(result, null, 2));
        
        await browser.close();
    } catch (error) {
        console.error('Error during execution:', error);
        if (page) {
            try {
                await page.screenshot({path: 'error.png'});
                const html = await page.content();
                fs.writeFileSync('error.html', html);
                console.log('Saved error.png and error.html');
            } catch (e) { }
        }
        if (browser) await browser.close();
    }
})();
