<script setup>
import {__} from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import {toRefs, ref, onBeforeMount, computed} from 'vue';
import {addQueryArgs} from '@wordpress/url';
import Edit from './Edit.vue';

import {useComponentStore} from '../../stores/component';
import {useQuickEditStore} from '../../stores/quickEdit';
import {useSubscriptionStore} from '../../stores/subscription';
import {useNoticeStore} from '../../stores/notice';
import Popup from './Popup.vue';

const props = defineProps( {
    subscription: Object
} );

const {subscription} = toRefs( props );
const showPopup = ref( false );
const showBox = ref( true );
const pillColor = ref( '' );
const isRecurring = ref( false );
const quickMenuStatus = ref( false );
const billingAmount = ref( 0 );
const subscribers = ref( 0 );
const subscribersLink = wpufSubscriptions.siteUrl + '/wp-admin/edit.php?post_type=wpuf_subscription&page=wpuf_subscribers&post_ID=' + subscription.value.ID;
const componentStore = useComponentStore();
const quickEditStore = useQuickEditStore();
const subscriptionStore = useSubscriptionStore();
const noticeStore = useNoticeStore();
const currentSubscription = subscriptionStore.currentSubscription;

const setPillBackground = () => {
    const postStatus = subscription.value.post_status;

    if (postStatus === 'publish') {
        pillColor.value = 'wpuf-text-green-700 wpuf-bg-green-50 ring-green-600/20';
    } if (postStatus === 'private') {
        pillColor.value = 'wpuf-text-orange-700 wpuf-bg-orange-50 wpuf-ring-orange-600/10';
    } else if (postStatus === 'draft') {
        pillColor.value = 'wpuf-text-yellow-700 wpuf-bg-yellow-50 wpuf-ring-yellow-600/10';
    } else if (postStatus === 'pending') {
        pillColor.value = 'wpuf-text-slate-700 wpuf-bg-slate-50 wpuf-ring-slate-600/10';
    } else if (postStatus === 'trash') {
        pillColor.value = 'wpuf-text-red-700 wpuf-bg-red-50 wpuf-ring-red-600/10';
    } else {
        pillColor.value = 'wpuf-text-green-700 wpuf-bg-green-50 ring-green-600/20';
    }
};

const showQuickMenu = () => {
    quickMenuStatus.value = true;
};

const hideQuickMenu = () => {
    quickMenuStatus.value = false;
};

const vClickOutside = {
    beforeMount: ( el ) => {
        el.clickOutsideEvent = ( event ) => {
            if ( ! el.contains( event.target ) ) {
                quickMenuStatus.value = false;
            }
        };
        document.body.addEventListener( 'click', el.clickOutsideEvent )
    },

    unmounted: ( el ) => {
        document.body.removeEventListener( 'click', el.clickOutsideEvent )
    }
}

const getSubscribers = () => {
    const queryParams = { 'subscription_id': subscription.value.ID };

    apiFetch(
        {
            path: addQueryArgs( '/wp-json/wpuf/v1/wpuf_subscription/subscribers', queryParams ),
            method: 'GET',
            headers: {
                'X-WP-Nonce': wpufSubscriptions.nonce,
            },
        }
    )
    .then( ( response ) => {
        subscribers.value = response.subscribers;
        subscription.value.subscribers = subscribers.value;
    } )
    .catch( ( error ) => {
        console.log( error );
    } );
};

const setBillingAmount = () => {
    if (parseFloat( subscription.value.meta_value.billing_amount ) === 0 || subscription.value.meta_value.billing_amount === '') {
        billingAmount.value = __( 'Free', 'wp-user-frontend' );
    } else {
        billingAmount.value = wpufSubscriptions.currencySymbol + subscription.value.meta_value.billing_amount;
    }
};

onBeforeMount( () => {
    setPillBackground();
    setBillingAmount();

    if ( subscription.value.meta_value.recurring_pay === 'yes' ) {
        isRecurring.value = true;
        billingAmount.value += '/' + subscription.value.meta_value.cycle_period[0].toUpperCase();
    }

    getSubscribers();
} );

const title = computed(() => {
    return currentSubscription ? currentSubscription.post_title : subscription.value.post_title;
});

const toggleSubscriptionStatus = ( subscription ) => {
    const promiseResult = subscriptionStore.toggleDraft(subscription);

    promiseResult.then((result) => {
        if (result.success) {
            noticeStore.display = true;
            noticeStore.type = 'success';
            noticeStore.message = result.message;

            subscription.post_status = subscription.post_status === 'publish' ? 'draft' : 'publish';
            setPillBackground();
        } else {
            noticeStore.display = true;
            noticeStore.type = 'danger';
            noticeStore.message = result.message;
        }

        setTimeout(() => {
            noticeStore.display = false;
            noticeStore.type = '';
            noticeStore.message = '';
        }, 3000);
    });
};

const deleteSubscription = () => {
    const promiseResult = subscriptionStore.deleteSubscription( subscription.value.ID );

    promiseResult.then((result) => {
        if (result.success) {
            noticeStore.display = true;
            noticeStore.type = 'success';
            noticeStore.message = result.message;
            showPopup.value = false;
            showBox.value = false;
        } else {
            noticeStore.display = true;
            noticeStore.type = 'danger';
            noticeStore.message = result.message;
        }

        setTimeout(() => {
            noticeStore.display = false;
            noticeStore.type = '';
            noticeStore.message = '';
        }, 3000);
    });
};

const postStatus = computed(() => {
    const firstLetter = subscription.value.post_status.charAt(0);

    const firstLetterCap = firstLetter.toUpperCase();

    const remainingLetters = subscription.value.post_status.slice(1);

    return firstLetterCap + remainingLetters;
});

</script>
<template>
    <div v-if="showBox" class="wpuf-text-base wpuf-justify-between wpuf-max-w-sm wpuf-bg-white wpuf-border wpuf-border-gray-200 wpuf-rounded-xl wpuf-shadow">
        <div class="wpuf-flex wpuf-justify-between wpuf-border-b border-gray-900/5 wpuf-bg-gray-50 wpuf-p-4 wpuf-rounded-t-xl">
            <div>
                <div class="wpuf-block wpuf-py-1 wpuf-text-gray-900 wpuf-m-0 wpuf-font-medium" :title="'id: ' + subscription.ID">{{ title }}</div>
                <p class="wpuf-text-gray-500 wpuf-text-base wpuf-m-0">{{ billingAmount }}</p>
            </div>
            <div class="wpuf-flex wpuf-justify-between wpuf-flex-col wpuf-relative">
                <svg
                    @click="showQuickMenu"
                    v-click-outside="hideQuickMenu"
                    class="wpuf-h-5 wpuf-w-5 hover:wpuf-cursor-pointer"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                    aria-hidden="true"><path d="M3 10a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0zM8.5 10a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0zM15.5 8.5a1.5 1.5 0 100 3 1.5 1.5 0 000-3z"></path></svg>
                <div
                    v-if="quickMenuStatus"
                    class="wpuf-w-max wpuf--left-20 wpuf-absolute wpuf-rounded-xl wpuf-bg-white wpuf-shadow-lg wpuf-ring-1 wpuf-ring-gray-900/5 wpuf-overflow-hidden">
                    <ul>
                        <li @click="componentStore.setCurrentComponent( 'Edit' ); subscriptionStore.setCurrentSubscription(subscription)" class="wpuf-px-4 wpuf-py-2 wpuf-mb-0 hover:wpuf-bg-gray-100 hover:wpuf-cursor-pointer">{{ __( 'Edit', 'wp-user-frontend' ) }}</li>
                        <li @click="quickEditStore.setQuickEditStatus(true); subscriptionStore.setCurrentSubscription(subscription)" class="wpuf-px-4 wpuf-py-2 wpuf-mb-0 hover:wpuf-bg-gray-100 hover:wpuf-cursor-pointer">{{ __( 'Quick Edit', 'wp-user-frontend' ) }}</li>
                        <li @click="toggleSubscriptionStatus(subscription)" class="wpuf-px-4 wpuf-py-2 wpuf-mb-0 hover:wpuf-bg-gray-100 hover:wpuf-cursor-pointer">{{ __( 'Draft/Publish', 'wp-user-frontend' ) }}</li>
                        <li @click="showPopup = true" class="wpuf-px-4 wpuf-py-2 wpuf-mb-0 hover:wpuf-bg-gray-100 hover:wpuf-cursor-pointer">{{ __( 'Delete', 'wp-user-frontend' ) }}</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="wpuf-flex wpuf-px-4 wpuf-py-4 wpuf-justify-between wpuf-items-center">
            <div :class="pillColor"
                 class="wpuf-text-sm wpuf-w-fit wpuf-px-2.5 wpuf-py-1 wpuf-shadow-sm wpuf-bg-gray-100 wpuf-rounded-md wpuf-ring-1">
                {{ postStatus }}
            </div>
            <div v-if="isRecurring" class="dashicons dashicons-controls-repeat"></div>
        </div>
        <div class="wpuf-flex wpuf-px-4 wpuf-py-4 wpuf-justify-between wpuf-items-center">
            <p class="wpuf-text-gray-500 wpuf-text-base wpuf-m-0">{{ __( 'Total Subscribers' ) }}</p>
            <a :href="subscribersLink" class="wpuf-text-gray-500">{{ subscribers }}</a>
        </div>
    </div>
    <Popup @hide-popup="showPopup = false" @delete="deleteSubscription" v-if="showPopup" />
</template>
