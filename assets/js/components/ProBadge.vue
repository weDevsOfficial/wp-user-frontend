<script setup>
import { computed } from 'vue';

const assetUrl = computed(() => {
    return window.wpufSubscriptions?.assetUrl || '/wp-content/plugins/wp-user-frontend/assets';
});

const badgeUrl = computed(() => `${assetUrl.value}/images/pro-badge.svg`);
</script>

<template>
    <img :src="badgeUrl" alt="PRO" width="39" height="22" />
</template>
