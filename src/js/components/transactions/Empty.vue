<script setup>
import {__} from '@wordpress/i18n';
</script>

<template>
    <div class="wpuf-h-screen wpuf-flex wpuf-items-center wpuf-justify-center wpuf-w-screen">
        <div class="wpuf-w-3/4 wpuf-text-center">
            <h2 class="wpuf-text-xl">{{ __( 'No Transaction Found!', 'wp-user-frontend' ) }}</h2>
            <p class="wpuf-text-base">{{ __( 'Oops! It looks like there are no transactions to display at the moment. Don\'t worry, this could be due to no recent activity or transactions on your account.', 'wp-user-frontend' ) }}</p>
        </div>
    </div>
</template>
