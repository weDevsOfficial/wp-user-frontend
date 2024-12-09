<div class="panel-field-opt panel-field-opt-text">
    <div class="wpuf-flex">
        <label
            class="wpuf-font-sm wpuf-text-gray-900">{{ option_field.title }}</label>
        <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>
    </div>
    <div class="wpuf-mt-2 wpuf-flex">
            <label class="wpuf-block text-sm/6 wpuf-font-medium wpuf-text-gray-900">
                <input
                    type="checkbox"
                    :value="key"
                    v-model="show_value"
                    class="wpuf-input-checkbox">
                <?php esc_attr_e( 'Show values', 'wp-user-frontend' ); ?>
            </label>
        <label class="wpuf-block text-sm/6 wpuf-font-medium wpuf-text-gray-900 wpuf-ml-2">
            <input
                type="checkbox"
                v-model="sync_value"
                class="wpuf-input-checkbox"
            /><?php esc_attr_e( 'Sync values', 'wp-user-frontend' ); ?>
        </label>
    </div>

    <ul :class="['option-field-option-chooser', show_value ? 'show-value' : '']">
        <div class="wpuf-flex wpuf-mt-2">
            <label class="wpuf-font-sm wpuf-text-gray-900">
                <?php esc_attr_e( 'Label', 'wp-user-frontend' ); ?>
            </label>
            <help-text text="<?php esc_attr_e( 'Do not use & or other special character for option label', 'wp-user-frontend' ); ?>"></help-text>
        </div>
        <div v-if="show_value" class="value">
            <?php esc_attr_e( 'Value', 'wp-user-frontend' ); ?>
        </div>
    </ul>

    <ul :class="['option-field-option-chooser margin-0', show_value ? 'show-value' : '']">
        <li v-for="(option, index) in options" :key="option.id" :data-index="index" class="clearfix option-field-option wpuf-flex wpuf-items-center wpuf-justify-start wpuf-gap-4">
            <div class="selector">
                <input
                    v-if="option_field.is_multiple"
                    type="checkbox"
                    :value="option.value"
                    v-model="selected"
                    class="wpuf-input-checkbox"
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

            <div class="wpuf-flex">
                <div
                    class="action-buttons hover:wpuf-cursor-pointer"
                    @click="delete_option(index)">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="wpuf-size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <div
                    v-if="index === options.length - 1"
                    class="plus-buttons hover:wpuf-cursor-pointer"
                    @click="add_option">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                         stroke="currentColor" class="wpuf-size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>
        </li>
    </ul>

    <a v-if="!option_field.is_multiple && selected" href="#clear" @click.prevent="clear_selection"><?php esc_attr_e( 'Clear Selection', 'wp-user-frontend' ); ?></a>
</div>
