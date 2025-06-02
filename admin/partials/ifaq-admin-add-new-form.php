<?php
    global $wpdb;
    $ifaq_db = new Ifaq_DB($wpdb);
    $all_categories = $ifaq_db->get_ifaq_all_categories();
?>
<div class="ifaq-container">
    <h1>Add New FAQ</h1>

    <form id="ifaq-add-new-form" method="post">
        <div class="ifaq-form-group">
        <div class="ifaq-form-row">
            <label for="display-style">Question<span class="ifaq_required">*</span> </label>
            <div class="input-field">
                <textarea name="ifaq_question" id="ifaq_question"></textarea>
            </div>
        </div>
        <div class="ifaq-form-row">
            <label for="display-style">Answer<span class="ifaq_required">*</span> </label>
            <div class="input-field">
                <textarea name="ifaq_answer" id="ifaq_answer"></textarea>
            </div>
        </div>

        <div class="ifaq-form-row">
            <label>Category<span class="ifaq_required">*</span></label>
            <div class="input-field">
                <?php foreach ($all_categories as $category) : ?>
                    <label>
                        <input type="checkbox" name="ifaq_category[]" value="<?php echo esc_attr($category->id); ?>">
                        <?php echo esc_html(__($category->title)); ?>
                    </label><br>
                <?php endforeach; ?>
            </div>
        </div>


        <div class="ifaq-form-row">
            <label for="ifaq_status">Status</label>
            <div class="input-field">
                <select id="ifaq_status">
                    <option>Active</option>
                    <option>Deactive</option>
                </select>
            </div>
        </div>

    </div>

        <div class="form-actions">
            <button class="button button-primary">Save</button>
        </div>
    </form>
</div>
