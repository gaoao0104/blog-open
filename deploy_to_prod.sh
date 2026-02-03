#!/bin/bash
set -euo pipefail

SRC="/var/www/blog-test/"
DST="/var/www/blog/"
TEST_CONFIG="/var/www/blog-test/app/config/config.php"
PROD_CONFIG="/var/www/blog/app/config/config.php"
MIGRATIONS_DIR="/var/www/blog-test/app/database/migrations"
MIGRATE_SCRIPT="/var/www/blog-test/app/database/migrate.php"

get_db_value() {
  local cfg="$1"
  local key="$2"
  php -r "\$c = require '$cfg'; echo \$c['db']['$key'] ?? '';"
}

dump_schema() {
  local dbname="$1"
  local user="$2"
  local pass="$3"
  local host="$4"
  local socket="$5"
  local outfile="$6"

  local args=(--no-data --skip-comments --skip-add-drop-table --skip-lock-tables --skip-set-charset --routines --triggers --no-tablespaces "$dbname")
  if [[ -n "$socket" ]]; then
    args+=(--socket="$socket")
  else
    args+=(--host="$host")
  fi

  MYSQL_PWD="$pass" mysqldump -u "$user" "${args[@]}" \
    | sed -E 's/AUTO_INCREMENT=[0-9]+//g; s/ CHARACTER SET [^ ]+//g' \
    > "$outfile"
}

check_db_schema() {
  local test_name prod_name test_user prod_user test_pass prod_pass test_host prod_host test_socket prod_socket

  test_name="$(get_db_value "$TEST_CONFIG" name)"
  prod_name="$(get_db_value "$PROD_CONFIG" name)"
  test_user="$(get_db_value "$TEST_CONFIG" user)"
  prod_user="$(get_db_value "$PROD_CONFIG" user)"
  test_pass="$(get_db_value "$TEST_CONFIG" pass)"
  prod_pass="$(get_db_value "$PROD_CONFIG" pass)"
  test_host="$(get_db_value "$TEST_CONFIG" host)"
  prod_host="$(get_db_value "$PROD_CONFIG" host)"
  test_socket="$(get_db_value "$TEST_CONFIG" socket)"
  prod_socket="$(get_db_value "$PROD_CONFIG" socket)"

  local tmpdir
  tmpdir="$(mktemp -d)"

  dump_schema "$test_name" "$test_user" "$test_pass" "$test_host" "$test_socket" "$tmpdir/test.sql"
  dump_schema "$prod_name" "$prod_user" "$prod_pass" "$prod_host" "$prod_socket" "$tmpdir/prod.sql"

  if diff -u "$tmpdir/prod.sql" "$tmpdir/test.sql"; then
    rm -rf "$tmpdir"
    echo "DB schema check: no differences."
    return 0
  fi

  rm -rf "$tmpdir"
  echo "DB schema check: differences detected."
  return 1
}

if [[ "${1:-}" == "--check-db" ]]; then
  check_db_schema
  exit $?
fi

if ! check_db_schema; then
  echo "DB schema differs between test and prod."
  read -r -p "Continue code sync anyway? [y/N]: " reply
  if [[ ! "$reply" =~ ^[Yy]$ ]]; then
    echo "Sync cancelled."
    exit 1
  fi
fi

if [[ -f "$MIGRATE_SCRIPT" && -d "$MIGRATIONS_DIR" ]]; then
  if ! php "$MIGRATE_SCRIPT" --config="$PROD_CONFIG" --migrations="$MIGRATIONS_DIR" --dry-run; then
    echo "Pending migrations found for production."
    read -r -p "Apply migrations to production now? [y/N]: " mig_reply
    if [[ "$mig_reply" =~ ^[Yy]$ ]]; then
      php "$MIGRATE_SCRIPT" --config="$PROD_CONFIG" --migrations="$MIGRATIONS_DIR"
    else
      echo "Migrations not applied."
    fi
  fi
fi

rsync -a --delete \
  --exclude 'app/config/config.php' \
  --exclude 'public/uploads' \
  --exclude 'public/.well-known' \
  --exclude '.env' \
  "$SRC" "$DST"

echo "Sync complete: $SRC -> $DST"
