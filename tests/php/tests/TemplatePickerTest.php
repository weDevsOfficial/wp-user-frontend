<?php
/**
 * Data of the React form template picker (Admin\Forms\Template_Picker).
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Admin\Forms\Template_Picker;

class TemplatePickerTest extends WP_UnitTestCase {

    /**
     * A template object as the registries hold them.
     *
     * @param string $title   Title.
     * @param bool   $enabled Integration installed.
     * @param string $image   Image URL.
     *
     * @return object
     */
    private function template( $title, $enabled = true, $image = 'http://example.org/a.webp' ) {
        return new class( $title, $enabled, $image ) {
            public $title;
            public $image;
            public $description = '<b>Needs</b> the plugin.';
            private $enabled;

            public function __construct( $title, $enabled, $image ) {
                $this->title   = $title;
                $this->enabled = $enabled;
                $this->image   = $image;
            }

            public function get_title() {
                return $this->title;
            }

            public function is_enabled() {
                return $this->enabled;
            }

            public function get_image() {
                return $this->image;
            }
        };
    }

    public function test_post_templates_keep_key_link_and_category() {
        $data = Template_Picker::data(
            [
                'form_type'      => 'post',
                'registry'       => [
                    'post_form_template_post'        => $this->template( 'Post Form' ),
                    'post_form_template_woocommerce' => $this->template( 'WooCommerce Product', false ),
                    'post_form_template_volunteer'   => $this->template( 'Volunteer Opportunity' ),
                ],
                'action_name'    => 'post_form_template',
                'blank_form_url' => admin_url( 'admin.php?page=wpuf-post-forms&action=add-new' ),
            ]
        );

        $this->assertSame( 'post', $data['form_type'] );
        $this->assertSame( 'post', $data['default_category'] );
        $this->assertSame( [ 'ecommerce', 'post' ], wp_list_pluck( $data['categories'], 'slug' ) );
        $this->assertCount( 3, $data['templates'] );

        list( $post, $woo, $volunteer ) = $data['templates'];

        $this->assertSame( 'post_form_template_post', $post['key'] );
        $this->assertSame( 'post', $post['category'] );
        $this->assertTrue( $post['enabled'] );
        $this->assertStringContainsString( 'action=post_form_template', $post['url'] );
        $this->assertStringContainsString( 'template=post_form_template_post', $post['url'] );
        $this->assertStringContainsString( '_wpnonce=', $post['url'] );

        // Integration not installed: listed, no create link, plain description.
        $this->assertSame( 'ecommerce', $woo['category'] );
        $this->assertFalse( $woo['enabled'] );
        $this->assertSame( '', $woo['url'] );
        $this->assertSame( 'Needs the plugin.', $woo['description'] );

        // No keyword: the default category of the screen.
        $this->assertSame( 'post', $volunteer['category'] );
    }

    public function test_registration_templates_use_the_registration_categories() {
        $data = Template_Picker::data(
            [
                'form_type'   => 'profile',
                'registry'    => [
                    'simple_user_signup_template'         => $this->template( 'Simple User Signup' ),
                    'dokan_vendor_reg_template'           => $this->template( 'Dokan Vendor Registration Form', false ),
                    'wc_marketplace_reg_template'         => $this->template( 'WCFM Membership Registration Form', false ),
                    'community_member_join_form_template' => $this->template( 'Community Member Join Form' ),
                ],
                'action_name' => 'wpuf_profile_form_template',
            ]
        );

        $this->assertSame( 'profile', $data['form_type'] );
        $this->assertSame( [ 'general', 'ecommerce', 'membership', 'community' ], wp_list_pluck( $data['categories'], 'slug' ) );
        $this->assertSame(
            [ 'general', 'ecommerce', 'membership', 'community' ],
            wp_list_pluck( $data['templates'], 'category' )
        );
        $this->assertStringContainsString( 'action=wpuf_profile_form_template', $data['templates'][0]['url'] );
    }

    public function test_pro_previews_link_to_the_upgrade_page_only() {
        $data = Template_Picker::data(
            [
                'registry'      => [],
                'pro_templates' => [ 'WPUF_Pro_Form_Preview_EDD' => $this->template( 'EDD Download' ) ],
            ]
        );

        $preview = $data['templates'][0];

        $this->assertSame( 'pro_WPUF_Pro_Form_Preview_EDD', $preview['key'] );
        $this->assertTrue( $preview['is_pro'] );
        $this->assertFalse( $preview['enabled'] );
        $this->assertStringNotContainsString( 'action=', $preview['url'] );
        $this->assertSame( 'ecommerce', $preview['category'] );
    }

    public function test_every_bundled_template_image_exists() {
        foreach ( wpuf_get_post_form_templates() as $key => $template ) {
            $path = str_replace( WPUF_ASSET_URI, WPUF_ROOT . '/assets', (string) $template->image );

            if ( 0 !== strpos( $path, WPUF_ROOT ) ) {
                continue; // Pro keeps its own images.
            }

            $this->assertFileExists( $path, $key );
        }
    }
}
