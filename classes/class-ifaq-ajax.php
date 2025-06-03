<?php

class Ifaq_Ajax
{
    use Ifaq_Validator;

    public function __construct()
    {
        // Register AJAX handlers
        add_action('wp_ajax_save_ifaq_new', [$this, 'save_ifaq_new']);
        add_action('wp_ajax_save_ifaq_settings', [$this, 'save_ifaq_settings']);
    }

    /**
     * Handle AJAX request to save a new FAQ
     */
    public function save_ifaq_new()
    {
        $this->verify_nonce('ifaq_nonce_action', 'nonce');
//        echo "<pre>";print_r($_POST);echo "</pre>";exit();
        $data = [
            'question'   => sanitize_text_field($_POST['ifaqQuestion'] ?? ''),
            'answer'     => wp_kses_post($_POST['ifaqAnswer'] ?? ''),
            'categories' => array_map('intval', $_POST['ifaqCategories'] ?? []),
            'status'     => sanitize_text_field($_POST['ifaqStatus'] ?? 'Active'),
            'isEdit'     => isset($_POST['isEdit']) && $_POST['isEdit'] == '1' ? 1 : 0,
            'faq_id'     => isset($_POST['faq_id']) ? intval($_POST['faq_id']) : 0,
        ];

        $validation = $this->validate_faq_data($data);

        if (!empty($validation['errors'])) {
            return $this->send_json(false, 'Required fields are missing', $validation['errors']);
        }

        global $wpdb;
        $ifaq_db = new Ifaq_DB($wpdb);

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
     * Handle AJAX request to save plugin settings
     */
    public function save_ifaq_settings()
    {
        $this->verify_nonce('ifaq_nonce_action', 'nonce');

        $settings = maybe_serialize($_POST['settingsData']);

        update_option('ifaq_settings', $settings);

        $this->send_json(true, 'Settings saved successfully');
    }

    /**
     * Verify nonce or send error response
     */
    private function verify_nonce(string $action, string $name)
    {
        if (!check_ajax_referer($action, $name, false)) {
            $this->send_json(false, 'Invalid or expired nonce');
        }
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
