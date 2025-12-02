import { useState, useEffect, useCallback, useMemo } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

// Stores
import '../stores-react/subscription';
import '../stores-react/component';
import '../stores-react/notice';
import '../stores-react/quickEdit';

// Components
import Header from './Header';
import SidebarMenu from './subscriptions/SidebarMenu';
import List from './subscriptions/List';
import Empty from './subscriptions/Empty';
import Edit from './subscriptions/Edit';
import New from './subscriptions/New';
import Preferences from './subscriptions/Preferences';
import QuickEdit from './subscriptions/QuickEdit';
import Notice from './subscriptions/Notice';
import Unsaved from './subscriptions/Unsaved';
import ContentHeader from './subscriptions/ContentHeader';

export default function Subscriptions() {
    // Local state
    const [tempSubscriptionStatus, setTempSubscriptionStatus] = useState('all');
    const [tempNavigationTarget, setTempNavigationTarget] = useState(null);

    // Store selectors
    const {
        currentComponent,
    } = useSelect((select) => ({
        currentComponent: select('wpuf/subscriptions-component').getCurrentComponent(),
    }), []);

    const {
        isSubscriptionLoading,
        isDirty,
        isUnsavedPopupOpen,
        currentSubscriptionStatus,
        subscriptionList,
    } = useSelect((select) => {
        const store = select('wpuf/subscriptions');
        return {
            isSubscriptionLoading: store.isLoading(),
            isDirty: store.isDirty(),
            isUnsavedPopupOpen: store.isUnsavedPopupOpen(),
            currentSubscriptionStatus: store.getCurrentStatus(),
            subscriptionList: store.getItems(),
        };
    }, []);

    const {
        isQuickEdit,
    } = useSelect((select) => ({
        isQuickEdit: select('wpuf/subscriptions-quick-edit').isQuickEdit(),
    }), []);

    const {
        notices,
        isNoticeDisplaying,
    } = useSelect((select) => {
        const store = select('wpuf/subscriptions-notice');
        return {
            notices: store.getNotices(),
            isNoticeDisplaying: store.isDisplaying(),
        };
    }, []);

    // Store dispatchers
    const {
        setCurrentComponent,
    } = useDispatch('wpuf/subscriptions-component');

    const {
        setSubscriptionsByStatus,
        getSubscriptionCount,
        setIsUnsavedPopupOpen,
        setIsDirty,
        setCurrentStatus,
        setItem,
        setCurrentPage,
        resetErrors,
        setErrors,
    } = useDispatch('wpuf/subscriptions');

    const {
        setQuickEditStatus,
    } = useDispatch('wpuf/subscriptions-quick-edit');

    const {
        removeNotice,
    } = useDispatch('wpuf/subscriptions-notice');

    // Initial data fetch
    useEffect(() => {
        setSubscriptionsByStatus(currentSubscriptionStatus).then((result) => {
            if (result && result.subscriptions && result.subscriptions.length > 0) {
                setCurrentComponent('List');
            } else {
                setCurrentComponent('Empty');
            }
        });
        getSubscriptionCount();
    }, []);

    // Reset errors when component changes
    useEffect(() => {
        resetErrors();
    }, [currentComponent]);

    // Navigation logic
    const goToList = useCallback(() => {
        setIsDirty(false);
        setIsUnsavedPopupOpen(false);

        if (tempNavigationTarget === 'preferences') {
            setCurrentComponent('Preferences');
            setCurrentStatus('preferences');
            setTempNavigationTarget(null);
        } else {
            setSubscriptionsByStatus(tempSubscriptionStatus);
            setCurrentComponent('List');
            setItem(null);
            setCurrentPage(1);
        }
    }, [tempNavigationTarget, tempSubscriptionStatus]);

    const checkIsDirty = useCallback((subscriptionStatus = 'all') => {
        if (subscriptionStatus === 'preferences') {
            if (isDirty) {
                setIsUnsavedPopupOpen(true);
                setTempNavigationTarget('preferences');
                setTempSubscriptionStatus(currentSubscriptionStatus || 'all');
            } else {
                setCurrentComponent('Preferences');
                setCurrentStatus('preferences');
            }
        } else if (isDirty) {
            setIsUnsavedPopupOpen(true);
            setTempSubscriptionStatus(subscriptionStatus);
            setTempNavigationTarget(null);
        } else {
            setIsDirty(false);
            setIsUnsavedPopupOpen(false);

            setSubscriptionsByStatus(subscriptionStatus);
            setCurrentComponent('List');
            setItem(null);
            getSubscriptionCount();
            setCurrentPage(1);
        }
    }, [isDirty, currentSubscriptionStatus]);

    // Render component based on state
    const CurrentView = useMemo(() => {
        switch (currentComponent) {
            case 'List':
                return List;
            case 'Edit':
                return Edit;
            case 'New':
                return New;
            case 'Preferences':
                return Preferences;
            case 'Empty':
                return Empty;
            default:
                return null;
        }
    }, [currentComponent]);

    return (
        <div>
            <Header />

            {(isSubscriptionLoading || !CurrentView) && (
                <div className="wpuf-flex wpuf-h-svh wpuf-items-center wpuf-justify-center">
                    <div className="wpuf-inline-block wpuf-h-8 wpuf-w-8 wpuf-animate-spin wpuf-rounded-full wpuf-border-4 wpuf-border-solid wpuf-border-primary wpuf-border-r-transparent" role="status">
                        <span className="wpuf-sr-only">{__('Loading...', 'wp-user-frontend')}</span>
                    </div>
                </div>
            )}

            {isQuickEdit && (
                <div
                    className="wpuf-absolute wpuf-w-full wpuf-h-screen wpuf-z-10 wpuf-left-[-20px]"
                    onClick={() => {
                        setQuickEditStatus(false);
                        setErrors({});
                    }}
                />
            )}

            {isQuickEdit && <QuickEdit />}

            <ContentHeader />

            {!isSubscriptionLoading && (
                <div className={`wpuf-flex wpuf-pt-[40px] wpuf-pr-[20px] wpuf-pl-[20px] ${isQuickEdit ? 'wpuf-blur' : ''}`}>
                    <div className="wpuf-basis-1/5 wpuf-border-r-2 wpuf-border-gray-200">
                        <SidebarMenu onCheckIsDirty={checkIsDirty} />
                    </div>
                    <div className="wpuf-basis-4/5">
                        {CurrentView && (
                            <CurrentView
                                onGoToList={goToList}
                                onCheckIsDirty={checkIsDirty}
                            />
                        )}
                    </div>
                    {isUnsavedPopupOpen && (
                        <Unsaved
                            onClose={() => setIsUnsavedPopupOpen(false)}
                            onGoToList={goToList}
                        />
                    )}
                </div>
            )}

            <div className="wpuf-fixed wpuf-top-20 wpuf-right-8 wpuf-z-10">
                {isNoticeDisplaying && notices.map((notice, index) => (
                    <Notice
                        key={`notice-${index}`}
                        index={index}
                        type={notice.type}
                        message={notice.message}
                        onRemove={() => removeNotice(index)}
                    />
                ))}
            </div>
        </div>
    );
}
