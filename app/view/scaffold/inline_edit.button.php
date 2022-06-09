<?php /*
<fusedoc>
	<io>
		<in>
			<string name="controller" scope="$fusebox" />
			<structure name="$xfa">
				<string name="submit" optional="yes" />
				<string name="cancel" optional="yes" />
			</structure>
			<string name="$recordID" />
		</in>
		<out />
	</io>
</fusedoc>
*/

// submit button
if ( isset($xfa['submit']) ) :
	?><button
		type="submit"
		class="btn btn-xs px-1 btn-primary scaffold-btn-save"
	><i class="fa fa-download"></i> Save</button> <?php
endif;

// cancel button
if ( isset($xfa['cancel']) ) :
	?><a
		href="<?php echo F::url($xfa['cancel']); ?>"
		class="btn btn-xs px-1 btn-link text-dark scaffold-btn-cancel"
		data-toggle="ajax-load"
		data-target="#<?php echo F::command('controller'); ?>-inline-edit-<?php echo $recordID; ?>"
   >Cancel</a> <?php
endif;