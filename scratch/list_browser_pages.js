import fs from 'fs';
import puppeteer from 'puppeteer-core';

async function run() {
    console.log('Reading DevToolsActivePort...');
    const activePortContent = fs.readFileSync('/Users/antoniovarona/Library/Application Support/Google/Chrome/DevToolsActivePort', 'utf8');
    const lines = activePortContent.trim().split('\n');
    const port = lines[0].trim();
    const path = lines[1].trim();
    const wsUrl = `ws://127.0.0.1:${port}${path}`;
    console.log(`Connecting to WS URL: ${wsUrl}`);

    const browser = await puppeteer.connect({
        browserWSEndpoint: wsUrl,
        defaultViewport: null
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

