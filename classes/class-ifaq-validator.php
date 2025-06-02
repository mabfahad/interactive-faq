<?php

trait Ifaq_Validator {

    /**
     * Sanitize and validate FAQ form data.
     *
     * @param array $data Raw $_POST or input array.
     * @return array|WP_Error Sanitized data array or WP_Error.
     */
    public function validate_faq_data(array $data) {
        // Sanitize inputs
        $question   = sanitize_textarea_field($data['question']);
        $answer     = wp_kses_post($data['answer']);
        $categories = is_array($data['categories']) ? array_map('sanitize_text_field', $data['categories']) : [];
        $status     = sanitize_key($data['status']);

        // Validate required fields
        $errors = [];
        if (empty($question)) {
            $errors[] =['question'];
        }

        if (empty($answer)) {
            $errors[] =['answer'];
        }

        if (empty($categories)) {
            $errors[] =['categories'];
        }

        if (!in_array($status, ['active', 'deactive'], true)) {
            $errors[] =['status'];
        }

        return [
            'errors'     => $errors,
            'question'   => $question,
            'answer'     => $answer,
            'categories' => $categories,
            'status'     => $status,
        ];
    }
}
