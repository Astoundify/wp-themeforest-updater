<?php
if ( ! class_exists( 'Astoundify_Theme_Updater' ) ) :
/**
 * Shims in our premium automatic updates alongside the standard
 * WordPress.org updates.
 *
 * @package Astoundify_Theme_Updater
 */
class Astoundify_Theme_Updater {

	/**
	 * The single class instance.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var object
	*/
	private static $_instance = null;

	/**
	 * Main Envato_Market_API Instance
	 *
	 * Ensures only one instance of this class exists in memory at any one time.
	 *
	 * @see Astoundify_Theme_Updater()
	 *
	 * @since 1.0.0
	 * @static
	 * @return object The one true Astoundify_Envato_Market_API.
	 * @codeCoverageIgnore
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
			self::init();
		}

		return self::$_instance;
	}

	/**
	 * Themes purchased on ThemeForest.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var array
	 */
	private static $purchased_themes = array();

	/**
	 * Themes that are currently installed.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var array
	 */
	private static $installed_themes = array();

	/**
	 * Start things.
	 *
	 * @since 1.0.0
	 * @access private
	 * @codeCoverageIgnore
	 */
	public static function init() {
		self::init_actions();
	}

	/**
	 * Add hooks and filters.
	 *
	 * @since 1.0.0
	 * @access private
	 * @codeCoverageIgnore
	 */
	private static function init_actions() {
		add_filter( 'site_transient_update_themes', array( __CLASS__, 'check_theme_updates' ) );
		add_filter( 'delete_site_transient_update_themes', array( __CLASS__, 'delete_theme_update_transient' ) );
		add_action( 'load-update-core.php', array( __CLASS__, 'delete_theme_update_transient' ) );
		add_action( 'load-themes.php', array( __CLASS__, 'delete_theme_update_transient' ) );

		// Deferred Download.
		add_action( 'upgrader_package_options', array( __CLASS__, 'maybe_deferred_download' ), 99 );
	}

	public static function delete_theme_update_transient() {
		delete_transient( 'atu_installed_themes' );
		delete_transient( 'atu_purchased_themes' );
		delete_transient( 'update_themes' );
	}

	/**
	 * Check for theme updates.
	 *
	 * Only check for themes that have both been installed and purchased.
	 *
	 * @since 1.0.0
	 *
	 * @param object $transient
	 * @return object $transient
	 */
	public static function check_theme_updates( $transient ) {
		if ( ! isset( $transient->checked ) ) {
			return $transient;
		}

		$api = Astoundify_Envato_Market_API::instance();

		if ( ! $api->token ) {
			self::delete_theme_update_transient();

			return $transient;
		}

		$installed_and_purchased = self::get_installed_and_purchased_themes();

		foreach ( $installed_and_purchased as $slug => $items ) {
			$installed_version = $items[ 'installed' ]->get( 'Version' );
			$purchased_version = $items[ 'purchased' ][ 'version' ];

			if ( version_compare( $installed_version, $purchased_version, '<' ) ) {
				$transient->response[ $slug ] = array(
					'theme'       => $slug,
					'new_version' => $purchased_version,
					'url'         => $items[ 'installed' ]->get_template_directory_uri() . '/readme.txt',
					'package'     => $api->deferred_download( $items[ 'purchased' ][ 'id' ] ),
				);
			}
		}

		return $transient;
	}

	/**
	 * Get a list of themes that are both installed and purchased.
	 *
	 * @since 1.0.0
	 *
	 * @return array self::$installed_purchased_themes
	 */
	public static function get_installed_and_purchased_themes() {
		$installed = self::get_installed_themes();
		$purchased = self::get_purchased_themes();

		$installed_and_purchased = array();

		foreach ( $installed as $theme_slug => $theme ) {
			if ( array_key_exists( $theme_slug, $purchased ) ) {
				$installed_and_purchased[ $theme_slug ] = array(
					'installed' => $theme,
					'purchased' => $purchased[ $theme_slug ]
				);
			}
		}

		return $installed_and_purchased;
	}

	/**
	 * Get a list of all purchased themes.
	 *
	 * @since 1.0.0
	 *
	 * @return array self::$purchased_themes
	 */
	public static function get_purchased_themes() {
		if ( ! empty( self::$purchased_themes ) ) {
			return self::$purchased_themes;
		}

		$api = Astoundify_Envato_Market_API::instance();

		if ( ! $api->token ) {
			self::delete_theme_update_transient();

			return;
		}

		// @see https://core.trac.wordpress.org/ticket/15058
		$transient = 'atu_purchased_themes';

		self::$purchased_themes = get_transient( $transient );

		if ( false === self::$purchased_themes ) {
			self::$purchased_themes = $api->themes();

			set_transient( $transient, self::$purchased_themes, DAY_IN_SECONDS );
		}

		return self::$purchased_themes;
	}

	/**
	 * Get a list of all installed themes.
	 *
	 * @since 1.0.0
	 *
	 * @return array self::$installed_themes
	 */
	public static function get_installed_themes() {
		if ( ! empty( self::$installed_themes ) ) {
			return self::$installed_themes;
		}

		// @see https://core.trac.wordpress.org/ticket/15058
		$transient = 'atu_installed_themes';

		self::$installed_themes = get_transient( $transient );

		if ( false === self::$installed_themes ) {
			self::$installed_themes = wp_get_themes();

			set_transient( $transient, self::$installed_themes, DAY_IN_SECONDS );
		}

		return self::$installed_themes;
	}

	/**
	* Defers building the API download url until the last responsible moment to limit file requests.
	*
	* Filter the package options before running an update.
	*
	* @since 1.0.0
	*
	* @param array $options {
	*     Options used by the upgrader.
	*
	*     @type string $package                     Package for update.
	*     @type string $destination                 Update location.
	*     @type bool   $clear_destination           Clear the destination resource.
	*     @type bool   $clear_working               Clear the working resource.
	*     @type bool   $abort_if_destination_exists Abort if the Destination directory exists.
	*     @type bool   $is_multi                    Whether the upgrader is running multiple times.
	*     @type array  $hook_extra                  Extra hook arguments.
	* }
	*/
	public static function maybe_deferred_download( $options ) {
		$package = $options['package'];

		if ( false !== strrpos( $package, 'deferred_download' ) && false !== strrpos( $package, 'item_id' ) ) {
			parse_str( parse_url( $package, PHP_URL_QUERY ), $vars );

			$api = Astoundify_Envato_Market_API::instance();

			if ( $vars['item_id'] ) {
				$options['package'] = $api->download( $vars['item_id'], $args );
			}
		}

		return $options;
	}
}
endif;

Astoundify_Theme_Updater::instance();
