<div class="ifaq-accordion">
    <form id="ifaq-controls" method="get">
            <input type="text" class="ifaq-search-input" name="ifaq-search" placeholder="Search FAQs...">
            <select id="ifaq-category-filter">

                <option value="all">All Categories</option>
                <?php
                if (!empty($categories)) :
                    foreach ($categories as $category) :?>
                        <option value="<?php echo esc_attr($category->id); ?>"><?php echo esc_html($category->title); ?></option>
                    <?php
                    endforeach;
                endif;
                ?>
            </select>

    </form>
    <?php
    if (!empty($faqs)) :
        foreach ($faqs as $faq) :
            ?>
            <div class="ifaq-accordion-item">
                <div class="ifaq-question">
                    <?php echo esc_html($faq->question); ?>
                    <span class="ifaq-icon">&#9662;&#43;</span>
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
