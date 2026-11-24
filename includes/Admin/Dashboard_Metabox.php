<?php

namespace WeDevs\Wpuf\Admin;

use DOMDocument;

class Dashboard_Metabox {
    const URL = 'https://wedevs.com/category/user-frontend-pro';
    const FEED_URL = 'https://wedevs.com/rss.xml';
    const FEED_CATEGORIES = [ 'WP User Frontend', 'WP User Frontend Pro' ];
    const OPT_KEY = 'wpuf_admin_db_mb';
    const SURVEY_KEY = 'wpuf_dashboard_survey';

    protected $banner;
    protected $message;

    public function __construct() {
        define( 'BANNER', WPUF_ASSET_URI . '/images/wpuf-updates.png' );
        $default_message = '<p>Could you please take a moment and <a
                        href="https://wordpress.org/support/plugin/wp-user-frontend/reviews/?filter=5" target="_blank">share
                        your
                        opinion</a> on WP.org? It would motivate us a lot and help other users get decisive while
                    choosing WP User Frontend. Thanks in advance.
                </p>';

        $survey = get_transient( self::SURVEY_KEY );

        if ( false === $survey ) {
            $survey_url = 'https://raw.githubusercontent.com/weDevsOfficial/wpuf-util/master/survey.json';
            $response   = wp_remote_get( $survey_url, [ 'timeout' => 15 ] );
            $survey     = wp_remote_retrieve_body( $response );

            if ( is_wp_error( $response ) || 200 !== $response['response']['code'] ) {
                $survey = '[]';
            }

            set_transient( self::SURVEY_KEY, $survey, DAY_IN_SECONDS );
        }

        // Before decoding, fix invalid characters
        $survey = str_replace( '\’', "'", $survey );
        $survey = json_decode( $survey, true );

        $this->banner  = ! empty( $survey['banner_image'] ) ? $survey['banner_image'] : BANNER;
        $this->message = ! empty( $survey['message'] ) ? $survey['message'] : $default_message;

        add_action( 'wp_dashboard_setup', [ $this, 'add_metabox' ] );
    }

    public function add_metabox() {
        wp_add_dashboard_widget(
            self::OPT_KEY, esc_html__( 'WP User Frontend News & Updates', 'wp-user-frontend' ), [
				$this,
				'render_metabox',
			], null, null, 'normal', 'high'
        );
    }

    public function render_metabox() {
        ?>
        <style>
            .wpuf-db-banner > img {
                max-width: 100%;
            }

            .wpuf-divider-bottom {
                margin: 0 -12px;
                border-bottom: 1px solid #f0f0f1;
            }

            .wpuf-db-latest-blog {
                padding: 4px 0;
            }

            .wpuf-db-links ul {
                display: flex;
                margin-bottom: 0;
            }

            .wpuf-db-links .blog-item:not(:first-of-type) {
                margin-right: 4px;
                padding-left: 4px;
                border-left: 1px solid #ddd;
            }
        </style>
        <div class="wpuf-db-widget">
            <div class="wpuf-db-banner">
                <img src="<?php echo esc_url( $this->banner ); ?>" alt="Banner">
                <div style="margin-top: 1rem;">
                    <?php echo wp_kses_post( $this->message ); ?>
                </div>
            </div>
            <div class="wpuf-divider-bottom"></div>
            <div class="wpuf-db-latest-blog">
                <ul>
                    <?php
                    $articles = $this->fetch_articles();
                    foreach ( $articles as $article ) {
                        ?>
                        <li><a href="<?php echo esc_url( $article['href'] ); ?>"
                                target="_blank"><?php echo esc_html( $article['title'] ); ?></a></li>
                    <?php } ?>
                </ul>
            </div>
            <div class="wpuf-divider-bottom"></div>
            <div class="wpuf-db-links">
                <ul>
                    <?php
                    foreach ( $this->get_links() as $link ) {
                        if ( wpuf()->is_pro() && 'Go Pro' === $link['title'] ) {
                            continue;
                        }
                        ?>
                        <li class="blog-item">
                            <a href="<?php echo esc_url( $link['href'] ); ?>"
                                target="_blank"><?php esc_html( $link['title'], 'wp-user-frontend' ); ?>
                                <span aria-hidden="true" class="dashicons dashicons-external"></span></a>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
        <?php
    }

    private function fetch_articles() {
        $article_list = get_transient( self::OPT_KEY );

        // A cached empty list is a hit too, so a failed or empty fetch is not retried on every dashboard load.
        if ( is_array( $article_list ) ) {
            return $article_list;
        }

        $article_list = [];
        $response     = wp_remote_get( self::FEED_URL, [ 'timeout' => 5 ] );

        if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
            $article_list = $this->parse_feed( wp_remote_retrieve_body( $response ) );
        }

        set_transient( self::OPT_KEY, $article_list, $article_list ? DAY_IN_SECONDS : 6 * HOUR_IN_SECONDS );

        return $article_list;
    }

    /**
     * Get the latest WP User Frontend posts from the weDevs blog RSS feed
     *
     * @since WPUF_SINCE
     *
     * @param string $body RSS XML.
     *
     * @return array
     */
    private function parse_feed( $body ) {
        $dom      = new DOMDocument();
        $previous = libxml_use_internal_errors( true );
        $loaded   = $dom->loadXML( $body, LIBXML_NONET );

        libxml_clear_errors();
        libxml_use_internal_errors( $previous );

        if ( ! $loaded ) {
            return [];
        }

        $article_list = [];

        foreach ( $dom->getElementsByTagName( 'item' ) as $item ) {
            $categories = [];

            foreach ( $item->getElementsByTagName( 'category' ) as $category ) {
                $categories[] = trim( $category->textContent ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
            }

            if ( ! array_intersect( $categories, self::FEED_CATEGORIES ) ) {
                continue;
            }

            $title = $item->getElementsByTagName( 'title' )->item( 0 );
            $link  = $item->getElementsByTagName( 'link' )->item( 0 );

            if ( ! $title || ! $link ) {
                continue;
            }

            $article_list[] = [
                'title' => trim( $title->textContent ), // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
                'href'  => trim( $link->textContent ), // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
            ];

            if ( count( $article_list ) >= 5 ) {
                break;
            }
        }

        return $article_list;
    }

    private function get_links() {
        return [
            [
                'title' => 'Blog',
                'href'  => 'https://wedevs.com/category/user-frontend-pro',
            ],
            [
                'title' => 'Docs',
                'href'  => 'https://wedevs.com/docs/wp-user-frontend-pro/',
            ],
            [
                'title' => 'Help',
                'href'  => 'https://wedevs.com/contact',
            ],
            [
                'title' => 'Go Pro',
                'href'  => 'https://wedevs.com/wp-user-frontend-pro/pricing/',
            ],
            [
                'title' => 'Tutorials',
                'href'  => 'https://www.youtube.com/watch?v=rzxdIN8ZMYc&list=PLJorZsV2RVv9G5J3kcqJQjUwgqZSwc_Hf&index=2&ab_channel=weDevs',
            ],
            [
                'title' => 'Community',
                'href'  => 'https://www.facebook.com/groups/weDevs',
            ],
        ];
    }
}
