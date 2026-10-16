---
name: wpuf-release
description: Release WP User Frontend free to wp.org via the 10up GitHub Actions pipeline (NOT Appsero), plus the Pro companion tag. Bump version + changelog + WPUF_SINCE stamps, then push tag vX.Y.Z to weDevsOfficial/wp-user-frontend where `.github/workflows/deploy-org.yml` builds with pnpm, zips with `bin/zip.mjs`, publishes a GitHub Release and deploys to wp.org SVN (10up). Pro: tag on weDevsOfficial/wpuf-pro, `release.yml` builds the 7 plan zips and checks them with `tools/verify-zips.mjs`. Trigger on "release wpuf", "ship wpuf X.Y.Z", "publish wpuf", "/wpuf-release". Also covers changelog keyword phrasing for wp.org ranking (frontend post, user directory, membership, user profile, user registration).
---

# WP User Frontend Release (free via 10up, Pro via GitHub Release)

Free: pushing tag `vX.Y.Z` to `weDevsOfficial/wp-user-frontend` triggers
`.github/workflows/deploy-org.yml` (pnpm via `pnpm/action-setup`, Node from `.nvmrc` (22),
PHP 7.4 + WP-CLI, Composer): `pnpm install --frozen-lockfile && pnpm build` (once, ends with
`bin/verify-build.mjs`) → `pnpm run i18n` (POT, `bin/make-pot.mjs`) → `composer install
--no-dev --optimize-autoloader` → `node bin/verify-build.mjs --release` → `node bin/zip.mjs
--out "$RUNNER_TEMP/release" --skip-verify --keep-vendor` → changelog extract → **GitHub
Release** with the zip → **wp.org SVN deploy** (10up, LAST). SVN secrets `WPORG_SVN_USERNAME` /
`WPORG_SVN_PASS` live on the upstream repo only. Grunt has no release task any more: locally
the one release command is `pnpm release`.

The 5.0.0 release (new React admin, PRs #1993 + pro#1732) ships as **5.0.0 for both free and
Pro**; `VersionGuard::REQUIRED_PRO_VERSION` (free) and Pro's `Platform_Guard::REQUIRED_FREE_VERSION`
already say 5.0.0.

## 🚨 10up is the sole publisher (Appsero auto-release is OFF)

- **Generated assets are UNTRACKED (gitignored, `/assets/*` plus the listed outputs)**: built
  ONCE in CI and shipped by the 10up action from the **working tree** minus `.distignore`.
  `guard-build-untracked.yml` fails any PR/push that re-tracks them. Third-party libs and
  hand-written legacy assets stay tracked.
- **NEVER set `BUILD_DIR`** on the 10up action: it would sync only the git-tracked set and
  **strip the untracked, CI-built assets**. The action runs with `dry-run: false`, `SLUG: wp-user-frontend`.
- `.distignore` controls what ships (root-anchored `/src`, `/bin`, `/tests`, `/tools`, `/docs`,
  `/.claude`, `/openspec`, `/qa-stories`, `/dist`, build configs, lockfiles, `*.map`,
  `CLAUDE.md` anywhere, the user-directory npm project files).
- `bin/zip.mjs` (`pnpm zip`): checks WPUF_VERSION = header `Version` = readme `Stable tag`
  (= the git tag in CI), runs `verify-build --release` unless `--skip-verify`, copies through
  `.distignore` (same list as the deploy), installs production Composer packages in the copy
  (unless `--keep-vendor`), writes `wp-user-frontend-v<version>.zip` (default `dist/`), then
  checks the unpacked zip: one folder, every generated asset (`bin/build-outputs.mjs`), no
  sources / dev files / maps / dot-files / dev Composer packages, every PHP file lints.
- `pnpm release` = `pnpm install --frozen-lockfile && pnpm clean && pnpm build && pnpm run i18n && pnpm zip`
  (the CI steps, for a local package check; needs WP-CLI for the POT).

## ⭐ Golden path

Never tag, release or dry-run in the fork (`arifulhoque7/*`): `deploy-org.yml` and Pro's
`release.yml` fire on any `v*` tag. Tags go to the org repos only.

```bash
# 1. draft changelog to a file (one entry per line, no header, USER-FACING only).
#    Phrase entries with the wp.org tag keywords where truthful (see below).
cat > /tmp/wpuf-cl.md <<'EOF'
Fix – Tag suggestions not loading in the frontend post form builder.
New – Search and filtering in the user directory.
EOF

# 2. prepare in a tmp clone (bump + changelog + tag, NO push, NO build):
rm -rf /tmp/wpuf-rel
git clone --branch develop git@github.com:weDevsOfficial/wp-user-frontend.git /tmp/wpuf-rel
cd /tmp/wpuf-rel
git config user.email hoquea57@gmail.com; git config user.name arifulhoque7
~/wpuf-release.sh X.Y.Z --no-clone --skip-build --skip-push --wp-tested 7.1 --changelog-file /tmp/wpuf-cl.md -y
# --wp-tested is REQUIRED non-interactively (else it prompts and exits).
# ALWAYS --skip-build: the script's build path still runs npm ci + `grunt release`, which no longer exist.

# 3. finish what the script does not cover, as a NEW commit, then move the unpushed tag:
pnpm install --frozen-lockfile
pnpm version:stamp            # bin/version-replace.mjs: WPUF_SINCE in wpuf.php, wpuf-functions.php,
                              # includes, Lib, templates, src, modules/user-directory (the script only stamps includes/)
pnpm version:check            # must list nothing
#   readme.txt: the script writes "= vX.Y.Z (...) =", shipped entries use "= X.Y.Z (...) =": fix it.
#   changelog.txt: add "= vX.Y.Z (D Month, YYYY) =" + the same bullets.
#   readme.md: mirror the entry ("### X.Y.Z (D Month, YYYY) ###"), or `npx grunt readme`.
git add -u && git commit -m "chore: prepare X.Y.Z: @since stamps, changelog"
git tag -d vX.Y.Z && git tag -a vX.Y.Z -m "release version X.Y.Z"

# 4. local package check (optional, recommended for a big release):
pnpm release                  # zip in dist/; then see "Release build check" in docs/build-and-test.md

# 5. go live, in this order:
git push origin develop && git push origin vX.Y.Z     # tag BEFORE master
gh run watch --repo weDevsOfficial/wp-user-frontend
#    then Pro (section below; its release.yml builds against free develop)
#    then, after the free deploy is green: merge develop into master, push master only when
#    the merge tree equals the tag tree (main.yml syncs readme.txt to SVN trunk on every master push)

# 6. POST-RELEASE verification (below), then sync the fork: git push fork develop
```

When the release ships through an integration PR instead (the 4.3.12 / 4.3.13 pattern):
merge it (`gh pr merge --merge --subject "Release X.Y.Z: ... (#N)"`), tag the merge commit
with an annotated `vX.Y.Z`, push the tag, then Pro, then master as above.

`~/wpuf-release.sh` flags: `--no-clone`, `--skip-build`, `--skip-push`,
`--wp-tested X.Y`, `--changelog-file PATH`, `--repo URL`, `--branch`, `--remote`, `-y`.

## ✅ MANDATORY post-release verification (green workflow ≠ working package)

```bash
V=X.Y.Z; P=X.Y.W   # P = previous version
curl -sL "https://downloads.wordpress.org/plugin/wp-user-frontend.$V.zip" -o /tmp/w.zip
unzip -l /tmp/w.zip | grep -c 'assets/js/screens/form-builder.js'   # want >=1 (React admin)
unzip -l /tmp/w.zip | grep -c 'assets/js/frontend/forms.js'         # want >=1 (React frontend)
unzip -l /tmp/w.zip | grep -c '\.claude\|CLAUDE.md\|/src/'          # want 0
curl -s "https://plugins.svn.wordpress.org/wp-user-frontend/trunk/readme.txt" | grep 'Stable tag'   # == $V
curl -sI "https://plugins.svn.wordpress.org/wp-user-frontend/tags/$V/wpuf.php" | head -1            # 200
# no-break proof: unpack $V and $P zips and `diff -rq`; account for every changed CSS/JS file
```

Then install the wp.org zip (and one Pro plan zip) on a fresh site with the AI mock and run
`releaseSmokeTest` + `aiFormBuilderTest` (see `docs/build-and-test.md`, "Release build check").

## Version sync (must equal the tag, no `v`)
`wpuf.php` `Version:` header · `wpuf.php` `define( 'WPUF_VERSION', ... )` ·
`package.json` `version` · `readme.txt` `Stable tag`. The script bumps and verifies all four;
`bin/zip.mjs` re-checks header / WPUF_VERSION / Stable tag (+ tag in CI) and fails the
workflow on a mismatch. Tag = `vX.Y.Z`, Stable tag = `X.Y.Z`. Every `WPUF_SINCE` placeholder
is stamped with the version (`pnpm version:stamp`, check with `pnpm version:check`).
Stamping pulls legacy files into the PHPCS scope (CI lints whole changed files): budget for
`phpcbf` + a few manual fixes.

## Changelog (WPUF free format)
`readme.txt` under `== Changelog ==` (wp.org), `changelog.txt` and `readme.md` mirror it:
```text
= X.Y.Z (D Month, YYYY) =        (readme.txt)
= vX.Y.Z (D Month, YYYY) =       (changelog.txt)
* Security – ...
* Fix – ...
* New – ...
```
The workflow takes the first block of `readme.txt` (`/^= v?[0-9]/`) as the GitHub Release
body. **User-facing only: never put dev/chore/refactor commits in the changelog.**

### 🔍 Keyword phrasing (wp.org ranking)

The changelog ships with every release and feeds wp.org search relevance. Active
installs are trending down, so entries must carry the plugin's **5 wp.org tags**
verbatim wherever they honestly apply:

```
frontend post · user directory · membership · user profile · user registration
```

(Those 5 are the `Tags:` header in `readme.txt`; only the first 5 count. Keep the
changelog wording aligned with them.)

Rules:

- **Name the feature area in full, not the shorthand.** Prefer the tag phrase over
  the internal term; that's where the ranking value sits.
- Applies to **small fixes/chores/enhancements too**: a minor fix is still a
  chance to place a keyword, as long as it stays accurate.
- **Never fabricate.** The keyword must describe what the change truly touched. A
  checkbox-styling fix in the form builder is a *frontend post form* fix; it is
  NOT a membership fix. False entries burn user trust and wp.org can flag
  keyword-stuffed readmes.
- Don't cram all 5 into one line. One natural keyword per entry.
- Chore/dev commits still stay OUT entirely; rephrasing doesn't make them
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

Description/FAQ copy is edited straight on wp.org via the `wporg-readme-svn-push` skill (no
release needed). The **changelog** can only ship with a release, so land the keywords here,
at draft time (golden path step 1).

## Pro companion release (`weDevsOfficial/wpuf-pro`, no wp.org)

- Bump `wpuf-pro.php` `Version:` header, `define( 'WPUF_PRO_VERSION', ... )`, `package.json`
  `version`; add `= vX.Y.Z (D Month, YYYY) =` to `changelog.txt`; stamp placeholders with
  `pnpm version:stamp` (`tools/version-replace.mjs`), check with `pnpm version:check`.
- Merge free first: Pro's `release.yml` checks out free **`develop`** as a sibling (Pro's
  webpack resolves packages from free's `node_modules`).
- Tag `vX.Y.Z` on the org repo → `.github/workflows/release.yml` (Node 22, PHP 7.4 + WP-CLI,
  pnpm 12.8.1): free `pnpm install` → Pro `pnpm install` → `composer install --no-dev` (Pro and
  `modules/user-directory`) → `pnpm run build` → user-directory `npm install && npm run build`
  → `pnpm run makepot` → `pnpm run zip:all` (`grunt zip-all`, one zip per `pack.json` plan with
  its Appsero key) → `node tools/verify-zips.mjs` → GitHub Release with
  `dist/wp-user-frontend-pro-*-v*.zip`.
- 7 plans: personal, professional, business, lifetime-personal, lifetime-professional,
  lifetime-business, appsumo. `verify-zips.mjs` checks versions (package.json = header =
  WPUF_PRO_VERSION = tag), one folder per zip, the plan in the header and `$plan`, its Appsero
  key, exactly its modules, the user-directory build where the plan has the module, built
  assets, POT, autoloaders, no sources / lockfiles / maps / dev packages, PHP lint.
- Local: `pnpm release` (build, user-directory, makepot, zip:all, verify:zips) or one plan with
  `pnpm run zip:plan -- professional`.
- After the Release: the zips are uploaded to Appsero by hand (the owner does this); Pro is
  done only after that upload.

## Workflow facts
- `deploy-org.yml`: trigger any `v*` tag; steps as in the first section; Release notes and zip
  are written to `$RUNNER_TEMP` so nothing extra reaches SVN.
- `main.yml`: on every `master` push, syncs `readme.txt` (+ `.wordpress-org/` assets, none in the
  repo today) to SVN trunk via `10up/action-wordpress-plugin-asset-update`. That is why the tag
  goes first: a master push before the tag deploy points wp.org at a missing `tags/X.Y.Z`.
- `e2e-wpuf.yml` skips on the org repos (runs on forks); `phpcs.yml` lints whole changed PHP files on PRs.

## Required repo secrets (set on weDevsOfficial/wp-user-frontend)
`WPORG_SVN_USERNAME`, `WPORG_SVN_PASS`: the wp.org committer credentials. Without them
the deploy step fails (the GitHub Release still publishes).

## Gotchas
- A green workflow does NOT prove wp.org got a working package; only the post-release block does.
- PolinRider hooks block `--amend`/force/rebase on `develop` → make NEW commits;
  re-tag an UNPUSHED tag via `git tag -d` + fresh `git tag -a`.
- `git push -q` through the local pre-push hook can report a failure while the push
  succeeded; rerun without `-q` before assuming a rejection.
- wp.org rate-limits the indexer: avoid more than one release per ~24h.
- After release, sync `develop` (and `master`) on fork + upstream; delete any release tag
  that reached the fork by mistake (`git push fork :refs/tags/vX.Y.Z`).

## Repo facts
- Repo `weDevsOfficial/wp-user-frontend` · branch `develop` · slug `wp-user-frontend`
  · fork `arifulhoque7/wp-user-frontend`
- Main file `wpuf.php` · tag `vX.Y.Z` · build pnpm (`packageManager` pnpm 12.8.1, Node 22.22.2+) + Composer + PHP 7.4; Grunt only for the POT wrapper and the Tailwind 3 classic sheets
- Generated assets are **gitignored** (built in CI); `.distignore` controls the package.
