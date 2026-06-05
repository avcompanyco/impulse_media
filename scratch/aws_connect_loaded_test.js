import puppeteer from 'puppeteer-core';
import path from 'path';

const delay = ms => new Promise(resolve => setTimeout(resolve, ms));

async function run() {
    console.log('Connecting to browser on 127.0.0.1:9222...');
    const browser = await puppeteer.connect({
        browserURL: 'http://127.0.0.1:9222'
    });

    console.log('Creating a new page...');
    const page = await browser.newPage();
    await page.setViewport({ width: 1280, height: 800 });

    const awsUrl = 'https://us-east-2.console.aws.amazon.com/ec2-instance-connect/ssh/home?region=us-east-2&connType=standard&instanceId=i-0e8979285efc5b4f3&osUser=ubuntu&sshPort=22&addressFamily=ipv4';
    console.log(`Navigating to: ${awsUrl}`);
    await page.goto(awsUrl, { waitUntil: 'load', timeout: 60000 });

    console.log('Waiting 15 seconds for terminal to load...');
    await delay(15000);

    const screenshotPath = '/Users/antoniovarona/Desktop/impulse_media/scratch/aws_connect_loaded.png';
    console.log(`Saving screenshot to: ${screenshotPath}`);
    await page.screenshot({ path: screenshotPath });

    await browser.disconnect();
    console.log('Done!');
}

run().catch(console.error);
