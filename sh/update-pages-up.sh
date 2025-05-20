#!/bin/bash
set -e
cd ./Frontend
yarn run build
cd ./frontend
cp ../../vercel.json ./
git init
git add -A
git commit -m 'deploy'
git push -f git@github.com:eastspire/LTPP-FRONTEND.git master:master
rm -rf ../frontend
echo "Press Enter to continue..."
read -n 1
