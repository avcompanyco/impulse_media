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
    
    // Part 1: Verify Creator Channel Page "Dashboard" Shortcut
    const page = pages.find(p => p.url().includes('impulsemedia.me'));
    if (page) {
      console.log('Navigating creator tab to channel page...');
      await page.goto('https://impulsemedia.me/channel/movie', { waitUntil: 'networkidle2' });
      await delay(3000);
      
      const bodyText = await page.evaluate(() => document.body.innerText);
      console.log('Is "Dashboard" button text visible on channel header:', bodyText.includes('Dashboard'));
      
      const screenshotPath = '/Users/antoniovarona/.gemini/antigravity/brain/7e38f2f8-1fb8-414b-a1d7-9b8826746182/creator_channel_reloaded.png';
      await page.screenshot({ path: screenshotPath });
      console.log(`Saved creator channel screenshot to: ${screenshotPath}`);
    }
    
    // Part 2: Verify Admin Panel "Visit Website" Shortcut
    console.log('Opening new page to verify admin panel...');
    const adminPage = await browser.newPage();
    await adminPage.setViewport({ width: 1280, height: 800 });
    
    // Admin login or direct dashboard access if session active
    await adminPage.goto('https://impulsemedia.me/admin', { waitUntil: 'networkidle2' });
    await delay(3000);
    
    const adminBodyText = await adminPage.evaluate(() => document.body.innerText);
    console.log('Is "Visit Website" link visible in admin sidebar:', adminBodyText.includes('Visit Website'));
    
    const adminScreenshotPath = '/Users/antoniovarona/.gemini/antigravity/brain/7e38f2f8-1fb8-414b-a1d7-9b8826746182/admin_sidebar_visit_website.png';
    await adminPage.screenshot({ path: adminScreenshotPath });
    console.log(`Saved admin panel screenshot to: ${adminScreenshotPath}`);
    
    await adminPage.close();
  } catch (err) {
    console.error('Error verifying new shortcuts:', err);
  } finally {
    if (browser) {
      await browser.disconnect();
    }
  }
}

main();
