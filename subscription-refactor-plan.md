# Subscriptions React Refactor Plan

## Overview

Refactor the subscriptions React application to improve:
1. **Component composition** using `@wordpress/compose`
2. **URL-based navigation** using `@wordpress/router`
3. **Code organization** and maintainability

---

## Phase 1: @wordpress/compose Integration

### Goal
Replace manual `useSelect`/`useDispatch` patterns with compose HOCs and custom hooks for cleaner, more reusable code.

### 1.1 Add Dependency

**File:** `package.json`

```json
"@wordpress/compose": "^6.0.0"
```

### 1.2 Create Custom Hooks

**New file:** `src/js/hooks/useSubscriptionData.js`
```javascript
/**
 * DESCRIPTION: Custom hook for accessing subscription data from store
 * DESCRIPTION: Consolidates common useSelect patterns
 */
import { useSelect } from '@wordpress/data';

export const useSubscriptionData = () => {
    return useSelect((select) => {
        const store = select('wpuf/subscriptions');
        return {
            subscription: store.getItem(),
            isUpdating: store.isUpdating(),
            isDirty: store.isDirty(),
            isLoading: store.isLoading(),
            isUnsavedPopupOpen: store.isUnsavedPopupOpen(),
            currentStatus: store.getCurrentStatus(),
            counts: store.getCounts(),
            hasErrors: store.hasError(),
            errors: store.getErrors(),
        };
    }, []);
};
```

**New file:** `src/js/hooks/useSubscriptionActions.js`
```javascript
/**
 * DESCRIPTION: Custom hook for subscription actions
 * DESCRIPTION: Consolidates common useDispatch patterns
 */
import { useDispatch } from '@wordpress/data';

export const useSubscriptionActions = () => {
    const dispatch = useDispatch('wpuf/subscriptions');
    return {
        setItem: dispatch.setItem,
        setItemCopy: dispatch.setItemCopy,
        modifyItem: dispatch.modifyItem,
        updateItem: dispatch.updateItem,
        deleteItem: dispatch.deleteItem,
        fetchItems: dispatch.fetchItems,
        fetchCounts: dispatch.fetchCounts,
        setIsDirty: dispatch.setIsDirty,
        setIsUnsavedPopupOpen: dispatch.setIsUnsavedPopupOpen,
        setBlankItem: dispatch.setBlankItem,
        validateFields: dispatch.validateFields,
        setCurrentStatus: dispatch.setCurrentStatus,
    };
};
```

**New file:** `src/js/hooks/useSubscriptionListData.js`
```javascript
/**
 * DESCRIPTION: Custom hook for subscription list data
 * DESCRIPTION: Provides filtered list data for list view
 */
import { useSelect } from '@wordpress/data';

export const useSubscriptionListData = (status = 'all') => {
    return useSelect((select) => {
        const store = select('wpuf/subscriptions');
        return {
            subscriptions: store.getItems(),
            counts: store.getCounts(),
            isLoading: store.isLoading(),
            currentStatus: store.getCurrentStatus(),
        };
    }, [status]);
};
```

**New file:** `src/js/hooks/useRouterParams.js` (for Phase 2)
```javascript
/**
 * DESCRIPTION: Custom hook for accessing router query parameters
 * DESCRIPTION: Provides current URL params and navigation helpers
 */
import { useSelect } from '@wordpress/data';

export const useRouterParams = () => {
    return useSelect((select) => {
        const router = select('core/router');
        const params = router.getQueryParams();
        return {
            action: params.action || 'list',
            subscriptionId: params.id ? parseInt(params.id) : null,
            status: params.post_status || 'all',
            page: params.p ? parseInt(params.p) : 1,
        };
    }, []);
};
```

### 1.3 Create Container Components

**New file:** `src/js/components/subscriptions/containers/SubscriptionFormContainer.jsx`
```javascript
/**
 * DESCRIPTION: Container component for SubscriptionForm
 * DESCRIPTION: Uses compose to connect data and actions
 */
import { compose } from '@wordpress/compose';
import { withSelect, withDispatch } from '@wordpress/data';
import SubscriptionForm from '../SubscriptionForm';

const mapStateToProps = (select, { subscriptionId }) => {
    const store = select('wpuf/subscriptions');
    return {
        subscription: store.getItem(),
        isUpdating: store.isUpdating(),
        isDirty: store.isDirty(),
        isUnsavedPopupOpen: store.isUnsavedPopupOpen(),
        currentSubscriptionStatus: store.getCurrentStatus(),
        allCount: store.getCounts(),
    };
};

const mapDispatchToProps = (dispatch) => {
    const storeDispatch = dispatch('wpuf/subscriptions');
    return {
        setItem: storeDispatch.setItem,
        setItemCopy: storeDispatch.setItemCopy,
        modifyItem: storeDispatch.modifyItem,
        setIsDirty: storeDispatch.setIsDirty,
        setIsUnsavedPopupOpen: storeDispatch.setIsUnsavedPopupOpen,
        setBlankItem: storeDispatch.setBlankItem,
        updateItem: storeDispatch.updateItem,
        fetchCounts: storeDispatch.fetchCounts,
    };
};

export default compose(
    withSelect(mapStateToProps),
    withDispatch(mapDispatchToProps)
)(SubscriptionForm);
```

**New file:** `src/js/components/subscriptions/containers/SubscriptionListContainer.jsx`
```javascript
/**
 * DESCRIPTION: Container component for SubscriptionList
 * DESCRIPTION: Uses compose to connect list data and actions
 */
import { compose } from '@wordpress/compose';
import { withSelect, withDispatch } from '@wordpress/data';
import SubscriptionList from '../SubscriptionList';

const mapStateToProps = (select, { initialStatus }) => {
    const store = select('wpuf/subscriptions');
    return {
        subscriptions: store.getItems(),
        counts: store.getCounts(),
        isLoading: store.isLoading(),
        currentSubscriptionStatus: initialStatus || 'all',
    };
};

const mapDispatchToProps = (dispatch) => {
    const storeDispatch = dispatch('wpuf/subscriptions');
    return {
        fetchItems: storeDispatch.fetchItems,
        fetchCounts: storeDispatch.fetchCounts,
        setCurrentStatus: storeDispatch.setCurrentStatus,
        setItem: storeDispatch.setItem,
    };
};

export default compose(
    withSelect(mapStateToProps),
    withDispatch(mapDispatchToProps)
)(SubscriptionList);
```

### 1.4 Refactor Components to Use Hooks

**File:** `src/js/components/subscriptions/SubscriptionForm.jsx`

Replace manual `useSelect`/`useDispatch` with custom hooks:

```javascript
// BEFORE (lines 28-50):
const { subscription, isUpdating, isDirty, isUnsavedPopupOpen, currentSubscriptionStatus, allCount } = useSelect(
    ( select ) => ( {
        subscription: select( 'wpuf/subscriptions' ).getItem(),
        isUpdating: select( 'wpuf/subscriptions' ).isUpdating(),
        isDirty: select( 'wpuf/subscriptions' ).isDirty(),
        isUnsavedPopupOpen: select( 'wpuf/subscriptions' ).isUnsavedPopupOpen(),
        currentSubscriptionStatus: select( 'wpuf/subscriptions' ).getCurrentStatus(),
        allCount: select( 'wpuf/subscriptions' ).getCounts(),
    } ),
    []
);

const {
    setItem,
    setItemCopy,
    setIsDirty,
    setIsUnsavedPopupOpen,
    modifyItem,
    setBlankItem,
    updateItem: storeUpdateItem,
    fetchCounts,
} = useDispatch( 'wpuf/subscriptions' );

// AFTER:
import { useSubscriptionData, useSubscriptionActions } from '../../hooks';

const { subscription, isUpdating, isDirty, isUnsavedPopupOpen, currentSubscriptionStatus, allCount } = useSubscriptionData();
const { setItem, setItemCopy, setIsDirty, setIsUnsavedPopupOpen, modifyItem, setBlankItem, updateItem: storeUpdateItem, fetchCounts } = useSubscriptionActions();
```

**File:** `src/js/components/subscriptions/SubscriptionBox.jsx`

Replace manual store access with hooks:

```javascript
// BEFORE (lines 22-23):
const { getReadableBillingAmount, isRecurring } = useSelect( ( select ) => select( 'wpuf/subscriptions' ), [] );
const { setItem, updateItem, deleteItem } = useDispatch( 'wpuf/subscriptions' );

// AFTER:
import { useSubscriptionData, useSubscriptionActions } from '../../hooks';

const { getReadableBillingAmount, isRecurring } = useSubscriptionData();
const { setItem, updateItem, deleteItem } = useSubscriptionActions();
```

### 1.5 Extract SubscriptionList Component

**Current:** `SubscriptionList` is embedded in `subscriptions-react.jsx` (lines 37-190)

**Create:** `src/js/components/subscriptions/SubscriptionList.jsx`

Move the entire component to its own file, then import and use in the main entry point.

### 1.6 Extract LoadingSpinner Component

**Current:** `LoadingSpinner` is embedded in `subscriptions-react.jsx` (lines 28-34)

**Create:** `src/js/components/subscriptions/LoadingSpinner.jsx`

```javascript
/**
 * DESCRIPTION: Loading spinner component for async operations
 * DESCRIPTION: Displays centered spinning loader
 */
const LoadingSpinner = () => {
    return (
        <div className="wpuf-flex wpuf-h-svh wpuf-items-center wpuf-justify-center">
            <div className="wpuf-animate-spin wpuf-h-12 wpuf-w-12 wpuf-border-4 wpuf-border-green-500 wpuf-border-t-transparent wpuf-rounded-full"></div>
        </div>
    );
};

export default LoadingSpinner;
```

### 1.7 Clean Up Console.logs

Remove all debug console.log statements and replace with a proper logger:

**New file:** `src/js/utils/logger.js`
```javascript
/**
 * DESCRIPTION: Debug logging utility
 * DESCRIPTION: Only logs when debug mode is enabled
 */

// Check if debug mode is enabled via wp_localize_script
const DEBUG = window.wpufSubscriptions?.debug || false;

export const logger = {
    log: (...args) => {
        if (DEBUG) {
            console.log('[WPUF Subscriptions]', ...args);
        }
    },
    error: (...args) => {
        console.error('[WPUF Subscriptions]', ...args);
    },
    warn: (...args) => {
        if (DEBUG) {
            console.warn('[WPUF Subscriptions]', ...args);
        }
    },
    info: (...args) => {
        if (DEBUG) {
            console.info('[WPUF Subscriptions]', ...args);
        }
    },
};
```

Replace console.log calls in:
- `subscriptions-react.jsx` (~10 occurrences)
- `SubscriptionForm.jsx` (~5 occurrences)
- `SubscriptionBox.jsx` (~8 occurrences)

Example replacement:
```javascript
// BEFORE:
console.log('[SubscriptionList] Fetching data for status:', status);

// AFTER:
import { logger } from '../../utils/logger';
logger.log('Fetching data for status:', status);
```

---

## Phase 2: @wordpress/router Integration

### Goal
Replace custom view state with URL-based routing using `@wordpress/router`.

### 2.1 Add Dependencies

**File:** `package.json`

```json
"@wordpress/router": "^7.0.0"
```

### 2.2 Register Router Store

**New file:** `src/js/stores-react/router/index.js`
```javascript
/**
 * DESCRIPTION: Register WordPress router store
 * DESCRIPTION: Enables URL-based navigation for the app
 */
import { registerStore } from '@wordpress/data';
import { store as routerStore } from '@wordpress/router';

// Register the router store with WordPress data
registerStore('core/router', routerStore);

export default routerStore;
```

### 2.3 Update Entry Point

**File:** `src/js/subscriptions-react.jsx`

**Key changes:**
1. Import router store
2. Remove `viewState` management
3. Use router selectors for current params
4. Route-based component rendering

```javascript
// Add import
import './stores-react/router';

// In SubscriptionsApp component:
const SubscriptionsApp = () => {
    // Get current route from router store
    const { params } = useSelect((select) => ({
        params: select('core/router').getQueryParams(),
    }), []);

    // Determine view based on URL params
    const action = params.action || 'list';
    const subscriptionId = params.id ? parseInt(params.id) : null;

    // Render based on action
    if (action === 'edit' || action === 'new') {
        return <SubscriptionForm
            mode={action === 'new' ? 'add-new' : 'edit'}
            subscriptionId={subscriptionId}
        />;
    }

    return <SubscriptionList />;
};
```

### 2.4 Create Navigation Hook

**New file:** `src/js/hooks/useSubscriptionNavigation.js`
```javascript
/**
 * DESCRIPTION: Navigation hook for subscriptions
 * DESCRIPTION: Provides URL-based navigation methods
 */
import { useCallback } from '@wordpress/element';
import { useDispatch } from '@wordpress/data';

export const useSubscriptionNavigation = () => {
    const { navigate } = useDispatch('core/router');

    const goToEdit = useCallback((id) => {
        navigate({ action: 'edit', id: String(id) });
    }, [navigate]);

    const goToNew = useCallback(() => {
        navigate({ action: 'new' });
    }, [navigate]);

    const goToList = useCallback((status = null) => {
        const params = {};
        // Clear action param to return to list view
        if (status && status !== 'all') {
            params.post_status = status;
        }
        navigate(params);
    }, [navigate]);

    const goToPage = useCallback((page, status = null) => {
        const params = { p: String(page) };
        if (status && status !== 'all') {
            params.post_status = status;
        }
        navigate(params);
    }, [navigate]);

    return { goToEdit, goToNew, goToList, goToPage };
};
```

### 2.5 Update Components for Navigation

**File:** `src/js/components/subscriptions/SubscriptionList.jsx`

```javascript
import { useSubscriptionNavigation } from '../../hooks/useSubscriptionNavigation';

const SubscriptionList = () => {
    const { goToEdit, goToNew, goToPage, goToList } = useSubscriptionNavigation();

    // Get current params from URL
    const { params } = useSelect((select) => ({
        params: select('core/router').getQueryParams(),
    }), []);

    const currentStatus = params.post_status || 'all';
    const currentPage = params.p ? parseInt(params.p) : 1;

    // Use navigation methods instead of callbacks
    const handleAddSubscription = () => goToNew();
    const handleEdit = (id) => goToEdit(id);
    const handlePageChange = (page) => goToPage(page, currentStatus);
    const handleStatusChange = (status) => goToList(status);
};
```

**File:** `src/js/components/subscriptions/SubscriptionForm.jsx`

```javascript
import { useSubscriptionNavigation } from '../../hooks/useSubscriptionNavigation';

const SubscriptionForm = ({ mode, subscriptionId }) => {
    const { goToList } = useSubscriptionNavigation();

    // Get status from URL for sidebar display
    const { params } = useSelect((select) => ({
        params: select('core/router').getQueryParams(),
    }), []);

    const currentSubscriptionStatus = params.post_status || 'all';

    // Navigate after save
    const handlePublish = async () => {
        // ... save logic
        if (result?.success) {
            goToList(currentSubscriptionStatus);
        }
    };
};
```

### 2.6 Update Sidebar Menu

**File:** `src/js/components/subscriptions/SidebarMenu.jsx`

Remove `onCheckIsDirty` callback prop - use navigation hook internally:

```javascript
import { useSubscriptionNavigation } from '../../hooks/useSubscriptionNavigation';

const SidebarMenu = ({ currentSubscriptionStatus, allCount }) => {
    const { goToList } = useSubscriptionNavigation();

    // Direct navigation - dirty checking handled by router guard if needed
    const handleStatusClick = (status) => {
        goToList(status);
    };

    return (
        // ... rest of component
        <li onClick={() => handleStatusClick(item.key)}>
            // ...
        </li>
    );
};
```

### 2.7 Implement Resolvers (Auto-fetch on Route Change)

**File:** `src/js/stores-react/subscription/resolvers.js`

Currently empty - implement resolvers to auto-fetch:

```javascript
import { fetchItems, fetchCounts } from './actions';

/**
 * Resolver: Auto-fetch subscriptions when getItems is called
 */
export function getItems(state, status, offset) {
    return fetchItems(status || 'all', offset || 0);
}

/**
 * Resolver: Auto-fetch counts when getCounts is called
 */
export function getCounts(state, status) {
    return fetchCounts(status || 'all');
}
```

This means when components call `store.getItems()` or `store.getCounts()`, the data is automatically fetched if not already present.

### 2.8 Add Router Guard for Dirty State

**New file:** `src/js/utils/routerGuard.js`
```javascript
/**
 * DESCRIPTION: Router guard for unsaved changes protection
 * DESCRIPTION: Prevents navigation away when form has unsaved changes
 */
import { useEffect } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { useDispatch } from '@wordpress/data';

export const useDirtyStateGuard = () => {
    const isDirty = useSelect((select) => select('wpuf/subscriptions').isDirty());
    const { setIsUnsavedPopupOpen } = useDispatch('wpuf/subscriptions');
    const { navigate } = useDispatch('core/router');

    useEffect(() => {
        const handleRouteChange = () => {
            if (isDirty) {
                setIsUnsavedPopupOpen(true);
                return true; // Prevent navigation
            }
            return false; // Allow navigation
        };

        // Register route change handler
        // Note: WordPress router may need custom implementation for this
        // This is a placeholder for the concept
    }, [isDirty, setIsUnsavedPopupOpen]);
};
```

---

## URL Structure

### Current State
```jsx
// Custom state - no URL sync
viewState = {
    view: 'list',
    mode: 'edit',
    subscriptionId: 123,
    status: 'draft'
}
```

### New URL Pattern
```
admin.php?page=wpuf_subscriptions_react
admin.php?page=wpuf_subscriptions_react&action=new
admin.php?page=wpuf_subscriptions_react&action=edit&id=123
admin.php?page=wpuf_subscriptions_react&post_status=draft
admin.php?page=wpuf_subscriptions_react&post_status=draft&p=2
```

### Route Mappings
| URL | View | Params |
|-----|------|--------|
| `page=wpuf_subscriptions_react` | List | status=all, page=1 |
| `page=...&action=new` | Form (Add) | mode=add-new |
| `page=...&action=edit&id=123` | Form (Edit) | mode=edit, id=123 |
| `page=...&post_status=draft` | List (filtered) | status=draft |
| `page=...&post_status=draft&p=2` | List (paged) | status=draft, page=2 |

---

## Files Summary

### Files to Modify
| File | Phase | Changes |
|------|-------|---------|
| `package.json` | 1, 2 | Add @wordpress/compose, @wordpress/router |
| `src/js/subscriptions-react.jsx` | 1, 2 | Remove viewState, extract components, add router |
| `src/js/components/subscriptions/SubscriptionForm.jsx` | 1, 2 | Use hooks, navigation, remove callback props |
| `src/js/components/subscriptions/SubscriptionBox.jsx` | 1 | Use hooks, remove console.logs |
| `src/js/components/subscriptions/SidebarMenu.jsx` | 2 | Use navigation hook |
| `src/js/stores-react/subscription/resolvers.js` | 2 | Implement auto-fetch resolvers |

### New Files to Create
| File | Phase | Purpose |
|------|-------|---------|
| `src/js/hooks/useSubscriptionData.js` | 1 | Common data selector hook |
| `src/js/hooks/useSubscriptionActions.js` | 1 | Common action dispatchers |
| `src/js/hooks/useSubscriptionListData.js` | 1 | List-specific data hook |
| `src/js/hooks/useRouterParams.js` | 2 | Router params access |
| `src/js/hooks/useSubscriptionNavigation.js` | 2 | Navigation actions |
| `src/js/components/subscriptions/containers/SubscriptionFormContainer.jsx` | 1 | Compose HOC for form |
| `src/js/components/subscriptions/containers/SubscriptionListContainer.jsx` | 1 | Compose HOC for list |
| `src/js/components/subscriptions/SubscriptionList.jsx` | 1 | Extracted list component |
| `src/js/components/subscriptions/LoadingSpinner.jsx` | 1 | Extracted spinner |
| `src/js/stores-react/router/index.js` | 2 | Register router store |
| `src/js/utils/logger.js` | 1 | Debug logging utility |
| `src/js/utils/routerGuard.js` | 2 | Dirty state protection |

---

## Testing Plan

### Phase 1 Testing (@wordpress/compose)

1. **Custom Hooks**
   - Verify `useSubscriptionData()` returns all expected values
   - Verify `useSubscriptionActions()` dispatches work correctly
   - Test hook updates trigger re-renders

2. **Container Components**
   - Test SubscriptionFormContainer connects props correctly
   - Test SubscriptionListContainer connects props correctly
   - Verify store updates propagate to components

3. **Extracted Components**
   - SubscriptionList renders independently
   - LoadingSpinner renders correctly

### Phase 2 Testing (@wordpress/router)

1. **List View**
   - Load `admin.php?page=wpuf_subscriptions_react`
   - Verify subscriptions load
   - Click sidebar filters → verify URL updates to `?post_status=draft`
   - Test pagination → verify URL updates to `?p=2`

2. **Add New Subscription**
   - Click "Add Subscription" → verify `?action=new`
   - Save as draft → verify redirect to `?post_status=draft`
   - Publish → verify redirect to `?post_status=publish`

3. **Edit Subscription**
   - Click card → verify `?action=edit&id=123`
   - Make changes and save → verify redirect to list
   - Test cancel with no changes → navigates back
   - Test cancel with changes → shows unsaved modal

4. **URL Navigation**
   - Direct URL: `?action=edit&id=123` → loads edit form
   - Direct URL: `?post_status=trash` → shows trash filter
   - Test browser back/forward buttons
   - Test page refresh (state preserved)

5. **Dirty State Protection**
   - Edit, make changes, click sidebar filter → shows modal
   - Discard changes → navigation occurs
   - Continue editing → modal closes

---

## Implementation Order

1. ✅ **Phase 1.1-1.3**: Add compose, create hooks and containers
2. ✅ **Phase 1.4-1.6**: Refactor components, extract components
3. ✅ **Phase 1.7**: Clean up console.logs
4. ✅ **Test Phase 1**: Verify compose integration works
5. ✅ **Phase 2.1-2.4**: Add router, create navigation
6. ✅ **Phase 2.5-2.7**: Update components for navigation
7. ✅ **Phase 2.8**: Add dirty state guard
8. ✅ **Test Phase 2**: Verify router integration works
9. ✅ **Integration Test**: Full end-to-end testing

---

## Progress Tracking

- [ ] Phase 1.1: Add @wordpress/compose dependency
- [ ] Phase 1.2: Create custom hooks
- [ ] Phase 1.3: Create container components
- [ ] Phase 1.4: Refactor SubscriptionForm to use hooks
- [ ] Phase 1.5: Refactor SubscriptionBox to use hooks
- [ ] Phase 1.6: Extract SubscriptionList component
- [ ] Phase 1.7: Extract LoadingSpinner component
- [ ] Phase 1.8: Clean up console.logs
- [ ] Phase 1 Testing: Verify all changes work
- [ ] Phase 2.1: Add @wordpress/router dependency
- [ ] Phase 2.2: Register router store
- [ ] Phase 2.3: Update main entry point
- [ ] Phase 2.4: Create navigation hook
- [ ] Phase 2.5: Update SubscriptionList for navigation
- [ ] Phase 2.6: Update SubscriptionForm for navigation
- [ ] Phase 2.7: Update SidebarMenu for navigation
- [ ] Phase 2.8: Implement resolvers
- [ ] Phase 2.9: Add dirty state guard
- [ ] Phase 2 Testing: Verify router integration
- [ ] Final Integration Testing
