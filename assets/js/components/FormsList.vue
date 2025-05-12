<script setup>
import Header from './Header.vue';
import {__} from '@wordpress/i18n';
import {ref, onMounted, computed, watch} from 'vue';
import _ from 'lodash';
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue';
import {HollowDotsSpinner} from 'epic-spinners';

// store only counts without 0 values
const postCounts = wpuf_forms_list.post_counts;
const postType = wpuf_forms_list.post_type ? wpuf_forms_list.post_type : 'wpuf_forms';
const formType = postType === 'wpuf_forms' ? 'post' : 'profile';

const newFormUrl = wpuf_admin_script.admin_url + 'admin.php?page=wpuf-' + formType + '-forms&action=add-new';
const blankImg = wpuf_admin_script.asset_url + '/images/form-blank-state.svg';

const currentTab = ref('any');
const forms = ref([]);
const loading = ref(true);
const currentPage = ref(1);
const perPage = ref(10);
const totalPages = ref(0);
const searchTerm = ref('');
const selectAllChecked = ref(false);
const selectedForms = ref([]);
const selectedBulkAction = ref('');

// Debounced search handler
const debouncedFetchForms = _.debounce((page, status, search) => {
  fetchForms(page, status, search);
}, 500);

const fetchForms = async (page = 1, status = 'any', search = '') => {
  try {
    loading.value = true;
    currentPage.value = page;
    let apiUrl = `/wp-json/wpuf/v1/wpuf_form?page=${page}&per_page=${perPage.value}&status=${status}&post_type=${postType}`;
    if (search) {
      apiUrl += `&s=${search}`;
    }
    const response = await fetch(apiUrl,
     {
      headers: {
        'X-WP-Nonce': wpuf_forms_list.rest_nonce,
      },
    });
    const data = await response.json();

    if (data.success) {
      forms.value = data.result;
      if (data.pagination) {
        totalPages.value = data.pagination.total_pages;
      } else {
        totalPages.value = 0;
      }

      // Reset selection when forms data changes
      selectedForms.value = [];
      selectAllChecked.value = false;
    } else {
      forms.value = [];
      totalPages.value = 0;
      selectedForms.value = [];
      selectAllChecked.value = false;
    }
  } catch (error) {
    console.error('Error fetching forms:', error);
    forms.value = [];
    totalPages.value = 0;
    selectedForms.value = [];
    selectAllChecked.value = false;
  } finally {
    loading.value = false;
  }
};

const changePage = (page) => {
  if (page < 1 || page > totalPages.value || page === currentPage.value) {
    return;
  }
  fetchForms(page, currentTab.value, searchTerm.value);
};

// Select/Deselect All handler
const handleSelectAll = () => {
  if (selectAllChecked.value) {
    selectedForms.value = forms.value.map(form => form.ID);
  } else {
    selectedForms.value = [];
  }
};

// Update selectAllChecked based on individual selections
watch(selectedForms, (newSelection) => {
  if (forms.value.length > 0 && newSelection.length === forms.value.length) {
    selectAllChecked.value = true;
  } else {
    selectAllChecked.value = false;
  }
});

// Watch for changes in currentTab and fetch forms accordingly
watch(currentTab, (newTab) => {
  fetchForms(1, newTab, searchTerm.value);
});

// Watch for changes in searchTerm and fetch forms accordingly
watch(searchTerm, (newSearch) => {
  debouncedFetchForms(1, currentTab.value, newSearch);
});

const paginationRange = computed(() => {
  const range = [];
  const delta = 2; // Number of pages to show before and after current page

  for (
    let i = Math.max(1, currentPage.value - delta);
    i <= Math.min(totalPages.value, currentPage.value + delta);
    i++
  ) {
    range.push(i);
  }

  return range;
});

// Add computed property for menu items
const menuItems = computed(() => {
  if (currentTab.value === 'trash') {
    return [
      {
        label: __('Restore', 'wp-user-frontend'),
        action: 'restore',
        class: 'wpuf-text-gray-900',
        activeClass: 'wpuf-bg-primary wpuf-text-white'
      },
      {
        label: __('Delete Permanently', 'wp-user-frontend'),
        action: 'delete',
        class: 'wpuf-text-red-600',
        activeClass: 'wpuf-bg-red-500 wpuf-text-white'
      }
    ];
  }
  return [
    {
      label: __('Edit', 'wp-user-frontend'),
      action: 'edit',
      class: 'wpuf-text-gray-900',
      activeClass: 'wpuf-bg-primary wpuf-text-white'
    },
    {
      label: __('Duplicate', 'wp-user-frontend'),
      action: 'duplicate',
      class: 'wpuf-text-gray-900',
      activeClass: 'wpuf-bg-primary wpuf-text-white'
    },
    {
      label: __('Trash', 'wp-user-frontend'),
      action: 'trash',
      class: 'wpuf-text-red-600',
      activeClass: 'wpuf-bg-red-500 wpuf-text-white'
    }
  ];
});

const copyToClipboard = async (shortcode, $event) => {
  const eventElement = $event.target;
  const buttonElement = eventElement.closest('button');
  const codeElement = buttonElement.previousElementSibling;

  try {
    await navigator.clipboard.writeText(shortcode);

    if (codeElement && codeElement.tagName === 'CODE') {
      codeElement.textContent = 'Copied!';
      setTimeout(() => {
        codeElement.textContent = shortcode;
      }, 2000);
    }
  } catch (err) {
    console.error('Failed to copy shortcode: ', err);
  }
};

const getShortcode = (formId) => {
  return `[wpuf_form id="${formId}"]`;
};

const handleEdit = (formId) => {
  // Construct the edit URL
  const editUrl = `${wpuf_admin_script.admin_url}admin.php?page=wpuf-post-forms&action=edit&id=${formId}`;
  // Navigate to the edit page
  window.location.href = editUrl;
};

const handleDuplicate = (formId) => {
  // Generate WordPress nonce for security
  const wpnonce = wpuf_forms_list.bulk_nonce;
  // Construct the base admin URL with nonce
  const adminUrl = `${wpuf_admin_script.admin_url}admin.php?page=wpuf-post-forms&id=${formId}&_wpnonce=${wpnonce}`;
  // Construct the duplicate URL
  const duplicateUrl = `${adminUrl}&action=duplicate`;
  // Redirect to the duplicate URL
  window.location.href = duplicateUrl;
};

const handleTrash = (formId) => {
  // Generate WordPress nonce for security
  const wpnonce = wpuf_forms_list.bulk_nonce;
  // Construct the base admin URL with nonce
  const adminUrl = `${wpuf_admin_script.admin_url}admin.php?page=wpuf-post-forms&id=${formId}&_wpnonce=${wpnonce}`;
  // Construct the trash URL
  const trashUrl = `${adminUrl}&action=trash`;
  // Redirect to the trash URL
  window.location.href = trashUrl;
};

// Add new handler for restore action
const handleRestore = (formId) => {
  // Generate WordPress nonce for security
  const wpnonce = wpuf_forms_list.bulk_nonce;
  // Construct the base admin URL with nonce
  const adminUrl = `${wpuf_admin_script.admin_url}admin.php?page=wpuf-post-forms&id=${formId}&_wpnonce=${wpnonce}`;
  // Construct the restore URL
  const restoreUrl = `${adminUrl}&action=restore`;
  // Redirect to the restore URL
  window.location.href = restoreUrl;
};

// Add new handler for delete permanently action
const handleDelete = (formId) => {
  // Show confirmation dialog
  if (!confirm(__('Are you sure you want to delete this form permanently? This action cannot be undone.', 'wp-user-frontend'))) {
    return;
  }

  // Generate WordPress nonce for security
  const wpnonce = wpuf_forms_list.bulk_nonce;
  // Construct the base admin URL with nonce
  const adminUrl = `${wpuf_admin_script.admin_url}admin.php?page=wpuf-post-forms&id=${formId}&_wpnonce=${wpnonce}`;
  // Construct the delete URL
  const deleteUrl = `${adminUrl}&action=delete`;
  // Redirect to the delete URL
  window.location.href = deleteUrl;
};

const handleBulkAction = () => {
  if (!selectedBulkAction.value || selectedForms.value.length === 0) {
    return;
  }

  // Construct the base URL
  let url = `${wpuf_admin_script.admin_url}admin.php?page=wpuf-post-forms`;

  // Add search parameter if exists
  if (searchTerm.value) {
    url += `&s=${encodeURIComponent(searchTerm.value)}`;
  }

  // Add nonce
  url += `&_wpnonce=${wpuf_forms_list.bulk_nonce}`;

  // Add referer
  const referer = encodeURIComponent(window.location.href);
  url += `&_wp_http_referer=${referer}`;

  // Add action and bulk action
  url += `&action=${selectedBulkAction.value}&bulk_action=Apply`;

  // Add current page
  url += `&paged=${currentPage.value}`;

  // Add post status if in trash
  if (currentTab.value === 'trash') {
    url += '&post_status=trash';
  }

  // Add selected form IDs
  selectedForms.value.forEach(formId => {
    url += `&post[]=${formId}`;
  });

  // Add action2 (same as action)
  url += `&action2=${selectedBulkAction.value}`;

  // Navigate to the constructed URL
  window.location.href = url;
};

const openModal = (event) => {
  event.preventDefault();

  // Trigger the jQuery modal
  if (window.jQuery) {
    window.jQuery('.wpuf-form-template-modal').show();
    window.jQuery('#wpbody-content .wrap').hide();
  } else {
    // Fallback if jQuery is not available
    window.location.href = newFormUrl;
  }
};

onMounted(() => {
  fetchForms(1, currentTab.value, searchTerm.value);
});

</script>

<template>
  <Header utm="wpuf-form-builder" />
  <div class="wpuf-flex wpuf-justify-between wpuf-items-center wpuf-mt-9">
    <h3 class="wpuf-text-2xl wpuf-font-bold wpuf-m-0 wpuf-p-0 wpuf-leading-none">{{ __( 'Post Forms', 'wp-user-frontend' ) }}</h3>
    <div>
      <a
        @click="openModal"
        class="new-wpuf-form wpuf-rounded-md wpuf-text-center wpuf-bg-primary wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-bg-primaryHover hover:wpuf-text-white focus:wpuf-bg-primaryHover focus:wpuf-text-white focus:wpuf-shadow-none hover:wpuf-cursor-pointer">
      <span class="dashicons dashicons-plus-alt2"></span>
      &nbsp;
      {{ __( 'Add New ', 'wp-user-frontend' ) }}
      </a>
    </div>
  </div>
  <div class="wpuf-flex wpuf-mt-9">
    <span
        v-for="(value, key) in postCounts"
        :key="key"
        @click="key === 'all' ? currentTab = 'any' : currentTab = key"
        :class="currentTab === key || ( key === 'all' && currentTab === 'any' ) ? 'wpuf-border-primary wpuf-text-primary' : 'wpuf-border-transparent wpuf-text-gray-500'"
        class="wpuf-flex hover:wpuf-border-primary hover:wpuf-text-primary wpuf-whitespace-nowrap wpuf-py-4 wpuf-px-1 wpuf-border-b-2 wpuf-font-medium wpuf-text-sm wpuf-mr-8 focus:wpuf-outline-none focus:wpuf-shadow-none wpuf-transition-all hover:wpuf-cursor-pointer">
      {{ value.label }}
      <span class="wpuf-bg-gray-100 wpuf-text-gray-900 wpuf-ml-3 wpuf-rounded-full wpuf-py-0.5 wpuf-px-2.5 wpuf-text-xs wpuf-font-medium md:wpuf-inline-block">{{ value.count }}</span>
    </span>
  </div>
  <div class="wpuf-flex wpuf-justify-between wpuf-my-8">
    <div class="wpuf-flex">
      <select
        v-model="selectedBulkAction"
        class="wpuf-block wpuf-w-full wpuf-min-w-full !wpuf-py-[10px] !wpuf-px-[14px] wpuf-text-gray-700 wpuf-font-normal !wpuf-leading-none !wpuf-shadow-sm wpuf-border !wpuf-border-gray-300 !wpuf-rounded-[6px] focus:!wpuf-ring-transparent focus:checked:!wpuf-ring-transparent hover:checked:!wpuf-ring-transparent hover:wpuf-text-gray-700 !wpuf-text-base !leading-6">
        <option value="">{{ __( 'Bulk actions', 'wp-user-frontend' ) }}</option>
        <option v-if="currentTab !== 'trash'" value="trash">{{ __( 'Move to trash', 'wp-user-frontend' ) }}</option>
        <option v-if="currentTab === 'trash'" value="restore">{{ __( 'Restore', 'wp-user-frontend' ) }}</option>
        <option v-if="currentTab === 'trash'" value="delete">{{ __( 'Delete Permanently', 'wp-user-frontend' ) }}</option>
      </select>
      <button
        @click="handleBulkAction"
        :disabled="!selectedBulkAction || selectedForms.length === 0"
        :class="{
          'wpuf-opacity-50 wpuf-cursor-not-allowed': !selectedBulkAction || selectedForms.length === 0
        }"
        class="wpuf-ml-4 wpuf-inline-flex wpuf-items-center wpuf-justify-center wpuf-rounded-md wpuf-border wpuf-border-transparent wpuf-bg-primary wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-white hover:wpuf-bg-primaryHover focus:wpuf-bg-primaryHover focus:wpuf-text-white">
        {{ __( 'Apply', 'wp-user-frontend' ) }}
      </button>
    </div>
    <div class="wpuf-form-search-box">
      <div class="wpuf-relative">
        <input
            type="text"
            v-model="searchTerm"
            placeholder="Search Forms"
            class="wpuf-block wpuf-min-w-full !wpuf-m-0 !wpuf-leading-none !wpuf-py-[10px] !wpuf-px-[14px] wpuf-text-gray-700 !wpuf-shadow-sm placeholder:wpuf-text-gray-400 wpuf-border !wpuf-border-gray-300 !wpuf-rounded-[6px] wpuf-max-w-full focus:!wpuf-ring-transparent"
        />
        <span class="wpuf-absolute wpuf-top-0 wpuf-right-0 wpuf-p-[10px]">
          <svg class="wpuf-h-5 wpuf-w-5 wpuf-text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
          </svg>
        </span>
      </div>
    </div>
  </div>
    <div v-if="loading" class="wpuf-flex wpuf-h-16 wpuf-items-center wpuf-justify-center">
        <hollow-dots-spinner
            :animation-duration="1000"
            :dot-size="20"
            :dots-num="3"
            :color="'#7DC442'"
        />
    </div>
    <div v-else-if="forms.length === 0 && searchTerm !== ''">
        <div class="wpuf-text-center">
            <h2 class="wpuf-text-lg wpuf-text-gray-800 wpuf-mt-8">
                {{ __( 'No forms found matching your search!', 'wp-user-frontend' ) }}
            </h2>
        </div>
    </div>
    <div v-else-if="forms.length === 0 && currentTab === 'any' && searchTerm === ''">
        <div class="wpuf-grid wpuf-min-h-full wpuf-bg-white wpuf-px-6 wpuf-py-24 sm:wpuf-py-32 lg:wpuf-px-8">
            <div class="wpuf-flex wpuf-flex-col wpuf-items-center">
                <img :src="blankImg" alt="">
                <h2 class="wpuf-text-lg wpuf-text-gray-800 wpuf-mt-8">
                    {{ __( 'No Post Forms Created Yet', 'wp-user-frontend' ) }}
                </h2>
                <p class="wpuf-text-sm wpuf-text-gray-500 wpuf-mt-8 wpuf-mb-10">
                    {{
                        __( 'Start building a post form to let users submit content from the frontend.', 'wp-user-frontend' )
                    }}
                </p>

                <a
                    @click="openModal"
                    class="new-wpuf-form wpuf-rounded-md wpuf-text-center wpuf-bg-primary wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-bg-primaryHover hover:wpuf-text-white focus:wpuf-bg-primaryHover focus:wpuf-text-white focus:wpuf-shadow-none hover:wpuf-cursor-pointer">
                    <span class="dashicons dashicons-plus-alt2"></span>
                    &nbsp;
                    {{ __( 'Add New ', 'wp-user-frontend' ) }}
                </a>
            </div>
        </div>
    </div>
    <div v-else-if="forms.length === 0 && currentTab !== 'any' && searchTerm === ''">
        <div class="wpuf-grid wpuf-min-h-full wpuf-bg-white wpuf-px-6 wpuf-py-24 sm:wpuf-py-32 lg:wpuf-px-8">
            <div class="wpuf-flex wpuf-flex-col wpuf-items-center">
                <h2 class="wpuf-text-lg wpuf-text-gray-800 wpuf-mt-8">
                    {{ __( 'No Items Here!', 'wp-user-frontend' ) }}
                </h2>
            </div>
        </div>
    </div>
    <div
      v-else
      class="wpuf-flow-root">
    <div class="wpuf--mx-4 wpuf--my-2 sm:wpuf--mx-6 lg:wpuf--mx-8">
      <div class="wpuf-inline-block wpuf-min-w-full wpuf-py-2 wpuf-align-middle sm:wpuf-px-6 lg:wpuf-px-8">
        <div class="wpuf-shadow wpuf-border wpuf-border-gray-200 sm:wpuf-rounded-lg">
          <table class="wpuf-min-w-full wpuf-divide-y wpuf-divide-gray-200">
            <thead>
            <tr>
              <th scope="col" class="wpuf-py-3.5 wpuf-pl-4 wpuf-pr-3 wpuf-text-left wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900 sm:wpuf-pl-6">
                <input
                    type="checkbox"
                    v-model="selectAllChecked"
                    @change="handleSelectAll"
                    :indeterminate="selectedForms.length > 0 && selectedForms.length < forms.length"
                    class="!wpuf-mt-0 !wpuf-mr-2 wpuf-h-4 wpuf-w-4 !wpuf-shadow-none checked:!wpuf-shadow-none focus:checked:!wpuf-shadow-primary focus:checked:!wpuf-shadow-none !wpuf-border-gray-300 checked:!wpuf-border-primary before:checked:!wpuf-bg-white hover:checked:!wpuf-bg-primary focus:!wpuf-ring-transparent focus:checked:!wpuf-ring-transparent hover:checked:!wpuf-ring-transparent focus:checked:!wpuf-bg-primary focus:wpuf-shadow-primary checked:focus:!wpuf-bg-primary checked:hover:wpuf-bg-primary checked:!wpuf-bg-primary before:!wpuf-content-none wpuf-rounded" />
                {{ __( 'Form Name', 'wp-user-frontend' ) }}
              </th>
              <th scope="col" class="wpuf-px-3 wpuf-py-3.5 wpuf-text-left wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900">{{ __( 'Post Type', 'wp-user-frontend' ) }}</th>
              <th scope="col" class="wpuf-px-3 wpuf-py-3.5 wpuf-text-left wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900">{{ __( 'Post Status', 'wp-user-frontend' ) }}</th>
              <th scope="col" class="wpuf-px-3 wpuf-py-3.5 wpuf-text-left wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900">{{ __( 'Shortcode', 'wp-user-frontend' ) }}</th>
              <th scope="col" class="wpuf-px-3 wpuf-py-3.5 wpuf-text-left wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900">{{ __( 'Guest Post', 'wp-user-frontend' ) }}</th>
              <th scope="col" class="wpuf-px-3 wpuf-py-3.5 wpuf-text-left wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900">
                <span class="wpuf-sr-only">Menu</span>
              </th>
            </tr>
            </thead>
            <tbody class="wpuf-divide-y wpuf-divide-gray-200">
              <tr v-for="form in forms" :key="form.ID" class="wpuf-relative wpuf-group">
                <td class="wpuf-py-4 wpuf-pl-4 wpuf-pr-3 wpuf-text-sm wpuf-font-medium wpuf-text-gray-900 sm:wpuf-pl-6">
                  <input
                    type="checkbox"
                    :value="form.ID"
                    v-model="selectedForms"
                    class="!wpuf-mt-0 !wpuf-mr-2 wpuf-h-4 wpuf-w-4 !wpuf-shadow-none checked:!wpuf-shadow-none focus:checked:!wpuf-shadow-primary focus:checked:!wpuf-shadow-none !wpuf-border-gray-300 checked:!wpuf-border-primary before:checked:!wpuf-bg-white hover:checked:!wpuf-bg-primary focus:!wpuf-ring-transparent focus:checked:!wpuf-ring-transparent hover:checked:!wpuf-ring-transparent focus:checked:!wpuf-bg-primary focus:wpuf-shadow-primary checked:focus:!wpuf-bg-primary checked:hover:wpuf-bg-primary checked:!wpuf-bg-primary before:!wpuf-content-none wpuf-rounded" />
                  <span
                      @click="handleEdit(form.ID)"
                      class="hover:wpuf-cursor-pointer">{{ form.post_title }}</span>
                  <span
                    v-if="form.form_status === 'draft'"
                    class="wpuf-text-gray-400">
                    — {{ __( 'Draft', 'wp-user-frontend' ) }}
                  </span>
                </td>
                <td class="wpuf-whitespace-nowrap wpuf-px-3 wpuf-py-4 wpuf-text-sm wpuf-text-gray-500">
                  {{ form.settings_post_type }}
                </td>
                <td class="wpuf-whitespace-nowrap wpuf-px-3 wpuf-py-4 wpuf-text-sm">
                  <span
                    :class="{
                      'wpuf-bg-emerald-50 wpuf-border-emerald-200 wpuf-text-emerald-800': form.post_status === 'publish',
                      'wpuf-bg-yellow-100 wpuf-text-yellow-800': form.post_status === 'pending',
                      'wpuf-bg-indigo-50 wpuf-border-indigo-200 wpuf-text-purple-800': form.post_status === 'private',
                      'wpuf-bg-gray-100 wpuf-border-gray-200 wpuf-text-gray-800': form.post_status === 'draft'
                    }"
                    class="wpuf-inline-flex wpuf-items-center wpuf-py-[2px] wpuf-px-[12px] wpuf-rounded-[5px] wpuf-text-xs wpuf-font-medium wpuf-border">
                    {{ form.post_status === 'publish' ? 'Published' :
                       form.post_status === 'pending' ? 'Pending Review' :
                       form.post_status === 'private' ? 'Private' : 'Draft' }}
                  </span>
                </td>
                <td class="wpuf-whitespace-nowrap wpuf-px-3 wpuf-py-4 wpuf-text-sm wpuf-font-medium wpuf-text-gray-500">
                  <div class="wpuf-flex wpuf-items-center">
                    <code class="wpuf-mr-2 wpuf-bg-gray-50 wpuf-border wpuf-border-gray-300 wpuf-rounded-md wpuf-shadow-sm wpuf-py-[10px] wpuf-px-[14px]">{{ getShortcode(form.ID) }}</code>
                    <button
                      @click="copyToClipboard(getShortcode(form.ID), $event)"
                      class="wpuf-text-gray-500 hover:wpuf-text-gray-700 wpuf-focus:outline-none"
                      title="Copy shortcode">
                        <svg
                            class="wpuf-stroke-gray-400"
                            width="20"
                            height="20"
                            viewBox="0 0 20 20"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path d="M13.125 14.375V17.1875C13.125 17.7053 12.7053 18.125 12.1875 18.125H4.0625C3.54473 18.125 3.125 17.7053 3.125 17.1875V6.5625C3.125 6.04473 3.54473 5.625 4.0625 5.625H5.625C6.05089 5.625 6.46849 5.6605 6.875 5.7287M13.125 14.375H15.9375C16.4553 14.375 16.875 13.9553 16.875 13.4375V9.375C16.875 5.65876 14.1721 2.5738 10.625 1.9787C10.2185 1.9105 9.80089 1.875 9.375 1.875H7.8125C7.29473 1.875 6.875 2.29473 6.875 2.8125V5.7287M13.125 14.375H7.8125C7.29473 14.375 6.875 13.9553 6.875 13.4375V5.7287M16.875 11.25V9.6875C16.875 8.1342 15.6158 6.875 14.0625 6.875H12.8125C12.2947 6.875 11.875 6.45527 11.875 5.9375V4.6875C11.875 3.1342 10.6158 1.875 9.0625 1.875H8.125" stroke="#6B7280" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                  </div>
                </td>
                <td class="wpuf-whitespace-nowrap wpuf-px-3 wpuf-py-4 wpuf-text-sm wpuf-font-medium wpuf-text-gray-500">
                  <svg
                      v-if="form.settings_guest_post"
                      xmlns="http://www.w3.org/2000/svg"
                      viewBox="0 0 16 16"
                      class="wpuf-size-4 wpuf-w-6">
                    <path fill="#059669" fill-rule="evenodd" d="M12.416 3.376a.75.75 0 0 1 .208 1.04l-5 7.5a.75.75 0 0 1-1.154.114l-3-3a.75.75 0 0 1 1.06-1.06l2.353 2.353 4.493-6.74a.75.75 0 0 1 1.04-.207Z" clip-rule="evenodd" />
                  </svg>
                  <svg
                      v-else
                      xmlns="http://www.w3.org/2000/svg"
                      viewBox="0 0 16 16"
                      class="wpuf-size-4 wpuf-w-6">
                    <path fill="#ef4444" d="M5.28 4.22a.75.75 0 0 0-1.06 1.06L6.94 8l-2.72 2.72a.75.75 0 1 0 1.06 1.06L8 9.06l2.72 2.72a.75.75 0 1 0 1.06-1.06L9.06 8l2.72-2.72a.75.75 0 0 0-1.06-1.06L8 6.94 5.28 4.22Z" />
                  </svg>
                </td>
                <td class="wpuf-whitespace-nowrap wpuf-px-3 wpuf-py-4 wpuf-text-sm wpuf-font-medium wpuf-text-gray-500 wpuf-text-right">
                  <Menu as="div" class="wpuf-relative wpuf-inline-block wpuf-text-left">
                    <div>
                      <MenuButton class="wpuf-inline-flex wpuf-w-full wpuf-justify-center wpuf-rounded-md wpuf-px-2 wpuf-py-2 wpuf-text-sm wpuf-font-medium wpuf-text-gray-700 hover:wpuf-bg-gray-50 focus:wpuf-outline-none focus-visible:wpuf-ring-2 focus-visible:wpuf-ring-white focus-visible:wpuf-ring-opacity-75">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="wpuf-h-5 wpuf-w-5 wpuf-text-gray-400 hover:wpuf-text-gray-600">
                           <path d="M5 12H5.01M12 12H12.01M19 12H19.01M6 12C6 12.5523 5.55228 13 5 13C4.44772 13 4 12.5523 4 12C4 11.4477 4.44772 11 5 11C5.55228 11 6 11.4477 6 12ZM13 12C13 12.5523 12.5523 13 12 13C11.4477 13 11 12.5523 11 12C11 11.4477 11.4477 11 12 11C12.5523 11 13 11.4477 13 12ZM20 12C20 12.5523 19.5523 13 19 13C18.4477 13 18 12.5523 18 12C18 11.4477 18.4477 11 19 11C19.5523 11 20 11.4477 20 12Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                      </MenuButton>
                    </div>

                    <transition
                      enter-active-class="wpuf-transition wpuf-duration-100 wpuf-ease-out"
                      enter-from-class="wpuf-transform wpuf-scale-95 wpuf-opacity-0"
                      enter-to-class="wpuf-transform wpuf-scale-100 wpuf-opacity-100"
                      leave-active-class="wpuf-transition wpuf-duration-75 wpuf-ease-in"
                      leave-from-class="wpuf-transform wpuf-scale-100 wpuf-opacity-100"
                      leave-to-class="wpuf-transform wpuf-scale-95 wpuf-opacity-0"
                    >
                      <MenuItems class="wpuf-absolute wpuf-right-0 wpuf-mt-2 wpuf-w-40 wpuf-origin-top-right wpuf-divide-y wpuf-divide-gray-100 wpuf-rounded-md wpuf-bg-white wpuf-shadow-lg wpuf-ring-1 wpuf-ring-black wpuf-ring-opacity-5 focus:wpuf-outline-none wpuf-z-10">
                        <div class="wpuf-px-1 wpuf-py-1">
                          <MenuItem v-for="item in menuItems" :key="item.action" v-slot="{ active }">
                            <button
                              @click="item.action === 'edit' ? handleEdit(form.ID) :
                                      item.action === 'duplicate' ? handleDuplicate(form.ID) :
                                      item.action === 'trash' ? handleTrash(form.ID) :
                                      item.action === 'restore' ? handleRestore(form.ID) :
                                      handleDelete(form.ID)"
                              :class="[
                                active ? item.activeClass : item.class,
                                'wpuf-group wpuf-flex wpuf-w-full wpuf-items-center wpuf-rounded-md wpuf-px-2 wpuf-py-2 wpuf-text-sm',
                              ]"
                            >
                              {{ item.label }}
                            </button>
                          </MenuItem>
                        </div>
                      </MenuItems>
                    </transition>
                  </Menu>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="wpuf-flex wpuf-items-center wpuf-justify-center wpuf-mt-8">
          <nav class="wpuf-flex wpuf-items-center wpuf-w-full">
            <div>
              <button
                @click="changePage(currentPage - 1)"
                :disabled="currentPage === 1"
                :class="{ 'wpuf-cursor-not-allowed wpuf-opacity-50': currentPage === 1 }"
                class="wpuf-mr-3 wpuf-rounded-md wpuf-relative wpuf-inline-flex wpuf-items-center wpuf-text-sm wpuf-font-medium wpuf-text-gray-700 hover:wpuf-text-primary"
              >
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" clip-rule="evenodd" d="M7.70711 14.7071C7.31658 15.0976 6.68342 15.0976 6.2929 14.7071L2.29289 10.7071C1.90237 10.3166 1.90237 9.68342 2.29289 9.29289L6.29289 5.29289C6.68342 4.90237 7.31658 4.90237 7.70711 5.29289C8.09763 5.68342 8.09763 6.31658 7.70711 6.70711L5.41421 9L17 9C17.5523 9 18 9.44771 18 10C18 10.5523 17.5523 11 17 11L5.41421 11L7.70711 13.2929C8.09763 13.6834 8.09763 14.3166 7.70711 14.7071Z" fill="#94A3B8"/>
                </svg>
                &nbsp;
                {{ __('Previous', 'wp-user-frontend') }}
              </button>
            </div>

            <div class="wpuf-flex wpuf-items-center">
              <span
                v-for="page in paginationRange"
                :key="page"
                @click="changePage(page)"
                :class="[
                  page === currentPage
                    ? 'wpuf-text-primary wpuf-border-primary'
                    : 'wpuf-text-gray-500 wpuf-border-transparent',
                  'wpuf-relative wpuf-inline-flex wpuf-items-center wpuf-px-4 wpuf-py-2 wpuf-text-sm wpuf-font-medium wpuf-cursor-pointer wpuf-mx-1 wpuf-border-t-2 hover:wpuf-border-primary wpuf-transition-all'
                ]"
              >
                {{ page }}
              </span>
            </div>

            <div>
              <button
                @click="changePage(currentPage + 1)"
                :disabled="currentPage === totalPages"
                :class="{ 'wpuf-cursor-not-allowed wpuf-opacity-50': currentPage === totalPages }"
                class="wpuf-ml-3 wpuf-rounded-md wpuf-relative wpuf-inline-flex wpuf-items-center wpuf-text-sm wpuf-font-medium wpuf-text-gray-700 hover:wpuf-text-primary"
              >
                {{ __('Next', 'wp-user-frontend') }}
                &nbsp;
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" clip-rule="evenodd" d="M12.2929 5.29289C12.6834 4.90237 13.3166 4.90237 13.7071 5.29289L17.7071 9.29289C18.0976 9.68342 18.0976 10.3166 17.7071 10.7071L13.7071 14.7071C13.3166 15.0976 12.6834 15.0976 12.2929 14.7071C11.9024 14.3166 11.9024 13.6834 12.2929 13.2929L14.5858 11H3C2.44772 11 2 10.5523 2 10C2 9.44772 2.44772 9 3 9H14.5858L12.2929 6.70711C11.9024 6.31658 11.9024 5.68342 12.2929 5.29289Z" fill="#94A3B8"/>
                </svg>
              </button>
            </div>
          </nav>
        </div>
      </div>
    </div>
    </div>
</template>
