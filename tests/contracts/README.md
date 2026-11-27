# Contract snapshots (gate G5)

Tools that record what third parties can rely on, so a branch can be compared with `develop`.

| File | What it records | Run |
|---|---|---|
| `static-hooks.php` | every `do_action` / `apply_filters` call (incl. `_ref_array`, `_deprecated`): hook, kind, argument count, `file:line` | `php tests/contracts/static-hooks.php <free-dir> <pro-dir> > hook-calls.tsv` |
| `endpoints.php` | REST routes (methods, permission, args), `wp_ajax_*` actions, admin menu/pages | see below |
| `runtime-recorder.php` | per admin screen: hooks fired with argument count, script/style handles (src, deps, enqueued), localized globals with their keys | mu-plugin, see below |
| `diff.php` | runtime losses develop → branch (handles, deps, enqueue, globals/keys, hooks/args); additions listed, never fail | `php tests/contracts/diff.php <develop-dir> <branch-dir> [--allow=allowed.tsv]` |

## Endpoints

```sh
wp eval-file tests/contracts/endpoints.php --user=admin                                                   # rest
wp --exec='define("WP_ADMIN",true);define("DOING_AJAX",true);' eval-file tests/contracts/endpoints.php --user=admin  # ajax
wp --exec='define("WP_ADMIN",true);' eval-file tests/contracts/endpoints.php --user=admin                 # pages
```

## Runtime recorder (test sites only)

```sh
cp tests/contracts/runtime-recorder.php <site>/wp-content/mu-plugins/wpuf-contract-recorder.php
wp option update wpuf_contract_record develop   # or: branch
# visit every WPUF admin screen (Playwright crawl), then:
wp option delete wpuf_contract_record
```

Output: `wp-content/uploads/wpuf-contracts/<label>/<screen>.json`.

Take snapshots with every Pro module active and their dependencies installed (BuddyPress, MailPoet, Paid Memberships Pro).

Note: a missing JS file is redirected (301) to WordPress's HTML 404 page with status 200. Crawls must check script responses for a non-JavaScript content type, not only for 4xx.
