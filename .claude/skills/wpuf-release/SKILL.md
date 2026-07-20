---
name: wpuf-release
description: Release WP User Frontend (free) to wp.org via the 10up GitHub Actions pipeline (NOT Appsero). Bump version + changelog, then push tag vX.Y.Z to weDevsOfficial/wp-user-frontend where `.github/workflows/deploy-org.yml` builds + deploys to wp.org SVN (10up) and publishes a GitHub Release. Trigger on "release wpuf", "ship wpuf X.Y.Z", "publish wpuf", "/wpuf-release". Free only — for Pro use wpuf-pro-release. Also covers changelog keyword phrasing for wp.org ranking (frontend post, user directory, membership, user profile, user registration).
---

# WP User Frontend (Free) Release — 10up pipeline

Orchestrator: `~/wpuf-release.sh`. Pushing tag `vX.Y.Z` triggers
`.github/workflows/deploy-org.yml` (Node 22 + npm + Grunt + Composer + PHP 7.4):
build → POT (grunt-wp-i18n) → composer `--no-dev` → disk-gate → **zip + GitHub
Release** → **wp.org SVN deploy** (10up, LAST). SVN secrets `WPORG_SVN_USERNAME` /
`WPORG_SVN_PASS` live on the upstream repo only.

## 🚨 10up is the sole publisher (Appsero auto-release is OFF)

The old flow pushed `master` and let **Appsero** auto-deploy to wp.org. That is
replaced: `deploy-org.yml` (10up) is now the only publisher. Key model facts:

- **Generated assets are UNTRACKED (gitignored)** — built in CI (`npm run build`
  + `npx grunt release`) and shipped by the 10up action from the **working tree**
  minus `.distignore` (same model as weDocs). `guard-build-untracked.yml` fails any
  PR/push that re-tracks them. Only the generated outputs are ignored — third-party
  libs and the hand-written vite/less sources stay tracked.
- **NEVER set `BUILD_DIR`** on the 10up action — it would sync only the git-tracked
  set and **strip the untracked, CI-built assets** (the package would ship with no
  built JS/CSS). Leave it unset.
- `.distignore` controls what ships (excludes `src`, `node_modules`, `.github`,
  `.claude`, `Gruntfile.js`, `vite.config.mjs`, `appsero.json`, tests, etc.).
- POT comes from `grunt-wp-i18n` (`npx grunt release` runs `i18n`) — **no WP-CLI
  install needed** (unlike weDocs).

## ⭐ Golden path

```bash
# 1. draft changelog to a file (one entry per line, no header, USER-FACING only):
#    Phrase entries with the wp.org tag keywords where truthful — see
#    "Keyword phrasing (wp.org ranking)" below. Do this NOW; changelog only
#    ships with a release.
cat > /tmp/wpuf-cl.md <<'EOF'
Fix – Tag suggestions not loading in the frontend post form builder.
New – Search and filtering in the user directory.
EOF

# 2. prepare in a tmp clone (bump + changelog + tag, NO push):
rm -rf /tmp/wpuf-rel
git clone --branch develop git@github.com:weDevsOfficial/wp-user-frontend.git /tmp/wpuf-rel
cd /tmp/wpuf-rel
git config user.email hoquea57@gmail.com; git config user.name arifulhoque7
~/wpuf-release.sh X.Y.Z --no-clone --skip-build --skip-push --wp-tested 7.0 --changelog-file /tmp/wpuf-cl.md -y
# --wp-tested is REQUIRED non-interactively (else it prompts and exits).

# 3. (optional) fork dry-run — confirms build/zip/Release, NOT the SVN deploy:
git remote add fork git@github.com:arifulhoque7/wp-user-frontend.git
git push fork develop --force && git push fork vX.Y.Z
gh run watch --repo arifulhoque7/wp-user-frontend   # WordPress Plugin Deploy WILL fail (no SVN secrets) — expected

# 4. go live:
git push origin develop && git push origin vX.Y.Z
gh run watch --repo weDevsOfficial/wp-user-frontend

# 5. POST-RELEASE verification (below). 6. sync fork: git push fork develop
```

`~/wpuf-release.sh` flags: `--no-clone`, `--skip-build`, `--skip-push`,
`--wp-tested X.Y`, `--changelog-file PATH`, `--repo URL`, `--branch`, `--remote`, `-y`.

## ✅ MANDATORY post-release verification (green workflow ≠ working package)

The fork dry-run CANNOT test SVN (no secrets). Always check the real package:

```bash
V=X.Y.Z
curl -sL "https://downloads.wordpress.org/plugin/wp-user-frontend.$V.zip" -o /tmp/w.zip
unzip -l /tmp/w.zip | grep -c 'assets/js/forms-list.min.js'   # want >=1
unzip -l /tmp/w.zip | grep -c '\.claude'                      # want 0
curl -s "https://plugins.svn.wordpress.org/wp-user-frontend/trunk/readme.txt" | grep 'Stable tag'   # == $V
curl -sI "https://plugins.svn.wordpress.org/wp-user-frontend/tags/$V/wpuf.php" | head -1            # 200
```

## Version sync (script bumps + verifies all 4 — must equal the tag, no `v`)
`wpuf.php` `Version:` header · `wpuf.php` `define( 'WPUF_VERSION', ... )` ·
`package.json` `version` · `readme.txt` `Stable tag`. Hard-aborts on mismatch
(wp.org rejects a `Stable tag` mismatch). Tag = `vX.Y.Z`, Stable tag = `X.Y.Z`.
`WPUF_SINCE` docblock placeholders are replaced with the version too.

## Changelog (WPUF free format)
`readme.txt` uses `== Changelog ==`. Block:
```text
= vX.Y.Z (D Month, YYYY) =
* Fix – ...
* New – ...
```
The workflow extracts the first block into the GitHub Release body. **User-facing
only — never put dev/chore/refactor commits in the changelog.**

### 🔍 Keyword phrasing (wp.org ranking)

The changelog ships with every release and feeds wp.org search relevance. Active
installs are trending down, so entries must carry the plugin's **5 wp.org tags**
verbatim wherever they honestly apply:

```
frontend post · user directory · membership · user profile · user registration
```

(Those 5 are the `Tags:` header in `readme.txt` — only the first 5 count. Keep the
changelog wording aligned with them.)

Rules:

- **Name the feature area in full, not the shorthand.** Prefer the tag phrase over
  the internal term — that's where the ranking value sits.
- Applies to **small fixes/chores/enhancements too** — a minor fix is still a
  chance to place a keyword, as long as it stays accurate.
- **Never fabricate.** The keyword must describe what the change truly touched. A
  checkbox-styling fix in the form builder is a *frontend post form* fix; it is
  NOT a membership fix. False entries burn user trust and wp.org can flag
  keyword-stuffed readmes.
- Don't cram all 5 into one line. One natural keyword per entry.
- Chore/dev commits still stay OUT entirely — rephrasing doesn't make them
  user-facing. Only reword entries that already earned a place.

Rewrite examples (same fix, better placement):

| Instead of | Write |
|---|---|
| `Fix – Tags suggestions not loading in form builder` | `Fix – Tag suggestions not loading in the frontend post form builder` |
| `Fix – Checkbox styling in form builder` | `Fix – Checkbox styling in the frontend post form builder` |
| `Fix – Restore edit button for CPT published posts` | `Fix – Restore edit button for custom post type frontend posts in the user dashboard` |
| `Enhance – Directory search` | `Enhance – Search and filtering in the user directory` |
| `Fix – Profile fields not saving` | `Fix – User profile fields not saving from the frontend profile form` |
| `Enhance – Pack assignment` | `Enhance – Membership subscription pack assignment` |
| `Fix – Registration email verification` | `Fix – Email verification during user registration` |

Description/FAQ copy is edited straight on wp.org via the
`wpuf-wporg-readme-svn` skill (no release needed). The **changelog** can only ship
with a release — so land the keywords here, at draft time (golden path step 1).

## Workflow facts (`.github/workflows/deploy-org.yml`)
- Trigger: any tag push. Order: build → `grunt release` (POT + legacy assets +
  tailwind + readme.md) → composer `--no-dev` → **disk-gate** (`forms-list.min.js`
  + `wp-user-frontend.pot`) → zip → GitHub Release → wp.org SVN deploy (LAST).
- 10up deploy: NO `BUILD_DIR`, NO `dry-run`. `SLUG: wp-user-frontend`.
- `main.yml` updates wp.org readme/assets on `master` push (needs `.wordpress-org/`
  for banners/icons — add it if/when assets exist).

## Required repo secrets (set on weDevsOfficial/wp-user-frontend)
`WPORG_SVN_USERNAME`, `WPORG_SVN_PASS` — the wp.org committer credentials. Without them
the deploy step fails (the GitHub Release still publishes).

## Gotchas
- A green workflow / clean fork zip does NOT prove wp.org got a working package —
  only the post-release curl block does.
- PolinRider hooks block `--amend`/force/rebase on `develop` → make NEW commits;
  re-tag via `git tag -d` + fresh `git tag -a`.
- wp.org rate-limits the indexer — avoid more than one release per ~24h.
- After release, sync `develop` (and `master` if used) on fork + upstream.

## Repo facts
- Repo `weDevsOfficial/wp-user-frontend` · branch `develop` · slug `wp-user-frontend`
  · fork `arifulhoque7/wp-user-frontend`
- Main file `wpuf.php` · tag `vX.Y.Z` · build Node 22 + npm + Grunt + Composer + PHP 7.4
- Generated assets are **gitignored** (built in CI); `.distignore` controls the package.
