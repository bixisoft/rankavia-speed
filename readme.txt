=== Rankavia Speed – Performance Controls ===
Contributors: bixisoft
Tags: performance, page speed, optimization, script defer, resource hints
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Focused WordPress performance controls. Companion to Rankavia.

== Description ==

Rankavia Speed offers WordPress cleanup and optional asset-loading controls. Results depend on the site's theme, plugins, hosting and traffic. Test optional controls on your site before keeping them enabled.

This is an early development release. It includes emoji script and legacy head-link cleanup, plus the optional Dashicons, script defer, Core prefetch restriction and preconnect controls described below. Page caching, lazy-loading controls, LCP preloading, a separate speculative-loading engine, and font and image optimisation are not included.

The optional Dashicons setting removes its stylesheet for logged-out visitors on the front end. It is off by default because themes and plugins may use Dashicons for public-facing icons. Admin pages and logged-in visitors keep the stylesheet.

The script defer field is empty by default. Add only script handles you have tested: a changed loading order can break theme or plugin features. Rankavia Speed asks WordPress to use its native dependency-aware defer strategy for the selected handles on anonymous singular pages. WordPress core handles are excluded, and the feature is bypassed when WooCommerce is active. It does not minify or combine scripts.

On WordPress 6.8 and later, WordPress Core already provides conservative link prefetching on eligible sites. The optional Rankavia Speed restriction keeps Core's rules to anonymous public posts/pages and adds common search, account, cart, checkout and download path exclusions. It does not print a second rules tag or enable prefetching if Core or another plugin has disabled it. On WordPress 6.5–6.7 this setting has no effect. Site-specific private or transactional links need Core's no-prefetch opt-out class or an exclusion filter.

The optional preconnect origin list is empty by default. An administrator may enter up to five HTTPS origins to be added to WordPress's native resource hints on anonymous public posts/pages. The visitor's browser may contact those selected origins before navigation; the plugin does not fetch them server-side. Use only origins you operate or trust. Existing hints and crossorigin attributes are preserved. DNS resolution and ownership of an entered domain are not verified by the plugin.

== External services ==

The plugin makes no server-side HTTP requests. If an administrator configures preconnect origins, a visitor's browser may connect to those selected external origins. No origins are configured by default.

== Changelog ==

= Unreleased =
* Add an optional, default-off Dashicons cleanup for logged-out front-end visitors.
* Add an opt-in script handle list for WordPress native defer on eligible front-end pages.
* Add optional restrictions to WordPress Core's conservative prefetch rules on WordPress 6.8 and later.
* Add a bounded, opt-in preconnect origin list using WordPress's native resource hints.

= 0.1.0 =
* Initial release: emoji script removal and legacy head-link cleanup with a settings page.
