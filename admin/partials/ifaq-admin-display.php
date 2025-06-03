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
$ifaq_db    = new Ifaq_DB($wpdb);
$ifaq_settings = maybe_unserialize(get_option('ifaq_settings'));
$page       = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$per_page   = $ifaq_settings['faqsPerPage'] ?? 10;
$faqs_data  = $ifaq_db->get_all_ifaqs($page, $per_page);
$faqs       = $faqs_data['faqs'];
$current    = $faqs_data['current_page'];
$total      = $faqs_data['total_pages'];
?>

<div class="ifaq-container">
    <h2>All Saved FAQs</h2>
    <div class="ifaq-accordion">
        <?php if (!empty($faqs)) : ?>
            <?php foreach ($faqs as $faq) : ?>
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
                            <a href="#" class="edit" data-faq-id="<?php echo esc_html($faq->id); ?>">Edit</a>
                            <a href="#" class="delete">Delete</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <p>No FAQs found.</p>
        <?php endif; ?>
    </div>

    <div id="ifaq-pagination">
        <?php if ( $total > 1 ): ?>
            <ul>
                <?php for ( $i = 1; $i <= $total; $i++ ): ?>
                    <li><a href="<?php echo esc_url( add_query_arg( 'paged', $i ) ); ?>" class="<?php echo ($i === $current) ? 'active' : ''; ?>"><?php echo $i; ?></a></li>
                <?php endfor; ?>
            </ul>
        <?php endif;?>
    </div>
</div>
