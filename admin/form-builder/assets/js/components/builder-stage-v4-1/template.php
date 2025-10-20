<div id="form-preview-stage" class="wpuf-h-[70vh]">
    <div v-if="!form_fields.length" class="wpuf-flex wpuf-flex-col wpuf-items-center wpuf-justify-center wpuf-h-[80vh]">
        <img src="<?php echo WPUF_ASSET_URI . '/images/form-blank-state.svg'; ?>" alt="">
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
                        'field-items', 'wpuf-el', field.name, field.css, 'form-field-' + field.template,
                        field.width ? 'field-size-' + field.width : '',
                        ('custom_hidden_field' === field.template) ? 'hidden-field' : ''
                    ]"
            class="wpuf-group wpuf-rounded-lg hover:!wpuf-bg-green-50 wpuf-transition wpuf-duration-150 wpuf-ease-out !wpuf-m-0 !wpuf-p-0">
            <div
                v-if="field.input_type !== 'column_field' && field.input_type !== 'repeat'"
                :class="parseInt(editing_form_id) === parseInt(field.id) ? 'wpuf-bg-green-50 wpuf-border-green-400' : 'wpuf-border-transparent'"
                class="wpuf-flex wpuf-justify-between wpuf-p-6 wpuf-rounded-t-md wpuf-border-t wpuf-border-r wpuf-border-l wpuf-border-dashed group-hover:wpuf-border-green-400 group-hover:wpuf-cursor-pointer !wpuf-pb-3">
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
                                    }}</strong> <?php _e( 'is available in Pro Version', 'wp-user-frontend' ); ?></a>
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
                class="field-buttons group-hover:wpuf-opacity-100 wpuf-rounded-b-lg !wpuf-bg-green-600 wpuf-items-center wpuf-transition wpuf-duration-150 wpuf-ease-out wpuf-flex wpuf-justify-around">
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
                        :class="action_button_classes" class="hover:wpuf-bg-green-700">
                            <a
                                :href="pro_link"
                                target="_blank"
                                class="wpuf-rounded-r-md hover:wpuf-bg-slate-500 hover:wpuf-cursor-pointer wpuf-transition wpuf-duration-150 wpuf-ease-out hover:wpuf-transition-all">
                                <img src="<?php esc_attr_e( WPUF_ASSET_URI . '/images/pro-badge.svg' ); ?>" alt="">
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
                    :class="parseInt(editing_form_id) === parseInt(field.id) ? 'wpuf-bg-green-50 wpuf-border-green-400' : 'wpuf-border-transparent'"
                    class="wpuf-flex wpuf-rounded-t-lg wpuf-border-t wpuf-border-r wpuf-border-l wpuf-border-dashed group-hover/hidden-fields:wpuf-border-green-400 group-hover/hidden-fields:wpuf-bg-green-50">
                    <div class="wpuf-bg-primary wpuf-m-4 wpuf-py-2 wpuf-px-4 wpuf-w-full wpuf-rounded-lg">
                        <strong><?php esc_html_e( 'key', 'wp-user-frontend' ); ?></strong>: {{ field.name }} |
                        <strong><?php esc_html_e( 'value', 'wp-user-frontend' ); ?></strong>: {{ field.meta_value }}
                    </div>
                </div>
                <div
                    :class="parseInt(editing_form_id) === parseInt(field.id) ? 'wpuf-opacity-100' : 'wpuf-opacity-0'"
                    class="field-buttons wpuf-opacity-0 group-hover/hidden-fields:wpuf-opacity-100 wpuf-bg-green-600 wpuf-rounded-b-lg wpuf-transition wpuf-duration-150 wpuf-ease-out wpuf-flex wpuf-items-center wpuf-justify-around">
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
