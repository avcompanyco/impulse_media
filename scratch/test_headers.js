import http from 'http';

function get(url, hostHeader) {
    const urlObj = new URL(url);
    return new Promise((resolve, reject) => {
        const options = {
            hostname: urlObj.hostname,
            port: urlObj.port,
            path: urlObj.pathname,
            method: 'GET',
            headers: {
                'Host': hostHeader
            }
        };
        http.request(options, (res) => {
            let data = '';
            res.on('data', (chunk) => data += chunk);
            res.on('end', () => resolve({ statusCode: res.statusCode, body: data, headers: res.headers }));
        }).on('error', reject).end();
    });
}

async function run() {
    const hosts = ['localhost:9222', '127.0.0.1:9222', 'localhost', '127.0.0.1'];
    for (const host of hosts) {
        console.log(`\nQuerying /json with Host: "${host}"`);
        try {
            const res = await get('http://127.0.0.1:9222/json', host);
            console.log('Status:', res.statusCode);
            console.log('Body:', res.body);
        } catch (e) {
            console.error('Error:', e.message);
        }
    }
}

run();
