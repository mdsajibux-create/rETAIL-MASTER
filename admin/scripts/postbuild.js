const fs = require('fs');
const path = require('path');
const fse = require('fs-extra');

const root = path.resolve(__dirname, '..');
const deployDir = path.join(root, 'deploy');
const buildDir = path.join(root, 'build');

// ---------------------------
// STEP 0: Clean deploy folder
// ---------------------------
fse.removeSync(deployDir);
fs.mkdirSync(deployDir);

// ---------------------------
// STEP 1: Copy standalone build
// ---------------------------

let standalonePath = path.join(buildDir, 'standalone');

if (!fs.existsSync(standalonePath)) {
  throw new Error(
    '❌ "build/standalone" not found. Did you set output: "standalone" in next.config.js?'
  );
}

// Detect nested folder such as build/standalone/admin
const children = fs.readdirSync(standalonePath);
if (children.length === 1) {
  const nested = path.join(standalonePath, children[0]);
  if (
    fs.lstatSync(nested).isDirectory() &&
    fs.existsSync(path.join(nested, 'server.js'))
  ) {
    console.log(`ℹ️ Detected nested standalone dir: ${children[0]}`);
    standalonePath = nested;
  }
}

fse.copySync(standalonePath, deployDir, {
  dereference: true, // ✅ Resolve symlinks — fixes EPERM on Windows
  filter: (src) => {
    const rel = path.relative(standalonePath, src);
    if (rel.startsWith('src')) return false;
    if (src.endsWith('.ts') || src.endsWith('.tsx')) return false;
    if (src.endsWith('.map')) return false;
    return true;
  },
});

// ---------------------------
// STEP 2: Copy .next/static
// ---------------------------
const nextStaticPath = path.join(buildDir, 'static');
const deployNextStaticPath = path.join(deployDir, '_next', 'static');

if (fs.existsSync(nextStaticPath)) {
  fse.ensureDirSync(path.join(deployDir, '_next'));
  fse.copySync(nextStaticPath, deployNextStaticPath, {
    dereference: true, // ✅ Resolve symlinks
  });
  console.log('✅ Copied _next/static');
} else {
  console.log('⚠️ .next/static not found in standalone build');
}

// ---------------------------
// STEP 3: Copy public/ folder
// ---------------------------
const publicPath = path.join(root, 'public');
if (fs.existsSync(publicPath)) {
  fse.copySync(publicPath, path.join(deployDir, 'public'), {
    dereference: true, // ✅ Resolve symlinks
  });
}

// ---------------------------
// STEP 4: Copy package files
// ---------------------------
fse.copySync(
  path.join(root, 'package.json'),
  path.join(deployDir, 'package.json')
);

const packageLockPath = path.join(root, 'package-lock.json');
if (fs.existsSync(packageLockPath)) {
  fse.copySync(packageLockPath, path.join(deployDir, 'package-lock.json'));
}

// ---------------------------
// STEP 5: Copy .env if exists
// ---------------------------
const envPath = path.join(root, '.env');
if (fs.existsSync(envPath)) {
  fse.copySync(envPath, path.join(deployDir, '.env'));
}

// ---------------------------
// STEP 6: Copy node_modules from standalone
// ---------------------------
const nodeModulesPath = path.join(standalonePath, 'node_modules');
if (fs.existsSync(nodeModulesPath)) {
  fse.copySync(nodeModulesPath, path.join(deployDir, 'node_modules'), {
    dereference: true, // ✅ Resolve symlinks — fixes EPERM on Windows
  });
}

console.log('✅ Deploy folder is ready at:', deployDir);