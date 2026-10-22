<?php
/**
 * Removes WordPress front-end bloat.
 *
 * @package RankaviaSpeed
 */

declare( strict_types=1 );

namespace Bixisoft\RankaviaSpeed\Modules\Cleanup;

use Bixisoft\RankaviaSpeed\Vendor\Core\Container;
use Bixisoft\RankaviaSpeed\Vendor\Core\Contracts\Module;
use Bixisoft\RankaviaSpeed\Vendor\Core\Options;

/**
 * Emoji scripts, legacy discovery links and the oEmbed script, each behind a setting.
 */
final class Cleanup_Module implements Module {

	/**
	 * Module id.
	 *
	 * @return string
	 */
	public function id(): string {
		return 'cleanup';
	}

	/**
	 * Nothing to bind.
	 *
	 * @param Container $container Shared container.
	 * @return void
	 */
	public function register( Container $container ): void {
		unset( $container );
	}

	/**
	 * Apply the enabled cleanups.
	 *
	 * @param Container $container Shared container.
	 * @return void
	 */
	public function boot( Container $container ): void {
		$options = $container->get( 'options' );
		if ( ! $options instanceof Options ) {
			return;
		}

		if ( (bool) $options->get( 'remove_emoji', true ) ) {
			$this->remove_emoji();
		}
		if ( (bool) $options->get( 'clean_head_links', true ) ) {
			$this->clean_head_links();
		}
		if ( (bool) $options->get( 'disable_embeds_js', false ) ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'dequeue_embed_script' ), 100 );
		}
		if ( (bool) $options->get( 'disable_dashicons', false ) ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'dequeue_dashicons' ), 100 );
		}
	}

	/**
	 * Remove the emoji detection script and styles (about 12 KB on every page).
	 *
	 * @return void
	 */
	private function remove_emoji(): void {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		add_filter( 'emoji_svg_url', '__return_false' );
	}

	/**
	 * Remove RSD, Windows Live Writer, generator and shortlink head links.
	 *
	 * @return void
	 */
	private function clean_head_links(): void {
		remove_action( 'wp_head', 'rsd_link' );
		remove_action( 'wp_head', 'wlwmanifest_link' );
		remove_action( 'wp_head', 'wp_generator' );
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	}

	/**
	 * Drop the oEmbed front-end script (embeds of this site into others keep working).
	 *
	 * @return void
	 */
	public function dequeue_embed_script(): void {
		wp_dequeue_script( 'wp-embed' );
	}

	/**
	 * Remove Dashicons only for logged-out front-end visitors.
	 *
	 * @return void
	 */
	public function dequeue_dashicons(): void {
		if ( is_admin() || is_user_logged_in() ) {
			return;
		}
		wp_dequeue_style( 'dashicons' );
	}
}
