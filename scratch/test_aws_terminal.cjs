const puppeteer = require('puppeteer-core');

async function run() {
    console.log('Connecting to existing Chrome on port 9222...');
    const browser = await puppeteer.connect({
        browserURL: 'http://localhost:9222',
        defaultViewport: null
    });

    console.log('Fetching open pages...');
    const pages = await browser.pages();
    console.log(`Found ${pages.length} pages:`);
    for (let i = 0; i < pages.length; i++) {
        try {
            const title = await pages[i].title();
            const url = pages[i].url();
            console.log(`[${i}] Title: "${title}" | URL: ${url}`);
        } catch (e) {
            console.log(`[${i}] Error getting details: ${e.message}`);
        }
    }
    
    // Check if any page is the AWS EC2 Instance Connect or Session Manager page
    let targetPage = null;
    for (const page of pages) {
        try {
            const url = page.url();
            if (url.includes('ec2-instance-connect') || url.includes('console.aws.amazon.com')) {
                targetPage = page;
                console.log(`Found matching AWS page: ${url}`);
            }
        } catch (e) {}
    }
    
    if (targetPage) {
        console.log('Found AWS page. Title:', await targetPage.title());
    } else {
        console.log('No AWS page found.');
    }

    await browser.disconnect();
}

run().catch(console.error);
