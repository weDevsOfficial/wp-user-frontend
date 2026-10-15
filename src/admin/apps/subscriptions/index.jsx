/**
 * DESCRIPTION: Entry point for Subscriptions React app
 * DESCRIPTION: Renders the subscription management interface with URL-based navigation
 */
import { createRoot } from '@wordpress/element';
import { useSelect, useDispatch, dispatch as dataDispatch } from '@wordpress/data';
import { useState, useCallback, useEffect, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { SlotFillProvider } from '@wordpress/components';
import { doAction } from '@wordpress/hooks';
import { WpufProviders, PageHeader, PageShell, UnsavedGuard } from '@wpuf/components';
import SubscriptionForm from './components/subscriptions/SubscriptionForm';
import SubscriptionList from './components/subscriptions/SubscriptionList';
import SidebarMenu from './components/subscriptions/SidebarMenu';
import ContentHeader from './components/subscriptions/ContentHeader';
import QuickEdit from './components/subscriptions/QuickEdit';
import Preferences from './components/subscriptions/Preferences';
import Notices from './components/subscriptions/Notices';
import Subscribers from './components/subscriptions/Subscribers';

// Import stores to register them
import './stores/subscription';
import './stores/fieldDependency';
import './stores/notice';
import './stores/component';
import './stores/quickEdit';
import './stores/router';
import { addRouteGuard, getRouteQuery, onRouteQuery, registerScreen } from '../../app/client';

// Import styles

// Signal to Pro and third-party plugins that stores are registered and ready
doAction( 'wpuf.subscription.init' );

const SubscriptionsApp = () => {
    // Get current route from router store
    const { params } = useSelect((select) => {
        const router = select('wpuf/subscriptions-router');
        return {
            params: router.getQueryParams(),
        };
    }, []);

    // Get subscription data from store
    const { allCount, isDirty, isUnsavedPopupOpen } = useSelect((select) => {
        const store = select('wpuf/subscriptions');
        return {
            allCount: store.getCounts(),
            isDirty: store.isDirty(),
            isUnsavedPopupOpen: store.isUnsavedPopupOpen(),
        };
    }, []);

    const { navigate } = useDispatch('wpuf/subscriptions-router');
    const { setIsUnsavedPopupOpen, setIsDirty } = useDispatch('wpuf/subscriptions');

    const [pendingStatus, setPendingStatus] = useState(null);
    // A route change of the admin app waiting for the unsaved popup's answer.
    const pendingLeave = useRef(null);
    const dirtyRef = useRef(isDirty);
    dirtyRef.current = isDirty;

    useEffect(
        () => addRouteGuard(() => {
            if (!dirtyRef.current) {
                return true;
            }

            return new Promise((resolve) => {
                pendingLeave.current = resolve;
                setIsUnsavedPopupOpen(true);
            });
        }),
        [setIsUnsavedPopupOpen]
    );

    // Determine view based on URL params
    const action = params.action || 'list';
    const subscriptionId = params.id ? parseInt(params.id, 10) : null;
    const status = params.post_status || 'all';

    // Get actions from store for fetching counts
    const { fetchCounts } = useDispatch('wpuf/subscriptions');

    // Fetch counts on mount
    useEffect(() => {
        fetchCounts();
    }, [fetchCounts]);

    // Handle add subscription click
    // develop shows "Add Subscription" on the form views too; leaving a dirty
    // form asks first (develop did not).
    const handleAddSubscription = useCallback(() => {
        if (isDirty) {
            setPendingStatus('__new');
            setIsUnsavedPopupOpen(true);
            return;
        }
        navigate({ action: 'new', id: null, post_status: null, p: null });
    }, [isDirty, navigate, setIsUnsavedPopupOpen]);

    // Handle sidebar status click
    const handleStatusClick = useCallback((newStatus) => {
        if (isDirty) {
            setIsUnsavedPopupOpen(true);
            setPendingStatus(newStatus);
        } else {
            navigate({ action: null, id: null, post_status: newStatus === 'all' ? null : newStatus, p: null });
        }
    }, [isDirty, navigate, setIsUnsavedPopupOpen]);

    // Handle discard changes from unsaved popup
    const handleDiscardChanges = useCallback(() => {
        setIsDirty(false);
        setIsUnsavedPopupOpen(false);
        if (pendingLeave.current) {
            pendingLeave.current(true);
            pendingLeave.current = null;
            return;
        }
        // A sidebar click goes to that status; the form's Cancel goes back to
        // the list it came from (develop goToList).
        if ('__new' === pendingStatus) {
            navigate({ action: 'new', id: null, post_status: null, p: null });
            setPendingStatus(null);
            return;
        }
        const target = pendingStatus || status;
        navigate({ action: null, id: null, post_status: target === 'all' ? null : target, p: null });
        setPendingStatus(null);
    }, [pendingStatus, status, navigate, setIsDirty, setIsUnsavedPopupOpen]);

    // Handle continue editing from unsaved popup
    const handleContinueEditing = useCallback(() => {
        setIsUnsavedPopupOpen(false);
        if (pendingLeave.current) {
            pendingLeave.current(false);
            pendingLeave.current = null;
        }
        // Staying: forget where the cancelled navigation was going.
        setPendingStatus(null);
    }, [setIsUnsavedPopupOpen]);

    return (
        <PageShell>
            <PageHeader
                utm="wpuf-subscription"
                helpUrl="https://wedevs.com/docs/wp-user-frontend-pro/subscription-payment/?utm_source=wpuf-footer-help&utm_medium=text-link&utm_campaign=learn-more-subscription"
                helpLabel={ __( 'Learn more about Subscription', 'wp-user-frontend' ) }
            />
            <ContentHeader
                currentSubscriptionStatus={status}
                allCount={allCount}
                onAddSubscription={handleAddSubscription}
            />
            <div className={`flex items-start gap-6 pt-6 ${isUnsavedPopupOpen ? 'blur-sm' : ''}`}>
                {/* Left Sidebar */}
                <div className="w-60 shrink-0">
                    <SidebarMenu
                        currentSubscriptionStatus={status}
                        allCount={allCount}
                        onStatusClick={handleStatusClick}
                        isUnsavedPopupOpen={isUnsavedPopupOpen}
                    />
                </div>

                {/* Main Content */}
                <div className="min-w-0 flex-1">
                    {action === 'edit' || action === 'new' ? (
                        <SubscriptionForm
                            mode={action === 'new' ? 'add-new' : 'edit'}
                            subscriptionId={subscriptionId}
                            onDiscardChanges={handleDiscardChanges}
                            onContinueEditing={handleContinueEditing}
                        />
                    ) : action === 'subscribers' && subscriptionId ? (
                        <Subscribers subscriptionId={subscriptionId} />
                    ) : status === 'preferences' ? (
                        <Preferences />
                    ) : (
                        <SubscriptionList />
                    )}
                </div>
            </div>

            {/* Unsaved changes: the dialog on sidebar/cancel, the browser prompt on unload */}
            <UnsavedGuard
                dirty={isDirty}
                open={isUnsavedPopupOpen}
                onDiscard={handleDiscardChanges}
                onContinue={handleContinueEditing}
            />

            {/* Quick Edit modal */}
            <QuickEdit />

            {/* Quick edit / preferences notices go to the shared toasts (develop's Notice list position) */}
            <Notices />

        </PageShell>
    );
};

// On its own page it mounts into #wpuf-subscription-page; in the admin app the
// shell mounts it on the subscriptions route (task 5d).
registerScreen('subscriptions', ['wpuf-subscription-page'], (container) => {
    // The route's query is the source of truth on every mount, and back /
    // forward inside the route follow it.
    dataDispatch('wpuf/subscriptions-router').setUrlParams(getRouteQuery());
    const stopFollowing = onRouteQuery((query) => dataDispatch('wpuf/subscriptions-router').setUrlParams(query));
    const root = createRoot(container);

    root.render(
        // host: the form views are still legacy markup until 4.1b (design.md D25).
        <WpufProviders host>
            <SlotFillProvider>
                <SubscriptionsApp />
            </SlotFillProvider>
        </WpufProviders>
    );

    return () => {
        stopFollowing();
        root.unmount();
    };
});
