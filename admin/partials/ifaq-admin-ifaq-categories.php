<?php
global $wpdb;
$category_table = $wpdb->prefix . 'faq_category';
$ifaq_db = new Ifaq_DB();

// Handle Add/Edit
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category_title'])) {
    // Verify nonce for security
    if (!isset($_POST['ifaq_category_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ifaq_category_nonce'])), 'ifaq_category_action')) {
        echo '<div class="notice notice-error is-dismissible"><p><strong>Error:</strong> Security check failed.</p></div>';
    } else {
        $title = sanitize_text_field(wp_unslash($_POST['category_title']));
        if (empty($title)) {
            // Display admin notice
            echo '<div class="notice notice-error is-dismissible"><p><strong>Error:</strong> Category title is required.</p></div>';
        } else {
            $slug = isset($_POST['category_slug']) ? sanitize_title(wp_unslash($_POST['category_slug'])) : '';
            $id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
            $data = ['title' => $title, 'slug' => $slug];
            // Check if we're updating or inserting
            if ($id > 0) {
                $data['id'] = $id;
                $ifaq_db->update_faq_category($data);
                echo '<div class="notice notice-success"><p>Category updated successfully.</p></div>';
            } else {
                $ifaq_db->insert_faq_category($data);
                echo '<div class="notice notice-success"><p>Category added successfully.</p></div>';
            }
        }
    }
}

$categories = $ifaq_db->get_ifaq_all_categories();

// Handle Edit - Pre-fill form
$edit_category = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $category_id = isset($_GET['id']) ? intval(wp_unslash($_GET['id'])) : 0;
    $edit_category = $ifaq_db->get_faq_category_details([$category_id])[0];
}

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $wpdb->delete($category_table, ['id' => $id]);
    echo '<div class="notice notice-success"><p>Category deleted successfully.</p></div>';
}
include plugin_dir_path(__FILE__) . 'ifaq-admin-ifaq-categories-html.php';
