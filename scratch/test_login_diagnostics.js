import puppeteer from 'puppeteer-core';

const delay = ms => new Promise(resolve => setTimeout(resolve, ms));

async function run() {
    console.log('Launching browser for login diagnostics...');
    const browser = await puppeteer.launch({
        executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 1280, height: 800 });

    console.log('Navigating to login...');
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'load' });
    await delay(1000);

    console.log('Filling form...');
    await page.type('input[autocomplete="email"]', 'creator@test.com');
    await page.type('input[autocomplete="current-password"]', 'password');

    console.log('Clicking submit...');
    await page.click('button[type="submit"]');
    await delay(5000);

    console.log(`Current URL after 5s: ${page.url()}`);
    
    const pageText = await page.evaluate(() => document.body.innerText);
    console.log('Page body text length:', pageText.length);
    console.log('Page body snippet:\n', pageText.substring(0, 1000));

    const screenshotPath = '/Users/antoniovarona/Desktop/impulse_media/scratch/login_diag.png';
    await page.screenshot({ path: screenshotPath });
    console.log('Screenshot saved to:', screenshotPath);

    await browser.close();
}

run().catch(console.error);
