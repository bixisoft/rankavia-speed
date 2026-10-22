<?php
/**
 * Plugin Name:       Rankavia Speed – Performance Controls
 * Plugin URI:        https://rankavia.com/speed/
 * Description:       WordPress cleanup and opt-in script defer, preconnect hints and Core prefetch restrictions. Companion to Rankavia.
 * Version:           0.1.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            Bixisoft
 * Author URI:        https://bixisoft.com/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       rankavia-speed
 *
 * @package RankaviaSpeed
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;

define( 'RANKAVIA_SPEED_VERSION', '0.1.0' );
define( 'RANKAVIA_SPEED_FILE', __FILE__ );
define( 'RANKAVIA_SPEED_PATH', plugin_dir_path( __FILE__ ) );
define( 'RANKAVIA_SPEED_URL', plugin_dir_url( __FILE__ ) );

/**
 * Load the Composer autoloader: the plugin's own vendor/ in a built zip, or the monorepo root vendor/ in development.
 *
 * @return bool Whether an autoloader was found.
 */
function rankavia_speed_load_autoloader(): bool {
	$candidates = array(
		// A deployment or dev environment (wp-env mounts the monorepo) can point straight at an autoloader.
		defined( 'BIXISOFT_AUTOLOADER' ) ? (string) BIXISOFT_AUTOLOADER : '',
		RANKAVIA_SPEED_PATH . 'vendor/autoload.php',
		dirname( RANKAVIA_SPEED_PATH, 2 ) . '/vendor/autoload.php',
	);
	foreach ( $candidates as $autoloader ) {
		if ( '' !== $autoloader && file_exists( $autoloader ) ) {
			require_once $autoloader;
			return true;
		}
	}
	return false;
}

if ( ! rankavia_speed_load_autoloader() || ! class_exists( \Bixisoft\RankaviaSpeed\Plugin::class ) ) {
	add_action(
		'admin_notices',
		static function (): void {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Rankavia Speed could not load its dependencies. Install the plugin from a release zip, or run "composer install" in the repository.', 'rankavia-speed' ) . '</p></div>';
		}
	);
	return;
}

register_activation_hook( __FILE__, array( \Bixisoft\RankaviaSpeed\Plugin::class, 'activate' ) );

add_action(
	'plugins_loaded',
	static function (): void {
		\Bixisoft\RankaviaSpeed\Plugin::instance()->boot();
	}
);
