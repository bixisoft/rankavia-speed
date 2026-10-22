<?php
/**
 * Opt-in script loading strategy for explicitly selected handles.
 *
 * @package RankaviaSpeed
 */

declare( strict_types=1 );

namespace Bixisoft\RankaviaSpeed\Modules\Assets;

use Bixisoft\RankaviaSpeed\Vendor\Core\Container;
use Bixisoft\RankaviaSpeed\Vendor\Core\Contracts\Module;
use Bixisoft\RankaviaSpeed\Vendor\Core\Options;

/**
 * Uses WordPress's dependency-aware defer strategy.
 */
final class Assets_Module implements Module {
	/**
	 * Whitelisted handles for this request.
	 *
	 * @var array<int, string>
	 */
	private array $handles = array();

	/**
	 * Module id.
	 *
	 * @return string
	 */
	public function id(): string {
		return 'assets';
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
	 * Read the whitelist and attach a front-end hook only when nonempty.
	 *
	 * @param Container $container Shared container.
	 * @return void
	 */
	public function boot( Container $container ): void {
		$options = $container->get( 'options' );
		if ( ! $options instanceof Options ) {
			return;
		}
		$this->handles = self::parse_handles( $options->get( 'defer_handles', '' ) );
		if ( array() !== $this->handles ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'defer_selected_scripts' ), 1000 );
		}
	}

	/**
	 * Parse a bounded, comma/newline separated list of WordPress script handles.
	 *
	 * @param mixed $input Submitted field or stored option.
	 * @return array<int, string>
	 */
	public static function parse_handles( $input ): array {
		if ( ! is_string( $input ) || strlen( $input ) > 1024 ) {
			return array();
		}
		$parts = preg_split( '/[,\r\n]+/', $input );
		if ( false === $parts ) {
			return array();
		}
		$handles = array();
		foreach ( $parts as $part ) {
			$handle = trim( $part );
			if ( '' === $handle ) {
				continue;
			}
			if ( 1 !== preg_match( '/\A[a-zA-Z0-9_-]{1,64}\z/D', $handle ) ) {
				return array();
			}
			$handles[ $handle ] = $handle;
			if ( count( $handles ) > 20 ) {
				return array();
			}
		}
		return array_values( $handles );
	}

	/**
	 * Apply native defer data to selected enqueued handles only.
	 *
	 * @return void
	 */
	public function defer_selected_scripts(): void {
		if ( is_admin() || is_user_logged_in() || wp_doing_ajax()
			|| ( defined( 'REST_REQUEST' ) && REST_REQUEST )
			|| is_feed() || ! is_singular()
			|| class_exists( 'WooCommerce', false ) || defined( 'WC_VERSION' ) ) {
			return;
		}
		foreach ( $this->handles as $handle ) {
			if ( self::protected_handle( $handle ) || ! wp_script_is( $handle, 'enqueued' ) ) {
				continue;
			}
			$scripts = wp_scripts();
			if ( ! isset( $scripts->registered[ $handle ] ) || empty( $scripts->registered[ $handle ]->src )
				|| $scripts->get_data( $handle, 'strategy' ) ) {
				continue;
			}
			wp_script_add_data( $handle, 'strategy', 'defer' );
		}
	}

	/**
	 * Keep core and commerce dependency roots on their existing strategies.
	 *
	 * @param string $handle Script handle.
	 * @return bool
	 */
	private static function protected_handle( string $handle ): bool {
		return 0 === strpos( $handle, 'wp-' ) || 0 === strpos( $handle, 'wc-' )
			|| in_array( $handle, array( 'jquery', 'jquery-core', 'jquery-migrate', 'underscore', 'backbone', 'react', 'react-dom', 'heartbeat', 'admin-bar', 'woocommerce' ), true );
	}
}
