#!/usr/bin/env node

/**
 * Bundle Size Analyzer
 * 
 * Analyzes the production build output and reports on asset sizes
 */

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const buildDir = path.join(__dirname, '..', 'public', 'build');
const assetsDir = path.join(buildDir, 'assets');

function formatBytes(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function getCompressionRatio(original, compressed) {
    return ((1 - compressed / original) * 100).toFixed(1) + '%';
}

function analyzeAssets() {
    console.log('\n📊 Bundle Size Analysis\n');
    console.log('='.repeat(80));
    
    if (!fs.existsSync(buildDir)) {
        console.error('❌ Build directory not found. Run "npm run build" first.');
        process.exit(1);
    }

    const files = fs.readdirSync(assetsDir);
    const assets = {};

    // Group files by base name
    files.forEach(file => {
        const baseName = file.replace(/\.(br|gz)$/, '');
        if (!assets[baseName]) {
            assets[baseName] = {};
        }

        const filePath = path.join(assetsDir, file);
        const stats = fs.statSync(filePath);

        if (file.endsWith('.br')) {
            assets[baseName].brotli = stats.size;
        } else if (file.endsWith('.gz')) {
            assets[baseName].gzip = stats.size;
        } else {
            assets[baseName].original = stats.size;
        }
    });

    // Display results
    let totalOriginal = 0;
    let totalGzip = 0;
    let totalBrotli = 0;

    console.log('\n📦 Asset Sizes:\n');
    console.log('File'.padEnd(40) + 'Original'.padEnd(15) + 'Gzip'.padEnd(15) + 'Brotli'.padEnd(15) + 'Savings');
    console.log('-'.repeat(100));

    Object.entries(assets).forEach(([name, sizes]) => {
        if (sizes.original) {
            totalOriginal += sizes.original;
            totalGzip += sizes.gzip || 0;
            totalBrotli += sizes.brotli || 0;

            const gzipRatio = sizes.gzip ? getCompressionRatio(sizes.original, sizes.gzip) : 'N/A';
            const brotliRatio = sizes.brotli ? getCompressionRatio(sizes.original, sizes.brotli) : 'N/A';

            console.log(
                name.padEnd(40) +
                formatBytes(sizes.original).padEnd(15) +
                (sizes.gzip ? formatBytes(sizes.gzip) : 'N/A').padEnd(15) +
                (sizes.brotli ? formatBytes(sizes.brotli) : 'N/A').padEnd(15) +
                `(${brotliRatio})`
            );
        }
    });

    console.log('-'.repeat(100));
    console.log(
        'TOTAL'.padEnd(40) +
        formatBytes(totalOriginal).padEnd(15) +
        formatBytes(totalGzip).padEnd(15) +
        formatBytes(totalBrotli).padEnd(15) +
        `(${getCompressionRatio(totalOriginal, totalBrotli)})`
    );

    // Performance recommendations
    console.log('\n💡 Performance Recommendations:\n');

    if (totalOriginal > 200 * 1024) {
        console.log('⚠️  Total bundle size exceeds 200KB. Consider:');
        console.log('   - Code splitting for large features');
        console.log('   - Lazy loading non-critical components');
        console.log('   - Removing unused dependencies');
    } else {
        console.log('✅ Bundle size is within recommended limits');
    }

    const cssFiles = Object.keys(assets).filter(f => f.endsWith('.css'));
    const jsFiles = Object.keys(assets).filter(f => f.endsWith('.js'));

    console.log(`\n📄 CSS Files: ${cssFiles.length}`);
    console.log(`📜 JS Files: ${jsFiles.length}`);

    // Check for large individual files
    Object.entries(assets).forEach(([name, sizes]) => {
        if (sizes.original > 100 * 1024) {
            console.log(`\n⚠️  Large file detected: ${name} (${formatBytes(sizes.original)})`);
            console.log('   Consider splitting this file or reviewing its contents');
        }
    });

    console.log('\n' + '='.repeat(80) + '\n');
}

try {
    analyzeAssets();
} catch (error) {
    console.error('❌ Error analyzing bundle:', error.message);
    process.exit(1);
}
