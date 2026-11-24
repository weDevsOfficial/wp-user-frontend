# WPUF Post Form — functionality, features, and test plan

Reference build: form **#10992 "Sample Form"** on `dokantesting.test`
(`wp-admin/admin.php?page=wpuf-post-forms&action=edit&id=10992`), WPUF + WPUF Pro,
new (Vue) form-editor UI.

> State when documented: form #10992 has **no fields saved** — the canvas opens on the
> "Add fields and build your desired form" empty state. Any test that assumes fields
> exist must build them first.

---

## 1. Purpose

A **post form** lets a site visitor create and edit WordPress content **from the frontend**,
without ever seeing wp-admin. The admin designs the form in the builder; WPUF renders it on a
page via shortcode and maps each field to a post property (`post_title`, `post_content`,
taxonomy) or to post meta (custom fields).

Typical uses: guest post submission, classifieds/listings, directory entries, vendor product
submission (Dokan), job boards, membership-gated content, pay-per-post.

Rendering entry points (`includes/Frontend/Shortcode.php`):

| Shortcode | What it does |
|---|---|
| `[wpuf_form id="10992"]` | Renders the form for **new** submissions |
| `[wpuf_edit]` | Renders the same form in **edit** mode (`?pid=<post id>`) |
| `[wpuf_dashboard]` | Frontend list of the user's posts, with Edit/Delete actions |

The `#10992` chip beside the form title in the editor copies that ID/shortcode.

---

## 2. Editor anatomy

Header: form title (+ dropdown), form-ID copy chip, **Preview**, **Save**.
Two top-level tabs: **Form Editor** and **Settings**.

### 2.1 Form Editor

- **Canvas** (left) — the live field stage. Each field row exposes on hover: drag handle,
  **Edit**, **Copy**, **Remove**. Fields reorder by drag.
- **Add Fields** panel (right) — searchable field catalogue, grouped (see §3). Click adds
  the field to the end of the canvas.
- **Field Options** panel (right) — options of the currently selected field (click a field
  or its **Edit** action).

First time a *custom* field is added, an info modal appears: *"Do you want to show custom
field data inside your post?"* → points to **WPUF → Settings → Frontend Posting → Show
custom fields on post content area** plus the per-field **Show Data in Post** option.
Buttons: **Okay** / **Don't show again**.

### 2.2 Field Options — common option set

Basic (varies by field type):

| Option | Applies to | Notes |
|---|---|---|
| Field Label | all | printed as `data-label` on the frontend row |
| Meta Key | custom fields only | the `post_meta` key the value is stored under |
| Help text | all | hint under the control |
| Required | custom fields | Yes/No; post fields like Post Title are required implicitly |
| Read Only | custom fields | renders disabled control |
| Show Icon | all | Yes/No |

Advanced Options (collapsed by default):

- Placeholder text, Default value
- Content restriction — restricted type (**Minimum** / **Maximum**) × restricted by
  (**Character** / **Word**) × count
- Field Size — Small / Medium / Large
- CSS Class Name
- **Show Data in Post** (custom fields) and **Hide Field Label in Post**
- **Visibility** — Everyone / Hidden / Logged-in users only / Subscription users only
- **Conditional Logic** — Yes/No, then show/hide this field based on another field's value

---

## 3. Field catalogue

Matches `tests/e2e/utils/fieldTypes.ts` (slug = builder `data-form-field`). ★ = Pro.

**Post Fields** — Post Title, Post Content, Post Excerpt, Featured Image
**Taxonomies** — Category (`taxonomy`), Tags (`post_tags`)
**Custom Fields** — Text, Textarea, Dropdown, Multi Select, Radio, Checkbox, Website URL,
Email Address, Hidden Field, Image Upload, Repeat Field ★, Date / Time ★, Time Field ★,
File Upload ★, Country List ★, Numeric Field ★, Phone Field ★, Address Field ★,
Google Map ★, Step Start ★, Embed ★
**Pricing Fields** ★ — Price, Pricing Checkbox, Pricing Radio, Pricing Dropdown,
Pricing Multi-Select, Cart Total
**Others** — Columns ★, Section Break, Custom HTML, reCaptcha, Cloudflare Turnstile,
Shortcode, Action Hook, Terms & Conditions ★, Ratings ★, Really Simple Captcha ★,
Math Captcha ★

Backend classes live in `includes/Fields/` (`Form_Field_*`, all implementing
`Field_Contract`); Pro field classes ship with wpuf-pro.

Environment-dependent (won't render without external config): Google Map (Maps JS + Places
key, referer allowlist), reCaptcha / Cloudflare Turnstile / Really Simple Captcha (keys or
companion plugin), Cart Total (payments).

Structural fields render no labelled row: Hidden Field, Step Start, Columns, Shortcode,
Action Hook.

---

## 4. Settings tab

Sidebar: **General · Payment Settings · Notification Settings · Display Settings · Advanced ·
Post Expiration · AI Review · N8N · Modules**. Every panel has **Cancel** / **Save Form**.

### 4.1 General

**Before Post Settings** — behaviour up to submission:
- Show Form Title, Form Description
- **Post Type** (default `post`; any registered CPT)
- Default Categories
- **Successful Redirection** — Newly created post / Same page / Another page / To a URL
- **Post Submission Status** — Published / Draft / Pending / Private
- Enable saving as draft
- Submit Post Button Text
- Choose Form Template
- **Enable Multi-Step** (pairs with the Step Start field)

**After Post Settings** — behaviour on edit/update:
- Post Update Status (Published / Draft / Pending / Private / No change)
- Successful Redirection (default: same page)
- Post Update Message
- **Lock User Editing After** *N* Hours
- Update Post Button Text

**Posting Control** — `post_permission`: who may submit. Includes guest posting
(`guest_post`, handled in `Frontend_Form::publish_guest_post()` with email verification)
and role-based restriction.

**View Control** — who may *see* the rendered form:
- Restrict by user roles → allowed roles + Unauthorized Message (Roles)
- Restrict by subscription packs → allowed packs + Unauthorized Message (Subscription)

### 4.2 Payment Settings
- Enable Payments (pay-per-post)
- Enable Pricing Fields Payment

### 4.3 Notification Settings
- **New Post Notification** — enable, To, Subject, Email Body
- **Update Post Notification** — enable + same fields

Placeholders usable in To/Subject/Body: `{post_title}`, `{post_content}`, `{post_excerpt}`,
`{tags}`, `{category}`, `{author}`, `{author_email}`, `{author_bio}`, `{sitename}`,
`{siteurl}`, `{permalink}`, `{editlink}`, `{custom_{META_KEY}}` (e.g. `{custom_website_url}`).

### 4.4 Display Settings
- Choose Form Style (form-template gallery)
- Use Theme CSS
- Label Position — Above Element / left / right / hidden

### 4.5 Advanced
- Enable User Comment (comment status on created post: Open/Closed)
- Enable Form Scheduling (form active between two dates)
- Limit Form Entries (max submissions)
- Conditional Logic on Submit Button (Yes/No)

### 4.6 Post Expiration
Enable Post Expiration → Expiration Time + Duration Type (Day(s)/Month(s)/Year(s)) →
Post Status after expiry (default Draft) → Send post expiration email to author.

### 4.7 AI Review
"Review Submitted Posts by AI" — routes submissions through the AI moderation flow
(`includes/AI/`).

### 4.8 N8N
Enable N8N Integration — POSTs submitted post data to an n8n webhook.

### 4.9 Modules
Lists WPUF modules affecting this form. On #10992: *"No modules have been activated yet."*
with a **Go To Module Page** link.

---

## 5. End-to-end flow

1. Admin builds form → **Save**.
2. Admin places `[wpuf_form id="10992"]` on a page (setup wizard creates a default one).
3. Visitor opens the page → View Control decides render vs unauthorized message.
4. Visitor fills fields → validation (required, content restriction, captcha, conditional
   logic) → **Submit**.
5. WPUF creates the post with **Post Submission Status**, maps post fields + taxonomies,
   writes custom fields to post meta, fires notification email, applies payment /
   expiration / AI review if enabled.
6. Redirect per **Successful Redirection**.
7. Later edits go through `[wpuf_dashboard]` → Edit (`[wpuf_edit]` page, `?pid=`), governed
   by **Post Update Status**, **Lock User Editing After**, and Update notification.

⚠️ The dashboard Edit link needs **Settings → Frontend Posting → Edit Page** set, or it
links back to the post itself (see `BUGS-FOUND.md` §20).

---

## 6. Test plan

Existing coverage lives in `tests/e2e/` (IDs in `features-map/features-map.yml`); post-form
settings already have many mapped cases — check there before adding new ones. Below is the
full surface with what to assert.

### 6.1 Builder (editor UI)

| # | Case | Assert |
|---|---|---|
| B1 | Open form editor by ID | title, `#10992` chip, Form Editor / Settings tabs render |
| B2 | Empty state | "Add fields and build your desired form" shown when no fields |
| B3 | Add every field type | each catalogue entry lands on canvas with expected `data-form-field` slug |
| B4 | Field search box | typing filters the panel; no-match state |
| B5 | Copy field | duplicate appears with a fresh meta key |
| B6 | Remove field | row disappears; Save persists removal |
| B7 | Drag reorder | order persists after Save + reload |
| B8 | Custom-field info modal | shows on first custom field; **Don't show again** suppresses it |
| B9 | Save + reload | every field and option round-trips |
| B10 | Preview | opens the rendered form for the current (saved) state |
| B11 | Pro-gated fields | on Lite, Pro fields are absent/upsell — no JS error |

### 6.2 Field options

| # | Case | Assert |
|---|---|---|
| F1 | Field label | frontend row shows the label / `data-label` |
| F2 | Meta key | value stored under that `post_meta` key after submit |
| F3 | Required = Yes | submitting empty blocks with validation message |
| F4 | Read only | control rendered disabled; value not writable |
| F5 | Help text / Placeholder / Default value | rendered on the frontend control |
| F6 | Content restriction min/max × character/word | under/over limit blocks submit |
| F7 | Field size S/M/L + CSS class | class applied on frontend markup |
| F8 | Show Data in Post / Hide Field Label in Post | meta printed (or not) in post content |
| F9 | Visibility: Hidden / Logged-in only / Subscription only | field hidden for the wrong audience |
| F10 | Conditional logic | field shows/hides as the controlling field changes |

### 6.3 Settings — General

| # | Case | Assert |
|---|---|---|
| G1 | Show Form Title / Description | rendered on frontend |
| G2 | Post Type = CPT | submission creates that CPT |
| G3 | Default categories | pre-selected + applied when field absent |
| G4 | Redirection: same page / new post / another page / URL | landing URL after submit |
| G5 | Submission status: publish / draft / pending / private | post status in wp-admin list |
| G6 | Save as draft | draft button appears; draft created, not published |
| G7 | Submit button text | button label matches |
| G8 | Multi-step + Step Start | steps navigate; validation per step |
| G9 | Update status + update message + update button text | on edit flow |
| G10 | Lock User Editing After N hours | edit blocked once the window passes |
| G11 | Posting control: guest post | guest can submit; email-verification flow publishes |
| G12 | Posting control: role restriction | disallowed role blocked |
| G13 | View control: roles / subscription packs | correct unauthorized message shown |

### 6.4 Settings — other panels

| # | Case | Assert |
|---|---|---|
| P1 | Enable Payments (pay-per-post) | checkout appears; post goes live only after payment |
| P2 | Pricing fields payment | Cart Total sums pricing fields; charged amount matches |
| N1 | New post notification | email sent to `To`, subject/body placeholders resolved |
| N2 | Update post notification | email on edit only, not on create |
| N3 | `{custom_<meta_key>}` placeholder | resolves to the submitted value |
| D1 | Form style / template | matching CSS class on the frontend form |
| D2 | Use Theme CSS | WPUF stylesheet dropped |
| D3 | Label position | label placement in DOM/CSS matches setting |
| A1 | Enable user comment | created post has comments open/closed accordingly |
| A2 | Form scheduling | before start / after end → form unavailable message |
| A3 | Limit form entries | submit blocked at the cap |
| A4 | Conditional logic on submit button | submit disabled until condition met |
| E1 | Post expiration | after the window, post flips to the configured status |
| E2 | Expiration email | author notified |
| AI1 | AI review enabled | submission held for AI moderation |
| X1 | N8N integration | webhook receives the submission payload |
| M1 | Modules panel | empty state + Go To Module Page link |

### 6.5 Regression / environment notes

- Google Map, reCaptcha, Turnstile, Really Simple Captcha, Cart Total are **environment
  dependent** — report/skip rather than fail when unconfigured (see `tests/e2e/CLAUDE.md`).
- Repeat Field is a known defect: saved as `input_type "repeat"` but registered as
  `repeat_field`, so it never renders (`BUGS-FOUND.md`).
- Frontend dashboard Edit requires **Settings → Frontend Posting → Edit Page**
  (`BUGS-FOUND.md` §20) — set it in setup before dashboard-edit tests.
- Free/Pro split: gate every ★ case behind a Pro check so Lite runs stay green.

---

## 7. Coverage gaps (audited against `features-map/features-map.yml` + `tests/`)

Existing specs: `postFormTest`, `postFormSettingsTest`, `fieldOptionSettingsTest`,
`formEditorTest`, `allFieldTypesTest`, `subscriptionTest`, `api/wpufRestApi`.
What is **not** covered today:

### 7.1 Settings panels with zero coverage

| Gap | Why it matters | Suggested case |
|---|---|---|
| **AI Review** panel | whole feature untested | enable → submit → post held for AI moderation, status/meta reflects review |
| **N8N integration** | webhook never exercised | enable + point at a local receiver → assert payload shape on submit |
| **Modules** panel | empty state + link untested | assert empty state text and **Go To Module Page** target |
| **Display → Use Theme CSS** | style regressions invisible | toggle → WPUF stylesheet absent/present on frontend |
| **Display → Label Position** | 4 positions, none tested | above / left / right / hidden → DOM/CSS placement |
| **Advanced → Form Scheduling** | date-window logic untested | before start, inside window, after end → correct message vs form |
| **Payment → Enable Pricing Fields Payment** | only pay-per-post covered | pricing fields + Cart Total → charged amount matches sum |
| **View Control (both)** | restrict-by-role and restrict-by-subscription never tested | allowed vs disallowed viewer → correct unauthorized message |

### 7.2 Mapped but only half-tested

| Gap | Current state | Missing assertion |
|---|---|---|
| **Post Expiration** | only "Admin is enabling post expiration" | post actually flips to configured status at expiry; duration types (day/month/year); expiration email to author |
| **Lock User Editing After N hours** | setting is written | edit blocked once the window passes (and allowed before) |
| **Guest posting** | guest submits + post validated | email-verification path: verify link publishes the post, admin notified after verification (`Frontend_Form::publish_guest_post`) |
| **Choose Form Template** | one preset flow | each shipped template produces its expected field set |
| **Default categories** | validated from FE | applied when the Category field is *absent* from the form |
| **Post type** | post / WC product / download | a plain custom CPT (non-WooCommerce) |
| **Copy field** | duplicate appears | duplicate gets a unique meta key (collision = silent data overwrite) |

### 7.3 Field-level gaps

| Gap | Missing |
|---|---|
| Section Break / Custom HTML | HTML actually rendered on the frontend, and escaped where it should be |
| Shortcode field | the embedded shortcode is executed on the rendered form |
| Action Hook field | `do_action( '<hook>' )` fires — attach a probe and assert output |
| Columns ★ | dropping fields into columns, column layout on the frontend |
| Terms & Conditions ★ | required-accept blocks submit (only "Open in New Window" is covered) |
| Ratings ★ | option set + submitted rating value stored in meta |
| Embed ★ | option set + rendered embed |
| Hidden Field | value persisted to post meta after submit |
| File Upload ★ | allowed file types / max size rejection (only Max Files covered) |
| Checkbox / Radio / Multi Select | option-list editing (only Dropdown options are covered), inline-list, default selections |
| Repeat Field ★ | still `test.fail()` on a known bug — needs a real case once fixed |

### 7.4 Lifecycle / frontend gaps

- **Authorization on edit**: opening `[wpuf_edit]?pid=` for a post you don't own — must be rejected. Untested.
- **Draft resume**: save-as-draft → reopen from dashboard → publish. Only the draft creation is covered.
- **Multi-step validation**: Next blocked while a required field on the current step is empty; back navigation retains values.
- **Conditional logic depth**: per-field multi-condition and chained (field A → B → C); only submit-button any/all and a single field rule are covered.
- **Form list screen**: duplicate form, trash/restore, search, bulk actions — **no coverage at all**.

### 7.5 Security / API gaps

- No REST/AJAX coverage for **post-form CRUD** — only `GET /wpuf_form` list. Missing: form save with a bad/missing nonce, save as a non-admin (capability check), malformed field payload.
- XSS coverage is limited to the **form title**. Field label, help text, placeholder, Custom HTML and the unauthorized messages are unverified.
- Captcha fields (reCaptcha, Turnstile, Really Simple Captcha) are env-gated and therefore effectively **unverified** — needs a CI environment with test keys, or the spam path stays untested.

### 7.6 Priority

1. View Control (roles + subscription) — user-visible access control, zero coverage.
2. Edit authorization (`pid` of another user's post) — security.
3. Post form save nonce/capability negative tests — security.
4. Post Expiration end-to-end + Lock User Editing — silent data/behaviour bugs.
5. Form Scheduling, Label Position, Use Theme CSS — cheap, purely deterministic.
6. Form list screen actions (duplicate/trash/search).
7. Pricing fields payment + Cart Total.
8. AI Review / N8N — new subsystems, currently untested.

---

## 8. Test cases for the §7 gaps

IDs use the `PFG####` (post-form gap) prefix and are registered in
`tests/e2e/features-map/features-map.yml`. **Pri** = 1 (do first) … 3 (nice to have).
Shared preconditions unless stated otherwise: admin logged in, a saved post form with
Post Title + Post Content + one Text custom field, and a page holding
`[wpuf_form id="<form>"]`.

**Implemented in** (`tests/e2e/tests/`, page object `pages/postFormGaps.ts`):

| Cases | Spec |
|---|---|
| PFG0000–0006 | `postFormViewControlTestPro.spec.ts` |
| PFG0010–0023, 0040–0044 | `postFormDisplayAdvancedTest.spec.ts` |
| PFG0030–0033 | `postFormPricingTestPro.spec.ts` |
| PFG0050–0056 | `postFormExpirationTestPro.spec.ts` |
| PFG0060–0067 | `postFormGuestSetupTest.spec.ts` |
| PFG0070–0081 | `postFormFieldBehaviourTest.spec.ts` |
| PFG0090–0099 | `postFormLifecycleTest.spec.ts` |
| PFG0100–0107 | `postFormSecurityTest.spec.ts` |

Side effects with no UI surface (post meta, expiry cron, seeded roles/packs) go through
`utils/wpEnvCli.ts`; those tests self-skip when no wp-env CLI container is reachable, the
same way the Pro- and key-gated cases self-skip.

### 8.1 View Control (§7.1) — Pri 1

| ID | Case | Steps | Expected |
|---|---|---|---|
| PFG0001 | Restrict by roles — allowed role sees the form | Settings → General → View Control → **Restrict by user roles** on, allow `editor` → Save → log in as an editor → open the form page | Form renders normally; submit works |
| PFG0002 | Restrict by roles — disallowed role blocked | Same setup → log in as a subscriber → open the form page | Form is not rendered; the configured **Unauthorized Message (Roles)** is shown verbatim |
| PFG0003 | Restrict by roles — logged-out visitor | Same setup → open the page logged out | Blocked, message shown, no form markup in DOM |
| PFG0004 | Restrict by subscription packs — subscriber with the pack | Create a free pack → View Control → **Restrict by subscription packs** on, select that pack → user buys the pack → open the form page | Form renders |
| PFG0005 | Restrict by subscription packs — user without the pack | Same setup → a user with no pack opens the page | Blocked with **Unauthorized Message (Subscription)** |
| PFG0006 | Both restrictions on | Roles = editor **and** pack = X → editor without the pack | Blocked (both gates must pass); message identifies the failing gate |

### 8.2 Display Settings (§7.1) — Pri 2

| ID | Case | Steps | Expected |
|---|---|---|---|
| PFG0010 | Use Theme CSS off | Settings → Display → Use Theme CSS = off → Save → open form page | WPUF form stylesheet is enqueued (`wpuf-*.css` present) |
| PFG0011 | Use Theme CSS on | Toggle on → Save → reload form page | WPUF form stylesheet is not enqueued; markup unchanged |
| PFG0012 | Label Position = Above Element | Set → Save → frontend | Label node precedes the control in DOM / layout class matches |
| PFG0013 | Label Position = Left / Right | Set each → Save → frontend | Corresponding label-position class applied; label renders on that side |
| PFG0014 | Label Position = Hidden | Set → Save → frontend | No visible label text (still accessible to screen readers if aria/label kept) |
| PFG0015 | Form style selection | Pick a non-default form style → Save → reload editor → frontend | Style persists in the editor and the frontend wrapper carries the style's class |

### 8.3 Advanced → Form Scheduling (§7.1) — Pri 2

| ID | Case | Steps | Expected |
|---|---|---|---|
| PFG0020 | Inside the schedule window | Advanced → Enable Form Scheduling, start = yesterday, end = tomorrow → Save → open form page | Form renders; submit succeeds |
| PFG0021 | Before the window opens | Start = tomorrow, end = +7 days → Save → open form page | Form hidden; "form is not available / scheduled" message shown |
| PFG0022 | After the window closes | Start = −7 days, end = yesterday → Save → open form page | Form hidden; expired-schedule message shown |
| PFG0023 | Scheduling off | Disable → Save → open form page | Form always renders regardless of the stored dates |

### 8.4 Payment — pricing fields (§7.1) — Pri 2 ★Pro

| ID | Case | Steps | Expected |
|---|---|---|---|
| PFG0030 | Enable Pricing Fields Payment | Add Price + Pricing Checkbox + Cart Total → Payment Settings → Enable Pricing Fields Payment → Save | Setting persists after reload; frontend renders the pricing fields and a Total |
| PFG0031 | Cart Total sums selections | Frontend: select two priced options | Cart Total updates live to the sum |
| PFG0032 | Charged amount matches the cart | Submit → payment screen | Amount due equals Cart Total; post stays unpublished until payment is accepted |
| PFG0033 | Pricing payment disabled | Turn the setting off → submit | No payment step; post created directly |

### 8.5 AI Review / N8N / Modules (§7.1) — Pri 3

| ID | Case | Steps | Expected |
|---|---|---|---|
| PFG0040 | AI Review enabled | Settings → AI Review → Review Submitted Posts by AI on → Save → submit a post from the frontend | Setting persists; submission routed through AI moderation — post status / review meta reflects the verdict, no PHP notice in the log |
| PFG0041 | AI Review disabled | Toggle off → submit | Normal flow, no review meta written |
| PFG0042 | N8N enabled | Settings → N8N → enable, webhook URL = local receiver → Save → submit a post | Receiver gets one POST; payload contains post ID, title and the custom-field values |
| PFG0043 | N8N bad/unreachable URL | Set an unreachable URL → submit | Submission still succeeds (post created); failure is logged, not fatal |
| PFG0044 | Modules panel empty state | Settings → Modules on a site with no modules active | "No modules have been activated yet." + **Go To Module Page** links to the WPUF Modules screen |

### 8.6 Post Expiration & edit lock (§7.2) — Pri 1

| ID | Case | Steps | Expected |
|---|---|---|---|
| PFG0050 | Expiration settings persist | Post Expiration → enable, time = 1, Day(s), status = Draft, email on → Save → reload | All four values round-trip |
| PFG0051 | Post flips status at expiry | Submit a post, then move the expiration date into the past (meta edit) and run the expiry cron | Post status becomes the configured one (Draft); it is no longer publicly visible |
| PFG0052 | Duration types | Repeat PFG0051 for Month(s) and Year(s) | Stored expiry date matches the chosen unit |
| PFG0053 | Expiration email | With "Send post expiration email to author" on, trigger expiry | Author receives the expiration mail once |
| PFG0054 | Expiration disabled | Disable → submit | No expiry meta written; post stays published |
| PFG0055 | Lock editing — inside the window | Lock User Editing After = 2 hours → submit a post → open it from the dashboard immediately | Edit form loads and updates save |
| PFG0056 | Lock editing — after the window | Same form, backdate the post beyond the lock → open Edit from the dashboard | Editing blocked with the lock message; no edit form rendered |

### 8.7 Guest posting & form setup (§7.2) — Pri 1–2

| ID | Case | Steps | Expected |
|---|---|---|---|
| PFG0060 | Guest submission pends until verified | Posting Control = guest post, require verification → submit as a guest with a valid email | Post is created but not published; verification mail sent to the guest |
| PFG0061 | Verification link publishes | Open the verification link from the mail | Post moves to the configured submission status; guest user created/linked as author |
| PFG0062 | Admin notified after verification | Same flow with New Post Notification on | Admin mail fires **after** verification, not at submit time (`wpuf_guest_post_email_verified`) |
| PFG0063 | Invalid/expired verification link | Tamper with the activation key | Rejected; post stays unpublished |
| PFG0064 | Form template presets | Create a form from each shipped template | Each produces its documented field set and settings |
| PFG0065 | Default category with no Category field | Set Default Categories, remove the Category field → submit | Post lands in the default category |
| PFG0066 | Plain custom post type | Register a test CPT → Post Type = that CPT → submit | Post created under the CPT; appears in its admin list |
| PFG0067 | Copy field meta key uniqueness | Add a Text field with meta key `foo` → **Copy** → Save → submit with different values | Duplicate carries a distinct meta key; both values stored, neither overwritten |

### 8.8 Field behaviour (§7.3) — Pri 2

| ID | Case | Steps | Expected |
|---|---|---|---|
| PFG0070 | Section Break renders | Add Section Break with title + description → frontend | Title/description printed; a separator wraps the following fields |
| PFG0071 | Custom HTML renders and is scoped | Custom HTML = `<div id="probe">hi</div>` → frontend | Element present; a `<script>` payload in the same field is not executed |
| PFG0072 | Shortcode field executes | Shortcode field = a registered test shortcode → frontend | Shortcode output appears (raw tag text does not) |
| PFG0073 | Action Hook field fires | Action Hook = `wpuf_test_probe`, attach a callback printing a marker → frontend | Marker printed at the field's position |
| PFG0074 ★ | Columns hold nested fields | Add Columns, drop two fields into separate columns → Save → frontend | Editor keeps the nesting after reload; frontend renders side-by-side columns |
| PFG0075 ★ | Terms & Conditions blocks submit | ToC field required → submit without accepting | Submission blocked with a validation message; accepting allows submit |
| PFG0076 ★ | Ratings value stored | Add Ratings, submit a 4-star value | Meta stores `4`; the edit form re-selects 4 |
| PFG0077 ★ | Embed field | Add Embed, submit a supported URL | Meta stores the URL; the post/edit form renders the embed |
| PFG0078 | Hidden Field persists | Hidden Field with meta key + default value → submit | Value written to post meta (not visible on the form) |
| PFG0079 ★ | File Upload restrictions | Set allowed types = pdf, max size small → try a .exe and an oversized file | Both rejected with the field's error; a valid pdf uploads |
| PFG0080 | Checkbox / Radio / Multi Select option lists | Configure options + defaults + inline list for each of the three | Options, default selection and inline layout render on the frontend; selected values round-trip |
| PFG0081 ★ | Repeat Field renders | Add Repeat Field, save, open frontend | Field renders and repeats — currently fails (`input_type` mismatch, see `BUGS-FOUND.md`); flip from `test.fail()` when fixed |

### 8.9 Lifecycle / frontend (§7.4) — Pri 1–2

| ID | Case | Steps | Expected |
|---|---|---|---|
| PFG0090 | Edit another user's post is rejected | User A submits a post → user B opens `[wpuf_edit]` page with `?pid=<A's post>` | Edit form not rendered; permission message shown; no update possible |
| PFG0091 | Edit with a bogus `pid` | Open the edit page with `?pid=999999` and with `?pid=<a post from another form>` | Graceful message, no fatal error, no blank form |
| PFG0092 | Draft resume | Enable save-as-draft → save a draft → open it from the dashboard → complete and submit | Draft values are pre-filled; publishing uses the Post Submission Status |
| PFG0093 | Multi-step blocks Next on invalid step | Multi-step form, required field on step 1 empty → click Next | Navigation blocked with a validation message |
| PFG0094 | Multi-step back keeps values | Fill step 1 → Next → Back | Step 1 values retained |
| PFG0095 | Chained conditional logic | Field B shows when A = x; field C shows when B = y | Setting A reveals B; setting B reveals C; resetting A hides both |
| PFG0096 | Multi-condition field rule (any / all) | One field with two conditions in "any" then "all" mode | Visibility matches the mode |
| PFG0097 | Form list — duplicate | Post Forms list → duplicate a form | Copy created with the same fields/settings and a new ID; original untouched |
| PFG0098 | Form list — trash / restore / delete | Trash a form, restore it, then delete permanently | List counts update at each step; a page holding the deleted form's shortcode degrades gracefully |
| PFG0099 | Form list — search & pagination | Search by form title; page through the list | Only matching forms shown; empty-search state handled |

### 8.10 Security / API (§7.5) — Pri 1

| ID | Case | Steps | Expected |
|---|---|---|---|
| PFG0100 | Form save without a nonce | POST the form-builder save request with the nonce removed | 400/403, form unchanged |
| PFG0101 | Form save with a stale/invalid nonce | Same with a garbage nonce | Rejected, form unchanged |
| PFG0102 | Form save as a non-admin | Authenticate as subscriber/editor → send the save request | 403 (capability check), form unchanged |
| PFG0103 | Malformed field payload | Save with a field object missing `input_type` / with an unknown type | Rejected or ignored safely; no fatal, no corrupted form meta |
| PFG0104 | XSS in field label / help text / placeholder | Set `<script>alert(1)</script>` in each → frontend | Escaped as text, script never executes |
| PFG0105 | XSS in unauthorized messages | Script payload in the roles/subscription unauthorized message → blocked viewer | Escaped when displayed |
| PFG0106 | XSS in Custom HTML field | Script payload in Custom HTML | Documented behaviour asserted explicitly (allowed for admins / stripped) — pick one and lock it in |
| PFG0107 | Captcha env-gated spam path | With test keys configured in CI, submit with a missing/invalid captcha token | Submission rejected; with a valid token it succeeds. Skip-with-report when keys are absent |
