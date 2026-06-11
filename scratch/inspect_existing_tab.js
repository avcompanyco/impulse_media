import fs from 'fs';
import puppeteer from 'puppeteer-core';

async function delay(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

async function main() {
  let browser;
  try {
    const activePortContent = fs.readFileSync('/Users/antoniovarona/Library/Application Support/Google/Chrome/DevToolsActivePort', 'utf8');
    const lines = activePortContent.trim().split('\n');
    const port = lines[0].trim();
    const path = lines[1].trim();
    const wsUrl = `ws://127.0.0.1:${port}${path}`;
    console.log(`Connecting to WS URL: ${wsUrl}`);
    
    browser = await puppeteer.connect({
      browserWSEndpoint: wsUrl,
      defaultViewport: null
    });
    console.log('Connected successfully!');
    
    const pages = await browser.pages();
    console.log(`Open pages count: ${pages.length}`);
    for (let i = 0; i < pages.length; i++) {
      console.log(`Page ${i}: ${pages[i].url()} (${await pages[i].title()})`);
    }
    
    const page = pages.find(p => p.url().includes('impulsemedia.me'));
    if (!page) {
      console.log('No impulsemedia.me tab found!');
      return;
    }
    
    console.log(`Inspecting page: ${page.url()}`);
    await page.bringToFront();
    await delay(1000);
    
    const bodyText = await page.evaluate(() => document.body.innerText);
    console.log('--- Body Text ---');
    console.log(bodyText.slice(0, 1000));
    
    const screenshotPath = '/Users/antoniovarona/.gemini/antigravity/brain/7e38f2f8-1fb8-414b-a1d7-9b8826746182/existing_impulse_tab.png';
    await page.screenshot({ path: screenshotPath });
    console.log(`Screenshot saved to: ${screenshotPath}`);
    
  } catch (err) {
    console.error('Error:', err);
  } finally {
    if (browser) {
      await browser.disconnect();
    }
  }
}

main();
