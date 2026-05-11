---
name: wpuf-release
description: Release a new version of WP User Frontend plugin to weDevsOfficial GitHub + wp.org via Appsero. Handles version bumps, asset builds, gitflow merges, tag, GitHub release, and wp.org auto-deploy. Trigger when user says "release wpuf", "ship version X", "publish wpuf X.Y.Z", "create wpuf release", or invokes /wpuf-release.
---

# WP User Frontend Release Skill

Battle-tested release playbook. Encodes lessons from v4.3.3 disaster (Appsero deploy broke because master was stale). Follow EVERY step in order. Skipping = broken release.

## When to Use

User wants to ship a new WPUF version to wp.org. Examples:
- "release wpuf 4.3.4"
- "publish next wpuf version"
- "ship 4.4.0 with these notes: ..."

**Do NOT use for:** code review, dev work, hotfixes that don't bump version.

## Required inputs (ASK USER if missing)

1. **New version** — semver `X.Y.Z` (e.g. `4.3.4`)
2. **Release notes** — list of changes (Enhance/Fix/New entries)
3. **Release date** — defaults to today, formatted `D Month, YYYY` (e.g. `30 April, 2026`)
4. **Tested up to WP version** — defaults to fetching from `https://api.wordpress.org/core/version-check/1.7/`
5. **Confirmation user has master push perm OR Appsero source = develop** — see "Critical Permissions Check" below

## Critical permissions check (DO FIRST)

Before any git work, verify the user can ship cleanly. **This was the v4.3.3 blocker.**

### Check 1: Master push permission

```bash
gh api repos/weDevsOfficial/wp-user-frontend/collaborators/$(gh api user --jq .login)/permission --jq '.role_name'
```

Result handling:
- `admin` or `maintain` → can push master ✅
- `write` → likely BLOCKED by branch protection. Verify with dry-run later.
- Anything else → STOP, ask user to get access

### Check 2: Appsero source branch (cannot detect via CLI — ask user)

Ask: "Is Appsero configured to deploy from `master` or `develop` branch? Login to https://app.appsero.com → WP User Frontend → Settings to verify."

- If `master` → master MUST be at new version BEFORE publishing GitHub release
- If `develop` → master sync is cosmetic; can publish without master push

If user doesn't know: assume `master` (safer assumption — historically true for this repo).

### Decision matrix

| User can push master | Appsero source | Action |
|----------------------|----------------|--------|
| Yes | master | Standard flow — push master before release |
| Yes | develop | Standard flow — master push optional |
| No | master | **STOP** — get admin merge OR change Appsero source first |
| No | develop | Skip master push, proceed with develop+tag+release |

## Step 1 — Workspace setup

Always use a fresh clone. Dev folder has multiple remotes (origin=fork). Release scripts pushing to `origin` would push to fork by accident.

```bash
RELEASE_DIR="$HOME/Sites/wpuf-release"
mkdir -p "$RELEASE_DIR"
cd "$RELEASE_DIR"
rm -rf wp-user-frontend
git clone git@github.com:weDevsOfficial/wp-user-frontend.git
cd wp-user-frontend
git remote -v
# MUST show only: origin git@github.com:weDevsOfficial/wp-user-frontend.git
```

## Step 2 — Sync develop + master

```bash
git checkout develop && git pull origin develop
git checkout master && git pull origin master
git checkout develop
git log --oneline -10
```

Verify all PRs being released are in the log. If not, abort.

## Step 3 — Init git-flow

### 3a. Install if missing

```bash
which git-flow || brew install git-flow
```

> **Known limitation:** brew `git-flow` v0.4.1 has a getopt bug — `-m "string with spaces"` fails. Workaround in Step 11.
>
> Better alternative if user wants: `brew install git-flow-avh` (handles spaces correctly). Both work.

### 3b. Init with `v` tag prefix (CRITICAL)

```bash
git flow init -d
git config gitflow.prefix.versiontag "v"
git config --get gitflow.prefix.versiontag    # Must echo: v
```

WPUF tags are `v4.3.3` not `4.3.3`. Without this, Appsero won't trigger.

## Step 4 — Start release branch

```bash
git flow release start <NEW_VERSION>
# e.g. git flow release start 4.3.4
```

Now on branch `release/<NEW_VERSION>`.

## Step 5 — Bump versions in 4 files

> **macOS BSD sed quirk:** `0,/PATTERN/s//REPLACE/` range syntax is unreliable for JSON. Use line-number sed for package*.json.

### 5a. wpuf.php (2 lines)

```bash
OLD="<CURRENT_VERSION>"  # e.g. 4.3.3
NEW="<NEW_VERSION>"      # e.g. 4.3.4

sed -i '' "s/^Version: ${OLD}$/Version: ${NEW}/" wpuf.php
sed -i '' "s/define( 'WPUF_VERSION', '${OLD}' );/define( 'WPUF_VERSION', '${NEW}' );/" wpuf.php
```

### 5b. readme.txt (Stable tag + Tested up to)

```bash
sed -i '' "s/^Stable tag: ${OLD}$/Stable tag: ${NEW}/" readme.txt

# Tested up to (if user wants to bump)
TESTED_UP_TO=$(curl -s https://api.wordpress.org/core/version-check/1.7/ | node -e "const d=require('fs').readFileSync('/dev/stdin','utf8');try{const j=JSON.parse(d);console.log(j.offers[0].version)}catch(e){}" 2>/dev/null)
sed -i '' "s/^Tested up to: .*/Tested up to: ${TESTED_UP_TO}/" readme.txt
```

### 5c. package.json

```bash
# Use node for precision (BSD sed unreliable on JSON)
node -e "
const fs = require('fs');
const pkg = JSON.parse(fs.readFileSync('package.json', 'utf8'));
pkg.version = '${NEW}';
fs.writeFileSync('package.json', JSON.stringify(pkg, null, 2) + '\n');
"
```

### 5d. package-lock.json

```bash
node -e "
const fs = require('fs');
const lock = JSON.parse(fs.readFileSync('package-lock.json', 'utf8'));
lock.version = '${NEW}';
if (lock.packages && lock.packages['']) {
    lock.packages[''].version = '${NEW}';
}
fs.writeFileSync('package-lock.json', JSON.stringify(lock, null, 2) + '\n');
"
```

### 5e. Verify all bumps

```bash
grep -E "Version: |WPUF_VERSION" wpuf.php
grep -E "Stable tag|Tested up to" readme.txt
grep '"version"' package.json | head -1
grep '"version"' package-lock.json | head -2
```

All must show `${NEW}`.

## Step 6 — Replace WPUF_SINCE placeholders

```bash
grep -rn "WPUF_SINCE" --include="*.php" .
```

Replace:

```bash
COUNT=$(grep -rl 'WPUF_SINCE' --include='*.php' . 2>/dev/null | wc -l | tr -d ' ')
if [ "$COUNT" -gt 0 ]; then
  grep -rl "WPUF_SINCE" --include="*.php" . | xargs sed -i '' "s/WPUF_SINCE/${NEW}/g"
  echo "Replaced WPUF_SINCE in $COUNT file(s)"
else
  echo "No WPUF_SINCE found, skipping"
fi
```

Verify clean:

```bash
grep -rn "WPUF_SINCE" --include="*.php" . || echo "Clean"
```

## Step 7 — Add changelog to readme.txt

Use node for reliable multi-line insert:

```bash
TODAY=$(date '+%-d %B, %Y')
CHANGELOG_BLOCK="= v${NEW} (${TODAY}) =
Enhance – <description>
Fix – <description>
"

CHANGELOG_FILE=$(mktemp)
printf '%s' "$CHANGELOG_BLOCK" > "$CHANGELOG_FILE"

node -e "
const fs = require('fs');
const changelog = fs.readFileSync('${CHANGELOG_FILE}', 'utf8').trimEnd();
const content = fs.readFileSync('readme.txt', 'utf8');
const updated = content.replace('== Changelog ==', '== Changelog ==\n' + changelog + '\n');
fs.writeFileSync('readme.txt', updated);
"
rm -f "$CHANGELOG_FILE"
```

> Match format of MOST RECENT existing entry. Some entries have `*` prefix, some don't. Look at the entry above to match style.

## Step 8 — Add changelog to changelog.txt (top of file)

```bash
TEMP=$(mktemp)
printf '%s\n\n' "$CHANGELOG_BLOCK" > "$TEMP"
cat changelog.txt >> "$TEMP"
mv "$TEMP" changelog.txt
```

## Step 9 — Build assets (MANDATORY — DO NOT SKIP)

Bump commit MUST include built CSS/JS/.pot/readme.md or release is broken.

### 9a. Composer

```bash
composer install --no-dev
composer dump-autoload -o
```

PSR-4 warnings about `WPUF_*` classes are pre-existing, ignore.

### 9b. Node + Grunt

```bash
npm install
grunt release
```

Task chain: less → concat → uglify → i18n → readme → tailwind → tailwind-minify

### 9c. Build distribution zip

```bash
grunt zip
```

Output: `./build/wp-user-frontend-v${NEW}.zip`

### 9d. Verify build output

```bash
git status --short | wc -l
# Expect 20+ files. If only 4-5: build did NOT run, abort.

ls -la build/*.zip
```

## Step 10 — Commit version bump

```bash
git status     # Confirm no vendor/, node_modules/, build/ in staging

git add --all
git commit -m "chore: bump version to ${NEW}"
```

## Step 11 — Finish git-flow release

> **Critical workaround for brew git-flow getopt bug.** If user installed `git-flow-avh`, can use `-m "release version ${NEW}"` directly. If brew git-flow, must use dummy `-m"x"` then re-annotate.

### Detect git-flow variant

```bash
git flow version 2>&1 | head -1
# git-flow-avh shows: "AVH Edition" 
# brew git-flow shows: "0.4.1" (no AVH)
```

### 11a. Run finish

For **git-flow-avh** (handles spaces):

```bash
GIT_MERGE_AUTOEDIT=no git flow release finish -m "release version ${NEW}" "${NEW}"
```

For **brew git-flow** (getopt bug):

```bash
GIT_MERGE_AUTOEDIT=no git flow release finish -m"x" "${NEW}"

# Re-annotate tag manually:
TAG_SHA=$(git rev-list -n 1 v${NEW})
git tag -d v${NEW}
git tag -a v${NEW} -m "release version ${NEW}" $TAG_SHA
```

> Note: gitflow auto-appends `<tagname>` to message. So `-m "release version 4.3.4"` becomes `release version 4.3.4 v4.3.4` in the actual annotated tag. Matches sapayth's pattern.

### 11b. Verify

```bash
git show v${NEW} --no-patch | head -8
# Should show: tag v${NEW}, message "release version ${NEW} v${NEW}"

git log --oneline master -3
# Top: Merge branch 'release/${NEW}'
# Next: chore: bump version to ${NEW}

git log --oneline develop -3
# Top: Merge branch 'release/${NEW}' into develop
```

## Step 12 — Push to upstream

### 12a. Push develop (always works with WRITE)

```bash
git push origin develop
```

### 12b. Push master

```bash
git push origin master
```

If `GH006: Protected branch update failed`:
- Either user got admin/maintain perm now (re-test with `gh api .../collaborators/$(gh api user --jq .login)/permission`)
- OR fall through to fork+PR fallback below

#### Fork + PR fallback (if master push blocked)

```bash
git remote add fork "git@github.com:$(gh api user --jq .login)/wp-user-frontend.git" 2>/dev/null
git push fork master
gh pr create --repo weDevsOfficial/wp-user-frontend \
  --base master --head "$(gh api user --jq .login):master" \
  --title "Release v${NEW}" \
  --body "Standard gitflow release merge into master. Cannot push directly due to branch protection.

Tag v${NEW} already on upstream (or being pushed in next step).

**Admin merge instructions:** to preserve sapayth's commit pattern, use fast-forward:
\`\`\`bash
git fetch git@github.com:USER/wp-user-frontend.git master:tmp-rel-${NEW}
git checkout master
git merge --ff-only tmp-rel-${NEW}
git push origin master
git branch -d tmp-rel-${NEW}
\`\`\`"
```

### 12c. Push tag (works even if master blocked — tags NOT protected)

```bash
git push origin v${NEW}
```

Verify:

```bash
gh api "repos/weDevsOfficial/wp-user-frontend/git/ref/tags/v${NEW}" -q '.object.sha'
git rev-parse v${NEW}
# Two should match
```

### 12d. 🔥 CRITICAL VERIFICATION before Step 13

If Appsero source = master AND user does NOT have admin push perm: **STOP HERE, do NOT proceed to release publish.**

Wait for admin to merge PR. Verify master updated:

```bash
gh api "repos/weDevsOfficial/wp-user-frontend/contents/wpuf.php?ref=master" --jq '.content' | base64 -d | grep WPUF_VERSION
gh api "repos/weDevsOfficial/wp-user-frontend/contents/readme.txt?ref=master" --jq '.content' | base64 -d | grep "Stable tag"
```

Both must show new version. If still showing old: WAIT. Publishing now = broken Appsero deploy + wp.org pipeline frozen + retry errors.

If Appsero source = develop: skip the wait, proceed.

## Step 13 — GitHub Release (DRAFT first)

### 13a. Create draft

```bash
gh release create v${NEW} \
  --repo weDevsOfficial/wp-user-frontend \
  --draft \
  --title "v${NEW}" \
  --notes "## v${NEW} (${RELEASE_DATE})

- **Enhance** – <description>
- **Fix** – <description>" \
  ./build/wp-user-frontend-v${NEW}.zip
```

### 13b. Verify draft

```bash
gh release view v${NEW} --repo weDevsOfficial/wp-user-frontend
# Look for: draft: true, asset attached, tag: v${NEW}
```

Visual review:

```bash
gh release view v${NEW} --repo weDevsOfficial/wp-user-frontend --web
```

### 13c. Publish (TRIGGERS APPSERO — irreversible)

Confirm user wants to publish. Last chance to abort.

```bash
gh release edit v${NEW} --repo weDevsOfficial/wp-user-frontend --draft=false
```

## Step 14 — Verify Appsero auto-deploy

Appsero builds zip from configured source branch (master OR develop), pushes to wp.org SVN.

### 14a. Wait 5-15 min, check SVN

```bash
curl -sI "https://plugins.svn.wordpress.org/wp-user-frontend/tags/${NEW}/" | head -1
# Expect: HTTP/2 200

curl -s "https://plugins.svn.wordpress.org/wp-user-frontend/trunk/readme.txt" | grep "Stable tag"
# Expect: Stable tag: ${NEW}
```

### 14b. Wait 15-60 min, check wp.org API

```bash
curl -s "https://api.wordpress.org/plugins/info/1.0/wp-user-frontend.json" | python3 -c "import sys,json; d=json.load(sys.stdin); print(d.get('version'), d.get('last_updated'))"
```

### 14c. Verify download zip exists

```bash
curl -sI "https://downloads.wordpress.org/plugin/wp-user-frontend.${NEW}.zip" | head -1
# Expect: HTTP/2 200
```

### 14d. If anything fails

| Symptom | Likely cause | Fix |
|---------|--------------|-----|
| SVN tag dir missing after 30 min | Appsero deploy failed | Check https://app.appsero.com dashboard for error |
| Error: "tag X already exists" | Appsero source branch is stale (not bumped) | Get master merged, retry from Appsero dashboard |
| Stable tag mismatch | readme.txt on source branch shows old version | Push fix to source branch, retry |
| wp.org download URL 404 | wp.org build pipeline frozen by Appsero error | Resolve Appsero error first, then wp.org pipeline runs |
| API stuck on old version | wp.org cache | Wait 1-2 hours. SVN is source of truth. |

## Step 15 — Sync user's local dev folder

```bash
DEV_FOLDER="$HOME/Sites/wps-site/wp-content/plugins/wp-user-frontend"
cd "$DEV_FOLDER"
git fetch upstream
git checkout develop
git merge upstream/develop
git push origin develop
```

## Step 16 — Cleanup

After Appsero deploy confirmed (SVN tag exists + wp.org download URL returns 200):

```bash
rm -rf "$RELEASE_DIR"
# Close any release-related PR if admin merged it
# gh pr close <PR-NUMBER> --repo weDevsOfficial/wp-user-frontend --comment "Merged via fast-forward"
```

## Pre-flight checklist (run BEFORE Step 1)

- [ ] User has new version number
- [ ] User has release notes (Enhance/Fix/New entries)
- [ ] User confirmed master push perm OR Appsero source branch
- [ ] All PRs being released are merged in upstream/develop
- [ ] `gh auth status` shows logged in
- [ ] `composer --version` works
- [ ] `node --version` works (v18+ recommended)
- [ ] `grunt --version` works
- [ ] Disk has 1GB+ free for clone + build

## Self-review checklist (run BEFORE Step 12 push)

- [ ] `wpuf.php` Plugin Header `Version:` = new version
- [ ] `wpuf.php` `WPUF_VERSION` constant = new version
- [ ] `readme.txt` `Stable tag:` = new version
- [ ] `readme.md` `Stable tag:` = new version (regenerated by grunt)
- [ ] `package.json` version = new version
- [ ] `package-lock.json` first 2 `version` entries = new version
- [ ] Zero `WPUF_SINCE` left in PHP files
- [ ] `readme.txt` changelog has new entry at top of `== Changelog ==`
- [ ] `changelog.txt` has new entry at top
- [ ] `git status` clean after commit
- [ ] `git tag -l v${NEW}` shows tag
- [ ] `git show v${NEW}` shows message `release version X.Y.Z vX.Y.Z`
- [ ] Bump commit modified ~20+ files (NOT just 4 — that means build didn't run)
- [ ] Built zip exists at `build/wp-user-frontend-v${NEW}.zip`

## Reference: sapayth's v4.3.2 release pattern

Mirror this exactly:

```
3252a39  Merge tag 'v4.3.2' into develop      ← back-merge (brew git-flow says "Merge branch 'release/X.Y.Z' into develop" — content same)
9a3d694  Merge branch 'release/4.3.2'         ← master merge
b19cb4b  chore: bump version to 4.3.2         ← release branch commit (~22 files)

Tag: v4.3.2 (annotated)
Tag message: "release version 4.3.2 v4.3.2"
```

## Rollback procedures

| Problem | Fix |
|---------|-----|
| Wrong version in a file before commit | edit, re-stage, re-commit |
| Bad commit on release/X (not yet finished) | `git reset --soft HEAD~1`, redo |
| `git flow release finish` fails mid-way | resolve conflict, re-run — gitflow resumes |
| Tag pushed with bug, NOT yet released | `git push origin :refs/tags/vX` then `git tag -d vX`, fix, re-tag, re-push |
| Tag pushed AND release published | Cannot retract. Ship next version |
| Pushed broken master | `git revert <merge-sha> -m 1`, push revert. Do NOT force-push public master |
| Appsero deployed broken zip | `gh release delete vX` (deletes release, not tag). Then bump + retag + new release |
| wp.org API stuck after SVN updated | Wait 1-2 hours. Don't panic |

## Known issues + workarounds (verified 2026-05-11 from v4.3.3 disaster)

1. **brew `git-flow` not installed by default** → `brew install git-flow`
2. **macOS BSD sed `0,/PATTERN/` fails on JSON** → use `node` for JSON edits
3. **brew git-flow `-m "msg with spaces"` getopt error** → use `-m"x"` + re-annotate, OR install `git-flow-avh`
4. **`master` is protected on weDevsOfficial** → write-only collaborators must use fork+PR
5. **Banner "develop ahead of master"** → normal gitflow back-merge always +1, do NOT chase zero
6. **🔥 Appsero deploys from MASTER branch on this repo** → master MUST be at new version BEFORE publishing release. Else: "tag X already exists" error, wp.org pipeline blocked. Fix: get master push perm, OR change Appsero source to develop in dashboard.

## Repo facts (cached for skill)

- Repo: `weDevsOfficial/wp-user-frontend`
- Default branch: `develop`
- Protected branches: `master`
- Repo admins: `tareq1988`, `nizamuddin`
- Maintainers (push perm): `shams-sadek1981`, `anik-fahmid`, `jamil-mahmud`, `sharifcraft`
- Tag format: `vX.Y.Z`
- Tag message format: `release version X.Y.Z vX.Y.Z` (gitflow auto-appends tagname)
- Build output: `./build/wp-user-frontend-vX.Y.Z.zip`
- Appsero config file: `appsero.json` (root, defines wp.org excludes)
- SVN ignore: `.svnignore` (extra wp.org excludes)
- Auto-deploy: Appsero on tag push + GitHub release publish

## Skill execution flow (for the agent running this)

1. **Greet + ask for inputs** (version, notes, date, perm status)
2. **Run Critical Permissions Check** — abort if user doesn't have a clear path
3. **Verify pre-flight** (`gh auth`, tools installed)
4. **Execute Steps 1-10** with user confirmation between major sections
5. **STOP at Step 12d** if appsero=master and user=write-only — wait for admin merge
6. **Resume Steps 13-16** once master verified
7. **Show Appsero verification commands** (Step 14) and tell user to check dashboard
8. **Hand off** with summary of SHAs, tag, release URL, next steps

## DO NOT

- DO NOT run release in the user's dev folder (`wp-content/plugins/wp-user-frontend`) — has multiple remotes, will push to fork
- DO NOT skip `grunt release` — built assets are committed, NOT generated by Appsero
- DO NOT publish GitHub release if Appsero source = master AND master not at new version
- DO NOT force-push master under any circumstance
- DO NOT delete a published tag unless certain (Appsero may have already deployed)
- DO NOT chase zero "ahead/behind" banner — gitflow always leaves develop +1 from master after release
