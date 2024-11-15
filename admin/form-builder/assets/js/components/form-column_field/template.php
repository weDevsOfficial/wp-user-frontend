<div :class="['wpuf-flex wpuf-flex-col md:wpuf-flex-row wpuf-gap-4 wpuf-p-4 wpuf-w-full', 'has-columns-'+field.columns]">
    <div
        v-for="column in columnClasses"
        class="wpuf-flex-1 wpuf-min-w-0 wpuf-min-h-full">
        <div class="wpuf-column-inner-fields wpuf-border wpuf-border-dashed wpuf-border-green-400 wpuf-bg-green-50 wpuf-shadow-sm wpuf-rounded-md wpuf-p-3">
            <ul class="wpuf-column-fields-sortable-list ui-sortable wpuf-space-y-3">
                <li
                    v-for="(field, index) in column_fields[column]"
                    :key="field.id"
                    :column-field-index="index"
                    :in-column="column"
                    data-source="column-field-stage"
                    :class="[
                        'wpuf-group',
                        'wpuf-rounded-lg',
                        'hover:wpuf-bg-green-50',
                        'wpuf-transition',
                        'wpuf-duration-150',
                        'wpuf-ease-out',
                        'column-field-items',
                        'wpuf-el',
                        field.name,
                        field.css,
                        'form-field-' + field.template,
                        field.width ? 'field-size-' + field.width : '',
                        ('custom_hidden_field' === field.template) ? 'hidden-field' : '',
                        parseInt(editing_form_id) === parseInt(field.id) ? 'current-editing' : ''
                      ]">
                    <div class="wpuf-flex wpuf-flex-col md:wpuf-flex-row wpuf-gap-2 wpuf-p-2">
                        <div
                            v-if="!(is_full_width(field.template) || is_pro_feature(field.template))"
                            class="wpuf-w-full md:wpuf-w-1/4 wpuf-shrink-0">
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
                             (is_full_width(field.template) || is_pro_feature(field.template))
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
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>
