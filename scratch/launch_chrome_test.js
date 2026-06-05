import puppeteer from 'puppeteer-core';
import path from 'path';

const delay = ms => new Promise(resolve => setTimeout(resolve, ms));

async function run() {
    console.log('Launching Chrome with persistent agent profile...');
    const browser = await puppeteer.launch({
        executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
        headless: true,
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--user-data-dir=/Users/antoniovarona/.gemini/antigravity/chrome-profile'
        ]
    });

    console.log('Opening page...');
    const page = await browser.newPage();
    await page.setViewport({ width: 1280, height: 800 });

    const awsUrl = 'https://us-east-2.console.aws.amazon.com/ec2-instance-connect/ssh/home?region=us-east-2&connType=standard&instanceId=i-0e8979285efc5b4f3&osUser=ubuntu&sshPort=22&addressFamily=ipv4';
    console.log(`Navigating to AWS URL: ${awsUrl}`);
    await page.goto(awsUrl, { waitUntil: 'load', timeout: 60000 });

    console.log('Waiting 15 seconds for terminal/login page to load...');
    await delay(15000);

    const screenshotPath = '/Users/antoniovarona/Desktop/impulse_media/scratch/aws_connect_loaded.png';
    console.log(`Saving screenshot to: ${screenshotPath}`);
    await page.screenshot({ path: screenshotPath });

    await browser.close();
    console.log('Browser closed. Done!');
}

run().catch(console.error);
