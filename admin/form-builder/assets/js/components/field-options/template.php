<div class="wpuf-form-builder-field-options">
    <div class="option-fields-section">
        <h3 class="section-title clearfix" @click="show_basic_settings = !show_basic_settings">
            {{ form_field_type_title }} <i :class="[show_basic_settings ? 'fa fa-angle-down' : 'fa fa-angle-right']"></i>
        </h3>

        <transition name="slide-fade">
            <div v-if="show_basic_settings" class="option-field-section-fields">
                <component
                    v-for="option_field in basic_settings"
                    :is="'field-' + option_field.type"
                    :option_field="option_field"
                    :editing_form_field="editing_form_field"
                ></component>
            </div>
        </transition>
    </div>


    <div class="option-fields-section">
        <h3 class="section-title" @click="show_advanced_settings = !show_advanced_settings">
            {{ i18n.advanced_options }}  <i :class="[show_advanced_settings ? 'fa fa-angle-down' : 'fa fa-angle-right']"></i>
        </h3>

        <transition name="slide-fade">
            <div v-if="show_advanced_settings" class="option-field-section-fields">
                <component
                    v-for="option_field in advanced_settings"
                    :is="'field-' + option_field.type"
                    :option_field="option_field"
                    :editing_form_field="editing_form_field"
                ></component>
                <!-- <pre>{{ advanced_settings }}</pre> -->
            </div>
        </transition>
    </div>
</div>
