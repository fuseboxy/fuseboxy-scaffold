<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$scaffold">
				<string name="beanType" />
				<string name="editMode" comments="inline|modal|classic" />
				<string name="modalSize" optional="yes" comments="normal|large|max" />
			</structure>
		</in>
	</io>
</fusedoc>
*/ ?>
<div id="<?php echo $scaffold['beanType']; ?>-modal" class="scaffold-modal modal fade" data-backdrop="static" data-nocache role="dialog" aria-labelledby="<?php echo $scaffold['beanType']; ?>-modal-label" aria-hidden="true" tabindex="-1">
	<div class="modal-dialog <?php if ( $scaffold['modalSize'] == 'large' ) : ?>modal-lg<?php elseif ( $scaffold['modalSize'] == 'max' ) : ?>modal-max<?php endif; ?>">
		<div class="modal-content">
			<h1 class="text-muted text-center"><i class="fa fa-spinner fa-spin"></i></h1>
		</div>
	</div>
</div>