<script setup>
    import {__} from '@wordpress/i18n';

    import {useSubscriptionStore} from '../../stores/subscription';
    import {reactive, ref} from 'vue';
    import AccordionOverview from './AccordionOverview.vue';
    import AccordionAccess from './AccordionAccess.vue';
    import AccordionExpiration from './AccordionExpiration.vue';

    const subscriptionStore = useSubscriptionStore();
    const subscription = subscriptionStore.currentSubscription;

    const currentTab = ref( 'details' );

    const errors = reactive( {
        planName: false,
        date: false,
        isPrivate: false,
    } );
</script>
<template>
    <div class="wpuf-mt-4 wpuf-text-sm wpuf-font-medium wpuf-text-center wpuf-text-gray-500 wpuf-border-b wpuf-border-gray-200 dark:wpuf-text-gray-400 dark:wpuf-border-gray-700">
        <ul class="wpuf-flex wpuf-flex-wrap wpuf--mb-px">
            <li class="wpuf-mb-0 wpuf-me-2">
                <a href="#"
                   :class="currentTab === 'details' ? 'wpuf-border-blue-600 wpuf-text-blue-600' : ''"
                   class="active:wpuf-shadow-none focus:wpuf-shadow-none wpuf-inline-block wpuf-p-4 wpuf-border-b-2 wpuf-border-transparent wpuf-rounded-t-lg hover:wpuf-text-gray-600 hover:wpuf-border-gray-300 dark:hover:wpuf-text-gray-300">
                    {{ __( 'Subscription Details', 'wp-user-frontend' ) }}
                </a>
            </li>
            <li class="wpuf-mb-0 wpuf-me-2">
                <a href="#"
                   :class="currentTab === 'payment' ? 'wpuf-border-blue-600 wpuf-text-blue-600' : ''"
                   class="active:wpuf-shadow-none focus:wpuf-shadow-none wpuf-inline-block wpuf-p-4 wpuf-border-b-2 wpuf-border-transparent wpuf-rounded-t-lg hover:wpuf-text-gray-600 hover:wpuf-border-gray-300 dark:hover:wpuf-text-gray-300">
                    {{ __( 'Payment Settings', 'wp-user-frontend' ) }}
                </a>
            </li>
            <li class="wpuf-mb-0 wpuf-me-2">
                <a href="#"
                   :class="currentTab === 'advanced' ? 'wpuf-border-blue-600 wpuf-text-blue-600' : ''"
                   class="active:wpuf-shadow-none focus:wpuf-shadow-none wpuf-inline-block wpuf-p-4 wpuf-border-b-2 wpuf-border-transparent wpuf-rounded-t-lg hover:wpuf-text-gray-600 hover:wpuf-border-gray-300 dark:hover:wpuf-text-gray-300">
                    {{ __( 'Advanced Configuration', 'wp-user-frontend' ) }}
                </a>
            </li>
        </ul>
    </div>
    <div id="accordion-collapse" data-accordion="collapse">
        <AccordionOverview :subscription="subscription" :errors="errors" />
        <AccordionAccess :subscription="subscription" :errors="errors" />
        <AccordionExpiration :subscription="subscription" :errors="errors" />
    </div>

</template>
