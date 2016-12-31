<div id="form-preview-stage">
    <ul class="wpuf-form">
        <li v-for="field in form_fields" :class="['field-items', 'wpuf-el', field.name, field.css]">
            <div class="wpuf-label">
                <label :for="'wpuf-' + field.name ? field.name : 'cls'">
                    {{ field.label }} <span v-if="field.required && 'yes'" class="required">*</span>
                </label>
            </div>

            <component :is="'form-' + field.template" :field="field"></component>

            <div class="control-buttons">
                <p>
                    <i class="fa fa-arrows move"></i>
                    <i class="fa fa-pencil" @click="open_field_settings(field.id)"></i>
                    <i class="fa fa-clone" @click="clone_field(field.id)"></i>
                    <i class="fa fa-trash-o" @click="delete_field(field.id)"></i>
                </p>
            </div>
        </li>

        <li class="wpuf-submit">
            <div class="wpuf-label">&nbsp;</div>

            <?php do_action( 'wpuf-form-builder-template-builder-stage-submit-area' ); ?>
        </li>
    </ul><!-- .wpuf-form -->
    <pre>{{ $data }}</pre>
    <pre>{{ form_fields }}</pre>
</div><!-- #form-preview-stage -->
