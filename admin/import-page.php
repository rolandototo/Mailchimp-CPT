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
        <?php settings_errors( 'mcpt_messages' ); ?>
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
    if ( ! isset( $_POST['mcpt_import_nonce'] ) ) {
        return;
    }

    check_admin_referer( 'mcpt_import_action', 'mcpt_import_nonce' );

    // Same capability as the import page itself.
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You are not allowed to import newsletters.', 'mcpt' ), 403 );
    }

    $url = isset( $_POST['mcpt_url'] ) ? esc_url_raw( wp_unslash( $_POST['mcpt_url'] ), array( 'http', 'https' ) ) : '';

    // settings_errors() prints messages as HTML, so escape them here.
    if ( '' === $url ) {
        add_settings_error( 'mcpt_messages', 'mcpt_error', esc_html__( 'Enter a valid http or https URL.', 'mcpt' ), 'error' );
        return;
    }

    $result = mcpt_import_newsletter_from_url( $url );

    if ( is_wp_error( $result ) ) {
        add_settings_error( 'mcpt_messages', 'mcpt_error', esc_html( $result->get_error_message() ), 'error' );
        return;
    }

    $message = sprintf(
        /* translators: %s: link to edit the imported draft */
        __( 'Newsletter imported as a draft. %s', 'mcpt' ),
        '<a href="' . esc_url( get_edit_post_link( $result, 'url' ) ) . '">' . esc_html__( 'Review and publish it', 'mcpt' ) . '</a>'
    );
    add_settings_error( 'mcpt_messages', 'mcpt_success', $message, 'success' );
}
add_action( 'admin_init', 'mcpt_handle_import_request' );
