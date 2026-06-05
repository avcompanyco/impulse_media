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
        console.log('Querying /json/version...');
        const resVersion = await get('http://127.0.0.1:9222/json/version');
        console.log('Version status:', resVersion.statusCode);
        console.log('Version headers:', resVersion.headers);
        console.log('Version body:', resVersion.body);
    } catch (e) {
        console.error('Error querying /json/version:', e.message);
    }

    try {
        console.log('\nQuerying /json...');
        const resJson = await get('http://127.0.0.1:9222/json');
        console.log('Json status:', resJson.statusCode);
        console.log('Json body:', resJson.body);
    } catch (e) {
        console.error('Error querying /json:', e.message);
    }
}

run();
