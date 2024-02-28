<script>
import { __ } from '@wordpress/i18n';

export default {
    methods: {__}
}
</script>

<template>
    <div class="no-subscription-box wpuf-border-2 wpuf-border-zinc-300 wpuf-m-4">
        <div class="wpuf-inner-box wpuf-p-12 wpuf-text-center wpuf-mt-12">
            <span class="dashicons dashicons-archive wpuf-text-5xl wpuf-w-5xl wpuf-h-5xl"></span>
            <h3 class="wpuf-text-slate-900 wpuf-text-3xl wpuf-mt-16">
                {{ __( 'No Subscription created yet!', 'wp-user-frontend' ) }}
            </h3>
            <p class="wpuf-text-lg">
                {{ __( 'Powerful Subscription Features for Monetizing Your Content. Unlock a World of Possibilities with WPUF\'s Subscription Features – From Charging Users for Posting to Exclusive Content Access.',
                'wp-user-frontend') }}
            </p>
            <button
                type="button"
                @click="component ='Subscription'"
                class="wpuf-rounded-md wpuf-bg-indigo-600 wpuf-px-3 wpuf-py-2 wpuf-text-sm font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-bg-indigo-500 focus-visible:wpuf-outline focus-visible:wpuf-outline-2 focus-visible:wpuf-outline-offset-2 focus-visible:wpuf-outline-indigo-600">
                <span class="dashicons dashicons-plus-alt"></span>&nbsp;&nbsp;&nbsp;
                {{ __( 'Add Subscription', 'wp-user-frontend' ) }}
            </button>
        </div>
    </div>
</template>
