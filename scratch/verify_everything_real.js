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
    
    const page = await browser.newPage();
    await page.setViewport({ width: 1280, height: 800 });
    
    // Step 1: Verify Creator Channel Page "Dashboard" Button
    console.log('Navigating to Creator Channel...');
    await page.goto('https://impulsemedia.me/channel/movie', { waitUntil: 'networkidle2' });
    await delay(2000);
    console.log('Force reloaded page to bypass cache...');
    await page.reload({ waitUntil: 'networkidle2' });
    await delay(2000);
    
    const bodyText = await page.evaluate(() => document.body.innerText);
    console.log('Creator Channel URL:', page.url());
    console.log('Is "Dashboard" button visible on channel header:', bodyText.includes('Dashboard'));
    
    const channelScreenshot = '/Users/antoniovarona/.gemini/antigravity/brain/7e38f2f8-1fb8-414b-a1d7-9b8826746182/creator_channel_reloaded_real.png';
    await page.screenshot({ path: channelScreenshot });
    console.log(`Saved creator channel screenshot to: ${channelScreenshot}`);
    
    // Step 2: Verify Admin Panel "Visit Website" Link
    console.log('Navigating to Admin Panel...');
    await page.goto('https://impulsemedia.me/admin', { waitUntil: 'networkidle2' });
    await delay(2500);
    
    let currentUrl = page.url();
    console.log('Admin Panel URL:', currentUrl);
    
    if (currentUrl.includes('login-admin')) {
      console.log('Admin session expired. Logging in as admin...');
      await page.type('input[type="email"]', 'mantenimientodelavadorasen.cali@gmail.com');
      await page.type('input[type="password"]', 'password');
      await page.keyboard.press('Enter');
      await delay(4000);
      console.log('After login URL:', page.url());
    }
    
    const adminBodyText = await page.evaluate(() => document.body.innerText);
    console.log('Is "Visit Website" link visible in admin sidebar:', adminBodyText.includes('Visit Website'));
    
    const adminScreenshot = '/Users/antoniovarona/.gemini/antigravity/brain/7e38f2f8-1fb8-414b-a1d7-9b8826746182/admin_sidebar_visit_website_real.png';
    await page.screenshot({ path: adminScreenshot });
    console.log(`Saved admin panel screenshot to: ${adminScreenshot}`);
    
    await page.close();
  } catch (err) {
    console.error('Error during verification:', err);
  } finally {
    if (browser) {
      await browser.disconnect();
    }
  }
}

main();
