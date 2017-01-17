<div class="wpuf-dashboard-container">
    <nav class="wpuf-dashboard-navigation">
        <?php
            $sections = wpuf_get_dashboard_sections();
        ?>
        <ul>
            <?php
                foreach ( array_keys( $sections ) as $section ) {
                    echo sprintf( '<li><a href="%s/dashboard/?section=%s">%s</a></li>', site_url(), $section, ucwords( str_replace( '-', ' ', $section ) ) );
                }
            ?>
        </ul>
    </nav>

    <div class="wpuf-dashboard-content">
        <?php
            echo '<pre>';
            var_dump( $template );
        ?>
    </div>
</div>