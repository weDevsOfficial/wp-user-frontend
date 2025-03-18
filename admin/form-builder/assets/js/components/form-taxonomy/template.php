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
                <option class="wpuf-text-base !wpuf-leading-none"><?php _e( '— Select —', 'wp-user-frontend' ); ?></option>
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
    <p v-if="field.help" class="wpuf-mt-2 wpuf-mb-0 wpuf-text-sm wpuf-text-gray-500" v-html="field.help"></p>
</div>
