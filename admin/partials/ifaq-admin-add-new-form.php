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
            <label for="ifaq_category">Category<span class="ifaq_required">*</span> </label>
            <div class="input-field">
                <input type="text" id="ifaq_category" value="General"/>
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
