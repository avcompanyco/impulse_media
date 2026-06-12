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
    
    // Step 1: Navigating to website
    console.log('Navigating to homepage...');
    await page.goto('https://impulsemedia.me/', { waitUntil: 'networkidle2' });
    await delay(2000);
    
    let currentUrl = page.url();
    console.log('Current URL:', currentUrl);
    
    // If not on login page, click login or navigate to login
    if (!currentUrl.includes('/login') && !currentUrl.includes('/dashboard')) {
      console.log('Redirecting to login...');
      await page.goto('https://impulsemedia.me/login', { waitUntil: 'networkidle2' });
      await delay(2000);
    }
    
    currentUrl = page.url();
    if (currentUrl.includes('/login')) {
      console.log('Logging in as testspectator (spectator@test.com)...');
      await page.waitForSelector('input', { timeout: 5000 });
      const inputs = await page.$$('input');
      if (inputs.length >= 2) {
        await inputs[0].focus();
        await inputs[0].type('spectator@test.com', { delay: 100 });
        
        await inputs[1].focus();
        await inputs[1].type('password', { delay: 100 });
      } else {
        console.log('Could not find enough input fields!');
      }
      
      console.log('Clicking login button...');
      await page.click('button[type="submit"]');
      await delay(5000);
      console.log('Logged in successfully, current URL:', page.url());
    }
    
    // Step 2: Open Sidebar & Check Account / Log Out Quick Links
    console.log('Opening Sidebar Drawer...');
    const openMenuBtn = await page.$('#openMenuBtn');
    if (openMenuBtn) {
      await openMenuBtn.click();
      await delay(1500);
      console.log('Sidebar opened!');
    } else {
      console.log('Hamburger menu button not found, is sidebar already open?');
    }
    
    // Check if Account and Log Out text exists in the drawer
    const drawerText = await page.evaluate(() => {
      const menu = document.getElementById('sideMenu');
      return menu ? menu.innerText : '';
    });
    
    console.log('--- Drawer Content Inspect ---');
    console.log('Has "Account" section:', drawerText.includes('Account'));
    console.log('Has "My Account" link:', drawerText.includes('My Account'));
    console.log('Has "Log Out" action:', drawerText.includes('Log Out'));
    console.log('------------------------------');
    
    // Save screenshot of the sidebar
    const sidebarScreenshot = '/Users/antoniovarona/.gemini/antigravity/brain/7e38f2f8-1fb8-414b-a1d7-9b8826746182/audit_purchases_sidebar_reloaded.png';
    await page.screenshot({ path: sidebarScreenshot });
    console.log(`Saved sidebar screenshot to: ${sidebarScreenshot}`);
    
    // Close sidebar menu
    const closeMenuBtn = await page.$('#closeMenuBtn');
    if (closeMenuBtn) {
      try {
        await closeMenuBtn.click();
        await delay(1000);
      } catch (e) {
        console.log('Close button not clickable (desktop layout?), skipping close...');
      }
    }
    
    // Step 3: Play a free movie and check for ad loading
    console.log('Checking for catalog contents on dashboard...');
    // Find all links to movies
    const movieLinks = await page.evaluate(() => {
      return Array.from(document.querySelectorAll('a'))
        .map(a => a.href)
        .filter(href => href.includes('/movie/') && !href.includes('/edit'));
    });
    
    if (movieLinks.length > 0) {
      const firstMovieUrl = movieLinks[0];
      console.log(`Navigating to first movie details: ${firstMovieUrl}`);
      await page.goto(firstMovieUrl, { waitUntil: 'networkidle2' });
      await delay(2000);
      
      // Look for the "Play movie" button
      console.log('Looking for the Play movie button...');
      const playButton = await page.$('.play-button');
      if (playButton) {
        console.log('Play movie button found! Clicking it...');
        await playButton.click();
        await delay(5000);
        
        console.log('Player loaded, current URL:', page.url());
        
        // Check if the Ad Overlay is visible
        const adVisible = await page.evaluate(() => {
          const overlay = document.querySelector('.ad-overlay');
          return overlay !== null && window.getComputedStyle(overlay).display !== 'none';
        });
        
        console.log('Is Ad Overlay visible on player load:', adVisible);
        
        const playerScreenshot = '/Users/antoniovarona/.gemini/antigravity/brain/7e38f2f8-1fb8-414b-a1d7-9b8826746182/spectator_player_with_ad.png';
        await page.screenshot({ path: playerScreenshot });
        console.log(`Saved player screenshot to: ${playerScreenshot}`);
      } else {
        console.log('Play button not found on movie details page.');
      }
    } else {
      console.log('No movie links found on dashboard.');
    }
    
    // Step 4: Perform Logout using sidebar Log Out button
    console.log('Returning to Dashboard to test Logout...');
    await page.goto('https://impulsemedia.me/dashboard', { waitUntil: 'networkidle2' });
    await delay(1500);
    
    console.log('Opening Sidebar Drawer again for Logout...');
    const openMenuBtn2 = await page.$('#openMenuBtn');
    if (openMenuBtn2) {
      await openMenuBtn2.click();
      await delay(1000);
    }
    
    // Click Log Out button in sidebar
    console.log('Clicking Log Out button...');
    await page.evaluate(() => {
      const buttons = Array.from(document.querySelectorAll('button'));
      const logoutBtn = buttons.find(b => b.innerText.includes('Log Out'));
      if (logoutBtn) {
        logoutBtn.click();
      } else {
        console.log('Log Out button not found in page DOM!');
      }
    });
    
    await delay(4000);
    console.log('Current URL after logout:', page.url());
    
    const logoutScreenshot = '/Users/antoniovarona/.gemini/antigravity/brain/7e38f2f8-1fb8-414b-a1d7-9b8826746182/after_logout_landing.png';
    await page.screenshot({ path: logoutScreenshot });
    console.log(`Saved logout page screenshot to: ${logoutScreenshot}`);
    
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
