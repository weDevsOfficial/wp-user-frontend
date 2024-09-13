<script setup>
import {computed, ref, watch} from 'vue';
import {useSubscriptionStore} from '../../stores/subscription';

const subscriptionStore = useSubscriptionStore();

const props = defineProps( {
    currentPage: {
        type: Number,
        required: true,
    },
    count:  {
        type: Number,
        required: true,
    },
    maxVisibleButtons:  {
        type: Number,
        required: true,
    },
    totalPages:  {
        type: Number,
        required: true,
    },
    perPage:  {
        type: Number,
        required: true,
    },
} );

const emit = defineEmits( ['changePageTo'] );
const currentPage = ref( props.currentPage );
const count = ref( props.count );
const maxVisibleButtons = ref( props.maxVisibleButtons );
const totalPages = ref( props.totalPages );
const perPage = parseInt( props.perPage );

const isInFirstPage = computed(() => currentPage.value === 1);
const isInLastPage = computed(() => currentPage.value === totalPages.value);
const startPage = computed(() => {
    // When on the first page, or when the total pages are less than the max visible buttons
    if (currentPage.value === 1 || totalPages.value <= maxVisibleButtons.value) {
        return 1;
    }

    // When on the last page
    if (currentPage.value === totalPages.value) {
        return totalPages.value - maxVisibleButtons.value;
    }

    // When in-between
    return currentPage.value - 1;
});
const startNumber = computed(() => {
    return (currentPage.value - 1) * perPage + 1;
});
const endNumber = computed(() => {
    return Math.min(currentPage.value * perPage, count.value);
});
const pages = computed(() => {
    const range = [];
    for (
        let i = startPage.value;
        i <= Math.min(startPage.value + maxVisibleButtons.value - 1, totalPages.value);
        i++
    ) {
        range.push({
            name: i,
            isDisabled: i === currentPage
        });
    }

    return range;
});

const goToFirstPage = () => {
    currentPage.value = 1;

    emit('changePageTo', 1);
};

const goToLastPage = () => {
    currentPage.value = totalPages.value;

    emit('changePageTo', totalPages.value);
};

watch( () => subscriptionStore.currentSubscriptionStatus, ( newValue ) => {
    count.value = subscriptionStore.allCount[newValue];
    totalPages.value = Math.ceil(count.value / wpufSubscriptions.perPage);
});

</script>
<template>
    <div class="wpuf-flex wpuf-items-center wpuf-justify-between wpuf-border-t wpuf-border-gray-200 wpuf-bg-white wpuf-py-3 wpuf-px-6 wpuf-mt-16">
        <div class="wpuf-flex wpuf-flex-1 wpuf-items-center wpuf-justify-between">
            <div>
                <p class="wpuf-text-sm wpuf-text-gray-700">
                    Showing
                    <span class="wpuf-font-medium">{{ startNumber }}</span>
                    to
                    <span class="wpuf-font-medium">{{ endNumber }}</span>
                    of
                    <span class="wpuf-font-medium">{{ count }}</span>
                    results
                </p>
            </div>
            <div v-if="count > perPage">
                <nav class="isolate wpuf-inline-flex wpuf--space-x-px wpuf-rounded-md wpuf-shadow-sm" aria-label="Pagination">
                    <button
                        @click="goToFirstPage"
                        :disabled="isInFirstPage"
                        :class="isInFirstPage ? 'wpuf-bg-gray-50 wpuf-cursor-not-allowed' : ''"
                        class="wpuf-relative wpuf-inline-flex wpuf-items-center wpuf-rounded-l-md wpuf-px-2 wpuf-py-2 wpuf-text-gray-400 wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 hover:wpuf-bg-gray-50 focus:wpuf-z-20 focus:outline-offset-0">
                        <span class="wpuf-sr-only">Previous</span>
                        <svg class="wpuf-h-5 wpuf-w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <button
                        v-for="page in pages"
                        @click="[emit('changePageTo', page.name)]"
                        :key="page.name"
                        :class="currentPage === page.name ? 'wpuf-bg-indigo-600 wpuf-text-white hover:wpuf-bg-indigo-700' : ''"
                        class="wpuf-relative wpuf-items-center wpuf-px-4 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900 wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 hover:wpuf-bg-gray-50 focus:wpuf-z-20 focus:outline-offset-0 wpuf-inline-flex">
                        {{ page.name }}
                    </button>
                    <button
                        @click="goToLastPage"
                        :disabled="isInLastPage"
                        :class="isInLastPage ? 'wpuf-bg-gray-50 wpuf-cursor-not-allowed' : ''"
                        class="wpuf-relative wpuf-inline-flex wpuf-items-center wpuf-rounded-r-md wpuf-px-2 wpuf-py-2 wpuf-text-gray-400 wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 hover:wpuf-bg-gray-50 focus:wpuf-z-20 focus:outline-offset-0">
                        <span class="wpuf-sr-only">Next</span>
                        <svg class="wpuf-h-5 wpuf-w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </nav>
            </div>
        </div>
    </div>
</template>
