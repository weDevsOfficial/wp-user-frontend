<script setup>
import Header from './Header.vue';
import {__} from '@wordpress/i18n';
import {ref} from 'vue';

const newFormUrl = wpuf_admin_script.admin_url + 'admin.php?page=wpuf-post-forms&action=add-new';
// store only counts without 0 values
const postCounts = wpuf_forms_list.post_counts;

const currentTab = ref( 'all' );

</script>

<template>
  <Header utm="wpuf-form-builder" />
  <div class="wpuf-flex wpuf-justify-between wpuf-items-center">
    <h3 class="wpuf-text-2xl wpuf-font-bold">{{ __( 'Post Forms', 'wp-user-frontend' ) }}</h3>
    <div>
      <a
        :href="newFormUrl"
        class="wpuf-rounded-md wpuf-text-center wpuf-bg-primary wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-bg-primaryHover hover:wpuf-text-white focus:wpuf-bg-primaryHover focus:wpuf-text-white">
      <span class="dashicons dashicons-plus-alt2"></span>
      &nbsp;&nbsp;
      {{ __( 'Add New ', 'wp-user-frontend' ) }}
    </a>
    </div>
  </div>
  <div class="wpuf-flex">
    <a
        v-for="(value, key) in postCounts"
        :key="key"
        @click="currentTab = key"
        href="#"
        :class = "currentTab === key ? 'wpuf-border-primary wpuf-text-primary' : 'wpuf-border-transparent wpuf-text-gray-500'"
        class="hover:wpuf-border-primary hover:wpuf-text-primary wpuf-flex wpuf-whitespace-nowrap wpuf-border-b-2 wpuf-py-4 wpuf-px-1 wpuf-font-medium wpuf-mr-8 focus:wpuf-shadow-none wpuf-transition-all">
      {{ value.label }}
      <span class="wpuf-bg-gray-100 wpuf-text-gray-900 wpuf-ml-3 wpuf-rounded-full wpuf-py-0.5 wpuf-px-2.5 wpuf-text-xs wpuf-font-medium md:wpuf-inline-block">{{ value.count }}</span>
    </a>
  </div>
</template>
