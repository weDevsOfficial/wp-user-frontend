<div class="wpuf-merge-tag-wrap">
    <a href="#" v-on:click.prevent="toggleFields($event)" class="merge-tag-link" title="<?php echo esc_attr( 'Click to toggle merge tags', 'wpuf' ); ?>"><span class="dashicons dashicons-editor-code"></span></a>

    <!-- <pre>{{ form_fields.length }}</pre> -->

    <div class="wpuf-merge-tags">
        <div class="merge-tag-section">
            <div class="merge-tag-head"><?php _e( 'Form Fields', 'wpuf' ); ?></div>

            <ul>
                <template v-if="form_fields.length">
                    <li v-for="field in form_fields">

                        <template v-if="field.input_type === 'name'">
                            <a href="#" v-on:click.prevent="insertField('name-full', field.name);">{{ field.label }}</a>
                            (
                            <a href="#" v-on:click.prevent="insertField('name-first', field.name);"><?php _e( 'first', 'wpuf' ); ?></a> |
                            <a href="#" v-on:click.prevent="insertField('name-middle', field.name);"><?php _e( 'middle', 'wpuf' ); ?></a> |
                            <a href="#" v-on:click.prevent="insertField('name-last', field.name);"><?php _e( 'last', 'wpuf' ); ?></a>
                            )
                        </template>

                        <a v-else href="#" v-on:click.prevent="insertField('field', field.name);">{{ field.label }}</a>

                    </li>
                </template>
                <li v-else><?php _e( 'No fields available', 'wpuf' ); ?></li>
            </ul>
        </div><!-- .merge-tag-section -->

        <?php
        if ( function_exists( 'weforms_get_merge_tags' ) ) {

            $merge_tags = weforms_get_merge_tags();

            foreach ($merge_tags as $section_key => $section) {
                ?>

                <div class="merge-tag-section">
                    <div class="merge-tag-head"><?php echo $section['title'] ?></div>

                    <ul>
                        <?php foreach ($section['tags'] as $key => $value) { ?>
                            <li>
                                <a href="#" v-on:click.prevent="insertField('<?php echo $key; ?>');"><?php echo $value; ?></a>
                            </li>
                        <?php } ?>
                    </ul>
                </div><!-- .merge-tag-section -->

                <?php
            }
        }
        ?>
    </div><!-- .merge-tags -->
</div>