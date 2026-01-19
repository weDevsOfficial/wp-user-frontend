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
