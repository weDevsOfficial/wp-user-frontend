<script setup>
import { __ } from '@wordpress/i18n';
import {inject} from 'vue';

const wpufSubscriptions = inject( 'wpufSubscriptions' );
const logoUrl = wpufSubscriptions.assetUrl + '/images/wpuf-icon-circle.svg';
</script>

<template>
    <div class="wpuf-w-[calc(100%+40px)] wpuf-ml-[-20px] wpuf-px-[20px] wpuf-flex wpuf-mt-4 wpuf-justify-between wpuf-items-center wpuf-border-b-2 wpuf-border-gray-100 wpuf-pb-4">
        <div class="wpuf-flex wpuf-justify-start wpuf-items-center">
            <img :src="logoUrl" alt="WPUF Icon" class="wpuf-w-12 wpuf-mr-4">
            <h2>{{ __( 'WP User Frontend', 'wp-user-frontend' ) }}</h2>
            <span class="wpuf-ml-2 wpuf-inline-flex wpuf-items-center wpuf-rounded-full wpuf-bg-green-100 wpuf-px-2 wpuf-py-1 wpuf-text-xs wpuf-font-medium wpuf-text-green-700 wpuf-ring-1 wpuf-ring-inset wpuf-ring-green-600/20">v{{ wpufSubscriptions.version }}</span>
            <a
                v-if="!wpufSubscriptions.isProActive"
                :href="wpufSubscriptions.upgradeUrl"
                target="_blank"
                class="wpuf-ml-4 wpuf-rounded-md wpuf-bg-amber-500 wpuf-px-3 wpuf-py-2 wpuf-text-sm font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-bg-amber-600 hover:wpuf-text-white hover:wpuf-shadow-none active:wpuf-shadow-none focus:wpuf-bg-amber-600 focus:wpuf-text-white">
                {{ __( 'Upgrade', 'wp-user-frontend' ) }}
                &nbsp;
                <span class="dashicons dashicons-superhero-alt"></span>
            </a>
        </div>
        <div class="wpuf-flex wpuf-justify-end wpuf-items-center wpuf-w-2/4">
            <span
                id="wpuf-headway-icon"
                class="wpuf-border wpuf-border-gray-100 wpuf-mr-[16px] wpuf-rounded-full wpuf-p-1 wpuf-shadow-sm hover:wpuf-bg-slate-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
            ></span>
            <a
                class="wpuf-border wpuf-border-gray-100 wpuf-mr-[16px] wpuf-canny-link wpuf-text-center wpuf-rounded-md wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-shadow-sm hover:wpuf-bg-slate-100 focus:wpuf-bg-slate-100"
                target="_blank"
                href="https://wpuf.canny.io/ideas">💡 {{ __( 'Submit Ideas', 'wp-user-frontend' ) }}</a>
            <a
                :href="wpufSubscriptions.supportUrl"
                target="_blank"
                class="wpuf-rounded-md wpuf-text-center wpuf-bg-indigo-600 wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-bg-indigo-500 hover:wpuf-text-white focus:wpuf-bg-indigo-500 focus:wpuf-text-white">
                {{ __( 'Support ', 'wp-user-frontend' ) }}
                &nbsp;&nbsp;
                <span class="dashicons dashicons-businessman"></span>
            </a>
        </div>
    </div>

</template>
