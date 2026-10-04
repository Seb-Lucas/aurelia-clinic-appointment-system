#!/bin/sh
set -eu

database_path="${DB_DATABASE:-/var/data/app.sqlite}"
database_directory="$(dirname "$database_path")"

if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ] && [ "$database_path" != ":memory:" ]; then
    mkdir -p "$database_directory"
    chown www-data:www-data "$database_directory"
    chmod 0750 "$database_directory"
fi

exec docker-php-entrypoint "$@"
