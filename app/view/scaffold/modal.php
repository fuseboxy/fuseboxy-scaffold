<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$scaffold">
				<string name="beanType" />
				<string name="modalSize" comments="sm|md|lg|xl|max" />
			</structure>
		</in>
	</io>
</fusedoc>
*/ ?>
<div id="<?php echo $scaffold['beanType']; ?>-modal" class="scaffold-modal modal fade" data-backdrop="static" data-nocache role="dialog" aria-labelledby="<?php echo $scaffold['beanType']; ?>-modal-label" aria-hidden="true" tabindex="-1">
	<div class="modal-dialog modal-<?php echo $scaffold['modalSize']; ?>">
		<div class="modal-content"></div>
	</div>
</div>