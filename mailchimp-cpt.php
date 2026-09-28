<?php
/**
 * Plugin Name:       Mailchimp CPT Importer
 * Plugin URI:        https://github.com/rolandototo/Mailchimp-CPT
 * Description:       Imports a newsletter from a public URL, such as a Mailchimp campaign page, into a Newsletter custom post type as a draft.
 * Version:           0.3.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Rolando Escobar
 * Author URI:        https://rolandowp.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       mailchimp-cpt
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Define plugin path and version
if ( ! defined( 'MCPT_PATH' ) ) {
    define( 'MCPT_PATH', plugin_dir_path( __FILE__ ) );
}
define( 'MCPT_VERSION', '0.3.0' );

// Include files
require_once MCPT_PATH . 'includes/cpt-newsletter.php';
require_once MCPT_PATH . 'includes/mailchimp-importer.php';
require_once MCPT_PATH . 'admin/import-page.php';

// Admin styles, loaded only on the Import Newsletter screen (see admin/import-page.php).
function mcpt_admin_enqueue() {
    wp_enqueue_style( 'mcpt-admin', plugin_dir_url( __FILE__ ) . 'assets/css/admin-style.css', array(), MCPT_VERSION );
}

/**
 * Registers the post type and flushes rewrite rules on activation, so
 * /newsletter/ works without re-saving Settings > Permalinks.
 */
function mcpt_activate() {
    mcpt_register_newsletter_cpt();
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'mcpt_activate' );

/**
 * Removes the post type's rewrite rules on deactivation.
 */
function mcpt_deactivate() {
    unregister_post_type( 'newsletter' );
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'mcpt_deactivate' );
