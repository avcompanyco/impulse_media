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
    
    console.log('Navigating to https://impulsemedia.me/profile...');
    await page.goto('https://impulsemedia.me/profile', { waitUntil: 'networkidle2' });
    await delay(2000);
    
    // Check if we are logged in as testspectator or need to log out
    const bodyText = await page.evaluate(() => document.body.innerText);
    if (bodyText.includes('spectator@test.com') || bodyText.includes('Log Out')) {
      console.log('Logging out first...');
      const logoutBtn = await page.$('.logout-button');
      if (logoutBtn) {
        await logoutBtn.click();
        await delay(3000);
      } else {
        console.log('Logout button not found by class, trying to evaluate post request...');
        await page.evaluate(() => {
          // Trigger logout
          const btns = Array.from(document.querySelectorAll('button'));
          const logOutBtn = btns.find(b => b.textContent.includes('Log Out'));
          if (logOutBtn) logOutBtn.click();
        });
        await delay(3000);
      }
    }
    
    // Go to login page
    console.log('Navigating to login page...');
    await page.goto('https://impulsemedia.me/login', { waitUntil: 'networkidle2' });
    await delay(2000);
    
    // Type creator credentials
    console.log('Logging in as creator...');
    // We expect email and password inputs
    await page.type('input[type="email"]', 'creator@test.com');
    await page.type('input[type="password"]', 'password');
    
    // Click submit
    const submitBtn = await page.$('button[type="submit"]');
    if (submitBtn) {
      await submitBtn.click();
    } else {
      await page.keyboard.press('Enter');
    }
    await delay(4000);
    
    // Go to profile page
    console.log('Navigating to profile...');
    await page.goto('https://impulsemedia.me/profile', { waitUntil: 'networkidle2' });
    await delay(3000);
    
    // Check if Creator Hub is visible
    const finalBodyText = await page.evaluate(() => document.body.innerText);
    console.log('Is Creator Hub in page text:', finalBodyText.includes('Creator Hub'));
    console.log('Is Go to Dashboard in page text:', finalBodyText.includes('Go to Dashboard'));
    
    const screenshotPath = '/Users/antoniovarona/.gemini/antigravity/brain/7e38f2f8-1fb8-414b-a1d7-9b8826746182/creator_profile_real.png';
    console.log(`Taking screenshot of Creator Profile: ${screenshotPath}`);
    await page.screenshot({ path: screenshotPath });
    console.log('Screenshot saved!');
    
    await page.close();
  } catch (err) {
    console.error('Error verifying creator UI:', err);
  } finally {
    if (browser) {
      await browser.disconnect();
    }
  }
}

main();
