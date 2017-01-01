<div id="form-preview-stage">
    <h4 v-if="!form_fields.length" class="text-center">
        <?php _e( 'Add fields by dragging the fields from the right sidebar to this area.', 'wpuf' ) ?>
    </h4>

    <ul class="wpuf-form">
        <li
            v-for="(field, index) in form_fields"
            :key="field.id"
            :class="['field-items', 'wpuf-el', field.name, field.css]"
            :data-index="index"
            data-source="stage"
        >
            <div class="wpuf-label">
                <label :for="'wpuf-' + field.name ? field.name : 'cls'">
                    {{ field.label }} <span v-if="field.required && 'yes' === field.required" class="required">*</span>
                </label>
            </div>

            <component :is="'form-' + field.template" :field="field"></component>

            <div class="control-buttons">
                <p>
                    <i class="fa fa-arrows move"></i>
                    <i class="fa fa-pencil" @click="open_field_settings(field.id)"></i>
                    <i class="fa fa-clone" @click="clone_field(field.id, index)"></i>
                    <i class="fa fa-trash-o" @click="delete_field(index)"></i>
                </p>
            </div>
        </li>

        <li v-if="!form_fields.length" class="field-items empty-list-item"></li>

        <li class="wpuf-submit">
            <div class="wpuf-label">&nbsp;</div>

            <?php do_action( 'wpuf-form-builder-template-builder-stage-submit-area' ); ?>
        </li>
    </ul><!-- .wpuf-form -->
    <!-- <pre>{{ $data }}</pre> -->
    <pre>{{ form_fields }}</pre>
</div><!-- #form-preview-stage -->
