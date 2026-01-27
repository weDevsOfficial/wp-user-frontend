<?php

namespace WeDevs\Wpuf\Integrations;

use WeDevs\Wpuf\Traits\FieldableTrait;

if ( ! class_exists( 'WeDevs\Wpuf\Integrations\WPUF_N8N_Integration' ) ) {
    /**
     * WPUF N8N Integration Class
     *
     * @since WPUF_PRO_SINCE
     */
    class WPUF_N8N_Integration {

        use FieldableTrait;

        public function __construct() {
            add_filter( 'wpuf_settings_sections', [ $this, 'add_n8n_settings_section' ] );
            add_filter( 'wpuf_settings_fields', [ $this, 'add_n8n_settings_fields' ] );
            add_filter( 'wpuf_post_form_builder_setting_menu_titles', [ $this, 'add_n8n_menu_title' ] );
            add_filter( 'wpuf_post_form_builder_setting_menu_contents', [ $this, 'add_n8n_form_settings' ] );

            // Hook into post submission
            add_action( 'wpuf_add_post_after_insert', [ $this, 'send_post_to_n8n' ], 20, 4 );
        }

        /**
         * Send post to N8N
         *
         * @since WPUF_PRO_SINCE
         *
         * @param int $post_id
         * @param int $form_id
         * @param array $form_settings
         * @param array $meta_vars
         *
         * @retun void
         */
        public function send_post_to_n8n( $post_id, $form_id, $form_settings, $meta_vars ) {
            // Check if N8N integration is enabled for this form
            if ( empty( $form_settings['enable_n8n'] ) || ! wpuf_is_checkbox_or_toggle_on( $form_settings['enable_n8n'] ) ) {
                return;
            }

            // Get the webhook URL
            $n8n_webhook_url = isset( $form_settings['n8n_webhook_url'] ) ? $form_settings['n8n_webhook_url'] : '';
            if ( empty( $n8n_webhook_url ) ) {
                return;
            }

            // Sanitize the URL
            $n8n_webhook_url = esc_url_raw( $n8n_webhook_url );

            // Get the post data
            $post = get_post( $post_id );
            if ( ! $post ) {
                return;
            }

            // Prepare the data to send to N8N
            $post_data = [
                'post_id' => $post_id,
                'form_id' => $form_id,
            ];

            if ( ! empty( $post->post_title ) ) {
                $post_data['post_title'] = $post->post_title;
            }
            if ( ! empty( $post->post_content ) ) {
                $post_data['post_content'] = $post->post_content;
            }
            if ( ! empty( $post->post_excerpt ) ) {
                $post_data['post_excerpt'] = $post->post_excerpt;
            }
            if ( ! empty( $post->post_status ) ) {
                $post_data['post_status'] = $post->post_status;
            }
            if ( ! empty( $post->post_type ) ) {
                $post_data['post_type'] = $post->post_type;
            }
            if ( ! empty( $post->post_author ) ) {
                $post_data['post_author'] = $post->post_author;
            }
            if ( ! empty( $post->post_date ) ) {
                $post_data['post_date'] = $post->post_date;
            }

            [ $meta_key_value, $multi_repeated, $files ] = self::prepare_meta_fields( $meta_vars );

            foreach ( $meta_key_value as $meta_key => $meta_value ) {
                $post_data[$meta_key] = $meta_value;
            }

            // Get global N8N settings for authentication
            $n8n_settings = get_option( 'n8n', [] );

            // Prepare headers for authentication
            $headers = [
                'Content-Type' => 'application/json',
            ];

            // Add authentication based on global settings
            $auth_type = isset( $n8n_settings['authentication_type'] ) ? $n8n_settings['authentication_type'] : 'none';

            switch ( $auth_type ) {
                case 'basic_auth':
                    $username = isset( $n8n_settings['basic_auth_username'] ) ? $n8n_settings['basic_auth_username'] : '';
                    $password = isset( $n8n_settings['basic_auth_password'] ) ? $n8n_settings['basic_auth_password'] : '';
                    if ( ! empty( $username ) && ! empty( $password ) ) {
                        $headers['Authorization'] = 'Basic ' . base64_encode( $username . ':' . $password );
                    }
                    break;

                case 'header_auth':
                    $header_name = isset( $n8n_settings['header_auth_name'] ) ? $n8n_settings['header_auth_name'] : '';
                    $header_value = isset( $n8n_settings['header_auth_value'] ) ? $n8n_settings['header_auth_value'] : '';
                    if ( ! empty( $header_name ) && ! empty( $header_value ) ) {
                        $headers[ $header_name ] = $header_value;
                    }
                    break;

                case 'jwt_auth':
                    $jwt_key_type = isset( $n8n_settings['jwt_key_type'] ) ? $n8n_settings['jwt_key_type'] : 'passphrase';
                    $jwt_key = '';

                    if ( $jwt_key_type === 'passphrase' ) {
                        $jwt_key = isset( $n8n_settings['jwt_key_passphrase'] ) ? $n8n_settings['jwt_key_passphrase'] : '';
                    } elseif ( $jwt_key_type === 'pem_key' ) {
                        $jwt_key = isset( $n8n_settings['jwt_key_pem_key'] ) ? $n8n_settings['jwt_key_pem_key'] : '';
                    }

                    if ( ! empty( $jwt_key ) ) {
                        $jwt_token = $this->create_jwt_token( $jwt_key );
                        if ( $jwt_token ) {
                            $headers['Authorization'] = 'Bearer ' . $jwt_token;
                        }
                    }
                    break;
            }

            // Send data to N8N webhook
            $response = wp_remote_post(
                $n8n_webhook_url, [
                    'method'  => 'POST',
                    'timeout' => 30,
                    'headers' => $headers,
                    'body'    => wp_json_encode( $post_data ),
                ]
            );
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

        /**
         * Add N8N settings fields
         *
         * @since WPUF_PRO_SINCE
         *
         * @param array $fields
         *
         * @return array
         */
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
                        'label'    => __( 'Passphrase', 'wp-user-frontend' ),
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
                        'label'    => __( 'PEM Key', 'wp-user-frontend' ),
                        'desc'     => __(
                            'Enter the PEM key for the JWT Auth authentication.',
                            'wp-user-frontend'
                        ),
                        'type'     => 'text',
                        'callback' => 'wpuf_settings_password_preview',
                        'depends_on' => [
                            'authentication_type' => 'jwt_auth',
                            'jwt_key_type'        => 'pem_key',
                        ],
                    ],
                ],
            ];

            return array_merge( $fields, $settings );
        }

        /**
         * Add N8N to menu titles
         *
         * This adds "N8N" to the sidebar menu in form builder
         *
         * @since WPUF_PRO_SINCE
         *
         * @param array $settings
         * @return array
         */
        public function add_n8n_menu_title( $settings ) {
            // Create N8N menu item
            $n8n = [
                'n8n' => [
                    'label' => __( 'N8N', 'wp-user-frontend' ),
                    'icon'  => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="custom-stroke">
<path d="M5.38983 12.0847C5.38983 13.0208 4.63099 13.7797 3.69492 13.7797C2.75884 13.7797 2 13.0208 2 12.0847C2 11.1487 2.75884 10.3898 3.69492 10.3898C4.63099 10.3898 5.38983 11.1487 5.38983 12.0847ZM5.38983 12.0847H7.50847M10.8983 12.0847C10.8983 13.0208 10.1395 13.7797 9.20339 13.7797C8.26731 13.7797 7.50847 13.0208 7.50847 12.0847M10.8983 12.0847C10.8983 11.1487 10.1395 10.3898 9.20339 10.3898C8.26731 10.3898 7.50847 11.1487 7.50847 12.0847M10.8983 12.0847H12.2542M16.4068 15.3051C16.4068 16.2412 17.1656 17 18.1017 17C19.0378 17 19.7966 16.2412 19.7966 15.3051C19.7966 14.369 19.0378 13.6102 18.1017 13.6102C17.1656 13.6102 16.4068 14.369 16.4068 15.3051ZM16.4068 15.3051H15.4746C14.5853 15.3051 13.8644 14.5842 13.8644 13.6949C13.8644 12.8056 13.1435 12.0847 12.2542 12.0847M12.2542 12.0847C13.1435 12.0847 13.8644 11.3638 13.8644 10.4746C13.8644 9.5853 14.5853 8.86441 15.4746 8.86441H18.6102M22 8.69492C22 9.63099 21.2412 10.3898 20.3051 10.3898C19.369 10.3898 18.6102 9.63099 18.6102 8.69492C18.6102 7.75884 19.369 7 20.3051 7C21.2412 7 22 7.75884 22 8.69492Z" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
</svg>',
                ],
            ];

            return array_merge( $settings, $n8n );
        }

        /**
         * Add N8N form settings
         *
         * @since WPUF_PRO_SINCE
         *
         * @param array $settings
         *
         * @return array
         */
        public function add_n8n_form_settings( $settings ) {
            $settings['post_settings']['n8n'] = [
                'section' => [
                    'n8n_settings' => [
                        'label'  => __( 'N8N Integration', 'wp-user-frontend' ),
                        'desc'   => __(
                            'Configure N8N webhook integration to send user post data to your N8N workflows',
                            'wp-user-frontend'
                        ),
                        'fields' => [
                            'enable_n8n' => [
                                'label'     => __( 'Enable N8N Integration', 'wp-user-frontend' ),
                                'type'      => 'toggle',
                                'help_text' => __( 'Toggle N8N webhook integration on or off for this form', 'wp-user-frontend' ),
                                'default'   => 'off',
                            ],
                            'n8n_webhook_url' => [
                                'label'       => __( 'Webhook URL', 'wp-user-frontend' ),
                                'type'        => 'text',
                                'help_text'   => __( 'Enter the N8N webhook URL to send form data when posts are submitted', 'wp-user-frontend' ),
                                'placeholder' => __( 'https://your-n8n-instance.com/webhook/your-webhook-id', 'wp-user-frontend' ),
                            ],
                        ],
                    ],
                ],
            ];

            return $settings;
        }

        /**
         * Create JWT token for authentication
         *
         * @since WPUF_PRO_SINCE
         *
         * @param string $secret_key
         * @return string|false
         */
        private function create_jwt_token( $secret_key ) {
            // Simple JWT implementation - you might want to use a proper JWT library
            $header = json_encode( [
                'typ' => 'JWT',
                'alg' => 'HS256'
            ] );

            $payload = json_encode( [
                'iat' => time(),
                'exp' => time() + 3600, // 1 hour expiration
                'iss' => get_site_url(),
            ] );

            $base64_header = str_replace( ['+', '/', '='], ['-', '_', ''], base64_encode( $header ) );
            $base64_payload = str_replace( ['+', '/', '='], ['-', '_', ''], base64_encode( $payload ) );

            $signature = hash_hmac( 'sha256', $base64_header . '.' . $base64_payload, $secret_key, true );
            $base64_signature = str_replace( ['+', '/', '='], ['-', '_', ''], base64_encode( $signature ) );

            return $base64_header . '.' . $base64_payload . '.' . $base64_signature;
        }
    }
}
