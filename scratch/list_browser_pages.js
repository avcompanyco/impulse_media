import puppeteer from 'puppeteer-core';

async function run() {
    console.log('Connecting to existing browser...');
    const browser = await puppeteer.connect({
        browserURL: 'http://127.0.0.1:9222'
    });

    console.log('Fetching open pages...');
    const pages = await browser.pages();
    console.log(`Found ${pages.length} open pages:`);
    for (let i = 0; i < pages.length; i++) {
        const page = pages[i];
        const title = await page.title().catch(() => 'Untitled');
        const url = page.url();
        console.log(`[Page ${i}] Title: ${title}, URL: ${url}`);
    }

    await browser.disconnect();
}

run().catch(console.error);
