<div class="wpuf-fields">
    <div :id="'wpuf-img_label-' + field.id + '-upload-container'">
        <div class="wpuf-attachment-upload-filelist" data-type="file" data-required="yes">
            <a class="button file-selector wpuf_img_label_148" href="#">
                {{ field.button_label }}
            </a>
        </div>
    </div>

    <span v-if="field.help" class="wpuf-help">{{ field.help }}</span>
</div>
