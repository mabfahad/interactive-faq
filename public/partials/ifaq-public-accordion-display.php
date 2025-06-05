<?php
    $ifaq_db = new Ifaq_DB();
    $faqs_data = $ifaq_db->get_all_ifaqs();
    $faqs = $faqs_data['faqs'];
?>
<div class="ifaq-accordion">
    <?php
        if (!empty($faqs)) :
            foreach ($faqs as $faq) :
    ?>
    <div class="ifaq-accordion-item">
        <div class="ifaq-question">
            <?php echo esc_html($faq->question); ?>
            <span class="ifaq-icon">&#9662;</span>
        </div>
        <div class="ifaq-answer">
            <?php echo esc_html($faq->answer); ?>
        </div>
    </div>
    <?php
            endforeach;
        endif;
    ?>
</div>
