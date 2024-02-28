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
                {{ __( 'Create New!', 'wp-user-frontend' ) }}
            </h3>
        </div>
    </div>
</template>
