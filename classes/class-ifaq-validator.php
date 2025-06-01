<?php

trait Ifaq_Validator {

    /**
     * Sanitize and validate FAQ form data.
     *
     * @param array $data Raw $_POST or input array.
     * @return array|WP_Error Sanitized data array or WP_Error.
     */
    public function validate_faq_data(array $data) {
        $question_raw = $data['question'] ?? '';
        $answer_raw   = $data['answer'] ?? '';
        $category_raw = $data['category'] ?? '';
        $status_raw   = $data['status'] ?? '';

        // Sanitize inputs
        $question = sanitize_textarea_field($question_raw);
        $answer   = wp_kses_post($answer_raw);
        $category = sanitize_text_field($category_raw);
        $status   = sanitize_key($status_raw);

        // Validate required fields
        if (empty($question)) {
            return new WP_Error('invalid_question', 'Question is required.');
        }

        if (empty($answer)) {
            return new WP_Error('invalid_answer', 'Answer is required.');
        }

        if (!in_array($status, ['active', 'deactive'], true)) {
            return new WP_Error('invalid_status', 'Invalid status value.');
        }

        // Optional: field length checks
        if (strlen($question) > 300) {
            return new WP_Error('question_too_long', 'Question must be under 300 characters.');
        }

        return [
            'question'  => $question,
            'answer'    => $answer,
            'category'  => $category,
            'status'    => $status,
        ];
    }
}
