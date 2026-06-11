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
    const page = pages.find(p => p.url().includes('ec2-instance-connect'));
    if (!page) {
      console.error('EC2 Instance Connect page not found!');
      process.exit(1);
    }
    
    console.log(`Found page: ${page.url()}`);
    
    // Bring page to front
    await page.bringToFront();
    
    // Locate the xterm helper textarea
    const textareaSelector = 'textarea.xterm-helper-textarea';
    await page.waitForSelector(textareaSelector, { timeout: 10000 });
    
    console.log('Focusing on the terminal...');
    await page.focus(textareaSelector);
    
    // Click on the terminal to ensure focus is active
    try {
      const terminalEl = await page.$('.xterm');
      if (terminalEl) {
        await terminalEl.click();
      }
    } catch (e) {
      console.log('Click on terminal container failed/skipped:', e.message);
    }
    await page.focus(textareaSelector);
    
    // Clear prompt with Ctrl+C
    console.log('Sending Ctrl+C...');
    await page.keyboard.down('Control');
    await page.keyboard.press('c');
    await page.keyboard.up('Control');
    await delay(1500);
    
    // Send clean and reset commands
    const resetCmd = 'git reset --hard HEAD && git clean -fd';
    console.log(`Sending reset command: ${resetCmd}`);
    await page.keyboard.type(resetCmd);
    await delay(500);
    await page.keyboard.press('Enter');
    await delay(2000);
    
    // Send pull and optimize commands
    const deployCmd = 'git pull origin main && php artisan migrate --force && php artisan storage:link && php artisan optimize:clear && php artisan config:cache && php artisan route:cache && echo "DEPLOYMENT_OUTPUT_MARKER_SUCCESS"';
    console.log(`Sending deploy command: ${deployCmd}`);
    await page.keyboard.type(deployCmd);
    await delay(500);
    await page.keyboard.press('Enter');
    
    console.log('Command executed. Waiting for completion...');
    
    const maxWaitTimeMs = 180000; // 3 minutes max
    const intervalMs = 3000;
    let elapsedMs = 0;
    let success = false;
    
    while (elapsedMs < maxWaitTimeMs) {
      await delay(intervalMs);
      elapsedMs += intervalMs;
      
      const terminalText = await page.evaluate(() => {
        const items = Array.from(document.querySelectorAll('.xterm-accessibility-tree [role="listitem"]'));
        return items.map(el => el.textContent).join('\n');
      });
      
      console.log(`--- Terminal Output (Elapsed: ${elapsedMs/1000}s) ---`);
      const outputLines = terminalText.split('\n');
      const lastLines = outputLines.slice(-15);
      console.log(lastLines.join('\n'));
      
      // We check for DEPLOYMENT_OUTPUT_MARKER_SUCCESS, but we must make sure it's not the typed command line
      // The typed command line has "echo \"DEPLOYMENT_OUTPUT_MARKER_SUCCESS\"" or similar.
      // The output line will just be "DEPLOYMENT_OUTPUT_MARKER_SUCCESS".
      // So let's look for a line in outputLines that is EXACTLY "DEPLOYMENT_OUTPUT_MARKER_SUCCESS" (ignoring whitespace).
      const hasSuccessLine = outputLines.some(line => line.trim() === 'DEPLOYMENT_OUTPUT_MARKER_SUCCESS');
      
      if (hasSuccessLine) {
        console.log('SUCCESS: DEPLOYMENT_OUTPUT_MARKER_SUCCESS detected as a separate line!');
        success = true;
        break;
      }
    }
    
    if (!success) {
      throw new Error('Timeout waiting for deployment completion');
    }
    
    // Take a screenshot of the page
    const screenshotPath = '/Users/antoniovarona/.gemini/antigravity/brain/7e38f2f8-1fb8-414b-a1d7-9b8826746182/deployment_success_real.png';
    console.log(`Taking screenshot and saving to: ${screenshotPath}`);
    await page.screenshot({ path: screenshotPath, fullPage: false });
    console.log('Screenshot saved!');
    
  } catch (err) {
    console.error('Error during deployment:', err);
    process.exit(1);
  } finally {
    if (browser) {
      await browser.disconnect();
      console.log('Disconnected from browser');
    }
  }
}

main();
