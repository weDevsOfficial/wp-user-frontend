<script setup>
import {__} from '@wordpress/i18n';
import SubscriptionsDetails from './SubscriptionsDetails.vue';
import {useComponentStore} from '../../stores/component';
import {useSubscriptionStore} from '../../stores/subscription';
import {onBeforeMount, ref} from 'vue';
import UpdateButton from './UpdateButton.vue';
import {useNoticeStore} from '../../stores/notice';

const componentStore = useComponentStore();
const subscriptionStore = useSubscriptionStore();
const noticeStore = useNoticeStore();

onBeforeMount(() => {
    subscriptionStore.setBlankSubscription();
});

const updateSubscription = () => {
    subscriptionStore.isUpdating = true;
    subscriptionStore.resetErrors();

    if(!subscriptionStore.validateFields()) {
        subscriptionStore.isUpdating = false;

        return;
    }

    subscriptionStore.isSubscriptionLoading = true;

    const promiseResult = subscriptionStore.updateSubscription();

    promiseResult.then((result) => {
        if (result.success) {
            noticeStore.display = true;
            noticeStore.type = 'success';
            noticeStore.message = result.message;

            subscriptionStore.setSubscriptionsByStatus( subscriptionStore.currentSubscriptionStatus );
            componentStore.setCurrentComponent( 'List' );
            subscriptionStore.getSubscriptionCount();
        } else {
            noticeStore.display = true;
            noticeStore.type = 'danger';
            noticeStore.message = result.message;
        }

        subscriptionStore.isUpdating = false;

        setTimeout(() => {
            noticeStore.display = false;
            noticeStore.type = '';
            noticeStore.message = '';
        }, 3000);

    }).finally(() => {
        subscriptionStore.isSubscriptionLoading = false;
    });
};

</script>
<template>
    <div
        :class="subscriptionStore.isUnsavedPopupOpen ? 'wpuf-blur' : ''"
        class="wpuf-px-12">
        <h3 class="wpuf-text-lg wpuf-font-bold wpuf-mb-0">{{ __( 'New Subscription', 'wp-user-frontend' ) }}</h3>
        <SubscriptionsDetails />
        <div class="wpuf-flex wpuf-flex-row-reverse wpuf-mt-8 wpuf-text-end">
            <UpdateButton
                buttonText="Save"
                @update-subscription="updateSubscription" />
            <button
                @click="$emit('checkIsDirty', subscriptionStore.currentSubscriptionStatus)"
                type="button"
                class="wpuf-mr-[10px] wpuf-rounded-md wpuf-bg-white wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900 wpuf-shadow-sm wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 hover:wpuf-bg-gray-50">
                {{ __( 'Cancel', 'wp-user-frontend' ) }}
            </button>
        </div>
    </div>
</template>
