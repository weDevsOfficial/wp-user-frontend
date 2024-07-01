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

    const promiseResult = subscriptionStore.updateSubscription();

    promiseResult.then((result) => {
        if (result.success) {
            noticeStore.display = true;
            noticeStore.type = 'success';
            noticeStore.message = result.message;
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

        subscriptionStore.setSubscriptionsByStatus( subscriptionStore.currentSubscriptionStatus );
        componentStore.setCurrentComponent( 'List' );
    });
};

</script>
<template>
    <div class="wpuf-px-12">
        <h3 class="wpuf-text-lg wpuf-font-bold wpuf-mb-0">{{ __( 'New Subscription', 'wp-user-frontend' ) }}</h3>
        <SubscriptionsDetails />
        <div class="wpuf-mt-8 wpuf-text-end">
            <UpdateButton
                buttonText="Save"
                @update-subscription="updateSubscription" />
        </div>
    </div>
</template>
