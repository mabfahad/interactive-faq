<?php

class Ifaq_Ajax
{
    function __construct() {
        add_action('wp_ajax_save_ifaq_new',[$this,'save_ifaq_new']);
    }

    public static function save_ifaq_new() {
        check_ajax_referer('ifaq_nonce_action', 'nonce');
        echo '<pre>';print_r($_POST);echo '</pre>';
        wp_die();
    }
}
new Ifaq_Ajax();
