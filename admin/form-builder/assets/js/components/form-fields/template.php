<div class="wpuf-px-6">
    <div
        class="wpuf-flex wpuf-rounded-md wpuf-bg-white wpuf-outline wpuf--outline-1 wpuf--outline-offset-1 wpuf-outline-gray-300 wpuf-border wpuf-border-gray-200">
        <input
            type="text"
            name="search"
            id="search"
            v-model="searched_fields"
            class="!wpuf-border-none wpuf-block wpuf-min-w-0 wpuf-grow wpuf-px-4 wpuf-py-1.5 wpuf-text-base wpuf-text-gray-900 placeholder:wpuf-text-gray-400 sm:wpuf-text-sm/6 !wpuf-shadow-none !wpuf-ring-transparent"
            placeholder="<?php esc_attr_e( 'Search Field', 'wp-user-frontend' ); ?>">
        <div class="wpuf-flex wpuf-py-1.5 wpuf-pr-1.5">
                            <span class="wpuf-inline-flex wpuf-items-center wpuf-rounded wpuf-px-1 wpuf-font-sans wpuf-text-xs wpuf-text-gray-400">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    class="wpuf-size-5 hover:wpuf-cursor-pointer">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                </svg>
                            </span>
        </div>
    </div>
    <div class="wpuf-form-builder-form-fields">
        <template
            v-for="(section, index) in panel_sections">
            <div v-if="section.fields.length" class="panel-form-field-group clearfix">
                <h3
                    class="wpuf-text-gray-500 wpuf-flex wpuf-justify-between hover:wpuf-cursor-pointer"
                    @click="panel_toggle(index)">
                    {{ section.title }}
                    <i :class="[section.show ? 'fa fa-angle-down' : 'fa fa-angle-right']"></i>
                </h3>
                <div
                    v-show="section.show"
                    :id="'panel-form-field-buttons-' + section.id"
                    class="wpuf-grid wpuf-grid-cols-1 wpuf-gap-4 sm:wpuf-grid-cols-2">
                    <template v-for="field in section.fields">
                        <div
                            v-if="is_pro_feature(field)"
                            :data-form-field="field"
                            data-source="panel"
                            @click="alert_pro_feature(field)"
                            class="wpuf-relative wpuf-flex wpuf-items-center wpuf-rounded-lg wpuf-border wpuf-border-gray-200 wpuf-bg-white wpuf-shadow-sm hover:wpuf-border-gray-300 wpuf-p-3">
                            <div
                                v-if="field_settings[field].icon"
                                class="wpuf-shrink-0 wpuf-mr-2">
                                <i :class="['fa fa-' + field_settings[field].icon]" aria-hidden="true"></i>
                            </div>
                            <div class="wpuf-min-w-0 wpuf-flex-1">
                                <a href="#" class="focus:wpuf-outline-none">
                                    <span class="wpuf-absolute wpuf-inset-0" aria-hidden="true"></span>
                                    <p class="wpuf-text-sm wpuf-font-medium wpuf-text-gray-500 wpuf-m-0">{{ field_settings[field].title }}</p>
                                </a>
                            </div>
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
                                <i :class="['fa fa-' + field_settings[field].icon]" aria-hidden="true"></i>
                            </div>
                            <div class="wpuf-min-w-0 wpuf-flex-1">
                                <a href="#" class="focus:wpuf-outline-none">
                                    <span class="wpuf-absolute wpuf-inset-0" aria-hidden="true"></span>
                                    <p class="wpuf-text-sm wpuf-font-medium wpuf-text-gray-500 wpuf-m-0">{{ field_settings[field].title }}</p>
                                </a>
                            </div>
                        </div>
                        <div
                            v-else
                            :data-form-field="field"
                            data-source="panel"
                            @click="add_form_field(field)"
                            class="wpuf-relative wpuf-flex wpuf-items-center wpuf-rounded-lg wpuf-border wpuf-border-gray-200 wpuf-bg-white wpuf-shadow-sm hover:wpuf-border-gray-300 wpuf-p-3">
                            <div
                                v-if="field_settings[field].icon"
                                class="wpuf-shrink-0 wpuf-mr-2">
                                <i :class="['fa fa-' + field_settings[field].icon]" aria-hidden="true"></i>
                            </div>
                            <div class="wpuf-min-w-0 wpuf-flex-1">
                                <a href="#" class="focus:wpuf-outline-none">
                                    <span class="wpuf-absolute wpuf-inset-0" aria-hidden="true"></span>
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
