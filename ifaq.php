<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://abfahad.me
 * @since             1.0.0
 * @package           Ifaq
 *
 * @wordpress-plugin
 * Plugin Name:       Interactive FAQ Manager
 * Plugin URI:        https://abfahad.me
 * Description:       A modern FAQ plugin with search, animations, and accessibility features
 * Version:           1.0.0
 * Author:            Md Abdullah Al Fahad
 * Author URI:        https://abfahad.me/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ifaq
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'IFAQ_VERSION', '1.0.0' );
define('IFAQ_PLUGIN_DIR',plugin_dir_path( __FILE__ ));

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-ifaq-activator.php
 */
function activate_ifaq() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ifaq-activator.php';
    $activator = new Ifaq_Activator();
	$activator->activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-ifaq-deactivator.php
 */
function deactivate_ifaq() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ifaq-deactivator.php';
    $deactivate = new Ifaq_Deactivator();
	$deactivate->deactivate();
}

register_activation_hook( __FILE__, 'activate_ifaq' );
register_deactivation_hook( __FILE__, 'deactivate_ifaq' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-ifaq.php';

/**
 * Require all the classes
 */

require_once plugin_dir_path(__FILE__).'classes/class-ifaq-db.php';
require_once plugin_dir_path(__FILE__).'classes/class-ifaq-validator.php';
require_once plugin_dir_path(__FILE__).'classes/class-ifaq-ajax.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_ifaq() {

	$plugin = new Ifaq();
	$plugin->run();

}
run_ifaq();
