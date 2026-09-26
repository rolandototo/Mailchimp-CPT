<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Imports a public newsletter page as a draft "newsletter" post.
 *
 * @param string $url Public URL of the newsletter, e.g. a Mailchimp campaign page.
 * @return int|WP_Error ID of the new draft, or an error.
 */
function mcpt_import_newsletter_from_url( $url ) {
    // wp_safe_remote_get() refuses local and private network addresses.
    $response = wp_safe_remote_get( $url, array( 'timeout' => 20 ) );

    if ( is_wp_error( $response ) ) {
        return $response;
    }

    $code = (int) wp_remote_retrieve_response_code( $response );
    if ( 200 !== $code ) {
        /* translators: %d: HTTP status code */
        return new WP_Error( 'http_error', sprintf( __( 'The page returned HTTP %d.', 'mcpt' ), $code ) );
    }

    $html = wp_remote_retrieve_body( $response );

    if ( '' === trim( $html ) ) {
        return new WP_Error( 'empty_body', __( 'Empty response body.', 'mcpt' ) );
    }

    // Parse HTML. The XML declaration makes DOMDocument read the page as UTF-8.
    libxml_use_internal_errors( true );
    $doc = new DOMDocument();
    $doc->loadHTML( '<?xml encoding="UTF-8">' . $html );
    libxml_clear_errors();

    $title   = mcpt_extract_title( $doc );
    $excerpt = mcpt_extract_excerpt( $doc );
    $content = mcpt_extract_content( $doc, $url );

    if ( '' === trim( $content ) ) {
        return new WP_Error( 'empty_content', __( 'The page has no content to import.', 'mcpt' ) );
    }

    // Create post
    $post_id = wp_insert_post( array(
        'post_title'   => $title,
        'post_content' => $content,
        'post_status'  => 'draft',
        'post_type'    => 'newsletter',
        'post_excerpt' => $excerpt,
    ), true );

    if ( is_wp_error( $post_id ) ) {
        return $post_id;
    }

    $image_id = mcpt_sideload_first_image( $doc, $url, $post_id );
    if ( $image_id ) {
        set_post_thumbnail( $post_id, $image_id );
    }

    return $post_id;
}

/**
 * Title: the page's <title>, then the first <h1>, then a dated fallback.
 */
function mcpt_extract_title( DOMDocument $doc ) {
    foreach ( array( 'title', 'h1' ) as $tag ) {
        $node = $doc->getElementsByTagName( $tag )->item( 0 );
        if ( $node ) {
            $title = sanitize_text_field( $node->textContent );
            if ( '' !== $title ) {
                return $title;
            }
        }
    }

    /* translators: %s: import date */
    return sprintf( __( 'Newsletter imported on %s', 'mcpt' ), wp_date( get_option( 'date_format' ) ) );
}

/**
 * Content: the inner HTML of <body>, without scripts or styles, with
 * absolute URLs, filtered like any post content.
 */
function mcpt_extract_content( DOMDocument $doc, $page_url ) {
    $xpath = new DOMXPath( $doc );

    // Email CSS and scripts don't belong in a post and would affect the whole theme.
    foreach ( iterator_to_array( $xpath->query( '//script | //style | //noscript | //link | //meta' ) ) as $node ) {
        $node->parentNode->removeChild( $node );
    }

    // Relative image and link URLs would break once the HTML lives on this site.
    foreach ( array( 'img' => 'src', 'a' => 'href' ) as $tag => $attribute ) {
        foreach ( $doc->getElementsByTagName( $tag ) as $element ) {
            $value = trim( $element->getAttribute( $attribute ) );
            if ( '' !== $value && '#' !== $value[0] && 0 !== strpos( $value, 'mailto:' ) ) {
                $element->setAttribute( $attribute, WP_Http::make_absolute_url( $value, $page_url ) );
            }
        }
    }

    $body = $doc->getElementsByTagName( 'body' )->item( 0 );
    if ( ! $body ) {
        return '';
    }

    // Only the children: the <body> tag itself doesn't belong in post content.
    $content = '';
    foreach ( $body->childNodes as $child ) {
        $content .= $doc->saveHTML( $child );
    }

    return wp_kses_post( trim( $content ) );
}

/**
 * Excerpt: the first paragraph with text, trimmed to 55 words.
 */
function mcpt_extract_excerpt( DOMDocument $doc ) {
    foreach ( $doc->getElementsByTagName( 'p' ) as $p ) {
        $text = trim( $p->textContent );
        if ( '' !== $text ) {
            return wp_trim_words( $text, 55 );
        }
    }
    return '';
}

/**
 * Downloads the first image of the page into the Media Library,
 * attached to the new post.
 *
 * @return int Attachment ID, or 0 if there is no usable image.
 */
function mcpt_sideload_first_image( DOMDocument $doc, $page_url, $post_id ) {
    $src = '';
    foreach ( $doc->getElementsByTagName( 'img' ) as $img ) {
        $candidate = trim( $img->getAttribute( 'src' ) );
        if ( '' !== $candidate && 0 !== strpos( $candidate, 'data:' ) ) {
            $src = WP_Http::make_absolute_url( $candidate, $page_url );
            break;
        }
    }

    if ( '' === $src ) {
        return 0;
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $tmp = download_url( $src );
    if ( is_wp_error( $tmp ) ) {
        return 0;
    }

    $file = array(
        'name'     => wp_basename( (string) wp_parse_url( $src, PHP_URL_PATH ) ),
        'tmp_name' => $tmp,
    );

    $attachment_id = media_handle_sideload( $file, $post_id );
    if ( is_wp_error( $attachment_id ) ) {
        @unlink( $tmp );
        return 0;
    }

    return $attachment_id;
}
