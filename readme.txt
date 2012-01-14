=== WP User Frontend ===
Contributors: tareq1988
Donate link: http://tareq.wedevs.com
Tags: frontend, post, edit, dashboard, restrict
Requires at least: 3.1
Tested up to: 3.3
Stable tag: 0.2

Create, update, delete posts and edit profile from wordpress frontend.

== Description ==

Some of us want something like that the subscriber/contributor will not be able to go in the
wordpress backend and everything these user can control will be done from wordpress frontend.

Features:

So here is my plugin that solves your problem. This features of this plugin in it’s version 0.1 are follows:

    * User can create a new post and edit from frontend
    * They can view their page in the custom dashboard
    * Users can edit their profile
    * Administrator can restrict any user level to access the wordpress backend (/wp-admin)
    * New posts status, submitted by users are configurable via admin panel. i.e. Published, Draft, Pending
    * Admin can configure to receive notification mail when the users creates a new post.
    * Configurable options if the user can edit or delete their posts.

== Installation ==

This section describes how to install the plugin and get it working.

1. Create a new Page “New Post” and insert shorcode `[wpuf_addpost]`
2. Create a new Page “Edit” for editing posts and insert shorcode `[wpuf_edit]`
3. Create a new Page “Profile” for editing profile and insert shorcode `[wpuf_editprofile]`
4. Create a new Page “Dashboard” and insert shorcode `[wpuf_dashboard]`
5. Correct the permalink structure of your wordpress installation
6. Insert the “Edit” Page url to the admin options page of the plugin
7. To show the subscription info, insert the shortcdoe `[wpuf_sub_info]`
8. To show the subscription packs, insert the shortcode `[wpuf_sub_pack]`
9. For subscription payment page, create a new page and insert the page ID in WP frontend CMS's "Paypal Payment Page" option.


== Screenshots ==
1. Admin panel
2. dashboard
3. Edit Posts
4. Edit Profile

== Frequently Asked Questions ==

= Can I create new posts from frontend =

Yes

= Can I Edit my posts from frontend =

Yes

= Can I delete my posts from frontend =

Yes

= Can I upload photo/image/video =
No


== Changelog ==

Nothing to say

== Upgrade Notice ==

Nothing to say
