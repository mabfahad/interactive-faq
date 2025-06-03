<?php

class Ifaq_Ajax
{
    use Ifaq_Validator;
    function __construct() {
        add_action('wp_ajax_save_ifaq_new',[$this,'save_ifaq_new']);
    }

    public function save_ifaq_new() {
        check_ajax_referer('ifaq_nonce_action', 'nonce');

        $question   = sanitize_text_field($_POST['ifaqQuestion'] ?? '');
        $answer     = wp_kses_post($_POST['ifaqAnswer'] ?? '');
        $categories = array_map('intval', $_POST['ifaqCategories'] ?? []);
        $status     = sanitize_text_field($_POST['ifaqStatus'] ?? 'Active');

        $inserted_data = [
            'question'      => $question,
            'answer'        => $answer,
            'categories'    => $categories,
            'status'        => $status
        ];

        $validated = $this->validate_faq_data($inserted_data);

        if (!empty($validated['errors'])) {
            wp_send_json(['success'=>false,'message'=>'required fields are missing','data'=>$validated['errors']]);
            exit();
        }

        global $wpdb;
        $ifaq_db = new Ifaq_DB($wpdb);
        $ifaq_db->insert_ifaq($inserted_data);

        wp_send_json(['success'=>true,'message'=>'Successfully added','data'=>$validated]);

        wp_die();
    }
}
new Ifaq_Ajax();
