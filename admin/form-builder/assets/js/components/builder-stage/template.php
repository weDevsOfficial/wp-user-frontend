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
                <label v-if="!is_invisible(field)" :for="'wpuf-' + field.name ? field.name : 'cls'">
                    {{ field.label }} <span v-if="field.required && 'yes' === field.required" class="required">*</span>
                </label>
                <span v-if="field.template === 'twitter_url' && field.show_icon === 'yes'" class="wpuf-social-label-icon wpuf-inline-flex wpuf-items-center wpuf-ml-2">
                        <svg class="wpuf-twitter-svg" width="16" height="16" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="X (Twitter)" role="img">
                            <path d="M6 16L10.1936 11.8065M10.1936 11.8065L6 6H8.77778L11.8065 10.1935M10.1936 11.8065L13.2222 16H16L11.8065 10.1935M16 6L11.8065 10.1935M1.5 11C1.5 6.52166 1.5 4.28249 2.89124 2.89124C4.28249 1.5 6.52166 1.5 11 1.5C15.4784 1.5 17.7175 1.5 19.1088 2.89124C20.5 4.28249 20.5 6.52166 20.5 11C20.5 15.4783 20.5 17.7175 19.1088 19.1088C17.7175 20.5 15.4784 20.5 11 20.5C6.52166 20.5 4.28249 20.5 2.89124 19.1088C1.5 17.7175 1.5 15.4783 1.5 11Z" stroke="#079669" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                </span>
                <span v-if="field.template === 'facebook_url' && field.show_icon === 'yes'" class="wpuf-social-label-icon wpuf-inline-flex wpuf-items-center wpuf-ml-2">
                    <svg class="wpuf-facebook-svg" width="16" height="16" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="Facebook" role="img">
                        <path d="M14.1061 6.68815H11.652C10.7822 6.68815 10.0752 7.3899 10.0688 8.25975L9.99768 17.8552M8.40234 11.6676H12.4046M2.08398 9.9987C2.08398 6.26675 2.08398 4.40077 3.24335 3.2414C4.40273 2.08203 6.2687 2.08203 10.0007 2.08203C13.7326 2.08203 15.5986 2.08203 16.758 3.2414C17.9173 4.40077 17.9173 6.26675 17.9173 9.9987C17.9173 13.7306 17.9173 15.5966 16.758 16.756C15.5986 17.9154 13.7326 17.9154 10.0007 17.9154C6.2687 17.9154 4.40273 17.9154 3.24335 16.756C2.08398 15.5966 2.08398 13.7306 2.08398 9.9987Z" stroke="#1877F3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
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
