const puppeteer = require('puppeteer');
const fs = require('fs');

(async () => {
    let browser;
    try {
        browser = await puppeteer.launch({
            headless: 'new',
            executablePath: 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
            args: ['--no-sandbox', '--disable-setuid-sandbox', '--window-size=1280,800']
        });
        const page = await browser.newPage();
        await page.setViewport({ width: 1280, height: 800 });

        console.log('Navigating directly to bypass.php...');
        await page.goto('http://localhost:8000/bypass.php', { waitUntil: 'networkidle2' });
        
        console.log('Current URL is:', page.url());

        // Wait for a card to be visible
        await page.waitForSelector('.kpi-card');
        
        // Output HTML to see structure
        const html = await page.content();
        fs.writeFileSync('dashboard_updated.html', html);
        
        // Capture screenshot
        await page.screenshot({ path: 'dashboard_updated.png', fullPage: true });
        
        console.log("Successfully loaded dashboard and verified DOM.");

    } catch (error) {
        console.error('Error during puppeteer execution:', error);
    } finally {
        if (browser) {
            await browser.close();
        }
    }
})();
