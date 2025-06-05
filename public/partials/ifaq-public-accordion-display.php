<?php
    $ifaq_db = new Ifaq_DB();
    $faqs_data = $ifaq_db->get_all_ifaqs();
    $faqs = $faqs_data['faqs'];
    $categories = $ifaq_db->get_ifaq_all_categories();
?>
<div class="ifaq-accordion">
    <div id="ifaq-controls">
        <form action="" id="ifaq-search" method="get">
            <input type="text" class="ifaq-search-input" placeholder="Search FAQs...">
        </form>
        <select id="ifaq-category-filter">

            <option value="all">All Categories</option>
            <?php
                if (!empty($categories)) :
                    foreach ($categories as $category) :?>
                        <option value="<?php echo esc_attr($category->title);?>"><?php echo esc_html($category->title); ?></option>
            <?php
                    endforeach;
                endif;
            ?>
        </select>
    </div>
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
