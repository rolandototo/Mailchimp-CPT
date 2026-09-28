=== Mailchimp CPT Importer ===
Contributors: rolandototo
Tags: mailchimp, newsletter, import, custom post type
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 0.3.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Imports a newsletter from a public URL, such as a Mailchimp campaign page, into a Newsletter post type as a draft.

== Description ==

The plugin registers a public `newsletter` post type with an archive at `/newsletter/` and adds a **Newsletters > Import Newsletter** screen for administrators.

Paste the public URL of a newsletter and the plugin creates a draft:

* Title: the page's `<title>`, or its first `<h1>`.
* Content: the page's body HTML, without scripts or styles, filtered with `wp_kses_post()`.
* Excerpt: the first paragraph, trimmed to 55 words.
* Featured image: the first image on the page, downloaded into the Media Library.

It reads the public HTML page, so no Mailchimp API key is needed.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/` or install the ZIP from **Plugins > Add New > Upload Plugin**.
2. Activate **Mailchimp CPT Importer**.
3. Go to **Newsletters > Import Newsletter**.

== Frequently Asked Questions ==

= Can I change the /newsletter/ URL? =

Yes. Use the `mcpt_newsletter_post_type_args` filter to change `$args['rewrite']['slug']` (or any other post type argument), then re-save **Settings > Permalinks**.

== Changelog ==

= 0.3.0 =
* New `mcpt_newsletter_post_type_args` filter to customize the post type, such as its URL slug or archive. Defaults are unchanged.

= 0.2.0 =
* Success and error messages now appear on the import screen.
* The import requires the `manage_options` capability, not just a valid nonce.
* Rewrite rules are flushed on activation, so `/newsletter/` works right away.
* Cleaner content: no `<body>` tag, scripts or styles; relative URLs made absolute.
* Title and featured image fallbacks, and pages without a charset are read as UTF-8.
* Admin CSS loads only on the import screen. Text domain is now `mailchimp-cpt`.

= 0.1.1 =
* Imported newsletters are saved as drafts.
