<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function mcpt_register_newsletter_cpt() {
    $labels = array(
        'name'               => _x( 'Newsletters', 'post type general name', 'mailchimp-cpt' ),
        'singular_name'      => _x( 'Newsletter', 'post type singular name', 'mailchimp-cpt' ),
        'menu_name'          => _x( 'Newsletters', 'admin menu', 'mailchimp-cpt' ),
        'name_admin_bar'     => _x( 'Newsletter', 'add new on admin bar', 'mailchimp-cpt' ),
        'add_new'            => _x( 'Add New', 'newsletter', 'mailchimp-cpt' ),
        'add_new_item'       => __( 'Add New Newsletter', 'mailchimp-cpt' ),
        'new_item'           => __( 'New Newsletter', 'mailchimp-cpt' ),
        'edit_item'          => __( 'Edit Newsletter', 'mailchimp-cpt' ),
        'view_item'          => __( 'View Newsletter', 'mailchimp-cpt' ),
        'all_items'          => __( 'All Newsletters', 'mailchimp-cpt' ),
        'search_items'       => __( 'Search Newsletters', 'mailchimp-cpt' ),
        'parent_item_colon'  => __( 'Parent Newsletters:', 'mailchimp-cpt' ),
        'not_found'          => __( 'No newsletters found.', 'mailchimp-cpt' ),
        'not_found_in_trash' => __( 'No newsletters found in Trash.', 'mailchimp-cpt' )
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'newsletter' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt' )
    );

    register_post_type( 'newsletter', $args );
}
add_action( 'init', 'mcpt_register_newsletter_cpt' );
