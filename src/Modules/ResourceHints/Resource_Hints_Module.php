<?php
/**
 * Operator-selected browser preconnect origins.
 *
 * @package RankaviaSpeed
 */

declare( strict_types=1 );

namespace Bixisoft\RankaviaSpeed\Modules\ResourceHints;

use Bixisoft\RankaviaSpeed\Vendor\Core\Container;
use Bixisoft\RankaviaSpeed\Vendor\Core\Contracts\Module;
use Bixisoft\RankaviaSpeed\Vendor\Core\Options;

/**
 * Adds a bounded set of HTTPS origins to WordPress's native resource hints.
 */
final class Resource_Hints_Module implements Module {
	/**
	 * Parsed origins for this request.
	 *
	 * @var array<int, string>
	 */
	private array $origins = array();

	/**
	 * Module id.
	 *
	 * @return string
	 */
	public function id(): string {
		return 'resource_hints';
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
	 * Attach one native filter only when origins were explicitly saved.
	 *
	 * @param Container $container Shared container.
	 * @return void
	 */
	public function boot( Container $container ): void {
		$options = $container->get( 'options' );
		if ( ! $options instanceof Options ) {
			return;
		}
		$this->origins = self::parse_origins( $options->get( 'preconnect_origins', '' ) );
		if ( array() !== $this->origins ) {
			add_filter( 'wp_resource_hints', array( $this, 'add_preconnect_origins' ), 10, 2 );
		}
	}

	/**
	 * Accept up to five public-looking ASCII HTTPS origins, with no path or credentials.
	 * This is a syntax check, not a DNS lookup.
	 *
	 * @param mixed $input Admin text or stored value.
	 * @return array<int, string>
	 */
	public static function parse_origins( $input ): array {
		if ( ! is_string( $input ) || strlen( $input ) > 1024 ) {
			return array();
		}
		$parts = preg_split( '/[,\r\n]+/', $input );
		if ( false === $parts ) {
			return array();
		}
		$origins = array();
		foreach ( $parts as $part ) {
			$origin = trim( $part );
			if ( '' === $origin ) {
				continue;
			}
			if ( 1 !== preg_match( '#\Ahttps://((?:[a-zA-Z0-9-]+\.)+[a-zA-Z]{2,63})/?\z#Di', $origin, $match ) ) {
				return array();
			}
			$host = strtolower( $match[1] );
			if ( strlen( $host ) > 253 || self::blocked_host( $host ) ) {
				return array();
			}
			$origins[ 'https://' . $host ] = 'https://' . $host;
			if ( count( $origins ) > 5 ) {
				return array();
			}
		}
		return array_values( $origins );
	}

	/**
	 * Append hints without changing existing attributes such as crossorigin.
	 *
	 * @param array<int, array<string, mixed>|string> $urls          Existing hints.
	 * @param string                                  $relation_type Hint type.
	 * @return array<int, array<string, mixed>|string>
	 */
	public function add_preconnect_origins( array $urls, string $relation_type ): array {
		if ( 'preconnect' !== $relation_type || is_admin() || is_user_logged_in() || wp_doing_ajax()
			|| ( defined( 'REST_REQUEST' ) && REST_REQUEST )
			|| is_feed() || is_preview() || ! is_singular( array( 'post', 'page' ) )
			|| 'publish' !== get_post_status() || post_password_required()
			|| class_exists( 'WooCommerce', false ) || defined( 'WC_VERSION' ) ) {
			return $urls;
		}
		$seen = array();
		foreach ( $urls as $hint ) {
			$href = is_array( $hint ) && isset( $hint['href'] ) ? $hint['href'] : $hint;
			if ( is_string( $href ) ) {
				$parsed = wp_parse_url( $href );
				if ( is_array( $parsed ) && isset( $parsed['scheme'], $parsed['host'] ) && 'https' === strtolower( $parsed['scheme'] ) ) {
					$seen[ 'https://' . strtolower( $parsed['host'] ) ] = true;
				}
			}
		}
		foreach ( $this->origins as $origin ) {
			if ( ! isset( $seen[ $origin ] ) ) {
				$urls[] = $origin;
			}
		}
		return $urls;
	}

	/**
	 * Reject host labels and suffixes commonly used outside public DNS.
	 *
	 * @param string $host Lowercase host.
	 * @return bool
	 */
	private static function blocked_host( string $host ): bool {
		foreach ( explode( '.', $host ) as $label ) {
			if ( strlen( $label ) > 63 || '-' === $label[0] || '-' === substr( $label, -1 ) ) {
				return true;
			}
		}
		$suffix = substr( $host, strrpos( $host, '.' ) + 1 );
		return in_array( $suffix, array( 'local', 'localhost', 'internal', 'lan', 'home', 'test', 'invalid', 'example', 'arpa', 'onion' ), true );
	}
}
