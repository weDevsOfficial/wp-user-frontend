<script>
import Header from './Header.vue';
import Empty from './subscriptions/Empty.vue';

export default {
    name: 'Subscriptions',
    components: {Empty, Header}
};
</script>

<template>
    <Header />

    <div class="wpuf-flex wpuf-flex-row wpuf-mt-12">
        <div class="wpuf-basis-1/4 wpuf-border-r-2 wpuf-border-zinc-300">
            <h2>Menu</h2>
        </div>
        <div class="wpuf-basis-1/2">
            <Empty />
        </div>
    </div>
</template>
