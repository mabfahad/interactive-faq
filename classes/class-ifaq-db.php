<?php

class Ifaq_DB
{
    protected $wpdb;

    public function __construct($wpdb)
    {
        $this->wpdb = $wpdb;
    }

    /**
     * Creates necessary database tables during plugin activation.
     *
     * This method is hooked into the plugin activation process and simply
     * calls the main table creation function.
     *
     * @return void
     */
    public function create_tables_at_activation()
    {
        return $this->create_tables();
    }

    /**
     *
     * Delete all the tables which were created at the time of plugin activation
     */
    public function delete_tables_at_deactivation()
    {
        return $this->deleteTables();
    }

    /**
     *
     * Create table functions where all the tables create SQL are written
     */
    private function create_tables()
    {

        $charset_collate = $this->wpdb->get_charset_collate();

        $faq_table = $this->wpdb->prefix . 'interactive_faq';
        $category_table = $this->wpdb->prefix . 'faq_category';

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
            category_ids longtext DEFAULT NULL,
            order_num int DEFAULT 0,
            status varchar(20) DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        dbDelta($sql_faq);
    }

    /**
     * Deletes all custom plugin tables related to the FAQ system.
     *
     * This includes: interactive_faq, faq_category, and faq_category_pivot tables.
     *
     * @return void
     */
    private function deleteTables()
    {
        //delete table
        $table_names = ['interactive_faq', 'faq_category', 'faq_category_pivot'];
        foreach ($table_names as $table_name) {
            $table = $this->wpdb->prefix . $table_name;
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

    /**
     * Retrieve all categories from the FAQ category table.
     *
     * @return array Array of category objects.
     */
    public function get_ifaq_all_categories()
    {
        $category_table = $this->wpdb->prefix . 'faq_category';
        return $this->wpdb->get_results(" SELECT * FROM $category_table");
    }

    /**
     * Retrieve all FAQs with their category details.
     *
     * @return array Array of FAQ objects, each with a 'categories' property containing category objects.
     */
    public function get_all_ifaqs()
    {
        $faq_table = $this->wpdb->prefix . 'interactive_faq';

        $faqs = $this->wpdb->get_results("SELECT * FROM $faq_table");

        if (empty($faqs)) {
            return [];
        }

        return $this->attach_categories_to_faqs($faqs);
    }

    /**
     * Attach category details to each FAQ object.
     *
     * @param array $faqs Array of FAQ objects.
     * @return array FAQ objects with 'categories' property added.
     */
    private function attach_categories_to_faqs(array $faqs)
    {
        foreach ($faqs as $faq) {
            $category_ids_serialized = $faq->category_ids ?? '';
            $category_ids = maybe_unserialize($category_ids_serialized);

            if (is_array($category_ids) && !empty($category_ids)) {
                $faq->categories = $this->get_faq_category_details($category_ids);
            } else {
                $faq->categories = [];
            }
        }
        return $faqs;
    }

    /**
     * Retrieves detailed information for a single FAQ entry, including its categories.
     *
     * Fetches the FAQ data from the database using the given FAQ ID, and appends
     * the related categories as an additional property (`categories`) on the returned object.
     *
     * @param int $faq_id The ID of the FAQ to retrieve.
     * @return object|null The FAQ object with an added 'categories' property, or null if not found.
     */
    public function get_single_faq_details($faq_id)
    {
        $faq_table = $this->wpdb->prefix . 'interactive_faq';

        //Get the details for the given FAQ
        $faq = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM $faq_table WHERE id = %d",
            $faq_id
        ));
        $faq->categories = $this->get_ifaq_all_categories_by_faq_id($faq_id);
        return $faq;
    }

    /**
     * Retrieves the serialized category IDs for a given FAQ ID.
     *
     * This returns the raw serialized string stored in the `category_ids` column
     * of the interactive_faq table.
     *
     * @param int $faq_id The ID of the FAQ.
     * @return string|null The serialized category IDs, or null if not found.
     */
    private function get_serialized_categories_with_faq_id($faq_id)
    {
        $faq_table = $this->wpdb->prefix . 'interactive_faq';

        //Get the serialized category_ids for the given FAQ
        return $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT category_ids FROM $faq_table WHERE id = %d",
            $faq_id
        ));
    }

    /**
     * Retrieves all category details associated with a given FAQ ID.
     *
     * This method fetches the serialized category IDs from the FAQ,
     * unserializes them, and returns detailed category objects.
     *
     * @param int $faq_id The ID of the FAQ.
     * @return array Array of category objects, or an empty array if none are found.
     */
    private function get_ifaq_all_categories_by_faq_id($faq_id)
    {
        $category_ids_serialized = $this->get_serialized_categories_with_faq_id($faq_id);

        //Unserialize safely
        $category_ids = maybe_unserialize($category_ids_serialized);

        if (!is_array($category_ids) || empty($category_ids)) {
            return []; // No categories found
        }

        return $this->get_faq_category_details($category_ids);
    }

    /**
     * Retrieves detailed category records based on an array of category IDs.
     *
     * This method sanitizes the provided category IDs and queries the database
     * for matching category records.
     *
     * @param array $category_ids An array of category IDs.
     * @return array Array of category objects.
     */
    private function get_faq_category_details($category_ids)
    {
        $category_table = $this->wpdb->prefix . 'faq_category';

        //Sanitize and prepare SQL
        $category_ids = array_map('intval', $category_ids);
        $placeholders = implode(',', array_fill(0, count($category_ids), '%d'));

        //Fetch matching categories
        $query = $this->wpdb->prepare(
            "SELECT * FROM $category_table WHERE id IN ($placeholders)",
            ...$category_ids
        );
        return $this->wpdb->get_results($query);
    }
}
