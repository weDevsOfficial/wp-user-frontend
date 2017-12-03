<?php
$changelog = array(
    array(
        'version'  => 'Version 2.7.0',
        'released' => '2017-12-03',
        'changes' => array(
            array(
                'title'       => 'Introducing All New modules and packaging System',
                'type'        => 'New',
                'description' => 'Say bye bye to previous add-ons, which were very difficult to manage. From our new update, we are going to transform all our add-ons into modules. Guess what, you will be able to manage all of them from a single place. So, we have added a new menu called ‘Modules’ and removed the old ‘Add-ons’ menu. This is how the new page looks like.
                <img src="'. WPUF_ASSET_URI .'/images/whats-new/module-activation.gif" alt="WP User Frontend Module">'
            ),
            array(
                'title'       => 'Interactive Settings Page to Manage it All',
                'type'        => 'New',
                'description' => 'WP User Frontend now has better and improved settings page where you can easily configure everything for your WP User Frontend.
                <img src="'. WPUF_ASSET_URI .'/images/whats-new/settings.png" alt="WP User Frontend Settings">'
            ),
            array(
                'title'       => 'User Listing Module Improvements',
                'type'        => 'New',
                'description' => 'Admins can now select among different design layouts for all user profiles as well as user listings.
                <br><iframe width="100%" height="400" src="https://www.youtube.com/embed/KklvoCCivYs" frameborder="0" allowfullscreen></iframe>'
            ),
            array(
                'title'       => 'Social Login with Facebook, Twitter, LinkedIn and Google.',
                'type'        => 'New',
                'description' => 'Users can now login as well as register with these 4 social profiles.
                <br><iframe width="100%" height="400" src="https://www.youtube.com/embed/a_zvS00Uxb8" frameborder="0" allowfullscreen></iframe>'
            ),
            array(
                'title'       => 'Fallback cost for form subscription payment',
                'type'        => 'New',
                'description' => 'When a subscribed user reaches post limit before the pack expires, this option will allow user to pay per post and continue making posts until the membership is valid.
                <br><iframe width="100%" height="400" src="https://www.youtube.com/embed/cS6O7U5h-Nk" frameborder="0" allowfullscreen></iframe>'
            ),
            array(
                'title'       => 'Taxonomy restriction when creating a post',
                'type'        => 'New',
                'description' => 'Admins can now define the categories for each subscription pack in which a subscribed user can create posts.
                <br><iframe width="100%" height="400" src="https://www.youtube.com/embed/Tr9sFmTwauc" frameborder="0" allowfullscreen></iframe>'
            ),
            array(
                'title'       => 'WC Vendors Registration Template',
                'type'        => 'New',
                'description' => 'Vendors registering in WC Vendors with this form will automatically get assigned as vendors. Admins can add extra fields and customize the template as desired.
                <br><iframe width="100%" height="400" src="https://www.youtube.com/embed/gVuB3f8daOw" frameborder="0" allowfullscreen></iframe>'
            ),
            array(
                'title'       => 'Dokan Vendor Registration Template',
                'type'        => 'New',
                'description' => 'Vendors registering in Dokan Multivendor with this form will automatically get assigned as vendors. Admins can add extra fields and customize the template as desired.
                <br><iframe width="100%" height="400" src="https://www.youtube.com/embed/gSdLGtVuIYo" frameborder="0" allowfullscreen></iframe>'
            ),
            array(
                'title'       => 'WC Marketplace Registration Template',
                'type'        => 'New',
                'description' => 'Vendors registering in WC Marketplace with this form will automatically get assigned as vendors. Admins can add extra fields and customize the template as desired.
                <br><iframe width="100%" height="400" src="https://www.youtube.com/embed/95OtuSY2JuE" frameborder="0" allowfullscreen></iframe>'
            ),
            array(
                'title'       => 'Integration WPUF with Dokan',
                'type'        => 'New',
                'description' => 'Admins can allow vendors to create and publish posts on the marketplace from the vendor dashboard. Admins can also select the form that vendor will use. Vendors will be able to make use of all the WPUF settings in this form.
                <br><iframe width="100%" height="400" src="https://www.youtube.com/embed/Joie3j3aqcM" frameborder="0" allowfullscreen></iframe>'
            ),
            array(
                'title'       => 'Automatic Updates for Modules',
                'type'        => 'New',
                'description' => 'Previously, you didn’t get a live update for any of the WP User Frontend add-ons. Now, you can manage them from a single place as well as get live updates directly with WP User Frontend plugin. So, no more manual updates! You don’t have to download each add-ons and install them separately every time you get an update.'
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
    <h1>What's New in WPUF?</h1>

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
<?php
    $offer_key = 'wpuf_whats_new_notice';
    update_option( $offer_key . '_tracking_notice', 'hide' );
?>
<style type="text/css">
.wpuf-whats-new h1{
    text-align: center;
}

.error, .udpated, .info, .notice {
    display: none;
}

.WP User Frontend-whats-new h1 {
    text-align: center;
    margin-top: 20px;
    font-size: 30px;
}

.wedevs-changelog {
    display: flex;
    max-width: 920px;
    border: 1px solid #e5e5e5;
    padding: 12px 20px 20px 20px;
    margin: 20px auto;
    background: #fff;
    box-shadow: 0 1px 1px rgba(0,0,0,0.04);
}

.wedevs-changelog-wrapper .wedevs-support-help {

}

.wedevs-changelog .wedevs-changelog-version {
    width: 360px;
}

.wedevs-changelog .wedevs-changelog-version .released {
    font-style: italic;
}

.wedevs-changelog .wedevs-changelog-history {
    width: 100%;
    font-size: 14px;
}

.wedevs-changelog .wedevs-changelog-history li {
    margin-bottom: 30px;
}

.wedevs-changelog .wedevs-changelog-history h4 {
    margin: 0 0 10px 0;
    font-size: 1.3em;
    line-height: 26px;
}

.wedevs-changelog .wedevs-changelog-history p {
    font-size: 14px;
    line-height: 1.5;
}

.wedevs-changelog .wedevs-changelog-history img {
    margin-top: 30px;
    max-width: 100%;
}

.wedevs-changelog-history span.label {
    margin-left: 10px;
    position: relative;
    color: #fff;
    border-radius: 20px;
    padding: 0 8px;
    font-size: 12px;
    height: 20px;
    line-height: 19px;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    font-weight: normal;
}

span.label.new {
    background: #3778ff;
    border: 1px solid #3778ff;
}

span.label.improvement {
    background: #3aaa55;
    border: 1px solid #3aaa
}

span.label.fix {
    background: #ff4772;
    border: 1px solid #ff4772;
}

</style>