<?php
/**
 * Envato API class.
 *
 * @see https://github.com/envato/wp-envato-market/blob/master/inc/api.php
 *
 * @package Astoundify_ThemeForest_Updater
 * @category API
 */

if ( ! class_exists( 'Astoundify_Envato_Market_API' ) ) :
/**
 * Creates the Envato API connection.
 *
 * @class Astoundify_Envato_Market_API
 * @version 1.0.0
 * @since 1.0.0
 */
class Astoundify_Envato_Market_API {

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
	 * The Envato API personal token.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $token;

	/**
	 * Main Envato_Market_API Instance
	 *
	 * Ensures only one instance of this class exists in memory at any one time.
	 *
	 * @see Astoundify_Envato_Market_API()
	 * @uses Astoundify_Envato_Market_API::init_globals() Setup class globals.
	 * @uses Astoundify_Envato_Market_API::init_actions() Setup hooks and actions.
	 *
	 * @since 1.0.0
	 * @static
	 * @return object The one true Astoundify_Envato_Market_API.
	 * @codeCoverageIgnore
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();

			self::$_instance->init_globals();
			self::$_instance->init_actions();
		}

		return self::$_instance;
	}

	/**
	 * A dummy constructor to prevent this class from being loaded more than once.
	 *
	 * @see Envato_Market_API::instance()
	 *
	 * @since 1.0.0
	 * @access private
	 * @codeCoverageIgnore
	 */
	private function __construct() {
		/* We do nothing here! */
	}

	/**
	 * You cannot clone this class.
	 *
	 * @since 1.0.0
	 * @codeCoverageIgnore
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html( self::$strings[ 'cheating' ] ), '1.0.0' );
	}

	/**
	 * You cannot unserialize instances of this class.
	 *
	 * @since 1.0.0
	 * @codeCoverageIgnore
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html( $this->strings[ 'cheating' ] ), '1.0.0' );
	}

	/**
	 * Setup the class globals.
	 *
	 * @since 1.0.0
	 * @access private
	 * @codeCoverageIgnore
	 */
	private function init_globals() {
		$this->strings = Astoundify_ThemeForest_Updater::get_strings();
		$this->token = apply_filters( 'astoundify_themeforest_updater', null );
	}

	/**
	 * Add hooks and filters.
	 *
	 * @since 1.0.0
	 * @access private
	 * @codeCoverageIgnore
	 */
	private function init_actions() {
	}

	/**
	 * Query the Envato API.
	 *
	 * @uses wp_remote_get() To perform an HTTP request.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $url API request URL, including the request method, parameters, & file type.
	 * @param  array  $args The arguments passed to `wp_remote_get`.
	 * @return array  The HTTP response.
	 */
	public function request( $url, $args = array() ) {
		$defaults = array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $this->token,
			),
			'timeout' => 20,
		);

		$args = wp_parse_args( $args, $defaults );

		$token = trim( str_replace( 'Bearer', '', $args['headers']['Authorization'] ) );

		if ( empty( $token ) ) {
			return new WP_Error( 'api_token_error', $this->strings[ 'no-token' ] );
		}

		// Make an API request.
		$response = wp_remote_get( esc_url_raw( $url ), $args );

		// Check the response code.
		$response_code    = wp_remote_retrieve_response_code( $response );
		$response_message = wp_remote_retrieve_response_message( $response );

		if ( 200 !== $response_code && ! empty( $response_message ) ) {
			return new WP_Error( $response_code, $response_message );
		} elseif ( 200 !== $response_code ) {
			return new WP_Error( $response_code, $this->strings[ 'api-error' ] );
		} else {
			$return = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( null === $return ) {
				return new WP_Error( 'api_error', $this->strings[ 'api-error' ] );
			}

			return $return;
		}
	}

	/**
	 * Deferred item download URL.
	 *
	 * @since 1.0.0
	 *
	 * @param int $id The item ID.
	 * @return string.
	 */
	public function deferred_download( $id ) {
		if ( empty( $id ) ) {
			return '';
		}

		$args = array(
			'deferred_download' => true,
			'item_id' => $id,
		);

		$page = admin_url( 'themes.php' );

		return add_query_arg( $args, esc_url( $page ) );
	}

	/**
	 * Get the item download.
	 *
	 * @since 1.0.0
	 *
	 * @param  int   $id The item ID.
	 * @param  array $args The arguments passed to `wp_remote_get`.
	 * @return bool|array The HTTP response.
	 */
	public function download( $id, $args = array() ) {
		if ( empty( $id ) ) {
			return false;
		}

		$url = 'https://api.envato.com/v2/market/buyer/download?item_id=' . $id . '&shorten_url=true';
		$response = $this->request( $url, $args );

		// @todo Find out which errors could be returned & handle them in the UI.
		if ( is_wp_error( $response ) || empty( $response ) || ! empty( $response['error'] ) ) {
			return false;
		}

		if ( ! empty( $response['wordpress_theme'] ) ) {
			return $response['wordpress_theme'];
		}

		return false;
	}

	/**
	 * Get an item by ID and type.
	 *
	 * @since 1.0.0
	 *
	 * @param  int   $id The item ID.
	 * @param  array $args The arguments passed to `wp_remote_get`.
	 * @return array The HTTP response.
	 */
	public function item( $id, $args = array() ) {
		$url = 'https://api.envato.com/v2/market/catalog/item?id=' . $id;
		$response = $this->request( $url, $args );

		if ( is_wp_error( $response ) || empty( $response ) ) {
			return false;
		}

		if ( ! empty( $response['wordpress_theme_metadata'] ) ) {
			return $this->normalize_theme( $response );
		}

		return false;
	}

	/**
	 * Get the list of available themes.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $args The arguments passed to `wp_remote_get`.
	 * @return array The HTTP response.
	 */
	public function themes( $args = array() ) {
		$themes = array();

		$url = 'https://api.envato.com/v2/market/buyer/list-purchases?filter_by=wordpress-themes&include_all_item_details=true';
		$response = $this->request( $url, $args );

		if ( is_wp_error( $response ) || empty( $response ) || empty( $response['results'] ) ) {
			return $themes;
		}

		foreach ( $response['results'] as $theme ) {
			$normalized = $this->normalize_theme( $theme[ 'item' ] );

			$themes[ sanitize_title( $normalized[ 'name' ] ) ] = $normalized;
		}

		return $themes;
	}

	/**
	 * Normalize a theme.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $theme An array of API request values.
	 * @return array A normalized array of values.
	 */
	public function normalize_theme( $theme ) {
		return array(
			'id' => $theme['id'],
			'name' => $theme['wordpress_theme_metadata']['theme_name'],
			'author' => $theme['wordpress_theme_metadata']['author_name'],
			'version' => $theme['wordpress_theme_metadata']['version'],
			'description' => self::remove_non_unicode( $theme['wordpress_theme_metadata']['description'] ),
			'url' => $theme['url'],
			'author_url' => $theme['author_url'],
			'thumbnail_url' => $theme['thumbnail_url'],
			'rating' => $theme['rating'],
		);
	}

	/**
	 * Remove all non unicode characters in a string
	 *
	 * @since 1.0.0
	 *
	 * @param string $retval The string to fix.
	 * @return string
	 */
	static private function remove_non_unicode( $retval ) {
		return preg_replace( '/[\x00-\x1F\x80-\xFF]/', '', $retval );
	}

	/**
	 * See if the current token can make an API request.
	 *
	 * @since 1.0.0
	 *
	 * @return boolean
	 */
	public function can_make_request_with_token() {
		return $this->item( '9602611' ) ? true : false;
	}

	/**
	 * Display the API connection status.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function connection_status_label() {
		return $this->can_make_request_with_token() ? $this->strings[ 'api-connected' ] : $this->strings[ 'api-disconnected' ];
	}
}
endif;
