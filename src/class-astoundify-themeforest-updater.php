<?php
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
	 * Include necessary files.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 * @codeCoverageIgnore
	 */
	public static function includes() {
		require_once( dirname( __FILE__ ) . '/includes/class-envato-market-api.php' );
		require_once( dirname( __FILE__ ) . '/includes/class-theme-updater.php' );
	}

}
add_action( 'plugins_loaded', array( 'Astoundify_ThemeForest_Updater', 'instance' ), 999 );
