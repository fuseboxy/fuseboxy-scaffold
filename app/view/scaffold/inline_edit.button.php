<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$xfa">
				<string name="submit" optional="yes" />
				<string name="cancel" optional="yes" />
			</structure>
			<structure name="$scaffold">
				<string name="beanType" />
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
		class="btn btn-sm py-0 px-1 btn-primary scaffold-btn-save"
	><i class="fa fa-download"></i> Save</button> <?php
endif;

// cancel button
if ( isset($xfa['cancel']) ) :
	?><a
		href="<?php echo F::url($xfa['cancel']); ?>"
		class="btn btn-sm py-0 px-1 btn-link text-dark scaffold-btn-cancel"
		data-toggle="ajax-load"
		data-target="#<?php echo $scaffold['beanType']; ?>-inline-edit-<?php echo $recordID; ?>"
   >Cancel</a> <?php
endif;