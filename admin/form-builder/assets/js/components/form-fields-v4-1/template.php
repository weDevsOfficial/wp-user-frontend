<div class="wpuf-px-6">
    <div
        class="wpuf-flex wpuf-rounded-lg wpuf-bg-white wpuf-outline wpuf--outline-1 wpuf--outline-offset-1 wpuf-outline-gray-300 wpuf-border wpuf-border-gray-200 wpuf-shadow">
        <input
            type="text"
            name="search"
            id="search"
            v-model="searched_fields"
            class="!wpuf-border-none !wpuf-rounded-lg wpuf-block wpuf-min-w-0 wpuf-grow wpuf-px-4 wpuf-py-1.5 wpuf-text-base wpuf-text-gray-900 placeholder:wpuf-text-gray-400 sm:wpuf-text-sm/6 !wpuf-shadow-none !wpuf-ring-transparent"
            placeholder="<?php esc_attr_e( 'Search Field', 'wp-user-frontend' ); ?>">
        <div class="wpuf-flex wpuf-py-1.5 wpuf-pr-1.5">
            <span class="wpuf-inline-flex wpuf-items-center wpuf-rounded wpuf-px-1 wpuf-font-sans wpuf-text-xs wpuf-text-gray-400">
                <svg
                    v-if="!searched_fields"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    class="wpuf-size-5 hover:wpuf-cursor-pointer wpuf-transition-all">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <svg
                    v-if="searched_fields"
                    @click="searched_fields = ''"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                    class="wpuf-size-5 hover:wpuf-cursor-pointer wpuf-transition-all">
                    <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                </svg>
            </span>
        </div>
    </div>
    <div class="wpuf-form-builder-form-fields">
        <template
            v-for="(section, index) in panel_sections">
            <div v-if="section.fields.length" class="panel-form-field-group clearfix">
                <h3
                    :class="section.show ? 'wpuf-text-green-600' : 'wpuf-text-gray-500'"
                    class="wpuf-flex wpuf-justify-between hover:wpuf-cursor-pointer wpuf-text-sm wpuf-my-4"
                    @click="panel_toggle(index)">
                    {{ section.title }}
                    <i :class="[section.show ? 'fa fa-angle-down wpuf-text-green-600' : 'fa fa-angle-right wpuf-text-gray-500']"></i>
                </h3>
                <div
                    v-show="section.show"
                    :id="'panel-form-field-buttons-' + section.id"
                    class="wpuf-field-button panel-form-field-buttons wpuf-grid wpuf-grid-cols-1 wpuf-gap-4 sm:wpuf-grid-cols-2">
                    <template v-for="field in section.fields">
                        <div
                            v-if="is_pro_feature(field)"
                            :data-form-field="field"
                            data-source="panel"
                            @click="alert_pro_feature(field)"
                            class="wpuf-relative wpuf-flex wpuf-items-center wpuf-rounded-lg wpuf-border wpuf-border-gray-200 wpuf-bg-white wpuf-shadow-sm hover:wpuf-border-gray-300 wpuf-p-3">
                            <div
                                v-if="field_settings[field].icon"
                                class="wpuf-shrink-0 wpuf-mr-2 wpuf-text-gray-400">
                                <img :src="asset_url + '/images/' + field_settings[field].icon + '.svg'" alt="">
                            </div>
                            <div class="wpuf-min-w-0 wpuf-flex-1">
                                <a href="#" class="focus:wpuf-outline-none focus:wpuf-shadow-none">
                                    <p class="wpuf-text-sm wpuf-font-medium wpuf-text-gray-400 wpuf-m-0">{{ field_settings[field].title }}</p>
                                </a>
                            </div>
                            <img src="<?php esc_attr_e( WPUF_ASSET_URI . '/images/crown.svg' ); ?>" alt="">
                        </div>
                        <div
                            v-else-if="is_failed_to_validate(field)"
                            :data-form-field="field"
                            data-source="panel"
                            @click="alert_invalidate_msg(field)"
                            class="wpuf-relative wpuf-flex wpuf-items-center wpuf-rounded-lg wpuf-border wpuf-border-gray-200 wpuf-bg-white wpuf-shadow-sm hover:wpuf-border-gray-300 wpuf-p-3">
                            <div
                                v-if="field_settings[field].icon"
                                class="wpuf-shrink-0 wpuf-mr-2">
                                <img :src="asset_url + '/images/' + field_settings[field].icon + '.svg'" alt="">
                            </div>
                            <div class="wpuf-min-w-0 wpuf-flex-1">
                                <a href="#" class="focus:wpuf-outline-none focus:wpuf-shadow-none">
                                    <p class="wpuf-text-sm wpuf-font-medium wpuf-text-gray-500 wpuf-m-0">{{ field_settings[field].title }}</p>
                                </a>
                            </div>
                        </div>
                        <div
                            v-else
                            :data-form-field="field"
                            data-source="panel"
                            @click="add_form_field(field)"
                            class="wpuf-field-button wpuf-relative wpuf-flex wpuf-items-center wpuf-rounded-lg wpuf-border wpuf-border-gray-200 wpuf-bg-white wpuf-shadow-sm wpuf-p-3 hover:wpuf-border-gray-300 hover:wpuf-cursor-pointer">
                            <div
                                v-if="field_settings[field].icon"
                                class="wpuf-shrink-0 wpuf-mr-2">
                                <img :src="asset_url + '/images/' + field_settings[field].icon + '.svg'" alt="">
                            </div>
                            <div class="wpuf-min-w-0 wpuf-flex-1">
                                <a href="#" class="focus:wpuf-outline-none focus:wpuf-shadow-none">
                                    <p class="wpuf-text-sm wpuf-font-medium wpuf-text-gray-500 wpuf-m-0">{{ field_settings[field].title }}</p>
                                </a>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>
</div>
