<script setup>
import {__} from '@wordpress/i18n';
</script>

<template>
    <div class="wpuf-p-12">
        <p class="wpuf-text-lg wpuf-text-gray-500">{{
                __( 'Your trash is empty! If you delete a subscription, it will be moved here.',
                    'wp-user-frontend' )
            }}
        </p>
    </div>
</template>
