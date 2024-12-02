<div class="wpuf-form-builder-form-fields wpuf-px-6">
    <template v-for="(section, index) in panel_sections">
        <div v-if="section.fields.length" class="panel-form-field-group clearfix">
            <h3
                class="wpuf-text-gray-500 wpuf-flex wpuf-justify-between"
                @click="panel_toggle(index)">
                {{ section.title }}
                <i
                    class="hover:wpuf-cursor-pointer"
                    :class="[section.show ? 'fa fa-angle-down' : 'fa fa-angle-right']"></i>
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
