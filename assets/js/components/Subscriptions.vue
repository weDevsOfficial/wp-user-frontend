<script setup>
import {ref, provide, onBeforeMount, watch} from 'vue';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

import {HollowDotsSpinner} from 'epic-spinners'

import Header from './Header.vue';
import SidebarMenu from './subscriptions/SidebarMenu.vue';
import List from './subscriptions/List.vue';
import Empty from './subscriptions/Empty.vue';
import {useCurrentComponent} from '../composables/components';

const isLoading = ref( false );
const subscriptions = ref( null );

provide( 'wpufSubscriptions', wpufSubscriptions );
const { currentComponent, setCurrentComponent } = useCurrentComponent();

const fetchData = async () => {
    isLoading.value = true;

    const queryParams = { 'per_page': 10, 'offset': 0 };

    // todo: add nonce and other validations.
    apiFetch( {path: addQueryArgs( wpufSubscriptions.siteUrl + '/wp-json/wpuf/v1/wpuf_subscription', queryParams )} )
        .then( ( response ) => {
            subscriptions.value = response.subscriptions;
    } ).catch( ( error ) => {
        console.log( error );
    } ).finally( () => {
        isLoading.value = false;
    })
}

onBeforeMount( () => {
    fetchData();
} );

watch( subscriptions, ( subscriptions ) => {
    if ( subscriptions.length > 0 ) {
        setCurrentComponent( List );
    } else {
        setCurrentComponent( Empty );
    }
} );

</script>

<template>
    <Header/>
    <div v-if="isLoading" class="wpuf-flex wpuf-h-svh wpuf-items-center wpuf-justify-center">
        <hollow-dots-spinner
            :animation-duration="1000"
            :dot-size="20"
            :dots-num="3"
            :color="'#7DC442'"
        />
    </div>

    <div class="wpuf-flex wpuf-flex-row wpuf-mt-12">
        <div class="wpuf-basis-1/4 wpuf-border-r-2 wpuf-border-zinc-300">
            <SidebarMenu />
        </div>
        <div class="wpuf-basis-3/4">
            <component :is="currentComponent" :subscriptions=subscriptions />
        </div>
    </div>
</template>
