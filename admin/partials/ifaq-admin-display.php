<?php

/**
 * Provides the admin area view for the plugin
 *
 * This file contains the markup for the admin-facing aspects of the plugin.
 *
 * @link       https://abfahad.me
 * @since      1.0.0
 *
 * @package    Ifaq
 * @subpackage Ifaq/admin/partials
 */

global $wpdb;
$ifaq_db = new Ifaq_DB($wpdb);
$all_faqs = $ifaq_db->get_all_ifaqs();
?>

<div class="ifaq-container">
    <h2>All Saved FAQs</h2>
    <div class="ifaq-accordion">
        <?php if (!empty($all_faqs)) : ?>
            <?php foreach ($all_faqs as $faq) : ?>
                <div class="ifaq-accordion-item">
                    <div class="ifaq-question">
                        <?php echo esc_html($faq->question); ?>
                        <span class="ifaq-icon">&#9662;</span>
                    </div>
                    <div class="ifaq-answer">
                        <?php echo esc_html($faq->answer); ?>
                        <div class="ifaq-meta">
                            Status: <span class="ifaq-status active"><?php echo esc_html($faq->status); ?></span> |
                            Created: <?php echo esc_html($faq->created_at); ?>
                        </div>
                        <div class="ifaq-actions">
                            <a href="#" class="edit">Edit</a>
                            <a href="#" class="delete" onclick="return confirm('Are you sure you want to delete this FAQ?')">Delete</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <p>No FAQs found.</p>
        <?php endif; ?>
    </div>
</div>
