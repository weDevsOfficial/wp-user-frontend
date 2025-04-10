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
      &nbsp;
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

  <div class="wpuf-mt-8 wpuf-flow-root">
    <div class="wpuf--mx-4 wpuf--my-2 wpuf-overflow-x-auto sm:wpuf--mx-6 lg:wpuf--mx-8">
      <div class="wpuf-inline-block wpuf-min-w-full wpuf-py-2 wpuf-align-middle sm:wpuf-px-6 lg:wpuf-px-8">
        <div class="wpuf-overflow-hidden wpuf-shadow wpuf-border wpuf-border-gray-200 sm:wpuf-rounded-lg">
          <table class="wpuf-min-w-full wpuf-divide-y wpuf-divide-gray-200">
            <thead>
            <tr>
              <th scope="col" class="wpuf-py-3.5 wpuf-pl-4 wpuf-pr-3 wpuf-text-left wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900 sm:wpuf-pl-6">
                <input
                    type="checkbox"
                    class="!wpuf-mt-0 !wpuf-mr-2 wpuf-h-4 wpuf-w-4 !wpuf-shadow-none checked:!wpuf-shadow-none focus:checked:!wpuf-shadow-primary focus:checked:!wpuf-shadow-none !wpuf-border-gray-300 checked:!wpuf-border-primary before:checked:!wpuf-bg-white hover:checked:!wpuf-bg-primary focus:!wpuf-ring-transparent focus:checked:!wpuf-ring-transparent hover:checked:!wpuf-ring-transparent focus:checked:!wpuf-bg-primary focus:wpuf-shadow-primary checked:focus:!wpuf-bg-primary checked:hover:wpuf-bg-primary checked:!wpuf-bg-primary before:!wpuf-content-none wpuf-rounded" />
                {{ __( 'Form Name', 'wp-user-frontend' ) }}
              </th>
              <th scope="col" class="wpuf-px-3 wpuf-py-3.5 wpuf-text-left wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900">{{ __( 'Post Type', 'wp-user-frontend' ) }}</th>
              <th scope="col" class="wpuf-px-3 wpuf-py-3.5 wpuf-text-left wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900">{{ __( 'Status', 'wp-user-frontend' ) }}</th>
              <th scope="col" class="wpuf-px-3 wpuf-py-3.5 wpuf-text-left wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900">{{ __( 'Shortcode', 'wp-user-frontend' ) }}</th>
              <th scope="col" class="wpuf-px-3 wpuf-py-3.5 wpuf-text-left wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900">{{ __( 'Guest Post', 'wp-user-frontend' ) }}</th>
              <th scope="col" class="wpuf-px-3 wpuf-py-3.5 wpuf-text-left wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M8.60386 3.59776C8.95919 2.13408 11.0408 2.13408 11.3961 3.59776C11.6257 4.54327 12.709 4.99198 13.5398 4.48571C14.8261 3.70199 16.298 5.17392 15.5143 6.46015C15.008 7.29105 15.4567 8.37431 16.4022 8.60386C17.8659 8.95919 17.8659 11.0408 16.4022 11.3961C15.4567 11.6257 15.008 12.709 15.5143 13.5398C16.298 14.8261 14.8261 16.298 13.5398 15.5143C12.709 15.008 11.6257 15.4567 11.3961 16.4022C11.0408 17.8659 8.95919 17.8659 8.60386 16.4022C8.37431 15.4567 7.29105 15.008 6.46016 15.5143C5.17392 16.298 3.70199 14.8261 4.48571 13.5398C4.99198 12.709 4.54327 11.6257 3.59776 11.3961C2.13408 11.0408 2.13408 8.95919 3.59776 8.60386C4.54327 8.37431 4.99198 7.29105 4.48571 6.46015C3.70199 5.17392 5.17392 3.70199 6.46015 4.48571C7.29105 4.99198 8.37431 4.54327 8.60386 3.59776Z" stroke="#1F2937" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                  <path d="M12.5 10C12.5 11.3807 11.3807 12.5 10 12.5C8.61929 12.5 7.5 11.3807 7.5 10C7.5 8.61929 8.61929 7.5 10 7.5C11.3807 7.5 12.5 8.61929 12.5 10Z" stroke="#1F2937" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </th>
            </tr>
            </thead>
            <tbody class="wpuf-divide-y wpuf-divide-gray-200 wpuf-bg-white">
            <tr>
              <td class="wpuf-whitespace-nowrap wpuf-py-4 wpuf-pl-4 wpuf-pr-3 wpuf-text-sm wpuf-font-medium wpuf-text-gray-900 sm:wpuf-pl-6">Article Post Forms</td>
              <td class="wpuf-whitespace-nowrap wpuf-px-3 wpuf-py-4 wpuf-text-sm wpuf-text-gray-500">Post</td>
              <td class="wpuf-whitespace-nowrap wpuf-px-3 wpuf-py-4 wpuf-text-sm wpuf-text-gray-500">Published</td>
              <td class="wpuf-whitespace-nowrap wpuf-px-3 wpuf-py-4 wpuf-text-sm wpuf-text-gray-500">[wpuf_form id=”141”]</td>
              <td class="wpuf-whitespace-nowrap wpuf-px-3 wpuf-py-4 wpuf-text-sm wpuf-text-gray-500">Toggle</td>
              <td class="wpuf-whitespace-nowrap wpuf-px-3 wpuf-py-4 wpuf-text-sm wpuf-text-gray-500">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M5 12H5.01M12 12H12.01M19 12H19.01M6 12C6 12.5523 5.55228 13 5 13C4.44772 13 4 12.5523 4 12C4 11.4477 4.44772 11 5 11C5.55228 11 6 11.4477 6 12ZM13 12C13 12.5523 12.5523 13 12 13C11.4477 13 11 12.5523 11 12C11 11.4477 11.4477 11 12 11C12.5523 11 13 11.4477 13 12ZM20 12C20 12.5523 19.5523 13 19 13C18.4477 13 18 12.5523 18 12C18 11.4477 18.4477 11 19 11C19.5523 11 20 11.4477 20 12Z" stroke="#94A3B8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </td>
            </tr>

            <!-- More people... -->
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

</template>
