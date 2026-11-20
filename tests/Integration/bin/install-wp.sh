#!/usr/bin/env bash
# Installs the WordPress core and PHPUnit test library used by the integration tests.
# WordPress version: WP_VERSION, otherwise "Tested up to" from omnisend/readme.txt (latest patch release).
# Database: DB_NAME, DB_USER, DB_PASSWORD, DB_HOST. The database must exist; the test suite installs WordPress into it.
set -euo pipefail

repo_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/../../.." && pwd)"
install_root="$repo_root/.wordpress-develop"
wp_develop_repo="https://github.com/WordPress/wordpress-develop.git"

wp_version="${WP_VERSION:-}"
if [ -z "$wp_version" ]; then
	wp_version="$(sed -n 's/^Tested up to:[[:space:]]*//p' "$repo_root/omnisend/readme.txt" | tr -d '[:space:]')"
fi

if [[ "$wp_version" =~ ^[0-9]+\.[0-9]+$ ]]; then
	latest_patch="$(git ls-remote --tags --refs "$wp_develop_repo" "refs/tags/${wp_version}*" \
		| sed 's#.*refs/tags/##' \
		| grep -E "^${wp_version//./\\.}(\.[0-9]+)?$" \
		| sort -V | tail -1 || true)"
	wp_version="${latest_patch:-$wp_version}"
fi

wp_dir="$install_root/$wp_version"
echo "WordPress $wp_version in $wp_dir"

if [ ! -f "$wp_dir/tests/phpunit/includes/bootstrap.php" ]; then
	rm -rf "$wp_dir"
	git -c advice.detachedHead=false clone --quiet --depth 1 --branch "$wp_version" --filter=blob:none --sparse "$wp_develop_repo" "$wp_dir"
	git -C "$wp_dir" sparse-checkout set src tests/phpunit/includes tests/phpunit/data
fi

sed \
	-e "s/youremptytestdbnamehere/${DB_NAME:-wordpress_tests}/" \
	-e "s/yourusernamehere/${DB_USER:-root}/" \
	-e "s/yourpasswordhere/${DB_PASSWORD:-root}/" \
	-e "s|'localhost'|'${DB_HOST:-127.0.0.1}'|" \
	"$wp_dir/wp-tests-config-sample.php" > "$wp_dir/wp-tests-config.php"

ln -sfn "$wp_version" "$install_root/current"
