<script>

import {__} from '@wordpress/i18n';
import {defineComponent, ref} from 'vue';
import Empty from './Empty.vue';
import axios from 'axios';
import SidebarMenu from './SidebarMenu.vue';

export default defineComponent( {
    postsUrl: 'https://wpuf.test/wp-json/wp/v2/posts',
    posts: [],
    postsData: {
        per_page: 10,
        page: 1
    },
    pagination: {
        prevPage: '',
        nextPage: '',
        totalPages: '',
        from: '',
        to: '',
        total: '',
    },
    computed: {
        Empty() {
            return Empty
        }
    },
    components: {SidebarMenu, Empty},
    methods: {
        __
    }
} )

const currentPath = ref(window.location.hash)

window.addEventListener('hashchange', () => {
    currentPath.value = window.location.hash
})
</script>

<template>
</template>
