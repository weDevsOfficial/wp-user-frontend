<?php
$changelog = array(
    array(
        'version'  => 'Version 2.7.0',
        'released' => '2017-12-03',
        'changes' => array(
            array(
                'title'       => 'Interactive Settings Page to Manage it All',
                'type'        => 'New',
                'description' => 'WP User Frontend now has better and improved settings page where you can easily configure everything for your WP User Frontend.
                <img src="'. WPUF_ASSET_URI .'/images/whats-new/settings.png" alt="WP User Frontend Settings">'
            ),
            array(
                'title'       => 'Fallback cost for form subscription payment',
                'type'        => 'New',
                'description' => 'When a subscribed user reaches post limit before the pack expires, this option will allow user to pay per post and continue making posts until the membership is valid.
                <br><iframe width="100%" height="400" src="https://www.youtube.com/embed/-Z_uNM8eB_Y" frameborder="0" gesture="media" allow="encrypted-media" allowfullscreen></iframe>'
            ),
            array(
                'title'       => 'Integrated WP User Frontend with Dokan',
                'type'        => 'New',
                'description' => 'Admin can allow vendors to create and publish posts on the marketplace from the vendor dashboard. Admin can also select the form that vendor will use. Vendors will be able to make use of all the WPUF settings in this form.
                <br><iframe width="100%" height="400" src="https://www.youtube.com/embed/6n6CtGjTCF4" frameborder="0" gesture="media" allow="encrypted-media" allowfullscreen></iframe>'
            ),
        )
    )
);

function _wpuf_changelog_content( $content ) {
    $content = wpautop( $content, true );

    return $content;
}
?>

<div class="wrap wpuf-whats-new">
    <h1><?php _e( 'What\'s New in WPUF?', 'wpuf' ); ?></h1>

    <div class="wedevs-changelog-wrapper">

        <?php foreach ( $changelog as $release ) { ?>
            <div class="wedevs-changelog">
                <div class="wedevs-changelog-version">
                    <h3><?php echo esc_html( $release['version'] ); ?></h3>
                    <p class="released">
                        (<?php echo human_time_diff( time(), strtotime( $release['released'] ) ); ?> ago)
                    </p>
                </div>
                <div class="wedevs-changelog-history">
                    <ul>
                        <?php foreach ( $release['changes'] as $change ) { ?>
                            <li>
                                <h4>
                                    <span class="title"><?php echo esc_html( $change['title'] ); ?></span>
                                    <span class="label <?php echo strtolower( $change['type'] ); ?>"><?php echo esc_html( $change['type'] ); ?></span>
                                </h4>

                                <div class="description">
                                    <?php echo _wpuf_changelog_content( $change['description'] ); ?>
                                </div>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        <?php } ?>
    </div>

</div>
