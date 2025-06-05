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
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
            $wpdb->query("DROP TABLE IF EXISTS `%i`", $table);
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

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $result = $wpdb->insert(
            $table,
            [
                'question' => $data['question'],
                'answer' => $data['answer'],
                'order_num' => isset($data['order_num']) ? intval($data['order_num']) : 0,
                'category_ids' => maybe_serialize($data['categories'] ?? []),
                'status' => $data['status'],
                'created_at' => current_time('mysql'),
            ],
            [
                '%s', // question
                '%s', // answer
                '%d', // order_num should be an integer
                '%s', // category_ids (serialized string)
                '%s', // status
                '%s', // created_at
            ]
        );

        if ($result !== false) {
            // $faq_id = $wpdb->insert_id; // Useful if clearing specific new FAQ cache, not strictly needed for list caches
            $cache_group = 'ifaq_plugin';
            wp_cache_delete('ifaq_faqs_total_count', $cache_group);
        }

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

        if ($result !== false) {
            $cleaned_faq_id = intval($faq_id);
            $cache_group = 'ifaq_plugin';

            wp_cache_delete('ifaq_details_' . $cleaned_faq_id, $cache_group);
            wp_cache_delete('ifaq_serialized_categories_' . $cleaned_faq_id, $cache_group);
            wp_cache_delete('ifaq_faqs_total_count', $cache_group);
        }

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
        $cache_key = 'ifaq_all_categories';
        $cache_group = 'ifaq_plugin';
        $categories = wp_cache_get($cache_key, $cache_group);

        if (false === $categories) {
            $table_name = $wpdb->prefix . 'faq_category';
            $categories = $wpdb->get_results("SELECT * FROM `" . esc_sql($table_name) . "`");
            wp_cache_set($cache_key, $categories, $cache_group);
        }

        return $categories;
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

        // Total Count Caching
        $count_cache_key = 'ifaq_faqs_total_count';
        $cache_group = 'ifaq_plugin';
        $total = wp_cache_get($count_cache_key, $cache_group);

        if (false === $total) {
            $faq_table = $wpdb->prefix . 'interactive_faq';
            $total = (int)$wpdb->get_var("SELECT COUNT(*) FROM `" . esc_sql($faq_table) . "`");
            wp_cache_set($count_cache_key, $total, $cache_group);
        }

        // Calculate total pages
        $total_pages = (int)ceil($total / $per_page);

        // Paginated FAQs Caching
        $faqs_cache_key = "ifaq_faqs_p{$page}_pp{$per_page}";
        // $cache_group is already defined for total count
        $faqs = wp_cache_get($faqs_cache_key, $cache_group);

        if (false === $faqs) {
            $faq_table = $wpdb->prefix . 'interactive_faq';
            $faqs = $wpdb->get_results($wpdb->prepare("SELECT * FROM `" . esc_sql($faq_table) . "` LIMIT %d OFFSET %d", $per_page, $offset));
            wp_cache_set($faqs_cache_key, $faqs, $cache_group);
        }

        return [
            'faqs' => $faqs,
            'total' => intval($total),
            'current_page' => $page,
            'total_pages' => $total_pages,
        ];
    }

    /**
     * Retrieves all FAQs filtered by category ID and optional search string, with pagination support.
     *
     * @param int $category_id The ID of the category to filter FAQs by.
     * @param int $page Optional. The current page number for pagination. Default 1.
     * @param int $per_page Optional. Number of FAQs per page. Default 10.
     * @param string|null $s Optional. Search keyword to match against question or answer.
     *
     * @return array {
     * @type array $faqs The list of filtered FAQs for the current page.
     * @type int $total Total number of matched FAQs.
     * @type int $current_page Current page number.
     * @type int $total_pages Total number of pages based on `$per_page`.
     * }
     */
    public function get_all_ifaqs_by_category($category_id, $page = 1, $per_page = 10, $s = null)
    {
        global $wpdb;

        $faq_table = $wpdb->prefix . 'interactive_faq';
        $cache_group = 'ifaq_plugin';

        $search_key = $s ? md5($s) : 'all';
        $cache_key = "ifaq_all_for_cat_{$category_id}_s_{$search_key}";

        $faqs = wp_cache_get($cache_key, $cache_group);

        if ($faqs === false) {
            $raw_faqs = $wpdb->get_results("SELECT * FROM `$faq_table`");

            $faqs = array_filter($raw_faqs, function ($faq) use ($category_id, $s) {
                $categories = maybe_unserialize($faq->category_ids);
                $in_category = is_array($categories) && in_array($category_id, $categories);

                $matches_search = true;
                if ($s !== null && $s !== '') {
                    $search = strtolower($s);
                    $matches_search = stripos($faq->question, $search) !== false || stripos($faq->answer, $search) !== false;
                }

                return $in_category && $matches_search;
            });

            wp_cache_set($cache_key, $faqs, $cache_group);
        }

        $total = count($faqs);
        $total_pages = ceil($total / $per_page);
        $page = max(1, intval($page));
        $offset = ($page - 1) * $per_page;

        $paged_faqs = array_slice($faqs, $offset, $per_page);

        return [
            'faqs' => $paged_faqs,
            'total' => $total,
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

        if ($updated !== false) {
            $cache_group = 'ifaq_plugin';
            // Invalidate cache for the specific FAQ
            wp_cache_delete('ifaq_details_' . intval($faq_id), $cache_group);
            wp_cache_delete('ifaq_serialized_categories_' . intval($faq_id), $cache_group);

            // Invalidate caches for FAQ listings
            wp_cache_delete('ifaq_faqs_total_count', $cache_group);
        }

        return $updated !== false;
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
        $cache_key = 'ifaq_details_' . intval($faq_id);
        $cache_group = 'ifaq_plugin';
        $faq = wp_cache_get($cache_key, $cache_group);

        if (false === $faq) {
            global $wpdb;
            $faq_table = $wpdb->prefix . 'interactive_faq';
            $faq_id = intval($faq_id);
            $faq_row = $wpdb->get_row($wpdb->prepare("SELECT * FROM `" . esc_sql($faq_table) . "` WHERE id = %d", $faq_id));

            if ($faq_row) {
                $faq_row->categories = $this->get_ifaq_all_categories_by_faq_id(intval($faq_id));
                $faq = $faq_row; // Assign to $faq which will be cached
                wp_cache_set($cache_key, $faq, $cache_group);
            } else {
                $faq = null; // Or handle as appropriate if FAQ not found
                wp_cache_set($cache_key, null, $cache_group); // Cache null result
            }
        }
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
        $cache_key = 'ifaq_serialized_categories_' . intval($faq_id);
        $cache_group = 'ifaq_plugin';
        $serialized_ids = wp_cache_get($cache_key, $cache_group);

        if (false === $serialized_ids) {
            global $wpdb;
            $faq_table = $wpdb->prefix . 'interactive_faq';

            $serialized_ids = $wpdb->get_var($wpdb->prepare(
                "SELECT category_ids FROM `" . esc_sql($faq_table) . "` WHERE id = %d",
                absint($faq_id)
            ));
            wp_cache_set($cache_key, $serialized_ids, $cache_group);
        }
        return $serialized_ids;
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
        global $wpdb;

        // Sanitize IDs
        $category_ids = array_map('intval', $category_ids);
        $category_ids = array_unique($category_ids); // Remove duplicates
        $category_ids = array_filter($category_ids); // Remove any zeros if they are not valid IDs

        // If empty after filtering, return early
        if (empty($category_ids)) {
            return [];
        }

        // Create a consistent cache key
        $sorted_ids = $category_ids;
        sort($sorted_ids); // Sort for consistency
        $cache_key = 'ifaq_cat_details_' . implode('_', $sorted_ids);
        $cache_group = 'ifaq_plugin';

        $details = wp_cache_get($cache_key, $cache_group);

        if (false === $details) {
            $category_table = $wpdb->prefix . 'faq_category';

            // Prepare placeholders for the IN clause
            $placeholders = implode(',', array_fill(0, count($category_ids), '%d'));

            $details = $wpdb->get_results($wpdb->prepare("SELECT * FROM `" . esc_sql($category_table) . "` WHERE `id` IN ($placeholders)", ...$category_ids));

            wp_cache_set($cache_key, $details, $cache_group);
        }

        return $details;
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

        if ($result !== false) {
            $cache_group = 'ifaq_plugin';
            wp_cache_delete('ifaq_all_categories', $cache_group);
            // Any other relevant summary caches could be cleared here too.
        }

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

        if ($result !== false) {
            $cache_group = 'ifaq_plugin';
            wp_cache_delete('ifaq_all_categories', $cache_group);
        }

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
        $slug_to_test = sanitize_title($base_slug); // Ensure base_slug is sanitized initially
        $base_slug_sanitized = $slug_to_test; // Keep a sanitized version of the original base
        $i = 1;

        while (true) {
            global $wpdb;

            $table = $wpdb->prefix . 'faq_category';
            $where_clauses = ['slug = %s'];
            $params = [sanitize_title($slug_to_test)];

            // Conditionally add "id !=" clause
            if ($exclude_id !== null) {
                $where_clauses[] = 'id != %d';
                $params[] = absint($exclude_id);
            }

            // Combine WHERE clauses with AND
            $where_sql = implode(' AND ', $where_clauses);
            $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM `$table` WHERE $where_sql", ...$params));


            if (intval($exists) === 0) {
                break;
            }

            $slug_to_test = $base_slug_sanitized . '-' . $i;
            $i++;
        }
        return $slug_to_test;
    }
}