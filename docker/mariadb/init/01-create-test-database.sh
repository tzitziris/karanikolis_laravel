#!/usr/bin/env bash
set -euo pipefail

: "${MARIADB_ROOT_PASSWORD:?MARIADB_ROOT_PASSWORD is required}"
: "${MARIADB_APP_DATABASE:?MARIADB_APP_DATABASE is required}"
: "${MARIADB_USER:?MARIADB_USER is required}"
: "${MARIADB_PASSWORD:?MARIADB_PASSWORD is required}"
: "${MARIADB_TEST_DATABASE:?MARIADB_TEST_DATABASE is required}"

sql_identifier() {
    local escaped
    escaped="$(printf '%s' "$1" | sed 's/`/``/g')"
    printf '`%s`' "${escaped}"
}

sql_string() {
    local escaped
    escaped="$(printf '%s' "$1" | sed "s/'/''/g")"
    printf "'%s'" "${escaped}"
}

app_database="$(sql_identifier "${MARIADB_APP_DATABASE}")"
test_database="$(sql_identifier "${MARIADB_TEST_DATABASE}")"
database_user="$(sql_string "${MARIADB_USER}")"
database_password="$(sql_string "${MARIADB_PASSWORD}")"

mariadb --protocol=socket -uroot -p"${MARIADB_ROOT_PASSWORD}" <<SQL
CREATE DATABASE IF NOT EXISTS ${app_database}
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

CREATE DATABASE IF NOT EXISTS ${test_database}
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS ${database_user}@'%'
    IDENTIFIED BY ${database_password};

GRANT ALL PRIVILEGES ON ${app_database}.*
    TO ${database_user}@'%';

GRANT ALL PRIVILEGES ON ${test_database}.*
    TO ${database_user}@'%';

FLUSH PRIVILEGES;
SQL
