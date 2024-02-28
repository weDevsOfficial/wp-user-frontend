<script>

import {__} from '@wordpress/i18n';
import {computed, defineComponent, ref} from 'vue';
import Empty from './Empty.vue';
import axios from 'axios';

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
    components: {Empty},
    methods: {
        __,
        getSubscriptions() {
            axios.get(this.postsUrl, {params: this.postsData})
                .then((response) => {
                    console.log(response);
                    this.posts = response.data;
                    this.configPagination(response.headers);
                })
                .catch( (error) => {
                    console.log(error);
                });
        },
        configPagination(headers) {
            this.pagination.total = +headers['x-wp-total'];
            this.pagination.totalPages = +headers['x-wp-totalpages'];
            this.pagination.from = this.pagination.total ? ((this.postsData.page - 1) * this.postsData.per_page) + 1 : ' ';
            this.pagination.to = (this.postsData.page * this.postsData.per_page) > this.pagination.total ? this.pagination.total : this.postsData.page * this.postsData.per_page;
            this.pagination.prevPage = this.postsData.page > 1 ? this.postsData.page : '';
            this.pagination.nextPage = this.postsData.page < this.pagination.totalPages ? this.postsData.page + 1 : '';
        }
    }
} )

const currentPath = ref(window.location.hash)

window.addEventListener('hashchange', () => {
    currentPath.value = window.location.hash
})
</script>

<template>
    <div class="wpuf-basis-1/4 wpuf-border-r-2 wpuf-border-zinc-300">
        <h2 @click="getSubscriptions">{{ __( 'Menu', 'wp-user-frontend' ) }}</h2>
    </div>
    <div class="wpuf-basis-1/2">
        <component :is="currentView" />
        <Empty />
    </div>
</template>
