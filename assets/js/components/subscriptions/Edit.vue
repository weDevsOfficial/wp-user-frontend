<script setup>
import {__} from '@wordpress/i18n';
import InfoCard from './InfoCard.vue';
import SubscriptionsDetails from './SubscriptionsDetails.vue';
import {useComponentStore} from '../../stores/component';
import {useSubscriptionStore} from '../../stores/subscription';
import {ref} from 'vue';
import UpdateButton from './UpdateButton.vue';
import {useNoticeStore} from '../../stores/notice';
import Unsaved from './Unsaved.vue';

const componentStore = useComponentStore();
const subscriptionStore = useSubscriptionStore();
const noticeStore = useNoticeStore();

const updateSubscription = () => {
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

const goToList = () => {
    subscriptionStore.isDirty = false;
    subscriptionStore.isUnsavedPopupOpen = false;

    componentStore.setCurrentComponent('List');
    subscriptionStore.setCurrentSubscription(null);
};

</script>
<template>
    <div
        :class="subscriptionStore.isUnsavedPopupOpen ? 'wpuf-blur' : ''"
        class="wpuf-px-12">
        <div class="wpuf-flex wpuf-justify-between">
            <button
                type="button"
                @click="subscriptionStore.isDirty ? subscriptionStore.isUnsavedPopupOpen = subscriptionStore.isDirty : goToList()"
                class="wpuf-rounded-md wpuf-bg-indigo-600 wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                <span class="dashicons dashicons-arrow-left-alt"></span>&nbsp;{{ __( 'Back', 'wp-user-frontend' ) }}</button>
        </div>
        <h3 class="wpuf-text-lg wpuf-font-bold wpuf-mb-0">{{ __( 'Edit', 'wp-user-frontend' ) }}</h3>
        <InfoCard />
        <SubscriptionsDetails />
        <div class="wpuf-mt-8 wpuf-text-end">
            <UpdateButton
                @update-subscription="updateSubscription" />
        </div>
    </div>
    <Unsaved v-if="subscriptionStore.isUnsavedPopupOpen" @close-popup="subscriptionStore.isUnsavedPopupOpen = false" @go-to-list="goToList" />
</template>
