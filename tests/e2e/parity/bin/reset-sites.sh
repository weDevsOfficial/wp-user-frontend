#!/usr/bin/env bash
# Reset both parity sites to ONE identical fresh database (test sites only).
#
# Fresh WordPress on the develop site, same plugins, Pro license, every Pro
# module active; then the same dump is loaded into the branch site with the URL
# replaced, which is also what a real develop -> branch upgrade looks like.
#
# Needs in tests/e2e/.env: PARITY_DEVELOP_URL/_PATH, PARITY_BRANCH_URL/_PATH.
# Optional: PARITY_LICENSE_JSON = file holding the wpuf_license option as JSON.
set -euo pipefail
cd "$(dirname "$0")/../.."
set -a; source .env; set +a

DEV="$PARITY_DEVELOP_PATH"; BR="$PARITY_BRANCH_PATH"
DEV_HOST="${PARITY_DEVELOP_URL#http://}"; BR_HOST="${PARITY_BRANCH_URL#http://}"
DUMP="$(mktemp -t wpuf-parity-seed).sql"
PLUGINS="buddypress mailpoet paid-memberships-pro wp-user-frontend wpuf-pro"

wp --path="$DEV" db reset --yes --quiet
wp --path="$DEV" core install --url="$PARITY_DEVELOP_URL" --title="WPUF Parity" \
    --admin_user="${QA_ADMIN_USERNAME:-admin}" --admin_password="${QA_ADMIN_PASSWORD:-admin}" \
    --admin_email=admin@example.test --skip-email --quiet
wp --path="$DEV" rewrite structure '/%postname%/' --quiet
wp --path="$DEV" plugin activate $PLUGINS --quiet
if [ -n "${PARITY_LICENSE_JSON:-}" ] && [ -s "$PARITY_LICENSE_JSON" ]; then
    wp --path="$DEV" option update wpuf_license --format=json < "$PARITY_LICENSE_JSON" > /dev/null
fi
wp --path="$DEV" eval 'foreach ( array_keys( wpuf_pro_get_modules() ) as $m ) { wpuf_pro_activate_module( $m ); }'

# --set-gtid-purged=OFF: a dump with GTID_PURGED cannot be loaded into another DB on the same server.
wp --path="$DEV" db export "$DUMP" --quiet --set-gtid-purged=OFF
wp --path="$BR" db reset --yes --quiet
# `wp db import` uses SOURCE, which this MySQL client rejects; pipe instead.
wp --path="$BR" db query < "$DUMP"
wp --path="$BR" search-replace "$PARITY_DEVELOP_URL" "$PARITY_BRANCH_URL" --all-tables --skip-columns=guid --quiet
wp --path="$BR" search-replace "$DEV_HOST" "$BR_HOST" --all-tables --skip-columns=guid --quiet
wp --path="$BR" cache flush --quiet

for site in "$DEV" "$BR"; do
    wp --path="$site" eval 'echo home_url(), " plan=", wpuf_pro_current_plan(), " modules=", count( wpuf_pro_get_active_modules() ), "\n";'
done
