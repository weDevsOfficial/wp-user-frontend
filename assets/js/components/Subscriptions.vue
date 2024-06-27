<script setup>
import {provide, onBeforeMount, ref, watch} from 'vue';
import {storeToRefs} from 'pinia';
import { useComponentStore } from '../stores/component';

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
import {useSubscriptionStore} from '../stores/subscription';

const componentStore = useComponentStore();
const subscriptionStore = useSubscriptionStore();
const quickEditStore = useQuickEditStore();
const { currentComponent } = storeToRefs(componentStore);

const component = ref(Empty);

provide( 'wpufSubscriptions', wpufSubscriptions );

onBeforeMount( () => {
    const promiseResult = subscriptionStore.setSubscriptionsByStatus( subscriptionStore.currentSubscriptionStatus );
    promiseResult.then( ( result ) => {
        if (subscriptionStore.subscriptionList) {
            componentStore.setCurrentComponent( 'List' );
        } else {
            componentStore.setCurrentComponent( 'Empty' );
        }
    } );

    subscriptionStore.getSubscriptionCount();
} );

watch(
    () => componentStore.currentComponent,
    (newComponent) => {
        switch ( newComponent ) {
            case 'List':
                component.value = List;
                break;
            case 'Edit':
                component.value = Edit;
                break;
            case 'New':
                component.value = New;
                break;
            default:
                component.value = Empty;
        }
    }
);

</script>

<template>
    <Header/>
    <div v-if="subscriptionStore.isUpdating" class="wpuf-flex wpuf-h-svh wpuf-items-center wpuf-justify-center">
        <hollow-dots-spinner
            :animation-duration="1000"
            :dot-size="20"
            :dots-num="3"
            :color="'#7DC442'"
        />
    </div>
    <div
        v-if="quickEditStore.isQuickEdit"
        @click="quickEditStore.isQuickEdit = false"
        class="wpuf-absolute wpuf-w-full wpuf-h-screen wpuf-z-10 wpuf-left-[-20px]"></div>
    <template v-if="quickEditStore.isQuickEdit">
        <QuickEdit />
    </template>
    <div
        v-if="!subscriptionStore.isUpdating"
        :class="quickEditStore.isQuickEdit ? 'wpuf-blur' : ''"
        class="wpuf-flex wpuf-flex-row wpuf-mt-12 wpuf-bg-white wpuf-py-8">
        <div class="wpuf-basis-1/5 wpuf-border-r-2 wpuf-border-gray-200 wpuf-100vh">
            <keep-alive>
                <SidebarMenu />
            </keep-alive>
        </div>
        <div
            v-if="!subscriptionStore.isSubscriptionLoading"
            class="wpuf-basis-4/5">
            <component :is="component" />
        </div>
    </div>
    <Notice />
</template>
