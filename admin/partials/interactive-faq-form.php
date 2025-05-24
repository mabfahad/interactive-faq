<div class="wrap">
    <h1>FAQ Management</h1>

    <form method="post" action="">
        <?php wp_nonce_field('save_faq', 'faq_nonce'); ?>
        <table class="form-table">
            <tr>
                <th scope="row">Question</th>
                <td><textarea name="question" rows="3" cols="50" required></textarea></td>
            </tr>
            <tr>
                <th scope="row">Answer</th>
                <td><textarea name="answer" rows="5" cols="50" required></textarea></td>
            </tr>
            <tr>
                <th scope="row">Category</th>
                <td><input type="text" name="category" value="general" required></td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" name="submit_faq" class="button-primary" value="Add FAQ">
        </p>
    </form>