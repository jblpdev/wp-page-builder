/*

*/
(function($) {

$(document).ready(function() {

	$('.wp-admin.post-type-page').each(function(i, element) {

		var currentBlockEditModal = null

		var showBlockPickerModal = function() {
			$('.block-picker-modal').addClass('block-picker-modal-visible')
		}

		var hideBlockPickerModal = function() {
			$('.block-picker-modal').removeClass('block-picker-modal-visible')
		}

		var showBlockEditModal = function(url, source) {
			currentBlockEditModal = source
			$('.block-edit-modal').addClass('block-edit-modal-visible')
			$('.block-edit-modal iframe').attr('src', url)
		}

		var hideBlockEditModal = function() {
			$('.block-edit-modal').removeClass('block-edit-modal-visible')
			$('.block-edit-modal iframe').attr('src', '')
			currentBlockEditModal = null
		}

		var appendBlock = function(buid) {

			var post = $('#post_ID').val()
			if (post == null)  {
				return;
			}

			$.post(ajaxurl, {
				'action': 'add_page_block',
				'block_page': post,
				'block_buid': buid,
			}, function(result) {
				$('.page-blocks').append(setupBlock(result))
			})
		}

		var setupBlock = function(block) {

			block = $(block)
			block.find('.page-block-edit a').on('click', function(e) {

				e.preventDefault()
				e.stopPropagation()

				showBlockEditModal($(this).attr('href'), $(this).closest('.page-block').find('input[name="_page_blocks_id[]"]').val())
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

			var buid = $(this).closest('.block-template-info').attr('data-block-buid')
			if (buid == null) {
				hideBlockPickerModal()
				return
			}

			appendBlock(buid)

			hideBlockPickerModal()
		})

		window.wpbHideBlockEditModal = hideBlockEditModal
	})
})

})(jQuery);