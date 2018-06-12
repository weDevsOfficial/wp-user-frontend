<div class="wpuf-user-loggedin">

	<span class="wpuf-user-avatar">
		<?php echo get_avatar( $user->ID ); ?>
	</span>

    <br>
    <h3> <?php printf( __( 'Hello, %s', 'wp-user-frontend' ), $user->display_name ); ?> </h3>

    <?php printf( __( 'You are currently logged in! %s?', 'wp-user-frontend' ), wp_loginout( '', false ) ) ?>
</div>
