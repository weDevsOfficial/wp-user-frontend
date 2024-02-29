<script setup>
import {ref, provide, onBeforeMount, watch} from 'vue';
import {HollowDotsSpinner} from 'epic-spinners'

import Header from './Header.vue';
import SidebarMenu from './subscriptions/SidebarMenu.vue';
import List from './subscriptions/List.vue';
import Empty from './subscriptions/Empty.vue';
import {useCurrentComponent} from '../composables/components';

provide( 'wpufSubscriptions', wpufSubscriptions );
const { currentComponent, setCurrentComponent } = useCurrentComponent();

const isLoading = ref( false );
const subscriptions = ref( null );

const fetchData = async () => {
    const response = await fetch( 'https://wpuf.test/wp-json/wp/v2/wpuf_subscription' );
    subscriptions.value = await response.json();
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
