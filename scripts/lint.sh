#!/usr/bin/env bash
# Shell lint script: runs php -l over all PHP files
set -e
files=$(find . -name "*.php" -not -path "./vendor/*")
for f in $files; do
  echo "Checking $f"
  php -l "$f"
done

echo "PHP lint passed."
