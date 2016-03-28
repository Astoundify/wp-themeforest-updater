<?php
/**
 */
class Astoundify_ThemeForest_Updater {

	public static function init() {
		require_once( dirname( __FILE__ ) . '/includes/class-envato-market-api.php' );
		require_once( dirname( __FILE__ ) . '/includes/class-theme-updater.php' );
	}

}
add_action( 'plugins_loaded', array( 'Astoundify_ThemeForest_Updater', 'init' ), 999 );
