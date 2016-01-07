/*

*/
(function($) {

$(document).ready(function() {

	$('.wp-admin.post-type-page').each(function(i, element) {

		var blockPickerAreaId = null
		var blockPickerPostId = null
		var blockPickerPageId = null

		var showBlockPicker = function() {
			blockPickerAreaId = $(this).attr('data-area-id') || 0
			blockPickerPostId = $(this).closest('[data-post-id]').attr('data-post-id') || 0
			blockPickerPageId = $(this).closest('[data-page-id]').attr('data-page-id') || 0
			$('.block-picker-modal').addClass('block-picker-modal-visible')
		}

		var hideBlockPicker = function() {
			$('.block-picker-modal').removeClass('block-picker-modal-visible')
		}

		var showBlockEditor = function(url, source) {
			$('.block-edit-modal').addClass('block-edit-modal-visible')
			$('.block-edit-modal iframe').attr('src', url)
		}

		var hideBlockEditor = function() {
			$('.block-edit-modal').removeClass('block-edit-modal-visible')
			$('.block-edit-modal iframe').attr('src', '')
		}

		var appendBlock = function(blocks, buid) {

			var pageId = $('#post_ID').val()
			if (pageId == null)  {
				return;
			}

			$.post(ajaxurl, {
				'action': 'add_page_block',
				'buid': buid,
				'page_id': pageId,
				'into_id': blockPickerPostId,
				'area_id': blockPickerAreaId,
			}, function(result) {
				blocks.append(setupBlock(result))
			})
		}

		var setupBlock = function(block) {

			block = $(block)
			block.find('.block-edit a').on('click', function(e) {

				e.preventDefault()
				e.stopPropagation()

				showBlockEditor($(this).attr('href'), $(this).closest('.block').attr('data-post-id'))
			})

			block.sortable()
			block.disableSelection()

			return block
		}

		$('.blocks').each(function(i, element) {
			setupBlock(element)
		})

		$('.block-picker-modal-show').on('click', showBlockPicker)
		$('.block-picker-modal-hide').on('click', hideBlockPicker)
		$('.block-edit-modal-hide').on('click', hideBlockEditor)

		$('.block-template-info-action .button-insert').on('click', function() {

			var buid = $(this).closest('.block-template-info').attr('data-buid')
			if (buid == null) {
				hideBlockPicker()
				return
			}

			var blocks = $('.blocks[data-area-id="' + blockPickerAreaId + '"]')
			if (blocks.length == 0) {
				blocks = $('.blocks')
			}

			appendBlock(blocks, buid)

			hideBlockPicker()
		})
	})
})

})(jQuery);