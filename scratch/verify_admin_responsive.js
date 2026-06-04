import puppeteer from 'puppeteer-core';
import path from 'path';

const delay = ms => new Promise(resolve => setTimeout(resolve, ms));

async function fillInput(page, selector, value) {
    await page.waitForSelector(selector);
    await page.click(selector);
    await page.focus(selector);
    await page.$eval(selector, el => el.value = '');
    await page.type(selector, value);
    await page.$eval(selector, el => {
        el.dispatchEvent(new Event('input', { bubbles: true }));
        el.dispatchEvent(new Event('change', { bubbles: true }));
    });
}

async function run() {
    console.log('Launching browser...');
    const browser = await puppeteer.launch({
        executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    const page = await browser.newPage();
    page.setDefaultNavigationTimeout(60000);

    // Pre-seed cookie consent
    await page.evaluateOnNewDocument(() => {
        try {
            localStorage.setItem('cookie_consent', JSON.stringify({
                necessary: true,
                analytics: true,
                advertising: true,
                timestamp: new Date().toISOString()
            }));
        } catch (e) {}
    });

    console.log('Navigating to admin login...');
    await page.setViewport({ width: 1280, height: 800 });
    await page.goto('http://127.0.0.1:8000/login-admin', { waitUntil: 'load' });
    await delay(1500);

    console.log('Logging in as admin...');
    await fillInput(page, 'input[autocomplete="email"]', 'admin@example.com');
    await fillInput(page, 'input[autocomplete="current-password"]', 'password');
    await page.click('button[type="submit"]');
    
    // Wait for navigation
    await delay(3000);
    
    console.log('Navigating to admin payouts...');
    await page.goto('http://127.0.0.1:8000/admin/payouts', { waitUntil: 'load' });
    await delay(2000);

    console.log('Taking admin payouts desktop screenshot...');
    await page.screenshot({ path: '/Users/antoniovarona/.gemini/antigravity/brain/7e38f2f8-1fb8-414b-a1d7-9b8826746182/admin_payouts_desktop_fixed.png' });

    console.log('Setting viewport to mobile (375x812)...');
    await page.setViewport({ width: 375, height: 812 });
    await delay(1000);

    console.log('Taking admin payouts mobile screenshot...');
    await page.screenshot({ path: '/Users/antoniovarona/.gemini/antigravity/brain/7e38f2f8-1fb8-414b-a1d7-9b8826746182/admin_payouts_mobile_fixed.png' });

    // Open sidebar on mobile by clicking mobile-nav-toggle
    console.log('Clicking hamburger menu button on mobile...');
    await page.click('.mobile-nav-toggle');
    await delay(1000);

    console.log('Taking admin payouts mobile with sidebar open screenshot...');
    await page.screenshot({ path: '/Users/antoniovarona/.gemini/antigravity/brain/7e38f2f8-1fb8-414b-a1d7-9b8826746182/admin_payouts_mobile_sidebar_open.png' });

    await browser.close();
    console.log('Done!');
}

run().catch(err => {
    console.error('Error:', err);
    process.exit(1);
});
