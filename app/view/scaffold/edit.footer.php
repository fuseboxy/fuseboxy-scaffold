<?php /*
<fusedoc>
	<io>
		<in>
			<string name="controller" scope="$fusebox" />
			<structure name="$xfa">
				<string name="submit" optional="yes" />
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
// button @ modal
if ( in_array($options['formType'], ['modal','inline-modal']) ) :
	// close button @ modal
	if ( $options['formType'] == 'modal' ) :
		?><button 
			type="button"
			class="btn btn-link text-dark scaffold-btn-close"
			data-dismiss="modal"
		>Close</button><?php
	// canel button @ inline-modal
	elseif ( $options['formType'] == 'inline-modal' and isset($xfa['cancel']) ) :
		?><a 
			href="<?php echo F::url($xfa['cancel']); ?>"
			class="btn btn-link text-dark scaffold-btn-cancel"
			data-toggle="ajax-load"
			data-target="#<?php echo F::command('controller'); ?>-edit-<?php echo $recordID; ?>"
		>Cancel</a><?php
	endif;
	// submit button
	if ( isset($xfa['submit']) ) :
		?><button 
			type="submit" 
			class="btn btn-primary scaffold-btn-save ml-1"
		>Save changes</button><?php
	endif;

// button @ basic
elseif ( $options['formType'] == 'basic' ) :
	if ( isset($xfa['submit']) ) :
		?><button 
			type="submit"
			class="btn btn-primary scaffold-btn-save mr-1"
		>Save changes</button><?php
	endif;
	?><a 
		href="javascript:history.back();" 
		class="btn btn-link text-dark scaffold-btn-cancel"
	>Cancel</a><?php
endif;