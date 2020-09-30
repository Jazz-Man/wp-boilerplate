#!/usr/bin/env bash
composer install
rm -rf node_modules
npm install
npm run production
rm -rf node_modules