<?php

/**
 * Fired during plugin activation
 *
 * @link       https://abfahad.me
 * @since      1.0.0
 *
 * @package    Ifaq
 * @subpackage Ifaq/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Ifaq
 * @subpackage Ifaq/includes
 * @author     Md Abdullah Al Fahad <mabf.fahad@gmail.com>
 */
class Ifaq_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public function activate() {
        global $wpdb;
        $ifaq_db = new Ifaq_DB($wpdb);
        $ifaq_db->create_tables_at_installations();
	}

}
