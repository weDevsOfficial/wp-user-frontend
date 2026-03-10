<div class="wpuf-user-loggedin">

	<span class="wpuf-user-avatar">
		<?php echo get_avatar( $user->ID ); ?>
	</span>

    <br>
    <h3> <?php printf( esc_html( 
        // translators: %s is displayname
        __( 'Hello, %s', 'wp-user-frontend' ) ), esc_html( $user->display_name  ) ); ?> </h3>

    <?php printf( esc_html( 
        // translators: %s is replaced with the login/logout link 
        __( 'You are currently logged in! %s?', 'wp-user-frontend' ) ), wp_loginout( '', false )  ); ?>
</div>
