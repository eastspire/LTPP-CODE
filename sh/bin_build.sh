#!/bin/bash
# Build the LTPP single-file binary, then sync it to the local
# ../LTPP checkout. Requires PHP 8.2 with phar.readonly=0 and
# the `gtl` helper on PATH (operator-specific).
php -d phar.readonly=0 webman build:bin 8.2
php -d phar.readonly=0 webman build:bin 8.2
gtl acp
# ./sh/bin_up.sh
cp ./build/LTPP ../LTPP
cd ../LTPP
gtl acp
