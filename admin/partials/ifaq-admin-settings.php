<?php
$settings = maybe_unserialize(get_option('ifaq_settings'),true);
//echo "<pre>";print_r($settings);echo "</pre>";exit();
$showSearchBox = filter_var($settings['showSearchBox'], FILTER_VALIDATE_BOOLEAN);
$enableCategories = filter_var($settings['enableCategories'], FILTER_VALIDATE_BOOLEAN);

?>
<div class="ifaq-container">
    <h1>FAQ Settings</h1>
    <p>Customize how frequently asked questions are displayed on your site.</p>

    <h2>Display Settings</h2>
    <div class="ifaq-form-group">
        <div class="ifaq-form-row">
            <label for="display-style">FAQ Display Style</label>
            <div class="input-field">
                <select id="display-style">
                    <?php foreach (['accordion', 'timeline', 'grid', 'table'] as $style): ?>
                        <option value="<?php echo esc_attr($style); ?>" <?php echo selected($settings['displayStyle'], $style, false); ?>>
                            <?php echo esc_html(ucfirst($style)); ?>
                        </option>

                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="ifaq-form-row">
            <label for="search-box">Show Search Box</label>
            <div class="input-field">
                <input type="checkbox" id="search-box" <?php echo checked($showSearchBox, true, false); ?> />
            </div>
        </div>

        <div class="ifaq-form-row">
            <label for="ifaq-limit">FAQs per Page</label>
            <div class="input-field">
                <input type="number" id="ifaq-limit" value="<?php echo esc_attr($settings['faqsPerPage']); ?>" min="1"/>
            </div>
        </div>

        <div class="ifaq-form-row">
            <label for="enable-ifaq-cat">Enable Categories</label>
            <div class="input-field">
                <input type="checkbox" id="enable-ifaq-cat" <?php echo checked($enableCategories, true, false); ?> />
            </div>
        </div>
    </div>

    <h2>Customization</h2>
    <div class="ifaq-form-group">
        <div class="ifaq-form-row">
            <label for="color-scheme">Color Scheme</label>
            <div class="input-field">
                <input type="color" id="color-scheme" value="<?php echo esc_attr($settings['colorScheme']); ?>"/>
            </div>
        </div>

        <div class="ifaq-form-row">
            <label for="font-style">Font Family</label>
            <div class="input-field">
                <select id="font-style">
                    <?php foreach (['Arial', 'Roboto', 'Open Sans'] as $font): ?>
                        <option value="<?php echo esc_attr($font); ?>" <?php echo selected($settings['fontStyle'], $font, false); ?>>
                            <?php echo esc_html($font); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="ifaq-form-row">
            <label for="icon-style">Icon Style</label>
            <div class="input-field">
                <select id="icon-style">
                    <?php foreach (['Plus/Minus', 'Chevron', 'Arrow'] as $icon): ?>
                        <option value="<?php echo esc_attr($icon); ?>" <?php echo selected($settings['iconStyle'], $icon, false); ?>>
                            <?php echo esc_html($icon); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button class="button button-primary ifaq-sattings-save" data-attribute-action="save">Save Changes</button>
        <button class="button button-secondary ifaq-sattings-reset-default ifaq-sattings-save" data-attribute-action="reset">Reset to Default</button>
    </div>

    <div id="ifaq-loader" style="display:none; text-align:center; margin-top:10px;">
        <span class="spinner is-active"></span> Saving FAQ...
    </div>
    <div id="ifaq-message" style="display:none; margin-top:10px; position:relative;">
        <span class="ifaq-close" style="position:absolute; right:10px; top:8px; cursor:pointer;">&times;</span>
        <span class="ifaq-message-text"></span>
    </div>
</div>
