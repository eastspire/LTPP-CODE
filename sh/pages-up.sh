#!/bin/bash
set -e
cd ./Frontend
npm run build
cd ./frontend
cp ../../vercel.json ./
git init
git add -A
git commit -m 'deploy'
git push -f git@github.com:ltpp-universe/LTPP-FRONTEND.git master:master
rm -rf ../frontend
echo "Press Enter to continue..."
read -n 1