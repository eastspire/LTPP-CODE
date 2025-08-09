#!/bin/bash
set -e
nvm use 20
cd ./Frontend
rm -rf ./frontend
yarn
yarn run build
cd ./frontend
cp ../../vercel.json ./
git init
git add -A
git commit -m 'deploy'
git push -f git@github.com:eastspire/LTPP-FRONTEND.git master:master
echo "Press Enter to continue..."
read -n 1
