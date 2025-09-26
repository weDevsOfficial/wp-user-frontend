<div class="panel-field-opt panel-field-opt-radio">
    <div class="wpuf-flex">
        <label
            v-if="option_field.title"
            class="wpuf-option-field-title wpuf-font-sm wpuf-text-gray-700 wpuf-font-medium">{{ option_field.title }}</label>
        <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>
    </div>

    <div
        v-if="!option_field.inline"
        class="wpuf-flex wpuf-items-center wpuf-gap-x-2 wpuf-m-2"
        v-for="(option, key) in option_field.options">
        <label
            class="wpuf-block text-sm/6 wpuf-font-medium wpuf-text-gray-900">
            <input
                type="radio"
                :name="'visibility_' + editing_form_field.id"
                :value="key"
                v-model="selected"
                class="checked:!wpuf-bg-primary checked:before:!wpuf-bg-transparent">
            {{ option }}</label>
    </div>

    <div
        v-if="option_field.inline"
        class="wpuf-mt-2 wpuf-flex wpuf-flex-wrap">
        <div
            v-for="(option, key, index) in option_field.options"
            class="wpuf-items-center wpuf-mr-9">
            <label
                class="wpuf-block wpuf-my-1 wpuf-mr-2 wpuf-font-medium wpuf-text-gray-900">
                <input
                    type="radio"
                    :name="'visibility_' + editing_form_field.id"
                    :value="key"
                    v-model="selected"
                    :class="builder_class_names('radio')">
                {{ option }}
            </label>
        </div>
    </div>

    <div v-if="'logged_in' === selected" class="condiotional-logic-container wpuf-mt-2">

    	<?php $roles = get_editable_roles(); ?>

    	<ul>
			<?php
                foreach ( $roles as $role => $value ) {
                    $role_name = $value['name'];

                    $output  = '<li class="wpuf-mt-2 wpuf-flex wpuf-items-center">';
                    $output .= "<label class='wpuf-flex wpuf-items-center'><input :class=\"builder_class_names('checkbox')\" class=\"!wpuf-mr-2\" type=\"checkbox\" v-model=\"choices\" value=\"{$role}\"> {$role_name} </label>";
                    $output .= '</li>';

                    $allowed_html = [
                        'li'    => [
                            'class' => true,
                        ],
                        'label' => [
                            'class' => true,
                        ],
                        'input' => [
                            'class' => true,
                            'type'  => true,
                            'value' => true,
                        ],
                    ];

                    // Apply standard wp_kses first
                    $partially_filtered = wp_kses( $output, $allowed_html );

                    // Then re-add Vue attributes with explicit pattern matching for safety
                    $vue_attributes = array(
                        ':class="builder_class_names(\'checkbox\')"',
                        'v-model="choices"'
                    );

                    foreach ($vue_attributes as $attr) {
                        // Safely insert the attribute back into the input tag
                        $partially_filtered = preg_replace('/(<input[^>]+)/', '$1 ' . $attr, $partially_filtered, 1);
                    }

                    echo esc_html( $partially_filtered );
                }
            ?>
	    </ul>
    </div>

    <div v-if="'subscribed_users' === selected" class="condiotional-logic-container wpuf-mt-2">

    	<ul>
    		<?php

                if ( class_exists( 'WPUF_Subscription' ) ) {
                    $subscriptions  = wpuf()->subscription->get_subscriptions();

                    if ( $subscriptions ) {
                        foreach ( $subscriptions as $pack ) {
                            $output  = '<li class="wpuf-mt-2 wpuf-flex wpuf-items-center">';
                            $output .= "<label class='wpuf-flex wpuf-items-center'><input  :class=\"builder_class_names('checkbox')\" class=\"!wpuf-mr-2\" type='checkbox' v-model='choices' value='{$pack->ID}' > {$pack->post_title} </label>";
                            $output .= '</li>';

                            $allowed_html = [
                                'li'    => [
                                    'class' => true,
                                ],
                                'label' => [
                                    'class' => true,
                                ],
                                'input' => [
                                    'class' => true,
                                    'type'  => true,
                                    'value' => true,
                                ],
                            ];

                            // Apply standard wp_kses first
                            $partially_filtered = wp_kses( $output, $allowed_html );

                            // Then re-add Vue attributes with explicit pattern matching for safety
                            $vue_attributes = array(
                                ':class="builder_class_names(\'checkbox\')"',
                                'v-model="choices"'
                            );

                            foreach ($vue_attributes as $attr) {
                                // Safely insert the attribute back into the input tag
                                $partially_filtered = preg_replace('/(<input[^>]+)/', '$1 ' . $attr, $partially_filtered, 1);
                            }

                            echo esc_html( $partially_filtered );
                        }
                    } else {
                        esc_html_e( 'No subscription plan found.', 'wp-user-frontend' );
                    }
                }
            ?>
    	</ul>

    </div>
</div>
