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
 * Class responsible for tasks to run during plugin activation.
 *
 * This class defines all the code necessary to set up the plugin's environment
 * when it is activated, such as creating required database tables.
 *
 * @since      1.0.0
 * @package    Ifaq
 * @subpackage Ifaq/includes
 * @author     Md Abdullah Al Fahad <mabf.fahad@gmail.com>
 */
class Ifaq_Activator
{

    /**
     * Executes activation logic for the plugin.
     *
     * Initializes the database handler and creates necessary tables.
     *
     * @return void
     * @since 1.0.0
     */
    public function activate()
    {
        global $wpdb;
        $ifaq_db = new Ifaq_DB($wpdb);
        $ifaq_db->create_tables_at_activation();
    }

}
