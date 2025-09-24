#!/usr/bin/env bash
set -euo pipefail
BRANCH="staging-ci"

# 1) create workflow
mkdir -p .github/workflows
cat > .github/workflows/deploy.yml <<'YML'
name: CI / Build / Deploy

on:
  push:
    branches: ["main"]
  pull_request:
    branches: ["main"]

jobs:
  lint_build_deploy:
    runs-on: ubuntu-latest
    steps:
      - name: Checkout
        uses: actions/checkout@v4

      - name: Setup Node
        uses: actions/setup-node@v4
        with:
          node-version: 18

      - name: Install dependencies
        run: npm ci

      - name: Lint
        run: npm run lint

      - name: Build
        run: npm run build

      - name: Deploy to Netlify
        env:
          NETLIFY_AUTH_TOKEN: ${{ secrets.NETLIFY_AUTH_TOKEN }}
          NETLIFY_SITE_ID: ${{ secrets.NETLIFY_SITE_ID }}
        run: |
          npm i -g netlify-cli
          npx netlify-cli deploy --dir=./dist --prod --site=$NETLIFY_SITE_ID --auth=$NETLIFY_AUTH_TOKEN
YML

# 2) add editor/config files
cat > .prettierrc <<'JSON'
{
  "printWidth": 100,
  "tabWidth": 2,
  "singleQuote": true,
  "trailingComma": "es5",
  "endOfLine": "lf"
}
JSON

cat > .eslintrc.json <<'JSON'
{
  "env": { "browser": true, "es2021": true, "node": true },
  "extends": ["eslint:recommended"],
  "parserOptions": { "ecmaVersion": 2021, "sourceType": "module" },
  "rules": { "no-unused-vars": ["warn"], "no-console": "off" }
}
JSON

cat > .gitignore <<'EOF'
node_modules/
dist/
.build/
.env
.vscode/
EOF

# 3) ensure package.json has scripts
if command -v node >/dev/null 2>&1 && [ -f package.json ]; then
  node -e '
const fs=require("fs");
const p=JSON.parse(fs.readFileSync("package.json","utf8"));
p.scripts=p.scripts||{};
p.scripts.lint=p.scripts.lint||"eslint . --ext .js,.jsx,.ts,.tsx,.html";
p.scripts.build=p.scripts.build||"echo \"No build script defined. Replace with your build command.\" && exit 1";
fs.writeFileSync("package.json", JSON.stringify(p,null,2));
console.log("package.json updated");
'
elif [ ! -f package.json ]; then
  cat > package.json <<'JSON'
{
  "name":"site",
  "version":"1.0.0",
  "private":true,
  "scripts":{
    "lint":"eslint . --ext
