<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function mcpt_register_newsletter_cpt() {
    $labels = array(
        'name'               => _x( 'Newsletters', 'post type general name', 'mcpt' ),
        'singular_name'      => _x( 'Newsletter', 'post type singular name', 'mcpt' ),
        'menu_name'          => _x( 'Newsletters', 'admin menu', 'mcpt' ),
        'name_admin_bar'     => _x( 'Newsletter', 'add new on admin bar', 'mcpt' ),
        'add_new'            => _x( 'Add New', 'newsletter', 'mcpt' ),
        'add_new_item'       => __( 'Add New Newsletter', 'mcpt' ),
        'new_item'           => __( 'New Newsletter', 'mcpt' ),
        'edit_item'          => __( 'Edit Newsletter', 'mcpt' ),
        'view_item'          => __( 'View Newsletter', 'mcpt' ),
        'all_items'          => __( 'All Newsletters', 'mcpt' ),
        'search_items'       => __( 'Search Newsletters', 'mcpt' ),
        'parent_item_colon'  => __( 'Parent Newsletters:', 'mcpt' ),
        'not_found'          => __( 'No newsletters found.', 'mcpt' ),
        'not_found_in_trash' => __( 'No newsletters found in Trash.', 'mcpt' )
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
