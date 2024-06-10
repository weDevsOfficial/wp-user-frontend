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

        componentStore.setCurrentComponent( 'List' );
    });
};

</script>
<template>
    <div class="wpuf-px-12">
        <div class="wpuf-flex wpuf-justify-between">
            <button
                type="button"
                @click="[componentStore.setCurrentComponent('List'), subscriptionStore.setCurrentSubscription(null)]"
                class="wpuf-rounded-md wpuf-bg-indigo-600 wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                <span class="dashicons dashicons-arrow-left-alt"></span>&nbsp;{{ __( 'Back', 'wp-user-frontend' ) }}</button>
        </div>
        <h3 class="wpuf-text-lg wpuf-font-bold wpuf-mb-0">{{ __( 'New Subscription', 'wp-user-frontend' ) }}</h3>
        <SubscriptionsDetails />
        <div class="wpuf-mt-8 wpuf-text-end">
            <UpdateButton
                buttonText="Save"
                @update-subscription="updateSubscription" />
        </div>
    </div>
</template>
