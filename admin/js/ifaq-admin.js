(function ($) {
    'use strict';

    $(document).ready(function () {

        // Toggle FAQ answer visibility
        $('.ifaq-question').on('click', function () {
            var $answer = $(this).next('.ifaq-answer');
            $(this).toggleClass('active');
            $answer.slideToggle(200);
        });

        // Handle FAQ form submission via AJAX
        $("#ifaq-form button").on('click', function (e) {
            e.preventDefault();
            // alert('test');return false;
            $("#ifaq-loader").show();
            $("#ifaq-message").hide().removeClass('success error').html('');

            const ifaqQuestion = $("#ifaq_question").val().trim();
            const ifaqAnswer = $("#ifaq_answer").val().trim();
            const ifaqCategories = $('input[name="ifaq_category[]"]:checked').get().map(el => el.value);
            const ifaqStatus = $("#ifaq_status").val();
            const isEdit = $(this).attr('data-attribute-action');
            const faq_id = $(this).attr('data-attribute-id');

            $.ajax({
                url: ifaq_ajax.ajax_url,
                method: 'post',
                data: {
                    action: 'save_ifaq_new',
                    ifaqQuestion,
                    ifaqAnswer,
                    ifaqCategories,
                    ifaqStatus,
                    isEdit,
                    faq_id,
                    nonce: ifaq_ajax.ifaq_nonce,
                },
                success: function (response) {
                    $("#ifaq-loader").hide();

                    const isError = response.success === false;
                    const messageText = response.message;

                    $("#ifaq-message")
                        .removeClass('success error')
                        .addClass(isError ? 'error' : 'success')
                        .html('<span class="ifaq-close" style="float:right; cursor:pointer;">&times;</span><span class="ifaq-message-text">' + messageText + '</span>')
                        .fadeIn();

                    setTimeout(() => {
                        $("#ifaq-message").fadeOut();
                    }, 3000);

                    if (!isError) {
                        $("#ifaq_question").val('');
                        $("#ifaq_answer").val('');
                        $('input[name="ifaq_category[]"]').prop('checked', false);
                        $("#ifaq_status").val('Active');
                    }
                },
                error: function (xhr, status, error) {
                    $("#ifaq-loader").hide();

                    const message = 'An unexpected error occurred. Please try again.';

                    $("#ifaq-message")
                        .removeClass('success')
                        .addClass('error')
                        .html('<span class="ifaq-close" style="float:right; cursor:pointer;">&times;</span><span class="ifaq-message-text">' + message + '</span>')
                        .fadeIn();

                    setTimeout(() => {
                        $("#ifaq-message").fadeOut();
                    }, 3000);
                }
            });
        });

        // Make dismissable
        $(document).on('click', '.ifaq-close', function () {
            $("#ifaq-message").fadeOut();
        });

        //Handle all the settings
        $('.ifaq-sattings-save').on('click', function (e) {
            e.preventDefault();
            const isSave = $(this).attr('data-attribute-action') === 'save';

            $("#ifaq-loader").show();

            const settingsData = {
                displayStyle: isSave ? $('#display-style').val() : 'accordion',
                showSearchBox: isSave ? $('#search-box').is(':checked') : false,
                faqsPerPage: isSave ? +$('#ifaq-limit').val() : 10,
                enableCategories: isSave ? $('#enable-ifaq-cat').is(':checked') : false,
                colorScheme: isSave ? $('#color-scheme').val() : '#007bff',
                fontStyle: isSave ? $('#font-style').val() : 'Arial',
                iconStyle: isSave ? $('#icon-style').val() : 'Plus/Minus'
            };

            $.ajax({
                url: ifaq_ajax.ajax_url,
                method: 'post',
                data: {
                    action: 'save_ifaq_settings',
                    settingsData:settingsData,
                    nonce: ifaq_ajax.ifaq_nonce,
                },
                success: function (response) {
                    $("#ifaq-loader").hide();

                    const isError = response.success === false;
                    const messageText = response.message;

                    $("#ifaq-message")
                        .removeClass('success error')
                        .addClass(isError ? 'error' : 'success')
                        .html('<span class="ifaq-close" style="float:right; cursor:pointer;">&times;</span><span class="ifaq-message-text">' + messageText + '</span>')
                        .fadeIn();

                    if (!isSave) {
                        $('#display-style').val('accordion');
                        $('#search-box').prop('checked', false);
                        $('#ifaq-limit').val(10);
                        $('#enable-ifaq-cat').prop('checked', false);
                        $('#color-scheme').val('#007bff');
                        $('#font-style').val('Arial');
                        $('#icon-style').val('Plus/Minus');
                    }
                    setTimeout(() => {
                        $("#ifaq-message").fadeOut();
                    }, 3000);
                },
                error: function (xhr, status, error) {
                    $("#ifaq-loader").hide();

                    $("#ifaq-message")
                        .removeClass('success')
                        .addClass('error')
                        .html('<span class="ifaq-close" style="float:right; cursor:pointer;">&times;</span><span class="ifaq-message-text">An unexpected error occurred. Please try again.</span>')
                        .fadeIn();

                    setTimeout(() => {
                        $("#ifaq-message").fadeOut();
                    }, 3000);
                }
            });
        });
    });

})(jQuery);
