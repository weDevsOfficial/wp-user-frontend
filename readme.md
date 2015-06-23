# WP User Frontend #
**Contributors:** tareq1988, wedevs

**Donate link:** http://tareq.wedevs.com/donate/

**Tags:** frontend, post, edit, dashboard, restrict, content submission, guest post, guest, dashboard, registration, profile, anonymous post, gravity, gravity forms, formidable

**Requires at least:** 3.3

**Tested up to:** 4.2.2

**Stable tag:** trunk


Create, update, delete posts and edit profile directly from the WordPress frontend.

## Description ##

This plugin gives the user the ability to create new posts, edit their profile all from the site frontend, so the user does not need to enter the backend admin panel to do these things.

### Features:  ###

* The user can create a new post and edit it from the frontend
* They can view their page in the frontend custom dashboard
* Users can edit their profile
* Administrator can restrict any user level on accessing the WordPress backend
* New posts status, submitted by users are configurable via admin panel. i.e. Published, Draft, Pending
* Get email notification on new posts
* Configurable options giving access to the user edit or delete their posts
* Upload attachments from the frontend
* Upload post featured image
* Admins can manage users from frontend
* Pay-per-post or subscription package for posting

### WP User Frontend PRO - Premium Features ###

The <a href="http://wedevs.com/plugin/wp-user-frontend-pro">premium version</a> of WP User Frontend is completely different from the free version as there are a lot more features included.

[youtube http://www.youtube.com/watch?v=C0sInxx49Vg]


* <a href="http://wedevs.com/plugin/wp-user-frontend-pro/#unlimited-forms">Unlimited post type form creation</a>
* <a href="http://wedevs.com/plugin/wp-user-frontend-pro/#post-forms">Drag-n-drop form builder</a>
* <a href="http://wedevs.com/plugin/wp-user-frontend-pro/#custom-taxonomy">Custom taxonomy support</a>
* <a href="http://wedevs.com/plugin/wp-user-frontend-pro/#custom-fields">13 variations of custom fields</a>
* <a href="http://wedevs.com/plugin/wp-user-frontend-pro/#guest-posting">Guest post support</a>
* <a href="http://wedevs.com/plugin/wp-user-frontend-pro/#custom-redirection">Custom Redirection</a>
* <a href="http://wedevs.com/plugin/wp-user-frontend-pro/#image-upload-post">Image upload on post content area</a>
* Post status selection on new post and edited post separately
* <a href="http://wedevs.com/plugin/wp-user-frontend-pro/#post-notification">New or edit post notification</a>
* <a href="http://wedevs.com/plugin/wp-user-frontend-pro/#custom-field-admin">Custom fields are also generated in admin area</a>
* <a href="http://wedevs.com/plugin/wp-user-frontend-pro/#profile-builder">Profile form builder</a>
* Different profile edit forms for different user roles
* Drag-n-Drop profile form builder
* Profile fields are also generated on the backend
* Avatar Upload
* Frontend profile edit
* <strong>Registration form</strong> builder
* <a href="http://wedevs.com/plugin/wp-user-frontend-pro/#captcha">Captcha Support</a>

Try an <a href="http://demo.wedevs.com/wpuf/wp-admin/">online demo</a> of the Pro version.

### Translation ###

* Italian translation by Gabriele Lamberti

[Github Repository](https://github.com/tareq1988/WP-User-Frontend).

## Installation ##

After having installed the plugin:

1. Click "Install WPUF Pages" after installation for automatic settings installation.
1. Create a form from the form builder. Get the shortcode for a form. Copy and paste that shortcode to a page.
1. Create a new Page “Edit” for editing posts and insert shortcode `[wpuf_edit]`
1. Create a new Page “Profile” for editing profile and insert shortcode `[wpuf_editprofile]`
1. Create a new Page “Dashboard” and insert shortcode `[wpuf_dashboard]`
    To list custom post type **event**, use `[wpuf_dashboard post_type="event"]`
1. Set the *Edit Page* option from *Others* tab on settings page.
1. To show the subscription info, insert the shortcdoe `[wpuf_sub_info]`
1. To show the subscription packs, insert the shortcode `[wpuf_sub_pack]`
1. For subscription payment page, set the *Payment Page* from *Payments* tab on settings page.


## Screenshots ##

###1. Admin panel
###
![Admin panel
](https://ps.w.org/wp-user-frontend/assets/screenshot-1.png)

###2. Admin panel &rarr; Dashboard Tab
###
![Admin panel &rarr; Dashboard Tab
](https://ps.w.org/wp-user-frontend/assets/screenshot-2.png)

###3. Admin panel &rarr; Login Tab
###
![Admin panel &rarr; Login Tab
](https://ps.w.org/wp-user-frontend/assets/screenshot-3.png)

###4. Admin panel &rarr; Payments Tab
###
![Admin panel &rarr; Payments Tab
](https://ps.w.org/wp-user-frontend/assets/screenshot-4.png)

###5. Post Forms
###
![Post Forms
](https://ps.w.org/wp-user-frontend/assets/screenshot-5.png)

###6. Form Builder
###
![Form Builder
](https://ps.w.org/wp-user-frontend/assets/screenshot-6.png)

###7. Form Builder &rarr; Post Settings
###
![Form Builder &rarr; Post Settings
](https://ps.w.org/wp-user-frontend/assets/screenshot-7.png)

###8. Form Builder &rarr; Edit Settings
###
![Form Builder &rarr; Edit Settings
](https://ps.w.org/wp-user-frontend/assets/screenshot-8.png)

###9. Form Elements
###
![Form Elements
](https://ps.w.org/wp-user-frontend/assets/screenshot-9.png)

###10. Subscription Packs
###
![Subscription Packs
](https://ps.w.org/wp-user-frontend/assets/screenshot-10.png)

###11. Subscription Pack Settings
###
![Subscription Pack Settings
](https://ps.w.org/wp-user-frontend/assets/screenshot-11.png)

###12. Subscription Packs in a Page
###
![Subscription Packs in a Page
](https://ps.w.org/wp-user-frontend/assets/screenshot-12.png)

###13. Subscription Payment Screen
###
![Subscription Payment Screen
](https://ps.w.org/wp-user-frontend/assets/screenshot-13.png)

###14. A single Form Element on Form Editor
###
![A single Form Element on Form Editor
](https://ps.w.org/wp-user-frontend/assets/screenshot-14.png)

###15. A Form in a Page
###
![A Form in a Page
](https://ps.w.org/wp-user-frontend/assets/screenshot-15.png)

###16. Frontend User Dashboard
###
![Frontend User Dashboard
](https://ps.w.org/wp-user-frontend/assets/screenshot-16.png)


## Frequently Asked Questions ##

### Can I create new posts from frontend ###

Yes

### Can I Edit my posts from frontend ###

Yes

### Can I delete my posts from frontend ###

Yes

### Can I upload photo/image/video ###
Yes

### I am having problem with uploading files ###
Please check if you've specified the max upload size on setting

### Why "Edit Post" page shows "invalid post id"? ###
This page is for the purpose of editing posts. You shouldn't access this page directly.
First you need to go to the dashboard, then when you click "edit", you'll be
redirected to the edit page with that post id. Then you'll see the edit post form.


## Changelog ##

### version 2.3.2 ###

 * [fix] Featured image upload fix
 * [new] Image upload field brought back to free

### version 2.3.1 ###

 * [fix] Compatibility problem with PHP < 5.2. Accidental PHP array shorthand used.

### version 2.3 ###

 * Pro plugin released as free with less features

### version 1.3.2 ###

 * [improve] post thumbnail image association added
 * [improve] various form styles updated
 * [fix] teeny textarea buttons fix
 * [fix] Dashboard show post type settings won't effect
 * [fix] zxcvbn is not defined in edit profile
 * [fix] Two click needed to submit a post
 * [fix] dashboard author bio height fix

### version 1.3.1 ###

 * [fix] `[wpuf_editpost]` typo fix
 * [fix] clean $dashboard_query from corrupting beyond use

### version 1.3 ###

 * [fix] PayPal payment user_id issue fixed
 * [fix] Plupload `o is null` error fix
 * [fix] PHP 5.4 strict warnings fix
 * [update] new version of settings api class

### version 1.2.3 ###

* [fix] `has_shortcode()` brought back again by renaming as `wpuf_has_shortcode()`
* [fix] all the labels now have a default text

### version 1.2.2 ###

* [fix] shortcode error fix for edit users
* [fix] plugin css/js url
* [fix] removed has_shortcode() call

### version 1.2.1 ###

* [fix] Performance problem with wp_list_users()

### version 1.2 ###

* [fix] Subscription post publish
* [fix] Post delete fix in dashboard
* [fix] Silverlight in IE upload error
* [fix] Category checklist bug fix
* [new] Checkbox field in custom field

### version 1.1 ###

* warning for multisite fix
* allow category bug fix
* fix ajaxurl in ajaxified category
* custom post type dropdown fix in admin
* post date bug fix
* category dropdown fix

### version 1.0 ###

* Admin panel converted to settings API
* Ajax featured Image uploader added (using plupload)
* Ajax attachment uploader added (using plupload)
* Rich/full/normal text editor mode
* Editor button fix on twentyelven theme
* Massive Code rewrite and cleanup
* Dashboard replaced with WordPress loop
* Output buffering added for header already sent warning
* Redirect user on deleting a post
* Category checklist added
* Post publish date fix and post expirator changed from hours to day
* Subscription and payment rewrite. Extra payment gateways can be added as plugin
* Other payment currency added

### version 0.7 ###

* admin ui improved
* updated new post notification mail template
* custom fields and attachment show/hide in posts
* post edit link override option
* ajax "posting..." changed
* attachment fields restriction in edit page
* localized ajaxurl and posting message
* improved action hooks and filter hooks

### version 0.6 ###

* fixed error on attachment delete
* added styles on dashboard too
* fixed custom field default dropdown
* fixed output buffering for add_post/edit_post/dashboard/profile pages
* admin panel scripts are added wp_enqueue_script instead of echo
* fixed admin panel block logic
* filter hook added on edit post for post args

### version 0.5 ###

* filters on add posting page for blocking the post capa
* subscription pack id added on user meta upon purchase
* filters on add posting page for blocking the post capa
* option for force pack purchase on add post. dropdown p
* subscription info on profile edit page
* post direction fix after payment
* filter added on form builder


### version 0.4 ###

* missing custom meta field added on edit post form
* jQuery validation added on edit post form

### version 0.3 ###

* rich/plain text on/off fixed
* ajax chained category added on add post form
* missing action added on edit post form
* stripslashes on admin/frontend meta field
* 404 error fix on add post

### version 0.2 ###

* Admin settings page has been improved
* Header already sent warning messages has been fixed
* Now you can add custom post meta from the settings page
* A new pay per post and subscription based posting options has been introduced (Only paypal is supported now)
* You can upload attachment with post
* WYSIWYG editor has been added
* You can add and manage your users from frontend now (only having the capability to edit_users )
* Some action and filters has been added for developers to add their custom form elements and validation
* Pagination added in post dashboard
* You can use the form to accept "custom post type" posts. e.g: [wpuf_addpost post_type="event"]. It also applies for showing post on dashboard like "[wpuf_dashboard post_type="event"]"
* Changing the form labels of the add post form is now possible from admin panel.
* The edit post page setting is changed from URL to page select dropdown.
* You can lock certain users from posting from their edit profile page.

## Upgrade Notice ##

### 2.3 ###
 * It's a **massive update and change** from the previous version 1.3.2. Please do test in your site and main compatibility.
