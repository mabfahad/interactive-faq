<?php

class Ifaq_Ajax
{
    use Ifaq_Validator;

    public function __construct()
    {
        // Register AJAX handlers
        add_action('wp_ajax_save_ifaq_new', [$this, 'save_ifaq_new']);
        add_action('wp_ajax_save_ifaq_settings', [$this, 'save_ifaq_settings']);
        add_action('wp_ajax_delete_ifaq', [$this, 'delete_ifaq']);
        add_action('wp_ajax_filter_ifaq_by_category', [$this, 'filter_ifaq_by_category']);
    }

    /**
     * Handle AJAX request to save a new FAQ
     */
    public function save_ifaq_new()
    {
        check_ajax_referer('ifaq_nonce_action', 'nonce', false);

        $data = [
            'question'      => sanitize_text_field(wp_unslash($_POST['ifaqQuestion'] ?? '')),
            'answer'        => wp_kses_post(wp_unslash($_POST['ifaqAnswer'] ?? '')),
            'categories'    => array_map('intval', wp_unslash($_POST['ifaqCategories'] ?? [])),
            'order_num'     => intval(wp_unslash($_POST['ifaqOrderNumber'] ?? 0)),
            'status'        => sanitize_text_field(wp_unslash($_POST['ifaqStatus'] ?? 'Active')),
            'isEdit'        => isset($_POST['isEdit']) && sanitize_text_field(wp_unslash($_POST['isEdit'])) === '1' ? 1 : 0,
            'faq_id'        => isset($_POST['faq_id']) ? intval(wp_unslash($_POST['faq_id'])) : 0,
        ];


        $validation = $this->validate_faq_data($data);

        if (!empty($validation['errors'])) {
            return $this->send_json(false, 'Required fields are missing', $validation['errors']);
        }

        $ifaq_db = new Ifaq_DB();

        // Insert or update based on isEdit flag
        if ($data['isEdit'] == 1 && $data['faq_id'] > 0) {
            $ifaq_db->update_ifaq($data['faq_id'], $data);
            $this->send_json(true, 'Successfully updated', $validation);
        } else {
            $ifaq_db->insert_ifaq($data);
            $this->send_json(true, 'Successfully added', $validation);
        }
        wp_die();
    }

    /**
     * Handle AJAX request to Delete faq with faq ID
     */
    public function delete_ifaq()
    {
        check_ajax_referer('ifaq_nonce_action', 'nonce', false);

        $faq_id = isset($_POST['faq_id']) ? intval(wp_unslash($_POST['faq_id'])) : 0;

        $ifaq_db = new Ifaq_DB();
        $ifaq_db->delete_ifaq($faq_id);

        $this->send_json(true, 'FAQ deleted successfully');
    }

    /**
     * Handle AJAX request to save plugin settings
     */
    public function save_ifaq_settings()
    {
        check_ajax_referer('ifaq_nonce_action', 'nonce', false);

        $settings = isset($_POST['settingsData']) ? maybe_serialize(array_map('sanitize_text_field', wp_unslash($_POST['settingsData']))) : '';

        update_option('ifaq_settings', $settings);

        $this->send_json(true, 'Settings saved successfully');
    }

    /**
     * Handle AJAX request to filter category
     */
    public function filter_ifaq_by_category()
    {
        check_ajax_referer('ifaq_frontend_nonce_action', 'nonce', false);

        $category = intval(wp_unslash($_POST['category'] ?? 0));
        $search = sanitize_text_field(wp_unslash($_POST['search'] ?? ''));
        $ifaq_data = new Ifaq_DB();
        $faqs = $ifaq_data->get_all_ifaqs_by_category($category,1,10,$search);

        $this->send_json(true, 'Settings saved successfully');
    }

    /**
     * Send standardized JSON response
     */
    private function send_json(bool $success, string $message, $data = null)
    {
        $response = [
            'success' => $success,
            'message' => $message,
        ];

        if (!is_null($data)) {
            $response['data'] = $data;
        }

        wp_send_json($response);
        wp_die();
    }
}

new Ifaq_Ajax();
