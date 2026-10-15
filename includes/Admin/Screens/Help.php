<?php
/**
 * Help screen
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Admin\Screens;

use WeDevs\Wpuf\Admin\Help_Content;

/**
 * User Frontend > Help: the admin app route `#/help` (React app
 * src/admin/apps/help); the content comes from Admin\Help_Content. The old
 * `page=wpuf-support` URL redirects there.
 *
 * @since WPUF_SINCE
 */
class Help extends Screen {

    /**
     * Menu slug
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function slug() {
        return 'wpuf-support';
    }

    /**
     * Nothing to print: the old URL always redirects to the app route.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function render() {}

    /**
     * Admin app route.
     *
     * @since WPUF_SINCE
     *
     * @return array[]
     */
    public function app_routes() {
        return [
            [
                'id'             => 'help',
                'path'           => '/help',
                'title'          => __( 'Help', 'wp-user-frontend' ),
                'app'            => 'help',
                'boot'           => 'help',
                'in_app'         => true,
                'menuLink'       => true,
                'notices'        => true,
                'container'      => 'wpuf-help-root',
                'containerClass' => 'px-[20px]',
            ],
        ];
    }

    /**
     * The old page opens the route.
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function app_route_for_request() {
        return '/help';
    }

    /**
     * On the app page: the Help app and the admin pages sheet.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function load_in_app() {
        wp_enqueue_style( 'wpuf-admin-pages' );
        wp_enqueue_script( 'wpuf-help' );
    }

    /**
     * Window globals of the route (Admin\Help_Content).
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function app_globals() {
        $content  = new Help_Content();
        $articles = $content->articles();
        // Some source strings carry entities (`&amp;`); the app prints text.
        $text     = function ( $value ) {
            return html_entity_decode( wp_strip_all_tags( (string) $value ), ENT_QUOTES, 'UTF-8' );
        };
        $user     = wp_get_current_user();
        $topics   = [];

        foreach ( $content->topics() as $topic ) {
            $related = [];

            foreach ( $topic['articles'] && isset( $articles[ $topic['articles'] ] ) ? $articles[ $topic['articles'] ] : [] as $article ) {
                $related[] = [
                    'title' => $text( $article['title'] ),
                    'url'   => $content->article_url( $article ),
                ];
            }

            $topics[] = [
                'id'       => $topic['id'],
                'label'    => $text( $topic['label'] ),
                'icon'     => $topic['icon'],
                'title'    => $text( $topic['title'] ),
                'body'     => $content->body( $topic['id'] ),
                'button'   => [
                    'label' => $text( $topic['button']['label'] ),
                    'url'   => $topic['button']['url'],
                ],
                'articles' => $related,
            ];
        }

        return [
            'wpufHelp' => [
                'docsUrl'    => $content->docs_url(),
                'topics'     => $topics,
                'blocks'     => $content->blocks(),
                'newsletter' => array_merge(
                    $content->newsletter(),
                    [
                        'firstName' => $user->exists() ? $user->first_name : '',
                        'email'     => $user->exists() ? $user->user_email : '',
                    ]
                ),
            ],
        ];
    }
}
