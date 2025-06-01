<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://abfahad.me
 * @since      1.0.0
 *
 * @package    Ifaq
 * @subpackage Ifaq/includes
 */

/**
 * Class responsible for tasks to run during plugin deactivation.
 *
 * This class defines all the code necessary to clean up when the plugin is deactivated,
 * such as removing custom database tables or resetting configurations.
 *
 * @since      1.0.0
 * @package    Ifaq
 * @subpackage Ifaq/includes
 * @author     Md Abdullah Al Fahad <mabf.fahad@gmail.com>
 */
class Ifaq_Deactivator {

	/**
	 * Executes deactivation logic for the plugin.
	 *
	 * Initializes the database handler and deletes plugin-related tables.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function deactivate() {
        global $wpdb;
        $ifaq_db = new Ifaq_DB($wpdb);
        $ifaq_db->delete_tables_at_deactivation();
	}
}
