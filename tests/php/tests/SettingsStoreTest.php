<?php
/**
 * The React settings REST controller forwards to the settings store and still
 * stores the same bytes, fires the same hook and answers the same way (task
 * 2.4c); compared with the pre-store copy in tests/php/src/LegacySettingsWriters.php.
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Platform\REST\Controllers\SettingsController as Settings_Api;
use WeDevs\Wpuf\Platform\Stores\SettingsStore;
use WeDevs\Wpuf\Platform\Stores\Stores;

/**
 * Settings store tests.
 */
class SettingsStoreTest extends WP_UnitTestCase {

    /**
     * `wpuf_settings_saved` calls.
     *
     * @var array
     */
    private $fired = [];

    public function set_up() {
        parent::set_up();
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
        add_action(
            'wpuf_settings_saved',
            function ( $saved, $incoming, $extra ) {
                $this->fired[] = [ $saved, $incoming, $extra ];
            },
            10,
            3
        );
    }

    private function section_ids() {
        return wp_list_pluck( Stores::settings()->sections(), 'id' );
    }

    private function options() {
        $options = [];

        foreach ( $this->section_ids() as $id ) {
            $options[ $id ] = get_option( $id, 'ABSENT' );
        }

        return $options;
    }

    private function reset_options( $options ) {
        foreach ( $options as $id => $value ) {
            if ( 'ABSENT' === $value ) {
                delete_option( $id );
            } else {
                update_option( $id, $value );
            }
        }
    }

    /**
     * A value of the field's type, including markup, slashes and odd numbers.
     */
    private function value_for( $field ) {
        $type    = isset( $field['type'] ) ? $field['type'] : 'text';
        $options = isset( $field['options'] ) && is_array( $field['options'] ) ? array_keys( $field['options'] ) : [ 'a', 'b' ];

        switch ( $type ) {
            case 'multicheck':
            case 'multiselect':
                return array_slice( $options, 0, 2 );
            case 'checkbox':
                return 'on';
            case 'number':
                return '1e3';
            case 'select':
            case 'radio':
                return isset( $options[0] ) ? $options[0] : 'x';
            case 'url':
                return 'javascript:alert(1)';
            case 'textarea':
            case 'wysiwyg':
                return "<p>Hi \\ \"x\"</p><script>bad()</script>";
            default:
                return "Text <b>bold</b> \\ 'q'";
        }
    }

    /**
     * Every registered field of every section with a typed value, plus an
     * unknown field and an unknown section.
     */
    private function full_payload() {
        $payload = [ 'not_a_section' => [ 'x' => 'y' ] ];

        foreach ( Stores::settings()->fields() as $section_id => $fields ) {
            // Pro previews are display only since 4.7 (the reference copy stored
            // them): left out here, covered by test_pro_previews_are_not_stored().
            if ( $this->is_preview_section( $section_id ) ) {
                continue;
            }

            foreach ( (array) $fields as $field ) {
                if ( ! empty( $field['name'] ) && ! Stores::settings()->is_pro_preview( $field ) ) {
                    $payload[ $section_id ][ $field['name'] ] = $this->value_for( $field );
                }
            }

            $payload[ $section_id ]['not_a_field'] = 'z';
        }

        return $payload;
    }

    private function is_preview_section( $section_id ) {
        $section = current( wp_list_filter( Stores::settings()->sections(), [ 'id' => $section_id ] ) );

        return is_array( $section ) && Stores::settings()->is_pro_preview( $section, 'title' );
    }

    private function request( $settings, $extra = null ) {
        $request = new WP_REST_Request( 'POST', '/wpuf/v1/settings' );
        $request->set_param( 'settings', $settings );

        if ( null !== $extra ) {
            $request->set_param( 'extra', $extra );
        }

        return $request;
    }

    /**
     * Save through the legacy controller and the current one from the same
     * starting options; return both results.
     */
    private function both( $settings, $extra = null ) {
        $start = $this->options();

        $this->fired     = [];
        $legacy          = ( new WPUF_Legacy_Settings_Api() )->save_items( $this->request( $settings, $extra ) );
        $legacy_options  = $this->options();
        $legacy_fired    = $this->fired;
        $this->reset_options( $start );

        $this->fired     = [];
        $current         = ( new Settings_Api() )->save_items( $this->request( $settings, $extra ) );

        return [ $legacy, $current, $legacy_options, $this->options(), $legacy_fired, $this->fired ];
    }

    /**
     * The legacy save response with secrets masked, as the REST save answers
     * since the adversarial review (the read route already masked them).
     *
     * @param array $data Response data.
     *
     * @return array
     */
    private function masked( $data ) {
        if ( empty( $data['data']['values'] ) || ! is_array( $data['data']['values'] ) ) {
            return $data;
        }

        $store = Stores::settings();

        foreach ( $data['data']['values'] as $section_id => $values ) {
            $data['data']['values'][ $section_id ] = $store->mask_secrets( $values, $store->section_fields( $section_id ) );
        }

        foreach ( [ 'openai', 'anthropic', 'google' ] as $provider ) {
            if ( isset( $data['data']['values']['wpuf_ai'][ $provider . '_api_key' ] ) ) {
                $data['data']['values']['wpuf_ai'][ $provider . '_api_key' ] = wpuf_settings_mask_secret( $data['data']['values']['wpuf_ai'][ $provider . '_api_key' ], 4 );
            }
        }

        return $data;
    }

    public function test_store_is_shared_from_the_container() {
        $this->assertInstanceOf( SettingsStore::class, Stores::settings() );
        $this->assertSame( Stores::settings(), wpuf()->platform()->get( SettingsStore::class ) );
    }

    public function test_full_save_stores_the_same_bytes_hook_and_response() {
        $payload = $this->full_payload();
        $this->assertGreaterThan( 5, count( $payload ), 'real sections in the schema' );
        $this->assertGreaterThan( 40, array_sum( array_map( 'count', $payload ) ), 'real fields in the schema' );

        list( $legacy, $current, $legacy_options, $current_options, $legacy_fired, $current_fired ) = $this->both( $payload, [ 'tax' => [ 'rate' => 5 ] ] );
        $this->assertNotEmpty( array_filter( $current_options, 'is_array' ), 'options were written' );

        $this->assertSame( $legacy->get_status(), $current->get_status() );
        $this->assertSame( $this->masked( $legacy->get_data() ), $current->get_data(), 'same response, secrets masked' );
        $this->assertSame( $legacy_options, $current_options, 'every section option identical' );
        $this->assertSame( $legacy_fired, $current_fired, 'wpuf_settings_saved fired once with the same saved, incoming and extra' );
        $this->assertCount( 1, $current_fired );
    }

    public function test_first_save_of_one_field_stores_defaults_like_before() {
        foreach ( $this->section_ids() as $id ) {
            delete_option( $id );
        }

        $first = $this->section_ids()[0];
        list( , , $legacy_options, $current_options ) = $this->both( [ $first => [] ] );

        $this->assertSame( $legacy_options, $current_options );
    }

    /**
     * The legacy form posted strings, so a numeric field default (Pro's
     * `pre_sub_notification_date` => 7) was first stored as "7" (4.6b, SET0010).
     */
    public function test_numeric_default_is_stored_as_the_posted_string() {
        $store  = Stores::settings();
        $fields = [
            [ 'name' => 'edited', 'type' => 'text', 'default' => '' ],
            [ 'name' => 'days', 'type' => 'number', 'default' => 7 ],
            [ 'name' => 'ratio', 'type' => 'text', 'default' => 1.5 ],
        ];

        delete_option( 'wpuf_test_numeric_default' );
        $stored = $store->save_section( 'wpuf_test_numeric_default', [ 'edited' => 'x' ], $fields );
        delete_option( 'wpuf_test_numeric_default' );

        $this->assertSame( '7', $stored['days'] );
        $this->assertSame( '1.5', $stored['ratio'] );
        $this->assertSame( 'x', $stored['edited'] );
    }

    /**
     * The legacy wp_editor() posted a wysiwyg default back trimmed per line
     * with \r\n breaks (the PHP source indentation gone); the first save stores
     * that shape (5b.9a, settings comparison 2026-10-06).
     */
    public function test_wysiwyg_default_is_stored_like_the_legacy_editor_posted_it() {
        $store  = Stores::settings();
        $fields = [
            [ 'name' => 'edited', 'type' => 'text', 'default' => '' ],
            [ 'name' => 'body', 'type' => 'wysiwyg', 'default' => "Hi {username},\n\n            Your post has been approved.\n\n            Thanks" ],
            [ 'name' => 'tail', 'type' => 'wysiwyg', 'default' => "Dear Subscriber, \r\n\r\nYour pack is expiring!" ],
            [ 'name' => 'plain', 'type' => 'textarea', 'default' => "  keep\n  as is" ],
        ];

        delete_option( 'wpuf_test_wysiwyg_default' );
        $stored = $store->save_section( 'wpuf_test_wysiwyg_default', [ 'edited' => 'x' ], $fields );
        delete_option( 'wpuf_test_wysiwyg_default' );

        $this->assertSame( "Hi {username},\r\n\r\nYour post has been approved.\r\n\r\nThanks", $stored['body'] );
        $this->assertSame( "Dear Subscriber,\r\n\r\nYour pack is expiring!", $stored['tail'] );
        $this->assertSame( "  keep\n  as is", $stored['plain'], 'only wysiwyg defaults are normalized' );
    }

    /**
     * Rich text from the email body editors (bold, list, link with &amp;, a
     * {tag}) is stored byte for byte as the legacy screen stored it (QA
     * 2026-10-09: same md5 on develop and the branch for all 10 editors).
     */
    public function test_wysiwyg_html_is_stored_like_the_legacy_screen() {
        $html    = "<p>Hi <strong>{username}</strong>, see <em>this</em>:</p>\r\n<ul>\r\n \t<li>One</li>\r\n \t<li>Two &amp; more</li>\r\n</ul>\r\n<a href=\"https://example.com/x?a=1&amp;b=2\">Link</a>";
        $payload = [ 'wpuf_mails' => [ 'guest_email_body' => $html ] ];

        list( , , $legacy_options, $current_options ) = $this->both( $payload );

        $this->assertSame( $legacy_options['wpuf_mails']['guest_email_body'], $current_options['wpuf_mails']['guest_email_body'] );
        $this->assertStringContainsString( '<strong>{username}</strong>', $current_options['wpuf_mails']['guest_email_body'] );
        $this->assertStringContainsString( 'href="https://example.com/x?a=1&amp;b=2"', $current_options['wpuf_mails']['guest_email_body'] );
    }

    /**
     * Without Pro, a Pro preview field or section is display only: develop's
     * legacy screen never posted it, so a save stores nothing for it (4.7,
     * free-only pass). A field flagged `is_pro_preview` is never stored.
     */
    public function test_pro_previews_are_not_stored() {
        $store  = Stores::settings();
        $fields = [
            [ 'name' => 'real', 'type' => 'text' ],
            [ 'name' => 'flagged', 'type' => 'text', 'is_pro_preview' => true ],
            [ 'name' => 'badged', 'type' => 'text', 'label' => 'Key <span class="pro-icon"></span>' ],
        ];

        delete_option( 'wpuf_test_preview' );
        $stored = $store->save_section( 'wpuf_test_preview', [ 'real' => 'a', 'flagged' => 'b', 'badged' => 'c' ], $fields );
        delete_option( 'wpuf_test_preview' );

        // With Pro the badge is only a label: the field is real and stored.
        $this->assertSame( class_exists( 'WP_User_Frontend_Pro' ) ? [ 'real' => 'a', 'badged' => 'c' ] : [ 'real' => 'a' ], $stored );
        $this->assertSame( ! class_exists( 'WP_User_Frontend_Pro' ), $store->is_pro_preview( [ 'title' => 'SMS <img class="pro-badge">' ], 'title' ) );
        $this->assertFalse( $store->is_pro_preview( [ 'title' => 'General' ], 'title' ) );
    }

    public function test_empty_lists_masked_secrets_and_bad_input_match() {
        $store   = Stores::settings();
        $payload = [];

        foreach ( $store->fields() as $section_id => $fields ) {
            if ( $this->is_preview_section( $section_id ) ) {
                continue;
            }

            foreach ( (array) $fields as $field ) {
                if ( empty( $field['name'] ) || $store->is_pro_preview( $field ) ) {
                    continue;
                }

                if ( $store->is_multiselect( $field ) || ( isset( $field['type'] ) && 'multicheck' === $field['type'] ) ) {
                    $payload[ $section_id ][ $field['name'] ] = [];
                } elseif ( $store->is_secret_field( $field ) ) {
                    $payload[ $section_id ][ $field['name'] ] = wpuf_settings_mask_secret( 'stored-secret-value' );
                    update_option( $section_id, array_merge( (array) get_option( $section_id, [] ), [ $field['name'] => 'stored-secret-value' ] ) );
                } elseif ( isset( $field['type'] ) && 'number' === $field['type'] ) {
                    $payload[ $section_id ][ $field['name'] ] = 'abc';
                }
            }
        }

        list( $legacy, $current, $legacy_options, $current_options ) = $this->both( $payload );

        $expected = $this->masked( $legacy->get_data() );

        if ( isset( $expected['data']['values'] ) && is_array( $expected['data']['values'] ) ) {
            $expected['data']['values'] = $this->editor_defaults( $expected['data']['values'] );
        }

        $this->assertSame( $expected, $current->get_data() );
        $this->assertStringNotContainsString( 'stored-secret-value', wp_json_encode( $current->get_data() ), 'the save response never carries a stored secret' );
        $this->assertSame( $this->editor_defaults( $legacy_options ), $current_options );

        list( $legacy, $current ) = $this->both( 'not an array' );
        $this->assertSame( $legacy->get_data(), $current->get_data() );
        $this->assertSame( 400, $current->get_status() );
    }

    /**
     * Legacy-copy options with the first-save defaults of wysiwyg fields as
     * the editor posted them (trimmed lines, \r\n): the store does that on
     * purpose (SettingsStore::save_section(), Normalizers::wysiwyg_default()),
     * the frozen legacy copy does not. Only Pro turns these fields into
     * editors, so free-only runs compare the raw values.
     *
     * @param array $options Options per section.
     *
     * @return array
     */
    private function editor_defaults( $options ) {
        foreach ( Stores::settings()->fields() as $section_id => $fields ) {
            foreach ( (array) $fields as $field ) {
                if ( empty( $field['name'] ) || ! isset( $field['type'], $field['default'] ) || 'wysiwyg' !== $field['type'] || ! is_string( $field['default'] ) ) {
                    continue;
                }

                if ( isset( $options[ $section_id ][ $field['name'] ] ) && $options[ $section_id ][ $field['name'] ] === $field['default'] ) {
                    $options[ $section_id ][ $field['name'] ] = \WeDevs\Wpuf\Platform\Stores\Normalizers::wysiwyg_default( $field['default'] );
                }
            }
        }

        return $options;
    }

    public function test_get_items_returns_the_same_payload() {
        $this->both( $this->full_payload() );

        $legacy  = ( new WPUF_Legacy_Settings_Api() )->get_items( new WP_REST_Request( 'GET', '/wpuf/v1/settings' ) );
        $current = ( new Settings_Api() )->get_items( new WP_REST_Request( 'GET', '/wpuf/v1/settings' ) );

        $this->assertSame( $legacy->get_data(), $current->get_data() );
    }
}
