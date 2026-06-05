import http from 'http';

function get(url) {
    return new Promise((resolve, reject) => {
        http.get(url, (res) => {
            let data = '';
            res.on('data', (chunk) => data += chunk);
            res.on('end', () => resolve({ statusCode: res.statusCode, body: data, headers: res.headers }));
        }).on('error', reject);
    });
}

async function run() {
    try {
        console.log('Querying root...');
        const res = await get('http://127.0.0.1:9222/');
        console.log('Root status:', res.statusCode);
        console.log('Root headers:', res.headers);
        console.log('Root body:', res.body);
    } catch (e) {
        console.error('Error querying root:', e.message);
    }
}

run();
