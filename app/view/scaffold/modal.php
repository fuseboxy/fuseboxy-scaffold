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
<div
	id="<?php echo $scaffold['beanType']; ?>-modal"
	class="scaffold-modal modal fade"
	data-backdrop="true"
	tabindex="-1"
	role="dialog"
	aria-hidden="true"
	tabindex="-1"
	aria-labelledby="<?php echo $scaffold['beanType']; ?>-modal-label" 
><div class="modal-dialog modal-<?php echo $scaffold['modalSize']; ?>"><div class="modal-content"></div></div></div>