<script type="text/x-template" id="tmpl-wpuf-builder-stage">
<div id="form-preview-stage">
    <h4 v-if="!form_fields.length" class="text-center">
        <?php _e( 'Add fields by dragging the fields from the right sidebar to this area.', 'wp-user-frontend' ); ?>
    </h4>
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
                        ('custom_hidden_field' === field.template) ? 'hidden-field' : '',
                        parseInt(editing_form_id) === parseInt(field.id) ? 'current-editing' : ''
                    ]"
            class="wpuf-group wpuf-rounded-lg hover:wpuf-bg-green-50 wpuf-transition wpuf-duration-150 wpuf-ease-out !wpuf-m-0 !wpuf-p-0">
            <div
                v-if="field.input_type !== 'column_field'"
                class="wpuf-flex wpuf-justify-between wpuf-p-4 wpuf-rounded-t-md wpuf-border-transparent wpuf-border-t wpuf-border-r wpuf-border-l wpuf-border-dashed group-hover:wpuf-border-green-400 group-hover:wpuf-cursor-pointer">
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
                    <component v-if="is_template_available(field)" :is="'form-' + field.template"
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
                class="control-buttons wpuf-opacity-0 group-hover:wpuf-opacity-100 wpuf-rounded-b-lg !wpuf-bg-green-600 wpuf-flex wpuf-items-center wpuf-transition wpuf-duration-150 wpuf-ease-out wpuf-flex wpuf-justify-around">
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
                :class="['field-items', parseInt(editing_form_id) === parseInt(field.id) ? 'current-editing' : '']"
                class="wpuf-group hover:wpuf-bg-green-50 !wpuf-m-0 !wpuf-p-0"
            >
                <div
                    class="wpuf-flex wpuf-bg-gray-50 wpuf-p-4 wpuf-rounded-t-md wpuf-border-transparent wpuf-border-t wpuf-border-r wpuf-border-l group-hover:wpuf-border-dashed group-hover:wpuf-border-green-400 group-hover:wpuf-cursor-pointer">
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
</script>

<script type="text/x-template" id="tmpl-wpuf-field-checkbox">
<div v-if="met_dependencies" class="panel-field-opt panel-field-opt-checkbox">
    <label v-if="option_field.title" :class="option_field.title_class">
        {{ option_field.title }} <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>
    </label>
    <ul :class="[option_field.inline ? 'list-inline' : '']">
        <li v-for="(option, key) in option_field.options">
            <label>
                <input type="checkbox" :value="key" v-model="value"> {{ option }}
            </label>
        </li>
    </ul>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-field-html_help_text">
<div class="panel-field-opt panel-field-html-help-text" v-html="option_field.text"></div>
</script>

<script type="text/x-template" id="tmpl-wpuf-field-multiselect">
<div class="panel-field-opt panel-field-opt-select" v-show="met_dependencies">
    <label v-if="option_field.title">
        {{ option_field.title }} <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>
    </label>

    <select
        :class="['term-list-selector']"
        v-model="value"
        multiple
    >
        <option v-for="(option, key) in option_field.options" :value="key">{{ option }}</option>
    </select>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-field-option-data">
<div class="panel-field-opt panel-field-opt-text">
    <div>
        {{ option_field.title }} <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>
        <ul class="pull-right list-inline field-option-actions">
            <li>
                <label>
                    <input
                        type="checkbox"
                        v-model="show_value"
                    /><?php esc_attr_e( 'Show values', 'wp-user-frontend' ); ?>
                </label>
            </li>
            <li>
                <label>
                    <input
                        type="checkbox"
                        v-model="sync_value"
                    /><?php esc_attr_e( 'Sync values', 'wp-user-frontend' ); ?>
                </label>
                <help-text placement="left" text="<?php esc_attr_e( 'When enabled, option values will update according to their labels.', 'wp-user-frontend' ); ?>" />
            </li>
        </ul>
    </div>

    <ul :class="['option-field-option-chooser', show_value ? 'show-value' : '']">
        <li class="clearfix margin-0 header">
            <div class="selector">&nbsp;</div>

            <div class="sort-handler">&nbsp;</div>

            <div class="label">
                <?php esc_attr_e( 'Label', 'wp-user-frontend' ); ?>
                <help-text placement="left" text="<?php esc_attr_e( 'Do not use & or other special character for option label', 'wp-user-frontend' ); ?>" />
            </div>

            <div v-if="show_value" class="value">
                <?php esc_attr_e( 'Value', 'wp-user-frontend' ); ?>
            </div>

            <div class="action-buttons">&nbsp;</div>
        </li>
    </ul>

    <ul :class="['option-field-option-chooser margin-0', show_value ? 'show-value' : '']">
        <li v-for="(option, index) in options" :key="option.id" :data-index="index" class="clearfix option-field-option">
            <div class="selector">
                <input
                    v-if="option_field.is_multiple"
                    type="checkbox"
                    :value="option.value"
                    v-model="selected"
                >
                <input
                    v-else
                    type="radio"
                    :value="option.value"
                    v-model="selected"
                    class="option-chooser-radio"
                >
            </div>

            <div class="sort-handler">
                <i class="fa fa-bars"></i>
            </div>

            <div class="label">
                <input type="text" v-model="option.label" @input="set_option_label(index, option.label)">
            </div>

            <div v-if="show_value" class="value">
                <input type="text" v-model="option.value">
            </div>

            <div class="action-buttons clearfix">
                <i class="fa fa-minus-circle" @click="delete_option(index)"></i>
            </div>
        </li>
        <li>
            <div class="plus-buttons clearfix" @click="add_option">
                <i class="fa fa-plus-circle"></i>
            </div>
        </li>
    </ul>

    <a v-if="!option_field.is_multiple && selected" href="#clear" @click.prevent="clear_selection"><?php esc_attr_e( 'Clear Selection', 'wp-user-frontend' ); ?></a>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-field-option-pro-feature-alert">
<div class="panel-field-opt panel-field-opt-pro-feature">
    <label>{{ option_field.title }}</label><br>
    <label class="wpuf-pro-text-alert">
        <a :href="pro_link" target="_blank"><?php _e( 'Available in Pro Version', 'wp-user-frontend' ); ?></a>
    </label>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-field-options">
<div class="wpuf-form-builder-field-options">
    <div v-if="!parseInt(editing_field_id)" class="options-fileds-section text-center">
        <p>
            <span class="loader"></span>
        </p>
    </div>

    <div v-else>
        <div class="option-fields-section">
            <h3 class="section-title clearfix" @click="show_basic_settings = !show_basic_settings">
                {{ form_field_type_title }} <i :class="[show_basic_settings ? 'fa fa-angle-down' : 'fa fa-angle-right']"></i>
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
            <h3 class="section-title" @click="show_advanced_settings = !show_advanced_settings">
                {{ i18n.advanced_options }}  <i :class="[show_advanced_settings ? 'fa fa-angle-down' : 'fa fa-angle-right']"></i>
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
    <label v-if="option_field.title">
        {{ option_field.title }} <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>
    </label>

    <ul :class="[option_field.inline ? 'list-inline' : '']">
        <li v-for="(option, key) in option_field.options">
            <label>
                <input type="radio" :value="key" v-model="value"> {{ option }}
            </label>
        </li>
    </ul>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-field-range">
<div v-if="met_dependencies" class="panel-field-opt panel-field-opt-text">
    <label>
        {{ option_field.title }} <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>
        {{ option_field.min_column }}
        <input
            type="range"
            v-model="value"
            v-bind:min="minColumn"
            v-bind:max="maxColumn"
        >
    </label>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-field-select">
<div class="panel-field-opt panel-field-opt-select">
    <label v-if="option_field.title">
        {{ option_field.title }} <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>
    </label>

    <select class="opt-select-element" v-model="value">
        <option value=""><?php _e( 'Select an option', 'wp-user-frontend' ); ?></option>
        <option v-for="(option, key) in option_field.options" :value="key">{{ option }}</option>
    </select>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-field-text">
<div v-if="met_dependencies" class="panel-field-opt panel-field-opt-text">
    <label>
        {{ option_field.title }} <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>

        <input
            v-if="option_field.variation && 'number' === option_field.variation"
            type="number"
            v-model="value"
            @focusout="on_focusout"
            @keyup="on_keyup"
        >

        <input
            v-if="!option_field.variation"
            type="text"
            v-model="value"
            @focusout="on_focusout"
            @keyup="on_keyup"
        >
    </label>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-field-text-meta">
<div class="panel-field-opt panel-field-opt-text panel-field-opt-text-meta">
    <label>
        {{ option_field.title }} <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>
        <input
            type="text"
            v-model="value"
        >
    </label>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-field-textarea">
<div class="panel-field-opt panel-field-opt-textarea">
    <label>
        {{ option_field.title }} <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>

        <textarea :rows="option_field.rows || 5" v-model="value"></textarea>
    </label>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-field-visibility">
<div class="panel-field-opt panel-field-opt-radio">
    <label v-if="option_field.title">
        {{ option_field.title }} <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>
    </label>

    <ul :class="[option_field.inline ? 'list-inline' : '']">
        <li v-for="(option, key) in option_field.options">
            <label>
                <input type="radio" :value="key" v-model="selected"> {{ option }}
            </label>
        </li>
    </ul>

    <div v-if="'logged_in' === selected" class="condiotional-logic-container">

    	<?php use WeDevs\Wpuf\Admin\Subscription;

	    $roles = get_editable_roles(); ?>

    	<ul>
			<?php
                foreach ( $roles as $role => $value ) {
                    $role_name = $value['name'];

                    $output  = '<li>';
                    $output .= "<label><input type='checkbox' v-model='choices' value='{$role}'> {$role_name} </label>";
                    $output .= '</li>';

                    echo $output;
                }
            ?>
	    </ul>
    </div>

    <div v-if="'subscribed_users' === selected" class="condiotional-logic-container">

    	<ul>
    		<?php

                if ( class_exists( 'WPUF_Subscription' ) ) {
                    $subscriptions  = wpuf()->subscription->get_subscriptions();

                    if ( $subscriptions ) {
                        foreach ( $subscriptions as $pack ) {
                            $output  = '<li>';
                            $output .= "<label><input type='checkbox' v-model='choices' value='{$pack->ID}' > {$pack->post_title} </label>";
                            $output .= '</li>';

                            echo $output;
                        }
                    } else {
                        _e( 'No subscription plan found.', 'wp-user-frontend' );
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
                    :class="class_names('checkbox_btns')"
                    class="wpuf-h-4 wpuf-w-4 wpuf-rounded wpuf-border-gray-300 wpuf-text-indigo-600 focus:wpuf-ring-indigo-600 !wpuf-mt-0.5">
                <label class="wpuf-ml-3 wpuf-text-sm wpuf-font-medium wpuf-text-gray-900">{{ label }}</label>
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
                :class="class_names('checkbox_btns')"
                class="!wpuf-mt-[.5px] wpuf-rounded wpuf-border-gray-300 wpuf-text-indigo-600">
            <label class="wpuf-ml-1 wpuf-text-sm wpuf-font-medium wpuf-text-gray-900">{{ label }}</label>
        </div>
    </div>

    <p v-if="field.help" class="wpuf-mt-2 wpuf-text-sm wpuf-text-gray-500" v-html="field.help"></p>
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
<div :class="['wpuf-field-columns wpuf-flex wpuf-flex-col md:wpuf-flex-row wpuf-gap-4 wpuf-p-4 wpuf-w-full', 'has-columns-'+field.columns]">
    <div
        v-for="column in columnClasses"
        class="wpuf-flex-1 wpuf-min-w-0 wpuf-min-h-full">
        <div class="wpuf-column-inner-fields wpuf-border wpuf-border-dashed wpuf-border-green-400 wpuf-bg-green-50 wpuf-shadow-sm wpuf-rounded-md wpuf-p-1">
            <ul class="wpuf-column-fields-sortable-list">
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
                        parseInt(editing_form_id) === parseInt(field.id) ? 'current-editing' : ''
                      ]">
                    <div class="wpuf-flex wpuf-flex-col md:wpuf-flex-row wpuf-gap-2 wpuf-p-4 wpuf-border-transparent group-hover/column-inner:wpuf-border-green-400 wpuf-rounded-t-md wpuf-border-t wpuf-border-r wpuf-border-l wpuf-border-dashed wpuf-border-green-400">
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
                                <div v-if="is_pro_feature(field.template)" class="stage-pro-alert wpuf-text-center">
                                    <label class="wpuf-pro-text-alert">
                                        <a :href="pro_link" target="_blank"
                                           class="wpuf-text-gray-700 wpuf-text-base"><strong>{{ get_field_name( field.template )
                                                }}</strong> <?php _e( 'is available in Pro Version', 'wp-user-frontend' ); ?></a>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        class="wpuf-column-field-control-buttons wpuf-opacity-0 group-hover/column-inner:wpuf-opacity-100 wpuf-rounded-b-lg wpuf-bg-green-600 wpuf-items-center wpuf-transition wpuf-duration-150 wpuf-ease-out">
                        <div class="wpuf-items-center wpuf-text-green-200 wpuf-flex wpuf-justify-evenly wpuf-p-1">
                            <template v-if="!is_failed_to_validate(field.template)">
                            <span :class="action_button_classes">
                            <i
                                class="fa fa-arrows move wpuf-pr-2 wpuf-rounded-l-md hover:!wpuf-cursor-move wpuf-border-r wpuf-border-green-200"></i>
                            </span>
                                <span :class="action_button_classes"
                                    @click="open_column_field_settings(field, innerIndex, column)">
                            <i
                                class="fa fa-pencil"></i>
                                Edit
                            </span>
                                <span :class="action_button_classes"
                                    @click="clone_column_field(field, innerIndex, column)">
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
                            <span :class="action_button_classes" @click="delete_column_field(innerIndex, column)">
                            <i
                                class="fa fa-trash-o wpuf--ml-1"></i>
                                Remove
                        </span>
                            <span :class="action_button_classes"
                                v-if="is_pro_feature(field.template)"
                                class="hover:wpuf-bg-green-700">
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
    <p v-if="field.help" class="wpuf-mt-2 wpuf-text-sm wpuf-text-gray-500" v-html="field.help"></p>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-custom_html">
<div class="wpuf-fields" v-html="field.html"></div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-dropdown_field">
<div class="wpuf-fields">
    <select
        :class="class_names('select_lbl')"
        class="wpuf-block wpuf-w-full wpuf-min-w-full wpuf-rounded-md wpuf-py-1.5 wpuf-text-gray-900 wpuf-shadow-sm   placeholder:wpuf-text-gray-400 sm:wpuf-text-sm sm:wpuf-leading-6 wpuf-border !wpuf-border-gray-300">
        <option v-if="field.first" value="">{{ field.first }}</option>
        <option
            v-if="has_options"
            v-for="(label, val) in field.options"
            :value="label"
            :selected="is_selected(label)"
        >{{ label }}</option>
    </select>
    <p v-if="field.help" class="wpuf-mt-2 wpuf-text-sm wpuf-text-gray-500" v-html="field.help"></p>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-email_address">
<div class="wpuf-fields">
    <input
        type="email"
        :class="class_names('email') + builder_class_names('text')"
        :placeholder="field.placeholder"
        :value="field.default"
        :size="field.size"
    >
    <p v-if="field.help" class="wpuf-mt-2 wpuf-text-sm wpuf-text-gray-500" v-html="field.help"></p>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-featured_image">
<div class="wpuf-fields">
    <div :id="'wpuf-img_label-' + field.id + '-upload-container'">
        <div class="wpuf-attachment-upload-filelist" data-type="file" data-required="yes">
            <a class="wpuf-inline-flex wpuf-items-center wpuf-gap-x-1.5"
               :class="builder_class_names('upload_btn')" href="#">
                <template v-if="field.button_label === ''">
                    <?php _e( 'Select Image', 'wp-user-frontend' ); ?>
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

    <p v-if="field.help" class="wpuf-mt-2 wpuf-text-sm wpuf-text-gray-500" v-html="field.help"></p>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-fields">
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
                                class="wpuf-shrink-0 wpuf-mr-2 wpuf-text-gray-400">
                                <i :class="['fa fa-' + field_settings[field].icon]" aria-hidden="true"></i>
                            </div>
                            <div class="wpuf-min-w-0 wpuf-flex-1">
                                <a href="#" class="focus:wpuf-outline-none">
                                    <span class="wpuf-absolute wpuf-inset-0" aria-hidden="true"></span>
                                    <p class="wpuf-text-sm wpuf-font-medium wpuf-text-gray-400 wpuf-m-0">{{ field_settings[field].title }}</p>
                                </a>
                            </div>
                            <img src="<?php esc_attr_e( WPUF_ASSET_URI . '/images/crown.svg' ); ?>" alt="">
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
                    <?php _e( 'Select Image', 'wp-user-frontend' ); ?>
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

    <p v-if="field.help" class="wpuf-mt-2 wpuf-text-sm wpuf-text-gray-500" v-html="field.help"></p>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-multiple_select">
<div class="wpuf-fields">
    <select
        :class="class_names('multi_label')"
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
    <div class="wp-media-buttons" v-if="field.insert_image == 'yes'">
        <button type="button" class="button insert-media add_media" data-editor="content">
            <span class="dashicons dashicons-admin-media insert-photo-icon"></span> <?php _e( 'Insert Photo', 'wp-user-frontend' ); ?>
        </button>
    </div>
    <br v-if="field.insert_image == 'yes'" />

    <textarea
        v-if="'no' === field.rich"
        :class="class_names('textareafield')"
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
        :class="class_names('textareafield')"
        class="wpuf-block wpuf-w-full wpuf-rounded-md wpuf-py-1.5 wpuf-text-gray-900 wpuf-shadow-sm   placeholder:wpuf-text-gray-400 sm:wpuf-text-sm sm:wpuf-leading-6 wpuf-border !wpuf-border-gray-300"
        :placeholder="field.placeholder"
        :rows="field.rows"
        :cols="field.cols"
    >{{ field.default }}</textarea>

    <text-editor v-if="'no' !== field.rich" :rich="field.rich" :default_text="field.default"></text-editor>

    <p v-if="field.help" class="wpuf-mt-2 wpuf-text-sm wpuf-text-gray-500" v-html="field.help"></p>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-post_tags">
<div class="wpuf-fields">
    <input
        type="text"
        :class="builder_class_names('text')"
        class="wpuf-block wpuf-w-full wpuf-rounded-md wpuf-py-1.5 wpuf-text-gray-900 wpuf-shadow-sm   placeholder:wpuf-text-gray-400 sm:wpuf-text-sm sm:wpuf-leading-6 wpuf-border !wpuf-border-gray-300"
        :placeholder="field.placeholder"
        :value="field.default"
        :size="field.size"
    >

    <p v-if="field.help" class="wpuf-mt-2 wpuf-text-sm wpuf-text-gray-500" v-html="field.help"></p>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-post_title">
<div class="wpuf-fields">
    <input
        type="text"
        :placeholder="field.placeholder"
        :value="field.default"
        :size="field.size"
        :class="class_names('textfield')"
        class="wpuf-block wpuf-w-full wpuf-rounded-md wpuf-py-1.5 wpuf-text-gray-900 wpuf-shadow-sm   placeholder:wpuf-text-gray-400 sm:wpuf-text-sm sm:wpuf-leading-6 wpuf-border !wpuf-border-gray-300"
    >
    <p v-if="field.help" class="wpuf-mt-2 wpuf-text-sm wpuf-text-gray-500" v-html="field.help"></p>
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
                class="wpuf-border-gray-300 wpuf-text-indigo-600 !wpuf-m-0">
            <label
                :value="val"
                :checked="is_selected(val)"
                :class="class_names('radio_btns')"
                class="wpuf-ml-3 wpuf-block wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-900">{{ label }}</label>
        </div>
    </div>

    <div
        v-else
        class="wpuf-space-y-6 sm:wpuf-flex sm:wpuf-items-center sm:wpuf-space-x-10 sm:wpuf-space-y-0">
        <div
            v-if="has_options" v-for="(label, val) in field.options"
            class="wpuf-flex wpuf-items-center">
            <input type="radio" class="wpuf-h-4 wpuf-w-4 wpuf-border-gray-300 wpuf-text-indigo-600 !wpuf-m-0">
            <label
                :value="val"
                :checked="is_selected(val)"
                :class="class_names('radio_btns')"
                class="wpuf-ml-3 wpuf-block wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-900">{{ label }}</label>
        </div>
    </div>

    <p v-if="field.help" class="wpuf-mt-2 wpuf-text-sm wpuf-text-gray-500" v-html="field.help"></p>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-recaptcha">
<div class="wpuf-fields">
    <template v-if="!has_recaptcha_api_keys">
        <p v-html="no_api_keys_msg"></p>
    </template>

    <template v-else>
    	<div
            v-if="'invisible_recaptcha' !== field.recaptcha_type"
            class="2xl:wpuf-w-1/3 wpuf-w-1/2">
        	<img class="wpuf-recaptcha-placeholder" src="<?php echo WPUF_ASSET_URI . '/images/recaptcha-placeholder.png'; ?>" alt="">
        </div>
        <div v-else><p><?php _e( 'Invisible reCaptcha', 'wp-user-frontend' ); ?></p></div>
    </template>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-section_break">
<div class="wpuf-fields">
    <div
        v-if="!field.divider || field.divider === 'regular'"
        class="wpuf-section-wrap">
        <h2 class="wpuf-section-title">{{ field.label }}</h2>
        <div class="wpuf-section-details">{{ field.description }}</div>
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
        v-html ="get_term_dropdown_options()">
    </select>

    <div v-if="'ajax' === field.type" class="category-wrap">
        <div>
            <select
                :class="builder_class_names('select')"
            >
                <option><?php _e( '— Select —', 'wp-user-frontend' ); ?></option>
                <option v-for="term in sorted_terms" :value="term.id">{{ term.name }}</option>
            </select>
        </div>
    </div>

    <div v-if="'multiselect' === field.type" class="category-wrap">
        <select
            :class="builder_class_names('select')"
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
    <p v-if="field.help" class="wpuf-mt-2 wpuf-text-sm wpuf-text-gray-500" v-html="field.help"></p>
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
    <p v-if="field.help" class="wpuf-mt-2 wpuf-text-sm wpuf-text-gray-500" v-html="field.help"></p>
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

    <p v-if="field.help" class="wpuf-mt-2 wpuf-text-sm wpuf-text-gray-500" v-html="field.help"></p>
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
    <p v-if="field.help" class="wpuf-mt-2 wpuf-text-sm wpuf-text-gray-500" v-html="field.help"></p>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-help-text">
<i class="fa fa-question-circle field-helper-text wpuf-tooltip" :data-placement="placement" :title="text"></i>
</script>

<script type="text/x-template" id="tmpl-wpuf-text-editor">
<div class="wpuf-text-editor">

    <div class="wp-core-ui wp-editor-wrap tmce-active">
        <link rel="stylesheet" :href="site_url + 'wp-includes/css/editor.css'" type="text/css" media="all">
        <link rel="stylesheet" :href="site_url + 'wp-includes/js/tinymce/skins/lightgray/skin.min.css'" type="text/css" media="all">

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
