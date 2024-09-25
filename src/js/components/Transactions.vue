<script setup>
import Header from './Header.vue';
import {HollowDotsSpinner} from 'epic-spinners';
import {ref} from 'vue';
import Empty from './transactions/Empty.vue';
import SidebarMenu from './SidebarMenu.vue';
import List from './List.vue';
import ContentHeader from './transactions/ContentHeader.vue';

const isLoading = ref( false );
const isEmpty = ref( false );

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
    <div class="wpuf-w-[calc(100%+40px)] wpuf-ml-[-20px] wpuf-bg-white wpuf-h-screen">
        <div class="wpuf-pt-[32px] wpuf-px-[40px]">
            <ContentHeader />
        </div>
        <Empty v-if="isEmpty"/>
        <div
            v-if="!isEmpty"
            class="wpuf-flex wpuf-pt-[40px] wpuf-px-[40px]">
            <div class="wpuf-w-72 wpuf-border-r wpuf-border-gray-200">
                <SidebarMenu/>
            </div>
            <div class="wpuf-basis-4/5">
                <List/>
            </div>
        </div>
    </div>
</template>
