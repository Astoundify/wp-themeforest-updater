<?php
/**
 * Use the drop-in plugin for an Astoundify WordPress theme.
 *
 * Create a step in the setup guide and saves the token.
 */

class Example_Astoundify_Theme_Updates {

	public function __construct() {
		$this->option = 'marketify_themeforest_updater_token';

		add_action( 'after_setup_theme', array( $this, 'filter_setup_guide' ), -1 );
	}

	public function get_token() {
		return get_option( $this->option );
	}

	public function filter_setup_guide() {
		add_filter( 'astoundify_themeforest_updater', array( $this, 'get_token' ) );
		add_filter( 'marketify_setup_steps', array( $this, 'add_setup_step' ) );
		add_filter( 'marketify_setup_step_updates_file', array( $this, 'set_updates_file' ) );

		add_action( 'wp_ajax_marketify_set_token', array( $this, 'set_token' ) );
	}

	public function add_setup_step( $steps ) {
		$completed = get_option( $this->option, false ) ? true : false;

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

		wp_send_json_success( array(
			'token' => $token
		) );

		exit();
	}
	
}

new Example_Astoundify_Theme_Updates();
