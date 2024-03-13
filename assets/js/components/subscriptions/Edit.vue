<script setup>
    import {__} from '@wordpress/i18n';
    import InfoCard from './InfoCard.vue';
    import SubscriptionsDetails from './SubscriptionsDetails.vue';
</script>
<template>
    <div class="wpuf-px-12">
        <h3 class="wpuf-text-lg wpuf-font-bold wpuf-mb-0">{{ __( 'Edit', 'wp-user-frontend' ) }}</h3>
        <InfoCard />
        <SubscriptionsDetails />
    </div>
</template>
