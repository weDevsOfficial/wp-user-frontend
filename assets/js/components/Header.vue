<script setup>
import { __ } from '@wordpress/i18n';
import {inject} from 'vue';

const wpufSubscriptions = inject( 'wpufSubscriptions' );
const logoUrl = wpufSubscriptions.assetUrl + '/images/icon-128x128.png';
</script>

<template>
    <div class="header wpuf-flex wpuf-flex-row wpuf-mt-4 wpuf-justify-between wpuf-items-center">
        <div class="wpuf-flex wpuf-justify-start wpuf-items-center">
            <img :src="logoUrl" alt="WPUF Icon" class="wpuf-w-12 wpuf-mr-4">
            <h2>{{ __( 'WP User Frontend', 'wp-user-frontend' ) }}</h2>
            <span class="wpuf-ml-2 wpuf-inline-flex wpuf-items-center wpuf-rounded-full wpuf-bg-green-100 wpuf-px-2 wpuf-py-1 wpuf-text-xs wpuf-font-medium wpuf-text-green-700 wpuf-ring-1 wpuf-ring-inset wpuf-ring-green-600/20">V{{ wpufSubscriptions.version }}</span>
        </div>
        <div class="wpuf-align-right">
            <a
                :href="wpufSubscriptions.supportUrl"
                target="_blank"
                class="wpuf-rounded-md wpuf-bg-indigo-600 wpuf-px-3 wpuf-py-2 wpuf-text-sm font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-bg-indigo-500 hover:wpuf-text-white focus:wpuf-bg-indigo-500 focus:wpuf-text-white">
                {{ __( 'Support', 'wp-user-frontend' ) }}
                &nbsp;&nbsp;
                <span class="dashicons dashicons-businessman"></span>
            </a>
        </div>
    </div>

</template>
