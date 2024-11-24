#!/bin/bash
nvm use 20;
npm install -g @vue/cli;
npm install -g @vue/cli-service;
npm install -g yarn;
yarn config set proxy http://127.0.0.1:7890
yarn config set https-proxy http://127.0.0.1:7890
yarn
