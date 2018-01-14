<?php
$changelog = array(
    array(
        'version'  => 'Version 2.8.1',
        'released' => '2018-01-14',
        'changes' => array(
            array(
                'title'       => __( 'Setup Wizard', 'wpuf' ),
                'type'        => 'New',
                'description' => __( 'Setup Wizard added to turn off payment options and install pages.', 'wpuf' ) .
                '<img src="'. WPUF_ASSET_URI .'/images/whats-new/wizard.gif" alt="Setup Wizard">'
            ),
            array(
                'title'       => __( 'Multi-select Category', 'wpuf' ),
                'type'        => 'New',
                'description' => __( 'Add multi-select to default category in post form settings.', 'wpuf' ) .
                '<img src="'. WPUF_ASSET_URI .'/images/whats-new/category.png" alt="Multi-select Category">'
            ),
            array(
                'title'       => __( 'Select Text option for Taxonomy', 'wpuf' ),
                'type'        => 'Improvement',
                'description' => __( 'Add Select Text option for taxonomy fields. Now you can add default text with empty value as first option for Taxonomy dropdown.', 'wpuf' )
            ),
            array(
                'title'       => __( 'Taxonomy Checkbox Inline', 'wpuf' ),
                'type'        => 'Improvement',
                'description' => __( 'Added checkbox inline option to taxonomy checkbox. You can now display Taxonomy checkbox fields inline.', 'wpuf' )
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
