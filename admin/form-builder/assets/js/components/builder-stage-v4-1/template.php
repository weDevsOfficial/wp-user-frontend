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
                v-if="field.input_type !== 'column_field'"
                :class="parseInt(editing_form_id) === parseInt(field.id) ? 'wpuf-bg-green-50 wpuf-border-green-400' : 'wpuf-border-transparent'"
                class="wpuf-flex wpuf-justify-between wpuf-p-6 wpuf-rounded-t-md wpuf-border-t wpuf-border-r wpuf-border-l wpuf-border-dashed group-hover:wpuf-border-green-400 group-hover:wpuf-cursor-pointer !wpuf-pb-3">
                <div v-if="!(is_full_width(field.template) || is_pro_preview(field.template))" class="wpuf-w-1/4 wpuf-flex wpuf-items-center">
                    <label
                        v-if="!is_invisible(field)"
                        :for="'wpuf-' + field.name ? field.name : 'cls'"
                        class="wpuf-block wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-900">
                        {{ field.label }} <span v-if="field.required && 'yes' === field.required"
                                                class="required">*</span>
                    </label>
                    <span v-if="field.template === 'twitter_url' && field.show_icon === 'yes'" class="wpuf-social-label-icon wpuf-inline-flex wpuf-items-center wpuf-ml-2">
                        <svg class="wpuf-twitter-svg" width="20" height="25" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="X (Twitter)" role="img">
                            <path d="M6 16L10.1936 11.8065M10.1936 11.8065L6 6H8.77778L11.8065 10.1935M10.1936 11.8065L13.2222 16H16L11.8065 10.1935M16 6L11.8065 10.1935M1.5 11C1.5 6.52166 1.5 4.28249 2.89124 2.89124C4.28249 1.5 6.52166 1.5 11 1.5C15.4784 1.5 17.7175 1.5 19.1088 2.89124C20.5 4.28249 20.5 6.52166 20.5 11C20.5 15.4783 20.5 17.7175 19.1088 19.1088C17.7175 20.5 15.4784 20.5 11 20.5C6.52166 20.5 4.28249 20.5 2.89124 19.1088C1.5 17.7175 1.5 15.4783 1.5 11Z" stroke="#079669" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span v-if="field.template === 'facebook_url' && field.show_icon === 'yes'" class="wpuf-social-label-icon wpuf-inline-flex wpuf-items-center wpuf-ml-2">
                        <svg class="wpuf-facebook-svg" width="20" height="25" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="Facebook" role="img">
                            <path d="M14.1061 6.68815H11.652C10.7822 6.68815 10.0752 7.3899 10.0688 8.25975L9.99768 17.8552M8.40234 11.6676H12.4046M2.08398 9.9987C2.08398 6.26675 2.08398 4.40077 3.24335 3.2414C4.40273 2.08203 6.2687 2.08203 10.0007 2.08203C13.7326 2.08203 15.5986 2.08203 16.758 3.2414C17.9173 4.40077 17.9173 6.26675 17.9173 9.9987C17.9173 13.7306 17.9173 15.5966 16.758 16.756C15.5986 17.9154 13.7326 17.9154 10.0007 17.9154C6.2687 17.9154 4.40273 17.9154 3.24335 16.756C2.08398 15.5966 2.08398 13.7306 2.08398 9.9987Z" stroke="#079669" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span v-if="field.template === 'linkedin_url' && field.show_icon === 'yes'" class="wpuf-social-label-icon wpuf-inline-flex wpuf-items-center wpuf-ml-2">
                        <svg class="wpuf-linkedin-svg" width="20" height="25" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="LinkedIn" role="img">
                        <path d="M4.83398 7.33203V13.1654M8.16732 9.83203V13.1654M8.16732 9.83203C8.16732 8.45128 9.28657 7.33203 10.6673 7.33203C12.0481 7.33203 13.1673 8.45128 13.1673 9.83203V13.1654M8.16732 9.83203V7.33203M4.84066 4.83203H4.83317M1.08398 8.9987C1.08398 5.26675 1.08398 3.40077 2.24335 2.2414C3.40273 1.08203 5.2687 1.08203 9.00065 1.08203C12.7326 1.08203 14.5986 1.08203 15.758 2.2414C16.9173 3.40077 16.9173 5.26675 16.9173 8.9987C16.9173 12.7306 16.9173 14.5966 15.758 15.756C14.5986 16.9154 12.7326 16.9154 9.00065 16.9154C5.2687 16.9154 3.40273 16.9154 2.24335 15.756C1.08398 14.5966 1.08398 12.7306 1.08398 8.9987Z" stroke="#079669" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span v-if="field.template === 'instagram_url' && field.show_icon === 'yes'" class="wpuf-social-label-icon wpuf-inline-flex wpuf-items-center wpuf-ml-2">
                        <svg class="wpuf-instagram-svg" width="20" height="25" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="Instagram" role="img">
                            <path d="M2.24335 2.2414L1.71302 1.71107L1.71302 1.71107L2.24335 2.2414ZM15.758 2.2414L16.2883 1.71108L16.2883 1.71106L15.758 2.2414ZM15.758 15.756L16.2883 16.2864L16.2883 16.2863L15.758 15.756ZM2.24335 15.756L1.71301 16.2864L1.71303 16.2864L2.24335 15.756ZM13.5905 5.16536C14.0047 5.16536 14.3405 4.82958 14.3405 4.41536C14.3405 4.00115 14.0047 3.66536 13.5905 3.66536V5.16536ZM13.583 3.66536C13.1688 3.66536 12.833 4.00115 12.833 4.41536C12.833 4.82958 13.1688 5.16536 13.583 5.16536V3.66536ZM1.08398 8.9987H1.83398C1.83398 7.11152 1.83558 5.77159 1.97222 4.75527C2.10596 3.76052 2.35657 3.18884 2.77368 2.77173L2.24335 2.2414L1.71302 1.71107C0.97076 2.45333 0.641695 3.39432 0.485593 4.5554C0.332392 5.6949 0.333984 7.15392 0.333984 8.9987H1.08398ZM2.24335 2.2414L2.77368 2.77173C3.19079 2.35462 3.76248 2.104 4.75722 1.97026C5.77354 1.83362 7.11347 1.83203 9.00065 1.83203V1.08203V0.332031C7.15588 0.332031 5.69685 0.330438 4.55735 0.48364C3.39627 0.639742 2.45529 0.968807 1.71302 1.71107L2.24335 2.2414ZM9.00065 1.08203V1.83203C10.8878 1.83203 12.2277 1.83362 13.2441 1.97026C14.2388 2.104 14.8105 2.35462 15.2277 2.77174L15.758 2.2414L16.2883 1.71106C15.546 0.968806 14.605 0.639742 13.4439 0.48364C12.3044 0.330438 10.8454 0.332031 9.00065 0.332031V1.08203ZM15.758 2.2414L15.2276 2.77172C15.6447 3.18883 15.8954 3.76052 16.0291 4.75526C16.1657 5.77158 16.1673 7.11152 16.1673 8.9987H16.9173H17.6673C17.6673 7.15392 17.6689 5.6949 17.5157 4.5554C17.3596 3.39433 17.0306 2.45334 16.2883 1.71108L15.758 2.2414ZM16.9173 8.9987H16.1673C16.1673 10.8859 16.1657 12.2258 16.0291 13.2421C15.8954 14.2369 15.6447 14.8086 15.2276 15.2257L15.758 15.756L16.2883 16.2863C17.0306 15.5441 17.3596 14.6031 17.5157 13.442C17.6689 12.3025 17.6673 10.8435 17.6673 8.9987H16.9173ZM15.758 15.756L15.2277 15.2257C14.8105 15.6428 14.2388 15.8934 13.2441 16.0271C12.2277 16.1638 10.8878 16.1654 9.00065 16.1654V16.9154V17.6654C10.8454 17.6654 12.3044 17.667 13.4439 17.5138C14.605 17.3577 15.546 17.0286 16.2883 16.2864L15.758 15.756ZM9.00065 16.9154V16.1654C7.11347 16.1654 5.77354 16.1638 4.75722 16.0271C3.76247 15.8934 3.19078 15.6428 2.77367 15.2257L2.24335 15.756L1.71303 16.2864C2.4553 17.0286 3.39628 17.3577 4.55735 17.5138C5.69685 17.667 7.15588 17.6654 9.00065 17.6654V16.9154ZM2.24335 15.756L2.77369 15.2257C2.35658 14.8086 2.10596 14.2369 1.97222 13.2421C1.83558 12.2258 1.83398 10.8859 1.83398 8.9987H1.08398H0.333984C0.333984 10.8435 0.332392 12.3025 0.485593 13.442C0.641695 14.6031 0.970759 15.5441 1.71301 16.2864L2.24335 15.756ZM12.7507 8.9987H12.0007C12.0007 10.6556 10.6575 11.9987 9.00065 11.9987V12.7487V13.4987C11.4859 13.4987 13.5007 11.484 13.5007 8.9987H12.7507ZM9.00065 12.7487V11.9987C7.3438 11.9987 6.00065 10.6556 6.00065 8.9987H5.25065H4.50065C4.50065 11.484 6.51537 13.4987 9.00065 13.4987V12.7487ZM5.25065 8.9987H6.00065C6.00065 7.34184 7.3438 5.9987 9.00065 5.9987V5.2487V4.4987C6.51537 4.4987 4.50065 6.51342 4.50065 8.9987H5.25065ZM9.00065 5.2487V5.9987C10.6575 5.9987 12.0007 7.34184 12.0007 8.9987H12.7507H13.5007C13.5007 6.51342 11.4859 4.4987 9.00065 4.4987V5.2487ZM13.5905 4.41536V3.66536H13.583V4.41536V5.16536H13.5905V4.41536Z" fill="#079669"/>    
                        </svg>
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
                v-if="is_template_available(field) && field.input_type === 'column_field'"
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
