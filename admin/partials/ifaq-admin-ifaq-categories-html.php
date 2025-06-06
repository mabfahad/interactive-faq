<div class="wrap">
    <h1>FAQ Categories</h1>
    <div class="ifaq-admin-categories">
        <div class="add_category">
            <!-- Add/Edit Category Form -->
            <h2><?php echo isset($edit_category) ? 'Edit Category' : 'Add New Category'; ?></h2>
            <form method="post" id="ifaq-category-form">
                <?php wp_nonce_field('ifaq_category_action', 'ifaq_category_nonce'); ?>
                <input type="hidden" name="category_id"
                    value="<?php echo isset($edit_category) ? esc_attr($edit_category->id) : ''; ?>">

                <table class="form-table">
                    <tr>
                        <th><label for="category_title">Title</label></th>
                        <td>
                            <input type="text" name="category_title" id="category_title" class="regular-text" required
                                value="<?php echo isset($edit_category) ? esc_attr($edit_category->title) : ''; ?>">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="category_slug">Slug</label></th>
                        <td>
                            <input type="text" name="category_slug" id="category_slug" class="regular-text"
                                value="<?php echo isset($edit_category) ? esc_attr($edit_category->slug) : ''; ?>">
                            <p class="description">Leave empty to auto-generate from title.</p>
                        </td>
                    </tr>
                </table>

                <?php submit_button(isset($edit_category) ? 'Update Category' : 'Add Category'); ?>
            </form>
        </div>


        <!-- Category List -->
        <div class="all_categories">
            <h2>All Categories</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Slug</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($categories)) : ?>
                        <?php foreach ($categories as $category) : ?>
                            <tr>
                                <td><?php echo esc_html($category->id); ?></td>
                                <td><?php echo esc_html($category->title); ?></td>
                                <td><?php echo esc_html($category->slug); ?></td>
                                <td><?php echo esc_html($category->created_at); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=ifaq-categories&action=edit&id=' . intval($category->id))); ?>">Edit</a>
                                    |
                                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=ifaq-categories&action=delete&id=' . intval($category->id)), 'delete_category_' . intval($category->id))); ?>"
                                        class="text-danger" onclick="return confirm('Are you sure you want to delete this category?');">Delete</a>

                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="5">No categories found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>