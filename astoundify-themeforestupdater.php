<?php
/*
 * Plugin Name: ThemeForest Automatic Updater
 * Plugin URI: https://astoundify.com
 * Description: Connect to Envato to enable automatic theme updates. Activating this plugin does nothing.
 * Version: 1.1.0
 * Author: Astoundify
 * Author URI: http://astoundify.com
 */

// load app
require_once( dirname( __FILE__ ) . '/app/class-astoundify-themeforest-updater.php' );

/**
 * Return Astoundify_ThemeForest_Updater instance.
 *
 * @since 1.1.0
 *
 * @return Astoundify_ThemeForest_Updater
 */
function astoundify_themeforestupdater() {
	return Astoundify_ThemeForest_Updater::instance();
}