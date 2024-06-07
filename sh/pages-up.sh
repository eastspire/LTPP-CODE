#!/bin/bash
set -e
cd ./Frontend
npm run build
cd ./frontend
git init
git add -A
git commit -m 'deploy'
git remote add origin git@github.com:ltpp-universe/LTPP-FRONTEND.git
rm -rf ../frontend