#!/usr/bin/env bash
composer install
rm -rf node_modules
pnpm install
pnpm run production
rm -rf node_modules
