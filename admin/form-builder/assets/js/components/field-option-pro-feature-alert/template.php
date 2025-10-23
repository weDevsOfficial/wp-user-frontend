<div class="panel-field-opt panel-field-opt-pro-feature wpuf-flex wpuf-items-center wpuf-text-sm wpuf-text-gray-700 wpuf-font-medium">
    <label>{{ option_field.title }} </label><br>
    <label
        class="wpuf-pro-text-alert wpuf-ml-2 wpuf-tooltip-top"
        data-tip="<?php esc_attr_e( 'Available in PRO version', 'wp-user-frontend' ); ?>">
        <a :href="pro_link" target="_blank"><img src="<?php echo esc_url( wpuf_get_pro_icon() ) ?>" alt="pro icon"></a>
    </label>
</div>
