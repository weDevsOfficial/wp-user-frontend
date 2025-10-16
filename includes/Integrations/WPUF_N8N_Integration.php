<?php

namespace WeDevs\Wpuf\Integrations;

if ( ! class_exists( 'WeDevs\Wpuf\Integrations\WPUF_N8N_Integration' ) ) {
    /**
     * WPUF N8N Integration Class
     *
     * @since WPUF_PRO_SINCE
     */
    class WPUF_N8N_Integration {

        public function __construct() {
            add_filter( 'wpuf_settings_sections', [ $this, 'add_n8n_settings_section' ] );
            add_filter( 'wpuf_settings_fields', [ $this, 'add_n8n_settings_fields' ], 20 );
        }

        /**
         * Add N8N settings section
         *
         * @since WPUF_PRO_SINCE
         *
         * @param array $sections Existing sections
         *
         * @return array Modified sections
         */
        public function add_n8n_settings_section( $sections ) {
            $sections['n8n'] = [
                'title' => __( 'N8N', 'wp-user-frontend' ),
                'id'    => 'n8n',
                'icon'  => 'dashicons-money',
            ];

            return $sections;
        }

        public function add_n8n_settings_fields( $fields ) {
            $settings = [
                'n8n' => [
                    [
                        'name'    => 'authentication_type',
                        'label'   => __( 'Authentication Type', 'wp-user-frontend' ),
                        'desc'    => __(
                            'Select the authentication type for the N8N integration.',
                            'wp-user-frontend'
                        ),
                        'type'    => 'select',
                        'default' => 'none',
                        'options' => [
                            'none'        => __( 'None', 'wp-user-frontend' ),
                            'basic_auth'  => __( 'Basic Auth', 'wp-user-frontend' ),
                            'header_auth' => __( 'Header Auth', 'wp-user-frontend' ),
                            'jwt_auth'    => __( 'JWT Auth', 'wp-user-frontend' ),
                        ],
                    ],
                    [
                        'name'    => 'basic_auth_username',
                        'label'   => __( 'Basic Auth Username', 'wp-user-frontend' ),
                        'desc'    => __(
                            'Enter the username for the Basic Auth authentication.',
                            'wp-user-frontend'
                        ),
                        'type'    => 'text',
                        'depends_on' => 'authentication_type',
                        'depends_on_value' => 'basic_auth',
                    ],
                    [
                        'name'     => 'basic_auth_password',
                        'label'    => __( 'Basic Auth Password', 'wp-user-frontend' ),
                        'desc'     => __(
                            'Enter the password for the Basic Auth authentication.',
                            'wp-user-frontend'
                        ),
                        'type'     => 'text',
                        'callback' => 'wpuf_settings_password_preview',
                        'depends_on' => 'authentication_type',
                        'depends_on_value' => 'basic_auth',
                    ],
                    [
                        'name'    => 'header_auth_name',
                        'label'   => __( 'Name', 'wp-user-frontend' ),
                        'desc'    => __(
                            'Enter the name for the Header Auth authentication.',
                            'wp-user-frontend'
                        ),
                        'type'    => 'text',
                        'depends_on' => 'authentication_type',
                        'depends_on_value' => 'header_auth',
                    ],
                    [
                        'name'     => 'header_auth_value',
                        'label'    => __( 'Header Auth Value', 'wp-user-frontend' ),
                        'desc'     => __(
                            'Enter the value for the Header Auth authentication.',
                            'wp-user-frontend'
                            ),
                        'type'     => 'text',
                        'callback' => 'wpuf_settings_password_preview',
                        'depends_on' => 'authentication_type',
                        'depends_on_value' => 'header_auth',
                    ],
                    [
                        'name'    => 'jwt_key_type',
                        'label'   => __( 'JWT Key Type', 'wp-user-frontend' ),
                        'desc'    => __(
                            'Select the type of JWT key for the JWT Auth authentication.',
                            'wp-user-frontend'
                        ),
                        'type'    => 'select',
                        'default' => 'passphrase',
                        'options' => [
                            'passphrase'  => __( 'Passphrase', 'wp-user-frontend' ),
                            'pem_key'     => __( 'PEM Key', 'wp-user-frontend' ),
                        ],
                        'depends_on' => 'authentication_type',
                        'depends_on_value' => 'jwt_auth',
                    ],
                    [
                        'name'     => 'jwt_key_passphrase',
                        'label'    => __( 'JWT Key Passphrase Secret', 'wp-user-frontend' ),
                        'desc'     => __(
                            'Enter the passphrase for the JWT Auth authentication.',
                            'wp-user-frontend'
                        ),
                        'type'     => 'text',
                        'callback' => 'wpuf_settings_password_preview',
                        'depends_on' => [
                            'authentication_type' => 'jwt_auth',
                            'jwt_key_type' => 'passphrase'
                        ],
                    ],
                    [
                        'name'     => 'jwt_key_pem_key',
                        'label'    => __( 'JWT Key PEM Key', 'wp-user-frontend' ),
                        'desc'     => __(
                            'Enter the PEM key for the JWT Auth authentication.',
                            'wp-user-frontend'
                        ),
                        'type'     => 'text',
                        'callback' => 'wpuf_settings_password_preview',
                        'depends_on' => [
                            'authentication_type' => 'jwt_auth',
                            'jwt_key_type' => 'pem_key'
                        ],
                    ],
                ],
            ];

            return array_merge( $fields, $settings );
        }
    }
}
