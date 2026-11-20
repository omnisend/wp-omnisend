#!/usr/bin/env bash
# Runs the integration tests locally against a MySQL container (DB_IMAGE overrides the image). Extra arguments go to PHPUnit.
set -euo pipefail

repo_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/../../.." && pwd)"
container="omnisend-wp-integration-db"
port="${DB_PORT:-33306}"
image="${DB_IMAGE:-mysql:8.0}"

if ! docker ps --format '{{.Names}}' | grep -qx "$container"; then
	docker rm -f "$container" >/dev/null 2>&1 || true
	docker run -d --name "$container" -e MYSQL_ROOT_PASSWORD=root -e MYSQL_DATABASE=wordpress_tests \
		-p "127.0.0.1:$port:3306" "$image" >/dev/null
fi

until docker exec "$container" mysqladmin ping -h 127.0.0.1 -proot --silent >/dev/null 2>&1; do
	sleep 1
done

DB_NAME=wordpress_tests DB_USER=root DB_PASSWORD=root DB_HOST="127.0.0.1:$port" "$repo_root/tests/Integration/bin/install-wp.sh"
composer install --working-dir="$repo_root/tests/Integration" --prefer-dist --no-progress --quiet
"$repo_root/tests/Integration/vendor/bin/phpunit" -c "$repo_root/tests/Integration/phpunit.xml.dist" "$@"
