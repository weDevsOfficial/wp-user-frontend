<script setup>
import {useSubscriptionStore} from '../../stores/subscription';
import {inject, provide, reactive, ref} from 'vue';
import Subsection from './Subsection.vue';
import {useFieldDependencyStore} from '../../stores/fieldDependency';

const subscriptionStore = useSubscriptionStore();
const dependencyStore = useFieldDependencyStore();

const subscription = subscriptionStore.currentSubscription;

const currentTab = ref( 'subscription_details' );

const wpufSubscriptions = inject( 'wpufSubscriptions' );

const errors = reactive( {
    planName: false,
    date: false,
    isPrivate: false,
} );

provide( 'currentSection', currentTab );

// for each wpufSubscriptions.dependentFields, add it to the dependencyStore
for (const dependentField in wpufSubscriptions.dependentFields) {
    for (const field in wpufSubscriptions.dependentFields[dependentField]) {

        if (dependencyStore.modifierFields.hasOwnProperty(dependentField)) {
            dependencyStore.modifierFields[dependentField][field] = wpufSubscriptions.dependentFields[dependentField][field];
        } else {
            dependencyStore.modifierFields[dependentField] = {
                [field]: wpufSubscriptions.dependentFields[dependentField][field]
            };
        }
    }
}

</script>
<template>
    <div class="wpuf-mt-4 wpuf-text-sm wpuf-font-medium wpuf-text-center wpuf-text-gray-500 wpuf-border-b wpuf-border-gray-200 dark:wpuf-text-gray-400 dark:wpuf-border-gray-700">
        <ul class="wpuf-flex wpuf-flex-wrap wpuf--mb-px">
            <li
                v-for="section in wpufSubscriptions.sections"
                :key="section.id"
                class="wpuf-mb-0 wpuf-me-2">
                <button
                   @click="currentTab = section.id"
                   :class="currentTab === section.id ? 'wpuf-border-blue-600 wpuf-text-blue-600' : ''"
                   class="active:wpuf-shadow-none focus:wpuf-shadow-none wpuf-inline-block wpuf-p-4 wpuf-border-b-2 wpuf-rounded-t-lg hover:wpuf-text-gray-600 hover:wpuf-border-blue-600 dark:hover:wpuf-text-gray-300">
                    {{ section.title }}
                </button>
            </li>
        </ul>
    </div>
    <Subsection
        v-for="section in wpufSubscriptions.subSections[currentTab]"
        :key="section.id"
        :currentSection="currentTab"
        :subSection="section"
        :subscription="subscription"
        :fields="wpufSubscriptions.fields[currentTab][section.id]"
    />
</template>
