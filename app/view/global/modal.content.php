<div <?php if ( isset($layout['modalID']) ) : ?>id="<?php echo $layout['modalID']; ?>"<?php endif; ?>>
	<div class="modal-content">
		<?php if ( isset($layout['modalTitle']) ) : ?>
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">
					<span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
				</button>
				<h4 class="modal-title"><?php echo $layout['modalTitle']; ?></h4>
			</div>
		<?php endif; ?>
		<?php if ( isset($layout['modalBody']) ) : ?>
			<div class="modal-body"><?php echo $layout['modalBody']; ?></div>
		<?php endif; ?>
		<div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
		</div>
	</div>
</div>