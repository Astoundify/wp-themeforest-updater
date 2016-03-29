<?php
if ( ! class_exists( 'Astoundify_ThemeForest_Updater' ) ) :
/**
 * @package Astoundify_ThemeForest_Updater
 */
class Astoundify_ThemeForest_Updater {

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
	 * The strings used for any output in the drop-ins.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @var array
	 */
	public static $strings = array();

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
	 * Set things up.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 * @codeCoverageIgnore
	 */
	public static function init() {
		self::includes();
	}

	/**
	 * Set the strings to be used inside the other drop in files.
	 *
	 * @since 1.0.0
	 *
	 * @return self::$strings
	 */
	public static function set_strings( $strings = array() ) {
		$defaults = array(
			'cheating' => 'Cheating?',
			'no-token' => 'An API token is required.',
			'api-error' => 'API error.',
			'api-connected' => 'Connected',
			'api-disconnected' => 'Disconnected',
		);

		$strings = wp_parse_args( $strings, $defaults );

		self::$strings = $strings;
	}

	/**
	 * Get strings.
	 *
	 * Set the defaults if none are available.
	 *
	 * @since 1.0.0
	 * @return self::$strings
	 */
	public static function get_strings() {
		if ( empty( self::$strings ) ) {
			self::set_strings();
		}

		return self::$strings;
	}

	/**
	 * Include necessary files.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 * @codeCoverageIgnore
	 */
	public static function includes() {
		require_once( dirname( __FILE__ ) . '/class-envato-market-api.php' );
		require_once( dirname( __FILE__ ) . '/class-theme-updater.php' );
	}

}
endif;
