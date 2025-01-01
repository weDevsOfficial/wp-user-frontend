<div id="form-preview-stage">
    <div v-if="!form_fields.length" class="wpuf-flex wpuf-flex-col wpuf-items-center wpuf-justify-center wpuf-h-[80vh]">
        <img src="<?php echo WPUF_ASSET_URI . '/images/form-blank-state.svg'; ?>" alt="">
        <h2 class="wpuf-text-lg wpuf-text-gray-800"><?php esc_html_e( 'Add fields and build your desired form', 'wp-user-frontend' ); ?></h2>
        <p class="wpuf-text-sm wpuf-text-gray-500"><?php esc_html_e( 'Add fields and build your desired form', 'wp-user-frontend' ); ?></p>
    </div>

    <ul
        :class="['form-label-' + label_type]"
        class="wpuf-form sortable-list wpuf-py-8">
        <li
            v-for="(field, index) in form_fields"
            :key="field.id"
            :data-index="index"
            data-source="stage"
            :class="[
                        'field-items', 'wpuf-el', field.name, field.css, 'form-field-' + field.template,
                        field.width ? 'field-size-' + field.width : '',
                        ('custom_hidden_field' === field.template) ? 'hidden-field' : ''
                    ]"
            class="wpuf-group wpuf-rounded-lg hover:wpuf-bg-green-50 wpuf-transition wpuf-duration-150 wpuf-ease-out !wpuf-m-0 !wpuf-p-0">
            <div
                v-if="field.input_type !== 'column_field'"
                :class="parseInt(editing_form_id) === parseInt(field.id) ? 'wpuf-bg-green-50 wpuf-border-green-400' : 'wpuf-border-transparent'"
                class="wpuf-flex wpuf-justify-between wpuf-p-4 wpuf-rounded-t-md wpuf-border-t wpuf-border-r wpuf-border-l wpuf-border-dashed group-hover:wpuf-border-green-400 group-hover:wpuf-cursor-pointer">
                <div v-if="!(is_full_width(field.template) || is_pro_feature(field.template))" class="wpuf-w-1/4">
                    <label
                        v-if="!is_invisible(field)"
                        :for="'wpuf-' + field.name ? field.name : 'cls'"
                        class="wpuf-block wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-900">
                        {{ field.label }} <span v-if="field.required && 'yes' === field.required"
                                                class="required">*</span>
                    </label>
                </div>
                <div
                    :class="(is_full_width(field.template) || is_pro_feature(field.template)) ? 'wpuf-w-full' : 'wpuf-w-3/4'"
                    class="wpuf-relative"
                >
                    <div class="wpuf-absolute wpuf-w-full wpuf-h-full wpuf-z-10"></div>
                    <component
                        v-if="is_template_available(field)"
                        :is="'form-' + field.template"
                        :field="field"></component>
                    <div v-if="is_pro_feature(field.template)" class="stage-pro-alert wpuf-text-center">
                        <label class="wpuf-pro-text-alert">
                            <a :href="pro_link" target="_blank"
                               class="wpuf-text-gray-700 wpuf-text-base"><strong>{{ get_field_name( field.template )
                                    }}</strong> <?php _e( 'is available in Pro Version', 'wp-user-frontend' ); ?></a>
                        </label>
                    </div>
                </div>
            </div>
            <component
                v-if="is_template_available(field) && field.input_type === 'column_field'"
                :is="'form-' + field.template"
                :field="field">
            </component>
            <div
                :class="parseInt(editing_form_id) === parseInt(field.id) ? 'wpuf-opacity-100' : 'wpuf-opacity-0'"
                class="control-buttons group-hover:wpuf-opacity-100 wpuf-rounded-b-lg !wpuf-bg-green-600 wpuf-flex wpuf-items-center wpuf-transition wpuf-duration-150 wpuf-ease-out wpuf-flex wpuf-justify-around">
                <div class="wpuf-flex wpuf-justify-around wpuf-text-green-200">
                    <template v-if="!is_failed_to_validate(field.template)">
                            <span :class="action_button_classes">
                            <i
                                class="fa fa-arrows move wpuf-pr-2 wpuf-rounded-l-md hover:!wpuf-cursor-move wpuf-border-r wpuf-border-green-200"></i>
                            </span>
                        <span
                            :class="action_button_classes"
                            @click="open_field_settings(field.id)">
                            <i
                                class="fa fa-pencil"></i>
                                Edit
                            </span>
                        <span
                            :class="action_button_classes"
                            @click="clone_field(field.id, index)">
                            <i
                                class="fa fa-clone"></i>
                                Copy
                            </span>
                    </template>
                    <template v-else>
                            <span :class="action_button_classes">
                            <i class="fa fa-arrows control-button-disabled wpuf--ml-1 wpuf-rounded-l-md"></i>
                                </span>
                        <span :class="action_button_classes">
                            <i class="fa fa-pencil control-button-disabled wpuf--ml-1"></i>
                                Edit
                                </span>
                        <span :class="action_button_classes">
                            <i
                                class="fa fa-clone control-button-disabled wpuf--ml-1"></i>
                                Copy
                            </span>
                    </template>
                    <span :class="action_button_classes" @click="delete_field(index)">
                            <i
                                class="fa fa-trash-o wpuf--ml-1"></i>
                                Remove
                        </span>
                    <span
                        v-if="is_pro_feature(field.template)"
                        :class="action_button_classes" class="hover:wpuf-bg-green-700">
                            <a
                                :href="pro_link"
                                target="_blank"
                                class="wpuf-rounded-r-md hover:wpuf-bg-slate-500 hover:wpuf-cursor-pointer wpuf-transition wpuf-duration-150 wpuf-ease-out hover:wpuf-transition-all">
                                <svg
                                    width="15" height="15" viewBox="0 0 20 15" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M19.2131 4.11564C19.2161 4.16916 19.2121 4.22364 19.1983 4.27775L17.9646 10.5323C17.9024 10.7741 17.6796 10.9441 17.4235 10.9455L10.0216 10.9818H10.0188H2.61682C2.35933 10.9818 2.13495 10.8112 2.07275 10.5681L0.839103 4.29542C0.824897 4.23985 0.820785 4.18385 0.824374 4.12895C0.34714 3.98269 0 3.54829 0 3.03636C0 2.40473 0.528224 1.89091 1.17757 1.89091C1.82692 1.89091 2.35514 2.40473 2.35514 3.03636C2.35514 3.39207 2.18759 3.71033 1.92523 3.92058L3.46976 5.43433C3.86011 5.81695 4.40179 6.03629 4.95596 6.03629C5.61122 6.03629 6.23596 5.7336 6.62938 5.22647L9.1677 1.95491C8.95447 1.74764 8.82243 1.46124 8.82243 1.14545C8.82243 0.513818 9.35065 0 10 0C10.6493 0 11.1776 0.513818 11.1776 1.14545C11.1776 1.45178 11.0526 1.72982 10.8505 1.93556L10.8526 1.93811L13.3726 5.21869C13.7658 5.73069 14.3928 6.03636 15.0499 6.03636C15.6092 6.03636 16.1351 5.82451 16.5305 5.43978L18.0848 3.92793C17.8169 3.71775 17.6449 3.39644 17.6449 3.03636C17.6449 2.40473 18.1731 1.89091 18.8224 1.89091C19.4718 1.89091 20 2.40473 20 3.03636C20 3.53462 19.6707 3.9584 19.2131 4.11564ZM17.8443 12.6909C17.8443 12.3897 17.5932 12.1455 17.2835 12.1455H2.77884C2.46916 12.1455 2.21809 12.3897 2.21809 12.6909V14C2.21809 14.3012 2.46916 14.5455 2.77884 14.5455H17.2835C17.5932 14.5455 17.8443 14.3012 17.8443 14V12.6909Z"
                                    fill="#FB9A28"/>
                            </svg>
                            </a>
                        </span>
                </div>
            </div>
        </li>
    </ul>
    <li class="wpuf-submit wpuf-list-none wpuf-hidden">
        <div class="wpuf-label">&nbsp;</div>
        <?php do_action( 'wpuf_form_builder_template_builder_stage_submit_area' ); ?>
    </li>
    <div v-if="hidden_fields.length" class="wpuf-border-t wpuf-border-dashed wpuf-border-gray-300 wpuf-mt-2">
        <h4><?php esc_html_e( 'Hidden Fields', 'wp-user-frontend' ); ?></h4>
        <ul class="wpuf-form">
            <li
                v-for="(field, index) in hidden_fields"
                class="field-items wpuf-group hover:wpuf-bg-green-50 !wpuf-m-0 !wpuf-p-0"
            >
                <div
                    :class="parseInt(editing_form_id) === parseInt(field.id) ? 'wpuf-border wpuf-border-dashed wpuf-bg-green-50 wpuf-border-green-400' : 'wpuf-border-transparent'"
                    class="wpuf-flex wpuf-bg-gray-50 wpuf-p-4 wpuf-rounded-t-md wpuf-border-t wpuf-border-r wpuf-border-l group-hover:wpuf-border-dashed group-hover:wpuf-border-green-400 group-hover:wpuf-cursor-pointer">
                    <strong><?php esc_html_e( 'key', 'wp-user-frontend' ); ?></strong>: {{ field.name }} |
                    <strong><?php esc_html_e( 'value', 'wp-user-frontend' ); ?></strong>: {{ field.meta_value }}
                </div>
                <div
                    class="control-buttons wpuf-opacity-0 group-hover:wpuf-opacity-100 wpuf-rounded-b-lg !wpuf-bg-green-600 wpuf-flex wpuf-justify-around wpuf-items-center wpuf-transition wpuf-duration-150 wpuf-ease-out">
                    <div class="wpuf-flex wpuf-items-center wpuf-text-green-200">
                        <template v-if="!is_failed_to_validate(field.template)">
                                <span
                                    :class="action_button_classes"
                                    @click="open_field_settings(field.id)">
                            <i
                                class="fa fa-pencil"></i>
                                Edit
                            </span>
                            <span
                                :class="action_button_classes"
                                @click="clone_field(field.id, index)">
                            <i
                                class="fa fa-clone"></i>
                                Copy
                            </span>
                        </template>
                        <template v-else>
                            <span :class="action_button_classes">
                            <i class="fa fa-arrows control-button-disabled wpuf--ml-1 wpuf-rounded-l-md"></i>
                                </span>
                            <span :class="action_button_classes">
                            <i class="fa fa-pencil control-button-disabled wpuf--ml-1"></i>
                                Edit
                                </span>
                            <span :class="action_button_classes">
                            <i
                                class="fa fa-clone control-button-disabled wpuf--ml-1"></i>
                                Copy
                            </span>
                        </template>
                        <span :class="action_button_classes" @click="delete_field(index)">
                            <i
                                class="fa fa-trash-o wpuf--ml-1"></i>
                                Remove
                        </span>
                        <span
                            v-if="is_pro_feature(field.template)"
                            :class="action_button_classes" class="hover:wpuf-bg-green-700">
                            <a
                                :href="pro_link"
                                target="_blank"
                                class="wpuf-rounded-r-md hover:wpuf-bg-slate-500 hover:wpuf-cursor-pointer wpuf-transition wpuf-duration-150 wpuf-ease-out hover:wpuf-transition-all">
                                <svg
                                    width="15" height="15" viewBox="0 0 20 15" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M19.2131 4.11564C19.2161 4.16916 19.2121 4.22364 19.1983 4.27775L17.9646 10.5323C17.9024 10.7741 17.6796 10.9441 17.4235 10.9455L10.0216 10.9818H10.0188H2.61682C2.35933 10.9818 2.13495 10.8112 2.07275 10.5681L0.839103 4.29542C0.824897 4.23985 0.820785 4.18385 0.824374 4.12895C0.34714 3.98269 0 3.54829 0 3.03636C0 2.40473 0.528224 1.89091 1.17757 1.89091C1.82692 1.89091 2.35514 2.40473 2.35514 3.03636C2.35514 3.39207 2.18759 3.71033 1.92523 3.92058L3.46976 5.43433C3.86011 5.81695 4.40179 6.03629 4.95596 6.03629C5.61122 6.03629 6.23596 5.7336 6.62938 5.22647L9.1677 1.95491C8.95447 1.74764 8.82243 1.46124 8.82243 1.14545C8.82243 0.513818 9.35065 0 10 0C10.6493 0 11.1776 0.513818 11.1776 1.14545C11.1776 1.45178 11.0526 1.72982 10.8505 1.93556L10.8526 1.93811L13.3726 5.21869C13.7658 5.73069 14.3928 6.03636 15.0499 6.03636C15.6092 6.03636 16.1351 5.82451 16.5305 5.43978L18.0848 3.92793C17.8169 3.71775 17.6449 3.39644 17.6449 3.03636C17.6449 2.40473 18.1731 1.89091 18.8224 1.89091C19.4718 1.89091 20 2.40473 20 3.03636C20 3.53462 19.6707 3.9584 19.2131 4.11564ZM17.8443 12.6909C17.8443 12.3897 17.5932 12.1455 17.2835 12.1455H2.77884C2.46916 12.1455 2.21809 12.3897 2.21809 12.6909V14C2.21809 14.3012 2.46916 14.5455 2.77884 14.5455H17.2835C17.5932 14.5455 17.8443 14.3012 17.8443 14V12.6909Z"
                                    fill="#FB9A28"/>
                            </svg>
                            </a>
                        </span>
                    </div>
                </div>
            </li>
        </ul>
    </div>
    <?php do_action( 'wpuf_form_builder_template_builder_stage_bottom_area' ); ?>
</div>
