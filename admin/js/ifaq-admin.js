(function( $ ) {
	'use strict';

	/**
	 * This file handles all admin-facing JavaScript functionality for the plugin.
	 *
	 * The $ alias is safely scoped via the IIFE, so jQuery can be used without conflicts.
	 *
	 * DOM-ready events, click handlers, and AJAX requests related to admin interactions
	 * should be defined here.
	 */

	$(document).ready(function($) {

		// Toggle FAQ answer visibility
		$('.ifaq-question').on('click', function () {
			var $answer = $(this).next('.ifaq-answer');
			$(this).toggleClass('active');
			$answer.slideToggle(200);
		});

		// Handle FAQ form submission via AJAX
		$("#ifaq-add-new-form button").on('click', function (e) {
			e.preventDefault();

			const ifaqQuestion = $("#ifaq_question").val().trim();
			const ifaqAnswer = $("#ifaq_answer").val().trim();
			const ifaqCategories = $('input[name="ifaq_category[]"]:checked').get().map(el => el.value);
			const ifaqStatus = $("#ifaq_status").val();

			// Submit via AJAX
			$.ajax({
				url: ifaq_ajax.ajax_url,
				method: 'post',
				data: {
					action: 'save_ifaq_new',
					ifaqQuestion: ifaqQuestion,
					ifaqAnswer: ifaqAnswer,
					ifaqCategories: ifaqCategories,
					ifaqStatus: ifaqStatus,
					nonce: ifaq_ajax.ifaq_nonce,
				},
				success: function (response) {
					if (response.status === 201) {
						// Optionally show success message
						alert(response.message);

						// Reset form fields
						$("#ifaq_question").val('');
						$("#ifaq_answer").val('');
						$('input[name="ifaq_category[]"]').prop('checked', false);
						$("#ifaq_status").val('Active');
					} else {
						alert('Something went wrong.'); // Fallback
					}
				}
			});
		});
	});

})( jQuery );
