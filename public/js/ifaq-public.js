(function ($) {
    'use strict';

    /**
     * All of the code for your public-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
     *
     * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */
    $(document).ready(function () {
        // Toggle FAQ answer visibility
        $('.ifaq-question').on('click', function () {
            const $answer = $(this).next('.ifaq-answer');
            $(this).toggleClass('active');
            $answer.slideToggle(200);
        });

        //Category On Changes
        $('#ifaq-category-filter').on('change', function () {
            const selectedCategory = $(this).val();
            const search = $(".ifaq-search-input").val();

            $.ajax({
                url: ifaq_frontend_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'filter_ifaq_by_category',
                    category: selectedCategory,
                    search: search,
                    nonce: ifaq_frontend_ajax.ifaq_frontend_nonce,
                },
                beforeSend: function () {
                    // Optional: show loader
                },
                success: function (response) {
                    // Handle the response (e.g., update a div with new content)
                    $('#ifaq-results').html(response);
                },
                error: function (xhr, status, error) {
                    console.log('AJAX Error:', error);
                }
            });
        });
    });

})(jQuery);
