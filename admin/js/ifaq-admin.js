(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
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

    $(document).ready(function($) {
        $('.ifaq-question').on('click', function () {
            var $answer = $(this).next('.ifaq-answer');
            $(this).toggleClass('active');
            $answer.slideToggle(200);
        });

		$("#ifaq-add-new-form button").on('click', function (e) {
			e.preventDefault();
			const ifaq_question = $("#ifaq_question").val(); // get textarea value
			const ifaq_answer = $("#ifaq_answer").val(); // get textarea value
			const ifaq_category = $("ifaq_category").val();
			const ifaq_status = $("#ifaq_status").val();

			$.ajax({
				url: ifaq_ajax.ajax_url,
				method:'post',
				data: {
					action: 'save_ifaq_new',
					question: ifaq_question,
					answer: ifaq_answer,
					ifaq_category: ifaq_category,
					ifaq_status: ifaq_status,
					nonce: ifaq_ajax.ifaq_nonce,
				},
				success: function (response) {
					console.log(response);
				},
				error: function () {

				}
			});
		});
    });

})( jQuery );
