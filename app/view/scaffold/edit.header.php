<?php /*
<fusedoc>
	<io>
		<in>
			<string name="controller" scope="$fusebox" />
			<string name="action" scope="$fusebox" />
			<structure name="$xfa">
				<string name="cancel" optional="yes" />
			</structure>
			<structure name="$options">
				<string name="formType" comments="modal|inline-modal|basic" />
			</structure>
			<string name="$recordID" />
		</in>
		<out />
	</io>
</fusedoc>
*/
// title
?><h5 class="modal-title"><?php echo ucfirst(F::command('action')); ?></h5><?php

// close button @ modal
if ( $options['formType'] == 'modal' ) :
	?><button 
		type="button"
		class="close scaffold-btn-close"
		data-dismiss="modal"
		aria-label="Close"
	><span aria-hidden="true">&times;</span></button><?php

// canel button @ inline-modal
elseif ( $options['formType'] == 'inline-modal' and isset($xfa['cancel']) ) :
	?><a 
		href="<?php echo F::url($xfa['cancel']); ?>"
		class="close scaffold-btn-cancel"
		data-toggle="ajax-load"
		data-target="#<?php echo F::command('controller'); ?>-edit-<?php echo $recordID; ?>"
	><span aria-hidden="true">&times;</span></a><?php
	?><?php
endif;