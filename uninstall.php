<?php
/**
 * Removes every option the plugin created.
 *
 * @package RankaviaSpeed
 */

declare( strict_types=1 );

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'rankavia_speed_settings' );
delete_option( 'rankavia_speed_version' );
