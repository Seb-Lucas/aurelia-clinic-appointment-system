#!/bin/sh
set -eu

database_path="${DB_DATABASE:-/var/data/app.sqlite}"
database_directory="$(dirname "$database_path")"

if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ] && [ "$database_path" != ":memory:" ]; then
    mkdir -p "$database_directory"
    chown www-data:www-data "$database_directory"
    chmod 0750 "$database_directory"
fi

if [ "${PORTFOLIO_DEMO_STANDALONE:-false}" = "true" ]; then
    if [ "${DB_CONNECTION:-sqlite}" != "sqlite" ]; then
        echo "Standalone portfolio demo provisioning requires SQLite." >&2
        exit 1
    fi
    if [ -z "${PORTFOLIO_DEMO_PASSWORD:-}" ]; then
        echo "PORTFOLIO_DEMO_PASSWORD must be set to provision the standalone portfolio demo." >&2
        exit 1
    fi

    PORTFOLIO_DEMO_QUIET=true php scripts/create-portfolio-demo.php
    chown www-data:www-data "$database_path"
fi

exec docker-php-entrypoint "$@"
