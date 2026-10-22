<?php
/**
 * Settings page for Rankavia Speed.
 *
 * @package RankaviaSpeed
 */

declare( strict_types=1 );

namespace Bixisoft\RankaviaSpeed\Modules\Admin;

use Bixisoft\RankaviaSpeed\Vendor\Core\Container;
use Bixisoft\RankaviaSpeed\Vendor\Core\Contracts\Module;
use Bixisoft\RankaviaSpeed\Vendor\Core\Options;
use Bixisoft\RankaviaSpeed\Plugin;
use Bixisoft\RankaviaSpeed\Modules\Assets\Assets_Module;
use Bixisoft\RankaviaSpeed\Modules\ResourceHints\Resource_Hints_Module;

/**
 * Settings API page, nested under the Rankavia menu when that plugin is active.
 */
final class Admin_Module implements Module {

	public const MENU_SLUG = 'rankavia-speed';

	/**
	 * Shared container.
	 *
	 * @var Container
	 */
	private Container $container;

	/**
	 * Module id.
	 *
	 * @return string
	 */
	public function id(): string {
		return 'admin';
	}

	/**
	 * Keep the container.
	 *
	 * @param Container $container Shared container.
	 * @return void
	 */
	public function register( Container $container ): void {
		$this->container = $container;
	}

	/**
	 * Add admin hooks.
	 *
	 * @param Container $container Shared container.
	 * @return void
	 */
	public function boot( Container $container ): void {
		$this->container = $container;
		// Priority 20 so the Rankavia top-level menu (priority 10) exists before we attach a submenu to it.
		add_action( 'admin_menu', array( $this, 'register_menu' ), 20 );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Menu entry: a submenu of Rankavia when its menu is registered, otherwise a top-level page.
	 *
	 * @return void
	 */
	public function register_menu(): void {
		global $admin_page_hooks;

		if ( isset( $admin_page_hooks['rankavia'] ) ) {
			add_submenu_page(
				'rankavia',
				__( 'Speed', 'rankavia-speed' ),
				__( 'Speed', 'rankavia-speed' ),
				'manage_options',
				self::MENU_SLUG,
				array( $this, 'render' )
			);
			return;
		}

		add_menu_page(
			__( 'Rankavia Speed', 'rankavia-speed' ),
			__( 'Rankavia Speed', 'rankavia-speed' ),
			'manage_options',
			self::MENU_SLUG,
			array( $this, 'render' ),
			'dashicons-performance',
			59
		);
	}

	/**
	 * Settings API registration.
	 *
	 * @return void
	 */
	public function register_settings(): void {
		register_setting(
			'rankavia_speed',
			Plugin::OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize' ),
				'default'           => Plugin::default_settings(),
			)
		);

		add_settings_section(
			'rankavia_speed_cleanup',
			__( 'Cleanup', 'rankavia-speed' ),
			static function (): void {
				echo '<p>' . esc_html__( 'Remove scripts and links WordPress adds to every page that most sites never use.', 'rankavia-speed' ) . '</p>';
			},
			self::MENU_SLUG
		);

		$fields = array(
			'remove_emoji'      => __( 'Remove the emoji detection script and styles', 'rankavia-speed' ),
			'clean_head_links'  => __( 'Remove RSD, Windows Live Writer, generator and shortlink head links', 'rankavia-speed' ),
			'disable_embeds_js' => __( 'Do not load the oEmbed script (embedding other sites into your posts keeps working)', 'rankavia-speed' ),
			'disable_dashicons' => __( 'Do not load Dashicons for logged-out visitors (some themes may use these icons)', 'rankavia-speed' ),
			'restrict_prefetch' => __( 'Restrict WordPress Core prefetch to public posts/pages and exclude common private paths (WordPress 6.8+)', 'rankavia-speed' ),
		);

		foreach ( $fields as $key => $label ) {
			add_settings_field(
				'rankavia_speed_' . $key,
				$label,
				array( $this, 'render_checkbox' ),
				self::MENU_SLUG,
				'rankavia_speed_cleanup',
				array(
					'key'       => $key,
					'label_for' => 'rankavia_speed_' . $key,
				)
			);
		}
		add_settings_field(
			'rankavia_speed_defer_handles',
			__( 'Defer selected script handles', 'rankavia-speed' ),
			array( $this, 'render_defer_handles' ),
			self::MENU_SLUG,
			'rankavia_speed_cleanup'
		);
		add_settings_field(
			'rankavia_speed_preconnect_origins',
			__( 'Preconnect to selected external origins', 'rankavia-speed' ),
			array( $this, 'render_preconnect_origins' ),
			self::MENU_SLUG,
			'rankavia_speed_cleanup'
		);
	}

	/**
	 * Keep only known keys and a bounded list of script handles.
	 *
	 * @param mixed $input Raw submitted value.
	 * @return array<string, bool|string>
	 */
	public function sanitize( $input ): array {
		$clean = array();
		foreach ( array( 'remove_emoji', 'clean_head_links', 'disable_embeds_js', 'disable_dashicons', 'restrict_prefetch' ) as $key ) {
			$clean[ $key ] = is_array( $input ) && ! empty( $input[ $key ] );
		}
		$handles                     = Assets_Module::parse_handles( is_array( $input ) && isset( $input['defer_handles'] ) ? $input['defer_handles'] : '' );
		$clean['defer_handles']      = implode( "\n", $handles );
		$origins                     = Resource_Hints_Module::parse_origins( is_array( $input ) && isset( $input['preconnect_origins'] ) ? $input['preconnect_origins'] : '' );
		$clean['preconnect_origins'] = implode( "\n", $origins );
		return $clean;
	}

	/**
	 * Render the explicit browser-origin opt-in.
	 *
	 * @return void
	 */
	public function render_preconnect_origins(): void {
		$options = $this->container->get( 'options' );
		$value   = $options instanceof Options ? $options->get( 'preconnect_origins', '' ) : '';
		printf(
			'<textarea id="rankavia_speed_preconnect_origins" name="%1$s[preconnect_origins]" rows="4" cols="50">%2$s</textarea><p class="description">%3$s</p>',
			esc_attr( Plugin::OPTION_NAME ),
			esc_textarea( is_string( $value ) ? $value : '' ),
			esc_html__( 'Up to five HTTPS origins, one per line or comma-separated. Visitors\' browsers contact these operator-selected origins before a resource is requested. Leave empty to add no preconnections.', 'rankavia-speed' )
		);
	}

	/**
	 * Render the opt-in script handle whitelist.
	 *
	 * @return void
	 */
	public function render_defer_handles(): void {
		$options = $this->container->get( 'options' );
		$value   = $options instanceof Options ? $options->get( 'defer_handles', '' ) : '';
		printf(
			'<textarea id="rankavia_speed_defer_handles" name="%1$s[defer_handles]" rows="4" cols="50">%2$s</textarea><p class="description">%3$s</p>',
			esc_attr( Plugin::OPTION_NAME ),
			esc_textarea( is_string( $value ) ? $value : '' ),
			esc_html__( 'One script handle per line or comma-separated. Changing script order can break theme or plugin features. Test each handle before adding another.', 'rankavia-speed' )
		);
	}

	/**
	 * One checkbox field.
	 *
	 * @param array{key: string, label_for: string} $args Field arguments.
	 * @return void
	 */
	public function render_checkbox( array $args ): void {
		$options = $this->container->get( 'options' );
		$checked = $options instanceof Options ? (bool) $options->get( $args['key'], false ) : false;

		printf(
			'<input type="checkbox" id="%1$s" name="%2$s[%3$s]" value="1" %4$s />',
			esc_attr( $args['label_for'] ),
			esc_attr( Plugin::OPTION_NAME ),
			esc_attr( $args['key'] ),
			checked( $checked, true, false )
		);
	}

	/**
	 * Page markup.
	 *
	 * @return void
	 */
	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		echo '<div class="wrap"><h1>' . esc_html__( 'Rankavia Speed', 'rankavia-speed' ) . '</h1>';
		echo '<form action="options.php" method="post">';
		settings_fields( 'rankavia_speed' );
		do_settings_sections( self::MENU_SLUG );
		submit_button();
		echo '</form></div>';
	}
}
