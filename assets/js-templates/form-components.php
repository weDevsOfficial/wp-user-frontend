<script type="text/x-template" id="tmpl-wpuf-builder-stage">
<div id="form-preview-stage">
    <h4 v-if="!form_fields.length" class="text-center">
        <?php _e( 'Add fields by dragging the fields from the right sidebar to this area.', 'wp-user-frontend' ); ?>
    </h4>

    <div
        v-for="(field, index) in form_fields"
        :key="field.id"
        :data-index="index"
        data-source="stage"
        :class="index === 0 ? '' : 'wpuf-mt-4'"
        class="wpuf-relative wpuf-flex wpuf-justify-between wpuf-space-x-3 wpuf-rounded-lg wpuf-bg-white wpuf-p-4 hover:wpuf-shadow-sm hover:wpuf-bg-gray-100 hover:wpuf-ease-in wpuf-transition wpuf-duration-150 wpuf-ease-out">
        <div v-if="!is_full_width(field.template)" class="wpuf-w-1/4">
            <label
                v-if="!is_invisible(field)"
                :for="'wpuf-' + field.name ? field.name : 'cls'"
                class="wpuf-block wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-900">
                {{ field.label }} <span v-if="field.required && 'yes' === field.required" class="required">*</span>
            </label>
        </div>
        <div class="wpuf-w-3/4">
            <component
                v-if="is_template_available(field)"
                :is="'form-' + field.template"
                :field="field"></component>
        </div>
    </div>

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
            </div>

            <component v-if="is_template_available(field)" :is="'form-' + field.template" :field="field"></component>

            <div v-if="is_pro_feature(field.template)" class="stage-pro-alert">
                <label class="wpuf-pro-text-alert">
                    <a :href="pro_link" target="_blank"><strong>{{ get_field_name(field.template) }}</strong> <?php _e( 'is available in Pro Version', 'wp-user-frontend' ); ?></a>
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

<script type="text/x-template" id="tmpl-wpuf-form-column_field">
<div v-bind:class="['wpuf-field-columns', 'has-columns-'+field.columns]">
    <div class="wpuf-column-field-inner-columns">
        <div class="wpuf-column">
            <!-- don't change column class names -->
            <div v-for="column in columnClasses" :class="[column, 'items-of-column-'+field.columns, 'wpuf-column-inner-fields']" :style="{ width: field.inner_columns_size[column], paddingRight: field.column_space+'px'}">
                <ul class="wpuf-column-fields-sortable-list">
                    <li
                        v-for="(field, index) in column_fields[column]"
                        :key="field.id"
                        :class="[
                            'column-field-items', 'wpuf-el', field.name, field.css, 'form-field-' + field.template,
                            field.width ? 'field-size-' + field.width : '',
                            parseInt(editing_form_id) === parseInt(field.id) ? 'current-editing' : ''
                        ]"
                        :column-field-index="index"
                        :in-column="column"
                        data-source="column-field-stage"
                    >
                        <div v-if="!is_full_width(field.template)" class="wpuf-label wpuf-column-field-label">
                            <label v-if="!is_invisible(field)" :for="'wpuf-' + field.name ? field.name : 'cls'">
                                {{ field.label }} <span v-if="field.required && 'yes' === field.required" class="required">*</span>
                            </label>
                        </div>

                        <component v-if="is_template_available(field)" :is="'form-' + field.template" :field="field"></component>

                        <div v-if="is_pro_feature(field.template)" class="stage-pro-alert">
                            <label class="wpuf-pro-text-alert">
                                <a :href="pro_link" target="_blank"><strong>{{ get_field_name(field.template) }}</strong> <?php _e( 'is available in Pro Version', 'wp-user-frontend' ); ?></a>
                            </label>
                        </div>

                        <div class="wpuf-column-field-control-buttons">
                            <p>
                                <i class="fa fa-arrows move"></i>
                                <i class="fa fa-pencil" @click="open_column_field_settings(field, index, column)"></i>
                                <i class="fa fa-clone" @click="clone_column_field(field, index, column)"></i>
                                <i class="fa fa-trash-o" @click="delete_column_field(index, column)"></i>
                            </p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <ul role="list" class="wpuf-divide-y wpuf-divide-gray-200">
        <li class="wpuf-py-4"></li>
        <li class="wpuf-py-4"></li>
        <li class="wpuf-py-4"></li>
    </ul>
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
            <a class="button file-selector" href="#">
                <template v-if="field.button_label === ''">
                    <?php _e( 'Select Image', 'wp-user-frontend' ); ?>
                </template>
                <template v-else>
                    {{ field.button_label }}
                </template>
            </a>
        </div>
    </div>

    <span v-if="field.help" class="wpuf-help" v-html="field.help" />
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

<script type="text/x-template" id="tmpl-wpuf-form-image_upload">
<div class="wpuf-fields">
    <div :id="'wpuf-img_label-' + field.id + '-upload-container'">
        <div class="wpuf-attachment-upload-filelist" data-type="file" data-required="yes">
            <a class="button file-selector wpuf_img_label_148" href="#">
                <template v-if="field.button_label === ''">
                    <?php _e( 'Select Image', 'wp-user-frontend' ); ?>
                </template>
                <template v-else>
                    {{ field.button_label }}
                </template>
            </a>
        </div>
    </div>

    <span v-if="field.help" class="wpuf-help" v-html="field.help"/>
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
    	<div v-if="'invisible_recaptcha' != field.recaptcha_type">
        	<img class="wpuf-recaptcha-placeholder" src="<?php echo WPUF_ASSET_URI . '/images/recaptcha-placeholder.png'; ?>" alt="">
        </div>
        <div v-else><p><?php _e( 'Invisible reCaptcha', 'wp-user-frontend' ); ?></p></div>
    </template>
</div>
</script>

<script type="text/x-template" id="tmpl-wpuf-form-section_break">
<div class="wpuf-section-wrap">
    <h2 class="wpuf-section-title">{{ field.label }}</h2>
    <div class="wpuf-section-details">{{ field.description }}</div>
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
        :deault="field.default"
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
