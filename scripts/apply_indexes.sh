#!/usr/bin/env bash
# Apply SQL indexes from database/index_suggestions.sql
# Usage: DB_HOST=localhost DB_USER=root DB_PASS=root DB_NAME=sipintar_ti ./scripts/apply_indexes.sh

set -e
: ${DB_HOST:=localhost}
: ${DB_USER:=root}
: ${DB_PASS:=}
: ${DB_NAME:=sipintar_ti}

SQL_FILE="$(dirname "$0")/../database/index_suggestions.sql"

if [ ! -f "$SQL_FILE" ]; then
  echo "SQL file not found: $SQL_FILE"
  exit 1
fi

echo "Applying indexes from $SQL_FILE to $DB_NAME@$DB_HOST"
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$SQL_FILE"

echo "Indexes applied."
