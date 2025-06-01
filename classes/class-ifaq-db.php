<?php

class Ifaq_DB
{
    protected $wpdb;

    public function __construct($wpdb) {
        $this->wpdb = $wpdb;
    }

    public function insert_interactive_faq($data) {
        $this->wpdb->insert(
            $this->wpdb->prefix . 'faq',
            ['question' => $data['question'],
            'answer' => $data['answer'],
            'category' => $data['category'],
            'status' => $data['status'],
            'created_at'  => current_time('mysql'),] // format: Y-m-d H:i:s
            ['%s'],
            ['%s'],
            ['%s'],
            ['%s'],
            ['%s'],
        );
    }
}
