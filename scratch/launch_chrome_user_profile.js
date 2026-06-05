import puppeteer from 'puppeteer-core';
import path from 'path';

const delay = ms => new Promise(resolve => setTimeout(resolve, ms));

async function run() {
    console.log('Launching headless Chrome to access AWS EC2 Instance Connect...');
    const browser = await puppeteer.launch({
        executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
        headless: true,
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--user-data-dir=/Users/antoniovarona/Library/Application Support/Google/Chrome',
            '--profile-directory=Default'
        ]
    });

    console.log('Opening page...');
    const page = await browser.newPage();
    await page.setViewport({ width: 1280, height: 800 });

    const awsUrl = 'https://us-east-2.console.aws.amazon.com/ec2-instance-connect/ssh/home?region=us-east-2&connType=standard&instanceId=i-0e8979285efc5b4f3&osUser=ubuntu&sshPort=22&addressFamily=ipv4';
    console.log(`Navigating to AWS URL: ${awsUrl}`);
    await page.goto(awsUrl, { waitUntil: 'load', timeout: 60000 });

    console.log('Waiting 15 seconds for page/terminal/login status...');
    await delay(15000);

    const screenshotPath1 = '/Users/antoniovarona/Desktop/impulse_media/scratch/aws_user_chrome_profile_loaded.png';
    console.log(`Saving initial screenshot to: ${screenshotPath1}`);
    await page.screenshot({ path: screenshotPath1 });

    await browser.close();
    console.log('Browser closed.');
}

run().catch(console.error);
