import { cpSync, existsSync, mkdirSync, rmSync } from 'node:fs';

if (!existsSync('public/build')) {
    console.error('public/build not found; run "npm run build" first.');
    process.exit(1);
}

rmSync('dist/build', { recursive: true, force: true });
mkdirSync('dist', { recursive: true });
cpSync('public/build', 'dist/build', { recursive: true });
console.log('Copied public/build to dist/build');
