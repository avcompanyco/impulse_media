import { execSync } from 'child_process';
import fs from 'fs';

const ACCESS_KEY = 'AKIAXLT4JJ2N2DMW7TH6';
const SECRET_KEY = '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';
const REGION = 'us-east-2';
const INSTANCE_ID = 'i-0e8979285efc5b4f3';
const IP = '3.150.160.164';
const KEY_PATH = '/Users/antoniovarona/Desktop/impulse_media/scratch/deploy_key';
const PUB_KEY_PATH = '/Users/antoniovarona/Desktop/impulse_media/scratch/deploy_key.pub';

async function run() {
    console.log('Starting deployment and diagnostic script...');
    
    // Ensure correct key permissions
    fs.chmodSync(KEY_PATH, 0o600);

    const zones = ['us-east-2b', 'us-east-2a', 'us-east-2c'];
    let sentSuccess = false;

    for (const zone of zones) {
        console.log(`Attempting to send SSH public key to zone ${zone}...`);
        const cmd = `AWS_ACCESS_KEY_ID=${ACCESS_KEY} AWS_SECRET_ACCESS_KEY=${SECRET_KEY} AWS_DEFAULT_REGION=${REGION} aws ec2-instance-connect send-ssh-public-key --instance-id ${INSTANCE_ID} --availability-zone ${zone} --instance-os-user ubuntu --ssh-public-key file://${PUB_KEY_PATH}`;
        try {
            const res = execSync(cmd).toString();
            console.log(`Success sending public key to zone ${zone}:`, res);
            sentSuccess = true;
            break;
        } catch (e) {
            console.warn(`Failed sending to zone ${zone}:`, e.message);
        }
    }

    if (!sentSuccess) {
        throw new Error('Failed to send SSH public key to any availability zone.');
    }

    console.log('Key uploaded. Connecting via SSH to run commands...');

    const runRemoteCommand = (remoteCmd) => {
        const sshCmd = `ssh -i ${KEY_PATH} -o StrictHostKeyChecking=no ubuntu@${IP} "${remoteCmd}"`;
        return execSync(sshCmd).toString();
    };

    console.log('\n--- RUNNING DEPLOYMENT COMMANDS ---');
    const deployCmd = 'cd /var/www/html/project-impulse && git pull origin main && php artisan migrate --force && php artisan storage:link && php artisan optimize:clear && php artisan config:cache && php artisan route:cache && echo DEPLOY_DONE';
    const deployOutput = runRemoteCommand(deployCmd);
    console.log(deployOutput);

    console.log('\n--- DIAGNOSTICS ---');
    
    const cmds = {
        'pwd': 'pwd',
        'git_log': 'cd /var/www/html/project-impulse && git log -n 1',
        'apache_conf': 'cat /etc/apache2/sites-enabled/000-default.conf | grep -i documentroot',
        'ls_html': 'ls -la /var/www/html',
        'artisan_routes': 'cd /var/www/html/project-impulse && php artisan route:list | grep debug-action',
        'laravel_log': 'tail -n 100 /var/www/html/project-impulse/storage/logs/laravel.log'
    };

    for (const [name, cmd] of Object.entries(cmds)) {
        console.log(`\n--- Running command: ${cmd} ---`);
        try {
            const output = runRemoteCommand(cmd);
            console.log(output);
        } catch (e) {
            console.error(`Error running ${name}:`, e.message);
        }
    }
}

run().catch(err => {
    console.error('Execution failed:', err);
    process.exit(1);
});
