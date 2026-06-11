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
    
    let impulseIndex = 0;
    for (let i = 0; i < pages.length; i++) {
      const page = pages[i];
      const url = page.url();
      const title = await page.title();
      console.log(`Page ${i}: ${url} (${title})`);
      
      if (url.includes('impulsemedia.me')) {
        console.log(`Inspecting impulsemedia.me tab: ${url}`);
        await page.bringToFront();
        await delay(1000);
        
        const bodyText = await page.evaluate(() => document.body.innerText);
        console.log(`--- Body Text for tab ${i} ---`);
        console.log(bodyText.slice(0, 500));
        
        const screenshotPath = `/Users/antoniovarona/.gemini/antigravity/brain/7e38f2f8-1fb8-414b-a1d7-9b8826746182/impulse_tab_${impulseIndex}.png`;
        await page.screenshot({ path: screenshotPath });
        console.log(`Screenshot saved to: ${screenshotPath}`);
        impulseIndex++;
      }
    }
    
  } catch (err) {
    console.error('Error:', err);
  } finally {
    if (browser) {
      await browser.disconnect();
    }
  }
}

main();
