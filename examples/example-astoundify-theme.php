<?php
/**
 * Use the drop-in plugin for an Astoundify WordPress theme.
 *
 * Create a step in the setup guide and saves the token.
 */

class Example_Astoundify_Theme_Updates {

	public $api;

	public function __construct() {
		// the key our token is stored in
		$this->option = 'marketify_themeforest_updater_token';

		// start the updater
		$updater = Astoundify_ThemeForest_Updater::instance();
		$updater::set_strings( array(
			'cheating' => 'Cheating?',
			'no-token' => 'An API token is required.',
			'api-error' => 'API error.',
			'api-connected' => 'Connected',
			'api-disconnected' => 'Disconnected'
		) );

		// set a filter for the token
		add_filter( 'astoundify_themeforest_updater', array( $this, 'get_token' ) );

		// init the api so it has a token value
		$this->api = Astoundify_Envato_Market_API::instance();

		// add interface to admin
		add_action( 'after_setup_theme', array( $this, 'filter_setup_guide' ), -1 );
	}

	public function get_token() {
		return get_option( $this->option );
	}

	public function filter_setup_guide() {
		add_filter( 'marketify_setup_steps', array( $this, 'add_setup_step' ) );
		add_filter( 'marketify_setup_step_updates_file', array( $this, 'set_updates_file' ) );

		add_action( 'wp_ajax_marketify_set_token', array( $this, 'set_token' ) );
	}

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

	public function set_updates_file( $file ) {
		$file = dirname( __FILE__ ) . '/example-astoundify-step-updates.php';

		return $file;
	}

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

new Example_Astoundify_Theme_Updates();
