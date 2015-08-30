<?php

/**
 * Manage Subscription packs
 *
 * @package WP User Frontend
 */
class WPUF_Admin_Subscription {

    private $table;
    private $db;
    public $baseurl;
    private static $_instance;

    public static function getInstance() {
        if ( !self::$_instance ) {
            self::$_instance = new WPUF_Admin_Subscription();
        }

        return self::$_instance;
    }

    function __construct() {
        global $wpdb;

        $this->db = $wpdb;
        $this->table = $this->db->prefix . 'wpuf_subscription';
        $this->baseurl = admin_url( 'admin.php?page=wpuf_subscription' );

        add_filter( 'post_updated_messages', array($this, 'form_updated_message') );

        add_action( 'show_user_profile', array($this, 'profile_subscription_details'), 30 );
        add_action( 'edit_user_profile', array($this, 'profile_subscription_details'), 30 );
        add_action( 'personal_options_update', array($this, 'profile_subscription_update') );
        add_action( 'edit_user_profile_update', array($this, 'profile_subscription_update') );
        add_action( 'wp_ajax_wpuf_delete_user_package', array($this, 'delete_user_package') );

        add_filter('manage_wpuf_subscription_posts_columns', array( $this, 'subscription_columns_head') );

        add_action('manage_wpuf_subscription_posts_custom_column', array( $this, 'subscription_columns_content' ),10, 2 );
    }

    /**
     * Custom post update message
     *
     * @param  array $messages
     * @return array
     */
    function form_updated_message( $messages ) {
        $message = array(
             0 => '',
             1 => __('Subscription pack updated.'),
             2 => __('Custom field updated.'),
             3 => __('Custom field deleted.'),
             4 => __('Subscription pack updated.'),
             5 => isset($_GET['revision']) ? sprintf( __('Subscription pack restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
             6 => __('Subscription pack published.'),
             7 => __('Subscription pack saved.'),
             8 => __('Subscription pack submitted.' ),
             9 => '',
            10 => __('Subscription pack draft updated.'),
        );

        $messages['wpuf_subscription'] = $message;

        return $messages;
    }

    /**
     * Update user profile lock
     *
     * @param int $user_id
     */
    function profile_subscription_update( $user_id ) {
        if ( !is_admin() && !current_user_can( 'edit_users' ) ) {
            return;
        }

        if ( !isset( $_POST['pack_id'] ) ) {
            return;
        }

        $pack_id = $_POST['pack_id'];
        $user_pack = WPUF_Subscription::get_user_pack( $_POST['user_id'] );
        if ( $pack_id == $user_pack['pack_id'] ) {
            //updating number of posts

            if( isset( $user_pack['posts'] ) ) {

                foreach( $user_pack['posts'] as $post_type => $post_num ) {
                    $user_pack['posts'][$post_type] = $_POST[$post_type];
                }

            }

            //post expiration enable or disable

            if( isset( $_POST['is_post_expiration_enabled'] ) ) {
                $user_pack['_enable_post_expiration'] = $_POST['is_post_expiration_enabled'];
            } else {
                unset($user_pack['_enable_post_expiration']);
            }

            //updating post time

            if( isset( $_POST['post_expiration_settings'] ) ) {
                echo $user_pack['_post_expiration_time'] = $_POST['post_expiration_settings']['expiration_time_value'].' '.$_POST['post_expiration_settings']['expiration_time_type'];
            }
            if ( isset( $user_pack['recurring'] ) && $user_pack['recurring'] == 'yes' ) {
                foreach ( $user_pack['posts'] as $type => $value ) {
                    $user_pack['posts'][$type] = isset( $_POST[$type] ) ? $_POST[$type] : 0;
                }
            } else {
                foreach ( $user_pack['posts'] as $type => $value ) {
                    $user_pack['posts'][$type] = isset( $_POST[$type] ) ? $_POST[$type] : 0;
                }
                $user_pack['expire'] = isset( $_POST['expire'] ) ? wpuf_date2mysql( $_POST['expire'] ) : $user_pack['expire'];
            }
            WPUF_Subscription::update_user_subscription_meta( $user_id, $user_pack );
        } else {
            if ( $pack_id == '-1' ) {
                return;
            }
            WPUF_Subscription::init()->new_subscription( $user_id, $pack_id, null, false, $status = null );
        }
    }

    function subscription_columns_content( $column_name, $post_ID ) {
        switch ( $column_name ) {
            case 'amount':

                $amount = get_post_meta( $post_ID, '_billing_amount', true );
                if ( intval($amount) == 0 ) {
                    $amount = __( 'Free', 'wpuf' );
                } else {
                    $currency = wpuf_get_option( 'currency_symbol', 'wpuf_payment' );
                    $amount = $currency . $amount;
                }
                echo $amount;
                break;

            case 'recurring':

                $recurring = get_post_meta( $post_ID, '_recurring_pay', true );
                if ( $recurring == 'yes' ) {
                    _e( 'Yes', 'wpuf' );
                } else {
                    _e( 'No', 'wpuf' );
                }
                break;

            case 'duration':

                $recurring_pay        =  get_post_meta( $post_ID, '_recurring_pay', true );
                $billing_cycle_number =  get_post_meta( $post_ID, '_billing_cycle_number', true );
                $cycle_period         =  get_post_meta( $post_ID, '_cycle_period', true );
                if ( $recurring_pay == 'yes' ) {
                    echo $billing_cycle_number .' '. $cycle_period . '\'s (cycle)';
                } else {
                    $expiration_number    =  get_post_meta( $post_ID, '_expiration_number', true );
                    $expiration_period    =  get_post_meta( $post_ID, '_expiration_period', true );
                    echo $expiration_number .' '. $expiration_period . '\'s';
                }
                break;
        }

    }

    function subscription_columns_head( $head ) {
        unset($head['date']);
        $head['title']     = __('Pack Name', 'wpuf' );
        $head['amount']    = __( 'Amount', 'wpuf' );
        $head['recurring'] = __( 'Recurring', 'wpuf' );
        $head['duration']  = __( 'Duration', 'wpuf' );

        return $head;
    }

    function get_packs() {
        return $this->db->get_results( "SELECT * FROM {$this->table} ORDER BY created DESC" );
    }

    function get_pack( $pack_id ) {
        return $this->db->get_row( $this->db->prepare( "SELECT * FROM {$this->table} WHERE id = %d", $pack_id ) );
    }

    function delete_pack( $pack_id ) {
        $this->db->query( $this->db->prepare( "DELETE FROM {$this->table} WHERE id= %d", $pack_id ) );
    }

    function list_packs() {

        //delete packs
        if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == "del" ) {
            check_admin_referer( 'wpuf_pack_del' );
            $this->delete_pack( $_GET['id'] );
            echo '<div class="updated fade" id="message"><p><strong>' . __( 'Pack Deleted', 'wpuf' ) . '</strong></p></div>';

            echo '<script type="text/javascript">window.location.href = "' . $this->baseurl . '";</script>';
        }
        ?>

        <table class="widefat meta" style="margin-top: 20px;">
            <thead>
                <tr>
                    <th scope="col"><?php _e( 'Name', 'wpuf' ); ?></th>
                    <th scope="col"><?php _e( 'Description', 'wpuf' ); ?></th>
                    <th scope="col"><?php _e( 'Cost', 'wpuf' ); ?></th>
                    <th scope="col"><?php _e( 'Validity', 'wpuf' ); ?></th>
                    <th scope="col"><?php _e( 'Post Count', 'wpuf' ); ?></th>
                    <th scope="col"><?php _e( 'Action', 'wpuf' ); ?></th>
                </tr>
            </thead>
            <?php
            $packs = $this->get_packs();
            if ( $packs ) {
                $count = 0;
                foreach ($packs as $row) {
                    ?>
                    <tr valign="top" <?php echo ( ($count % 2) == 0) ? 'class="alternate"' : ''; ?>>
                        <td><?php echo stripslashes( htmlspecialchars( $row->name ) ); ?></td>
                        <td><?php echo stripslashes( htmlspecialchars( $row->description ) ); ?></td>
                        <td><?php echo $row->cost; ?> <?php echo get_option( 'wpuf_sub_currency' ); ?></td>
                        <td><?php echo ( $row->pack_length == 0 ) ? 'Unlimited' : $row->pack_length . ' days'; ?></td>
                        <td><?php echo ( $row->count == 0 ) ? 'Unlimited' : $row->count; ?></td>
                        <td>
                            <a href="<?php echo wp_nonce_url( add_query_arg( array('action' => 'edit', 'pack_id' => $row->id), $this->baseurl, 'wpuf_pack_edit' ) ); ?>">
                                <?php _e( 'Edit', 'wpuf' ); ?>
                            </a>
                            <span class="sep">|</span>
                            <a href="<?php echo wp_nonce_url( add_query_arg( array('action' => 'del', 'id' => $row->id), $this->baseurl ), 'wpuf_pack_del' ); ?>" onclick="return confirm('<?php _e( 'Are you sure to delete this pack?', 'wpuf' ); ?>');">
                                <?php _e( 'Delete', 'wpuf' ); ?>
                            </a>
                        </td>

                    </tr>
                    <?php
                    $count++;
                }
                ?>
            <?php } else { ?>
                <tr>
                    <td colspan="6"><?php _e( 'No subscription pack found', 'wpuf' ); ?></td>
                </tr>
            <?php } ?>

        </table>
        <?php
    }

    function get_post_types( $post_types = null ) {

        if ( ! $post_types ) {
            $post_types = WPUF_Subscription::init()->get_all_post_type();
        }

        ob_start();

        foreach ( $post_types as $key => $name ) {
            $name = ( $post_types !== null ) ? $name : '';
            ?>
            <tr>
                <th><label for="wpuf-<?php echo esc_attr( $key ); ?>"><?php printf( 'Number of %ss', $key ); ?></label></th>
                <td>
                    <input type="text" size="20" style="" id="wpuf-<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( (int)$name ); ?>" name="post_type_name[<?php echo esc_attr( $key ); ?>]" />
                    <div><span class="description"><span><?php printf( 'How many %s the user can list with this pack? Enter <strong>-1</strong> for unlimited.', $key ); ?></span></span></div>
                </td>
            </tr>
            <?php
        }

        return ob_get_clean();
    }

    function form( $pack_id = null ) {
        global $post;

        $sub_meta = WPUF_Subscription::init()->get_subscription_meta( $post->ID, $post );

        $hidden_recurring_class = ( $sub_meta['recurring_pay'] != 'yes' ) ? 'none' : '';
        $hidden_trial_class     = ( $sub_meta['trial_status'] != 'yes' ) ? 'none' : '';
        $hidden_expire          = ( $sub_meta['recurring_pay'] == 'yes' ) ? 'none' : '';

        ?>

        <table class="form-table" style="width: 100%">
            <tbody>
                <input type="hidden" name="wpuf_subscription" id="wpuf_subscription_editor" value="<?php echo wp_create_nonce( 'wpuf_subscription_editor' ); ?>" />
                <tr>
                    <th><label><?php _e( 'Pack Description', 'wpuf' ); ?></label></th>
                    <td>
                        <?php wp_editor( $sub_meta['post_content'], 'post_content', array('editor_height' => 100, 'quicktags' => false, 'media_buttons' => false) ); ?>
                    </td>
                </tr>
                <tr>
                    <th><label for="wpuf-billing-amount">
                        <span class="wpuf-biling-amount wpuf-subcription-expire" style="display: <?php echo $hidden_expire; ?>;"><?php _e( 'Billing amount:', 'wpuf' ); ?></span>
                        <span class="wpuf-billing-cycle wpuf-recurring-child" style="display: <?php echo $hidden_recurring_class; ?>;"><?php _e( 'Billing amount each cycle:', 'wpuf' ); ?></span></label></th>
                    <td>
                        <?php echo wpuf_get_option( 'currency_symbol', 'wpuf_payment', '$' ); ?><input type="text" size="20" style="" id="wpuf-billing-amount" value="<?php echo esc_attr( $sub_meta['billing_amount'] ); ?>" name="billing_amount" />
                        <div><span class="description"></span></div>
                    </td>
                </tr>

                <tr class="wpuf-subcription-expire" style="display: <?php echo $hidden_expire; ?>;">
                    <th><label for="wpuf-expiration-number"><?php _e( 'Expires In:', 'wpuf' ); ?></label></th>
                    <td>
                        <input type="text" size="20" style="" id="wpuf-expiration-number" value="<?php echo esc_attr( $sub_meta['expiration_number'] ); ?>" name="expiration_number" />

                        <select id="expiration-period" name="expiration_period">
                            <?php echo $this->option_field( $sub_meta['expiration_period'] ); ?>
                        </select>
                        <div><span class="description"></span></div>
                    </td>
                </tr>

                <?php
                $is_post_exp_selected = isset($sub_meta['_enable_post_expiration']) && $sub_meta['_enable_post_expiration'] == 'on'?'checked':'';
                $_post_expiration_time = explode(' ',isset($sub_meta['_post_expiration_time'])?$sub_meta['_post_expiration_time']:' ');
                $time_value = isset($_post_expiration_time[0])?$_post_expiration_time[0]:1;
                $time_type = isset($_post_expiration_time[1])?$_post_expiration_time[1]:'day';

                $expired_post_status = isset($sub_meta['_expired_post_status'])?$sub_meta['_expired_post_status']:'';
                $is_enable_mail_after_expired = isset($sub_meta['_enable_mail_after_expired']) && $sub_meta['_enable_mail_after_expired'] == 'on'?'checked':'';
                $post_expiration_message = isset($sub_meta['_post_expiration_message'])?$sub_meta['_post_expiration_message']:'';;
                ?>
                <tr class="wpuf-metabox-post_expiration">
                    <th><?php _e( 'Enable Post Expiration', 'wpuf' ); ?></th>
                    <td>
                        <input type="checkbox" id="wpuf-enable_post_expiration" name="post_expiration_settings[enable_post_expiration]" value="on" <?php echo $is_post_exp_selected;?> />
                    </td>
                </tr>
                <tr class="wpuf-metabox-post_expiration wpuf_expiration_field">
                    <?php
                    $timeType_array = array(
                        'year' => 100,
                        'month' => 12,
                        'day' => 30
                    );
                    ?>
                    <th><?php _e( 'Post Expiration Time', 'wpuf' ); ?></th>
                    <td>
                        <select name="post_expiration_settings[expiration_time_value]" id="wpuf-expiration_time_value">
                            <?php
                            for($i = 1;$i <= $timeType_array[$time_type];$i++){
                                ?>
                                <option value="<?php echo $i; ?>" <?php echo $i == $time_value?'selected':''; ?>><?php echo $i;?></option>
                            <?php
                            }
                            ?>
                        </select>
                        <select name="post_expiration_settings[expiration_time_type]" id="wpuf-expiration_time_type">
                            <?php
                            foreach($timeType_array as $each_time_type=>$each_time_type_val){
                                ?>
                                <option value="<?php echo $each_time_type;?>" <?php echo $each_time_type==$time_type?'selected':''; ?>><?php echo ucfirst($each_time_type);?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </td>

                </tr>
                <tr class="wpuf_expiration_field">
                    <th>
                        Post Status :
                    </th>
                    <td>
                        <?php $post_statuses = get_post_statuses();
                        ?>
                        <select name="post_expiration_settings[expired_post_status]" id="wpuf-expired_post_status">
                            <?php
                            foreach($post_statuses as $post_status => $text){
                                ?>
                                <option value="<?php echo $post_status ?>" <?php echo ( $expired_post_status == $post_status )?'selected':''; ?>><?php echo $text;?></option>
                            <?php
                            }
                            ?>
                        </select>
                        <p class="description"><?php echo _('Status of post after post expiration time is over ');?></p>
                    </td>
                </tr>
                <tr class="wpuf_expiration_field">
                    <th>
                        Send Mail :
                    </th>
                    <td>
                        <input type="checkbox" name="post_expiration_settings[enable_mail_after_expired]" value="on" <?php echo $is_enable_mail_after_expired;?> />
                        Send Mail to Author After Exceeding Post Expiration Time
                    </td>
                </tr>
                <tr class="wpuf_expiration_field">
                    <th>Post Expiration Message</th>
                    <td>
                        <textarea name="post_expiration_settings[post_expiration_message]" id="wpuf-post_expiration_message" cols="50" rows="5"><?php echo $post_expiration_message;?></textarea>
                    </td>
                </tr>
                <?php echo $this->get_post_types( $sub_meta['post_type_name'] ); ?>
                <?php
                do_action( 'wpuf_admin_subscription_detail', $sub_meta, $hidden_recurring_class, $hidden_trial_class, $this );
                ?>
            </tbody>
        </table>

        <?php

    }

    function option_field( $selected ) {
        ?>
        <option value="day" <?php selected( $selected, 'day' ); ?> ><?php _e( 'Day(s)', 'wpuf' ); ?></option>
        <option value="week" <?php selected( $selected, 'week' ); ?> ><?php _e( 'Week(s)', 'wpuf' ); ?></option>
        <option value="month" <?php selected( $selected, 'month' ); ?> ><?php _e( 'Month(s)', 'wpur'); ?></option>
        <option value="year" <?php selected( $selected, 'year' ); ?> ><?php _e( 'Year(s)', 'wpuf' ); ?></option>
        <?php
    }

    function packdropdown_without_recurring( $packs, $selected = '' ) {
        $packs = isset( $packs ) ? $packs : array();
        foreach( $packs as $key => $pack ) {
            $recurring = isset( $pack->meta_value['recurring_pay'] ) ? $pack->meta_value['recurring_pay'] : '';
            if( $recurring == 'yes' ) {
                continue;
            }
            ?>
            <option value="<?php echo $pack->ID; ?>" <?php selected( $selected, $pack->ID ); ?>><?php echo $pack->post_title; ?></option>
            <?php
        }
    }

    /**
     * Adds the postlock form in users profile
     *
     * @param object $profileuser
     */
    function profile_subscription_details( $profileuser ) {

        if ( ! current_user_can( 'edit_users' ) ) {
            return;
        }
        if ( wpuf_get_option( 'charge_posting', 'wpuf_payment' ) != 'yes' ) {
            return;
        }
        $userdata = get_userdata( $profileuser->ID ); //wp 3.3 fix

        $packs = WPUF_Subscription::init()->get_subscriptions();
        $user_sub = WPUF_Subscription::get_user_pack( $userdata->ID );
        $pack_id = isset( $user_sub['pack_id'] ) ? $user_sub['pack_id'] : '';
        ?>
        <div class="wpuf-user-subscription">
            <h3><?php _e( 'WPUF Subscription', 'wpuf' ); ?></h3>
            <?php


            if ( isset( $user_sub['pack_id'] ) ) :

            $pack = WPUF_Subscription::get_subscription( $user_sub['pack_id'] );
            $details_meta = WPUF_Subscription::init()->get_details_meta_value();

            $billing_amount = ( intval( $pack->meta_value['billing_amount'] ) > 0 ) ? $details_meta['symbol'] . $pack->meta_value['billing_amount'] : __( 'Free', 'wpuf' );
            if ( $billing_amount && $pack->meta_value['recurring_pay'] == 'yes' ) {
                $recurring_des = sprintf( 'For each %s %s', $pack->meta_value['billing_cycle_number'], $pack->meta_value['cycle_period'], $pack->meta_value['trial_duration_type'] );
                $recurring_des .= !empty( $pack->meta_value['billing_limit'] ) ? sprintf( ', for %s installments', $pack->meta_value['billing_limit'] ) : '';
                $recurring_des = $recurring_des;
            } else {
                $recurring_des = '';
            }

            ?>
                <div class="wpuf-user-sub-info">
                    <h3><?php _e( 'Subscription Details', 'wpuf' ); ?></h3>
                    <?php if(isset($user_sub['recurring']) && $user_sub['recurring'] == 'yes' ){
                        ?>
                        <div class="updated">
                            <p><?php _e( 'This user is using recurring subscription pack', 'wpuf' ); ?></p>
                        </div>
                    <?php
                    } ?>
                    <div class="wpuf-text">
                        <div><strong><?php _e( 'Subcription Name: ','wpuf' ); ?></strong><?php echo $pack->post_title; ?></div>
                        <div>
                            <strong><?php _e( 'Package billing details: '); ?></strong>
                            <div class="wpuf-pricing-wrap">
                                <div class="wpuf-sub-amount">
                                    <?php echo $billing_amount; ?>
                                    <?php echo $recurring_des; ?>

                                </div>
                            </div>
                        </div>

                        <strong><?php _e( 'Remaining post: ', 'wpuf'); ?></strong>
                        <table class="form-table">

                            <?php

                            foreach ($user_sub['posts'] as $key => $value) {
                                ?>
                                 <tr>
                                     <th><label><?php echo $key; ?></label></th>
                                     <td><input type="text" value="<?php echo $value; ?>" name="<?php echo $key; ?>" ></td>
                                 </tr>
                                <?php
                            }
                            ?>
                        <?php
                        if ( $user_sub['recurring'] != 'yes' ) {
                            if ( !empty( $user_sub['expire'] ) ) {

                                $expire =  ( $user_sub['expire'] == 'unlimited' ) ? ucfirst( 'unlimited' ) : wpuf_date2mysql( $user_sub['expire'] );

                                ?>
                                <tr>
                                    <th><label><?php echo _e('Expire date:'); ?></label></th>
                                    <td><input type="text" class="wpuf-date-picker" name="expire" value="<?php echo wpuf_get_date( $expire ); ?>"></td>
                                </tr>
                                <?php
                            }

                        } ?>
                            <?php
                            $is_post_exp_selected = isset($user_sub['_enable_post_expiration'])?'checked':'';
                            $_post_expiration_time = explode(' ',isset($user_sub['_post_expiration_time'])?$user_sub['_post_expiration_time']:' ');
                            $time_value = isset($_post_expiration_time[0]) && !empty($_post_expiration_time[0])?$_post_expiration_time[0]:'1';
                            $time_type = isset($_post_expiration_time[1]) && !empty($_post_expiration_time[1])?$_post_expiration_time[1]:'day';
                            ?>
                            <tr>
                                <th><label><?php echo _e('Post Expiration Enabled'); ?></label></th>
                                <td><input type="checkbox" name="is_post_expiration_enabled" value="on" <?php echo $is_post_exp_selected;?>></td>
                            </tr>
                            <tr>
                                <?php
                                $timeType_array = array(
                                    'year' => 100,
                                    'month' => 12,
                                    'day' => 30
                                );
                                ?>
                                <th><?php _e( 'Post Expiration Time', 'wpuf' ); ?></th>
                                <td>
                                    <select name="post_expiration_settings[expiration_time_value]" id="wpuf-expiration_time_value">
                                        <?php
                                        for($i = 1;$i <= $timeType_array[$time_type];$i++){
                                            ?>
                                            <option value="<?php echo $i; ?>" <?php echo $i == $time_value?'selected':''; ?>><?php echo $i;?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                    <select name="post_expiration_settings[expiration_time_type]" id="wpuf-expiration_time_type">
                                        <?php
                                        foreach($timeType_array as $each_time_type=>$each_time_type_val){
                                            ?>
                                            <option value="<?php echo $each_time_type;?>" <?php echo $each_time_type==$time_type?'selected':''; ?>><?php echo ucfirst($each_time_type);?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            <?php endif;?>
            <?php if(!isset($user_sub['recurring']) || $user_sub['recurring'] != 'yes' ): ?>
            <a class="btn button-primary wpuf-assing-pack-btn wpuf-add-pack" href="#"><?php _e( 'Assign Package', 'wpuf' ); ?></a>
            <a class="btn button-primary wpuf-assing-pack-btn wpuf-cancel-pack" style="display:none;" href="#"><?php _e( 'Show Package', 'wpuf' ); ?></a>
            <table class="form-table wpuf-pack-dropdown" disabled="disabled" style="display: none;">
                <tr>
                    <th><label for="wpuf_sub_pack"><?php _e( 'Pack:', 'wpuf' ); ?> </label></th>
                    <td>
                        <select name="pack_id" id="wpuf_sub_pack">
                            <option value="-1"><?php _e( '--Select--', 'wpuf' ); ?></option>
                            <?php $this->packdropdown_without_recurring( $packs, $pack_id );//WPUF_Subscription::init()->packdropdown( $packs, $selected = '' ); ?>
                        </select>
                    </td>
                </tr>
            </table>
            <?php endif;?>
            <?php if( !empty($user_sub) ):?>
                <a class="btn button-primary wpuf-delete-pack-btn" href="javascript:" data-userid = "<?php echo $userdata->ID; ?>"><?php _e( 'Delete Package', 'wpuf' ); ?></a>
            <?php endif; ?>
        </div>
        <?php

    }

    function lenght_type_option( $selected ) {

        for ($i = 1; $i <= 30; $i++) {
            ?>
                <option value="<?php echo $i; ?>" <?php selected( $i, $selected ); ?>><?php echo $i; ?></option>
            <?php
        }

    }

    /**
     * Ajax function. Delete user package
     * @since 2.2.7
     */
    function delete_user_package(){
        $wpuf_paypal = new WPUF_Paypal();
        $wpuf_paypal->recurring_change_status( $_POST['userid'], 'Cancel' );
        echo delete_user_meta($_POST['userid'],'_wpuf_subscription_pack');
        exit;
    }
}

//$subscription = new WPUF_Admin_Subscription();
