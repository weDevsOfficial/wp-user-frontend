<script setup>
    import {useSubscriptionStore} from '../../stores/subscription';
    import {inject, reactive, ref} from 'vue';
    import Subsection from './Subsection.vue';

    const subscriptionStore = useSubscriptionStore();
    const subscription = subscriptionStore.currentSubscription;

    const currentTab = ref( 'subscription_details' );

    const wpufSubscriptions = inject( 'wpufSubscriptions' );

    const errors = reactive( {
        planName: false,
        date: false,
        isPrivate: false,
    } );
</script>
<template>
    <div class="wpuf-mt-4 wpuf-text-sm wpuf-font-medium wpuf-text-center wpuf-text-gray-500 wpuf-border-b wpuf-border-gray-200 dark:wpuf-text-gray-400 dark:wpuf-border-gray-700">
        <ul class="wpuf-flex wpuf-flex-wrap wpuf--mb-px">
            <li
                v-for="section in wpufSubscriptions.sections"
                :key="section.id"
                class="wpuf-mb-0 wpuf-me-2">
                <a href="#"
                   :class="currentTab === section.id ? 'wpuf-border-blue-600 wpuf-text-blue-600' : ''"
                   class="active:wpuf-shadow-none focus:wpuf-shadow-none wpuf-inline-block wpuf-p-4 wpuf-border-b-2 wpuf-rounded-t-lg hover:wpuf-text-gray-600 hover:wpuf-border-gray-300 dark:hover:wpuf-text-gray-300">
                    {{ section.title }}
                </a>
            </li>
        </ul>
    </div>
    <div id="accordion-collapse">
        <Subsection
            v-for="section in wpufSubscriptions.subSections[currentTab]"
            :key="section.id"
            :subSection="section"
            :subscription="subscription"
            :fields="wpufSubscriptions.fields[currentTab][section.id]"
        />
    </div>

</template>
