<?php

class Dashboard_Metabox {
    const URL     = 'https://wedevs.com/category/user-frontend-pro';
    const OPT_KEY = 'wpuf_admin_db_mb';
    const BANNER  = WPUF_ASSET_URI . '/images/wpuf-updates.png';

    public function __construct() {
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
                <img src="<?php echo self::BANNER; ?>" alt="Rating Banner">
                <p>
                    Could you please take a moment and <a
                        href="https://wordpress.org/support/plugin/wp-user-frontend/reviews/?filter=5" target="_blank">share your
                        opinion</a> on WP.org? It would motivate us a lot and help other users get decisive while
                    choosing WP User Frontend. Thanks in advance.
                </p>
            </div>
            <div class="wpuf-divider-bottom"></div>
            <div class="wpuf-db-latest-blog">
                <ul>
                    <?php
                    $articles = $this->fetch_articles();
                    foreach ( $articles as $article ) {
                        ?>
                        <li><a href="<?php echo esc_url( $article['href'] ); ?>" target="_blank"><?php echo $article['title']; ?></a></li>
                    <?php }; ?>
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
                            <a href="<?php echo $link['href']; ?>"
                               target="_blank"><?php esc_html_e( $link['title'], 'wp-user-frontend' ); ?>
                                <span aria-hidden="true" class="dashicons dashicons-external"></span></a>
                        </li>
                    <?php }; ?>
                </ul>
            </div>
        </div>
        <?php
    }

    private function fetch_articles() {
        $article_list = get_transient( self::OPT_KEY );

        if ( $article_list ) {
            return $article_list;
        }

        $response = wp_remote_get( esc_url( self::URL ) );

        if ( is_wp_error( $response ) ) {
            return [];
        }

        $body = wp_remote_retrieve_body( $response );

        $dom = new DOMDocument();
        @$dom->loadHTML( $body );

        $finder = new DomXPath( $dom );

        $classname = 'post__title';
        $nodes     = $finder->query( "//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]" );

        if ( empty( $nodes ) ) {
            return [];
        }

        $url          = parse_url( self::URL );
        $domain       = $url['scheme'] . '://' . $url['host'];
        $article_list = [];
        $count        = 0;

        foreach ( $nodes as $node ) {
            $title = $node->nodeValue;
            $path  = $node->lastChild->attributes[0]->nodeValue;

            $article = [
                'title' => $title,
                'href'  => $domain . $path,
            ];

            array_push( $article_list, $article );

            $count ++;

            if ( $count >= 5 ) {
                break;
            }
        }

        if ( ! empty( $article_list ) ) {
            set_transient( self::OPT_KEY, $article_list, DAY_IN_SECONDS );

            return $article_list;
        }

        return [];
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
