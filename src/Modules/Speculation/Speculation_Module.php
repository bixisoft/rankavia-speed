<?php
/**
 * Optional restrictions for WordPress Core speculative loading.
 *
 * @package RankaviaSpeed
 */

declare( strict_types=1 );

namespace Bixisoft\RankaviaSpeed\Modules\Speculation;

use Bixisoft\RankaviaSpeed\Vendor\Core\Container;
use Bixisoft\RankaviaSpeed\Vendor\Core\Contracts\Module;
use Bixisoft\RankaviaSpeed\Vendor\Core\Options;

/**
 * Narrows Core's native rules; never prints a second speculation-rules tag.
 */
final class Speculation_Module implements Module {
	/**
	 * Module id.
	 *
	 * @return string
	 */
	public function id(): string {
		return 'speculation';
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
	 * Attach filters only after explicit opt-in.
	 *
	 * @param Container $container Shared container.
	 * @return void
	 */
	public function boot( Container $container ): void {
		$options = $container->get( 'options' );
		if ( ! $options instanceof Options || true !== $options->get( 'restrict_prefetch', false ) ) {
			return;
		}
		add_filter( 'wp_speculation_rules_configuration', array( $this, 'restrict_configuration' ), 100 );
		add_filter( 'wp_speculation_rules_href_exclude_paths', array( $this, 'exclude_paths' ) );
	}

	/**
	 * Preserve an earlier opt-out and constrain enabled Core output to public posts/pages.
	 *
	 * @param array<string, string>|null $configuration Core or another plugin's configuration.
	 * @return array<string, string>|null
	 */
	public function restrict_configuration( ?array $configuration ): ?array {
		if ( null === $configuration ) {
			return null;
		}
		if ( is_admin() || is_user_logged_in() || wp_doing_ajax()
			|| ( defined( 'REST_REQUEST' ) && REST_REQUEST )
			|| is_feed() || is_preview() || ! is_singular( array( 'post', 'page' ) )
			|| 'publish' !== get_post_status() || post_password_required()
			|| class_exists( 'WooCommerce', false ) || defined( 'WC_VERSION' ) ) {
			return null;
		}
		return array(
			'mode'      => 'prefetch',
			'eagerness' => 'conservative',
		);
	}

	/**
	 * Extend Core's same-origin rule with common private, transactional and download paths.
	 *
	 * @param array<int, string> $paths Existing exclusions.
	 * @return array<int, string>
	 */
	public function exclude_paths( array $paths ): array {
		$extra = array(
			'/search',
			'/search/*',
			'/account',
			'/account/*',
			'/my-account',
			'/my-account/*',
			'/cart',
			'/cart/*',
			'/checkout',
			'/checkout/*',
			'/private',
			'/private/*',
			'/download',
			'/download/*',
			'/downloads',
			'/downloads/*',
		);
		return array_values( array_unique( array_merge( $paths, $extra ) ) );
	}
}
