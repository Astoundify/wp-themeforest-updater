<?php
/**
 * Example for integrating with an Astoundify theme.
 *
 * It is up to the theme to implement the token storing and setting. 
 */

// update this to where you add the updater class
require_once( dirname( __FILE__ ) . '/../updater/class-astoundify-themeforest-updater.php' );

class Example_Astoundify_Theme_Updates {

	/**
	 * Envato Market API
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @var object
	 */
	public $api;

	/**
	 * Set up automatic updates for an Astoundify theme.
	 *
	 * Sets the token option key, starts the updater (with translated strings) and 
	 * starts the API once the token filter has been added.
	 *
	 * The order these instances are first created is important.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		// the key our token is stored in
		$this->option = 'marketify_themeforest_updater_token';

		// start the updater
		$updater = Astoundify_ThemeForest_Updater::instance();
		$updater::set_strings( array(
			'cheating' => __( 'Cheating?', 'marketify' ),
			'no-token' => __( 'An API token is required.', 'marketify' ),
			'api-error' => __( 'API error.', 'marketify' ),
			'api-connected' => __( 'Connected', 'marketify' ),
			'api-disconnected' => __( 'Disconnected', 'marketify' )
		) );

		// set a filter for the token
		add_filter( 'astoundify_themeforest_updater', array( $this, 'get_token' ) );

		// init the api so it has a token value
		$this->api = Astoundify_Envato_Market_API::instance();

		// add interface to admin
		add_action( 'after_setup_theme', array( $this, 'filter_setup_guide' ), -1 );
	}

	/**
	 * Get the token stored in our set location.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed
	 */
	public function get_token() {
		return get_option( $this->option, null );
	}

	/**
	 * Filter the setup guide. These need to be hooked in to a very early `after_setup_theme`
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function filter_setup_guide() {
		add_filter( 'marketify_setup_steps', array( $this, 'add_setup_step' ) );
		add_filter( 'marketify_setup_step_updates_file', array( $this, 'set_updates_file' ) );

		add_action( 'wp_ajax_marketify_set_token', array( $this, 'set_token' ) );
	}

	/**
	 * Add a step to the setup guide.
	 *
	 * @since 1.0.0
	 *
	 * @param array $steps
	 * @return array $steps
	 */
	public function add_setup_step( $steps ) {
		$completed = get_option( $this->option, false ) && $this->api->can_make_request_with_token() ? true : false;

		$step = array(
			'title' => 'Automatic Updates',
			'completed' => $completed,
			'documentation' => array()
		);

		$steps = array( 'updates' => $step ) + $steps;

		return $steps;
	}

	/**
	 * Set the file location of this steps content.
	 *
	 * @since 1.0.0
	 *
	 * @param string $file
	 * @return string $file
	 */
	public function set_updates_file( $file ) {
		$file = dirname( __FILE__ ) . '/example-astoundify-step-updates.php';

		return $file;
	}

	/**
	 * AJAX response when a token is set in the Setup Guide.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function set_token() {
		check_ajax_referer( 'marketify-add-token', 'security' );

		$token = isset( $_POST[ 'token' ] ) ? esc_attr( $_POST[ 'token' ] ) : false;

		if ( ! $token ) {
			wp_send_json_error();
		}

		update_option( $this->option, $token );

		// hotswap the token
		$this->api->token = $token;

		wp_send_json_success( array(
			'token' => $token,
			'can_request' => $this->api->can_make_request_with_token(),
			'request_label' => $this->api->connection_status_label()
		) );

		exit();
	}
	
}

// autorun
new Example_Astoundify_Theme_Updates();
