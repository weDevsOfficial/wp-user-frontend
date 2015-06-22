# Installing the plugin


Installing the plugin is very simple as you normally do.

1. Go to your WordPress plugins area, click **Add New**
2. Locate your plugins Zip file and click install.

If you are using FTP to transfer your files to your server. Then just unzip the plugin and upload `wp-user-frontend-pro` directory to your `/wp-content/plugins/` directory.

# Usage:
## Creating Post forms

Creating post forms is easy.

1. Go to the first menu **Forms** from the top level menu in the sidebar **User Frontend**.
2. You can create new forms just you would create posts in WordPress.
3. Give your form a name and click on **Form Elements** on the right sidebar.
4. The form elements will be appeared to the **Form Editor** tab with some options.

# Form Elements

 1. **Required**: If you want to make this input area a required field, select *yes* or *no* if you don't.
 2. **Field Label:** Give your element a name. e.g. If it's a post title, may be give the name ***Post Title***
 3. **Help Text:** If you want to give some help assistance to your user about this input type, type some texts. May be add some texts about what do you expect from the user as a input.
 4. **CSS Class Name:** If you want to add a *CSS* class to your form element, you can add a name. This class name will be added to the `<li>` of the input element.
 5. **Placeholder Text:** A nice new feature of HTML5, you can give your users a assistive text about the input field.
 6. **Default Value:** If you want the form input should be auto populated with default value, you can add that here.
 7. **Size:** The width of a input text element.
 
## Showing forms in a page

After you create a form, you get a shortcode like this: `[wpuf_form id="2541"]`, inserting this shortcode to a page will show the form

# User Dashboard

A user can see all his posts in his dashboard. To show the dashboard, create a page, for example **Dashboard** and insert the shortcode `[wpuf_dashboard]`. All the `post` post type will be shown for the logged in user.

If you want to display a specific post type, you need to mention that. For example, if you want list `event` instead of `post`, you can use `[wpuf_dashboard post_type="event"]`.

To configure how many posts will be displayed in a page, you can configure the settings at `Settings > Dashboard > Posts per page`. There are other settings also for dashboard.

* You can show the user bio in dashboard
* Show the *count* of posts.
* Show featured image and set the size to display.

# Post Editing

A user can edit his/her posts from frontend. To setup the options, follow the instructions:

1. Create a page, for example: **Edit**.
2. Insert the shortcode `[wpuf_edit]` in the page.
3. Go to plugin **Settings** > **General Options** > **Edit Page**. Select the page your just created which contains the shortcode `[wpuf_edit]`


# Developer Documentation

There are some **actions** and **filters** provided for developers to extend the functionalities.

## 1. Action Hook Field

There is an element on the ***Form Elements*** called `Action Hook`. This is a great addition for developers to extend the form as they want. It's a *placeholder*  for creating their own hook where they could bind their functions and generate their own dynamic element.

Usage:
add_action('HOOK_NAME', 'your_function_name', 10, 3 );
function your_function_name( $form_id, $post_id, $form_settings ) {
    // do what ever you want
}

**Parameters:**

* $form_id : *(integer)* The ID of the form
* $post_id : *NULL* or *(integer)* the ID of the post. When creating a new post, the parameter becomes *NULL*. When editing a post, you get the edited post ID as the parameter
* $form_settings : *(array)* An array of form settings

## 2. Actions

***WP User Frontend*** comes with some actions by default.

### Post Add
=================

* **wpuf_add_post_form_top**
	* Called at top of the form. 2 parameters: **$form_id** *(integer)*, **$form_settings** *(array)*
* **wpuf_add_post_form_bottom**
 	* Called at bottom of the form. Accepts 2 parameters. **$form_id** *(integer)*, **$form_settings** *(array)*
 
* **wpuf_add_post_after_insert** 
	* Runs after inserting a post and updating the meta tags. Accepts 4 parameters. **$post_id** *(integer)*, **$form_id** *(integer)*, **$form_settings** *(array)*, **$form_vars** *(array)*;

### Post Edit
======================

* **wpuf_edit_post_form_top**
	* Called at top of the form. 2 parameters: **$form_id** *(integer)*, **$form_settings** *(array)*
* **wpuf_edit_post_form_bottom**
 	* Called at bottom of the form. Accepts 2 parameters. **$form_id** *(integer)*, **$form_settings** *(array)*
 
* **wpuf_edit_post_after_update** 
	* Runs after updating a post and updating the meta tags. Accepts 4 parameters. **$post_id** *(integer)*, **$form_id** *(integer)*, **$form_settings** *(array)*, **$form_vars** *(array)*;


### Dashboard
===============

* **wpuf_dashboard_top**
	* Called at top of the dashboard listing. Accpets 2 parameters. **$user_id**, **$post_type**
* **wpuf_dashboard_nopost**
	* Called when no post found. Accpets 2 parameters. **$user_id**, **$post_type**;
* **wpuf_dashboard_bottom**
	* Called at bottom of the dashboard listing. Accpets 2 parameters. **$user_id**, **$post_type**

### Admin Panel
===============

* wpuf_admin_menu
* wpuf_post_form_tab
* wpuf_post_form_tab_content
* wpuf_profile_form_tab
* wpuf_profile_form_tab_content
* wpuf_form_buttons_post
* wpuf_form_buttons_custom
* wpuf_form_buttons_other
* wpuf_form_buttons_user


# FAQ


#### Q. I get the message: "I don't know how to edit this post, I don't have the form ID"

**Ans:** When you create a post with the new version of WP User Frontend, a form ID is being attached to that post. It's needed because you might have 10 forms and it doesn't know which form should be used to edit that post.

For this problem with older posts, one thing you need to do. If you go the those posts edit screen in backend, you'll see a meta box "WPUF Form", select the form that should be used to edit the form.

### Q. Where does the user registration and profile information saves?

**Ans:** It's just the standard user meta fields. They are saved with **meta_key** you provide. You can pull the informations by this: `<?php echo get_user_meta( $user_id, 'meta_key', true ); ?>`


#### Q. How do I show the images/files in my theme?

**Ans:** Just use this snippet:

`$images = get_post_meta( $post->ID, 'mey_key_name' );

if ($images) {
    foreach ($images as $attachment_id) {
        $thumb = wp_get_attachment_image( $attachment_id, 'thumbnail' );
        $full_size = wp_get_attachment_url( $attachment_id );

        printf( '<a href="%s">%s</a>', $full_size, $thumb );
    }
}`

#### Q. Why the pay per post and subscription package removed from personal package?

**Ans:** It's already too much options to manage. And providing the support is fairly complex for the subscription and payment gateway. So it's been **removed** from the personal and business package and added to the **developer** package.

#### Q. What if I buy the personal package and use it on unlimited site?

**Ans:** You can do that, we'll not restrict you to use it on several sites. But depending on the package you purchase, you are given a license code. The license code is neccessary for to receive auto updates and support. If you use it on multiple sites, you'll **not receive** any further updates and support.

#### Q. Whats the facility of the develooper package.

**Ans:** Using the developer package, you can use it on unlimited sites, priority support and free add-ons.