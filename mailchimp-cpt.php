<?php
/**
 * Plugin Name: Mailchimp CPT Importer
 * Description: Import newsletters from Mailchimp or any URL and store them as a custom post type.
 * Version: 0.1.1
 * Author: Rolando
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Define plugin path
if ( ! defined( 'MCPT_PATH' ) ) {
    define( 'MCPT_PATH', plugin_dir_path( __FILE__ ) );
}

// Include files
require_once MCPT_PATH . 'includes/cpt-newsletter.php';
require_once MCPT_PATH . 'includes/mailchimp-importer.php';
require_once MCPT_PATH . 'admin/import-page.php';

// Enqueue admin styles
function mcpt_admin_enqueue() {
    wp_enqueue_style( 'mcpt-admin', plugin_dir_url( __FILE__ ) . 'assets/css/admin-style.css', array(), '0.1.1' );
}
add_action( 'admin_enqueue_scripts', 'mcpt_admin_enqueue' );
