<div class="wpuf-user-loggedin">

	<span class="wpuf-user-avatar">
		<?php echo get_avatar( $user->ID ); ?>
	</span>

    <br>
    <h3> <?php printf( __( 'Hello, %s', 'wpuf' ), $user->display_name ); ?> </h3>

    <?php printf( __( 'You are currently logged in! %s?', 'wpuf' ), wp_loginout( '', false ) ) ?>
</div>
