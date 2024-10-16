<div id="form-preview-stage">
    <h4 v-if="!form_fields.length" class="text-center">
        <?php _e( 'Add fields by dragging the fields from the right sidebar to this area.', 'wp-user-frontend' ); ?>
    </h4>

    <ul :class="['wpuf-form', 'sortable-list', 'form-label-' + label_type]">
        <li
            v-for="(field, index) in form_fields"
            :key="field.id"
            :data-index="index"
            data-source="stage"
            :class="[
                    'field-items', 'wpuf-el', field.name, field.css, 'form-field-' + field.template,
                    field.width ? 'field-size-' + field.width : '',
                    ('custom_hidden_field' === field.template) ? 'hidden-field' : '',
                    parseInt(editing_form_id) === parseInt(field.id) ? 'current-editing' : '',
                    index === 0 ? '' : 'wpuf-mt-4'
                ]"
            class="wpuf-group wpuf-relative wpuf-flex wpuf-justify-between wpuf-rounded-lg wpuf-bg-white wpuf-p-4  wpuf-border wpuf-border-transparent wpuf-transition wpuf-duration-150 wpuf-ease-out">
            <div v-if="!(is_full_width(field.template) || is_pro_feature(field.template))" class="wpuf-w-1/4">
                <label
                    v-if="!is_invisible(field)"
                    :for="'wpuf-' + field.name ? field.name : 'cls'"
                    class="wpuf-block wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-900">
                    {{ field.label }} <span v-if="field.required && 'yes' === field.required" class="required">*</span>
                </label>
            </div>
            <div :class="(is_full_width(field.template) || is_pro_feature(field.template)) ? 'wpuf-w-full' : 'wpuf-w-3/4'">
                <component v-if="is_template_available(field)" :is="'form-' + field.template" :field="field"></component>
                <div v-if="is_pro_feature(field.template)" class="stage-pro-alert wpuf-text-center">
                    <label class="wpuf-pro-text-alert">
                        <a :href="pro_link" target="_blank" class="wpuf-text-gray-700 wpuf-text-base"><strong>{{ get_field_name(field.template) }}</strong> <?php _e( 'is available in Pro Version', 'wp-user-frontend' ); ?></a>
                    </label>
                </div>
            </div>
            <div class="control-buttons wpuf-opacity-0 group-hover:wpuf-opacity-100 control-buttons wpuf-rounded-lg wpuf-absolute wpuf-w-full wpuf-h-full wpuf-bg-gray-50/50 wpuf-top-0 wpuf-left-0 wpuf-flex wpuf-justify-around wpuf-items-center wpuf-shadow-sm wpuf-bg-gray-100/50 wpuf-ease-in wpuf-border wpuf-border-dashed wpuf-border-gray-300">
                <p>
                    <template v-if="!is_failed_to_validate(field.template)">
                        <i
                            :class="action_button_classes"
                            class="fa fa-arrows move wpuf--ml-1 wpuf-rounded-l-md hover:!wpuf-cursor-move"></i>
                        <i
                            :class="action_button_classes"
                            class="fa fa-pencil wpuf--ml-1" @click="open_field_settings(field.id)"></i>
                        <i
                            :class="action_button_classes"
                            class="fa fa-clone wpuf--ml-1" @click="clone_field(field.id, index)"></i>
                    </template>
                    <template v-else>
                        <i
                            :class="action_button_classes"
                            class="fa fa-arrows control-button-disabled wpuf--ml-1 wpuf-rounded-l-md"></i>
                        <i
                            :class="action_button_classes"
                           class="fa fa-pencil control-button-disabled wpuf--ml-1"></i>
                        <i
                            :class="action_button_classes"
                           class="fa fa-clone control-button-disabled wpuf--ml-1"></i>
                    </template>
                    <i
                        :class="!is_pro_feature(field.template) ? [action_button_classes, 'wpuf-rounded-r-md'] : action_button_classes"
                        class="fa fa-trash-o wpuf--ml-1" @click="delete_field(index)"></i>
                </p>
            </div>
        </li>
    </ul>

    <li class="wpuf-submit wpuf-list-none">
        <div class="wpuf-label">&nbsp;</div>

        <?php do_action( 'wpuf_form_builder_template_builder_stage_submit_area' ); ?>
    </li>

    <div v-if="hidden_fields.length" class="wpuf-border-t wpuf-border-dashed wpuf-border-gray-300">
        <h4><?php esc_html_e( 'Hidden Fields', 'wp-user-frontend' ); ?></h4>

        <ul class="wpuf-form">
            <li
                v-for="(field, index) in hidden_fields"
                :class="['field-items wpuf-bg-gray-50 hover:wpuf-bg-gray-100', parseInt(editing_form_id) === parseInt(field.id) ? 'current-editing' : '']"
            >
                <strong><?php esc_html_e( 'key', 'wp-user-frontend' ); ?></strong>: {{ field.name }} | <strong><?php esc_html_e( 'value', 'wp-user-frontend' ); ?></strong>: {{ field.meta_value }}
            </li>
        </ul>
    </div>

    <?php do_action( 'wpuf_form_builder_template_builder_stage_bottom_area' ); ?>
</div><!-- #form-preview-stage -->
