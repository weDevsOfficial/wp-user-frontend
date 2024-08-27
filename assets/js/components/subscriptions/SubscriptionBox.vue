<script setup>
import {__} from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import {toRefs, ref, onBeforeMount, computed, onMounted} from 'vue';
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
const emit = defineEmits( ['toggleSubscriptionStatus'] );
const showPopup = ref( false );
const showBox = ref( true );
const pillColor = ref( '' );
const quickMenuStatus = ref( false );
const billingAmount = ref( 0 );
const subscribers = ref( 0 );
const subscribersLink = wpufSubscriptions.siteUrl + '/wp-admin/edit.php?post_type=wpuf_subscription&page=wpuf_subscribers&post_ID=' + subscription.value.ID;
const componentStore = useComponentStore();
const quickEditStore = useQuickEditStore();
const subscriptionStore = useSubscriptionStore();
const noticeStore = useNoticeStore();

const setPillBackground = () => {
    const postStatus = subscription.value.post_status;

    if (postStatus === 'publish') {
        pillColor.value = 'wpuf-text-green-700 wpuf-bg-green-50 ring-green-600/20';
    } else if (postStatus === 'private') {
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
            if (!el.contains( event.target )) {
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
    const queryParams = {'subscription_id': subscription.value.ID};

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
    if (isRecurring.value) {
        const cyclePeriod = subscription.value.meta_value.cycle_period === '' ? __( 'day', 'wp-user-frontend' ) : subscription.value.meta_value.cycle_period;
        const expireAfter = (parseInt( subscription.value.meta_value._billing_cycle_number ) === 0 || parseInt( subscription.value.meta_value._billing_cycle_number ) === 1) ? '' : ' ' + subscription.value.meta_value._billing_cycle_number + ' ';
        billingAmount.value = wpufSubscriptions.currencySymbol + subscription.value.meta_value.billing_amount + ' per ' + expireAfter + ' ' + cyclePeriod + '(s)';
    } else {
        if (parseInt( subscription.value.meta_value.billing_amount ) === 0 || subscription.value.meta_value.billing_amount === '') {
            billingAmount.value = __( 'Free', 'wp-user-frontend' );
        } else {
            billingAmount.value = wpufSubscriptions.currencySymbol + subscription.value.meta_value.billing_amount;
        }
    }
};

const isRecurring = computed(() => {
    return subscription.value.meta_value.recurring_pay === 'on' || subscription.value.meta_value.recurring_pay === 'yes';
});

onBeforeMount( () => {
    setPillBackground();
    setBillingAmount();

    getSubscribers();
} );

const title = computed( () => {
    return subscription.value.post_title;
} );

const trashSubscription = ( subscription ) => {
    subscription.edit_row_name = 'post_status';
    subscription.edit_row_value = 'trash';

    const promiseResult = subscriptionStore.changeSubscriptionStatus( subscription );

    processPromiseResult( promiseResult );
};

const restoreSubscription = ( subscription ) => {
    subscription.edit_row_name = 'post_status';
    subscription.edit_row_value = 'draft';

    const promiseResult = subscriptionStore.changeSubscriptionStatus( subscription );

    processPromiseResult( promiseResult );
};

const deleteSubscription = ( subscription ) => {
    const promiseResult = subscriptionStore.deleteSubscription( subscription.ID );

    processPromiseResult( promiseResult );
};

const toggleSubscriptionStatus = ( subscription ) => {
    subscription.edit_row_name = 'post_status';
    subscription.edit_row_value = subscription.post_status === 'draft' ? 'publish' : 'draft';

    subscriptionStore.isSubscriptionLoading = true;

    const promiseResult = subscriptionStore.changeSubscriptionStatus( subscription );

    processPromiseResult( promiseResult );
};

const processPromiseResult = ( promiseResult ) => {
    promiseResult.then( ( result ) => {
        if (result.success) {
            noticeStore.display = true;
            noticeStore.type = 'success';
            noticeStore.message = result.message;
            showPopup.value = false;
            showBox.value = false;

            subscriptionStore.isDirty = false;
            subscriptionStore.isUnsavedPopupOpen = false;

            subscriptionStore.setCurrentSubscription(null);
            subscriptionStore.setSubscriptionsByStatus( subscriptionStore.currentSubscriptionStatus );
            subscriptionStore.getSubscriptionCount();
        } else {
            noticeStore.display = true;
            noticeStore.type = 'danger';
            noticeStore.message = result.message;
        }

        setTimeout( () => {
            noticeStore.display = false;
            noticeStore.type = '';
            noticeStore.message = '';
        }, 3000 );
    } );
};

const postStatus = computed( () => {
    let postStatus = subscription.value.post_status;
    if (subscription.value.post_status === 'publish') {
        postStatus = 'Published';

        return postStatus;
    }
    const firstLetter = postStatus.charAt( 0 );

    const firstLetterCap = firstLetter.toUpperCase();

    const remainingLetters = postStatus.slice( 1 );

    return firstLetterCap + remainingLetters;
} );

const isPasswordProtected = computed( () => {
    return subscription.value.post_password !== '';
} );

</script>
<template>
    <div v-if="showBox"
         class="wpuf-text-base wpuf-justify-between wpuf-bg-white wpuf-border wpuf-border-gray-200 wpuf-rounded-xl wpuf-shadow wpuf-relative">
        <div
            @click="subscription.post_status !== 'trash' ? [ componentStore.setCurrentComponent( 'Edit' ), subscriptionStore.setCurrentSubscription(subscription) ] : ''"
            :class="subscription.post_status !== 'trash' ? 'wpuf-cursor-pointer' : ''"
            class="wpuf-flex wpuf-justify-between wpuf-border-b border-gray-900/5 wpuf-bg-gray-50 wpuf-p-6 wpuf-rounded-t-xl">
            <div>
                <div class="wpuf-flex wpuf-py-1 wpuf-text-gray-900 wpuf-m-0 wpuf-font-medium"
                     :title="'id: ' + subscription.ID">
                    {{ title }} &nbsp;
                    <span v-if="isPasswordProtected">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                  d="M5.99999 10.8V8.4C5.99999 5.08629 8.68628 2.4 12 2.4C15.3137 2.4 18 5.08629 18 8.4V10.8C19.3255 10.8 20.4 11.8745 20.4 13.2V19.2C20.4 20.5255 19.3255 21.6 18 21.6H5.99999C4.67451 21.6 3.59999 20.5255 3.59999 19.2V13.2C3.59999 11.8745 4.67451 10.8 5.99999 10.8ZM15.6 8.4V10.8H8.39999V8.4C8.39999 6.41178 10.0118 4.8 12 4.8C13.9882 4.8 15.6 6.41178 15.6 8.4Z"
                                  fill="#a0aec0"/>
                        </svg>
                    </span>
                </div>
              <p class="wpuf-text-gray-500 wpuf-text-base wpuf-m-0">
                {{ subscriptionStore.getReadableBillingAmount( subscription ) }}
              </p>
            </div>
        </div>
        <div class="wpuf-absolute wpuf-right-6 wpuf-p-[10px] wpuf-rounded-full hover:wpuf-bg-gray-100 wpuf-top-4 wpuf-right-4">
            <svg
                @click="showQuickMenu"
                v-click-outside="hideQuickMenu"
                class="wpuf-h-5 wpuf-w-5 hover:wpuf-cursor-pointer"
                viewBox="0 0 20 20"
                fill="currentColor"
                aria-hidden="true">
                <path
                    d="M3 10a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0zM8.5 10a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0zM15.5 8.5a1.5 1.5 0 100 3 1.5 1.5 0 000-3z"></path>
            </svg>
            <div
                v-if="quickMenuStatus"
                class="wpuf-w-max wpuf--left-20 wpuf-absolute wpuf-rounded-xl wpuf-bg-white wpuf-shadow-lg wpuf-ring-1 wpuf-ring-gray-900/5 wpuf-overflow-hidden wpuf-z-10">
                <ul v-if="subscription.post_status !== 'trash'">
                    <li @click="componentStore.setCurrentComponent( 'Edit' ); subscriptionStore.setCurrentSubscription(subscription)"
                        class="wpuf-px-4 wpuf-py-2 wpuf-mb-0 hover:wpuf-bg-gray-100 hover:wpuf-cursor-pointer">
                        {{ __( 'Edit', 'wp-user-frontend' ) }}
                    </li>
                    <li @click="quickEditStore.isQuickEdit = true; subscriptionStore.setCurrentSubscription(subscription); subscriptionStore.currentSubscriptionCopy = subscription"
                        class="wpuf-px-4 wpuf-py-2 wpuf-mb-0 hover:wpuf-bg-gray-100 hover:wpuf-cursor-pointer">
                        {{ __( 'Quick Edit', 'wp-user-frontend' ) }}
                    </li>
                    <li @click="toggleSubscriptionStatus(subscription)"
                        class="wpuf-px-4 wpuf-py-2 wpuf-mb-0 hover:wpuf-bg-gray-100 hover:wpuf-cursor-pointer">
                        {{ subscription.post_status === 'publish' ? __( 'Draft', 'wp-user-frontend' ) : __( 'Publish', 'wp-user-frontend' ) }}
                    </li>
                    <li @click="showPopup = true"
                        class="wpuf-px-4 wpuf-py-2 wpuf-mb-0 hover:wpuf-bg-gray-100 hover:wpuf-cursor-pointer">
                        {{ __( 'Trash', 'wp-user-frontend' ) }}
                    </li>
                </ul>
                <ul v-if="subscription.post_status === 'trash'">
                    <li @click="restoreSubscription(subscription); componentStore.setCurrentComponent( 'List' )"
                        class="wpuf-px-4 wpuf-py-2 wpuf-mb-0 hover:wpuf-bg-gray-100 hover:wpuf-cursor-pointer">
                        {{ __( 'Restore', 'wp-user-frontend' ) }}
                    </li>
                    <li @click="showPopup = true"
                        class="wpuf-px-4 wpuf-py-2 wpuf-mb-0 hover:wpuf-bg-gray-100 hover:wpuf-cursor-pointer">
                        {{ __( 'Delete Permanently', 'wp-user-frontend' ) }}
                    </li>
                </ul>
            </div>
        </div>
        <div class="wpuf-flex wpuf-px-6 wpuf-py-6 wpuf-justify-between wpuf-items-center">
            <div :class="pillColor"
                 class="wpuf-text-sm wpuf-w-fit wpuf-px-2.5 wpuf-py-1 wpuf-shadow-sm wpuf-bg-gray-100 wpuf-rounded-md wpuf-ring-1">
                {{ postStatus }}
            </div>
            <svg v-if="isRecurring" width="24" height="24" viewBox="0 0 24 24" fill="none"
                 xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M20 19C20 19.5523 20.4477 20 21 20C21.5523 20 22 19.5523 22 19L20 19ZM21 15.375L22 15.375L22 14.375H21V15.375ZM12 21L12 22L12 21ZM4.06195 13.0013C3.99361 12.4532 3.49394 12.0644 2.9459 12.1327C2.39786 12.201 2.00898 12.7007 2.07732 13.2488L4.06195 13.0013ZM20.3458 15.375L20.3458 14.375L20.3458 14.375L20.3458 15.375ZM17.375 14.375C16.8227 14.375 16.375 14.8227 16.375 15.375C16.375 15.9273 16.8227 16.375 17.375 16.375L17.375 14.375ZM4.00001 5.00002C4.00001 4.44773 3.55229 4.00002 3.00001 4.00002C2.44772 4.00002 2.00001 4.44773 2.00001 5.00002L4.00001 5.00002ZM3.00001 8.62502L2.00001 8.62502L2.00001 9.62502H3.00001V8.62502ZM3.65421 8.62502L3.65421 9.62502L3.65421 9.62502L3.65421 8.62502ZM12 3.00002L12 2.00002L12 3.00002ZM6.62501 9.62502C7.17729 9.62502 7.62501 9.1773 7.62501 8.62502C7.62501 8.07273 7.17729 7.62502 6.62501 7.62502L6.62501 9.62502ZM19.9381 10.9988C20.0064 11.5468 20.5061 11.9357 21.0541 11.8673C21.6022 11.799 21.991 11.2993 21.9227 10.7513L19.9381 10.9988ZM12.8552 9.58595C13.1788 10.0335 13.804 10.134 14.2515 9.81034C14.699 9.48673 14.7995 8.86159 14.4759 8.41404L12.8552 9.58595ZM12.5 7C12.5 6.44771 12.0523 6 11.5 6C10.9477 6 10.5 6.44771 10.5 7H12.5ZM10.5 17C10.5 17.5523 10.9477 18 11.5 18C12.0523 18 12.5 17.5523 12.5 17L10.5 17ZM10.1448 14.414C9.82121 13.9665 9.19606 13.866 8.74852 14.1896C8.30098 14.5133 8.20051 15.1384 8.52412 15.5859L10.1448 14.414ZM22 19L22 15.375L20 15.375L20 19L22 19ZM12 20C7.92115 20 4.55392 16.9466 4.06195 13.0013L2.07732 13.2488C2.69257 18.1827 6.89973 22 12 22L12 20ZM19.4189 14.9998C18.2313 17.9335 15.3558 20 12 20L12 22C16.1983 22 19.79 19.4132 21.2727 15.7502L19.4189 14.9998ZM21 14.375H20.3458V16.375H21V14.375ZM20.3458 14.375L17.375 14.375L17.375 16.375L20.3458 16.375L20.3458 14.375ZM2.00001 5.00002L2.00001 8.62502L4.00001 8.62502L4.00001 5.00002L2.00001 5.00002ZM4.58115 9.00023C5.76867 6.06656 8.6442 4.00002 12 4.00002L12 2.00002C7.80171 2.00002 4.21 4.58686 2.72728 8.2498L4.58115 9.00023ZM3.00001 9.62502H3.65421V7.62502H3.00001V9.62502ZM3.65421 9.62502L6.62501 9.62502L6.62501 7.62502L3.65421 7.62502L3.65421 9.62502ZM12 4.00002C16.0789 4.00001 19.4461 7.05347 19.9381 10.9988L21.9227 10.7513C21.3074 5.81736 17.1003 2.00001 12 2.00002L12 4.00002ZM11.5 11C10.4518 11 10 10.3556 10 10H8C8 11.8535 9.78676 13 11.5 13V11ZM10 10C10 9.64441 10.4518 9 11.5 9V7C9.78676 7 8 8.14644 8 10H10ZM11.5 9C12.1534 9 12.6379 9.28548 12.8552 9.58595L14.4759 8.41404C13.8286 7.51891 12.6973 7 11.5 7V9ZM11.5 13C12.5482 13 13 13.6444 13 14H15C15 12.1464 13.2132 11 11.5 11V13ZM10.5 7V8H12.5V7H10.5ZM10.5 16L10.5 17L12.5 17L12.5 16L10.5 16ZM11.5 15C10.8466 15 10.3621 14.7145 10.1448 14.414L8.52412 15.5859C9.17138 16.4811 10.3027 17 11.5 17L11.5 15ZM13 14C13 14.3556 12.5482 15 11.5 15V17C13.2132 17 15 15.8535 15 14H13Z"
                    fill="rgb(107 114 128)"/>
            </svg>
        </div>
        <div class="wpuf-flex wpuf-px-6 wpuf-pb-6 wpuf-justify-between wpuf-items-center">
            <p class="wpuf-text-gray-500 wpuf-text-sm wpuf-m-0">{{ __( 'Total Subscribers' ) }}</p>
            <a :href="subscribersLink" class="wpuf-text-gray-500">{{ subscribers }}</a>
        </div>
    </div>
    <Popup
        v-if="showPopup"
        @hide-popup="showPopup = false"
        @trash-subscription="trashSubscription(subscription)"
        @delete-subscription="deleteSubscription(subscription)" />
</template>
