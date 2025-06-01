<?php

class Ifaq_DB
{
    protected $wpdb;

    public function __construct($wpdb)
    {
        $this->wpdb = $wpdb;
    }

    public function create_tables_at_installations() {
        return $this->create_tables();
    }

    public function delete_tables_at_deactivation() {
        return $this->deleteTables();
    }

    private function create_tables()
    {

        $charset_collate = $this->wpdb->get_charset_collate();

        $faq_table = $this->wpdb->prefix . 'interactive_faq';
        $category_table = $this->wpdb->prefix . 'faq_category';
        $pivot_table = $this->wpdb->prefix . 'faq_category_pivot';

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // 1. FAQ Categories
        $sql_category = "CREATE TABLE $category_table (
            id mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
            title varchar(100) NOT NULL,
            slug varchar(100) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug)
        ) $charset_collate;";

        dbDelta($sql_category);

        // 2. FAQs
        $sql_faq = "CREATE TABLE $faq_table (
            id mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
            question text NOT NULL,
            answer text NOT NULL,
            order_num int DEFAULT 0,
            status varchar(20) DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        dbDelta($sql_faq);

        // 3. Pivot Table
        $sql_pivot = "CREATE TABLE $pivot_table (
            faq_id mediumint(9) unsigned NOT NULL,
            category_id mediumint(9) unsigned NOT NULL,
            PRIMARY KEY (faq_id, category_id),
            KEY idx_faq_id (faq_id),
            KEY idx_category_id (category_id)
        ) $charset_collate;";

        dbDelta($sql_pivot);
    }

    private function deleteTables() {
        //delete table
        $table_names = ['interactive_faq','faq_category','faq_category_pivot'];
        foreach ($table_names as $table_name) {
            $table = $this->wpdb->prefix.$table_name;
            $this->wpdb->query("DROP TABLE IF EXISTS $table");
        }
    }


    public function insert_interactive_faq($data)
    {
        $this->wpdb->insert(
            $this->wpdb->prefix . 'faq',
            ['question' => $data['question'],
                'answer' => $data['answer'],
                'category' => $data['category'],
                'status' => $data['status'],
                'created_at' => current_time('mysql'),] // format: Y-m-d H:i:s
            ['%s'],
            ['%s'],
            ['%s'],
            ['%s'],
            ['%s'],
        );
    }
}
