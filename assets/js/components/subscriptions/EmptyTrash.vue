<script setup>
import {__} from '@wordpress/i18n';
import {useComponentStore} from '../../stores/component';

const componentStore = useComponentStore();
</script>

<template>
    <div class="no-subscription-box wpuf-border-2 wpuf-border-zinc-300 wpuf-p-12">
        <div class="wpuf-text-center">
        <svg class="wpuf-mx-auto wpuf-h-12 wpuf-w-12 wpuf-text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
        </svg>
        <p class="wpuf-text-lg wpuf-text-gray-500">{{
                __( 'Your trash is empty! If you delete a subscription, it will be moved here.',
                    'wp-user-frontend' )
            }}
        </p>
        </div>
    </div>
</template>
