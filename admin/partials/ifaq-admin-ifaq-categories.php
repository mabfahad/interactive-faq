<?php
global $wpdb;
$category_table = $wpdb->prefix . 'faq_category';
$ifaq_db = new Ifaq_DB($wpdb);

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category_title'])) {
    $title = sanitize_text_field($_POST['category_title']);
    $slug = sanitize_title($_POST['category_slug']);
    $id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $data = ['title'=>$title,'slug'=>$slug];
    // Check if we're updating or inserting
    if ($id > 0) {
        $ifaq_db->update_faq_category($data);
        echo '<div class="notice notice-success"><p>Category updated successfully.</p></div>';
    } else {
        $ifaq_db->insert_faq_category($data);
        echo '<div class="notice notice-success"><p>Category added successfully.</p></div>';
    }
}

$categories = $ifaq_db->get_ifaq_all_categories();

// Handle Edit - Pre-fill form
$edit_category = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $edit_category = $ifaq_db->get_faq_category_details([$_GET['id']])[0];
}

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $wpdb->delete($category_table, ['id' => $id]);
    echo '<div class="notice notice-success"><p>Category deleted successfully.</p></div>';
}
include plugin_dir_path(__FILE__) . 'ifaq-admin-ifaq-categories-html.php';
