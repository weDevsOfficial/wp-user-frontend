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
import Unsaved from './subscriptions/Unsaved.vue';
import {useNoticeStore} from '../stores/notice';
import ContentHeader from './subscriptions/ContentHeader.vue';

const componentStore = useComponentStore();
const subscriptionStore = useSubscriptionStore();
const quickEditStore = useQuickEditStore();
const noticeStore = useNoticeStore();
const {currentComponent} = storeToRefs( componentStore );
const {notices} = storeToRefs( noticeStore );

const component = ref( null );
const tempSubscriptionStatus = ref( 'all' );
const componentKey = ref( 0 );
const noticeKey = ref( 0 );

provide( 'wpufSubscriptions', wpufSubscriptions );

onBeforeMount( () => {
    const promiseResult = subscriptionStore.setSubscriptionsByStatus( subscriptionStore.currentSubscriptionStatus );
    promiseResult.then( ( result ) => {
        if (subscriptionStore.subscriptionList) {
            componentStore.setCurrentComponent( 'List' );
        } else {
            componentStore.setCurrentComponent( 'Empty' );
        }
        componentKey.value += 1;
    } );

    subscriptionStore.getSubscriptionCount();
} );

const checkIsDirty = ( subscriptionStatus = 'all' ) => {
    if (subscriptionStore.isDirty) {
        subscriptionStore.isUnsavedPopupOpen = true;
        tempSubscriptionStatus.value = subscriptionStatus;
    } else {
        subscriptionStore.isDirty = false;
        subscriptionStore.isUnsavedPopupOpen = false;

        subscriptionStore.setSubscriptionsByStatus( subscriptionStatus );
        componentStore.setCurrentComponent( 'List' );
        subscriptionStore.setCurrentSubscription(null);
        subscriptionStore.getSubscriptionCount();
        subscriptionStore.currentPageNumber = 1;
    }
};

const goToList = () => {
    subscriptionStore.isDirty = false;
    subscriptionStore.isUnsavedPopupOpen = false;

    subscriptionStore.setSubscriptionsByStatus( tempSubscriptionStatus.value );
    componentStore.setCurrentComponent( 'List' );
    subscriptionStore.setCurrentSubscription(null);
    subscriptionStore.currentPageNumber = 1;
};

const removeNotice = ( index ) => {
    noticeStore.removeNotice( index );
    noticeKey.value += 1;
};

watch(
    () => componentStore.currentComponent,
    ( newComponent ) => {
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

        subscriptionStore.resetErrors();
    }
);

</script>

<template>
    <Header/>
    <div v-if="subscriptionStore.isSubscriptionLoading || component === null" class="wpuf-flex wpuf-h-svh wpuf-items-center wpuf-justify-center">
        <hollow-dots-spinner
            :animation-duration="1000"
            :dot-size="20"
            :dots-num="3"
            :color="'#7DC442'"
        />
    </div>
    <div
        v-if="quickEditStore.isQuickEdit"
        @click="[quickEditStore.setQuickEditStatus(false), subscriptionStore.errors = {}]"
        class="wpuf-absolute wpuf-w-full wpuf-h-screen wpuf-z-10 wpuf-left-[-20px]"></div>
    <template v-if="quickEditStore.isQuickEdit">
        <QuickEdit />
    </template>
    <ContentHeader />
    <div
        v-if="!subscriptionStore.isSubscriptionLoading"
        :class="quickEditStore.isQuickEdit ? 'wpuf-blur' : ''"
        class="wpuf-flex wpuf-pt-[40px] wpuf-pr-[20px] wpuf-pl-[20px]">
        <div class="wpuf-basis-1/5 wpuf-border-r-2 wpuf-border-gray-200">
            <keep-alive>
                <SidebarMenu @check-is-dirty="checkIsDirty" />
            </keep-alive>
        </div>
        <div
            class="wpuf-basis-4/5">
            <component :key="componentKey" :is="component" @go-to-list="goToList" @check-is-dirty="checkIsDirty" />
        </div>
        <Unsaved v-if="subscriptionStore.isUnsavedPopupOpen" @close-popup="subscriptionStore.isUnsavedPopupOpen = false" @goToList="goToList" />
    </div>
    <div class="wpuf-fixed wpuf-top-20 wpuf-right-8 wpuf-z-10">
        <Notice
            v-if="noticeStore.display"
            v-for="(notice, index) in notices"
            :key="noticeKey"
            :index="index"
            :type="notice.type"
            :message="notice.message"
            @removeNotice="removeNotice(index)"
        />
    </div>
</template>
