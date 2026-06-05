import puppeteer from 'puppeteer-core';
import path from 'path';

const delay = ms => new Promise(resolve => setTimeout(resolve, ms));

async function fillInput(page, selector, value) {
    console.log(`[fillInput] Selector: ${selector}, Value: ${value}`);
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
    console.log('Launching browser...');
    const browser = await puppeteer.launch({
        executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    const targetDir = '/Users/antoniovarona/Desktop/qa_screenshots/prod_audit/';
    const baseUrl = 'https://impulsemedia.me';

    // Helper to create page with pre-seeded cookie consent
    async function createPage() {
        const page = await browser.newPage();
        page.setDefaultNavigationTimeout(60000);
        
        // Listen to console messages
        page.on('console', msg => {
            const text = msg.text();
            if (!text.includes('[vite]') && !text.includes('HMR') && !text.includes('component')) {
                console.log(`[Browser Console] ${msg.type().toUpperCase()}: ${text}`);
            }
        });
        
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

        // Automatically accept dialogs
        page.on('dialog', async dialog => {
            console.log(`[Browser Dialog] Accept: ${dialog.message()}`);
            await dialog.accept();
        });
        
        return page;
    }

    // ========================================================
    // PHASE 3: Creator Dashboard, Pricing check, Payout request
    // ========================================================
    console.log('\n--- CREATOR ACTIONS ---');
    const pageCreator = await createPage();
    await pageCreator.setViewport({ width: 1280, height: 800 });

    console.log('Navigating to login...');
    await pageCreator.goto(`${baseUrl}/login`, { waitUntil: 'load' });
    await delay(1500);

    console.log('Logging in as creator...');
    await fillInput(pageCreator, 'input[autocomplete="email"]', 'creator_audit_prod@test.com');
    await fillInput(pageCreator, 'input[autocomplete="current-password"]', 'password');
    await clickAndRedirect(pageCreator, 'button[type="submit"]', '/dashboard');

    console.log('Navigating to creator dashboard...');
    await pageCreator.goto(`${baseUrl}/creator/dashboard`, { waitUntil: 'load' });
    await delay(2000);

    console.log('Taking creator dashboard screenshots...');
    await pageCreator.screenshot({ path: path.join(targetDir, 'prod_creator_dashboard_desktop.png') });
    await pageCreator.setViewport({ width: 375, height: 812 });
    await delay(1000);
    await pageCreator.screenshot({ path: path.join(targetDir, 'prod_creator_dashboard_mobile.png') });
    await pageCreator.setViewport({ width: 1280, height: 800 });

    console.log('Submitting payout request for $40.00...');
    await fillInput(pageCreator, 'input[type="number"][step="0.01"]', '40.00');
    await pageCreator.select('select', 'paypal');
    await fillInput(pageCreator, 'textarea', 'qa_prod_creator@example.com');
    await pageCreator.click('form.payout-form button.submit-btn');
    await delay(2000);
    
    console.log('Taking payout submitted screenshot...');
    await pageCreator.screenshot({ path: path.join(targetDir, 'prod_creator_payout_submitted.png') });

    // Log out creator
    console.log('Logging out creator...');
    await pageCreator.goto(`${baseUrl}/profile`, { waitUntil: 'load' });
    await pageCreator.waitForSelector('.logout-button', { timeout: 5000 });
    await pageCreator.click('.logout-button');
    await delay(1500);
    await pageCreator.close();

    // ========================================================
    // PHASE 4: Admin Control Panel, Payout settings & Rejection
    // ========================================================
    console.log('\n--- ADMIN ACTIONS ---');
    const pageAdmin = await createPage();
    await pageAdmin.setViewport({ width: 1280, height: 800 });

    console.log('Navigating to admin login...');
    await pageAdmin.goto(`${baseUrl}/login-admin`, { waitUntil: 'load' });
    await delay(1500);

    console.log('Logging in as admin...');
    await fillInput(pageAdmin, 'input[autocomplete="email"]', 'admin@example.com');
    await fillInput(pageAdmin, 'input[autocomplete="current-password"]', 'password');
    await clickAndRedirect(pageAdmin, 'button[type="submit"]', '/admin');

    console.log('Navigating to admin payouts...');
    await pageAdmin.goto(`${baseUrl}/admin/payouts`, { waitUntil: 'load' });
    await delay(2000);

    console.log('Taking admin payouts desktop screenshot...');
    await pageAdmin.screenshot({ path: path.join(targetDir, 'prod_admin_payouts_desktop.png') });

    console.log('Testing mobile responsive sidebar behavior...');
    await pageAdmin.setViewport({ width: 375, height: 812 });
    await delay(1000);
    await pageAdmin.screenshot({ path: path.join(targetDir, 'prod_admin_payouts_mobile.png') });

    console.log('Clicking hamburger menu to open sidebar...');
    await pageAdmin.click('.mobile-nav-toggle');
    await delay(1000);
    await pageAdmin.screenshot({ path: path.join(targetDir, 'prod_admin_payouts_mobile_sidebar_open.png') });

    console.log('Closing sidebar by clicking backdrop...');
    await pageAdmin.evaluate(() => {
        const el = document.querySelector('.sidebar-backdrop');
        if (el) el.click();
    });
    await delay(1000);
    await pageAdmin.setViewport({ width: 1280, height: 800 });
    await delay(1000);
    console.log('Current URL before settings audit:', pageAdmin.url());
    await pageAdmin.screenshot({ path: path.join(targetDir, 'debug_admin_before_settings.png') });

    // Update settings
    console.log('Updating platform monetization settings...');
    await pageAdmin.waitForSelector('.settings-form input', { timeout: 10000 });
    const inputs = await pageAdmin.$$('.settings-form input');
    
    // Split ratio -> 75%
    await inputs[0].click({ clickCount: 3 });
    await pageAdmin.keyboard.press('Backspace');
    await pageAdmin.keyboard.type('75');

    // Threshold -> 35.00
    await inputs[1].click({ clickCount: 3 });
    await pageAdmin.keyboard.press('Backspace');
    await pageAdmin.keyboard.type('35.00');

    // Member discount -> 20%
    await inputs[2].click({ clickCount: 3 });
    await pageAdmin.keyboard.press('Backspace');
    await pageAdmin.keyboard.type('20');

    // Min PPV -> 2.99
    await inputs[3].click({ clickCount: 3 });
    await pageAdmin.keyboard.press('Backspace');
    await pageAdmin.keyboard.type('2.99');

    console.log('Saving admin settings...');
    await pageAdmin.click('.settings-form button.submit-btn');
    await delay(2000);
    await pageAdmin.screenshot({ path: path.join(targetDir, 'prod_admin_settings_saved.png') });

    // Reject payout
    console.log('Rejecting payout request...');
    await pageAdmin.click('.payout-request-card .reject-btn');
    await delay(1000);
    await pageAdmin.screenshot({ path: path.join(targetDir, 'prod_admin_rejection_modal.png') });

    await fillInput(pageAdmin, '.reject-modal-content textarea', 'Please provide a verified PayPal email address.');
    await pageAdmin.click('.reject-modal-content .modal-btn.danger');
    await delay(2000);
    await pageAdmin.screenshot({ path: path.join(targetDir, 'prod_admin_payout_rejected.png') });

    // Log out admin
    console.log('Logging out admin...');
    await pageAdmin.evaluate(() => {
        const links = Array.from(document.querySelectorAll('a'));
        const logoutLink = links.find(a => a.innerText.toLowerCase().includes('logout'));
        if (logoutLink) logoutLink.click();
    });
    await delay(1500);
    await pageAdmin.close();

    // ========================================================
    // PHASE 5: Creator Verification of Rejection & Re-request
    // ========================================================
    console.log('\n--- CREATOR CHECK REJECTION ---');
    const pageCreator2 = await createPage();
    await pageCreator2.setViewport({ width: 1280, height: 800 });

    console.log('Logging in as creator...');
    await pageCreator2.goto(`${baseUrl}/login`, { waitUntil: 'load' });
    await delay(1500);
    await fillInput(pageCreator2, 'input[autocomplete="email"]', 'creator_audit_prod@test.com');
    await fillInput(pageCreator2, 'input[autocomplete="current-password"]', 'password');
    await clickAndRedirect(pageCreator2, 'button[type="submit"]', '/dashboard');

    await pageCreator2.goto(`${baseUrl}/creator/dashboard`, { waitUntil: 'load' });
    await delay(2000);
    console.log('Taking creator rejected status screenshot...');
    await pageCreator2.screenshot({ path: path.join(targetDir, 'prod_creator_dashboard_rejected.png') });

    // Resubmit request
    console.log('Resubmitting withdrawal request with verified email...');
    await fillInput(pageCreator2, 'input[type="number"][step="0.01"]', '40.00');
    await pageCreator2.select('select', 'paypal');
    await fillInput(pageCreator2, 'textarea', 'qa_prod_creator_verified@example.com');
    await pageCreator2.click('form.payout-form button.submit-btn');
    await delay(2000);
    await pageCreator2.screenshot({ path: path.join(targetDir, 'prod_creator_payout_resubmitted.png') });

    // Log out creator
    console.log('Logging out creator...');
    await pageCreator2.goto(`${baseUrl}/profile`, { waitUntil: 'load' });
    await pageCreator2.waitForSelector('.logout-button', { timeout: 5000 });
    await pageCreator2.click('.logout-button');
    await delay(1500);
    await pageCreator2.close();

    // ========================================================
    // PHASE 6: Admin Approval
    // ========================================================
    console.log('\n--- ADMIN APPROVAL ---');
    const pageAdmin2 = await createPage();
    await pageAdmin2.setViewport({ width: 1280, height: 800 });

    console.log('Logging in as admin...');
    await pageAdmin2.goto(`${baseUrl}/login-admin`, { waitUntil: 'load' });
    await delay(1500);
    await fillInput(pageAdmin2, 'input[autocomplete="email"]', 'admin@example.com');
    await fillInput(pageAdmin2, 'input[autocomplete="current-password"]', 'password');
    await clickAndRedirect(pageAdmin2, 'button[type="submit"]', '/admin');

    await pageAdmin2.goto(`${baseUrl}/admin/payouts`, { waitUntil: 'load' });
    await delay(2000);

    console.log('Approving the resubmitted request...');
    await pageAdmin2.click('.payout-request-card .approve-btn');
    await delay(2000);
    await pageAdmin2.screenshot({ path: path.join(targetDir, 'prod_admin_payout_approved.png') });

    // Log out admin
    console.log('Logging out admin...');
    await pageAdmin2.evaluate(() => {
        const links = Array.from(document.querySelectorAll('a'));
        const logoutLink = links.find(a => a.innerText.toLowerCase().includes('logout'));
        if (logoutLink) logoutLink.click();
    });
    await delay(1500);
    await pageAdmin2.close();

    // ========================================================
    // PHASE 7: Spectator Preview Paywall Trigger
    // ========================================================
    console.log('\n--- SPECTATOR PLAYBACK AND PAYWALL ---');
    const pageSpectator = await createPage();
    await pageSpectator.setViewport({ width: 1280, height: 800 });

    console.log('Logging in as spectator...');
    await pageSpectator.goto(`${baseUrl}/login`, { waitUntil: 'load' });
    await delay(1500);
    await fillInput(pageSpectator, 'input[autocomplete="email"]', 'spectator_audit_prod@test.com');
    await fillInput(pageSpectator, 'input[autocomplete="current-password"]', 'password');
    await clickAndRedirect(pageSpectator, 'button[type="submit"]', '/dashboard');

    console.log('Navigating to uploaded movie player page (movie 28)...');
    await pageSpectator.goto(`${baseUrl}/movie/28/player`, { waitUntil: 'load' });
    await delay(2000);

    console.log('Taking movie player screenshots...');
    await pageSpectator.screenshot({ path: path.join(targetDir, 'prod_spectator_player_desktop.png') });
    await pageSpectator.setViewport({ width: 375, height: 812 });
    await delay(1000);
    await pageSpectator.screenshot({ path: path.join(targetDir, 'prod_spectator_player_mobile.png') });
    await pageSpectator.setViewport({ width: 1280, height: 800 });

    console.log('Mocking 5-minute paywall trigger...');
    await pageSpectator.evaluate(() => {
        const v = document.querySelector('video');
        if (v) {
            v.currentTime = 300;
            v.dispatchEvent(new Event('timeupdate'));
        }
    });
    await delay(1500);

    console.log('Taking paywall triggered screenshots...');
    await pageSpectator.screenshot({ path: path.join(targetDir, 'prod_spectator_paywall_desktop.png') });
    await pageSpectator.setViewport({ width: 375, height: 812 });
    await delay(1000);
    await pageSpectator.screenshot({ path: path.join(targetDir, 'prod_spectator_paywall_mobile.png') });

    // Log out spectator
    console.log('Logging out spectator...');
    await pageSpectator.setViewport({ width: 1280, height: 800 });
    await pageSpectator.goto(`${baseUrl}/profile`, { waitUntil: 'load' });
    await pageSpectator.waitForSelector('.logout-button', { timeout: 5000 });
    await pageSpectator.click('.logout-button');
    await delay(1500);
    await pageSpectator.close();

    await browser.close();
    console.log('=== LIVE PRODUCTION AUDIT AUTOMATION COMPLETED SUCCESSFULLY! ===');
}

run().catch(err => {
    console.error('Error executing live audit:', err);
    process.exit(1);
});
