<?php

class Ifaq_DB
{

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
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $faq_table = $wpdb->prefix . 'interactive_faq';
        $category_table = $wpdb->prefix . 'faq_category';

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
        global $wpdb;
        $table_names = ['interactive_faq', 'faq_category'];
        foreach ($table_names as $table_name) {
            $table = $wpdb->prefix . $table_name;
            $wpdb->query($wpdb->prepare("DROP TABLE IF EXISTS `%s`", $table));
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
        global $wpdb;
        $table = $wpdb->prefix . 'interactive_faq';

        $result = $wpdb->insert(
            $table,
            [
                'question' => $data['question'],
                'answer' => $data['answer'],
                'order_num' => $data['order_num'],
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
     * Delete an FAQ entry from the database.
     *
     * This method deletes a row from the interactive_faq table based on the provided FAQ ID.
     *
     * @param int $faq_id The ID of the FAQ to delete.
     * @return bool True on successful delete, false on failure.
     */
    public function delete_ifaq($faq_id)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'interactive_faq';

        $result = $wpdb->delete(
            $table,
            ['id' => intval($faq_id)],
            ['%d'] // ID is an integer
        );

        return $result !== false; // true if deleted, false otherwise
    }


    /**
     * Retrieve all categories from the FAQ category table.
     *
     * @return array Array of category objects.
     */
    public function get_ifaq_all_categories()
    {
        global $wpdb;
        return $wpdb->get_results(" SELECT * FROM $wpdb->prefix . 'faq_category'");
    }

    /**
     * Retrieve all FAQs with their category details.
     *
     * @return array Array of FAQ objects, each with a 'categories' property containing category objects.
     */
    public function get_all_ifaqs($page = 1, $per_page = 10)
    {
        global $wpdb;

        $page = max(1, intval($page));
        $per_page = max(1, intval($per_page));
        $offset = ($page - 1) * $per_page;

        // Get total count for pagination (no placeholders needed here, but cast to int for safety)
        $total = (int)$wpdb->get_var("SELECT COUNT(*) FROM $wpdb->prefix . 'interactive_faq'");

        // Calculate total pages
        $total_pages = (int)ceil($total / $per_page);

        // Sanitize the table name to prevent SQL injection
        $faqs = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $wpdb->prefix . 'interactive_faq' LIMIT %d OFFSET %d",
                $per_page,
                $offset
            )
        );

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
        global $wpdb;
        $faq_table = $wpdb->prefix . 'interactive_faq';

        // Prepare data array for update
        $update_data = [
            'question' => $data['question'] ?? '',
            'answer' => $data['answer'] ?? '',
            'order_num' => $data['order_num'] ?? '',
            // Serialize category IDs array before saving
            'category_ids' => isset($data['categories']) ? maybe_serialize($data['categories']) : '',
            'status' => $data['status'] ?? 'active',
            'order_num' => isset($data['order_num']) ? intval($data['order_num']) : 0,
        ];

        // Prepare format array (all strings except order_num is int)
        $format = ['%s', '%s', '%s', '%s', '%d'];

        $where = ['id' => intval($faq_id)];
        $where_format = ['%d'];

        $updated = $wpdb->update($faq_table, $update_data, $where, $format, $where_format);

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
        global $wpdb;

        //Get the details for the given FAQ
        $faq = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $wpdb->prefix . 'interactive_faq' WHERE id = %d",
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
        global $wpdb;

        //Get the serialized category_ids for the given FAQ
        return $wpdb->get_var($wpdb->prepare(
            "SELECT category_ids FROM $wpdb->prefix . 'interactive_faq' WHERE id = %d",
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
    public function get_faq_category_details($category_ids) {
        global $wpdb;

        // Sanitize IDs
        $category_ids = array_map('intval', $category_ids);

        // If empty, return early
        if (empty($category_ids)) {
            return [];
        }

        // Prepare placeholders and SQL
        $placeholders = implode(',', array_fill(0, count($category_ids), '%d'));

        // Prepare the final SQL with IDs
        return $wpdb->get_results($wpdb->prepare(
           "SELECT * FROM $wpdb->prefix . 'faq_category' WHERE `id` LIKE %s",
           ...$category_ids
        ));
    }


    /**
     * Inserts a new FAQ category into the database.
     *
     * Automatically generates a unique slug if not provided or if a duplicate exists.
     *
     * @param array $data Associative array with keys: 'title' and optionally 'slug'.
     * @return bool True if insertion was successful, false on failure.
     */
    public function insert_faq_category($data)
    {
        global $wpdb;
        // Auto-generate slug if empty
        $raw_slug = empty($data['slug']) ? sanitize_title($data['title']) : sanitize_title($data['slug']);
        $slug = $this->generate_unique_slug($raw_slug);

        $result = $wpdb->insert(
            $wpdb->prefix . 'faq_category',
            [
                'title' => sanitize_text_field($data['title']),
                'slug' => $slug,
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
    public function update_faq_category($data)
    {
        global $wpdb;
        // Auto-generate slug if empty
        $raw_slug = empty($data['slug']) ? sanitize_title($data['title']) : sanitize_title($data['slug']);
        $slug = $this->generate_unique_slug($raw_slug, $data['id']); // optional: pass ID to exclude current row from uniqueness check

        $result = $wpdb->update(
            $wpdb->prefix . 'faq_category',
            [
                'title' => sanitize_text_field($data['title']),
                'slug' => $slug,
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
     * @param string $base_slug The initial slug (typically from the title or user input).
     * @param int|null $exclude_id Optional. ID of a category to exclude from the uniqueness check.
     * @return string A unique, sanitized slug.
     */
    private function generate_unique_slug($base_slug, $exclude_id = null)
    {
        global $wpdb;
        $slug = $base_slug;
        $i = 1;

        while (true) {
            $params = [$slug];

            if ($exclude_id !== null) {
                $query .= " AND id != %d";
                $params[] = $exclude_id;
            }

            $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->prefix . 'faq_category' WHERE slug = %s", ...$params));

            if ($exists == 0) {
                break;
            }

            $slug = $base_slug . '-' . $i;
            $i++;
        }

        return $slug;
    }


}
