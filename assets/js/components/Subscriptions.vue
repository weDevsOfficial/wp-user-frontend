<script setup>
import {ref, provide, onBeforeMount, computed} from 'vue';
import {storeToRefs} from 'pinia';
import { useComponentStore } from '../stores/component';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

import {HollowDotsSpinner} from 'epic-spinners';

import Header from './Header.vue';
import SidebarMenu from './subscriptions/SidebarMenu.vue';
import List from './subscriptions/List.vue';
import Empty from './subscriptions/Empty.vue';
import Edit from './subscriptions/Edit.vue';
import New from './subscriptions/New.vue';
import QuickEdit from './subscriptions/QuickEdit.vue';
import {useQuickEditStore} from '../stores/quickEdit';
import Notice from './subscriptions/Notice.vue';
import {__} from '@wordpress/i18n';
import {useSubscriptionStore} from '../stores/subscription';

const componentStore = useComponentStore();
const subscriptionStore = useSubscriptionStore();
const quickEditStore = useQuickEditStore();
const { currentComponent } = storeToRefs(componentStore);
const { isQuickEdit } = storeToRefs(quickEditStore);
const { isUnsavedPopupOpen } = storeToRefs(subscriptionStore);

const isLoading = ref( false );

provide( 'wpufSubscriptions', wpufSubscriptions );

const fetchData = async () => {
    isLoading.value = true;

    const queryParams = { 'per_page': wpufSubscriptions.perPage, 'offset': 0 };
    apiFetch( {
            path: addQueryArgs( '/wp-json/wpuf/v1/wpuf_subscription', queryParams ),
            method: 'GET',
            headers: {
            'X-WP-Nonce': wpufSubscriptions.nonce,
        },
        } )
        .then( ( response ) => {
            subscriptionStore.subscriptionList = response.subscriptions;

            if ( subscriptionStore.subscriptionList.length > 0 ) {
                componentStore.setCurrentComponent( 'List' );
            } else {
                componentStore.setCurrentComponent( 'Empty' );
            }
    } ).catch( ( error ) => {
        console.log( error );
    } ).finally( () => {
        isLoading.value = false;
    });
}

const component = computed( () => {
    switch ( currentComponent.value ) {
        case 'List':
            return List;
        case 'Empty':
            return Empty;
        case 'Edit':
            return Edit;
        case 'New':
            return New;
        default:
            return Empty;
    }
});

onBeforeMount( () => {
    fetchData();
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
    <div v-if="isQuickEdit" @click="quickEditStore.setQuickEditStatus(false)" class="wpuf-absolute wpuf-w-full wpuf-h-screen wpuf-z-10 wpuf-left-[-20px]"></div>
    <template v-if="isQuickEdit">
        <QuickEdit />
    </template>
    <div
        :class="isQuickEdit ? 'wpuf-blur' : ''"
        class="wpuf-flex wpuf-flex-row wpuf-mt-12 wpuf-bg-white wpuf-py-8">
        <div class="wpuf-basis-1/5 wpuf-border-r-2 wpuf-border-gray-200">
            <SidebarMenu />
        </div>
        <div class="wpuf-basis-4/5">
            <button
                v-if="currentComponent === 'List'"
                @click="componentStore.setCurrentComponent( 'New' )"
                type="button"
                class="wpuf-ml-12 wpuf-rounded-md wpuf-bg-indigo-600 wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                {{ __('Add new', 'wp-user-frontend') }}
            </button>
            <component :is="component" />
        </div>
    </div>
    <Notice />
</template>
