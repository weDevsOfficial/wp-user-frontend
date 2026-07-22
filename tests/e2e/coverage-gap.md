# WPUF E2E Coverage Gap Analysis

> Feature-by-feature audit of automated test coverage for **WP User Frontend Lite + Pro**
> against the current Playwright suite in `tests/e2e/`.
>
> **Method:** every feature area (Lite `includes/` + Pro `../wpuf-pro/modules` & `includes/`)
> was mapped to the `test()` blocks / feature-map IDs that actually exercise it. Counts are
> assertions/IDs, **not** `test()` blocks (many `test()` are workflow *steps*, e.g. "Admin is
> setting X"). See `CLAUDE.md` gotchas.
>
> **Legend:** ✅ strong · 🟡 partial · 🔴 none
> **Priority:** P0 revenue/security-critical · P1 core user journey · P2 module/nice-to-have
>
> Last audited: 2026-07-03 · Suite: 8 specs (`alphaSetupTest`, `postFormTest`,
> `postFormSettingsTest`, `regFormTestPro`, `regFormSettingsTestPro`, `fieldOptionSettingsTest`,
> `subscriptionTest`, `mailpoetRegistrationTestPro`).
> Stabilization pass: 2026-07-22 (see "Suite stabilization" below).

---

## Executive summary

| # | Feature area | Status | Priority of gap |
|---|---|---|---|
| 1 | Login & environment setup | ✅ | — |
| 2 | Post forms & field types (+ FE round-trip, uploads, edit/delete) | ✅ | P2 (Pro field types) |
| 3 | Post form settings | ✅ | P2 |
| 4 | Registration forms (+ vendor) | ✅ | P2 |
| 5 | Registration form settings | ✅ | — |
| 6 | Field option settings | ✅ | P2 |
| 7 | Subscriptions | 🟡 bank only | **P0** |
| 8 | **Payments — Stripe / PayPal** | 🔴 enable-only | **P0** |
| 9 | **Coupons & Tax** | 🔴 | **P0** |
| 10 | Email marketing (MailPoet) | 🟡 WIP | P1 |
| 11 | Email marketing (Mailchimp/GetResponse/ConvertKit/Campaign Monitor) | 🔴 | P2 |
| 12 | Content / menu / taxonomy restriction | 🟡 field-visibility only | **P0** |
| 13 | User dashboard & account page | 🟡 posts edit/delete | P1 (profile/subscription/billing) |
| 14 | Frontend login / lost-password / social login | 🔴 | **P1** |
| 15 | AI form builder & AI Review | 🔴 enable-only | P2 |
| 16 | Pro modules (directory, PM, SMS, reports, analytics, QR, BuddyPress, PMPro, comments, SEO, Zapier) | 🔴 | P2 |
| 17 | Integrations (Elementor, Events Calendar, ACF, n8n) | 🔴 | P2 |
| 18 | reCaptcha / Turnstile / Math captcha (functional) | 🟡 Math enforced (`PF0027`); reCaptcha/Turnstile 🔴 | P1 |
| 19 | Widgets & shortcodes | 🔴 | P2 |
| 20 | **REST API (`wpuf/v1`)** | 🟡 core layer built | P1 (wrong-role, update, AI routes) |
| 21 | **Negative / security / authorization** | 🔴 | **P0** |
| 22 | Cross-browser / mobile viewport | 🔴 Chromium-only | P1 |

**Top of the backlog (build first):** #8 Payments (Stripe/PayPal txns), #9 Coupons & Tax math,
#20 REST API layer, #21 negative/security cases, #12 content restriction, #7 recurring/renewal
subscription lifecycle.

---

## Suite stabilization (2026-07-22)

A full-suite green pass (`npm run test:setup` then `test:e2e`) surfaced 3 real failures + 2
flaky ones — all in the newly-added edit/delete/vendor journeys — plus a whole class of misleading
symptoms. Findings and fixes (all in the e2e harness, no plugin code changed):

**Config nuance that shapes every failure:** `playwright.config.ts` sets `actionTimeout: 0`, so a
single stuck action does **not** fail fast — it waits until the 120s **test** timeout, which tears
down the page. Every "element never appeared" bug therefore surfaces as `Target page, context or
browser has been closed` after exactly `2.0m`, never as a clean "locator timed out". Read that
message as "the thing it was waiting for never happened", then look at the `waiting for locator(...)`
line for the real cause.

**Serial fail-fast amplifies one failure into many.** Each stateful spec calls
`configureSpecFailFast()` → `test.describe.configure({ mode: 'serial' })`, so the first failure in a
file marks **every later test in that file `skipped`** ("did not run"). The mass-skips users see are
a *symptom* of one upstream failure, not N independent breakages. Fixing the first failure in a file
routinely unblocks a dozen "did not run" tests — and can **expose** the next real failure behind it
(that is how `PF0027`/`RF0014` first became visible once `PF0026`/`RF0012` were fixed).

**Real bugs fixed (test-side):**
- **`PF0026` (edit not persisting).** The Lite post form carries a **Math Captcha** field. Its
  submit handler calls `e.preventDefault()` and **silently returns until the equation is answered**
  — no error, no AJAX, no save. Creation already solved the captcha; the new dashboard **edit** path
  did not, so the update never persisted and the dashboard kept showing the old title. Fix:
  `postForm.ts::solveMathCaptchaIfPresent()` (reused solver, no-op when absent), called before the
  edit submit. *Verified live via the Playwright MCP: with the captcha answered the form redirects to
  `?msg=post_updated` and the title persists server-side.* **This is real WPUF behavior worth its own
  coverage — see #18: a captcha field genuinely blocks submission.**
- **`PF0027` (author logout hang).** `BasicLogoutPage.logOut()` hovers the wp-admin **"Howdy,"**
  admin-bar flyout. The post author is a **low-privilege front-end user**; visiting `/wp-admin/`
  redirects them to the front-end where that flyout does not exist, so the hover hangs 2 min. Every
  other `logOut()` call runs as **admin**, which is why this was the only place it broke. Fix: use
  the existing `signOutFE()` (WPUF account-page **"Sign out"** link) for front-end users.
- **`RF0012` (WC Vendors form, logged-out).** `RF0008` logs out for the FE vendor registration, and
  the admin **re-login lived in `RF0010`** — which `test.skip`s when the Dokan/Google-Maps flow
  (`RF0009`) is unavailable in this env. So `RF0012`+ ran **logged-out**, wp-admin bounced to the
  login form, and nothing was found. Fix: re-login admin at the start of `RF0012` (session-aware, so
  a no-op when already authenticated). This decouples the WC-Vendors block from the Maps env gap.

**Flaky (SPA/validation races) hardened:**
- **`SB0044` (recurring pack).** The subscription builder is a Vue SPA; the "Create New" click can
  land before the app mounts and no-op, so the "Subscription Details" tab never appears and the next
  step hangs. Fix: `subscription.ts::createSubscriptionPack` confirms the builder opened and re-clicks
  (bounded retries).
- **`RF0014` (WC Vendor register).** WPUF keeps the **Register** button `disabled` until every
  required field validates; a fast fill can race the still-disabled button. In isolation the form
  enables fine — confirmed by a direct Playwright probe. Fix: wait for
  `input[value="Register"]:not([disabled])` before clicking.

**Environmental, not code (do not "fix" in code):**
- Repeated **killed runs left ~16 zombie `chromium`/`headless_shell` processes** (load avg ~6),
  which starved the shared wp-env and produced *random* 2-min timeouts across unrelated tests
  (including `setup`'s `LS0036`) plus `ENOENT .playwright-artifacts` trace errors. Cure = kill strays
  + `rm -rf test-results/.playwright-artifacts-*`, then re-run. **A single stray-process pass before a
  full run is now part of the runbook.**
- Re-running a stateful spec **without a `test:setup` reset** re-creates fixtures and yields
  **strict-mode violations** (e.g. two `iPhone 16 Pro Max` products → `resolved to 2 elements`). These
  serial specs are only safe to re-run after a reset. Not a bug — an isolation property.

**Terminal readability:** page-object step logs (`✅ Clicked on //…`) are now **suppressed by
default** (`pages/base.ts` no-ops `console.log` unless `E2E_VERBOSE=1`) and the `list` reporter runs
with `printSteps: false`, so the terminal shows only test titles + Playwright's `✓/✘/-` status
markers and the end-of-run failure blocks. Set `E2E_VERBOSE=1` to restore per-action locator logs
when debugging one test.

**Net result:** all five target tests green; full suite moved from **337 → 343 passed** with the
remaining reds being the pre-existing env-gated skips (Google Maps `RF0009`–`RF0011`, MailPoet/SMTP
`EM0004`), not code defects.

---

## 1. Login & environment setup — ✅ (persistence now covered)

**Covered:** `alphaSetupTest.spec.ts` — `RS0001`, `LS0001`–`LS0036`.
Admin login, dashboard reached, Lite+Pro activation, **license activation**, WPUF setup wizard,
permalinks, form-list pages, WPUF settings, "anyone can register", create user, categories/tags,
Google Map + reCaptcha + Turnstile credentials, module **on/off** toggles, payment-gateway
**enable** (bank/Stripe/PayPal), AI **enable** (Google AI/OpenAI), dokan-lite activation, logout.

**Persistence verification added (`LS0032`–`LS0036`, 2026-07-03):** each configured surface is
now reloaded and asserted to have **persisted server-side** — previously the setup steps only
filled+saved with no proof it stuck. Locators were detected via the **Playwright MCP** and added
to `selectors.ts` (`settingsSetup.persistence`), assertions live in `settingsSetup.ts`
`validate*Persistence*` methods:
- `LS0032` permalink structure persists (`/%postname%/`).
- `LS0033` anyone-can-register (`users_can_register`) persists.
- `LS0034` payments enabled + bank/Stripe/PayPal gateways + PayPal sandbox mode persist.
- `LS0035` active AI provider selection persists (OpenAI).
- `LS0036` **deterministic round-trip** — write a sentinel Google Map API key → save → reload →
  assert → restore (env-independent, since the `.env` keys are 2-char stubs); also asserts the
  Turnstile enable toggle persisted.

**Remaining (moved to their own sections):**
- 🟡 The credential fields are still *enable/store only*; **functional** Stripe/PayPal/AI/captcha
  behavior is tracked under #8, #15, #18 (not section 1).
- 🔴 Setup-**wizard step choices** (`LS0006` just clicks Let's Go → Continue → End) still not
  asserted step-by-step. *(P2)*

---

## 2. Post forms & field types — ✅ (Lite + FE round-trip + edit/delete)

**Covered:** `postFormTest.spec.ts` — `PF0001`–`PF0028`.
Blank form with **all Lite fields**, page shortcode, **FE post creation + data round-trip**
(create → validate list → validate entered data BE/FE), **preset** form, **guest posting**
(`PF0007`–`PF0010`), **WooCommerce product** form (`PF0011`–`PF0017`), **EDD downloads** form
(`PF0018`–`PF0024`, Pro).

**Correction (2026-07-03):** the FE round-trip (`createPostFE` + `validateEnteredData`) is much
stronger than previously documented — it **uploads and asserts** the Featured Image, Image Upload,
and File Upload fields via `setInputFiles` (fixtures in `uploadeditems/`), and round-trips ~25
fields incl. several Pro ones (Date/Time, Country, Phone, multi-line Address, Embed, Ratings,
Math Captcha solved programmatically). So "upload not exercised" was **wrong** and is removed.

**Frontend post management added (`PF0025`–`PF0028`, 2026-07-03 / captcha 2026-07-22):** a real user
journey with no prior coverage — the user **edits** (title round-trip via the `⋮` → Edit dropdown,
`PF0025`–`PF0026`), then `PF0027` proves the **Math Captcha is enforced** on that edit form
(unanswered → blocked, answered → saved), then **deletes** (accepting the "Are you sure?" confirm)
their own post from the account **Posts** tab (`PF0028`). Self-cleaning (removes the post created in
`PF0003`). Locators detected via **Playwright MCP**
(`Selectors.postForms.dashboardManage`), POM methods in `postForm.ts`. Notes surfaced while
building: the Edit/Delete links live inside a hidden `⋮` dropdown that must be opened first, and
the post-update redirect races the next navigation (`ERR_ABORTED`) — both handled in the POM.

**Remaining gaps:**
- 🔴 **Pro-only field types not FE-round-tripped:** Signature, Repeat field (added to builder but
  its FE fill is commented out), Step/multi-step, Pricing fields (Checkbox/Dropdown/MultiSelect/
  Radio/Price/Cart Total), Really-Simple captcha, Avatar/Cover/Profile Photo (RF-side), Gender,
  DOB, Nickname, Display Name, Secondary Email, Social (FB/IG/LinkedIn), TOC. *(P2, high count.)*
  Source: `../wpuf-pro/includes/Fields/`.
- 🟡 **Upload validation is shallow** — asserts *an* attachment is visible on the post, not the
  specific filename/attachment id. Strengthen to verify the exact uploaded file. *(P2)*
- 🔴 Field **column / section-break** layout is validated in the *builder* but its **FE rendering**
  into columns is not asserted. *(P2)*
- 🔴 **reCaptcha/Turnstile on the post form** — commented out in `addOthers_Common` (see #18). *(P1)*

---

## 3. Post form settings — ✅

**Covered:** `postFormSettingsTest.spec.ts` — `PFS0001`–`PFS0059`.
Post type, default category, **4 redirect targets**, submission status (draft/pending/private/publish
× set→validate→FE), save-as-draft, submit-button text, **multi-step (Pro)**, update-status matrix,
update redirects, update message, lock-editing-after-time, form title/description, **pay-per-post**
(`PFS0057`–`PFS0059`, incl. accept payment).

**Gaps:**
- 🔴 **Post expiration** (`../wpuf-pro/includes/Post_Expiration.php`) — expiry date, expired-post
  status change, expiration email. Not tested. *(P1)*
- 🔴 **Notification emails** for post submission (admin/user) content not asserted via SMTP capture. *(P2)*
- 🔴 Pay-per-post tested with **bank only**; no Stripe/PayPal pay-per-post (ties to #8). *(P0)*
- 🔴 **Comment/feature toggle, custom post status** edge cases. *(P2)*

---

## 4. Registration forms (+ vendor) — ✅

**Covered:** `regFormTestPro.spec.ts` — `RF0001`–`RF0023`.
Reg-form fields add/validate, shortcode page, **user registration + validation**, **Dokan vendor**
registration (default + in-Dokan validation), **WC Vendors** (with email verification + activation),
**WCFM Membership** (multi-step + email verification).

**Gaps:**
- 🔴 **Duplicate-email / already-registered** rejection. *(P1, negative)*
- 🔴 **Weak/mismatched password** and **required-field** failures on the reg form. *(P1, negative)*
- 🔴 **Registration with a paid subscription** attached (pay-on-registration flow). *(P0, ties to #7/#8)*
- 🔴 **reCaptcha/Turnstile actually enforced** on the reg form. *(P1, see #18)*

---

## 5. Registration form settings — ✅

**Covered:** `regFormSettingsTestPro.spec.ts` — `RFS0001`–`RFS0059`.
All 5 **roles** set→validate, **approval** flow (needs approval → can't login → approve → can login),
after-registration redirects (3), success message, submit text, profile-update redirects/message,
**email verification** (set subject/body/tags → register → click activation link), **welcome email**,
**admin notification email**, **multi-step** progressbar + by-step.

**Gaps:**
- 🟡 Strong. Minor: **template-tag rendering correctness** in delivered emails asserted only loosely.
- 🔴 **Login-after-verification lockout** edge (login blocked until verified) only partially covered. *(P2)*

---

## 6. Field option settings — ✅

**Covered:** `fieldOptionSettingsTest.spec.ts` — `FOS0001`–`FOS0105`.
Label, meta key, help text, placeholder, default value, required, CSS class, size, read-only,
show-in-post, hide-label, **visibility (public/subscription/logged-in/hidden)**, **content
restriction (min/max char & word)**, **conditional logic (Pro)**, rich-text, dropdown options,
category type (text/checkbox/multiselect), selection type (include/exclude), inline list,
time format+interval, max files, max image size, button text, country/default-country/hide-countries,
address line 2, icons, numeric min/max/step, date format/time/range, open-in-new-window.

**Gaps:**
- 🔴 **Conditional logic** covered for one field pair only (`FOS0034/35`); no multi-condition / AND-OR
  / nested logic. *(P2)*
- 🔴 **Content-restriction min/max** validated on text only; not on other field types. *(P2)*
- 🔴 File-field option **allowed file types / size rejection** (upload a disallowed file → error). *(P1, negative)*

---

## 7. Subscriptions — 🟡 (bank transfer only)

**Covered:** `subscriptionTest.spec.ts` — `SB0001`–`SB0046`.
Free pack (create→buy→activate→subscribers count), **pack CRUD** (draft/publish/trash/restore/
delete/quick-edit + counts), paid pack via **bank** (`SB0026`–`SB0033`: one-time payment, complete
bank payment, accept transaction, subscription active, expiration day), **limits** (max posts/pages/
user-requests, featured-item exceeded, decreased limits), **cancel** subscription, **recurring** pack
create+validate (`SB0044`–`SB0046`).

**Gaps (all P0 unless noted):**
- 🔴 **Card/Stripe subscription payment** — the whole card path is **commented out** (`SB0029`–`SB0033`
  block at spec tail). This is the single biggest revenue-path gap.
- 🔴 **PayPal subscription payment** — none.
- 🔴 **Recurring billing lifecycle** — `SB0044`–`SB0046` only create+validate a recurring pack; no
  actual **renewal charge**, **trial period**, or **auto-renew → cancel** behavior.
- 🔴 **Subscription expiration enforcement** — expiry date is validated but not the *post-expiry*
  lockout (user blocked from posting after pack expires).
- 🔴 **Prorate / upgrade / downgrade** between packs. *(P1)*
- 🔴 **Pack assignment to specific form / role gating** on packs. *(P2)*

See `Subscription_Scenarios_Coverage_Analysis.md` for the detailed scenario matrix.

---

## 8. Payments — 🔴 (enable-only) — **P0, build first**

**Covered:** Bank transfer only (via #7 `SB0026`–`SB0033` and #3 pay-per-post `PFS0057`–`PFS0059`).
Stripe/PayPal are **enabled** in `alphaSetupTest` (`LS0023`–`LS0026`) but never transact.

**Gaps (P0):**
- 🔴 **Stripe** — card checkout success, declined card, 3DS/SCA, webhook confirmation, refund.
  Keys already in `.env-example`. Module: `../wpuf-pro/modules/stripe`.
- 🔴 **PayPal** — checkout success, cancel, IPN/return handling.
- 🔴 **Payment failure / cancel / duplicate-submission** handling for every gateway.
- 🔴 **Server-side price tampering** rejected (submit a lower amount → server enforces real price). *(security)*
- 🔴 **Pricing fields** (`Field_Price`, `Field_Cart_Total`, Pricing Checkbox/Dropdown/Radio/MultiSelect)
  → total calculation → charged amount round-trip.

---

## 9. Coupons & Tax — 🔴 — **P0**

Source: `../wpuf-pro/includes/Coupons.php`, `Tax.php`.

**Gaps:**
- 🔴 **Coupon**: create (%/flat), apply at checkout, discount reflected in charged total, expiry,
  usage-limit, invalid-code rejection. *(P0 — pure math, ideal for API-level tests.)*
- 🔴 **Tax**: tax rate by country/state, tax added to total, tax-inclusive vs exclusive, display on
  invoice. *(P0)*
- 🔴 **Coupon + tax combined** ordering of operations.

---

## 10. Email marketing — MailPoet — 🟡 (WIP)

**Covered (in-progress, uncommitted):** `mailpoetRegistrationTestPro.spec.ts` — `EM0001`–`EM0005`:
enable Mailpoet 3 module → create reg form → enable subscribe-on-registration + pick list →
visitor registers → assert subscriber landed in the MailPoet list (verified via `utils/wpEnvCli.ts`
DB query). Modules: `../wpuf-pro/modules/mailpoet`, `mailpoet3`.

**⚠️ Known issue in this WIP (found this session):** WPUF form creation is **not idempotent** —
`createBlankForm_RF` makes a new "MailPoet Reg" form every run, so re-runs accumulate duplicates and
the unscoped name selector (`clickForm`) hits a Playwright **strict-mode violation** (2 matches).
Fix: delete pre-existing forms of that title before creating (self-cleaning), matching the
"seed own data + clean up" isolation rule. `waitForFormSaved` was already hardened to stop
*within-run* duplicate creation; the *across-run* case still needs the cleanup step.

**Gaps:**
- 🔴 MailPoet **double-opt-in** vs single, unsubscribe, list change on profile update.
- 🔴 Subscribe **on post submission** (not just registration).

---

## 11. Email marketing — other providers — 🔴 — P2

Modules present, **zero coverage**: `mailchimp`, `getresponse`, `convertkit`, `campaign-monitor`.
Same shape as MailPoet (enable module → map list → register → assert subscriber). Best done with a
provider **API stub/sandbox** or provider API assertion; avoid hitting live provider endpoints in CI.

---

## 12. Content / menu / taxonomy restriction — 🟡 (field-visibility only) — **P0**

**Covered:** Field-level **visibility** by logged-in/subscription/role (`FOS0098`–`FOS0103`).

**Gaps:**
- 🔴 **Content restriction** (`../wpuf-pro/includes/Post_View_Control`) — restrict a post/page by
  role/subscription, direct-URL access to restricted content blocked, teaser/message shown. *(P0 security)*
- 🔴 **Menu restriction** (`Menu_Restriction.php`) — hide menu items by role/subscription.
- 🔴 **`[wpuf-restrict]` shortcode** content gating.
- 🔴 **Taxonomy restriction**.

---

## 13. User dashboard & account page — 🟡 (posts edit/delete covered)

**Covered:**
- `LS0012` checks the account-page **tabs exist** from FE.
- **Posts tab edit + captcha-enforced update + delete** (`PF0025`–`PF0028`, see #2) — the user edits
  (title round-trip via the `⋮` → Edit dropdown), the Math Captcha is proven enforced on that form,
  and the post is deleted (accepting the confirm) from `/account/?section=post`. POM `postForm.ts`
  `editFirstPostFromDashboard` / `validatePostEdited` / `validateMathCaptchaEnforced` /
  `deletePostFromDashboard`.

**Gaps:**
- 🔴 Posts tab: **pagination** and **status filter** (only edit/delete of the first post is covered).
- 🔴 Account **profile edit** (change details → persist), **subscription tab** (current pack, cancel),
  **billing/transactions** history.
- 🔴 `[wpuf_account]` / `[wpuf-dashboard]` shortcode rendering per role.

---

## 14. Frontend login / lost-password / social login — 🔴 — **P1**

**Covered:** none. (`LS0001` is wp-admin login, not the WPUF `[wpuf-login]` form.)

**Gaps:**
- 🔴 **`[wpuf-login]`** — valid login, invalid credentials, redirect-after-login, logout link.
- 🔴 **Lost password / reset** flow.
- 🔴 **Custom login redirect** per role.
- 🔴 **Social login** (`../wpuf-pro/modules/social-login`) — Google/Facebook/etc. (mock provider).

---

## 15. AI form builder & AI Review — 🔴 (enable-only) — P2

**Covered:** `LS0027`/`LS0028` enable Google AI / OpenAI keys only.

**Gaps:**
- 🔴 **AI form generation** — prompt → generated form → fields present. `includes/AI_Manager.php`,
  `../wpuf-pro/includes/AI`.
- 🔴 **AI Review** (`../wpuf-pro/includes/AI_Review`, REST `REST_API_Controller.php`) — submit → AI
  review verdict. Best mocked (don't call live LLM in CI).

---

## 16. Pro modules (untested) — 🔴 — P2

Modules in `../wpuf-pro/modules/` with **no coverage**:

| Module | What to test |
|---|---|
| `user-directory` (also Lite `modules/user-directory`) | Directory shortcode, search/filter, profile view |
| `private-message` | Send/receive message between users, inbox |
| `sms-notification` | SMS trigger on registration/post (mock gateway) |
| `report` | Report generation / export |
| `user-activity` | Activity log entries recorded |
| `user-analytics` | Analytics dashboard renders data |
| `qr-code-field` | QR field renders + encodes value |
| `bp-profile` (BuddyPress) | Profile sync to BuddyPress |
| `pmpro` (Paid Memberships Pro) | Membership integration |
| `comments` | Frontend comment submission |
| `seo` | SEO meta output for submitted posts |
| `zapier` | Webhook fired on trigger (mock endpoint) |
| `email-templates` | Custom email template applied |

---

## 17. Integrations — 🔴 — P2

Lite `includes/Integrations` + Pro: **Elementor** (widget/form embed), **Events Calendar** (event
post type submission), **ACF** compatibility, **n8n/N8N** (workflow webhook), **Dokan** (partially
covered via vendor reg #4), **WC Vendors / WCMp** (partially via #4). Elementor & Events Calendar
have **zero** coverage.

---

## 18. reCaptcha / Turnstile / Math captcha (functional) — 🔴 (enable-only) — P1

**Covered:** credentials entered in setup (`LS0019`/`LS0020`); captcha **field never enforced** on a
real submission.

**Math Captcha enforcement — ✅ (`PF0027`, 2026-07-22).** Dedicated negative+positive test on the
Lite post edit form (`postForm.ts::validateMathCaptchaEnforced`): an **unanswered** submit surfaces
the `.wpuf-captcha-error` and the decoy title **does not persist** server-side; **answering** the
equation lets the same edit through (`?msg=post_updated`). This is the first test that proves a
captcha field genuinely **blocks** submission, not just that its credentials are stored.

**Gaps:**
- 🔴 **reCaptcha / Turnstile / Really-Simple** captcha still only *enable-only* — same
  submit-without-solving → **blocked**, solve → **passes** shape as `PF0027`, but for the JS/service
  captchas (test keys). *(P1 — anti-spam is a core promise.)*
- 🔴 Math-captcha enforcement is covered on the **post form** only; not yet on the **registration**
  form. *(P2)*

---

## 19. Widgets & shortcodes — 🔴 — P2

**Gaps:** `includes/Widgets/` (login widget, etc.) render + function; full **shortcode inventory**
(`[wpuf_form]`, `[wpuf_profile]`, `[wpuf-login]`, `[wpuf_sub_pack]`, `[wpuf_editpost]`,
`[wpuf-dashboard]`, `[wpuf_account]`) — assert each renders for the right audience.

---

## 20. REST API (`wpuf/v1`) — 🟡 (core layer built) — P1 for the rest

**API layer added (`AP0001`–`AP0006`, 2026-07-03):** `tests/api/wpufRestApi.spec.ts` +
`pages/api/WpufApi.ts` client (Playwright `request` context, no browser) +
the `api` project in `playwright.config.ts` (`npm run test:api`). Auth = admin **Application Password** (Basic auth),
minted per-run via `createAdminAppPassword()` in `utils/wpEnvCli.ts` — CI-safe, no manual `.env`.
Route map + payloads were reverse-engineered from `includes/Api/*` and verified live. Covered:
- ✅ **`permission_callback` enforcement** — all 8 guarded routes return **401 `rest_forbidden`**
  unauthenticated (`GET/POST wpuf_form`, `wpuf_subscription`, `.../count`, `.../count/{status}`,
  `.../subscribers`, `subscription-settings`). *(security)*
- ✅ **Status + schema** — `GET /wpuf_form` (form shape), `/wpuf_subscription/count`,
  `/subscription-settings`.
- ✅ **CRUD round-trip** — create → read (find by title) → delete a subscription pack; self-cleaning.
- ✅ **Input validation** — malformed create payload rejected (`success:false`).

**Remaining (P1):**
- 🔴 **Wrong-role (403)** — a logged-in non-admin (subscriber) hitting admin routes; currently only
  the unauthenticated (401) case is asserted.
- 🔴 **Update path** — `PUT/PATCH /wpuf_subscription/{id}` (`edit_item`, incl. `edit_single_row`).
- 🔴 **XSS/SQLi-ish payloads** sanitized on store + escaped on read.
- 🔴 **AI controllers** — `wpuf/v1/ai-form-builder/*`, `ai-review/*` (12+ routes) untested.
- 🔴 Use the client for **faster setup/teardown** of UI tests (seed a subscription via API, assert in UI).

---

## 21. Negative / security / authorization — 🔴 — **P0**

Near-zero across the whole suite. Per feature add:
- 🔴 Required-field failure, invalid input, over-limit/quota.
- 🔴 **Unauthorized access** — direct URL to restricted content/admin AJAX without capability.
- 🔴 **Payment tampering** — server rejects manipulated price/quantity/coupon.
- 🔴 **Nonce/CSRF** failures rejected on AJAX endpoints.
- 🔴 **XSS/SQLi payloads** in form fields sanitized on store + escaped on output.
- 🔴 Duplicate submission / double-charge prevention.

---

## 22. Cross-browser / mobile viewport — 🔴 (Chromium-only) — P1

`playwright.*.config.ts` run **Chromium only**, `fullyParallel:false`, `workers:1`, `retries:0`.

**Gaps:**
- 🔴 Add **Firefox** + **WebKit** projects for release-critical FE flows (post form, reg, checkout).
- 🔴 Add a **mobile viewport** project for theme-facing flows.
- 🟡 Suite is stateful/sequential; independence work (per-test seeding) would unlock real parallelism
  + `retries` — reduces flakiness and CI time.

---

## Recommended build order

1. **P0 revenue:** Stripe + PayPal transactions (#8), un-comment & finish card subscription (#7),
   Coupons + Tax math (#9) — do the math/validation parts as **API tests** where possible.
2. **P0 security:** ~~REST API layer (#20)~~ ✅ core built (`AP0001`–`AP0006`) — extend with
   wrong-role/update; negative/authorization pass (#21) + content restriction (#12).
3. **P1 journeys:** frontend login/lost-password (#14), ~~user dashboard edit/delete (#13)~~ ✅ done
   (`PF0025`–`PF0027`), captcha enforcement (#18), ~~file-upload round-trip (#2)~~ ✅ already covered
   (was a doc error), post expiration (#3).
4. **P1 infra:** finish MailPoet WIP + fix idempotency bug (#10), add Firefox/WebKit + mobile (#22).
5. **P2 breadth:** Pro field types (#2), Pro modules (#16), integrations (#17), other ESPs (#11),
   AI generation/review (#15), widgets/shortcodes (#19).

> Keep this file in sync as tests land. Companion docs:
> `Field_Options_Coverage_Analysis.md`, `Subscription_Scenarios_Coverage_Analysis.md`,
> `features-map/features-map.yml`.
