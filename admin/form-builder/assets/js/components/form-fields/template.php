<div class="wpuf-form-builder-form-fields">
    <div v-for="(section, index) in panel_sections" class="panel-form-field-group clearfix">
        <h3 class="clearfix" @click="panel_toggle(index)">
            {{ section.title }} <i :class="[section.show ? 'fa fa-angle-down' : 'fa fa-angle-right']"></i>
        </h3>

        <transition name="slide-fade">
            <div v-if="section.show" class="panel-form-field-buttons clearfix">
                <button v-for="field in section.fields" type="button" class="button">
                    <i :class="['fa fa-' + field_settings[field].icon]" aria-hidden="true"></i> {{ field_settings[field].title }}
                </button>
            </div>
        </transition>
    </div>
</div>
