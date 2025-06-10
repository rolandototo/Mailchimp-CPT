<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function mcpt_register_import_page() {
    add_submenu_page(
        'edit.php?post_type=newsletter',
        __( 'Import Newsletter', 'mcpt' ),
        __( 'Import Newsletter', 'mcpt' ),
        'manage_options',
        'mcpt-import',
        'mcpt_render_import_page'
    );
}
add_action( 'admin_menu', 'mcpt_register_import_page' );

function mcpt_render_import_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Import Newsletter', 'mcpt' ); ?></h1>
        <form method="post">
            <?php wp_nonce_field( 'mcpt_import_action', 'mcpt_import_nonce' ); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><label for="mcpt_url">Newsletter URL</label></th>
                    <td><input type="url" name="mcpt_url" id="mcpt_url" class="regular-text" required></td>
                </tr>
            </table>
            <?php submit_button( __( 'Import Newsletter', 'mcpt' ) ); ?>
        </form>
    </div>
    <?php
}

function mcpt_handle_import_request() {
    if ( isset( $_POST['mcpt_import_nonce'] ) && wp_verify_nonce( $_POST['mcpt_import_nonce'], 'mcpt_import_action' ) ) {
        if ( ! empty( $_POST['mcpt_url'] ) ) {
            $result = mcpt_import_newsletter_from_url( esc_url_raw( $_POST['mcpt_url'] ) );
            if ( is_wp_error( $result ) ) {
                add_settings_error( 'mcpt_messages', 'mcpt_error', $result->get_error_message(), 'error' );
            } else {
                add_settings_error( 'mcpt_messages', 'mcpt_success', __( 'Newsletter imported.', 'mcpt' ), 'updated' );
            }
        }
    }
}
add_action( 'admin_init', 'mcpt_handle_import_request' );
