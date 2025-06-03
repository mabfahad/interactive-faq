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
        $table_names = ['interactive_faq', 'faq_category'];
        foreach ($table_names as $table_name) {
            $table = $this->wpdb->prefix . $table_name;
            $this->wpdb->query("DROP TABLE IF EXISTS $table");
        }
    }

    /**
     * Insert a new FAQ entry into the database.
     *
     * This method inserts a new row into the interactive_faq table with the provided
     * question, answer, serialized category IDs, status, and creation timestamp.
     *
     * @param array $data {
     *     Array of FAQ data to insert.
     *
     * @type string $question The FAQ question text.
     * @type string $answer The FAQ answer text.
     * @type array $category_ids Array of category IDs associated with this FAQ.
     * @type string $status Status of the FAQ (e.g., 'active', 'inactive').
     * }
     * @return bool True on successful insert, false on failure.
     */

    public function insert_ifaq($data)
    {
        $table = $this->wpdb->prefix . 'interactive_faq';

        $result = $this->wpdb->insert(
            $table,
            [
                'question' => $data['question'],
                'answer' => $data['answer'],
                'category_ids' => maybe_serialize($data['categories'] ?? []),
                'status' => $data['status'],
                'created_at' => current_time('mysql'),
            ],
            [
                '%s', // question
                '%s', // answer
                '%s', // category_ids (serialized string)
                '%s', // status
                '%s', // created_at
            ]
        );

        return $result !== false; // returns true on success, false on failure
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
    public function get_all_ifaqs($page = 1, $per_page = 10)
    {
        $faq_table = $this->wpdb->prefix . 'interactive_faq';

        $page = max(1, intval($page));
        $per_page = max(1, intval($per_page));
        $offset = ($page - 1) * $per_page;

        // Get total count for pagination
        $total = $this->wpdb->get_var("SELECT COUNT(*) FROM $faq_table");

        // Calculate total pages
        $total_pages = (int)ceil($total / $per_page);

        // Fetch paginated FAQs
        $faqs = $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT * FROM $faq_table LIMIT %d OFFSET %d",
            $per_page,
            $offset
        ));

        // Attach categories to each FAQ
        $faqs = $this->attach_categories_to_faqs($faqs);

        return [
            'faqs' => $faqs,
            'total' => intval($total),
            'current_page' => $page,
            'total_pages' => $total_pages,
        ];
    }

    /**
     * Update an existing FAQ entry by ID.
     *
     * Updates the question, answer, serialized category IDs, status, and order number.
     *
     * @param int $faq_id The ID of the FAQ to update.
     * @param array $data Associative array of fields to update:
     *                      - 'question' => (string) FAQ question text.
     *                      - 'answer' => (string) FAQ answer text.
     *                      - 'category_ids' => (array) Array of category IDs.
     *                      - 'status' => (string) FAQ status.
     *                      - 'order_num' => (int) Optional order number.
     * @return bool True on success, false on failure.
     */
    public function update_ifaq($faq_id, $data)
    {
        $faq_table = $this->wpdb->prefix . 'interactive_faq';

        // Prepare data array for update
        $update_data = [
            'question' => $data['question'] ?? '',
            'answer' => $data['answer'] ?? '',
            // Serialize category IDs array before saving
            'category_ids' => isset($data['categories']) ? maybe_serialize($data['categories']) : '',
            'status' => $data['status'] ?? 'active',
            'order_num' => isset($data['order_num']) ? intval($data['order_num']) : 0,
        ];

        // Prepare format array (all strings except order_num is int)
        $format = ['%s', '%s', '%s', '%s', '%d'];

        $where = ['id' => intval($faq_id)];
        $where_format = ['%d'];

        $updated = $this->wpdb->update($faq_table, $update_data, $where, $format, $where_format);

        return ($updated !== false);
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
    public function get_faq_category_details($category_ids)
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

    /**
     * Inserts a new FAQ category into the database.
     *
     * Automatically generates a unique slug if not provided or if a duplicate exists.
     *
     * @param array $data Associative array with keys: 'title' and optionally 'slug'.
     * @return bool True if insertion was successful, false on failure.
     */
    public function insert_faq_category($data) {
        // Auto-generate slug if empty
        $raw_slug = empty($data['slug']) ? sanitize_title($data['title']) : sanitize_title($data['slug']);
        $slug = $this->generate_unique_slug($raw_slug);

        $result = $this->wpdb->insert(
            $this->wpdb->prefix . 'faq_category',
            [
                'title'      => sanitize_text_field($data['title']),
                'slug'       => $slug,
                'created_at' => current_time('mysql'),
            ]
        );

        return $result !== false;
    }

    /**
     * Updates an existing FAQ category in the database.
     *
     * Automatically generates a unique slug if not provided or if a duplicate exists,
     * excluding the current category ID from the uniqueness check.
     *
     * @param array $data Associative array with keys: 'id', 'title', and optionally 'slug'.
     * @return bool True if the update was successful, false otherwise.
     */
    public function update_faq_category($data) {
        // Auto-generate slug if empty
        $raw_slug = empty($data['slug']) ? sanitize_title($data['title']) : sanitize_title($data['slug']);
        $slug = $this->generate_unique_slug($raw_slug, $data['id']); // optional: pass ID to exclude current row from uniqueness check

        $result = $this->wpdb->update(
            $this->wpdb->prefix . 'faq_category',
            [
                'title' => sanitize_text_field($data['title']),
                'slug'  => $slug,
            ],
            [
                'id' => intval($data['id']),
            ]
        );

        return $result !== false;
    }

    /**
     * Generates a unique slug for a FAQ category.
     *
     * If the provided slug already exists in the database, this method will append
     * an incrementing number (e.g., 'slug', 'slug-1', 'slug-2', etc.) until a unique slug is found.
     * It can optionally exclude a specific category ID when checking for duplicates,
     * which is useful during updates.
     *
     * @param string $base_slug   The initial slug (typically from the title or user input).
     * @param int|null $exclude_id Optional. ID of a category to exclude from the uniqueness check.
     * @return string A unique, sanitized slug.
     */
    private function generate_unique_slug($base_slug, $exclude_id = null) {
        $category_table = $this->wpdb->prefix . 'faq_category';
        $slug = $base_slug;
        $i = 1;

        while (true) {
            $query = "SELECT COUNT(*) FROM $category_table WHERE slug = %s";
            $params = [$slug];

            if ($exclude_id !== null) {
                $query .= " AND id != %d";
                $params[] = $exclude_id;
            }

            $exists = $this->wpdb->get_var($this->wpdb->prepare($query, ...$params));

            if ($exists == 0) {
                break;
            }

            $slug = $base_slug . '-' . $i;
            $i++;
        }

        return $slug;
    }


}
