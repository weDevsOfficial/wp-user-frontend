<script setup>
import { __ } from '@wordpress/i18n';
import {useSubscriptionStore} from '../../stores/subscription';

const subscriptionStore = useSubscriptionStore();

const status = [
    {
        'all': __( 'All Subscriptions', 'wp-user-frontend' )
    },
    {
        'publish': __( 'Published', 'wp-user-frontend' )
    },
    {
        'draft': __( 'Drafts', 'wp-user-frontend' )
    },
    {
        'trash': __( 'Trash', 'wp-user-frontend' )
    }
];

status.map( ( item ) => {
    const key = Object.keys( item )[0];
    const label = item[key];
} );

</script>
<template>
    <div
    :class="subscriptionStore.isUnsavedPopupOpen ? 'wpuf-blur' : ''">
        <div class="wpuf-flex wpuf-flex-col wpuf-pr-[48px]">
            <ul class="wpuf-space-y-2 wpuf-text-lg">
                <li
                    v-for="item in status"
                    :key="Object.keys( item )[0]"
                    @click="$emit('checkIsDirty', Object.keys( item )[0])"
                    :class="subscriptionStore.currentSubscriptionStatus === Object.keys( item )[0] ? 'wpuf-bg-gray-50 wpuf-text-indigo-600' : ''"
                    class="wpuf-justify-between wpuf-text-gray-700 hover:wpuf-text-indigo-600 hover:wpuf-bg-gray-50 group wpuf-flex wpuf-gap-x-3 wpuf-rounded-md wpuf-py-2 wpuf-px-[20px] wpuf-text-sm wpuf-leading-6 hover:wpuf-cursor-pointer">
                    {{ item[Object.keys( item )[0]] }}
                    <span
                        v-if="subscriptionStore.allCount[Object.keys( item )[0]] > 0"
                        :class="subscriptionStore.currentSubscriptionStatus === Object.keys( item )[0] ? 'wpuf-border-indigo-600' : ''"
                        class="wpuf-text-sm wpuf-w-fit wpuf-px-2.5 wpuf-py-1 wpuf-rounded-full wpuf-w-max wpuf-h-max wpuf-border">
                        {{ subscriptionStore.allCount[Object.keys( item )[0]] }}
                    </span>
                </li>
            </ul>
        </div>
    </div>
</template>
