<div class="wrap">
    <h2><?php _e( 'WP User Frontend - Add-Ons', 'wpuf' ); ?></h2>

    <?php
    $add_ons = get_transient( 'wpuf_addons' );

    if ( false === $add_ons ) {
        $response = wp_remote_get( 'https://wedevs.com/api/wpuf/addons.php', array('timeout' => 15) );
        $update   = wp_remote_retrieve_body( $response );

        if ( is_wp_error( $response ) || $response['response']['code'] != 200 ) {
            return false;
        }

        set_transient( 'wpuf_addons', $update, 12 * HOUR_IN_SECONDS );
        $add_ons = $update;
    }

    $add_ons = json_decode( $add_ons );

    if ( count( $add_ons ) ) {
        ?>
        <div class="wp-list-table widefat plugin-install">

        <?php foreach ($add_ons as $addon) { ?>

            <div class="plugin-card">
                <div class="plugin-card-top">

                    <div class="name column-name">
                        <h3>
                            <a href="<?php echo $addon->url; ?>" target="_blank">
                                <?php echo $addon->title; ?>
                                <img class="plugin-icon" src="<?php echo $addon->thumbnail; ?>" alt="<?php echo esc_attr( $addon->title ); ?>" />
                            </a>
                        </h3>
                    </div>

                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <li>
                                <?php if ( class_exists( $addon->class ) ) { ?>
                                    <a class="button button-disabled" href="<?php echo $addon->url; ?>" target="_blank">Installed</a>
                                <?php } else { ?>
                                    <a class="button" href="<?php echo $addon->url; ?>" target="_blank">View Details</a>
                                <?php } ?>
                            </li>
                        </ul>
                    </div>

                    <div class="desc column-description">
                        <p>
                            <?php echo $addon->desc; ?>
                        </p>

                        <p class="authors">
                            <cite>By <a href="https://wedevs.com" target="_blank">weDevs</a></cite>
                        </p>
                    </div>
                </div>

                <div class="plugin-card-bottom">
                    <div class="column-updated">
                        <strong>Last Updated:</strong> 2 months ago
                    </div>

                    <div class="column-compatibility">
                        <span class="compatibility-compatible">
                            <strong>Compatible</strong> with your version of WordPress
                        </span>
                    </div>
                </div>
            </div>

        <?php } ?>

        </div>

        <?php
    } else {
        echo '<div class="error"><p>Error fetching add-ons. Please refresh the page again.</p></div>';
    }
    ?>

    <style type="text/css">
        .wp-list-table {
            margin-top: 25px;
        }
    </style>

</div>