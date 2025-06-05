<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://abfahad.me
 * @since      1.0.0
 *
 * @package    Ifaq
 * @subpackage Ifaq/public/partials
 */
$ifaq_settings = maybe_unserialize(get_option('ifaq_settings'));
echo "<pre>";print_r($ifaq_settings);echo "</pre>";
$displayStyle = $ifaq_settings['displayStyle'];

switch ($displayStyle) {
    case 'accordion':
        require_once IFAQ_PLUGIN_DIR.'/public/partials/ifaq-public-accordion-display.php';
        break;

    case 'timeline':
        require_once IFAQ_PLUGIN_DIR.'/public/partials/ifaq-public-timeline-display.php';
        break;

    case 'grid':
        require_once IFAQ_PLUGIN_DIR.'/public/partials/ifaq-public-grid-display.php';
        break;

    case 'table':
        require_once IFAQ_PLUGIN_DIR.'/public/partials/ifaq-public-table-display.php';
        break;

    default:
        require_once IFAQ_PLUGIN_DIR.'/public/partials/ifaq-public-accordion-display.php';
        break;
}
