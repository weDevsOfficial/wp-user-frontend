<div class="wrap">
    <h2><?php _e( 'WP User Frontend - Support', 'wpuf' ); ?></h2>
    <table>
        <tr>
            <th class="lebel">
                <?php _e( 'Need Help?', 'wpuf' ); ?>
            </th>
            <td class="description">
                <ul>
                    <li>
                        <strong><?php printf( __( 'Step 1: ', 'wpuf' ) ); ?><a target="_blank" href="http://docs.wedevs.com/"><?php printf( __( 'Read Documentation and FAQ', 'wpuf' ) ); ?></a></strong>
                        <p><?php printf( __( 'We have created detailed step by step documentation for all the features (including docs for developers). We have also answered all possible FAQ queries based on user request. We hope you will find what you are looking for. If not, please continue to Step 2.', 'wpuf' ) ); ?></p>
                    </li>
                    <br/>
                    <li>
                        <strong><?php printf( __( 'Step 2: ', 'wpuf' ) ); ?><a href="https://wedevs.com/account/tickets" target="_blank"><?php printf( __( 'Create a Support Ticket', 'wpuf' ) ); ?></a></strong>
                        <p><?php printf( __( 'We reply from <code>7am to 3pm (GMT+6)</code> except <code><strong>Friday</strong></code>. Our responses are usually under 12 hours to as quick as 1 hour depending on channel pressure.', 'wpuf' ) ); ?>
                        </p>
                        <p><?php printf( __( 'When you are creating a ticket, please care to include a screenshot of the issue if possible and write the problem as specificly as you can. This will help us track and solve your trouble quickly.', 'wpuf' ) ); ?>
                        </p>
                    </li>
                </ul>
            </td>
        </tr>
    </table>
</div>


<style>
    table{
        margin-top: 20px;
    }
    .lebel{
        width: 20%;
        vertical-align: top;
        text-align: left;
    }
    .description{
        width: 80%;
        vertical-align: top;
    }
</style>