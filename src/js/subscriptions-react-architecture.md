# Subscriptions React — Architecture Report

**Entry point:** `src/js/subscriptions-react.jsx`  
**Scope:** Subscription management UI (list, form, preferences, quick edit) with URL-based navigation.

---

## 1. Architecture Overview

### 1.1 High-Level Structure

The app is a **single React tree** mounted on `#wpuf-subscription-page`, with:

- **URL as source of truth** for view and resource (action, id, post_status, p).
- **WordPress `@wordpress/data`** for global state (subscriptions, router, notices, quick edit, etc.).
- **Route-derived rendering**: one of List, Form (new/edit), or Preferences is shown based on `params.action` and `params.id`.

```
┌─────────────────────────────────────────────────────────────────────────┐
│  PHP: subscriptions-react.php → #wpuf-subscription-page                  │
└─────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────┐
│  subscriptions-react.jsx                                                │
│  • createRoot(container).render(<SubscriptionsApp />)                    │
│  • Registers 6 stores (side-effect imports)                             │
└─────────────────────────────────────────────────────────────────────────┘
                                    │
        ┌───────────────────────────┼───────────────────────────┐
        ▼                           ▼                           ▼
┌───────────────┐         ┌─────────────────┐         ┌─────────────────┐
│ core/router   │         │ wpuf/subscriptions │       │ Other stores    │
│ (URL params)  │         │ (items, item,    │         │ notice,         │
│               │         │  counts, dirty,   │         │ quickEdit,      │
│               │         │  popup, etc.)     │         │ component,      │
│               │         │                   │         │ fieldDependency │
└───────────────┘         └─────────────────┘         └─────────────────┘
        │                           │
        └───────────┬───────────────┘
                    ▼
        ┌───────────────────────────┐
        │ SubscriptionsApp          │
        │ • Reads router + store     │
        │ • Renders by action:       │
        │   list | edit/new | prefs  │
        │ • Unsaved popup (local +   │
        │   store state)             │
        │ • QuickEdit (store-driven) │
        └───────────────────────────┘
```

### 1.2 Entry Point Responsibilities

`subscriptions-react.jsx`:

1. **Store registration** — Imports register: `subscription`, `fieldDependency`, `notice`, `component`, `quickEdit`, `router`.
2. **Route derivation** — Reads `core/router` → `params`; derives `action`, `subscriptionId`, `status`, `activeStatus`.
3. **Global subscription state** — Uses `wpuf/subscriptions` for `allCount`, `isDirty`, `isUnsavedPopupOpen`.
4. **Navigation & unsaved flow** — `navigate`, `setIsUnsavedPopupOpen`, `setIsDirty`; local `pendingStatus` for “discard then go” target.
5. **Layout** — Header, ContentHeader, SidebarMenu, main content (List / Form / Preferences), unsaved popup, QuickEdit.
6. **Boot** — `fetchCounts()` on mount.

### 1.3 Component Hierarchy

```
SubscriptionsApp
├── Header (utm="wpuf-subscriptions")
├── ContentHeader (status, allCount, onAddSubscription)
├── div (layout + blur when popup)
│   ├── SidebarMenu (activeStatus, allCount, onStatusClick, isUnsavedPopupOpen)
│   └── main content (by action)
│       ├── Preferences
│       ├── SubscriptionForm (mode, subscriptionId, onDiscardChanges, onContinueEditing)
│       └── SubscriptionList
├── Unsaved changes popup (conditional)
└── QuickEdit (store-controlled modal)
```

### 1.4 Stores

| Store                     | Role |
|---------------------------|------|
| `core/router`             | URL query params; `navigate(params)` updates URL and state. |
| `wpuf/subscriptions`      | List, current item, counts, dirty/popup, loading/updating, errors. |
| `wpuf/subscriptions-notice` | Toasts/notices (e.g. success/error). |
| `wpuf/subscriptions-quick-edit` | Quick edit modal open/close and context. |
| `wpuf/subscriptions-component` | Component-level state (if used). |
| `wpuf/field-dependency`  | Field dependency state. |

---

## 2. Data Flow

### 2.1 URL → View

1. **Initial load:** Router store falls back to `window.location.search` in `getQueryParams`.
2. **Navigation:** Any `navigate({ action, id, post_status, p })` (from app or hooks) updates:
   - `window.history.pushState`
   - Router store state (`params`).
3. **Re-render:** `SubscriptionsApp` uses `useSelect( 'core/router' )` → `params`; derives `action`, `subscriptionId`, `status`.
4. **Content switch:** JSX branches on `action`: `preferences` → `<Preferences />`, `edit`/`new` → `<SubscriptionForm />`, else `<SubscriptionList />`.

So **URL drives which view is rendered**; list status and pagination also come from URL.

### 2.2 List View

1. **SubscriptionList** reads `params.post_status`, `params.p` and store’s `currentSubscriptionStatus`, `allCount`, `isLoading`, `subscriptionList`.
2. **Sync:** `useEffect` syncs store `currentStatus` with URL status.
3. **Fetch:** On mount / status change, `fetchItems(currentSubscriptionStatus)` and `fetchCounts()` (thunks in `wpuf/subscriptions` calling `/wpuf/v1/wpuf_subscription` and count endpoint).
4. **Navigation:** Uses `useSubscriptionNavigation()` → `goToNew`, `goToEdit`, `goToPage` (all delegate to `navigate`).

### 2.3 Form View (New / Edit)

1. **SubscriptionForm** receives `mode` and `subscriptionId` from parent (derived from URL).
2. **Edit:** `useEffect` runs `apiFetch( /wpuf/v1/wpuf_subscription/${subscriptionId} )` then `setItem` / `setItemCopy`.
3. **New:** `setBlankItem()`.
4. **Edits:** User changes → `modifyItem(...)` → store `item` and `isDirty`.
5. **Save:** `handlePublish` → store’s `updateItem()` (API) → on success can call `goToList()` (navigation hook).
6. **Unsaved:** Parent passes `onDiscardChanges` / `onContinueEditing`; app owns popup and `pendingStatus`.

### 2.4 Unsaved-Changes Flow

1. User has form dirty → `isDirty` true (from store).
2. User clicks sidebar (status or Preferences) → `handleStatusClick(newStatus)`.
3. If `isDirty`: `setIsUnsavedPopupOpen(true)`, `setPendingStatus(newStatus)`; else `navigate(...)`.
4. User in popup:
   - **Continue Editing:** `setIsUnsavedPopupOpen(false)`.
   - **Discard:** `setIsDirty(false)`, `setIsUnsavedPopupOpen(false)`, then `navigate` to `pendingStatus` (or preferences), then `setPendingStatus(null)`.

So **pending destination** is kept in React state (`pendingStatus`); **dirty and popup visibility** are in the store.

### 2.5 Preferences

- **Preferences** uses local state (`buttonColor`, `isSaving`) and direct `apiFetch` to `/wpuf/v1/subscription-settings` (GET/POST).
- Notifications via `addNotice` from `wpuf/subscriptions-notice`. No subscription store usage.

### 2.6 Quick Edit

- **QuickEdit** is always mounted; visibility from `wpuf/subscriptions-quick-edit` (`isQuickEdit()`).
- Uses `wpuf/subscriptions` for `item`, `errors`, `isUpdating`, `updateError` and actions `setItem`, `updateItem`, etc.
- Opens with an item set from list; save goes through store’s `updateItem`.

### 2.7 Data Flow Summary

- **Router:** URL ↔ `core/router` ↔ `navigate()`; read in app and list.
- **Subscriptions:** API thunks in `wpuf/subscriptions` (fetchItems, fetchCounts, updateItem, …); components use hooks or direct `useSelect`/`useDispatch`.
- **Form:** Single subscription in store (`item`, `itemCopy`); form and quick edit both read/write it.
- **Counts:** Fetched in app (mount) and list (status change); displayed in header and sidebar.

---

## 3. Improvement Opportunities

### 3.1 Router Store Name

- **Current:** `core/router` (WordPress “core” namespace).
- **Risk:** Naming conflict if WordPress or another plugin introduces an official `core/router` store.
- **Improvement:** Use a plugin-scoped store (e.g. `wpuf/router`) and update all references.

### 3.2 Single Source of Truth for “Pending Navigation”

- **Current:** `pendingStatus` lives in `SubscriptionsApp` state while `isDirty` and `isUnsavedPopupOpen` live in `wpuf/subscriptions`.
- **Improvement:** Move pending navigation target into the subscription store (e.g. `pendingNavigation: { action, id, post_status, p }`) so unsaved-handling logic and tests can live in one place and the popup can be refactored to a dedicated component that reads from the store.

### 3.3 Unsaved Popup as a Component

- **Current:** Inline JSX and handlers in `subscriptions-react.jsx` (~25 lines).
- **Improvement:** Extract an `UnsavedChangesModal` (or reuse existing `UnsavedChanges.jsx` if it fits) with props or store-based API for “discard” / “continue editing”. Keeps the entry file focused on layout and routing.

### 3.4 API Layer

- **Current:** `apiFetch` used directly in SubscriptionForm (fetch one), SubscriptionBox (subscribers), Preferences (settings), and in store thunks (list, count, update, delete).
- **Improvement:** Introduce a small API module (e.g. `api/subscriptions.js`) with functions like `getSubscription(id)`, `getSubscriptions(query)`, `updateSubscription(id, data)`, `getSubscriptionSettings()`, `saveSubscriptionSettings(data)`. Reduces duplication and simplifies mocking/testing.

### 3.5 Preferences and Store Consistency

- **Current:** Preferences uses local state and its own `apiFetch`; no shared “settings” store.
- **Improvement:** If more settings or reuse elsewhere is likely, consider a small `wpuf/subscription-settings` store or at least a shared `getSettings`/`saveSettings` API used by a single component.

### 3.6 Error Handling in Form Fetch

- **Current:** SubscriptionForm uses local `error` state for fetch failure and displays it; success path uses store (`setItem`, `setItemCopy`).
- **Improvement:** Align with rest of app: either move fetch into a store thunk and use store errors, or consistently document “form-level fetch errors stay in component state” and ensure one place shows them (and optionally report to notice store).

### 3.7 Counts Fetch Duplication

- **Current:** `fetchCounts()` runs in SubscriptionsApp (useEffect on mount) and in SubscriptionList (same effect as fetchItems when status changes).
- **Improvement:** Rely on list’s fetch for count updates when status changes; optionally keep a single mount fetch in the app for initial load, or centralize “when to refetch counts” in one place (e.g. after any mutation or route change) to avoid subtle duplication.

### 3.8 Dependency Arrays and Stability

- **Current:** Some `useEffect`/`useCallback` depend on `fetchCounts` or dispatch references; generally stable but worth auditing.
- **Improvement:** Ensure all dispatch/selector references used in effects are stable (WordPress data guarantees) and add an ESLint rule for exhaustive deps to avoid future bugs.

### 3.9 Accessibility of Modals

- **Current:** Unsaved popup and Quick Edit are modal-like; focus trap and `aria-modal`/`role="dialog"` not verified in this review.
- **Improvement:** Ensure one component (or a shared modal wrapper) handles focus trap, Escape to close, and ARIA attributes for both the unsaved dialog and Quick Edit.

### 3.10 Bundle and Code Splitting

- **Current:** Single entry `subscriptions-react.jsx` pulls in all views and stores.
- **Improvement:** If bundle size grows, consider lazy-loading Preferences and/or SubscriptionForm (e.g. `React.lazy` for route segments) so list view stays as small as possible.

---

## 4. File and Dependency Map

| Path | Purpose |
|------|--------|
| `src/js/subscriptions-react.jsx` | Entry; app shell; route → view; unsaved popup. |
| `src/js/stores-react/router/index.js` | URL params state and `navigate`. |
| `src/js/stores-react/subscription/*` | Subscriptions state, API thunks, selectors. |
| `src/js/hooks/useSubscriptionNavigation.js` | `goToEdit`, `goToNew`, `goToList`, `goToPage`. |
| `src/js/hooks/useSubscriptionData.js` | useSelect wrapper for subscription store. |
| `src/js/hooks/useSubscriptionActions.js` | useDispatch wrapper for subscription store. |
| `src/js/components/subscriptions/SubscriptionForm.jsx` | Add/edit form; fetches one subscription in edit mode. |
| `src/js/components/subscriptions/SubscriptionList.jsx` | List; syncs URL ↔ store; fetches list + counts. |
| `src/js/components/subscriptions/Preferences.jsx` | Settings UI; own state + apiFetch. |
| `src/js/components/subscriptions/QuickEdit.jsx` | Modal; store-driven; uses subscription store item. |
| `src/js/components/subscriptions/SidebarMenu.jsx` | Status/preferences links; calls `onStatusClick`. |
| `src/js/components/subscriptions/ContentHeader.jsx` | Title + Add Subscription button. |
| `includes/Admin/views/subscriptions-react.php` | Mount point `#wpuf-subscription-page`. |
| `includes/Admin/Admin_Subscription.php` | Enqueues script and passes data (e.g. `wpufSubscriptions`). |

---

## 5. Summary

- **Architecture:** Single React app with URL-driven routing and WordPress data stores; entry file owns route derivation, layout, unsaved popup, and count fetch on mount.
- **Data flow:** URL (router store) → view selection; subscription store for list, current item, counts, dirty and popup; form and quick edit use same item; preferences and settings are separate (local state + apiFetch).
- **Improvements:** Use a plugin-scoped router store name; consider moving pending navigation into the store and extracting the unsaved popup; centralize API calls; clarify count refetch strategy; and improve modal accessibility and optional lazy loading for larger views.
