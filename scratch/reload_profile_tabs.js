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
    const page = pages.find(p => p.url().includes('profile/manage'));
    if (!page) {
      console.log('Manage profile page not found!');
      return;
    }
    
    console.log(`Reloading page: ${page.url()}`);
    await page.reload({ waitUntil: 'networkidle2' });
    await delay(3000);
    
    // Check if Creator Dashboard is in body text or sidebar
    const bodyText = await page.evaluate(() => document.body.innerText);
    console.log('Is Creator Dashboard in body text after reload:', bodyText.includes('Creator Dashboard'));
    
    const sidebarHtml = await page.evaluate(() => {
      const el = document.querySelector('.side-menu');
      return el ? el.outerHTML : 'No sidebar found';
    });
    console.log('Sidebar outer html includes /creator/dashboard:', sidebarHtml.includes('/creator/dashboard'));
    
    const screenshotPath = '/Users/antoniovarona/.gemini/antigravity/brain/7e38f2f8-1fb8-414b-a1d7-9b8826746182/profile_manage_reloaded.png';
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
