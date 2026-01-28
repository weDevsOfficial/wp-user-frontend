# Subscriptions React App Architecture

## Overview

The Subscriptions React application is a Single Page Application (SPA) built with WordPress React components that provides a modern interface for managing subscription plans. It uses WordPress's `@wordpress/data` package (Redux-based state management) for handling application state.

## Entry Point

**File:** [src/js/subscriptions-react.jsx](src/js/subscriptions-react.jsx)

The app is initialized at the DOM element `#wpuf-subscription-page` using React 18's `createRoot`.

## Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                           subscriptions-react.jsx                               │
│                              (Entry Point)                                      │
│                              ┌──────────────┐                                   │
│                              │ Subscriptions│                                   │
│                              │     App      │                                   │
│                              └──────┬───────┘                                   │
│                                     │                                           │
│                    ┌────────────────┴────────────────┐                          │
│                    │      View State Management      │                          │
│                    │  view: 'list' | 'form'          │                          │
│                    │  mode: 'add-new' | 'edit'       │                          │
│                    │  subscriptionId: id | null      │                          │
│                    │  status: 'all' | 'publish' ...  │                          │
│                    └────────────────┬────────────────┘                          │
│                                     │                                           │
│           ┌─────────────────────────┴─────────────────────────┐                │
│           │                                                     │                │
│   ┌───────▼────────┐                                  ┌────────▼────────┐       │
│   │ SubscriptionList│                                  │ SubscriptionForm │       │
│   │    (View)       │                                  │    (View)        │       │
│   └───────┬────────┘                                  └────────┬────────┘       │
│           │                                                     │                │
└───────────┼─────────────────────────────────────────────────────┼────────────────┘
            │                                                     │
┌───────────┼─────────────────────────────────────────────────────┼────────────────┐
│           │              LIST VIEW                              │                │
│  ┌────────▼────────┐  ┌───────────────┐  ┌──────────────────┐ │                │
│  │   ContentHeader │  │  SidebarMenu  │  │  SubscriptionBox │ │                │
│  │                 │  │ (Status Nav)  │  │   (Card Grid)    │ │                │
│  │ - Title         │  │               │  │                  │ │                │
│  │ - Add Button    │  │ - All         │  │ - Title          │ │                │
│  └─────────────────┘  │ - Published   │  │ - Amount         │ │                │
│                       │ - Drafts      │  │ - Status         │ │                │
│                       │ - Trash       │  │ - Quick Menu     │ │                │
│                       │ - Preferences │  │ - Actions        │ │                │
│                       └───────────────┘  │ (Edit/Trash)     │ │                │
│  ┌─────────────────────────────────┐    └──────────────────┘ │                │
│  │          Pagination             │                          │                │
│  └─────────────────────────────────┘                          │                │
└────────────────────────────────────────────────────────────────┘                │
           │                                                     │
┌───────────┼─────────────────────────────────────────────────────┼────────────────┐
│           │              FORM VIEW                              │                │
│  ┌────────▼────────┐  ┌───────────────┐  ┌──────────────────┐ │                │
│  │   ContentHeader │  │  SidebarMenu  │  │SubscriptionDetails│ │                │
│  │                 │  │ (Status Nav)  │  │   (Tabs UI)      │ │                │
│  │ - Title         │  │               │  │                  │ │                │
│  │ - No Add Button │  │ - All         │  │ - Tab Navigation │ │                │
│  └─────────────────┘  │ - Published   │  │ - Subsections    │ │                │
│                       │ - Drafts      │  │ - Fields         │ │                │
│  ┌────────────────┐   │ - Trash       │  └──────────────────┘ │                │
│  │  UpdateButton  │   │ - Preferences │                          │                │
│  │                │   └───────────────┘  ┌──────────────────┐ │                │
│  │ - Publish      │                          │SubscriptionSub   │ │                │
│  │ - Save Draft   │                          │   section        │ │                │
│  └────────────────┘                          │ (Field Groups)   │ │                │
│  ┌────────────────┐                          └──────────────────┘ │                │
│  │ UnsavedChanges │                                                │                │
│  │   (Modal)      │                                                │                │
│  └────────────────┘                                                │                │
└────────────────────────────────────────────────────────────────────┘                │
                                                                             │
┌─────────────────────────────────────────────────────────────────────────────┤
│                        STATE MANAGEMENT (@wordpress/data)                    │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ┌───────────────────────────────────────────────────────────────────────┐ │
│  │                   wpuf/subscriptions (Main Store)                     │ │
│  │  ───────────────────────────────────────────────────────────────────  │ │
│  │  State: items, item, itemCopy, counts, errors, isLoading,             │ │
│  │         isUpdating, isDirty, isUnsavedPopupOpen, currentStatus        │ │
│  │                                                                        │ │
│  │  Actions: fetchItems, fetchCounts, updateItem, deleteItem,            │ │
│  │           setItem, modifyItem, setBlankItem, setIsDirty,              │ │
│  │           validateFields, populateTaxonomyRestrictionData             │ │
│  │                                                                        │ │
│  │  Selectors: getItems, getItem, getCounts, getFields, isRecurring,     │ │
│  │             getReadableBillingAmount, hasError, getMetaValue          │ │
│  └───────────────────────────────────────────────────────────────────────┘ │
│                                                                             │
│  ┌──────────────────────┐  ┌──────────────────────┐  ┌──────────────────┐ │
│  │wpuf/subscriptions-   │  │ wpuf/subscriptions-   │  │ wpuf/subscrip-   │ │
│  │  fieldDependency     │  │   notice              │  │   tion-component  │ │
│  │                      │  │                      │  │                  │ │
│  │ - Dependent fields   │  │ - Notices/Alerts     │  │ - Component state │ │
│  │   management         │  │                      │  │                  │ │
│  └──────────────────────┘  └──────────────────────┘  └──────────────────┘ │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
                                                                             │
┌─────────────────────────────────────────────────────────────────────────────┤
│                           DATA FLOW                                         │
└─────────────────────────────────────────────────────────────────────────────┘
                                                                             │
    User Action ──► Component Event Handler ──► Store Action ──► API Call     │
                                                                   │          │
                                                                   ▼          │
    WordPress REST API (/wpuf/v1/wpuf_subscription) ──► Response ──► Reducer   │
                                                                   │          │
                                                                   ▼          │
    State Update ──► Component Re-render                                    │
                                                                             │
┌─────────────────────────────────────────────────────────────────────────────┤
│                           API ENDPOINTS                                     │
└─────────────────────────────────────────────────────────────────────────────┘
                                                                             │
    GET    /wpuf/v1/wpuf_subscription           - List subscriptions          │
    GET    /wpuf/v1/wpuf_subscription/{id}      - Get single subscription     │
    POST   /wpuf/v1/wpuf_subscription           - Create/update subscription  │
    DELETE /wpuf/v1/wpuf_subscription/{id}      - Delete subscription         │
    GET    /wpuf/v1/wpuf_subscription/count     - Get subscription counts     │
    GET    /wpuf/v1/wpuf_subscription/subscribers - Get subscribers count     │
                                                                             │
┌─────────────────────────────────────────────────────────────────────────────┤
│                    COMPONENT FILE STRUCTURE                                 │
└─────────────────────────────────────────────────────────────────────────────┘
                                                                             │
    src/js/                                                                  │
    ├── subscriptions-react.jsx          # Entry point, view routing         │
    ├── components/                                                        │
    │   ├── Header.jsx                   # Global app header                │
    │   └── subscriptions/                                                 │
    │       ├── ContentHeader.jsx         # Page header with add button      │
    │       ├── SidebarMenu.jsx          # Status filter navigation         │
    │       ├── SubscriptionForm.jsx     # Form view container              │
    │       ├── SubscriptionBox.jsx      # List view card component         │
    │       ├── SubscriptionDetails.jsx  # Tabbed details interface         │
    │       ├── SubscriptionSubsection.jsx # Field grouping component       │
    │       ├── ListHeader.jsx           # List view header                 │
    │       ├── Pagination.jsx           # Pagination controls              │
    │       ├── Empty.jsx                # Empty state display              │
    │       ├── UpdateButton.jsx         # Publish/Save draft buttons       │
    │       └── UnsavedChanges.jsx       # Unsaved changes modal            │
    └── stores-react/                                                       │
        └── subscription/                                                    │
            ├── index.js                     # Store registration            │
            ├── actions.js                   # Action creators               │
            ├── selectors.js                 # State selectors               │
            ├── resolvers.js                 # Data resolvers                │
            ├── reducer.js                   # Reducer function              │
            └── constants.js                 # Action type constants         │
```

## Key Concepts

### View Routing

The app uses a **view state** pattern instead of traditional routing. The `SubscriptionsApp` component manages which view to display:

- **`view: 'list'`** - Shows the subscription list with filtering and pagination
- **`view: 'form'`** - Shows the subscription creation/editing form

### Navigation Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                    NAVIGATION FLOW                              │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  List View ──► Add Button ──► Form (mode: add-new)             │
│       │                                                         │
│       ├───► Click Card ──► Form (mode: edit)                   │
│       │                                                         │
│       ├───► Sidebar Filter ──► List (filtered by status)       │
│       │                                                         │
│       └───► Pagination ──► List (offset change)                │
│                                                                 │
│  Form View ──► Publish/Draft ──► List (status: publish/draft)   │
│       │                                                         │
│       ├───► Cancel (clean) ──► List                            │
│       │                                                         │
│       ├───► Cancel (dirty) ──► Unsaved Modal ──► List          │
│       │                                                         │
│       └───► Sidebar Click ──► List (with status filter)        │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### Dirty State Management

The form implements unsaved changes detection:

1. **`itemCopy`** - Stores the original subscription data
2. **`isDirty`** - Tracks if any changes were made
3. **`isUnsavedPopupOpen`** - Controls the unsaved changes modal
4. **Comparison** - On navigation, if `isDirty === true`, the modal appears

### Data Flow Patterns

**Loading Data (List View):**
```
useEffect ──► fetchItems(status) ──► API ──► setItems ──► State ──► Render
```

**Editing a Subscription:**
```
Click Edit ──► setViewState({ view: 'form', mode: 'edit', id }) ──►
SubscriptionForm mounts ──► apiFetch(/wpuf/v1/wpuf_subscription/{id}) ──►
setItem ──► setItemCopy ──► Form renders with data
```

**Saving Changes:**
```
Click Publish ──► modifyItem('post_status', 'publish') ──►
storeUpdateItem() ──► API POST ──► setIsDirty(false) ──►
onNavigateToList() ──► View switches to list
```

### Store Architecture

**wpuf/subscriptions Store State:**

| Property | Type | Description |
|----------|------|-------------|
| `items` | Array | List of subscriptions for current view |
| `item` | Object/null | Current subscription being edited |
| `itemCopy` | Object/null | Original subscription (for dirty check) |
| `counts` | Object | Subscription counts by status |
| `errors` | Object | Form validation errors |
| `isLoading` | Boolean | Loading state for list operations |
| `isUpdating` | Boolean | Loading state for CRUD operations |
| `isDirty` | Boolean | Whether form has unsaved changes |
| `isUnsavedPopupOpen` | Boolean | Modal visibility state |
| `currentStatus` | String | Current status filter ('all', 'publish', etc.) |

### Component Props Flow

**List View Prop Chain:**
```
SubscriptionsApp
└── SubscriptionList
    ├── ContentHeader (currentSubscriptionStatus, allCount, onAddSubscription)
    ├── SidebarMenu (currentSubscriptionStatus, allCount, onCheckIsDirty)
    └── SubscriptionBox[]
        └── onEdit callback ──► handleViewChange({ view: 'form', mode: 'edit', subscriptionId })
```

**Form View Prop Chain:**
```
SubscriptionsApp
└── SubscriptionForm
    ├── ContentHeader (currentSubscriptionStatus, allCount)
    ├── SidebarMenu (currentSubscriptionStatus, allCount, onCheckIsDirty)
    ├── SubscriptionDetails (subscription, onFieldChange, currentTab, onTabChange)
    │   └── SubscriptionSubsection[] (fields, subscription, onFieldChange)
    ├── UpdateButton (isUpdating, onPublish, onSaveDraft)
    └── UnsavedChanges (onDiscard, onContinue)
```

## File Locations Reference

| Component/Module | File Path |
|------------------|-----------|
| Entry Point | [subscriptions-react.jsx](src/js/subscriptions-react.jsx) |
| List View | [subscriptions-react.jsx:37-190](src/js/subscriptions-react.jsx#L37) |
| Form View | [SubscriptionForm.jsx](src/js/components/subscriptions/SubscriptionForm.jsx) |
| Subscription Card | [SubscriptionBox.jsx](src/js/components/subscriptions/SubscriptionBox.jsx) |
| Sidebar Navigation | [SidebarMenu.jsx](src/js/components/subscriptions/SidebarMenu.jsx) |
| Tabbed Details | [SubscriptionDetails.jsx](src/js/components/subscriptions/SubscriptionDetails.jsx) |
| Main Store | [stores-react/subscription/](src/js/stores-react/subscription/) |
| Actions | [stores-react/subscription/actions.js](src/js/stores-react/subscription/actions.js) |
| Selectors | [stores-react/subscription/selectors.js](src/js/stores-react/subscription/selectors.js) |
| Resolvers | [stores-react/subscription/resolvers.js](src/js/stores-react/subscription/resolvers.js) |

## Global Data (PHP to JS)

The app expects `window.wpufSubscriptions` to be populated by PHP with:

- `perPage` - Items per page for pagination
- `currencySymbol` - Currency symbol for display
- `siteUrl` - WordPress site URL
- `sections` - Tab sections configuration
- `subSections` - Subsection definitions per tab
- `fields` - Field definitions organized by section/subsection
- `dependentFields` - Field dependency rules
