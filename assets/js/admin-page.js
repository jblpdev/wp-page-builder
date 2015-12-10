/*

*/
(function($) {

$(document).ready(function() {

	$('.wp-admin.post-type-page').each(function(i, element) {

		var showBlockPickerModal = function() {
			$('.block-picker-modal').addClass('block-picker-modal-visible')
		}

		var hideBlockPickerModal = function() {
			$('.block-picker-modal').removeClass('block-picker-modal-visible')
		}

		var showBlockEditModal = function(url) {
			$('.block-edit-modal').addClass('block-edit-modal-visible')
			$('.block-edit-modal iframe').attr('src', url)
		}

		var hideBlockEditModal = function() {
			$('.block-edit-modal').removeClass('block-edit-modal-visible')
			$('.block-edit-modal iframe').attr('src', '')
		}

		var appendBlock = function(type) {

			var post = $('#post_ID').val()
			if (post == null)  {
				return;
			}

			$.post(ajaxurl, {
				'action': 'add_page_block',
				'block_post': post,
				'block_template': type,
			}, function(result) {
				$('.page-blocks').append(setupBlock(result))
			})
		}

		var setupBlock = function(block) {

			block = $(block)
			block.find('.page-block-edit a').on('click', function(e) {
				e.preventDefault()
				e.stopPropagation()
				showBlockEditModal($(this).attr('href'))
			})

			return block
		}

		$('.page-blocks').disableSelection();
		$('.page-blocks').sortable()
		$('.page-blocks').each(function(i, element) {
			setupBlock(element)
		})

		$('.block-picker-modal-show').on('click', showBlockPickerModal)
		$('.block-picker-modal-hide').on('click', hideBlockPickerModal)
		$('.block-edit-modal-hide').on('click', hideBlockEditModal)

		$('.block-template-info-action .button-insert').on('click', function() {

			var type = $(this).closest('.block-template-info').attr('data-block-template')
			if (type == null) {
				hideBlockPickerModal()
				return
			}

			appendBlock(type)

			hideBlockPickerModal()
		})
	})
})

})(jQuery);