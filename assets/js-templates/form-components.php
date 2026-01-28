<script type="text/x-template" id="tmpl-wpuf-builder-stage">
<div id="form-preview-stage" class="wpuf-style">
    <h4 v-if="!form_fields.length" class="text-center">
        <?php esc_html_e( 'Add fields by dragging the fields from the right sidebar to this area.', 'wp-user-frontend' ); ?>
    </h4>

    <ul :class="['wpuf-form', 'sortable-list', 'form-label-' + label_type]">
        <li
            v-for="(field, index) in form_fields"
            :key="field.id"
            :class="[
                'field-items', 'wpuf-el', field.name, field.css, 'form-field-' + field.template,
                field.width ? 'field-size-' + field.width : '',
                ('custom_hidden_field' === field.template) ? 'hidden-field' : '',
                parseInt(editing_form_id) === parseInt(field.id) ? 'current-editing' : ''
            ]"
            :data-index="index"
            data-source="stage"
        >
            <div v-if="!is_full_width(field.template)" class="wpuf-label">
                <span v-if="field.show_icon === 'yes' && field.field_icon && field.icon_position === 'left_label'" 
                      class="wpuf-field-label-icon wpuf-inline-flex wpuf-items-center wpuf-mr-1">
                      <i :class="[field.field_icon, 'wpuf-field-icon']"></i>
                </span>
                <label v-if="!is_invisible(field)" :for="'wpuf-' + field.name ? field.name : 'cls'">
                    {{ field.label }} <span v-if="field.required && 'yes' === field.required" class="required">*</span>
                </label>
                <span v-if="field.show_icon === 'yes' && field.field_icon && field.icon_position === 'right_label'" 
                      class="wpuf-field-label-icon wpuf-inline-flex wpuf-items-center wpuf-ml-2">
                      <i :class="[field.field_icon, 'wpuf-field-icon']"></i>
                </span>
            </div>

            <component v-if="is_template_available(field)" :is="'form-' + field.template" :field="field"></component>

            <div v-if="is_pro_feature(field.template)" class="stage-pro-alert">
                <label class="wpuf-pro-text-alert">
                    <a :href="pro_link" target="_blank"><strong>{{ get_field_name(field.template) }}</strong> <?php esc_html_e( 'is available in Pro Version', 'wp-user-frontend' ); ?></a>
                </label>
            </div>

            <div class="control-buttons">
                <p>
                    <template v-if="!is_failed_to_validate(field.template)">
                        <i class="fa fa-arrows move"></i>
                        <i class="fa fa-pencil" @click="open_field_settings(field.id)"></i>
                        <i class="fa fa-clone" @click="clone_field(field.id, index)"></i>
                    </template>
                    <template v-else>
                        <i class="fa fa-arrows control-button-disabled"></i>
                        <i class="fa fa-pencil control-button-disabled"></i>
                        <i class="fa fa-clone control-button-disabled"></i>
                    </template>
                    <i class="fa fa-trash-o" @click="delete_field(index)"></i>
                </p>
            </div>
        </li>

        <li v-if="!form_fields.length" class="field-items empty-list-item"></li>

        <li class="wpuf-submit">
            <div class="wpuf-label">&nbsp;</div>

            <?php do_action( 'wpuf_form_builder_template_builder_stage_submit_area' ); ?>
        </li>
    </ul><!-- .wpuf-form -->

    <div v-if="hidden_fields.length" class="hidden-field-list">
        <h4><?php esc_html_e( 'Hidden Fields', 'wp-user-frontend' ); ?></h4>

        <ul class="wpuf-form">
            <li
                v-for="(field, index) in hidden_fields"
                :class="['field-items', parseInt(editing_form_id) === parseInt(field.id) ? 'current-editing' : '']"
            >
                <strong><?php esc_html_e( 'key', 'wp-user-frontend' ); ?></strong>: {{ field.name }} | <strong><?php esc_html_e( 'value', 'wp-user-frontend' ); ?></strong>: {{ field.meta_value }}

                <div class="control-buttons">
                    <p>
                        <i class="fa fa-pencil" @click="open_field_settings(field.id)"></i>
                        <i class="fa fa-clone" @click="clone_field(field.id, index)"></i>
                        <i class="fa fa-trash-o" @click="delete_hidden_field(field.id)"></i>
                    </p>
                </div>
            </li>
        </ul>
    </div>

    <?php do_action( 'wpuf_form_builder_template_builder_stage_bottom_area' ); ?>
</div><!-- #form-preview-stage -->
</script>

<script type="text/x-template" id="tmpl-wpuf-builder-stage-v4-1">
<div id="form-preview-stage" class="wpuf-h-[70vh]">
    <div v-if="!form_fields.length" class="wpuf-flex wpuf-flex-col wpuf-items-center wpuf-justify-center wpuf-h-[80vh]">
        <img src="<?php echo esc_url( WPUF_ASSET_URI . '/images/form-blank-state.svg' ); ?>" alt="">
        <h2 class="wpuf-text-lg wpuf-text-gray-800 wpuf-mt-8 wpuf-mb-2"><?php esc_html_e( 'Add fields and build your desired form', 'wp-user-frontend' ); ?></h2>

        <p class="wpuf-text-sm wpuf-text-gray-500"><?php esc_html_e( 'Add the necessary field and build your form.', 'wp-user-frontend' ); ?></p>
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
                        'field-items', 'wpuf-el', field.name, filter_builder_css_classes(field.css), 'form-field-' + field.template,
                        field.width ? 'field-size-' + field.width : '',
                        ('custom_hidden_field' === field.template) ? 'hidden-field' : ''
                    ]"
            class="wpuf-group wpuf-rounded-lg hover:!wpuf-bg-green-50 wpuf-transition wpuf-duration-150 wpuf-ease-out !wpuf-m-0 !wpuf-p-0 wpuf-overflow-hidden">
            <div
                v-if="field.input_type !== 'column_field' && field.input_type !== 'repeat'"
                :class="parseInt(editing_form_id) === parseInt(field.id) ? 'wpuf-bg-green-50 wpuf-border-primary' : 'wpuf-border-transparent'"
                class="wpuf-flex wpuf-justify-between wpuf-p-6 wpuf-rounded-t-md wpuf-border-t wpuf-border-r wpuf-border-l wpuf-border-dashed group-hover:wpuf-border-primary group-hover:wpuf-cursor-pointer !wpuf-pb-3">
                <div v-if="!(is_full_width(field.template) || is_pro_preview(field.template))" class="wpuf-w-1/4 wpuf-flex wpuf-items-center">
                    <span v-if="field.show_icon === 'yes' && field.field_icon && field.icon_position === 'left_label'"
                          class="wpuf-field-label-icon wpuf-inline-flex wpuf-items-center wpuf-mr-1">
                          <i :class="[field.field_icon, 'wpuf-field-icon']"></i>
                    </span>
                    <label
                        v-if="!is_invisible(field)"
                        :for="'wpuf-' + field.name ? field.name : 'cls'"
                        class="wpuf-block wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-900">
                        {{ field.label }} <span v-if="field.required && 'yes' === field.required"
                                                class="required">*</span>
                        <span v-if="has_hidden_css_class(field.css)"
                              class="wpuf-inline-flex wpuf-items-center wpuf-ml-2 wpuf-px-2 wpuf-py-0.5 wpuf-rounded wpuf-text-xs wpuf-font-medium wpuf-bg-yellow-100 wpuf-text-yellow-800 wpuf-border wpuf-border-yellow-300"
                              title="This field will be hidden on the frontend due to CSS class">
                            <svg class="wpuf-w-3 wpuf-h-3 wpuf-mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path>
                            </svg>
                            Hidden on frontend
                        </span>
                    </label>
                    <span v-if="field.show_icon === 'yes' && field.field_icon && field.icon_position === 'right_label'"
                          class="wpuf-field-label-icon wpuf-inline-flex wpuf-items-center wpuf-ml-2">
                          <i :class="[field.field_icon, 'wpuf-field-icon']"></i>
                    </span>
                </div>
                <div
                    :class="(is_full_width(field.template) || is_pro_preview(field.template)) ? 'wpuf-w-full' : 'wpuf-w-3/4'"
                    class="wpuf-relative"
                >
                    <div class="wpuf-absolute wpuf-w-full wpuf-h-full wpuf-z-10"></div>
                    <component
                        v-if="is_template_available(field)"
                        :is="'form-' + field.template"
                        :field="field"></component>
                    <div v-if="is_pro_preview(field.template)" class="stage-pro-alert wpuf-text-center">
                        <label class="wpuf-pro-text-alert">
                            <a :href="pro_link" target="_blank"
                               class="wpuf-text-gray-700 wpuf-text-base"><strong>{{ get_field_name( field.template )
                                    }}</strong> <?php esc_html_e( 'is available in Pro Version', 'wp-user-frontend' ); ?></a>
                        </label>
                    </div>
                </div>
            </div>
            <component
                v-if="is_template_available(field) && (field.input_type === 'column_field' || field.input_type === 'repeat')"
                :is="'form-' + field.template"
                :field="field">
            </component>
            <div
                :class="parseInt(editing_form_id) === parseInt(field.id) ? 'wpuf-opacity-100' : 'wpuf-opacity-0'"
                class="field-buttons group-hover:wpuf-opacity-100 wpuf-rounded-b-lg !wpuf-bg-primary wpuf-items-center wpuf-transition wpuf-duration-150 wpuf-ease-out wpuf-flex wpuf-justify-around">
                <div class="wpuf-flex wpuf-justify-around wpuf-text-green-200">
                    <template v-if="!is_failed_to_validate(field.template)">
                        <span class="!wpuf-mt-2.5">
                            <i class="fa fa-arrows move wpuf-pr-2 wpuf-rounded-l-md hover:!wpuf-cursor-move wpuf-border-r wpuf-border-green-200 wpuf-text-[17px]"></i>
                        </span>
                        <span
                            :class="action_button_classes"
                            @click="open_field_settings(field.id)">
                            <svg class="wpuf-mr-2" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M5.43306 13.9163L6.69485 10.7618C6.89603 10.2589 7.19728 9.802 7.58033 9.41896L14.4995 2.50023C15.3279 1.6718 16.6711 1.6718 17.4995 2.50023C18.3279 3.32865 18.3279 4.6718 17.4995 5.50023L10.5803 12.419C10.1973 12.802 9.74042 13.1033 9.23746 13.3044L6.08299 14.5662C5.67484 14.7295 5.2698 14.3244 5.43306 13.9163Z" fill="#A7F3D0"/>
<path d="M3.5 5.74951C3.5 5.05916 4.05964 4.49951 4.75 4.49951H10C10.4142 4.49951 10.75 4.16373 10.75 3.74951C10.75 3.3353 10.4142 2.99951 10 2.99951H4.75C3.23122 2.99951 2 4.23073 2 5.74951V15.2495C2 16.7683 3.23122 17.9995 4.75 17.9995H14.25C15.7688 17.9995 17 16.7683 17 15.2495V9.99951C17 9.5853 16.6642 9.24951 16.25 9.24951C15.8358 9.24951 15.5 9.5853 15.5 9.99951V15.2495C15.5 15.9399 14.9404 16.4995 14.25 16.4995H4.75C4.05964 16.4995 3.5 15.9399 3.5 15.2495V5.74951Z" fill="#A7F3D0"/>
</svg> Edit
                        </span>
                        <span
                            :class="action_button_classes"
                            @click="clone_field(field.id, index)">
                            <svg class="wpuf-mr-2" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M13.75 6.875V5C13.75 3.96447 12.9105 3.125 11.875 3.125H5C3.96447 3.125 3.125 3.96447 3.125 5V11.875C3.125 12.9105 3.96447 13.75 5 13.75H6.875M13.75 6.875H15C16.0355 6.875 16.875 7.71447 16.875 8.75V15C16.875 16.0355 16.0355 16.875 15 16.875H8.75C7.71447 16.875 6.875 16.0355 6.875 15V13.75M13.75 6.875H8.75C7.71447 6.875 6.875 7.71447 6.875 8.75V13.75" stroke="#A7F3D0" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
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
                            <svg class="wpuf-mr-2" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M12.2837 7.5L11.9952 15M8.00481 15L7.71635 7.5M16.023 4.82547C16.308 4.86851 16.592 4.91456 16.875 4.96358M16.023 4.82547L15.1332 16.3938C15.058 17.3707 14.2434 18.125 13.2637 18.125H6.73631C5.75655 18.125 4.94198 17.3707 4.86683 16.3938L3.97696 4.82547M16.023 4.82547C15.0677 4.6812 14.1013 4.57071 13.125 4.49527M3.125 4.96358C3.40798 4.91456 3.69198 4.86851 3.97696 4.82547M3.97696 4.82547C4.93231 4.6812 5.89874 4.57071 6.875 4.49527M13.125 4.49527V3.73182C13.125 2.74902 12.3661 1.92853 11.3838 1.8971C10.9244 1.8824 10.463 1.875 10 1.875C9.53696 1.875 9.07565 1.8824 8.61618 1.8971C7.63388 1.92853 6.875 2.74902 6.875 3.73182V4.49527M13.125 4.49527C12.0938 4.41558 11.0516 4.375 10 4.375C8.94836 4.375 7.9062 4.41558 6.875 4.49527" stroke="#A7F3D0" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
                                Remove
                        </span>
                    <span
                        v-if="is_pro_preview(field.template)"
                        :class="action_button_classes" class="hover:wpuf-bg-primary">
                            <a
                                :href="pro_link"
                                target="_blank"
                                class="wpuf-rounded-r-md hover:wpuf-bg-slate-500 hover:wpuf-cursor-pointer wpuf-transition wpuf-duration-150 wpuf-ease-out hover:wpuf-transition-all">
                                <img src="<?php echo esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ); ?>" alt="">
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
                class="field-items wpuf-group/hidden-fields !wpuf-m-0 !wpuf-p-0 hover:wpuf-cursor-pointer"
            >
                <div
                    :class="parseInt(editing_form_id) === parseInt(field.id) ? 'wpuf-bg-green-50 wpuf-border-primaryHover' : 'wpuf-border-transparent'"
                    class="wpuf-flex wpuf-rounded-t-lg wpuf-border-t wpuf-border-r wpuf-border-l wpuf-border-dashed group-hover/hidden-fields:wpuf-border-primaryHover group-hover/hidden-fields:wpuf-bg-green-50">
                    <div class="wpuf-bg-primary wpuf-m-4 wpuf-py-2 wpuf-px-4 wpuf-w-full wpuf-rounded-lg">
                        <strong><?php esc_html_e( 'key', 'wp-user-frontend' ); ?></strong>: {{ field.name }} |
                        <strong><?php esc_html_e( 'value', 'wp-user-frontend' ); ?></strong>: {{ field.meta_value }}
                    </div>
                </div>
                <div
                    :class="parseInt(editing_form_id) === parseInt(field.id) ? 'wpuf-opacity-100' : 'wpuf-opacity-0'"
                    class="field-buttons wpuf-opacity-0 group-hover/hidden-fields:wpuf-opacity-100 wpuf-bg-primary wpuf-rounded-b-lg wpuf-transition wpuf-duration-150 wpuf-ease-out wpuf-flex wpuf-items-center wpuf-justify-around">
                    <div class="wpuf-flex wpuf-justify-around wpuf-text-green-200">
                        <template v-if="!is_failed_to_validate(field.template)">
                            <span
                                class="!wpuf-mt-2.5"
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
                            <span :class="action_button_classes"  @click="delete_hidden_field(field.id)">
                                <svg class="wpuf-mr-2" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M12.2837 7.5L11.9952 15M8.00481 15L7.71635 7.5M16.023 4.82547C16.308 4.86851 16.592 4.91456 16.875 4.96358M16.023 4.82547L15.1332 16.3938C15.058 17.3707 14.2434 18.125 13.2637 18.125H6.73631C5.75655 18.125 4.94198 17.3707 4.86683 16.3938L3.97696 4.82547M16.023 4.82547C15.0677 4.6812 14.1013 4.57071 13.125 4.49527M3.125 4.96358C3.40798 4.91456 3.69198 4.86851 3.97696 4.82547M3.97696 4.82547C4.93231 4.6812 5.89874 4.57071 6.875 4.49527M13.125 4.49527V3.73182C13.125 2.74902 12.3661 1.92853 11.3838 1.8971C10.9244 1.8824 10.463 1.875 10 1.875C9.53696 1.875 9.07565 1.8824 8.61618 1.8971C7.63388 1.92853 6.875 2.74902 6.875 3.73182V4.49527M13.125 4.49527C12.0938 4.41558 11.0516 4.375 10 4.375C8.94836 4.375 7.9062 4.41558 6.875 4.49527" stroke="#A7F3D0" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
                                    Remove
                            </span>
                        </template>
                    </div>
                </div>
            </li>
        </ul>
    </div>
    <?php do_action( 'wpuf_form_builder_template_builder_stage_bottom_area' ); ?>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-field-checkbox">
<div v-if="met_dependencies" class="panel-field-opt panel-field-opt-checkbox wpuf-mb-6">
    <div class="wpuf-flex">
        <label v-if="option_field.title" class="wpuf-option-field-title wpuf-font-sm wpuf-text-gray-700 wpuf-font-medium">
            {{ option_field.title }} <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>
        </label>
    </div>
    <ul :class="[option_field.inline ? 'list-inline' : '']">
        <li v-for="(option, key) in option_field.options">
            <label class="wpuf-block text-sm/6 wpuf-font-medium wpuf-text-gray-900 !wpuf-mb-0">
                <input type="checkbox" :class="builder_class_names('checkbox')" class="!wpuf-mr-2" :value="key" v-model="value">
                {{ option }}
            </label>
        </li>
    </ul>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-field-html_help_text">
<div class="panel-field-opt panel-field-html-help-text" v-html="option_field.text"></div>
</script>

<script type="text/x-template" id="tmpl-wpuf-field-icon_selector">
<div v-if="met_dependencies" class="panel-field-opt panel-field-opt-icon-selector">
    <div class="wpuf-flex">
        <label v-if="option_field.title" class="!wpuf-mb-0">
            {{ option_field.title }} <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>
        </label>
    </div>

    <div class="option-fields-section wpuf-relative">
        <div
            @click.stop="togglePicker"
            class="wpuf-w-full wpuf-mt-4 wpuf-min-w-full !wpuf-py-[10px] !wpuf-px-[14px] wpuf-text-gray-700 wpuf-font-medium !wpuf-shadow-sm wpuf-border !wpuf-border-gray-300 !wpuf-rounded-[6px] hover:!wpuf-text-gray-700 wpuf-flex wpuf-justify-between wpuf-items-center !wpuf-text-base wpuf-cursor-pointer"
        >
            <div class="wpuf-flex wpuf-items-center wpuf-gap-2">
                <i v-if="value" :class="value" class="wpuf-text-gray-600"></i>
                <span>{{ selectedIconDisplay }}</span>
            </div>
            <div class="wpuf-flex wpuf-items-center wpuf-gap-1">
                <i v-if="value" @click.stop="clearIcon" class="fa fa-times wpuf-text-gray-500 hover:wpuf-text-red-500 wpuf-cursor-pointer wpuf-p-1"></i>
                <i :class="showIconPicker ? 'fa-angle-up' : 'fa-angle-down'" class="fa wpuf-text-base"></i>
            </div>
        </div>

        <div
            v-if="showIconPicker"
            @click.stop
            class="wpuf-absolute wpuf-bg-white wpuf-border wpuf-border-gray-300 wpuf-rounded-lg wpuf-w-full wpuf-z-50 wpuf-mt-1 wpuf-shadow-lg wpuf-right-0"
            style="max-height: 300px; min-width: 320px; max-width: 400px;"
        >
            <!-- Search -->
            <div class="wpuf-p-3 wpuf-border-b wpuf-border-gray-200">
                <input
                    v-model="searchTerm"
                    type="text"
                    placeholder="Search icons... (e.g., user, email, home)"
                    class="wpuf-w-full !wpuf-px-4 !wpuf-py-1.5 wpuf-border wpuf-border-gray-300 wpuf-rounded wpuf-text-sm wpuf-text-gray-900 placeholder:wpuf-text-gray-400 wpuf-shadow focus:!wpuf-shadow-none"
                >
                <div class="wpuf-text-xs wpuf-text-gray-500 wpuf-mt-1">
                    {{ filteredIcons.length }} icons {{ searchTerm ? 'found' : 'available' }}
                </div>
            </div>

            <!-- Icons Grid -->
            <div class="wpuf-icon-grid-container" style="max-height: 210px; overflow-y: auto; padding: 10px;">
                <!-- Icons Grid -->
                <div v-if="filteredIcons.length > 0" class="wpuf-icon-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px;">
                    <div
                        v-for="icon in filteredIcons"
                        :key="icon.class"
                        @click="selectIcon(icon.class)"
                        :class="['wpuf-icon-grid-item', { 'selected': value === icon.class }]"
                        :title="icon.name + ' - ' + icon.keywords"
                        style="padding: 10px 5px; text-align: center; border: 1px solid #e0e0e0; border-radius: 4px; cursor: pointer; transition: all 0.2s; min-height: 60px; display: flex; flex-direction: column; align-items: center; justify-content: center;"
                    >
                        <i :class="icon.class" style="font-size: 18px; margin-bottom: 4px; color: #555;"></i>
                        <div style="font-size: 10px; color: #666; line-height: 1.2; word-break: break-word; max-width: 100%;">{{ icon.name }}</div>
                    </div>
                </div>

                <!-- No Results -->
                <div v-else class="wpuf-text-center wpuf-py-8 wpuf-text-gray-500">
                    <div style="font-size: 16px; margin-bottom: 8px;">🔍 No icons found</div>
                    <div style="font-size: 12px;">Try searching with different keywords like "user", "email", "home"</div>
                </div>
            </div>
        </div>
    </div>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-field-multiselect">
<div v-if="met_dependencies" class="panel-field-opt panel-field-opt-select">
    <div class="wpuf-flex">
        <label v-if="option_field.title" class="!wpuf-mb-0">
            {{ option_field.title }} <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>
        </label>
    </div>

    <select
        :class="['term-list-selector']"
        class="wpuf-w-full wpuf-mt-2 wpuf-border-primary wpuf-z-30"
        v-model="value"
        multiple
    >
        <option
            class="checked:wpuf-bg-primary"
            v-for="(option, key) in dynamic_options"
            :value="key">{{ option }}</option>
    </select>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-field-option-data">
<div class="panel-field-opt panel-field-opt-text">
    <div class="wpuf-flex">
        <label
            class="wpuf-font-sm wpuf-text-gray-700">{{ option_field.title }}
        <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>
        </label>
    </div>
    <div class="wpuf-mt-2 wpuf-flex">
        <label class="wpuf-block text-sm/6 wpuf-font-medium wpuf-text-gray-700">
            <input
                type="checkbox"
                v-model="show_value"
                :class="builder_class_names('checkbox')"
                class="!wpuf-mr-2" />
            <?php esc_attr_e( 'Show values', 'wp-user-frontend' ); ?>
        </label>
        <label class="wpuf-block text-sm/6 wpuf-font-medium wpuf-text-gray-700 wpuf-ml-8">
            <input
                type="checkbox"
                v-model="sync_value"
                :class="builder_class_names('checkbox')"
                class="!wpuf-mr-2" />
            <?php esc_attr_e( 'Sync values', 'wp-user-frontend' ); ?>
        </label>
    </div>

    <div class="wpuf-mt-4">
        <div class="wpuf-flex wpuf-items-center wpuf-justify-between wpuf-mb-2">
            <div class="wpuf-flex wpuf-items-center wpuf-gap-2">
                <span class="wpuf-text-[14px] wpuf-text-gray-700 wpuf-font-medium"><?php esc_attr_e( 'Label & Values', 'wp-user-frontend' ); ?></span>
                <button
                    type="button"
                    @click="open_ai_modal"
                    class="wpuf-w-8 wpuf-h-8 wpuf-flex wpuf-items-center wpuf-justify-center wpuf-rounded-lg wpuf-shadow-sm hover:wpuf-shadow-md wpuf-border-0"
                    style="background: linear-gradient(135deg, #FFEE00 0%, #D500FF 28%, #0082FF 100%);"
                    title="<?php esc_attr_e( 'AI Generate Options', 'wp-user-frontend' ); ?>">
                    <svg class="wpuf-w-5 wpuf-h-5" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8.17766 13.2532L7.5 15.625L6.82234 13.2532C6.4664 12.0074 5.4926 11.0336 4.24682 10.6777L1.875 10L4.24683 9.32234C5.4926 8.9664 6.4664 7.9926 6.82234 6.74682L7.5 4.375L8.17766 6.74683C8.5336 7.9926 9.5074 8.9664 10.7532 9.32234L13.125 10L10.7532 10.6777C9.5074 11.0336 8.5336 12.0074 8.17766 13.2532Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M15.2157 7.26211L15 8.125L14.7843 7.26212C14.5324 6.25444 13.7456 5.46764 12.7379 5.21572L11.875 5L12.7379 4.78428C13.7456 4.53236 14.5324 3.74556 14.7843 2.73789L15 1.875L15.2157 2.73788C15.4676 3.74556 16.2544 4.53236 17.2621 4.78428L18.125 5L17.2621 5.21572C16.2544 5.46764 15.4676 6.25444 15.2157 7.26211Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M14.0785 17.1394L13.75 18.125L13.4215 17.1394C13.2348 16.5795 12.7955 16.1402 12.2356 15.9535L11.25 15.625L12.2356 15.2965C12.7955 15.1098 13.2348 14.6705 13.4215 14.1106L13.75 13.125L14.0785 14.1106C14.2652 14.6705 14.7045 15.1098 15.2644 15.2965L16.25 15.625L15.2644 15.9535C14.7045 16.1402 14.2652 16.5795 14.0785 17.1394Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
            <?php do_action( 'wpuf_field_option_data_actions' ); ?>
        </div>
        <table class="option-field-option-chooser">
            <tbody>
                <tr
                v-for="(option, index) in options"
                :key="option.id"
                :data-index="index"
                class="option-field-option wpuf-flex wpuf-justify-start wpuf-items-center">
                    <td class="wpuf-flex wpuf-items-center">
                        <input
                            v-if="option_field.is_multiple"
                            type="checkbox"
                            :value="option.value"
                            v-model="selected"
                            :class="builder_class_names('checkbox')"
                        >
                        <input
                            v-else
                            type="radio"
                            :value="option.value"
                            v-model="selected"
                            class="!wpuf-mt-0"
                            :class="builder_class_names('radio')"
                        >
                        <i class="fa fa-bars sort-handler hover:!wpuf-cursor-move wpuf-text-gray-400 wpuf-ml-1"></i>
                    </td>
                    <td>
                        <input
                            :class="[builder_class_names('text'), '!wpuf-w-full']"
                            type="text"
                            v-model="option.label"
                            @input="set_option_label(index, option.label)">
                    </td>
                    <td v-if="show_value">
                        <input
                            :class="[builder_class_names('text'), '!wpuf-w-full']"
                            type="text"
                            v-model="option.value">
                    </td>
                    <td>
                        <div class="wpuf-flex wpuf-ml-2">
                            <div
                                @click="delete_option(index)"
                                class="action-buttons hover:wpuf-cursor-pointer">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    class="wpuf-size-6 wpuf-border wpuf-rounded-2xl wpuf-border-gray-400 hover:wpuf-border-primary wpuf-p-1">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                                </svg>
                            </div>
                            <div
                                v-if="index === options.length - 1"
                                @click="add_option"
                                class="plus-buttons hover:wpuf-cursor-pointer !wpuf-border-0">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    class="wpuf-ml-1 wpuf-size-6 wpuf-border wpuf-rounded-2xl wpuf-border-gray-400 wpuf-p-1">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <a
        v-if="!option_field.is_multiple && selected"
        class="wpuf-inline-flex wpuf-items-center wpuf-gap-x-2 wpuf-rounded-md wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-text-gray-700  hover:wpuf-text-gray-700 hover:wpuf-bg-gray-50 wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 wpuf-mt-4"
        href="#clear"
        @click.prevent="clear_selection">
        <?php esc_attr_e( 'Clear Selection', 'wp-user-frontend' ); ?>
    </a>

    <!-- AI Generate Options Modal -->
    <div v-if="show_ai_modal" class="wpuf-ai-modal-overlay" @click="close_ai_modal">
        <div class="wpuf-ai-modal" @click.stop>
            <div class="wpuf-ai-modal-header">
                <h3><?php esc_attr_e( 'AI Generate Options', 'wp-user-frontend' ); ?></h3>
                <button type="button" @click="close_ai_modal" class="wpuf-ai-modal-close">&times;</button>
            </div>
            <div class="wpuf-ai-modal-body">
                <label class="wpuf-block wpuf-mb-2 wpuf-text-sm wpuf-font-medium">
                    <?php esc_attr_e( 'Describe the options you need', 'wp-user-frontend' ); ?>
                </label>
                <textarea
                    v-model="ai_prompt"
                    rows="3"
                    class="wpuf-w-full wpuf-px-3 wpuf-py-2 wpuf-border wpuf-rounded"
                    placeholder="<?php esc_attr_e( 'e.g., List of US states, Business categories, Job titles', 'wp-user-frontend' ); ?>"
                ></textarea>
                <div v-if="ai_error" class="wpuf-mt-2 wpuf-text-sm wpuf-text-red-600">{{ ai_error }}</div>
                <div v-if="ai_generated_options.length" class="wpuf-mt-4">
                    <div class="wpuf-flex wpuf-justify-between wpuf-items-center wpuf-mb-2">
                        <span class="wpuf-text-sm wpuf-font-medium"><?php esc_attr_e( 'Generated Options', 'wp-user-frontend' ); ?></span>
                        <button type="button" @click="select_all_ai_options" class="wpuf-text-sm wpuf-text-primary hover:wpuf-underline">
                            {{ all_ai_selected ? '<?php esc_attr_e( 'Deselect All', 'wp-user-frontend' ); ?>' : '<?php esc_attr_e( 'Select All', 'wp-user-frontend' ); ?>' }}
                        </button>
                    </div>
                    <div class="wpuf-ai-options-list">
                        <label v-for="(opt, idx) in ai_generated_options" :key="idx" class="wpuf-flex wpuf-items-center wpuf-py-1 wpuf-cursor-pointer hover:wpuf-bg-gray-50 wpuf-rounded wpuf-px-2">
                            <input
                                type="checkbox"
                                v-model="opt.selected"
                                :class="builder_class_names('checkbox')">
                            <span class="wpuf-text-sm wpuf-text-gray-700">{{ opt.label }}</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="wpuf-ai-modal-footer">
                <button type="button" @click="close_ai_modal" class="wpuf-btn wpuf-btn-secondary">
                    <?php esc_attr_e( 'Cancel', 'wp-user-frontend' ); ?>
                </button>
                <button v-if="!ai_generated_options.length" type="button" @click="generate_ai_options" :disabled="ai_loading || !ai_prompt" class="wpuf-rounded-md wpuf-text-center wpuf-bg-gradient-to-r wpuf-from-purple-600 wpuf-to-blue-600 wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-from-purple-700 hover:wpuf-to-blue-700 hover:wpuf-text-white focus:wpuf-from-purple-700 focus:wpuf-to-blue-700 focus:wpuf-text-white focus:wpuf-shadow-none hover:wpuf-cursor-pointer wpuf-inline-flex wpuf-items-center disabled:wpuf-opacity-50 disabled:wpuf-cursor-not-allowed wpuf-border-0">
                    <svg v-if="!ai_loading" class="wpuf-w-5 wpuf-h-5 wpuf-pr-1" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8.17766 13.2532L7.5 15.625L6.82234 13.2532C6.4664 12.0074 5.4926 11.0336 4.24682 10.6777L1.875 10L4.24683 9.32234C5.4926 8.9664 6.4664 7.9926 6.82234 6.74682L7.5 4.375L8.17766 6.74683C8.5336 7.9926 9.5074 8.9664 10.7532 9.32234L13.125 10L10.7532 10.6777C9.5074 11.0336 8.5336 12.0074 8.17766 13.2532Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M15.2157 7.26211L15 8.125L14.7843 7.26212C14.5324 6.25444 13.7456 5.46764 12.7379 5.21572L11.875 5L12.7379 4.78428C13.7456 4.53236 14.5324 3.74556 14.7843 2.73789L15 1.875L15.2157 2.73788C15.4676 3.74556 16.2544 4.53236 17.2621 4.78428L18.125 5L17.2621 5.21572C16.2544 5.46764 15.4676 6.25444 15.2157 7.26211Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M14.0785 17.1394L13.75 18.125L13.4215 17.1394C13.2348 16.5795 12.7955 16.1402 12.2356 15.9535L11.25 15.625L12.2356 15.2965C12.7955 15.1098 13.2348 14.6705 13.4215 14.1106L13.75 13.125L14.0785 14.1106C14.2652 14.6705 14.7045 15.1098 15.2644 15.2965L16.25 15.625L15.2644 15.9535C14.7045 16.1402 14.2652 16.5795 14.0785 17.1394Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <i v-if="ai_loading" class="fa fa-spinner fa-spin wpuf-mr-1"></i>
                    {{ ai_loading ? '<?php esc_attr_e( 'Generating...', 'wp-user-frontend' ); ?>' : '<?php esc_attr_e( 'Generate', 'wp-user-frontend' ); ?>' }}
                </button>
                <button v-else type="button" @click="import_ai_options" class="wpuf-rounded-md wpuf-text-center wpuf-bg-gradient-to-r wpuf-from-purple-600 wpuf-to-blue-600 wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-from-purple-700 hover:wpuf-to-blue-700 hover:wpuf-text-white focus:wpuf-from-purple-700 focus:wpuf-to-blue-700 focus:wpuf-text-white focus:wpuf-shadow-none hover:wpuf-cursor-pointer wpuf-inline-flex wpuf-items-center wpuf-border-0">
                    <?php esc_attr_e( 'Import Selected', 'wp-user-frontend' ); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- AI Provider Configuration Modal -->
    <div v-if="show_ai_config_modal" class="wpuf-fixed wpuf-top-0 wpuf-left-0 wpuf-w-screen wpuf-h-screen wpuf-bg-black wpuf-bg-opacity-50 wpuf-z-[1000000] wpuf-flex wpuf-items-center wpuf-justify-center">
        <div class="wpuf-bg-white wpuf-rounded-md wpuf-p-8 wpuf-max-w-xl wpuf-w-full wpuf-mx-5 wpuf-relative">
            <!-- Key Icon -->
            <div class="wpuf-flex wpuf-justify-center wpuf-mb-8">
                <svg width="110" height="110" viewBox="0 0 110 110" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="110" height="110" rx="55" fill="#D1FAE5"/>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M60 41C55.0294 41 51 45.0294 51 50C51 50.525 51.0451 51.0402 51.1317 51.5419C51.2213 52.0604 51.089 52.4967 50.8369 52.7489L42.1716 61.4142C41.4214 62.1644 41 63.1818 41 64.2426V68C41 68.5523 41.4477 69 42 69H47C47.5523 69 48 68.5523 48 68V66H50C50.5523 66 51 65.5523 51 65V63H53C53.2652 63 53.5196 62.8946 53.7071 62.7071L57.2511 59.1631C57.5033 58.911 57.9396 58.7787 58.4581 58.8683C58.9598 58.9549 59.475 59 60 59C64.9706 59 69 54.9706 69 50C69 45.0294 64.9706 41 60 41ZM60 45C59.4477 45 59 45.4477 59 46C59 46.5523 59.4477 47 60 47C61.6569 47 63 48.3431 63 50C63 50.5523 63.4477 51 64 51C64.5523 51 65 50.5523 65 50C65 47.2386 62.7614 45 60 45Z" fill="#065F46"/>
                </svg>
            </div>

            <!-- Title -->
            <h2 class="wpuf-text-2xl wpuf-font-medium wpuf-text-center wpuf-text-gray-900 wpuf-mb-4">
                <?php esc_attr_e( 'AI Provider Not Configured', 'wp-user-frontend' ); ?>
            </h2>

            <!-- Description -->
            <p class="wpuf-text-lg wpuf-text-center wpuf-text-gray-400 wpuf-mb-16">
                <?php esc_attr_e( 'To use AI Form Generation, please connect an AI provider by adding your API key in the settings', 'wp-user-frontend' ); ?>
            </p>

            <!-- Buttons -->
            <div class="wpuf-flex wpuf-justify-center wpuf-gap-3">
                <button
                    type="button"
                    @click="close_ai_config_modal"
                    class="wpuf-px-6 wpuf-py-3 wpuf-border wpuf-border-gray-300 wpuf-rounded-md wpuf-text-gray-700 hover:wpuf-bg-gray-50 wpuf-text-lg wpuf-transition-colors wpuf-min-w-[101px]">
                    <?php esc_attr_e( 'Cancel', 'wp-user-frontend' ); ?>
                </button>
                <button
                    type="button"
                    @click="go_to_ai_settings"
                    class="wpuf-px-6 wpuf-py-3 wpuf-bg-emerald-700 hover:wpuf-bg-emerald-800 wpuf-text-white wpuf-rounded-md wpuf-text-lg wpuf-transition-colors wpuf-min-w-[158px]">
                    <?php esc_attr_e( 'Go to Settings', 'wp-user-frontend' ); ?>
                </button>
            </div>
        </div>
    </div>
    <?php do_action( 'wpuf_field_option_data_after' ); ?>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-field-option-pro-feature-alert">
<div class="panel-field-opt panel-field-opt-pro-feature wpuf-flex wpuf-items-center wpuf-text-sm wpuf-text-gray-700 wpuf-font-medium">
    <label>{{ option_field.title }} </label><br>
    <label
        class="wpuf-pro-text-alert wpuf-ml-2 wpuf-tooltip-top"
        data-tip="<?php esc_attr_e( 'Available in PRO version', 'wp-user-frontend' ); ?>">
        <a :href="pro_link" target="_blank"><img src="<?php echo esc_url( wpuf_get_pro_icon() ) ?>" alt="pro icon"></a>
    </label>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-field-options">
<div class="wpuf-form-builder-field-options">
    <div v-if="!parseInt(editing_field_id)" class="options-fileds-section text-center">
        <p class="wpuf-text-gray-500 wpuf-text-lg wpuf-font-medium">{{ i18n.empty_field_options_msg }}</p>
    </div>

    <div v-else>
        <div class="option-fields-section wpuf-mt-6">
            <h3
                :class="show_basic_settings ? 'wpuf-text-primary' : 'wpuf-text-gray-500'"
                class="wpuf-flex wpuf-mt-0 wpuf-mb-6 wpuf-justify-between hover:wpuf-cursor-pointer wpuf-font-medium wpuf-text-lg"
                @click="show_basic_settings = !show_basic_settings">
                {{ form_field_type_title }}
                <i :class="show_basic_settings ? 'fa fa-angle-down wpuf-text-primary' : 'fa fa-angle-right wpuf-text-gray-500'"></i>
            </h3>

            <transition name="slide-fade">
                <div v-show="show_basic_settings" class="option-field-section-fields">
                    <component
                        v-for="option_field in basic_settings"
                        :key="option_field.name"
                        :is="'field-' + option_field.type"
                        :option_field="option_field"
                        :editing_form_field="editing_form_field"
                    ></component>
                </div>
            </transition>
        </div>

        <div v-if="advanced_settings.length" class="option-fields-section">
            <h3
                :class="show_advanced_settings ? 'wpuf-text-primary' : 'wpuf-text-gray-500'"
                class="wpuf-flex wpuf-mt-0 wpuf-mb-6 wpuf-justify-between hover:wpuf-cursor-pointer wpuf-font-medium wpuf-text-lg"
                @click="show_advanced_settings = !show_advanced_settings">
                {{ i18n.advanced_options }}
                <i :class="show_advanced_settings ? 'fa fa-angle-down wpuf-text-primary' : 'fa fa-angle-right wpuf-text-gray-500'"></i>
            </h3>

            <transition name="slide-fade">
                <div v-show="show_advanced_settings" class="option-field-section-fields">
                    <component
                        v-for="option_field in advanced_settings"
                        :key="option_field.name"
                        :is="'field-' + option_field.type"
                        :option_field="option_field"
                        :editing_form_field="editing_form_field"
                    ></component>
                </div>
            </transition>
        </div>

        <?php do_action( 'wpuf_builder_field_options' ); ?>
    </div>

</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-field-radio">
<div v-if="met_dependencies" class="panel-field-opt panel-field-opt-radio">
    <div class="wpuf-flex">
        <label
            class="wpuf-option-field-title wpuf-font-sm wpuf-text-gray-700 wpuf-font-medium">{{ option_field.title }}</label>
        <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>
    </div>
    <div
        v-if="option_field.inline"
        class="wpuf-flex">
        <div
            v-for="(option, key, index) in option_field.options"
            class="wpuf-items-center">
            <label
                :class="index !== 0 ? 'wpuf-ml-8' : ''"
                class="wpuf-block text-sm/6 wpuf-font-medium wpuf-text-gray-900 !wpuf-mb-0">
                <input
                    type="radio"
                    :value="key"
                    v-model="value"
                    :class="builder_class_names('radio')">
                {{ option }}
            </label>
        </div>
    </div>
    <div
        v-else
        class="wpuf-flex wpuf-items-center"
        :class="index < Object.keys(option_field.options).length - 1 ? 'wpuf-mb-3' : ''"
        v-for="(option, key, index) in option_field.options">
        <label class="!wpuf-mb-0">
            <input
                type="radio"
                :value="key"
                v-model="value"
                :class="builder_class_names('radio')">
            {{ option }}
        </label>

    </div>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-field-range">
<div v-if="met_dependencies" class="panel-field-opt panel-field-opt-text">
    <div class="wpuf-flex">
        <label>
            {{ option_field.title }} <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>
            {{ option_field.min_column }}
        </label>
    </div>
    <input
        type="range"
        v-model="value"
        v-bind:min="minColumn"
        v-bind:max="maxColumn"
    >
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-field-select">
<div v-if="met_dependencies" class="panel-field-opt panel-field-opt-select">
    <div class="wpuf-flex">
        <label v-if="option_field.title" class="!wpuf-mb-0">
            {{ option_field.title }} <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>
        </label>
    </div>

    <div class="option-fields-section wpuf-relative">
        <div
            @click="showOptions = !showOptions"
            class="wpuf-my-4 wpuf-w-full wpuf-min-w-full !wpuf-py-[10px] !wpuf-px-[14px] wpuf-text-gray-700 wpuf-font-medium !wpuf-shadow-sm wpuf-border !wpuf-border-gray-300 !wpuf-rounded-[6px] focus:!wpuf-ring-transparent focus:checked:!wpuf-ring-transparent hover:checked:!wpuf-ring-transparent hover:!wpuf-text-gray-700 wpuf-flex wpuf-justify-between wpuf-items-center !wpuf-text-base"
        >
            {{ selectedOption }}
            <i
                :class="showOptions ? 'fa-angle-up' : 'fa-angle-down'"
                class="fa wpuf-text-base"></i>
        </div>

        <div
            v-if="showOptions"
            class="wpuf-absolute wpuf-bg-white wpuf-border wpuf-border-gray-300 wpuf-rounded-lg wpuf-w-full wpuf-z-40 wpuf--mt-4">
            <ul>
                <li
                    v-for="(option, key) in option_field.options"
                    @click="[value = key, showOptions = false, selectedOption = option]"
                    :value="key"
                    class="wpuf-text-sm wpuf-color-gray-900 wpuf-py-2 wpuf-px-4 hover:wpuf-cursor-pointer hover:wpuf-bg-gray-100">{{ option }}</li>
            </ul>
        </div>
    </div>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-field-text">
<div v-if="met_dependencies" class="panel-field-opt panel-field-opt-text">
    <div class="wpuf-flex">
        <label
            :for="option_field.name"
            class="wpuf-option-field-title wpuf-font-sm wpuf-text-gray-700 wpuf-font-medium">{{ option_field.title }}
        <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>
        </label>
    </div>
    <input
        v-if="option_field.variation && 'number' === option_field.variation"
        type="number"
        v-model="value"
        @focusout="on_focusout"
        @keyup="on_keyup"
        :class="builder_class_names('text')">

    <input
        v-if="!option_field.variation"
        type="text"
        v-model="value"
        @focusout="on_focusout"
        @keyup="on_keyup"
        :disabled="option_field.disabled"
        :readonly="option_field.readonly"
        :class="builder_class_names('text')">
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-field-text-meta">
<div v-if="met_dependencies" class="panel-field-opt panel-field-opt-text panel-field-opt-text-meta">
    <div class="wpuf-flex">
        <label
            :for="option_field.title"
            class="wpuf-option-field-title wpuf-font-sm wpuf-text-gray-700 wpuf-font-medium">{{ option_field.title }}</label>
        <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>
    </div>
    <div class="wpuf-mt-2">
        <input
            type="text"
            v-model="value"
            :readonly="option_field.is_read_only"
            :class="builder_class_names('text')">
    </div>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-field-textarea">
<div class="panel-field-opt panel-field-opt-textarea">
    <div class="wpuf-flex">
        <label class="wpuf-mb-2">
            {{ option_field.title }} <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>
        </label>
    </div>
    <textarea :class="builder_class_names('textareafield')" :rows="option_field.rows || 5" v-model="value"></textarea>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-field-visibility">
<div class="panel-field-opt panel-field-opt-radio">
    <div class="wpuf-flex">
        <label
            v-if="option_field.title"
            class="wpuf-option-field-title wpuf-font-sm wpuf-text-gray-700 wpuf-font-medium">{{ option_field.title }}</label>
        <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>
    </div>

    <div
        v-if="!option_field.inline"
        class="wpuf-flex wpuf-items-center wpuf-gap-x-2 wpuf-m-2"
        v-for="(option, key) in option_field.options">
        <label
            class="wpuf-block text-sm/6 wpuf-font-medium wpuf-text-gray-900">
            <input
                type="radio"
                :name="'visibility_' + editing_form_field.id"
                :value="key"
                v-model="selected"
                class="checked:!wpuf-bg-primary checked:before:!wpuf-bg-transparent">
            {{ option }}</label>
    </div>

    <div
        v-if="option_field.inline"
        class="wpuf-mt-2 wpuf-flex wpuf-flex-wrap">
        <div
            v-for="(option, key, index) in option_field.options"
            class="wpuf-items-center wpuf-mr-9">
            <label
                class="wpuf-block wpuf-my-1 wpuf-mr-2 wpuf-font-medium wpuf-text-gray-900">
                <input
                    type="radio"
                    :name="'visibility_' + editing_form_field.id"
                    :value="key"
                    v-model="selected"
                    :class="builder_class_names('radio')">
                {{ option }}
            </label>
        </div>
    </div>

    <div v-if="'logged_in' === selected" class="condiotional-logic-container wpuf-mt-2">

    	<?php $roles = get_editable_roles(); ?>

    	<ul>
			<?php
                foreach ( $roles as $role => $value ) {
                    $role_name = $value['name'];

                    $output  = '<li class="wpuf-mt-2 wpuf-flex wpuf-items-center">';
                    $output .= "<label class='wpuf-flex wpuf-items-center'><input :class=\"builder_class_names('checkbox')\" class=\"!wpuf-mr-2\" type=\"checkbox\" v-model=\"choices\" value=\"{$role}\"> {$role_name} </label>";
                    $output .= '</li>';

                    $allowed_html = [
                        'li'    => [
                            'class' => true,
                        ],
                        'label' => [
                            'class' => true,
                        ],
                        'input' => [
                            'class' => true,
                            'type'  => true,
                            'value' => true,
                        ],
                    ];

                    // Apply standard wp_kses first
                    $partially_filtered = wp_kses( $output, $allowed_html );

                    // Then re-add Vue attributes with explicit pattern matching for safety
                    $vue_attributes = array(
                        ':class="builder_class_names(\'checkbox\')"',
                        'v-model="choices"'
                    );

                    foreach ($vue_attributes as $attr) {
                        // Safely insert the attribute back into the input tag
                        $partially_filtered = preg_replace('/(<input[^>]+)/', '$1 ' . $attr, $partially_filtered, 1);
                    }

                    echo $partially_filtered;
                }
            ?>
	    </ul>
    </div>

    <div v-if="'subscribed_users' === selected" class="condiotional-logic-container wpuf-mt-2">

    	<ul>
    		<?php

                if ( class_exists( 'WeDevs\Wpuf\Admin\Subscription' ) ) {
                    $subscriptions  = wpuf()->subscription->get_subscriptions();

                    if ( $subscriptions ) {
                        foreach ( $subscriptions as $pack ) {
                            $output  = '<li class="wpuf-mt-2 wpuf-flex wpuf-items-center">';
                            $output .= "<label class='wpuf-flex wpuf-items-center'><input  :class=\"builder_class_names('checkbox')\" class=\"!wpuf-mr-2\" type='checkbox' v-model='choices' value='{$pack->ID}' > {$pack->post_title} </label>";
                            $output .= '</li>';

                            $allowed_html = [
                                'li'    => [
                                    'class' => true,
                                ],
                                'label' => [
                                    'class' => true,
                                ],
                                'input' => [
                                    'class' => true,
                                    'type'  => true,
                                    'value' => true,
                                ],
                            ];

                            // Apply standard wp_kses first
                            $partially_filtered = wp_kses( $output, $allowed_html );

                            // Then re-add Vue attributes with explicit pattern matching for safety
                            $vue_attributes = array(
                                ':class="builder_class_names(\'checkbox\')"',
                                'v-model="choices"'
                            );

                            foreach ($vue_attributes as $attr) {
                                // Safely insert the attribute back into the input tag
                                $partially_filtered = preg_replace('/(<input[^>]+)/', '$1 ' . $attr, $partially_filtered, 1);
                            }

                            echo $partially_filtered;
                        }
                    } else {
                        esc_html_e( 'No subscription plan found.', 'wp-user-frontend' );
                    }
                }
            ?>
    	</ul>

    </div>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-checkbox_field">
<div class="wpuf-fields">
    <div
        v-if="field.inline !== 'yes'"
        class="wpuf-space-y-2">
        <div
            v-if="has_options" v-for="(label, val) in field.options"
            class="wpuf-relative wpuf-flex wpuf-items-center">
            <div class="wpuf-flex wpuf-items-center">
                <input
                    type="checkbox"
                    :value="val"
                    :checked="is_selected(val)"
                    :class="builder_class_names('checkbox')">
                <label>{{ label }}</label>
            </div>
        </div>
    </div>

    <div
        v-else
        class="wpuf-flex"
    >
        <div
            v-if="has_options" v-for="(label, val) in field.options"
            class="wpuf-relative wpuf-flex wpuf-items-center wpuf-mr-4">
            <input
                type="checkbox"
                :value="val"
                :checked="is_selected(val)"
                :class="builder_class_names('checkbox')"
                class="!wpuf-mt-[.5px] wpuf-rounded wpuf-border-gray-300 wpuf-text-indigo-600">
            <label>{{ label }}</label>
        </div>
    </div>

    <p v-if="field.help" class="wpuf-mt-2 wpuf-mb-0 wpuf-text-sm wpuf-text-gray-500" v-html="field.help"></p>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-cloudflare_turnstile">
<div class="wpuf-fields">
    <template v-if="!has_turnstile_api_keys">
        <p v-html="no_api_keys_msg"></p>
    </template>

    <template v-else>
        <img
            class="wpuf-turnstile-placeholder"
            :src="turnstile_image"
            alt="">
    </template>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-column_field">
<div
    :class="'has-columns-'+field.columns"
    class="wpuf-field-columns wpuf-flex md:wpuf-flex-row wpuf-gap-4 wpuf-p-4 wpuf-w-full wpuf-justify-between wpuf-rounded-t-md !wpuf-border-t !wpuf-border-r !wpuf-border-l !wpuf-border-dashed !wpuf-border-transparent  group-hover:!wpuf-border-green-400 group-hover:wpuf-cursor-pointer">
    <div
        v-for="column in columnClasses"
        :style="{paddingRight: field.column_space+'px'}"
        :key="column"
        class="wpuf-flex-1 wpuf-min-w-0 wpuf-min-h-full wpuf-column-inner-fields">
        <div
            :data-column="column"
            class="wpuf-border wpuf-border-dashed wpuf-border-green-400 wpuf-bg-green-50 wpuf-shadow-sm wpuf-rounded-md wpuf-p-1">
            <ul class="wpuf-column-fields-sortable-list wpuf-min-h-16">
                <li
                    v-for="(field, innerIndex) in column_fields[column]"
                    :key="field.id"
                    :column-field-index="innerIndex"
                    :in-column="column"
                    data-source="column-field-stage"
                    class="!wpuf-m-0 !wpuf-p-0 wpuf-group/column-inner hover:wpuf-bg-green-50 wpuf-transition wpuf-duration-150 wpuf-ease-out column-field-items wpuf-el wpuf-rounded-t-md"
                    :class="[
                        field.name,
                        field.css,
                        'form-field-' + field.template,
                        field.width ? 'field-size-' + field.width : '',
                        ('custom_hidden_field' === field.template) ? 'hidden-field' : '',
                        parseInt(editing_form_id) === parseInt(field.id) ? 'wpuf-bg-green-50' : ''
                      ]">
                    <div class="wpuf-flex wpuf-flex-col md:wpuf-flex-row wpuf-gap-2 wpuf-p-4 wpuf-border-transparent group-hover/column-inner:wpuf-border-primary wpuf-rounded-t-md wpuf-border-t wpuf-border-r wpuf-border-l wpuf-border-dashed wpuf-border-emerald-400">
                        <div
                            v-if="!(is_full_width(field.template) || is_pro_preview(field.template))">
                            <label v-if="!is_invisible(field)"
                                   :for="'wpuf-' + (field.name ? field.name : 'cls')"
                                   class="wpuf-block wpuf-text-sm">
                                {{ field.label }}
                                <span v-if="field.required && 'yes' === field.required"
                                      class="required">*</span>
                            </label>
                        </div>
                        <div
                            :class="[
                             'wpuf-relative wpuf-min-w-0', // Added wpuf-min-w-0
                             (is_full_width(field.template) || is_pro_preview(field.template))
                               ? 'wpuf-w-full'
                               : 'wpuf-w-full md:wpuf-w-3/4'
                           ]">
                            <div class="wpuf-absolute wpuf-w-full wpuf-h-full wpuf-z-10"></div>
                            <div class="wpuf-relative">
                                <component
                                    v-if="is_template_available(field)"
                                   :is="'form-' + field.template"
                                   :field="field">
                                </component>
                                <div v-if="is_pro_preview(field.template)" class="stage-pro-alert wpuf-text-center">
                                    <label class="wpuf-pro-text-alert">
                                        <a :href="pro_link" target="_blank"
                                           class="wpuf-text-gray-700 wpuf-text-base"><strong>{{ get_field_name( field.template )
                                                }}</strong> <?php esc_html_e( 'is available in Pro Version', 'wp-user-frontend' ); ?></a>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        class="wpuf-column-field-control-buttons wpuf-opacity-0 group-hover/column-inner:wpuf-opacity-100 wpuf-rounded-b-lg wpuf-bg-primary wpuf-items-center wpuf-transition wpuf-duration-150 wpuf-ease-out wpuf-flex wpuf-justify-center">
                        <div class="wpuf-items-center wpuf-text-green-200 wpuf-flex wpuf-justify-evenly wpuf-p-1">
                            <template v-if="!is_failed_to_validate(field.template)">
                                <span class="!wpuf-mt-2.5">
                                    <i class="fa fa-arrows move wpuf-pr-2 wpuf-rounded-l-md hover:!wpuf-cursor-move wpuf-border-r wpuf-border-green-200 wpuf-text-[17px]"></i>
                                </span>
                                <span :class="action_button_classes"
                                    @click="open_column_field_settings(field, innerIndex, column)">
                                    <svg class="wpuf-mr-2" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M5.43306 13.9163L6.69485 10.7618C6.89603 10.2589 7.19728 9.802 7.58033 9.41896L14.4995 2.50023C15.3279 1.6718 16.6711 1.6718 17.4995 2.50023C18.3279 3.32865 18.3279 4.6718 17.4995 5.50023L10.5803 12.419C10.1973 12.802 9.74042 13.1033 9.23746 13.3044L6.08299 14.5662C5.67484 14.7295 5.2698 14.3244 5.43306 13.9163Z" fill="#A7F3D0"/>
<path d="M3.5 5.74951C3.5 5.05916 4.05964 4.49951 4.75 4.49951H10C10.4142 4.49951 10.75 4.16373 10.75 3.74951C10.75 3.3353 10.4142 2.99951 10 2.99951H4.75C3.23122 2.99951 2 4.23073 2 5.74951V15.2495C2 16.7683 3.23122 17.9995 4.75 17.9995H14.25C15.7688 17.9995 17 16.7683 17 15.2495V9.99951C17 9.5853 16.6642 9.24951 16.25 9.24951C15.8358 9.24951 15.5 9.5853 15.5 9.99951V15.2495C15.5 15.9399 14.9404 16.4995 14.25 16.4995H4.75C4.05964 16.4995 3.5 15.9399 3.5 15.2495V5.74951Z" fill="#A7F3D0"/>
</svg>
                                </span>
                                <span :class="action_button_classes"
                                    @click="clone_column_field(field, innerIndex, column)">
                                    <svg class="wpuf-mr-2" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M13.75 6.875V5C13.75 3.96447 12.9105 3.125 11.875 3.125H5C3.96447 3.125 3.125 3.96447 3.125 5V11.875C3.125 12.9105 3.96447 13.75 5 13.75H6.875M13.75 6.875H15C16.0355 6.875 16.875 7.71447 16.875 8.75V15C16.875 16.0355 16.0355 16.875 15 16.875H8.75C7.71447 16.875 6.875 16.0355 6.875 15V13.75M13.75 6.875H8.75C7.71447 6.875 6.875 7.71447 6.875 8.75V13.75" stroke="#A7F3D0" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
                                </span>
                            </template>
                            <template v-else>
                                <span :class="action_button_classes">
                                <i class="fa fa-arrows control-button-disabled wpuf--ml-1 wpuf-rounded-l-md"></i>
                            </span>
                                <span :class="action_button_classes">
                                <i class="fa fa-pencil control-button-disabled wpuf--ml-1"></i>
                            </span>
                                <span :class="action_button_classes">
                                <i
                                    class="fa fa-clone control-button-disabled wpuf--ml-1"></i>
                            </span>
                            </template>
                            <span :class="action_button_classes" @click="delete_column_field(innerIndex, column)">
                                <svg class="wpuf-mr-2" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M12.2837 7.5L11.9952 15M8.00481 15L7.71635 7.5M16.023 4.82547C16.308 4.86851 16.592 4.91456 16.875 4.96358M16.023 4.82547L15.1332 16.3938C15.058 17.3707 14.2434 18.125 13.2637 18.125H6.73631C5.75655 18.125 4.94198 17.3707 4.86683 16.3938L3.97696 4.82547M16.023 4.82547C15.0677 4.6812 14.1013 4.57071 13.125 4.49527M3.125 4.96358C3.40798 4.91456 3.69198 4.86851 3.97696 4.82547M3.97696 4.82547C4.93231 4.6812 5.89874 4.57071 6.875 4.49527M13.125 4.49527V3.73182C13.125 2.74902 12.3661 1.92853 11.3838 1.8971C10.9244 1.8824 10.463 1.875 10 1.875C9.53696 1.875 9.07565 1.8824 8.61618 1.8971C7.63388 1.92853 6.875 2.74902 6.875 3.73182V4.49527M13.125 4.49527C12.0938 4.41558 11.0516 4.375 10 4.375C8.94836 4.375 7.9062 4.41558 6.875 4.49527" stroke="#A7F3D0" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
                        </span>
                            <span :class="action_button_classes"
                                v-if="is_pro_preview(field.template)"
                                class="hover:wpuf-bg-primary">
                            <a
                                :href="pro_link"
                                target="_blank"
                                class="wpuf-rounded-r-md hover:wpuf-bg-slate-500 hover:wpuf-cursor-pointer wpuf-transition wpuf-duration-150 wpuf-ease-out hover:wpuf-transition-all">
                                <img src="<?php echo esc_attr( WPUF_ASSET_URI . '/images/pro-badge.svg' ); ?>" alt="">
                            </a>
                        </span>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-custom_hidden_field">
<div class="wpuf-fields">
    <input
        type="text"
        :class="builder_class_names('text_hidden')"
        :placeholder="field.placeholder"
        :value="field.default"
        :size="field.size"
    >
    <p v-if="field.help" class="wpuf-mt-2 wpuf-mb-0 wpuf-text-sm wpuf-text-gray-500" v-html="field.help"></p>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-custom_html">
<div class="wpuf-fields" v-html="field.html"></div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-dropdown_field">
<div class="wpuf-fields">
    <select
        :class="builder_class_names('dropdown')">
        <option v-if="field.first" value="">{{ field.first }}</option>
        <option
            v-if="has_options"
            v-for="(label, val) in field.options"
            :value="label"
            :selected="is_selected(label)"
        >{{ label }}</option>
    </select>
    <p v-if="field.help" class="wpuf-mt-2 wpuf-mb-0 wpuf-text-sm wpuf-text-gray-500" v-html="field.help"></p>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-email_address">
<div class="wpuf-fields">
    <input
        type="email"
        :class="builder_class_names('text')"
        :placeholder="field.placeholder"
        :value="field.default"
        :size="field.size"
    >
    <p v-if="field.help" class="wpuf-mt-2 wpuf-mb-0 wpuf-text-sm wpuf-text-gray-500" v-html="field.help"></p>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-featured_image">
<div class="wpuf-fields">
    <div :id="'wpuf-img_label-' + field.id + '-upload-container'">
        <div class="wpuf-attachment-upload-filelist" data-type="file" data-required="yes">
            <a class="wpuf-inline-flex wpuf-items-center wpuf-gap-x-1.5"
               :class="builder_class_names('upload_btn')" href="#">
                <template v-if="field.button_label === ''">
                    <?php esc_html_e( 'Select Image', 'wp-user-frontend' ); ?>
                </template>
                <template v-else>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="wpuf-size-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                    </svg>
                    {{ field.button_label }}
                </template>
            </a>
        </div>
    </div>

    <p v-if="field.help" class="wpuf-mt-2 wpuf-mb-0 wpuf-text-sm wpuf-text-gray-500" v-html="field.help"></p>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-fields">
<div class="wpuf-form-builder-form-fields">
    <template v-for="(section, index) in panel_sections">
        <div v-if="section.fields.length" class="panel-form-field-group clearfix">
            <h3 class="clearfix" @click="panel_toggle(index)">
                {{ section.title }} <i :class="[section.show ? 'fa fa-angle-down' : 'fa fa-angle-right']"></i>
            </h3>

            <transition name="slide-fade">
                <ul
                    v-show="section.show"
                    class="panel-form-field-buttons clearfix"
                    :id="'panel-form-field-buttons-' + section.id"
                >
                    <template v-for="field in section.fields">
                        <li
                            v-if="is_pro_feature(field)"
                            class="button button-faded"
                            :data-form-field="field"
                            data-source="panel"
                            @click="alert_pro_feature(field)"
                        >
                            <i v-if="field_settings[field].icon" :class="['fa fa-' + field_settings[field].icon]" aria-hidden="true"></i> {{ field_settings[field].title }}
                        </li>

                        <li
                            v-if="is_failed_to_validate(field)"
                            :class="['button', get_invalidate_btn_class(field)]"
                            :data-form-field="field"
                            data-source="panel"
                            @click="alert_invalidate_msg(field)"
                        >
                            <i v-if="field_settings[field].icon" :class="['fa fa-' + field_settings[field].icon]" aria-hidden="true"></i> {{ field_settings[field].title }}
                        </li>

                        <li
                            v-if="!is_pro_feature(field) && !is_failed_to_validate(field)"
                            class="button"
                            :data-form-field="field"
                            data-source="panel"
                            @click="add_form_field(field)"
                        >
                            <i v-if="field_settings[field].icon" :class="['fa fa-' + field_settings[field].icon]" aria-hidden="true"></i> {{ field_settings[field].title }}
                        </li>
                    </template>
                </ul>
            </transition>
        </div>
    </template>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-fields-v4-1">
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
                    :class="section.show ? 'wpuf-text-primary' : 'wpuf-text-gray-500'"
                    class="wpuf-flex wpuf-justify-between hover:wpuf-cursor-pointer wpuf-text-base wpuf-m-0 !wpuf-font-medium"
                    @click="panel_toggle(index)">
                    {{ section.title }}
                    <i
                        :class="[section.show ? 'fa fa-angle-down wpuf-text-primary' : 'fa fa-angle-right wpuf-text-gray-500']"
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
                                <div class="wpuf-opacity-50 wpuf-field-button wpuf-flex wpuf-items-center wpuf-rounded-lg wpuf-border wpuf-border-gray-200 wpuf-bg-white wpuf-shadow-sm wpuf-p-4 hover:wpuf-border-gray-300 hover:wpuf-cursor-pointer">
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
</script>

<script type="text/x-template" id="tmpl-wpuf-form-image_upload">
<div class="wpuf-fields">
    <div :id="'wpuf-img_label-' + field.id + '-upload-container'">
        <div class="wpuf-attachment-upload-filelist" data-type="file" data-required="yes">
            <a
                class="wpuf-inline-flex wpuf-items-center wpuf-gap-x-1.5"
                :class="builder_class_names('upload_btn')" href="#">
                <template v-if="field.button_label === ''">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="wpuf-size-5">
                        <path d="M8.75 3.75a.75.75 0 0 0-1.5 0v3.5h-3.5a.75.75 0 0 0 0 1.5h3.5v3.5a.75.75 0 0 0 1.5 0v-3.5h3.5a.75.75 0 0 0 0-1.5h-3.5v-3.5Z" />
                    </svg>
                    <?php esc_html_e( 'Select Image', 'wp-user-frontend' ); ?>
                </template>
                <template v-else>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="wpuf-size-5">
                        <path d="M8.75 3.75a.75.75 0 0 0-1.5 0v3.5h-3.5a.75.75 0 0 0 0 1.5h3.5v3.5a.75.75 0 0 0 1.5 0v-3.5h3.5a.75.75 0 0 0 0-1.5h-3.5v-3.5Z" />
                    </svg>
                    {{ field.button_label }}
                </template>
            </a>
        </div>
    </div>

    <p v-if="field.help" class="wpuf-mt-2 wpuf-mb-0 wpuf-text-sm wpuf-text-gray-500" v-html="field.help"></p>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-multiple_select">
<div class="wpuf-fields">
    <select
        :class="builder_class_names('multi_label')"
        class="wpuf-block wpuf-w-full wpuf-min-w-full wpuf-rounded-md wpuf-py-1.5 wpuf-text-gray-900 wpuf-shadow-sm   placeholder:wpuf-text-gray-400 sm:wpuf-text-sm sm:wpuf-leading-6 wpuf-border !wpuf-border-gray-300"
        multiple
    >
        <option v-if="field.first" value="">{{ field.first }}</option>

        <option
            v-if="has_options"
            v-for="(label, val) in field.options"
            :value="label"
            :selected="is_selected(label)"
        >{{ label }}</option>
    </select>

    <span v-if="field.help" class="wpuf-help" v-html="field.help"></span>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-post_content">
<div class="wpuf-fields">
    <div
        v-if="field.insert_image === 'yes'"
        class="wpuf-attachment-upload-filelist" data-type="file" data-required="yes">
        <a
            class="wpuf-inline-flex wpuf-items-center wpuf-gap-x-1.5"
            :class="builder_class_names('upload_btn')" href="#">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="wpuf-size-5">
            <path d="M8.75 3.75a.75.75 0 0 0-1.5 0v3.5h-3.5a.75.75 0 0 0 0 1.5h3.5v3.5a.75.75 0 0 0 1.5 0v-3.5h3.5a.75.75 0 0 0 0-1.5h-3.5v-3.5Z" />
            </svg>
            <?php esc_html_e( 'Insert Photo', 'wp-user-frontend' ); ?>
        </a>
    </div>
    <br v-if="field.insert_image === 'yes'" />

    <textarea
        v-if="'no' === field.rich"
        :class="builder_class_names('textareafield')"
        :placeholder="field.placeholder"
        :default_text="field.default"
        :rows="field.rows"
        :cols="field.cols"
    >{{ field.default }}</textarea>

    <text-editor v-if="'no' !== field.rich" :rich="field.rich" :default_text="field.default"></text-editor>

    <span v-if="field.help" class="wpuf-help" v-html="field.help" />
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-post_excerpt">
<div class="wpuf-fields">
    <textarea
        v-if="'no' === field.rich"
        :class="builder_class_names('textareafield')"
        :placeholder="field.placeholder"
        :default_text="field.default"
        :rows="field.rows"
        :cols="field.cols"
    >{{ field.default }}</textarea>

    <text-editor v-if="'no' !== field.rich" :rich="field.rich" :default_text="field.default"></text-editor>

    <p v-if="field.help" class="wpuf-mt-2 wpuf-mb-0 wpuf-text-sm wpuf-text-gray-500" v-html="field.help"></p>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-post_tags">
<div class="wpuf-fields">
    <input
        type="text"
        :class="builder_class_names('text')"
        :placeholder="field.placeholder"
        :value="field.default"
        :size="field.size"
    >

    <p v-if="field.help" class="wpuf-mt-2 wpuf-mb-0 wpuf-text-sm wpuf-text-gray-500" v-html="field.help"></p>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-post_title">
<div class="wpuf-fields">
    <input
        type="text"
        :placeholder="field.placeholder"
        :value="field.default"
        :size="field.size"
        :class="builder_class_names('text')"
    >
    <p v-if="field.help" class="wpuf-mt-2 wpuf-mb-0 wpuf-text-sm wpuf-text-gray-500" v-html="field.help"></p>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-radio_field">
<div class="wpuf-fields">
    <div
        v-if="field.inline !== 'yes'"
        class="wpuf-space-y-2">
        <div
            v-if="has_options" v-for="(label, val) in field.options"
            class="wpuf-flex wpuf-items-center">
            <input
                type="radio"
                :value="val"
                :checked="is_selected(val)"
                :id="'radio-' + field.name + '-' + val"
                :class="builder_class_names('radio')">
            <label :for="'radio-' + field.name + '-' + val">{{ label }}</label>
        </div>
    </div>

    <div
        v-else
        class="wpuf-space-y-6 sm:wpuf-flex sm:wpuf-items-center sm:wpuf-space-x-10 sm:wpuf-space-y-0">
        <div
            v-if="has_options" v-for="(label, val) in field.options"
            class="wpuf-flex wpuf-items-center">
            <input
                type="radio"
                :value="val"
                :checked="is_selected(val)"
                :id="'radio-' + field.name + '-' + val"
                :class="builder_class_names('radio')">
            <label :for="'radio-' + field.name + '-' + val">{{ label }}</label>
        </div>
    </div>

    <p v-if="field.help" class="wpuf-mt-2 wpuf-mb-0 wpuf-text-sm wpuf-text-gray-500" v-html="field.help"></p>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-recaptcha">
<div class="wpuf-fields">
    <template v-if="!has_recaptcha_api_keys">
        <p v-html="no_api_keys_msg"></p>
    </template>

    <template v-else>
        <img
            v-if="'invisible_recaptcha' !== field.recaptcha_type"
            class="wpuf-recaptcha-placeholder"
            src="<?php echo esc_url ( WPUF_ASSET_URI . '/images/recaptcha-placeholder.png' ); ?>"
            alt="">
        <div v-else><p><?php esc_html_e( 'Invisible reCaptcha', 'wp-user-frontend' ); ?></p></div>
    </template>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-section_break">
<div class="wpuf-fields wpuf-min-w-full">
    <div
        v-if="!field.divider || field.divider === 'regular'"
        class="wpuf-section-wrap">
        <h2 class="wpuf-section-title">{{ field.label }}</h2>
        <div class="wpuf-section-details wpuf-text-sm wpuf-text-gray-500">{{ field.description }}</div>
        <div class="wpuf-border wpuf-border-gray-200 wpuf-h-0 wpuf-w-full"></div>
    </div>
    <div
        v-else-if="field.divider === 'dashed'"
        class="wpuf-section-wrap">
        <div class="wpuf-flex wpuf-items-center wpuf-justify-between">
            <div class="wpuf-border wpuf-border-gray-200 wpuf-h-0 wpuf-w-2/5"></div>
            <div class="wpuf-section-title wpuf-text-base text-gray-900 wpuf-px-3 wpuf-font-semibold">{{ field.label }}</div>
            <div class="wpuf-border wpuf-border-gray-200 wpuf-h-0 wpuf-w-2/5"></div>
        </div>
        <div class="wpuf-section-details wpuf-text-gray-400 wpuf-text-center wpuf-mt-2">{{ field.description }}</div>
    </div>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-taxonomy">
<div class="wpuf-fields">
    <select
        v-if="'select' === field.type"
        :class="builder_class_names('select')"
        class="!wpuf-text-base"
        v-html ="get_term_dropdown_options()">
    </select>

    <div v-if="'ajax' === field.type" class="category-wrap">
        <div>
            <select
                :class="builder_class_names('select')"
                class="!wpuf-text-base"
            >
                <option class="wpuf-text-base !wpuf-leading-none"><?php esc_html_e( '— Select —', 'wp-user-frontend' ); ?></option>
                <option v-for="term in sorted_terms" :value="term.id">{{ term.name }}</option>
            </select>
        </div>
    </div>

    <div v-if="'multiselect' === field.type" class="category-wrap">
        <select
            :class="builder_class_names('select')"
            class="!wpuf-text-base"
            v-html="get_term_dropdown_options()"
            multiple
        >
        </select>
    </div>

    <div v-if="'checkbox' === field.type" class="category-wrap">
        <div v-if="'yes' === field.show_inline" class="category-wrap">
            <div v-html="get_term_checklist_inline()"></div>
        </div>
        <div v-else class="category-wrap">
            <div v-html="get_term_checklist()"></div>
        </div>
    </div>

    <input
        v-if="'text' === field.type"
        type="text"
        :class="builder_class_names('text')"
        :placeholder="field.placeholder"
        :size="field.size"
        value=""
        autocomplete="off"
    >
    <p v-if="field.help" class="wpuf-mt-2 wpuf-mb-0 wpuf-text-sm wpuf-text-gray-500" v-html="field.help"></p>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-text_field">
<div class="wpuf-fields">
    <input
        type="text"
        :placeholder="field.placeholder"
        :value="field.default"
        :size="field.size"
        :class="builder_class_names('textfield')"
    >
    <p v-if="field.help" class="wpuf-mt-2 wpuf-mb-0 wpuf-text-sm wpuf-text-gray-500" v-html="field.help"></p>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-textarea_field">
<div class="wpuf-fields">
    <textarea
        v-if="'no' === field.rich"
        :placeholder="field.placeholder"
        :default="field.default"
        :rows="field.rows"
        :cols="field.cols"
        :class="builder_class_names('textareafield')">{{ field.default }}</textarea>


    <text-editor
        v-if="'no' !== field.rich"
        :default_text="field.default"
        :rich="field.rich"></text-editor>

    <p v-if="field.help" class="wpuf-mt-2 wpuf-mb-0 wpuf-text-sm wpuf-text-gray-500" v-html="field.help"></p>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-website_url">
<div class="wpuf-fields">
    <input
        type="url"
        :class="builder_class_names('url')"
        :placeholder="field.placeholder"
        :value="field.default"
        :size="field.size"
    >
    <p v-if="field.help" class="wpuf-mt-2 wpuf-mb-0 wpuf-text-sm wpuf-text-gray-500" v-html="field.help"></p>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-help-text">
<span
    class="field-helper-text wpuf-ml-2"
    :data-placement="placement"
    data-toggle="tooltip"
    data-container="body">
    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M9.375 9.375L9.40957 9.35771C9.88717 9.11891 10.4249 9.55029 10.2954 10.0683L9.70458 12.4317C9.57507 12.9497 10.1128 13.3811 10.5904 13.1423L10.625 13.125M17.5 10C17.5 14.1421 14.1421 17.5 10 17.5C5.85786 17.5 2.5 14.1421 2.5 10C2.5 5.85786 5.85786 2.5 10 2.5C14.1421 2.5 17.5 5.85786 17.5 10ZM10 6.875H10.0063V6.88125H10V6.875Z" stroke="#9CA3AF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
</span>
</script>

<script type="text/x-template" id="tmpl-wpuf-text-editor">
<?php
wp_enqueue_style( 'editor-css', site_url() . '/wp-includes/css/editor.css', array(), null, 'all' );
wp_enqueue_style( 'skin-css', site_url() . '/wp-includes/js/tinymce/skins/lightgray/skin.min.css', array(), null, 'all' );
?>
<div class="wpuf-text-editor">

    <div class="wp-core-ui wp-editor-wrap tmce-active">

        <div class="wp-editor-container">
            <div class="mce-tinymce mce-container mce-panel" style="visibility: hidden; border-width: 1px;">
                <div class="mce-container-body mce-stack-layout">
                    <div class="mce-toolbar-grp mce-container mce-panel mce-stack-layout-item">
                        <div class="mce-container-body mce-stack-layout">
                            <div class="mce-container mce-toolbar mce-stack-layout-item">
                                <div class="mce-container-body mce-flow-layout">
                                    <div class="mce-container mce-flow-layout-item mce-btn-group">
                                        <div>
                                            <div v-if="is_full" class="mce-widget mce-btn mce-menubtn mce-fixed-width mce-listbox mce-btn-has-text"><button type="button"><span class="mce-txt">Paragraph</span> <i class="mce-caret"></i></button></div>
                                            <div class="mce-widget mce-btn"><button type="button"><i class="mce-ico mce-i-bold"></i></button></div>
                                            <div class="mce-widget mce-btn"><button type="button"><i class="mce-ico mce-i-italic"></i></button></div>
                                            <div class="mce-widget mce-btn"><button type="button"><i class="mce-ico mce-i-bullist"></i></button></div>
                                            <div class="mce-widget mce-btn"><button type="button"><i class="mce-ico mce-i-numlist"></i></button></div>
                                            <div class="mce-widget mce-btn"><button type="button"><i class="mce-ico mce-i-blockquote"></i></button></div>
                                            <div class="mce-widget mce-btn"><button type="button"><i class="mce-ico mce-i-alignleft"></i></button></div>
                                            <div class="mce-widget mce-btn"><button type="button"><i class="mce-ico mce-i-aligncenter"></i></button></div>
                                            <div class="mce-widget mce-btn"><button type="button"><i class="mce-ico mce-i-alignright"></i></button></div>
                                            <div class="mce-widget mce-btn"><button type="button"><i class="mce-ico mce-i-link"></i></button></div>
                                            <div class="mce-widget mce-btn"><button type="button"><i class="mce-ico mce-i-unlink"></i></button></div>
                                            <div v-if="is_full" class="mce-widget mce-btn"><button type="button"><i class="mce-ico mce-i-wp_more"></i></button></div>
                                            <div class="mce-widget mce-btn"><button type="button"><i class="mce-ico mce-i-fullscreen"></i></button></div>
                                            <div v-if="is_full" class="mce-widget mce-btn"><button type="button"><i class="mce-ico mce-i-wp_adv"></i></button></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mce-container mce-toolbar mce-stack-layout-item">
                                <div class="mce-container-body mce-flow-layout">
                                    <div class="mce-container mce-flow-layout-item mce-btn-group">
                                        <div>
                                            <div v-if="is_full" class="mce-widget mce-btn"><button type="button"><i class="mce-ico mce-i-strikethrough"></i></button></div>
                                            <div v-if="is_full" class="mce-widget mce-btn"><button type="button"><i class="mce-ico mce-i-hr"></i></button></div>
                                            <div v-if="is_full" class="mce-widget mce-btn mce-colorbutton"><button type="button"><i class="mce-ico mce-i-forecolor"></i><span class="mce-preview"></span></button><button type="button" class="mce-open"> <i class="mce-caret"></i></button></div>
                                            <div v-if="is_full" class="mce-widget mce-btn"><button type="button"><i class="mce-ico mce-i-pastetext"></i></button></div>
                                            <div v-if="is_full" class="mce-widget mce-btn"><button type="button"><i class="mce-ico mce-i-removeformat"></i></button></div>
                                            <div v-if="is_full" class="mce-widget mce-btn"><button type="button"><i class="mce-ico mce-i-charmap"></i></button></div>
                                            <div v-if="is_full" class="mce-widget mce-btn"><button type="button"><i class="mce-ico mce-i-outdent"></i></button></div>
                                            <div v-if="is_full" class="mce-widget mce-btn"><button type="button"><i class="mce-ico mce-i-indent"></i></button></div>
                                            <div class="mce-widget mce-btn mce-disabled"><button type="button"><i class="mce-ico mce-i-undo"></i></button></div>
                                            <div class="mce-widget mce-btn mce-disabled"><button type="button"><i class="mce-ico mce-i-redo"></i></button></div>
                                            <div v-if="is_full" class="mce-widget mce-btn"><button type="button"><i class="mce-ico mce-i-wp_help"></i></button></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mce-edit-area mce-container mce-panel mce-stack-layout-item" style="border-width: 1px 0px 0px;">
                        <div style="width: 100%; height: 150px; display: block;">{{default_text}}</div><!-- iframe replacement div -->
                    </div>
                    <div class="mce-statusbar mce-container mce-panel mce-stack-layout-item" style="border-width: 1px 0px 0px;">
                        <div class="mce-container-body mce-flow-layout">
                            <div class="mce-path mce-flow-layout-item">
                                <div class="mce-path-item" data-index="0" aria-level="0">p</div>
                            </div>
                            <div class="mce-flow-layout-item mce-resizehandle"><i class="mce-ico mce-i-resize"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</script>
