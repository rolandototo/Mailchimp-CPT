# Mailchimp CPT Importer

A WordPress plugin that imports a newsletter from a public web page, such as a Mailchimp campaign archive page, into a **Newsletter** custom post type. Each import is saved as a draft so you can review it before publishing.

It reads the public HTML page, so you don't need a Mailchimp API key.

![Version](https://img.shields.io/badge/version-0.3.0-blue)
![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-21759b)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-777bb4)
![License](https://img.shields.io/badge/license-GPL--2.0--or--later-green)

## Features

- **Newsletter post type:** registers a public `newsletter` post type with an archive at `/newsletter/`. It supports a title, the editor, a featured image and an excerpt. Rewrite rules are flushed on activation, so the URLs work right away.
- **Import screen:** adds **Newsletters > Import Newsletter**, a form with one URL field. It's available to administrators (`manage_options`), and the request is checked with a nonce and the same capability.
- **Automatic mapping:** fetches the page with `wp_safe_remote_get()`, parses it with `DOMDocument` and fills in the post:
  - Title: the page's `<title>`, then its first `<h1>`, then "Newsletter imported on (date)"
  - Content: the inner HTML of `<body>`, without scripts, styles or meta tags, filtered with `wp_kses_post()`. Relative image and link URLs are made absolute.
  - Excerpt: the first paragraph with text, trimmed to 55 words
  - Featured image: the first image on the page, downloaded into the Media Library and attached to the post
- **Drafts only:** imported newsletters are saved as drafts. The success message links straight to the draft; errors (bad URL, HTTP error, empty page) are shown on the same screen.

## Requirements

- WordPress 6.0+
- PHP 7.4+ with the `dom` extension, which most hosts enable by default
- The server must be able to make outbound HTTP requests to the newsletter URL.

## Installation

1. Download this repository as a ZIP (**Code > Download ZIP**).
2. In WordPress, go to **Plugins > Add New > Upload Plugin**, upload the ZIP and activate **Mailchimp CPT Importer**.

Or clone it into `wp-content/plugins/`.

## Usage

1. Open a campaign in your browser and copy its public URL (for example, the "View this email in your browser" link of a Mailchimp campaign).
2. In WordPress, go to **Newsletters > Import Newsletter**.
3. Paste the URL and click **Import Newsletter**.
4. Click **Review and publish it** in the success message, check the draft and publish it.

## Customization

The `mcpt_newsletter_post_type_args` filter receives the arguments passed to `register_post_type()`. Use it to change the URL slug (for example, if a page already uses `/newsletter/`), turn off the archive or enable the block editor:

```php
add_filter( 'mcpt_newsletter_post_type_args', function ( $args ) {
    $args['rewrite']      = array( 'slug' => 'email-archive' );
    $args['show_in_rest'] = true;
    return $args;
} );
```

After changing the slug, go to **Settings > Permalinks** and click **Save Changes** to refresh the rewrite rules.

## Limitations

- The body keeps the email's layout tables and inline styles, so the post looks like the email. `wp_kses_post()` may drop some inline CSS properties.
- The first image on the page becomes the featured image, even if it's a logo.
- Only the featured image is downloaded. Other images in the content still load from the original host (for Mailchimp, its CDN).
- URLs on local or private networks are refused, because the plugin fetches with `wp_safe_remote_get()`.
- It imports one URL at a time.

## Changelog

### 0.3.0

- New `mcpt_newsletter_post_type_args` filter to customize the post type, such as its URL slug or archive. The defaults are unchanged.

### 0.2.0

- Success and error messages now appear on the import screen.
- The import checks the `manage_options` capability, not just the nonce.
- Rewrite rules are flushed on activation, so `/newsletter/` no longer returns 404 until permalinks are saved.
- Cleaner content: no `<body>` tag, scripts or styles, and relative URLs are made absolute.
- Title and featured image fallbacks. Pages without a charset are read as UTF-8.
- The admin CSS loads only on the import screen. The text domain is now `mailchimp-cpt`.

### 0.1.1

- Imported newsletters are saved as drafts.

## File structure

```text
mailchimp-cpt.php               Main plugin file (loads includes, activation hooks, admin styles)
includes/cpt-newsletter.php     Registers the "newsletter" post type
includes/mailchimp-importer.php Fetches, parses and imports a newsletter URL
admin/import-page.php           "Import Newsletter" screen and form handler
assets/css/admin-style.css      Admin styles
readme.txt                      WordPress-style readme
LICENSE                         GNU GPL v2
```

## Author

**Rolando Escobar**, WordPress developer. [rolandowp.com](https://rolandowp.com)

## License

GPL-2.0-or-later. See [LICENSE](LICENSE).
