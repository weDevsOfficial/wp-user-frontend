<?php
$changelog = array(
    array(
        'version'  => 'Version 2.7.0',
        'released' => '2017-12-03',
        'changes' => array(
            array(
                'title'       => 'Fallback cost for form subscription payment',
                'type'        => 'New',
                'description' => 'When a subscribed user reaches post limit before the pack expires, this option will allow user to pay per post and continue making posts until the membership is valid.
                <br><iframe width="100%" height="400" src="https://www.youtube.com/embed/cS6O7U5h-Nk" frameborder="0" allowfullscreen></iframe>'
            ),
            array(
                'title'       => 'Integration WPUF with Dokan',
                'type'        => 'New',
                'description' => 'Admins can allow vendors to create and publish posts on the marketplace from the vendor dashboard. Admins can also select the form that vendor will use. Vendors will be able to make use of all the WPUF settings in this form.
                <br><iframe width="100%" height="400" src="https://www.youtube.com/embed/Joie3j3aqcM" frameborder="0" allowfullscreen></iframe>'
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
