<script setup>
import {ref, computed, provide, watch, reactive} from 'vue'
import { HollowDotsSpinner } from 'epic-spinners'

import Header from './Header.vue';
import List from './subscriptions/List.vue';
import New from './subscriptions/New.vue';

const routes = {
    '/': List,
    '/new': New
}

const currentPath = ref(window.location.hash)

window.addEventListener('hashchange', () => {
    currentPath.value = window.location.hash
})

const currentView = computed(() => {
    return routes[currentPath.value.slice(1) || '/'] || 'NotFound'
})

provide('wpufSubscriptions', wpufSubscriptions);

const isLoading = ref(false);

</script>

<template>
    <Header />
    <div v-if="isLoading" class="wpuf-flex wpuf-h-svh wpuf-items-center wpuf-justify-center">
        <hollow-dots-spinner
            :animation-duration="1000"
            :dot-size="20"
            :dots-num="3"
            :color="'#7DC442'"
        />
    </div>

    <div class="wpuf-flex wpuf-flex-row wpuf-mt-12">
        <component :is="currentView" />
    </div>
</template>
