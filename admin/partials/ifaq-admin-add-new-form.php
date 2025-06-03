<?php

$ifaq_db = new Ifaq_DB();

// Get all categories for checkbox list
$all_categories = $ifaq_db->get_ifaq_all_categories();
$isEdit = false;
$edit_faq = null;
// Handle Edit - Pre-fill form data if editing
if (isset($_GET['action']) && $_GET['action'] === 'edit_faq' && isset($_GET['id'])) {
    $faq_id = intval($_GET['id']);
    $edit_faq = $ifaq_db->get_single_faq_details($faq_id);

    // Extract category IDs from objects
    if (!empty($edit_faq->categories)) {
        $selected_categories = array_map(fn($cat) => $cat->id, $edit_faq->categories);
    } else {
        $selected_categories = [];
    }
    $isEdit = true;
} else {
    $selected_categories = [];
}

?>

<div class="ifaq-container">
    <h1><?php echo $isEdit ? 'Edit FAQ' : 'Add New FAQ'; ?></h1>

    <form id="ifaq-form" method="post" data-faq-id="<?php echo $edit_faq ? intval($edit_faq->id) : 0; ?>">
        <div class="ifaq-form-group">
            <div class="ifaq-form-row">
                <label for="ifaq_question">Question<span class="ifaq_required">*</span></label>
                <div class="input-field">
                    <textarea name="ifaq_question" id="ifaq_question" required><?php echo $edit_faq ? esc_textarea($edit_faq->question) : ''; ?></textarea>
                </div>
            </div>

            <div class="ifaq-form-row">
                <label for="ifaq_answer">Answer<span class="ifaq_required">*</span></label>
                <div class="input-field">
                    <textarea name="ifaq_answer" id="ifaq_answer" required><?php echo $edit_faq ? esc_textarea($edit_faq->answer) : ''; ?></textarea>
                </div>
            </div>

            <div class="ifaq-form-row">
                <label>Category<span class="ifaq_required">*</span></label>
                <div class="input-field">
                    <?php foreach ($all_categories as $category) :
                        $checked = in_array($category->id, $selected_categories) ? 'checked' : '';
                    ?>
                        <label>
                        <input type="checkbox" name="ifaq_category[]" value="<?php echo esc_attr($category->id); ?>" <?php echo esc_attr($checked); ?>>
                            <?php echo esc_html($category->title); ?>
                        </label><br>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="ifaq-form-row">
                <label for="ifaq_order_number">Order Number<span class="ifaq_required">*</span></label>
                <div class="input-field">
                    <input type="number" name="ifaq_order_number" id="ifaq_order_number" required value="<?php echo esc_attr( $edit_faq ? $edit_faq->order_num : 0 ); ?>">
                </div>
            </div>

            <div class="ifaq-form-row">
                <label for="ifaq_status">Status</label>
                <div class="input-field">
                    <select id="ifaq_status" name="ifaq_status">
                        <option value="Active" <?php selected($edit_faq->status ?? '', 'Active'); ?>>Active</option>
                        <option value="Deactive" <?php selected($edit_faq->status ?? '', 'Deactive'); ?>>Deactive</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button data-attribute-action="<?php echo esc_attr($isEdit); ?>" data-attribute-id="<?php echo esc_attr($faq_id); ?>" type="submit" class="button button-primary"><?php echo esc_html($isEdit ? 'Update' : 'Save'); ?>
        </button>

        </div>

        <div id="ifaq-loader" style="display:none; text-align:center; margin-top:10px;">
            <span class="spinner is-active"></span> Saving FAQ...
        </div>
        <div id="ifaq-message" style="display:none; margin-top:10px; position:relative;">
            <span class="ifaq-close" style="position:absolute; right:10px; top:8px; cursor:pointer;">&times;</span>
            <span class="ifaq-message-text"></span>
        </div>
    </form>
</div>
