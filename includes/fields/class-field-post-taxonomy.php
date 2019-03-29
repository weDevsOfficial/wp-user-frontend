<?php
// Post Taxonomy Class
class WPUF_Form_Field_Post_Taxonomy extends WPUF_Field_Contract {

    use WPUF_Form_Field_Post_trait;

    protected $tax_name;

    protected $taxonomy;

    protected $terms=array();

    protected $class;

    protected $field_settings;

    protected $form_id;


	function __construct( $tax_name, $taxonomy,$post_id = null , $user_id = null ) {
        $this->name       = __( $tax_name, 'wp-user-frontend' );
        $this->input_type = 'taxonomy';
        $this->tax_name   = $tax_name;
        // $this->taxonomy=$taxonomy;
        // $this->icon       = 'caret-square-o-down';
    }

    /**
     * Render the Post Taxonomy field
     *
     * @param  array  $field_settings
     * @param  integer  $form_id
     * @param  string  $type
     * @param  integer  $post_id
     *
     * @return void
     */
    public function render( $field_settings, $form_id, $type = 'post', $post_id = null ) {

         $this->field_settings = $field_settings;

         $this->form_id = $form_id;

    ?>

        <li <?php $this->print_list_attributes( $this->field_settings ); ?>>

        <?php

        $this->print_label( $this->field_settings, $this->form_id );

        $this->exclude_type       = isset( $this->field_settings['exclude_type'] ) ? $this->field_settings['exclude_type'] : 'exclude';

        $this->exclude            = $this->field_settings['exclude'];
        if ( $this->exclude_type == 'child_of' && !empty( $this->exclude ) ) {
          $this->exclude = $this->exclude[0];
        }

        $this->taxonomy           = $this->field_settings['name'];

        $this->class              = ' wpuf_'.$this->field_settings['name'].'_'.$form_id;

        $current_user       = get_current_user_id();

        if ( $post_id && $this->field_settings['type'] == 'text' ) {
            $this->terms = wp_get_post_terms( $post_id, $this->taxonomy, array('fields' => 'names') );
        } elseif( $post_id ) {
            $this->terms = wp_get_post_terms( $post_id, $this->taxonomy, array('fields' => 'ids') );
        }

        if ( ! taxonomy_exists( $this->taxonomy ) ) {
            echo '<br><div class="wpuf-message">' . __( 'This field is no longer available.', 'wp-user-frontend' ) . '</div>';
            return;
        }

        $div_class = 'wpuf_' . $this->field_settings['name'] . '_' . $this->field_settings['type'] . '_' . $field_settings['id'] . '_' . $form_id;

        if ( $this->field_settings['type'] == 'checkbox' ) { ?>
            <div class="wpuf-fields <?php echo $div_class; ?>" data-required="<?php echo esc_attr( $field_settings['required'] ); ?>" data-type="tax-checkbox">
        <?php } else { ?>
            <div class="wpuf-fields <?php echo $div_class; ?>">
        <?php }

        switch ($this->field_settings['type']) {
            case 'ajax':
                $this->tax_ajax( $post_id );
                break;
            case 'select':
                $this->tax_select($post_id=NULL);
                break;
            case 'multiselect':
                $this->tax_multiselect($post_id=NULL);
                break;
            case 'checkbox':
                wpuf_category_checklist( $post_id, false, $this->field_settings, $this->class);
                break;
            case 'text':
                $this->tax_input($post_id=NULL);
                break;
            default:
                # code...
                break;

        } ?>
        <span class="wpuf-wordlimit-message wpuf-help"></span>
        <?php $this->help_text( $field_settings );

    }

    public function taxnomy_select( $terms ) {

        $attr = $this->field_settings;

        $selected           = $terms ? $terms : '';
        $required           = sprintf( 'data-required="%s" data-type="select"', $attr['required'] );
        $class              = ' wpuf_'.$attr['name'].'_'.$selected;

        if ( $this->exclude_type == 'child_of' && !empty( $this->exclude ) ) {
          $this->exclude = $this->exclude[0];
        }
        $tax_args  = array(
            'show_option_none' => __( '-- Select --', 'wp-user-frontend' ),
            'hierarchical'     => 1,
            'hide_empty'       => 0,
            'orderby'          => isset( $attr['orderby'] ) ? $attr['orderby'] : 'name',
            'order'            => isset( $attr['order'] ) ? $attr['order'] : 'ASC',
            'name'             => $this->taxonomy . '[]',
            'taxonomy'         => $this->taxonomy,
            'echo'             => 0,
            'title_li'         => '',
            'class'            => 'cat-ajax '. $this->taxonomy . $this->class,
            $this->exclude_type      => $this->exclude,
            'selected'         => $selected,
            'depth'            => 1,
            'child_of'         => isset( $attr['parent_cat'] ) ? $attr['parent_cat'] : ''
        );

        $tax_args = apply_filters( 'wpuf_taxonomy_checklist_args', $tax_args );

        $select = wp_dropdown_categories( $tax_args );



        echo str_replace( '<select', '<select ' . $required, $select );
        $attr = array(
            'required'     => $attr['required'],
            'name'         => $attr['name'],
             'exclude_type' =>isset( $attr['exclude_type'] ) ? $attr['exclude_type'] : 'exclude',
            // 'exclude_type' => $attr['exclude_type'],
            'exclude'      => $attr['exclude'],
            'orderby'      => $attr['orderby'],
            'order'        => $attr['order'],
            'name'         => $attr['name'],
           // 'last_term_id' => isset( $attr['parent_cat'] ) ? $attr['parent_cat'] : '',
            //'term_id'      => $selected
        );
        $attr = apply_filters( 'wpuf_taxonomy_checklist_args', $attr );
        ?>
        <span data-taxonomy=<?php  echo json_encode( $attr ); ?>></span>
        <?php
    }

    public function catbuildTree($items) {
        $childs = array();

        foreach($items as &$item) $childs[$item->parent][] = &$item;
        unset($item);

        foreach($items as &$item) {
            if (isset($childs[$item->term_id])) {
                $item->childs = $childs[$item->term_id];
            }
        }

        return $childs[0];
    }

    public function RecursiveCatWrite($tree) {
        foreach ($tree as $vals) {
            $level = 0;
        ?>
             <div id="lvl<?php echo $level; ?>" level="<?php echo $level; ?>" >
                <?php $this->taxnomy_select( $vals->term_id ); ?>
            </div>

            <?php
            $this->field_settings['parent_cat'] = $vals->term_id;
            if( isset( $vals->childs ) ){
                $this->RecursiveCatWrite( $vals->childs );
            }else{

            }
        }
    }

    public function tax_ajax( $post_id = NULL ) {

        if( isset( $post_id ) ) {
            $this->terms = wp_get_post_terms( $post_id, $this->taxonomy, array('fields' => 'all') );
        }
    ?>

        <div class="category-wrap <?php echo $this->class; ?>">

            <?php

            if ( !count( $this->terms ) ) {

                ?>
                <div id="lvl0" level="0">
                    <?php $this->taxnomy_select( null ); ?>
                </div>
                <?php
            } else {
                $tree = $this->catbuildTree( $this->terms );
                $this->RecursiveCatWrite($tree);
            }
        ?>
        </div>
        <span class="loading"></span>
    <?php

    }

    public function tax_select( $post_id = NULL ) {

        $attr = $this->field_settings;

        $selected = $this->terms ? $this->terms[0] : '';

        $required = sprintf( 'data-required="%s" data-type="select"', $attr['required'] );

        $tax_args = array(
            'show_option_none' => isset ( $attr['first'] ) ? $attr['first'] : '--select--',
            'hierarchical'     => 1,
            'hide_empty'       => 0,
            'orderby'          => isset( $attr['orderby'] ) ? $attr['orderby'] : 'name',
            'order'            => isset( $attr['order'] ) ? $attr['order'] : 'ASC',
            'name'             => $this->taxonomy . '[]',
            'taxonomy'         => $this->taxonomy,
            'echo'             => 0,
            'title_li'         => '',
            'class'            => $this->taxonomy . $this->class,
            $this->exclude_type      => $this->exclude,
            'selected'         => $selected,
        );

        $tax_args = apply_filters( 'wpuf_taxonomy_checklist_args', $tax_args );

        $select   = wp_dropdown_categories( $tax_args );

        echo str_replace( '<select', '<select ' . $required, $select );
    }


    public function tax_multiselect( $post_id = NULL ){

        $attr = $this->field_settings;

        $selected = $this->terms ? $this->terms : array();

        $required = sprintf( 'data-required="%s" data-type="multiselect"', $attr['required'] );

        $walker   = new WPUF_Walker_Category_Multi();

        $tax_args = array(
            // 'show_option_none' => __( '-- Select --', 'wpuf' ),
            'hierarchical'     => 1,
            'hide_empty'       => 0,
            'orderby'          => isset( $attr['orderby'] ) ? $attr['orderby'] : 'name',
            'order'            => isset( $attr['order'] ) ? $attr['order'] : 'ASC',
            'name'             => $this->taxonomy . '[]',
            'id'               => 'cat-ajax',
            'taxonomy'         => $this->taxonomy,
            'echo'             => 0,
            'title_li'         => '',
            'class'            => $this->taxonomy . ' multiselect' . $this->class,
            $this->exclude_type      => $this->exclude,
            'selected'         => $selected,
            'walker'           => $walker
        );

        $tax_args = apply_filters( 'wpuf_taxonomy_checklist_args', $tax_args );

        $select   = wp_dropdown_categories( $tax_args );

        echo str_replace( '<select', '<select multiple="multiple" ' . $required, $select );
    }


    public function tax_input( $post_id = NULL ){

        $attr = $this->field_settings;

    ?>

        <input class="textfield<?php echo $this->required_class( $attr ); ?>" id="<?php echo $attr['name']; ?>" type="text" data-required="<?php echo $attr['required'] ?>" data-type="text"<?php $this->required_html5( $attr ); ?> name="<?php echo esc_attr( $attr['name'] ); ?>" value="<?php echo esc_attr( implode( ', ', $this->terms ) ); ?>" size="40" />

        <script type="text/javascript">
            ;(function($) {
                $(document).ready( function(){
                        $('#<?php echo $attr['name']; ?>').suggest( wpuf_frontend.ajaxurl + '?action=wpuf-ajax-tag-search&tax=<?php echo $attr['name']; ?>', { delay: 500, minchars: 2, multiple: true, multipleSep: ', ' } );
                });
            })(jQuery);
        </script>



    <?php }

    /**
     * Get field options setting
     *
     * @return array
    */
    public function get_options_settings() {
        $default_options      = $this->get_default_option_settings(false,array('dynamic'));
        $default_text_options = $this->get_default_taxonomy_option_setttings(false,$this->tax_name);
        return array_merge( $default_options, $default_text_options );
    }

    /**
     * Get the field props
     *
     * @return array
     */
    public function get_field_props() {
        $defaults = $this->default_attributes();
        $props    = array(
            'input_type'        => 'taxonomy',
            'label'             => $this->tax_name,
            'name'              => $this->tax_name,
            'is_meta'           => 'no',
            'width'             => 'small',
            'type'              => 'select',
            'first'             => __( '- select -', 'wp-user-frontend' ),
            'show_inline'       => 'inline',
            'orderby'           => 'name',
            'order'             => 'ASC',
            'exclude'           => array(),
            'id'                => 0,
            'is_new'            => true,
        );
        return array_merge( $defaults, $props );
    }

    /**
     * Prepare entry
     *
     * @param $field
     *
     * @return mixed
     */
    public function prepare_entry( $field ) {
        // $val   = $_POST[$field['name']];
        // return isset( $field['options'][$val] ) ? $field['options'][$val] : '';
        // return sanitize_text_field($_POST[$field['name']]);

        return $val   = $_POST[$field['name']];
        return isset( $field['options'][$val] ) ? $field['options'][$val] : '';

    }

}
