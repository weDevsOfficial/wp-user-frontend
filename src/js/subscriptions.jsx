/**
 * DESCRIPTION: Entry point for Subscriptions React app
 * DESCRIPTION: Renders the subscription management interface with URL-based navigation
 */
import { createRoot } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { useState, useCallback, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { SlotFillProvider } from '@wordpress/components';
import { doAction } from '@wordpress/hooks';
import SubscriptionForm from './components/subscriptions/SubscriptionForm';
import SubscriptionList from './components/subscriptions/SubscriptionList';
import SidebarMenu from './components/subscriptions/SidebarMenu';
import Header from './components/Header';
import ContentHeader from './components/subscriptions/ContentHeader';
import QuickEdit from './components/subscriptions/QuickEdit';
import UnsavedChanges from './components/subscriptions/UnsavedChanges';
import Preferences from './components/subscriptions/Preferences';

// Import stores to register them
import './stores-react/subscription';
import './stores-react/fieldDependency';
import './stores-react/notice';
import './stores-react/component';
import './stores-react/quickEdit';
import './stores-react/router';

// Import styles
import '../css/subscriptions.css';

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
    const handleAddSubscription = useCallback(() => {
        navigate({ action: 'new', id: null, post_status: null, p: null });
    }, [navigate]);

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
        if (pendingStatus) {
            navigate({ action: null, id: null, post_status: pendingStatus === 'all' ? null : pendingStatus, p: null });
            setPendingStatus(null);
        }
    }, [pendingStatus, navigate, setIsDirty, setIsUnsavedPopupOpen]);

    // Handle continue editing from unsaved popup
    const handleContinueEditing = useCallback(() => {
        setIsUnsavedPopupOpen(false);
    }, [setIsUnsavedPopupOpen]);

    return (
        <>
            <Header utm="wpuf-subscriptions" />
            <ContentHeader
                currentSubscriptionStatus={status}
                allCount={allCount}
                onAddSubscription={action !== 'edit' && action !== 'new' ? handleAddSubscription : null}
            />
            <div className={`wpuf-flex wpuf-pt-[40px] wpuf-px-[20px] ${isUnsavedPopupOpen ? 'wpuf-blur' : ''}`}>
                {/* Left Sidebar */}
                <div className="wpuf-basis-1/5 wpuf-border-r-2 wpuf-border-gray-200">
                    <SidebarMenu
                        currentSubscriptionStatus={status}
                        allCount={allCount}
                        onStatusClick={handleStatusClick}
                        isUnsavedPopupOpen={isUnsavedPopupOpen}
                    />
                </div>

                {/* Main Content */}
                <div className="wpuf-basis-4/5">
                    {action === 'edit' || action === 'new' ? (
                        <SubscriptionForm
                            mode={action === 'new' ? 'add-new' : 'edit'}
                            subscriptionId={subscriptionId}
                            onDiscardChanges={handleDiscardChanges}
                            onContinueEditing={handleContinueEditing}
                        />
                    ) : status === 'preferences' ? (
                        <Preferences />
                    ) : (
                        <SubscriptionList />
                    )}
                </div>
            </div>

            {/* Unsaved changes popup */}
            {isUnsavedPopupOpen && (
                <UnsavedChanges
                    onDiscard={handleDiscardChanges}
                    onContinue={handleContinueEditing}
                />
            )}

            {/* Quick Edit modal */}
            <QuickEdit />
        </>
    );
};

const container = document.getElementById('wpuf-subscription-page');

if (container) {
    const root = createRoot(container);
    root.render(
        <SlotFillProvider>
            <SubscriptionsApp />
        </SlotFillProvider>
    );
}
