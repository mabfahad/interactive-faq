<div class="wrap">
    <div class="ifaq-form-wrapper">
        <h1>FAQ Management</h1>

        <form method="post" action="">
            <?php wp_nonce_field('save_faq', 'faq_nonce'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row"><label for="faq_question">Question</label></th>
                    <td>
                        <textarea name="question" id="faq_question" rows="3" required></textarea>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="faq_answer">Answer</label></th>
                    <td>
                        <textarea name="answer" id="faq_answer" rows="5" required></textarea>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="faq_category">Category</label></th>
                    <td>
                        <input type="text" name="category" id="faq_category" value="general" required>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <input type="submit" name="submit_faq" class="button button-primary" value="Add FAQ">
            </p>
        </form>
    </div>
</div>
