import { execSync } from 'child_process';
import fs from 'fs';

const IP = '3.150.160.164';
const KEY_PATH = '/Users/antoniovarona/Desktop/impulse_media/scratch/deploy_key';

async function run() {
    console.log('Testing direct SSH connection using deploy_key...');
    
    // Ensure correct key permissions
    fs.chmodSync(KEY_PATH, 0o600);

    const sshCmd = `ssh -i ${KEY_PATH} -o StrictHostKeyChecking=no -o ConnectTimeout=10 ubuntu@${IP} "echo DIRECT_SSH_SUCCESS"`;
    try {
        const res = execSync(sshCmd).toString();
        console.log('SSH connection result:', res.trim());
    } catch (e) {
        console.error('SSH connection failed:', e.message);
    }
}

run();
