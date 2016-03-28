<?php
/**
 *
 */

class Astoundify_Theme_Updater {

	/**
	 * Premium themes.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var array
	*/
	private static $themes = array();

	public static function init() {
		add_filter( 'pre_set_site_transient_update_themes', array( __CLASS__, 'check_theme_updates' ) );
		add_filter( 'pre_set_transient_update_themes', array( __CLASS__, 'check_theme_updates' ) );
	}

	public static function check_theme_updates( $transient ) {
		// Process premium theme updates.
		if ( ! isset( $transient->checked ) ) {
			return $transient;
		}

		$api = Astoundify_Envato_Market_API::instance();

		$installed = self::get_installed_themes();

		foreach ( $installed as $slug => $premium ) {
			$theme = wp_get_theme( $slug );

			if ( $theme->exists() && version_compare( $theme->get( 'Version' ), $premium['version'], '<' ) ) {
				$transient->response[ $slug ] = array(
					'theme'       => $slug,
					'new_version' => $premium['version'],
					'url'         => $premium['url'],
					'package'     => $api->deferred_download( $premium['id'] ),
				);
			}
		}

		return $transient;
	}
}

Astoundify_Theme_Updater::init();
