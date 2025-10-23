<div>
    <div
        class="wpuf-flex wpuf-rounded-lg wpuf-bg-white wpuf-outline wpuf--outline-1 wpuf--outline-offset-1 wpuf-outline-gray-300 wpuf-border wpuf-border-gray-200 wpuf-shadow wpuf-mb-8">
        <input
            type="text"
            name="search"
            id="search"
            v-model="searched_fields"
            class="!wpuf-border-none !wpuf-rounded-[6px] wpuf-block wpuf-min-w-0 wpuf-grow !wpuf-px-4 !wpuf-py-1.5 !wpuf-text-base wpuf-text-gray-900 placeholder:wpuf-text-gray-400 !wpuf-ring-transparent wpuf-shadow focus:!wpuf-shadow-none"
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
    <div class="wpuf-form-builder-form-fields wpuf-mt-4">
        <template
            v-for="(section, index) in panel_sections">
            <div v-if="section.fields.length" class="panel-form-field-group wpuf-mb-8">
                <h3
                    :class="section.show ? 'wpuf-text-green-600' : 'wpuf-text-gray-500'"
                    class="wpuf-flex wpuf-justify-between hover:wpuf-cursor-pointer wpuf-text-base wpuf-m-0 !wpuf-font-medium"
                    @click="panel_toggle(index)">
                    {{ section.title }}
                    <i
                        :class="[section.show ? 'fa fa-angle-down wpuf-text-green-600' : 'fa fa-angle-right wpuf-text-gray-500']"
                        class="wpuf-text-[24px]"></i>
                </h3>
                <div
                    v-show="section.show"
                    :key="section.id"
                    :id="'panel-form-field-buttons-' + section.id"
                    class="panel-form-field-buttons wpuf-grid wpuf-grid-cols-1 wpuf-gap-3 sm:wpuf-grid-cols-2 wpuf-mt-3 ">
                    <template v-for="field in section.fields">
                        <div
                            v-if="is_pro_preview(field)"
                            :key="field"
                            :data-form-field="field"
                            data-source="panel"
                            @click="alert_pro_feature(field)"
                            class="wpuf-relative wpuf-group/pro-field">
                                <div class="wpuf-opacity-50 wpuf-field-button wpuf-flex wpuf-items-center wpuf-rounded-lg wpuf-border wpuf-border-gray-200 wpuf-bg-white wpuf-shadow-sm wpuf-px-4 wpuf-py-3 hover:wpuf-border-gray-300 hover:wpuf-cursor-pointer">
                                    <div
                                        v-if="field_settings[field].icon"
                                        class="wpuf-shrink-0 wpuf-mr-2 wpuf-text-gray-400">
                                        <img :src="get_icon_url(field)" alt="">
                                    </div>
                                    <div class="wpuf-min-w-0 wpuf-flex-1">
                                        <a href="#" class="focus:wpuf-outline-none focus:wpuf-shadow-none">
                                            <p class="wpuf-text-base wpuf-font-normal wpuf-text-gray-500 wpuf-m-0">
                                                {{ field_settings[field].title }}</p>
                                        </a>
                                    </div>
                                </div>
                            <div
                                class="wpuf-absolute wpuf-top-4 wpuf-right-4 wpuf-opacity-0 group-hover/pro-field:wpuf-opacity-100 wpuf-transition-all">
                                <img src="<?php echo esc_attr( WPUF_ASSET_URI . '/images/pro-badge.svg' ); ?>" alt="">
                            </div>
                        </div>
                        <div
                            v-else-if="is_failed_to_validate(field)"
                            :key="field"
                            :data-form-field="field"
                            data-source="panel"
                            @click="alert_invalidate_msg(field)"
                            class="wpuf-relative wpuf-flex wpuf-items-center wpuf-rounded-lg wpuf-border wpuf-border-gray-200 wpuf-bg-white wpuf-shadow-sm wpuf-px-3 wpuf-py-4 hover:wpuf-border-gray-300 hover:wpuf-cursor-pointer">
                            <div
                                v-if="field_settings[field].icon"
                                class="wpuf-shrink-0 wpuf-mr-2">
                                <img :src="get_icon_url(field)" alt="">
                            </div>
                            <div class="wpuf-min-w-0 wpuf-flex-1">
                                <a href="#" class="focus:wpuf-outline-none focus:wpuf-shadow-none">
                                    <p class="wpuf-text-base wpuf-font-normal wpuf-text-gray-500 wpuf-m-0">{{ field_settings[field].title }}</p>
                                </a>
                            </div>
                        </div>
                        <div
                            v-else
                            :key="field"
                            :data-form-field="field"
                            data-source="panel"
                            @click="add_form_field(field)"
                            class="wpuf-field-button wpuf-relative wpuf-flex wpuf-items-center wpuf-rounded-lg wpuf-border wpuf-border-gray-200 wpuf-bg-white wpuf-shadow wpuf-px-3 wpuf-py-4 hover:wpuf-cursor-pointer hover:wpuf-border-primary">
                            <div
                                v-if="field_settings[field].icon"
                                class="wpuf-shrink-0 wpuf-mr-2">
                                <img :src="get_icon_url(field)" alt="">
                            </div>
                            <div class="wpuf-min-w-0 wpuf-flex-1">
                                <a href="#" class="focus:wpuf-outline-none focus:wpuf-shadow-none">
                                    <p class="wpuf-text-base wpuf-font-normal wpuf-text-gray-500 wpuf-m-0">{{ field_settings[field].title }}</p>
                                </a>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        <div class="wpuf-mt-12 wpuf-p-6 wpuf-rounded-lg wpuf-shadow-md wpuf-text-center wpuf-border wpuf-border-gray-50">
            <h2 class="wpuf-text-slate-600 wpuf-text-xl wpuf-font-bold wpuf-mb-4">
                <?php esc_html_e( 'Got an idea for a new field?', 'wp-user-frontend' ); ?>
            </h2>
            <p class="wpuf-text-slate-600 wpuf-mb-6">
                <?php esc_html_e( 'We\'d love to hear it!', 'wp-user-frontend' ); ?>
            </p>
            <a
                class="wpuf-btn-primary"
               target="_blank"
                href="<?php echo esc_url( 'https://wpuf.canny.io/ideas' ); ?>">
                <?php esc_html_e( 'Share Your Idea', 'wp-user-frontend' ); ?>
            </a>
        </div>
    </div>
</div>
