import puppeteer from 'puppeteer-core';

async function run() {
    const browser = await puppeteer.launch({
        executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    const page = await browser.newPage();
    
    page.on('console', msg => console.log('PAGE LOG:', msg.text()));
    
    page.on('response', async response => {
        const url = response.url();
        if (url.includes('127.0.0.1') || url.includes('localhost') || url.includes('impulsemedia')) {
            console.log(`[Response] URL: ${url}, Status: ${response.status()}`);
            if (response.status() >= 400) {
                try {
                    const text = await response.text();
                    console.log(`=== ERROR RESPONSE BODY (${response.status()}) ===`);
                    console.log(text.substring(0, 3000));
                    console.log('=================================');
                } catch (e) {
                    console.log('Could not read response text:', e.message);
                }
            }
        }
    });

    console.log('Navigating to login-admin...');
    await page.goto('https://impulsemedia.me/login-admin', { waitUntil: 'load' });
    
    console.log('Filling form...');
    await page.type('input[autocomplete="email"]', 'admin@example.com');
    await page.type('input[autocomplete="current-password"]', 'password');
    
    console.log('Submitting...');
    await Promise.all([
        page.click('button[type="submit"]'),
        page.waitForNavigation({ waitUntil: 'networkidle0' }).catch(() => {})
    ]);

    console.log('Final URL:', page.url());

    await browser.close();
}

run().catch(console.error);
