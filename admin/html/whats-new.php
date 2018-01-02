<?php
$changelog = array(
    array(
        'version'  => 'Version 2.8',
        'released' => '2018-01-02',
        'changes' => array(
            array(
                'title'       => 'Manage schedule for form submission',
                'type'        => 'New',
                'description' => 'Do not accept form submission if the current date is not between the date range of the schedule.
                <img src="'. WPUF_ASSET_URI .'/images/whats-new/schedule.png" alt="Manage schedule for form submission">'
            ),
            array(
                'title'       => 'Restrict form submission based on the user roles',
                'type'        => 'New',
                'description' => 'Restrict form submission based on the user roles. Now you can manage user role base permission on form submission.
                <img src="'. WPUF_ASSET_URI .'/images/whats-new/role-base.png" alt="Restrict form submission based on the users role">'
            ),
            array(
                'title'       => 'Limit how many entries a form will accept',
                'type'        => 'New',
                'description' => 'Limit how many entries a form will accept and display a custom message when that limit is reached.
                <img src="'. WPUF_ASSET_URI .'/images/whats-new/limit.png" alt="Limit how many entries a form will accept">'
            ),
            array(
                'title'       => 'Show/hide Admin Bar',
                'type'        => 'New',
                'description' => 'Control the admin bar visibility based on user roles.
                <img src="'. WPUF_ASSET_URI .'/images/whats-new/admin-bar.png" alt="Show/hide Admin Bar">'
            ),
            array(
                'title'       => 'Ajax Login widget',
                'type'        => 'New',
                'description' => 'Login user is more simple now with Ajax Login Widget. The simple login form do not required page loding for login.
                <br><iframe width="100%" height="400" src="https://www.youtube.com/embed/6n6CtGjTCF4" frameborder="0" gesture="media" allow="encrypted-media" allowfullscreen></iframe>'
            ),
            array(
                'title'       => 'Form submission with Captcha field',
                'type'        => 'Improvement',
                'description' => 'Form field validation process updated if form submits with captcha field.'
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
