<script setup>
import {__} from '@wordpress/i18n';
import InfoCard from './InfoCard.vue';
import SubscriptionsDetails from './SubscriptionsDetails.vue';
import {useSubscriptionStore} from '../../stores/subscription';
import UpdateButton from './UpdateButton.vue';
import {useNoticeStore} from '../../stores/notice';
import {useComponentStore} from '../../stores/component';
import {defineEmits} from '../../../vendor/vue-3/vue.esm-browser';

const subscriptionStore = useSubscriptionStore();
const noticeStore = useNoticeStore();
const componentStore = useComponentStore();

const emit = defineEmits( ['go-to-list', 'checkIsDirty'] );

const updateSubscription = () => {
    subscriptionStore.resetErrors();

    if(!subscriptionStore.validateFields()) {
        subscriptionStore.isUpdating = false;

        return;
    }

    subscriptionStore.isUpdating = true;

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

        setTimeout(() => {
            noticeStore.display = false;
            noticeStore.type = '';
            noticeStore.message = '';
        }, 3000);
    }).finally(() => {
        subscriptionStore.isUpdating = false;
    });
};

const goToList = () => {
    subscriptionStore.isDirty = false;
    subscriptionStore.isUnsavedPopupOpen = false;

    subscriptionStore.setSubscriptionsByStatus( tempSubscriptionStatus.value );
    componentStore.setCurrentComponent( 'List' );
    subscriptionStore.setCurrentSubscription(null);
};

</script>
<template>
    <div
        :class="subscriptionStore.isUnsavedPopupOpen ? 'wpuf-blur' : ''"
        class="wpuf-px-12">
        <h3 class="wpuf-text-lg wpuf-font-bold wpuf-mb-0">{{ __( 'Edit Subscription', 'wp-user-frontend' ) }}</h3>
        <InfoCard />
        <SubscriptionsDetails />
        <div class="wpuf-flex wpuf-flex-row-reverse wpuf-mt-8 wpuf-text-end">
            <UpdateButton
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
