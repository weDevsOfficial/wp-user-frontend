<script setup>
import {__} from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import {toRefs, ref, onBeforeMount} from 'vue';
import {addQueryArgs} from '@wordpress/url';
import Edit from './Edit.vue';
import {useComponentStore} from '../../stores/component';
import {useQuickEditStore} from '../../stores/quickEdit';
import {useSubscriptionStore} from '../../stores/subscription';

const props = defineProps( {
    subscription: Object
} );

const {subscription} = toRefs( props );
const pillColor = ref( '' );
const isRecurring = ref( false );
const quickMenuStatus = ref( false );
const billingAmount = ref( 0 );
const subscribers = ref( 0 );
const subscribersLink = wpufSubscriptions.siteUrl + '/wp-admin/edit.php?post_type=wpuf_subscription&page=wpuf_subscribers&post_ID=' + subscription.value.ID;
const componentStore = useComponentStore();
const quickEditStore = useQuickEditStore();
const subscriptionStore = useSubscriptionStore();

const setPillBackground = () => {
    const postStatus = subscription.value.post_status;

    if (postStatus === 'publish') {
        pillColor.value = 'wpuf-text-green-700';
    } else if (postStatus === 'draft') {
        pillColor.value = 'wpuf-text-yellow-700';
    } else if (postStatus === 'pending') {
        pillColor.value = 'wpuf-text-slate-700';
    } else if (postStatus === 'trash') {
        pillColor.value = 'wpuf-text-red-700';
    } else {
        pillColor.value = 'wpuf-text-green-700';
    }
};

const showQuickMenu = () => {
    quickMenuStatus.value = true;
};

const hideQuickMenu = () => {
    quickMenuStatus.value = false;
};

const vClickOutside = {
    beforeMount: ( el, binding, vnode ) => {
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

    // todo: add nonce and other validations.
    apiFetch( {path: addQueryArgs( wpufSubscriptions.siteUrl + '/wp-json/wpuf/v1/wpuf_subscription/subscribers', queryParams )} )
        .then( ( response ) => {
            subscribers.value = response.subscribers;
        } ).catch( ( error ) => {
        console.log( error );
    } )
};

const setBillingAmount = () => {
    if (parseFloat( subscription.value.meta_value.billing_amount ) === 0) {
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

</script>
<template>
    <div class="wpuf-justify-between wpuf-max-w-sm wpuf-bg-white wpuf-border wpuf-border-gray-200 wpuf-shadow dark:wpuf-bg-gray-800 dark:wpuf-border-gray-700 dark:hover:wpuf-bg-gray-700">
        <div class="wpuf-flex wpuf-justify-between wpuf-p-4 wpuf-bg-gray-100">
            <div>
                <h5 class="wpuf-mb-1 wpuf-m-0 wpuf-text-2xl wpuf-font-bold wpuf-tracking-tight wpuf-text-gray-900 dark:wpuf-text-white">
                    {{ subscription.post_title }}</h5>
                <p class="wpuf-mt-1 wpuf-mb-1 wpuf-truncate wpuf-text-lg wpuf-text-gray-500">{{ billingAmount }}</p>
            </div>
            <div class="wpuf-flex wpuf-justify-between wpuf-flex-col wpuf-relative">
                <svg @click="showQuickMenu" v-click-outside="hideQuickMenu" class="wpuf-relative hover:wpuf-cursor-pointer" width="2em" height="2em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z"/>
                </svg>
                <div
                    v-if="quickMenuStatus"
                    class="wpuf-w-max wpuf--left-20 wpuf-absolute wpuf-rounded-xl wpuf-bg-white wpuf-text-sm wpuf-shadow-lg wpuf-ring-1 wpuf-ring-gray-900/5">
                    <ul>
                        <li @click="componentStore.setCurrentComponent( 'Edit' ); subscriptionStore.setCurrentSubscription(subscription)" class="wpuf-px-4 wpuf-py-2 wpuf-mb-0 hover:wpuf-bg-gray-100 hover:wpuf-cursor-pointer">{{ __( 'Edit', 'wp-user-frontend' ) }}</li>
                        <li @click="quickEditStore.setQuickEditStatus(true); subscriptionStore.setCurrentSubscription(subscription)" class="wpuf-px-4 wpuf-py-2 wpuf-mb-0 hover:wpuf-bg-gray-100 hover:wpuf-cursor-pointer">{{ __( 'Quick Edit', 'wp-user-frontend' ) }}</li>
                        <li class="wpuf-px-4 wpuf-py-2 wpuf-mb-0 hover:wpuf-bg-gray-100 hover:wpuf-cursor-pointer">{{ __( 'Draft/Publish', 'wp-user-frontend' ) }}</li>
                        <li class="wpuf-px-4 wpuf-py-2 wpuf-mb-0 hover:wpuf-bg-gray-100 hover:wpuf-cursor-pointer">{{ __( 'Delete', 'wp-user-frontend' ) }}</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="wpuf-flex wpuf-px-4 wpuf-py-2 wpuf-justify-between wpuf-items-center">
            <div :class="pillColor"
                 class="wpuf-w-fit wpuf-px-2.5 wpuf-py-1 wpuf-shadow-sm wpuf-bg-gray-100">
                {{ subscription.post_status }}
            </div>
            <div v-if="isRecurring" class="dashicons dashicons-controls-repeat"></div>
        </div>
        <div class="wpuf-flex wpuf-px-4 wpuf-py-2 wpuf-justify-between wpuf-items-center">
            <p class="wpuf-text-lg wpuf-text-gray-500">{{ __( 'Total Subscribers' ) }}</p>
            <a :href="subscribersLink" class="wpuf-text-lg wpuf-text-gray-500">{{ subscribers }}</a>
        </div>
    </div>
</template>
