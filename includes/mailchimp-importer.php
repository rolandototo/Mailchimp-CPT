<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function mcpt_import_newsletter_from_url( $url ) {
    $response = wp_remote_get( $url );

    if ( is_wp_error( $response ) ) {
        return $response;
    }

    $html = wp_remote_retrieve_body( $response );

    if ( empty( $html ) ) {
        return new WP_Error( 'empty_body', __( 'Empty response body.', 'mcpt' ) );
    }

    // Parse HTML
    libxml_use_internal_errors( true );
    $doc = new DOMDocument();
    $doc->loadHTML( $html );
    libxml_clear_errors();

    $xpath = new DOMXPath( $doc );
    $body_nodes = $xpath->query( '//body' );
    $content = '';
    if ( $body_nodes->length > 0 ) {
        $content = $doc->saveHTML( $body_nodes->item(0) );
    } else {
        $content = $html;
    }

    // Download images
    $image = mcpt_download_first_image( $doc );
    $excerpt = mcpt_extract_excerpt( $doc );

    // Create post
    $post_id = wp_insert_post( array(
        'post_title'   => wp_strip_all_tags( $doc->getElementsByTagName( 'title' )->item(0)->textContent ),
        'post_content' => $content,
        'post_status'  => 'draft',
        'post_type'    => 'newsletter',
        'post_excerpt' => $excerpt,
    ) );

    if ( ! is_wp_error( $post_id ) && $image ) {
        set_post_thumbnail( $post_id, $image );
    }

    return $post_id;
}

function mcpt_download_first_image( $doc ) {
    $imgs = $doc->getElementsByTagName( 'img' );
    if ( $imgs->length === 0 ) {
        return 0;
    }

    $src = $imgs->item(0)->getAttribute( 'src' );
    $tmp = download_url( $src );
    if ( is_wp_error( $tmp ) ) {
        return 0;
    }

    $file = array(
        'name'     => basename( parse_url( $src, PHP_URL_PATH ) ),
        'type'     => mime_content_type( $tmp ),
        'tmp_name' => $tmp,
        'error'    => 0,
        'size'     => filesize( $tmp ),
    );

    $sideload = wp_handle_sideload( $file, array( 'test_form' => false ) );
    if ( ! empty( $sideload['error'] ) ) {
        @unlink( $tmp );
        return 0;
    }

    $attachment = array(
        'post_title'     => sanitize_file_name( $file['name'] ),
        'post_content'   => '',
        'post_mime_type' => $sideload['type'],
        'guid'           => $sideload['url']
    );

    $attach_id = wp_insert_attachment( $attachment, $sideload['file'] );
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $attach_data = wp_generate_attachment_metadata( $attach_id, $sideload['file'] );
    wp_update_attachment_metadata( $attach_id, $attach_data );

    return $attach_id;
}

function mcpt_extract_excerpt( $doc ) {
    $p = $doc->getElementsByTagName( 'p' );
    if ( $p->length > 0 ) {
        return wp_trim_words( $p->item(0)->textContent, 55 );
    }
    return '';
}
