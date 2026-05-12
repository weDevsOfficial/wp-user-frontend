---
name: wpuf-release
description: Release a new version of WP User Frontend (free) to weDevsOfficial GitHub + wp.org via Appsero. One-command pipeline via `wpuf-release-free` alias. Trigger when user says "release wpuf", "ship wpuf X.Y.Z", "publish wpuf", "/wpuf-release", "wpuf-release-free".
---

# WP User Frontend (Free) Release Skill

Battle-tested one-command release. Verified working: v4.3.5 deployed to wp.org cleanly via `wpuf-release-free`.

## TL;DR

```bash
wpuf-release-free 4.3.6
# → prompts for changelog
# → does everything
# → ships to wp.org
```

That's it. **All steps automated.**

## When to Use

User wants to ship a WPUF (free) version. Match phrases:

- `release wpuf 4.3.6`
- `ship wpuf next version`
- `publish wpuf X.Y.Z`
- `wpuf-release-free`
- `/wpuf-release`

**Do NOT use for:** Pro plugin, code review, hotfixes that don't bump version.

## What `wpuf-release-free` does

Single command. Orchestrates entire pipeline:

1. **Verifies env** (git-flow-avh + GNU getopt + gh + composer + node + npm + grunt + curl)
2. **Verifies master push perm** via gh API
3. **Fresh-clones** `weDevsOfficial/wp-user-frontend` → `~/Sites/wpuf-release/wp-user-frontend`
4. **Copies patched scripts** from `~/.wpuf-release/` → `bin/`
5. **Runs `bin/release-zip.sh`** (interactive: WP version, changelog, confirm)
6. **Verifies zip excludes** (CLAUDE.md / .DS_Store / .claude must NOT be in zip)
7. **Runs `bin/release-git.sh`** (commits + git flow finish + push develop+master+tag)
8. **Creates GitHub release** with auto-extracted changelog from `changelog.txt`
9. **Waits 60s + verifies SVN** deploy via curl

Total time: ~5 min (interactive prompts) + ~60s SVN wait.

## File locations

| Path | Purpose |
|------|---------|
| `~/wpuf-release-free.sh` | Orchestrator (one-command entry point) |
| `~/.wpuf-release/release-zip.sh` | Sapayth's release-zip.sh (patched) |
| `~/.wpuf-release/release-git.sh` | Sapayth's release-git.sh (patched) |
| `~/Sites/wpuf-release/wp-user-frontend/` | Fresh clone workspace (created/destroyed each run) |
| `~/.zshrc` line | `alias wpuf-release-free='~/wpuf-release-free.sh'` |

## Critical pre-reqs (one-time per machine)

### Required tools

```bash
# All must succeed:
which git git-flow getopt composer node npm grunt curl gh
git flow version | head -1     # Must show "AVH Edition"
getopt --version | head -1     # Must show "util-linux"
gh auth status                 # Must show logged in
```

### If `git flow` is wrong (brew nvie 0.4.1 has tag bug)

```bash
brew uninstall git-flow 2>/dev/null
cd /tmp && rm -rf gitflow-avh
git clone --recursive https://github.com/petervanderdoes/gitflow-avh.git
cd gitflow-avh
make install prefix=~/.local
# ~/.local/bin/git-flow now exists. Add to PATH if not already.
```

### If `getopt` is BSD (fails on `-m` with spaces)

```bash
brew install gnu-getopt
echo 'export PATH="/opt/homebrew/opt/gnu-getopt/bin:$PATH"' >> ~/.zshrc
```

Open new terminal to pick up.

### Master push perm

```bash
gh api repos/weDevsOfficial/wp-user-frontend/collaborators/$(gh api user --jq .login)/permission --jq .role_name
```

If `write` only: master push may be blocked. Get bypass from `tareq1988` or `nizamuddin` BEFORE running `wpuf-release-free`. (Once granted, perm is permanent — only confirm once per machine setup.)

## Example prompts → expected outputs

### Example 1: standard chore release

**User says:** `release wpuf 4.3.6 with this change: Tested up to 6.9.5`

**Agent does:**

1. Verifies pre-reqs (env tools)
2. Asks: "Are config-file changes (e.g. .svnignore tweaks) needed?"
3. If user says no: runs `wpuf-release-free 4.3.6`
4. Tells user to enter changelog when script prompts:
   ```
   * Chore – Bump Tested up to WordPress 6.9.5
   ```
5. Hands off URL after success

**Expected output (~5 min later):**

```
✔ Version:        4.3.6
✔ Tag:            v4.3.6
✔ GitHub release: https://github.com/weDevsOfficial/wp-user-frontend/releases/tag/v4.3.6
✔ Zip asset:      build/wp-user-frontend-v4.3.6.zip
✔ SVN deployed
```

### Example 2: code change ships with release

**User says:** `release wpuf 4.4.0. New: AI form templates feature in PR #1870`

**Agent does:**

1. Verifies PR #1870 is merged to upstream develop:
   ```bash
   gh pr view 1870 --repo weDevsOfficial/wp-user-frontend --json state,baseRefName
   # Expect: state=MERGED, baseRefName=develop
   ```
2. If merged: runs `wpuf-release-free 4.4.0`
3. Tells user to enter changelog:
   ```
   * New – AI form templates feature
   ```
4. If NOT merged: aborts, tells user to merge PR first.

### Example 3: minor change needs PR first

**User says:** `release wpuf 4.3.7 with .svnignore tweak`

**Agent does:**

1. Detects: .svnignore change is config, NOT in upstream develop yet
2. Tells user: "Sapayth's flow needs config changes merged via PR first. Open PR?"
3. User confirms → agent creates branch, commits, pushes to fork, opens PR, merges
4. Then runs `wpuf-release-free 4.3.7`

### Example 4: dry-run version validation

**User says:** `is wpuf 4.3.6 ready to release?`

**Agent does:**

```bash
# Check upstream develop tip
gh api repos/weDevsOfficial/wp-user-frontend/branches/develop --jq '.commit.commit.message + " (" + .commit.sha[0:8] + ")"'

# Check what's NOT yet released since last tag
gh api repos/weDevsOfficial/wp-user-frontend/tags --jq '.[0].name'
gh pr list --repo weDevsOfficial/wp-user-frontend --state merged --base develop --search "merged:>=2026-05-11" --limit 10 --json number,title

# Check master push perm
gh api repos/weDevsOfficial/wp-user-frontend/collaborators/$(gh api user --jq .login)/permission --jq .role_name
```

Returns summary. Does NOT run release.

### Example 5: post-release recovery

**User says:** `release shipped but wp.org still shows old version`

**Agent does:**

```bash
# 1. Check SVN actual state (source of truth)
curl -sI "https://plugins.svn.wordpress.org/wp-user-frontend/tags/X.Y.Z/" | head -1
curl -s "https://plugins.svn.wordpress.org/wp-user-frontend/trunk/readme.txt" | grep "Stable tag"

# 2. Check wp.org API (cached)
curl -s "https://api.wordpress.org/plugins/info/1.0/wp-user-frontend.json" | python3 -c "import sys,json; d=json.load(sys.stdin); print(d.get('version'),d.get('last_updated'))"

# 3. Check Appsero deploy log via SVN log
svn log -l 5 "https://plugins.svn.wordpress.org/wp-user-frontend/trunk/readme.txt"
```

If SVN is correct but API stale: wait 15-60 min for cache. If >2 hours: email `plugins@wordpress.org` with slug + verification proof.

## Step-by-step (manual fallback if alias broken)

### If `wpuf-release-free` not available

```bash
# Run orchestrator directly
~/wpuf-release-free.sh 4.3.6
```

### If orchestrator broken, raw scripts

```bash
export PATH="/opt/homebrew/opt/gnu-getopt/bin:$HOME/.local/bin:$PATH"
mkdir -p ~/Sites/wpuf-release && cd ~/Sites/wpuf-release
rm -rf wp-user-frontend
git clone git@github.com:weDevsOfficial/wp-user-frontend.git
cd wp-user-frontend
mkdir bin
cp ~/.wpuf-release/release-*.sh bin/
chmod +x bin/*.sh
bash bin/release-zip.sh   # answer prompts
bash bin/release-git.sh   # confirm with y
gh release create v4.3.6 --repo weDevsOfficial/wp-user-frontend \
  --title "v4.3.6" --notes "..." \
  ./build/wp-user-frontend-v4.3.6.zip
```

## Critical Appsero gotcha (for awareness)

Appsero deploys from **master branch** on this repo. Master MUST be at new version BEFORE GitHub release publishes. Else: "tag X already exists" error → wp.org pipeline frozen.

`wpuf-release-free` handles this correctly:
- Pushes master BEFORE creating GitHub release
- Verifies master state via gh API
- Only proceeds to release publish if push succeeded

If master push blocked: orchestrator warns + offers fallback path.

## ⚠️ DO NOT release more than once per 24 hours

wp.org indexer rate-limits rapid releases. v4.3.3+v4.3.4+v4.3.5 in 4 hours triggered freeze that took manual reviewer intervention to clear.

If shipping multiple changes: bundle into ONE release. Only re-release within 24h for **emergency security fix**.

## Troubleshooting

### `flags:FATAL the available getopt does not support spaces`
- BSD getopt active. Run env setup again.

### Tag message has literal quotes
- brew git-flow installed. Uninstall + install AVH from source.

### `Stable tag` mismatch error from Appsero
- master not at new version. Verify with `gh api .../contents/wpuf.php?ref=master`.

### Zip contains CLAUDE.md or .DS_Store
- `Gruntfile.js` `copy.main.src` excludes wrong. Add `'!**/CLAUDE.md', '!.claude/**', '!**/.DS_Store'`. PR fix to develop first, then re-release.

### Orchestrator aborts "Working tree is not clean"
- Code/config changes uncommitted in fresh clone. Should be impossible. If happens: delete `~/Sites/wpuf-release/wp-user-frontend` and re-run.

### release-zip.sh aborts at WPUF_SINCE
- Unpatched script. Check line 180 has `|| true`. Re-copy from `~/.wpuf-release/`.

### release-zip.sh aborts at `grunt release`
- Patched version auto-runs `npm install`. If still fails: run `npm install` manually in `~/Sites/wpuf-release/wp-user-frontend`, then re-run from same `release/X.Y.Z` branch.

### `git push origin master` rejected (GH006)
- No bypass perm. Get from tareq1988/nizamuddin. OR open PR fork:master → upstream:master, admin merges (manual fallback).

### wp.org API stuck > 2 hours after publish
- Pipeline rate-limit OR review hold. Email plugins@wordpress.org with: slug, SVN verification, request reindex.

## Repo facts (cached)

- Repo: `weDevsOfficial/wp-user-frontend`
- Default branch: `develop`
- Protected: `master`
- Admins: `tareq1988`, `nizamuddin`
- Maintainers: `shams-sadek1981`, `anik-fahmid`, `jamil-mahmud`, `sharifcraft`
- Tag format: `vX.Y.Z`
- Tag message: `release version X.Y.Z vX.Y.Z` (AVH auto-appends tagname)
- Build output: `./build/wp-user-frontend-vX.Y.Z.zip`
- Excludes config: `appsero.json` (wp.org), `.svnignore` (SVN manual), `Gruntfile.js` `copy.main.src` (grunt zip)
- Appsero source: **master** branch
- Auto-deploy: Appsero on tag + GitHub release publish
- Wp.org plugin URL: https://wordpress.org/plugins/wp-user-frontend/
- SVN URL: https://plugins.svn.wordpress.org/wp-user-frontend/

## Patches in `~/.wpuf-release/release-zip.sh` vs sapayth's original

1. **WPUF_SINCE pipefail fix** (line ~180):
   - Original: `SINCE_COUNT=$(grep ... | wc -l ...)`
   - Patched: appended `|| true` + `${SINCE_COUNT:-0}` default
2. **Auto npm install** if `node_modules` missing
3. **Auto composer install** if `vendor/` missing
4. **Auto gitflow init** if not initialized + sets versiontag prefix to `v`
5. **AVH + GNU getopt detection** with install instructions
6. **Cleanup-on-error trap** with recovery commands
7. **Better next-steps output** with exact commands

## Patches in `~/.wpuf-release/release-git.sh` vs sapayth's original

1. **AVH + GNU getopt detection** with warnings
2. **Cleanup-on-error trap** with recovery commands
3. **Better next-steps output** including SVN verification commands

## Skill execution flow (for the agent)

1. **Detect intent** ("release wpuf X.Y.Z" or similar)
2. **Ask if any code/config changes need PR first**
   - If yes: handle PR flow first (separate from release)
   - If no: proceed
3. **Verify env one-liner:**
   ```bash
   git flow version | head -1   # Must say AVH
   getopt --version | head -1   # Must say util-linux
   ```
4. **Run:** `wpuf-release-free X.Y.Z`
5. **Walk user through interactive prompts** in `release-zip.sh`:
   - "When prompted for WP version: accept default or override"
   - "When prompted for changelog: type each `* Type – Description` line, empty line to finish"
6. **Hand off final URLs** after success

## DO NOT

- DO NOT run release in user's dev folder — multiple remotes, will push to fork
- DO NOT bundle config/code changes in release commit — script aborts on dirty tree
- DO NOT release more than once per 24h (wp.org rate-limits)
- DO NOT skip env verification — silent failures otherwise
- DO NOT publish GitHub release if master not at new version (Appsero will fail)
- DO NOT force-push master
- DO NOT delete published tags
- DO NOT chase zero "develop ahead of master" banner — normal gitflow
- DO NOT push develop → master after release to "fix" banner — triggers Appsero retry, breaks wp.org
- DO NOT manually svn commit unless emergency — adds churn

## Verified releases via this flow

| Version | Date | Status |
|---------|------|--------|
| 4.3.5 | 2026-05-11 | ✅ Full sapayth-script flow worked, deployed to wp.org cleanly after rate-limit cleared |
