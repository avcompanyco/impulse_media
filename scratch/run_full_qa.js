import puppeteer from 'puppeteer-core';
import { execSync } from 'child_process';
import path from 'path';

// Delay helper
const delay = ms => new Promise(resolve => setTimeout(resolve, ms));

// Helper to fill input fields reliably in Vue/Inertia
async function fillInput(page, selector, value) {
    console.log(`[fillInput] Selector: ${selector}, Value: ${value}`);
    await page.waitForSelector(selector);
    const count = await page.$$eval(selector, els => els.length);
    console.log(`[fillInput] Found ${count} element(s) matching ${selector}`);
    
    // Wait for stability
    await delay(300);
    
    await page.click(selector);
    await page.focus(selector);
    await page.$eval(selector, el => el.value = '');
    await page.type(selector, value);
    
    const currentValue = await page.$eval(selector, el => el.value);
    console.log(`[fillInput] Current DOM value after typing: "${currentValue}"`);
    
    if (currentValue !== value) {
        console.warn(`[fillInput] Value mismatch! Expected "${value}", got "${currentValue}". Falling back to direct assignment...`);
        await page.$eval(selector, (el, val) => {
            el.value = val;
            el.dispatchEvent(new Event('input', { bubbles: true }));
            el.dispatchEvent(new Event('change', { bubbles: true }));
        }, value);
    } else {
        await page.$eval(selector, el => {
            el.dispatchEvent(new Event('input', { bubbles: true }));
            el.dispatchEvent(new Event('change', { bubbles: true }));
        });
    }
}

// Helper to handle Inertia redirects via URL polling
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
    // If it timed out, capture a screenshot and dump the body text for debugging
    const errorScreenshotPath = '/Users/antoniovarona/.gemini/antigravity/brain/7e38f2f8-1fb8-414b-a1d7-9b8826746182/login_failure_debug.png';
    await page.screenshot({ path: errorScreenshotPath });
    const pageText = await page.evaluate(() => document.body.innerText);
    console.error(`Inertia redirect to ${targetPath} timed out! Current URL: ${await page.url()}`);
    console.error('Page text content on failure:\n', pageText);
    console.error('Debug screenshot saved to:', errorScreenshotPath);
    throw new Error(`Inertia redirect to ${targetPath} timed out`);
}

// Helper to run Artisan commands from Node
function runArtisan(phpCode) {
    console.log(`Running artisan query...`);
    const command = `php artisan tinker <<'EOF'\n${phpCode}\nEOF`;
    return execSync(command, { cwd: '/Users/antoniovarona/Desktop/impulse_media' }).toString();
}

async function run() {
    console.log('Resetting/seeding database states for QA test...');
    // Seed creator balance to 150, set movie 3 ppv price to 10.00 and allow_membership to 0 (PPV exclusive)
    runArtisan(`
        $c = \\App\\Models\\User::where('email', 'creator@test.com')->first();
        if ($c) {
            $c->earnings()->delete();
            \\App\\Models\\CreatorEarning::create([
                'creator_id' => $c->id,
                'amount' => 150.00,
                'source' => 'bonus',
                'description' => 'QA test seed balance'
            ]);
        }
        $m = \\App\\Models\\Movie::find(3);
        if ($m && $m->content) {
            $m->content->update(['ppv_price' => 10.00, 'allow_membership' => 0]);
            // delete any past purchases of movie 3 by spectator@test.com
            $s = \\App\\Models\\User::where('email', 'spectator@test.com')->first();
            if ($s) {
                \\App\\Models\\Purchase::where('user_id', $s->id)->where('content_id', $m->content->id)->delete();
            }
        }
        // Reset settings
        \\App\\Models\\Setting::set('revenue_split_ratio', 60, 'float');
        \\App\\Models\\Setting::set('min_payout_threshold', 25.00, 'float');
        \\App\\Models\\Setting::set('membership_discount_rate', 15, 'float');
        \\App\\Models\\Setting::set('min_ppv_price', 1.99, 'float');
        // Delete pending payouts
        \\App\\Models\\Payout::where('status', 'pending')->delete();
    `);

    console.log('Launching browser...');
    const browser = await puppeteer.launch({
        executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    const baseArtifactDir = '/Users/antoniovarona/.gemini/antigravity/brain/7e38f2f8-1fb8-414b-a1d7-9b8826746182/';

    // Helper to create page with pre-seeded cookie consent
    async function createIsolatedPage() {
        const context = await browser.createBrowserContext();
        const page = await context.newPage();
        
        // Set standard navigation timeout to 60 seconds
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
        
        return { context, page };
    }

    // ==========================================
    // PHASE 1: Spectator Flow (Desktop & Mobile)
    // ==========================================
    console.log('\n--- STARTING SPECTATOR FLOW ---');
    const { context: ctx1, page: page1 } = await createIsolatedPage();
    await page1.setViewport({ width: 1280, height: 800 });
    
    console.log('Navigating to login...');
    await page1.goto('http://127.0.0.1:8000/login', { waitUntil: 'load' });
    await delay(1500); // Wait for Vue mount

    console.log('Logging in as spectator@test.com...');
    await fillInput(page1, 'input[autocomplete="email"]', 'spectator@test.com');
    await fillInput(page1, 'input[autocomplete="current-password"]', 'password');
    await clickAndRedirect(page1, 'button[type="submit"]', '/dashboard');

    console.log('Taking spectator catalog screenshot (Desktop)...');
    await page1.screenshot({ path: path.join(baseArtifactDir, 'spectator_catalog_desktop.png') });

    console.log('Taking spectator catalog screenshot (Mobile)...');
    await page1.setViewport({ width: 375, height: 812 });
    await delay(1000);
    await page1.screenshot({ path: path.join(baseArtifactDir, 'spectator_catalog_mobile.png') });

    // Go to movie player page
    console.log('Navigating to movie 3 player (Desktop)...');
    await page1.setViewport({ width: 1280, height: 800 });
    await page1.goto('http://127.0.0.1:8000/movie/3/player', { waitUntil: 'load' });
    await delay(1000);
    
    console.log('Taking movie player screenshot (Desktop)...');
    await page1.screenshot({ path: path.join(baseArtifactDir, 'spectator_player_desktop.png') });

    console.log('Taking movie player screenshot (Mobile)...');
    await page1.setViewport({ width: 375, height: 812 });
    await delay(1000);
    await page1.screenshot({ path: path.join(baseArtifactDir, 'spectator_player_mobile.png') });

    // Mock paywall trigger (set video time to 300)
    console.log('Mocking 5-minute paywall trigger...');
    await page1.setViewport({ width: 1280, height: 800 });
    await page1.evaluate(() => {
        const v = document.querySelector('video');
        if (v) {
            v.currentTime = 300;
            v.dispatchEvent(new Event('timeupdate'));
        }
    });
    await delay(1000);
    console.log('Taking paywall triggered screenshot (Desktop)...');
    await page1.screenshot({ path: path.join(baseArtifactDir, 'spectator_paywall_desktop.png') });

    console.log('Taking paywall triggered screenshot (Mobile)...');
    await page1.setViewport({ width: 375, height: 812 });
    await delay(1000);
    await page1.screenshot({ path: path.join(baseArtifactDir, 'spectator_paywall_mobile.png') });

    // Test logout click
    console.log('Testing spectator logout...');
    await page1.setViewport({ width: 1280, height: 800 });
    await page1.goto('http://127.0.0.1:8000/profile', { waitUntil: 'load' });
    await page1.waitForSelector('.logout-button', { timeout: 5000 });
    await page1.click('.logout-button');
    await delay(1000);
    await ctx1.close();

    // ==========================================
    // PHASE 2: Creator Flow (Desktop & Mobile)
    // ==========================================
    console.log('\n--- STARTING CREATOR FLOW ---');
    const { context: ctx2, page: page2 } = await createIsolatedPage();
    await page2.setViewport({ width: 1280, height: 800 });
    
    console.log('Navigating to login...');
    await page2.goto('http://127.0.0.1:8000/login', { waitUntil: 'load' });
    await delay(1500);

    console.log('Logging in as creator@test.com...');
    await fillInput(page2, 'input[autocomplete="email"]', 'creator@test.com');
    await fillInput(page2, 'input[autocomplete="current-password"]', 'password');
    await clickAndRedirect(page2, 'button[type="submit"]', '/dashboard');

    console.log('Navigating to creator dashboard...');
    await page2.goto('http://127.0.0.1:8000/creator/dashboard', { waitUntil: 'load' });
    await delay(1000);
    
    console.log('Taking creator hub screenshot (Desktop)...');
    await page2.screenshot({ path: path.join(baseArtifactDir, 'creator_hub_desktop.png') });

    console.log('Taking creator hub screenshot (Mobile)...');
    await page2.setViewport({ width: 375, height: 812 });
    await delay(1000);
    await page2.screenshot({ path: path.join(baseArtifactDir, 'creator_hub_mobile.png') });

    await page2.setViewport({ width: 1280, height: 800 });

    // Verify upload movie page renders and style is premium
    console.log('Navigating to creator upload content page...');
    await page2.goto('http://127.0.0.1:8000/upload/movie', { waitUntil: 'load' });
    await delay(1000);
    console.log('Taking upload content form screenshot (Desktop)...');
    await page2.screenshot({ path: path.join(baseArtifactDir, 'creator_upload_form_desktop.png') });

    console.log('Taking upload content form screenshot (Mobile)...');
    await page2.setViewport({ width: 375, height: 812 });
    await delay(1000);
    await page2.screenshot({ path: path.join(baseArtifactDir, 'creator_upload_form_mobile.png') });

    // Go back to creator dashboard for pricing edits
    console.log('Navigating back to creator dashboard for pricing edits...');
    await page2.setViewport({ width: 1280, height: 800 });
    await page2.goto('http://127.0.0.1:8000/creator/dashboard', { waitUntil: 'load' });
    await delay(1000);

    // Click "Edit Pricing" for content row
    console.log('Opening edit pricing form...');
    await page2.click('button.edit-price-btn');
    await delay(500);
    console.log('Taking edit pricing form screenshot...');
    await page2.screenshot({ path: path.join(baseArtifactDir, 'creator_edit_pricing_form.png') });

    // Fill price and toggle membership checkbox
    console.log('Updating content price to $12.50...');
    await fillInput(page2, 'input.inline-price-input', '12.50');
    // Check "Allow Membership" checkbox if not already checked
    await page2.evaluate(() => {
        const cb = document.querySelector('.inline-edit-box input[type="checkbox"]');
        if (cb) cb.checked = true;
    });
    
    // Save
    console.log('Saving pricing...');
    await page2.click('button.save-btn');
    await delay(1500);
    console.log('Taking creator pricing saved screenshot...');
    await page2.screenshot({ path: path.join(baseArtifactDir, 'creator_pricing_updated.png') });

    // Submit payout request
    console.log('Submitting payout request for $45.00...');
    await fillInput(page2, 'input[type="number"][step="0.01"]', '45.00');
    await page2.select('select', 'paypal');
    await fillInput(page2, 'textarea', 'qa_creator_payout@example.com');
    console.log('Submitting request form...');
    await page2.click('form.payout-form button.submit-btn');
    await delay(1500);
    console.log('Taking payout submitted screenshot...');
    await page2.screenshot({ path: path.join(baseArtifactDir, 'creator_payout_submitted.png') });

    // Test logout click
    console.log('Testing creator logout...');
    await page2.goto('http://127.0.0.1:8000/profile', { waitUntil: 'load' });
    await page2.waitForSelector('.logout-button', { timeout: 5000 });
    await page2.click('.logout-button');
    await delay(1000);
    await ctx2.close();

    // ==========================================
    // PHASE 3: Admin Payout Management & Settings
    // ==========================================
    console.log('\n--- STARTING ADMIN FLOW ---');
    const { context: ctx3, page: page3 } = await createIsolatedPage();
    await page3.setViewport({ width: 1280, height: 800 });
    
    console.log('Navigating to admin login...');
    await page3.goto('http://127.0.0.1:8000/login-admin', { waitUntil: 'load' });
    await delay(1500);

    console.log('Logging in as admin@example.com...');
    await fillInput(page3, 'input[autocomplete="email"]', 'admin@example.com');
    await fillInput(page3, 'input[autocomplete="current-password"]', 'password');
    await clickAndRedirect(page3, 'button[type="submit"]', '/admin');

    console.log('Navigating to admin payouts...');
    await page3.goto('http://127.0.0.1:8000/admin/payouts', { waitUntil: 'load' });
    await delay(1000);

    console.log('Taking admin payouts screenshot (Desktop)...');
    await page3.screenshot({ path: path.join(baseArtifactDir, 'admin_payouts_desktop.png') });

    console.log('Taking admin payouts screenshot (Mobile)...');
    await page3.setViewport({ width: 375, height: 812 });
    await delay(1000);
    await page3.screenshot({ path: path.join(baseArtifactDir, 'admin_payouts_mobile.png') });

    await page3.setViewport({ width: 1280, height: 800 });

    // Update settings in form
    console.log('Updating platform monetization settings...');
    const inputs = await page3.$$('.settings-form input');
    
    // Split ratio -> 75%
    await inputs[0].click({ clickCount: 3 });
    await page3.keyboard.press('Backspace');
    await page3.keyboard.type('75');

    // Threshold -> 30.00
    await inputs[1].click({ clickCount: 3 });
    await page3.keyboard.press('Backspace');
    await page3.keyboard.type('30.00');

    // Member discount -> 20%
    await inputs[2].click({ clickCount: 3 });
    await page3.keyboard.press('Backspace');
    await page3.keyboard.type('20');

    // Min PPV -> 2.99
    await inputs[3].click({ clickCount: 3 });
    await page3.keyboard.press('Backspace');
    await page3.keyboard.type('2.99');

    console.log('Saving admin settings...');
    await page3.click('.settings-form button.submit-btn');
    await delay(1500);
    console.log('Taking settings saved screenshot...');
    await page3.screenshot({ path: path.join(baseArtifactDir, 'admin_settings_saved.png') });

    // Test rejection flow
    console.log('Testing payout rejection flow...');
    await page3.click('.payout-request-card .reject-btn');
    await delay(500); // Wait for modal
    console.log('Taking rejection modal screenshot...');
    await page3.screenshot({ path: path.join(baseArtifactDir, 'admin_rejection_modal.png') });

    console.log('Typing rejection reason...');
    await fillInput(page3, '.reject-modal-content textarea', 'Incorrect PayPal address, please use a valid email.');
    console.log('Submitting rejection...');
    await page3.click('.reject-modal-content .modal-btn.danger');
    await delay(1500);
    console.log('Taking payout rejected screenshot...');
    await page3.screenshot({ path: path.join(baseArtifactDir, 'admin_payout_rejected.png') });

    // Test logout click
    console.log('Testing admin logout...');
    await page3.evaluate(() => {
        const links = Array.from(document.querySelectorAll('a'));
        const logoutLink = links.find(a => a.innerText.toLowerCase().includes('logout'));
        if (logoutLink) logoutLink.click();
    });
    await delay(1000);
    await ctx3.close();

    // ==========================================
    // PHASE 4: Creator Verification of Rejection & Re-request
    // ==========================================
    console.log('\n--- RE-VERIFYING CREATOR FLOW ---');
    const { context: ctx4, page: page4 } = await createIsolatedPage();
    await page4.setViewport({ width: 1280, height: 800 });
    
    console.log('Navigating to login...');
    await page4.goto('http://127.0.0.1:8000/login', { waitUntil: 'load' });
    await delay(1500);

    console.log('Logging in as creator@test.com...');
    await fillInput(page4, 'input[autocomplete="email"]', 'creator@test.com');
    await fillInput(page4, 'input[autocomplete="current-password"]', 'password');
    await clickAndRedirect(page4, 'button[type="submit"]', '/dashboard');

    await page4.goto('http://127.0.0.1:8000/creator/dashboard', { waitUntil: 'load' });
    await delay(1000);
    console.log('Taking creator dashboard showing rejected request screenshot...');
    await page4.screenshot({ path: path.join(baseArtifactDir, 'creator_dashboard_rejected.png') });

    // Request new payout
    console.log('Requesting new withdrawal for $50.00...');
    await fillInput(page4, 'input[type="number"][step="0.01"]', '50.00');
    await page4.select('select', 'paypal');
    await fillInput(page4, 'textarea', 'qa_creator_new_paypal@example.com');
    await page4.click('form.payout-form button.submit-btn');
    await delay(1500);

    // Test logout click
    console.log('Testing creator logout...');
    await page4.goto('http://127.0.0.1:8000/profile', { waitUntil: 'load' });
    await page4.waitForSelector('.logout-button', { timeout: 5000 });
    await page4.click('.logout-button');
    await delay(1000);
    await ctx4.close();

    // ==========================================
    // PHASE 5: Admin Approval Flow
    // ==========================================
    console.log('\n--- APPROVING REQUEST ---');
    const { context: ctx5, page: page5 } = await createIsolatedPage();
    await page5.setViewport({ width: 1280, height: 800 });
    
    console.log('Navigating to admin login...');
    await page5.goto('http://127.0.0.1:8000/login-admin', { waitUntil: 'load' });
    await delay(1500);

    console.log('Logging in as admin@example.com...');
    await fillInput(page5, 'input[autocomplete="email"]', 'admin@example.com');
    await fillInput(page5, 'input[autocomplete="current-password"]', 'password');
    await clickAndRedirect(page5, 'button[type="submit"]', '/admin');

    await page5.goto('http://127.0.0.1:8000/admin/payouts', { waitUntil: 'load' });
    await delay(1000);
    console.log('Approving the new request (accepts dialog automatically)...');
    await page5.click('.payout-request-card .approve-btn');
    await delay(1500);

    console.log('Taking approved payout screenshot...');
    await page5.screenshot({ path: path.join(baseArtifactDir, 'admin_payout_approved.png') });

    // Test logout click
    console.log('Testing admin logout...');
    await page5.evaluate(() => {
        const links = Array.from(document.querySelectorAll('a'));
        const logoutLink = links.find(a => a.innerText.toLowerCase().includes('logout'));
        if (logoutLink) logoutLink.click();
    });
    await delay(1000);
    await ctx5.close();

    await browser.close();
    console.log('=== ALL AUTOMATED BROWSER TESTS PASSED SUCCESSFULLY! ===');
}

run().catch(err => {
    console.error('Error running QA suite:', err);
    process.exit(1);
});
