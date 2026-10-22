<?php
/**
 * Plugin entry for Rankavia Speed.
 *
 * @package RankaviaSpeed
 */

declare( strict_types=1 );

namespace Bixisoft\RankaviaSpeed;

use Bixisoft\RankaviaSpeed\Vendor\Core\Kernel;
use Bixisoft\RankaviaSpeed\Vendor\Core\Options;
use Bixisoft\RankaviaSpeed\Modules\Admin\Admin_Module;
use Bixisoft\RankaviaSpeed\Modules\Assets\Assets_Module;
use Bixisoft\RankaviaSpeed\Modules\Cleanup\Cleanup_Module;
use Bixisoft\RankaviaSpeed\Modules\Speculation\Speculation_Module;
use Bixisoft\RankaviaSpeed\Modules\ResourceHints\Resource_Hints_Module;

/**
 * Singleton owning the kernel for this plugin.
 */
final class Plugin {

	public const OPTION_NAME = 'rankavia_speed_settings';

	/**
	 * Shared instance.
	 *
	 * @var Plugin|null
	 */
	private static ?Plugin $instance = null;

	/**
	 * Kernel with container and modules.
	 *
	 * @var Kernel
	 */
	private Kernel $kernel;

	/**
	 * Build the kernel and register modules.
	 */
	private function __construct() {
		$this->kernel = new Kernel();

		$container = $this->kernel->container();
		$container->instance( 'options', new Options( self::OPTION_NAME, self::default_settings() ) );
		$container->instance( 'version', RANKAVIA_SPEED_VERSION );

		$this->kernel
			->add( new Cleanup_Module() )
			->add( new Assets_Module() )
			->add( new Speculation_Module() )
			->add( new Resource_Hints_Module() )
			->add( new Admin_Module() );
	}

	/**
	 * Shared instance.
	 *
	 * @return Plugin
	 */
	public static function instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new Plugin();
		}
		return self::$instance;
	}

	/**
	 * Boot every enabled module.
	 *
	 * @return void
	 */
	public function boot(): void {
		$this->kernel->boot();
	}

	/**
	 * The kernel.
	 *
	 * @return Kernel
	 */
	public function kernel(): Kernel {
		return $this->kernel;
	}

	/**
	 * Activation: seed options without overwriting existing ones.
	 *
	 * @return void
	 */
	public static function activate(): void {
		add_option( self::OPTION_NAME, self::default_settings() );
		update_option( 'rankavia_speed_version', RANKAVIA_SPEED_VERSION );
	}

	/**
	 * Default settings. Everything here is safe to enable on any site.
	 *
	 * @return array<string, bool|string>
	 */
	public static function default_settings(): array {
		return array(
			'remove_emoji'       => true,
			'clean_head_links'   => true,
			'disable_embeds_js'  => false,
			'disable_dashicons'  => false,
			'defer_handles'      => '',
			'restrict_prefetch'  => false,
			'preconnect_origins' => '',
		);
	}
}
