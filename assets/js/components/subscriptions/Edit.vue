<script setup>
import {__} from '@wordpress/i18n';
import InfoCard from './InfoCard.vue';
import SubscriptionsDetails from './SubscriptionsDetails.vue';
import {useSubscriptionStore} from '../../stores/subscription';
import UpdateButton from './UpdateButton.vue';
import {useNoticeStore} from '../../stores/notice';

const subscriptionStore = useSubscriptionStore();
const noticeStore = useNoticeStore();

const emit = defineEmits( ['go-to-list'] );

const updateSubscription = () => {
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
            subscriptionStore.getSubscriptionCount();

            emit('go-to-list');
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
        <h3 class="wpuf-text-lg wpuf-font-bold wpuf-mb-0">{{ __( 'Edit Subscription', 'wp-user-frontend' ) }}</h3>
        <InfoCard />
        <SubscriptionsDetails />
        <div class="wpuf-mt-8 wpuf-text-end">
            <UpdateButton
                @update-subscription="updateSubscription" />
        </div>
    </div>
</template>
