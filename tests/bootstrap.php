<?php /**
 * Unit Tests Bootstrap
 */
class Astoundify_ThemeForest_Updater_Unit_Tests_Bootstrap {

	/** @var \WPJMCL_Unit_Tests_Bootstrap instance */
	protected static $instance = null;

	/** @var string directory where wordpress-tests-lib is installed */
	public $wp_tests_dir;

	/** @var string testing directory */
	public $tests_dir;

	/** @var string plugin directory */
	public $plugin_dir;

	/**
	 * Setup the unit testing environment.
	 */
	public function __construct() {
		ini_set( 'display_errors','on' );
		error_reporting( E_ALL );

		$this->tests_dir    = dirname( __FILE__ );
		$this->plugin_dir   = dirname( $this->tests_dir );
		$this->wp_tests_dir = getenv( 'WP_TESTS_DIR' ) ? getenv( 'WP_TESTS_DIR' ) : '/tmp/wordpress-tests-lib';

		// load test function so tests_add_filter() is available
		require_once( $this->wp_tests_dir . '/includes/functions.php' );

		// load plugin
		tests_add_filter( 'muplugins_loaded', array( $this, 'load_plugin' ) );

		// install plugin
		tests_add_filter( 'setup_theme', array( $this, 'install_plugin' ) );
		tests_add_filter( 'setup_theme', array( $this, 'setup_token' ) );

		// load the WP testing environment
		require_once( $this->wp_tests_dir . '/includes/bootstrap.php' );

		// load testing framework
		$this->includes();
	}

	/**
	 * Load plugin
	 */
	public function load_plugin() {
		// right now only the api is tested
		require_once( $this->plugin_dir . '/updater/class-astoundify-themeforest-updater.php' );
	}

	/**
	 * Install plugin
	 */
	public function install_plugin() {
		Astoundify_ThemeForest_Updater::instance();
	}

	/**
	 * Setup Token
	 */
	public function setup_token() {
		// Setup the token.
		$_token = getenv( 'TOKEN' );

		if ( empty( $_token ) ) {
			$path = $this->plugin_dir . '/.token';

			if ( ! file_exists( $path ) ) {
				$_token = '';
			} else {
				$_token = @file_get_contents( $path );

				if ( false === $_token ) {
					$_token = '';
				}
			}
		}

		define( 'TOKEN', $_token );
		define( 'TESTS_DATA_DIR', dirname( __FILE__ ) . '/data' );
	}

	/**
	 */
	public function includes() {
	}

	/**
	 * Get the single class instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}

Astoundify_ThemeForest_Updater_Unit_Tests_Bootstrap::instance();
