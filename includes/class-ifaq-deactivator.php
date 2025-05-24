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
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Ifaq
 * @subpackage Ifaq/includes
 * @author     Md Abdullah Al Fahad <mabf.fahad@gmail.com>
 */
class Ifaq_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public function deactivate() {
        $this->deleteTable();
	}

    private function deleteTable() {
        global $wpdb;
        //delete table
        $table_name = $wpdb->prefix . 'interactive_faq';
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
    }

}
