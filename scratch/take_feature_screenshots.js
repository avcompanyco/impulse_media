import puppeteer from 'puppeteer-core';
import fs from 'fs';
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

async function clickAndRedirect(page, clickSelector, targetPath, timeout = 15000) {
    await page.click(clickSelector);
    const start = Date.now();
    while (Date.now() - start < timeout) {
        const url = await page.url();
        if (url.includes(targetPath)) {
            return;
        }
        await delay(200);
    }
    throw new Error(`Redirect to ${targetPath} timed out`);
}

async function run() {
    console.log('Starting screenshot capture for implemented features...');
    const targetDir = '/Users/antoniovarona/Desktop/qa_screenshots/features';
    if (!fs.existsSync(targetDir)) {
        fs.mkdirSync(targetDir, { recursive: true });
    }

    const browser = await puppeteer.launch({
        executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    // Helper to create page with pre-seeded cookie consent
    async function createPage() {
        const page = await browser.newPage();
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
        return page;
    }

    // ==========================================
    // 1. Creator Monetization Options Selector
    // ==========================================
    console.log('\n--- CAPTURING CREATOR MONETIZATION OPTION CARDS ---');
    const pageCreator = await createPage();
    await pageCreator.setViewport({ width: 1280, height: 800 });

    console.log('Logging in as creator...');
    await pageCreator.goto('http://127.0.0.1:8000/login', { waitUntil: 'load' });
    await delay(1000);
    await fillInput(pageCreator, 'input[autocomplete="email"]', 'creator@test.com');
    await fillInput(pageCreator, 'input[autocomplete="current-password"]', 'password');
    await clickAndRedirect(pageCreator, 'button[type="submit"]', '/dashboard');

    console.log('Navigating to Upload Movie page...');
    await pageCreator.goto('http://127.0.0.1:8000/upload/movie', { waitUntil: 'load' });
    await delay(2500);

    // Scroll down to the monetization option section
    console.log('Scrolling to monetization section...');
    await pageCreator.evaluate(() => {
        const section = document.querySelector('.monetization-options-group');
        if (section) {
            section.scrollIntoView({ block: 'center' });
        }
    });
    await delay(1000);

    const screenshot1Path = path.join(targetDir, 'creator_monetization_options.png');
    await pageCreator.screenshot({ path: screenshot1Path });
    console.log(`Saved: ${screenshot1Path}`);

    // Let's toggle to Option 2 (PPV Only) and take a screenshot
    console.log('Toggling to Option 2 (PPV Only)...');
    await pageCreator.evaluate(() => {
        const cards = document.querySelectorAll('.monetization-option-card');
        if (cards && cards[1]) {
            cards[1].click();
        }
    });
    await delay(1000);

    const screenshot2Path = path.join(targetDir, 'creator_monetization_options_ppv_active.png');
    await pageCreator.screenshot({ path: screenshot2Path });
    console.log(`Saved: ${screenshot2Path}`);

    await pageCreator.close();

    // ==========================================
    // 2. Admin Membership Split Calculator
    // ==========================================
    console.log('\n--- CAPTURING ADMIN MEMBERSHIP SPLIT CALCULATOR ---');
    const pageAdmin = await createPage();
    await pageAdmin.setViewport({ width: 1280, height: 800 });

    console.log('Logging in as admin...');
    await pageAdmin.goto('http://127.0.0.1:8000/login-admin', { waitUntil: 'load' });
    await delay(1000);
    await fillInput(pageAdmin, 'input[autocomplete="email"]', 'admin@example.com');
    await fillInput(pageAdmin, 'input[autocomplete="current-password"]', 'password');
    await clickAndRedirect(pageAdmin, 'button[type="submit"]', '/admin');

    console.log('Navigating to Admin Payouts Panel...');
    await pageAdmin.goto('http://127.0.0.1:8000/admin/payouts', { waitUntil: 'load' });
    await delay(2500);

    // Scroll to the Membership Split manual calculator card
    console.log('Scrolling to Membership Split card...');
    await pageAdmin.evaluate(() => {
        const card = document.querySelector('.card-calculator');
        if (card) {
            card.scrollIntoView({ block: 'center' });
        }
    });
    await delay(1000);

    const screenshot3Path = path.join(targetDir, 'admin_membership_split_card.png');
    await pageAdmin.screenshot({ path: screenshot3Path });
    console.log(`Saved: ${screenshot3Path}`);

    // Fill month and trigger execution
    console.log('Setting target month to 2026-05 and executing split...');
    await fillInput(pageAdmin, '#splitMonthSelect', '2026-05');
    await pageAdmin.click('.card-calculator button[type="submit"]');

    console.log('Waiting for console logs to load (flash message simulation)...');
    await delay(3500);

    const screenshot4Path = path.join(targetDir, 'admin_membership_split_executed_output.png');
    await pageAdmin.screenshot({ path: screenshot4Path });
    console.log(`Saved: ${screenshot4Path}`);

    await pageAdmin.close();
    await browser.close();

    console.log('\n=== ALL screenshots captured successfully! ===');
}

run().catch(console.error);
