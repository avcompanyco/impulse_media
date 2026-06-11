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
    // Use the tab that had profile/manage (Page 2)
    const page = pages.find(p => p.url().includes('profile/manage') || p.url().includes('profile'));
    if (!page) {
      console.log('Profile tab not found!');
      return;
    }
    
    console.log(`Navigating to https://impulsemedia.me/profile in tab: ${page.url()}`);
    await page.goto('https://impulsemedia.me/profile', { waitUntil: 'networkidle2' });
    await delay(3000);
    
    const bodyText = await page.evaluate(() => document.body.innerText);
    console.log('Is Creator Hub card visible on /profile page:', bodyText.includes('Creator Hub'));
    
    // Take screenshot of Creator Profile
    const profileScreenshotPath = '/Users/antoniovarona/.gemini/antigravity/brain/7e38f2f8-1fb8-414b-a1d7-9b8826746182/profile_page_creator_hub.png';
    await page.screenshot({ path: profileScreenshotPath });
    console.log(`Saved profile screenshot to: ${profileScreenshotPath}`);
    
    // Open the sidebar
    console.log('Opening sidebar...');
    await page.click('#openMenuBtn');
    await delay(1500);
    
    // Take screenshot of open sidebar
    const sidebarScreenshotPath = '/Users/antoniovarona/.gemini/antigravity/brain/7e38f2f8-1fb8-414b-a1d7-9b8826746182/sidebar_open_creator_dashboard.png';
    await page.screenshot({ path: sidebarScreenshotPath });
    console.log(`Saved sidebar screenshot to: ${sidebarScreenshotPath}`);
    
  } catch (err) {
    console.error('Error:', err);
  } finally {
    if (browser) {
      await browser.disconnect();
    }
  }
}

main();
