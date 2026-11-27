#!/usr/bin/env bash
# Install the WordPress test library and a scratch database for PHPUnit (WPUF, task 2.1).
#
#   bin/install-wp-tests.sh <db-name> <db-user> <db-pass> [db-host] [wp-version]
#
# The database is DROPPED and recreated, so point it at a scratch name, never a
# development database. CI passes a throwaway name; locally, use one too.

set -euo pipefail

DB_NAME=${1-}
DB_USER=${2-}
DB_PASS=${3-}
DB_HOST=${4-localhost}
WP_VERSION=${5-latest}

if [[ -z "$DB_NAME" || -z "$DB_USER" ]]; then
	echo "usage: $0 <db-name> <db-user> <db-pass> [db-host] [wp-version]"
	exit 1
fi

WP_TESTS_DIR=${WP_TESTS_DIR-/tmp/wpuf-tests-lib}
WP_CORE_DIR=${WP_CORE_DIR-/tmp/wpuf-wordpress}

download() {
	if command -v curl >/dev/null; then
		curl -s "$1" > "$2"
	else
		wget -nv -O "$2" "$1"
	fi
}

if [[ "$WP_VERSION" == "latest" ]]; then
	download http://api.wordpress.org/core/version-check/1.7/ /tmp/wpuf-wp-latest.json
	WP_VERSION=$(grep -o '"version":"[^"]*' /tmp/wpuf-wp-latest.json | head -1 | sed 's/.*"version":"//')
fi

# Core, only for ABSPATH — the tests run against the test library, not this copy.
if [[ ! -d "$WP_CORE_DIR" ]]; then
	mkdir -p "$WP_CORE_DIR"
	download "https://wordpress.org/wordpress-${WP_VERSION}.tar.gz" /tmp/wpuf-wordpress.tar.gz
	tar --strip-components=1 -zxmf /tmp/wpuf-wordpress.tar.gz -C "$WP_CORE_DIR"
fi

download https://raw.githubusercontent.com/WordPress/WordPress/master/wp-content/db.php "$WP_CORE_DIR/wp-content/db.php" 2>/dev/null || true

# The test library, matched to the core version.
if [[ ! -d "$WP_TESTS_DIR" ]]; then
	mkdir -p "$WP_TESTS_DIR"
	svn co --quiet "https://develop.svn.wordpress.org/tags/${WP_VERSION}/tests/phpunit/includes/" "$WP_TESTS_DIR/includes"
	svn co --quiet "https://develop.svn.wordpress.org/tags/${WP_VERSION}/tests/phpunit/data/" "$WP_TESTS_DIR/data"
fi

if [[ ! -f "$WP_TESTS_DIR/wp-tests-config.php" ]]; then
	download https://develop.svn.wordpress.org/tags/"${WP_VERSION}"/wp-tests-config-sample.php "$WP_TESTS_DIR/wp-tests-config.php"

	# BSD and GNU sed disagree about -i, so write through a temp file.
	sed \
		-e "s|youremptytestdbnamehere|$DB_NAME|" \
		-e "s|yourusernamehere|$DB_USER|" \
		-e "s|yourpasswordhere|$DB_PASS|" \
		-e "s|localhost|$DB_HOST|" \
		-e "s|dirname( __FILE__ ) . '/src/'|'$WP_CORE_DIR/'|" \
		"$WP_TESTS_DIR/wp-tests-config.php" > "$WP_TESTS_DIR/wp-tests-config.php.tmp"
	mv "$WP_TESTS_DIR/wp-tests-config.php.tmp" "$WP_TESTS_DIR/wp-tests-config.php"
fi

mysqladmin drop "$DB_NAME" -f --user="$DB_USER" --password="$DB_PASS" --host="$DB_HOST" 2>/dev/null || true
mysqladmin create "$DB_NAME" --user="$DB_USER" --password="$DB_PASS" --host="$DB_HOST"

echo "Test library ready: $WP_TESTS_DIR"
