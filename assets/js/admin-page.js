/*

*/
(function($) {

$(document).ready(function() {

	$('.wp-admin.post-type-page').each(function(i, element) {

		var currentModal = null
		var currentModalParentAreaId = null
		var currentModalParentPostId = null
		var currentModalParentPageId = null

		var showBlockPickerModal = function() {
			currentModalParentAreaId = $(this).closest('.block').attr('data-area-id') || 0
			currentModalParentPostId = $(this).closest('.block').attr('data-post-id') || 0
			currentModalParentPageId = $(this).closest('.block').attr('data-page-id') || 0
			$('.block-picker-modal').addClass('block-picker-modal-visible')
		}

		var hideBlockPickerModal = function() {
			$('.block-picker-modal').removeClass('block-picker-modal-visible')
		}

		var showBlockEditModal = function(url, source) {
			currentModal = source
			$('.block-edit-modal').addClass('block-edit-modal-visible')
			$('.block-edit-modal iframe').attr('src', url)
		}

		var hideBlockEditModal = function() {
			$('.block-edit-modal').removeClass('block-edit-modal-visible')
			$('.block-edit-modal iframe').attr('src', '')
			currentModal = null
		}

		var appendBlock = function(buid) {

			var pageId = $('#post_ID').val()
			if (pageId == null)  {
				return;
			}

			$.post(ajaxurl, {
				'action': 'add_page_block',
				'buid': buid,
				'page_id': pageId,
				'into_id': currentModalParentPostId,
				'area_id': currentModalParentAreaId,
			}, function(result) {
				$('.blocks').append(setupBlock(result))
			})
		}

		var setupBlock = function(block) {

			block = $(block)
			block.find('.block-edit a').on('click', function(e) {

				e.preventDefault()
				e.stopPropagation()

				showBlockEditModal($(this).attr('href'), $(this).closest('.block').attr('data-post-id'))
			})

			return block
		}

		$('.blocks').disableSelection();
		$('.blocks').sortable()
		$('.blocks').each(function(i, element) {
			setupBlock(element)
		})

		$('.block-picker-modal-show').on('click', showBlockPickerModal)
		$('.block-picker-modal-hide').on('click', hideBlockPickerModal)
		$('.block-edit-modal-hide').on('click', hideBlockEditModal)

		$('.block-template-info-action .button-insert').on('click', function() {

			var buid = $(this).closest('.block-template-info').attr('data-buid')
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