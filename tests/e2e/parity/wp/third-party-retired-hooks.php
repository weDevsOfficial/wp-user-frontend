<?php
/**
 * Plugin Name: WPUF parity fixture: retired Vue builder hooks + builder slots
 * Description: Test-only (PAR0030). Listens on retired Vue builder hooks and fills every React builder slot.
 */

// Retired PHP hooks (Builder\HookDeprecations): still fire, deprecated, admin notice.
add_filter( 'wpuf_form_builder_js_root_mixins', function ( $mixins ) {
    $mixins[] = 'tp_root_mixin';
    return $mixins;
} );
add_action( 'wpuf_builder_field_options', function () {
    update_option( 'wpuf_parity_retired_action_ran', time(), false );
    echo '<div class="tp-vue-markup">vue only</div>';
} );

// React slots: one fill per slot, scope wpuf-form-builder.
add_action( 'admin_enqueue_scripts', function () {
    // The builder page, or the admin app page that opens builders as routes
    // (how a plugin that checked the page slug keeps working with the app).
    $builder_page = ! empty( $_GET['page'] ) && 'wpuf-post-forms' === $_GET['page']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $app_page     = function_exists( 'wpuf_is_admin_app' ) && wpuf_is_admin_app();

    if ( ! $builder_page && ! $app_page ) {
        return;
    }
    wp_register_script( 'wpuf-parity-slots', false, [ 'wp-plugins', 'wp-components', 'wp-element' ], '1', true );
    wp_enqueue_script( 'wpuf-parity-slots' );
    wp_add_inline_script( 'wpuf-parity-slots', <<<'JS'
( function () {
    var el = wp.element.createElement, Fill = wp.components.Fill;
    var names = [ 'wpuf-form-builder-field-options-after', 'wpuf-form-builder-option-data-actions', 'wpuf-form-builder-option-data-after', 'wpuf-form-builder-canvas-submit-area', 'wpuf-form-builder-canvas-bottom', 'wpuf-form-builder-settings-general' ];
    wp.plugins.registerPlugin( 'wpuf-parity-slots', {
        scope: 'wpuf-form-builder',
        render: function () {
            return el( wp.element.Fragment, null, names.map( function ( name ) {
                return el( Fill, { key: name, name: name }, function ( props ) {
                    var extra = props && props.field ? ' field=' + props.field.name : ( props && props.tab ? ' tab=' + props.tab : '' );
                    return el( 'div', { className: 'tp-fill', 'data-slot-name': name }, 'fill ' + name + extra );
                } );
            } ) );
        }
    } );
} )();
JS
    );
} );
