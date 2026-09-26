<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function mcpt_register_import_page() {
    $hook = add_submenu_page(
        'edit.php?post_type=newsletter',
        __( 'Import Newsletter', 'mailchimp-cpt' ),
        __( 'Import Newsletter', 'mailchimp-cpt' ),
        'manage_options',
        'mcpt-import',
        'mcpt_render_import_page'
    );

    if ( $hook ) {
        add_action( 'load-' . $hook, 'mcpt_load_import_page' );
    }
}

function mcpt_load_import_page() {
    add_action( 'admin_enqueue_scripts', 'mcpt_admin_enqueue' );
}
add_action( 'admin_menu', 'mcpt_register_import_page' );

function mcpt_render_import_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Import Newsletter', 'mailchimp-cpt' ); ?></h1>
        <?php settings_errors( 'mcpt_messages' ); ?>
        <form method="post">
            <?php wp_nonce_field( 'mcpt_import_action', 'mcpt_import_nonce' ); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><label for="mcpt_url"><?php esc_html_e( 'Newsletter URL', 'mailchimp-cpt' ); ?></label></th>
                    <td>
                        <input type="url" name="mcpt_url" id="mcpt_url" class="regular-text" required aria-describedby="mcpt_url_help">
                        <p class="description" id="mcpt_url_help"><?php esc_html_e( 'Public URL of the newsletter, for example the "View this email in your browser" link of a Mailchimp campaign. It is saved as a draft.', 'mailchimp-cpt' ); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button( __( 'Import Newsletter', 'mailchimp-cpt' ) ); ?>
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
        wp_die( esc_html__( 'You are not allowed to import newsletters.', 'mailchimp-cpt' ), 403 );
    }

    $url = isset( $_POST['mcpt_url'] ) ? esc_url_raw( wp_unslash( $_POST['mcpt_url'] ), array( 'http', 'https' ) ) : '';

    // settings_errors() prints messages as HTML, so escape them here.
    if ( '' === $url ) {
        add_settings_error( 'mcpt_messages', 'mcpt_error', esc_html__( 'Enter a valid http or https URL.', 'mailchimp-cpt' ), 'error' );
        return;
    }

    $result = mcpt_import_newsletter_from_url( $url );

    if ( is_wp_error( $result ) ) {
        add_settings_error( 'mcpt_messages', 'mcpt_error', esc_html( $result->get_error_message() ), 'error' );
        return;
    }

    $message = sprintf(
        /* translators: %s: link to edit the imported draft */
        __( 'Newsletter imported as a draft. %s', 'mailchimp-cpt' ),
        '<a href="' . esc_url( get_edit_post_link( $result, 'url' ) ) . '">' . esc_html__( 'Review and publish it', 'mailchimp-cpt' ) . '</a>'
    );
    add_settings_error( 'mcpt_messages', 'mcpt_success', $message, 'success' );
}
add_action( 'admin_init', 'mcpt_handle_import_request' );
