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

		// Toggle FAQ answer visibility when question is clicked
		$('.ifaq-question').on('click', function () {
			var $answer = $(this).next('.ifaq-answer');
			$(this).toggleClass('active');
			$answer.slideToggle(200);
		});

		// Handle FAQ form submission via AJAX
		$("#ifaq-add-new-form button").on('click', function (e) {
			e.preventDefault();

			const ifaq_question = $("#ifaq_question").val(); // Get question
			const ifaq_answer = $("#ifaq_answer").val();     // Get answer
			const ifaq_category = $("#ifaq_category").val(); // Get category (fixed selector)
			const ifaq_status = $("#ifaq_status").val();     // Get status

			$.ajax({
				url: ifaq_ajax.ajax_url,
				method: 'post',
				data: {
					action: 'save_ifaq_new',
					question: ifaq_question,
					answer: ifaq_answer,
					ifaq_category: ifaq_category,
					ifaq_status: ifaq_status,
					nonce: ifaq_ajax.ifaq_nonce,
				},
				success: function (response) {
					console.log('FAQ saved:', response);
					// Optionally show success message or reset form
				},
				error: function () {
					console.error('Error saving FAQ.');
					// Optionally show error message
				}
			});
		});
	});

})( jQuery );
