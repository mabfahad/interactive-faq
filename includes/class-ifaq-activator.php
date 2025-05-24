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
        $this->createTable();
	}

    private function createTable(){
        global $wpdb;

        $table_name = $wpdb->prefix . 'interactive_faq';

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            question text NOT NULL,
            answer text NOT NULL,
            category varchar(100) DEFAULT 'general',
            order_num int DEFAULT 0,
            status enum('active', 'deactive') DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

}
