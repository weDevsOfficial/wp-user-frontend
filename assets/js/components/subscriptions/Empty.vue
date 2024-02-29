<script setup>
    import {__} from '@wordpress/i18n';
</script>

<template>
    <div class="no-subscription-box wpuf-border-2 wpuf-border-zinc-300 wpuf-m-4">
        <div class="wpuf-inner-box wpuf-p-12 wpuf-text-center wpuf-mt-12">
            <span class="dashicons dashicons-archive wpuf-text-5xl wpuf-w-5xl wpuf-h-5xl"></span>
            <h3 class="wpuf-text-slate-900 wpuf-text-3xl wpuf-mt-16">
                {{ __( 'No Subscription created yet!', 'wp-user-frontend' ) }}
            </h3>
            <p class="wpuf-text-lg">
                {{
                    __( 'Powerful Subscription Features for Monetizing Your Content. Unlock a World of Possibilities with WPUF\'s Subscription Features – From Charging Users for Posting to Exclusive Content Access.',
                        'wp-user-frontend' )
                }}
            </p>
            <button
                type="button"
                class="wpuf-rounded-md wpuf-bg-indigo-600 wpuf-px-3 wpuf-py-2 wpuf-text-sm font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-bg-indigo-500 focus-visible:wpuf-outline focus-visible:wpuf-outline-2 focus-visible:wpuf-outline-offset-2 focus-visible:wpuf-outline-indigo-600">
                <span class="dashicons dashicons-plus-alt"></span>&nbsp;&nbsp;&nbsp;
                {{ __( 'Add Subscription', 'wp-user-frontend' ) }}
            </button>
        </div>
    </div>
    <div class="wpuf-text-center">
        <svg class="wpuf-mx-auto wpuf-h-12 wpuf-w-12 wpuf-text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
        </svg>
        <h3 class="wpuf-mt-2 wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900">No projects</h3>
        <p class="wpuf-mt-1 wpuf-text-sm wpuf-text-gray-500">Get started by creating a new project.</p>
        <div class="wpuf-mt-6">
            <button type="button" class="wpuf-inline-flex wpuf-items-center wpuf-rounded-md wpuf-bg-indigo-600 wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-bg-indigo-500 focus-visible:wpuf-outline focus-visible:wpuf-outline-2 focus-visible:wpuf-outline-offset-2 focus-visible:wpuf-outline-indigo-600">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                </svg>
                New Project
            </button>
        </div>
    </div>

</template>
