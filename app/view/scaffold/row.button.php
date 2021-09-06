<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$xfa">
				<string name="edit" optional="yes" />
				<string name="enable" optional="yes" />
				<string name="disabled" optional="yes" />
				<string name="delete" optional="yes" />
			</structure>
			<object name="$bean">
				<number name="id" />
				<boolean name="disabled" />
			</object>
			<structure name="$scaffold">
				<string name="modalSize" />
				<string name="editMode" comments="inline|modal|inline-modal|basic" />
			</structure>
		</in>
		<out>
			<number name="id" scope="url" oncondition="xfa.edit|xfa.enable|xfa.disable|xfa.delete" />
		</out>
	</io>
</fusedoc>
*/

// edit button
if ( isset($xfa['edit']) and  !( isset($xfa['disable']) and $bean->disabled ) ) :
	?><a
		href="<?php echo F::url("{$xfa['edit']}&id={$bean->id}"); ?>"
		class="btn btn-xs px-1 btn-light scaffold-btn-edit"
		<?php if ( $scaffold['editMode'] == 'modal' ) : ?>
			data-toggle="ajax-modal"
			data-target="#global-modal-<?php echo $scaffold['modalSize']; ?>"
		<?php elseif ( in_array($scaffold['editMode'], ['inline','inline-modal']) ) : ?>
			data-toggle="ajax-load"
			data-target="#<?php echo $scaffold['beanType']; ?>-row-<?php echo $bean->id; ?>"
		<?php endif; ?>
	><i class="fa fa-pen"></i> Edit</a> <?php
endif;

// enable button
if ( isset($xfa['enable']) and $bean->disabled ) :
	?><a
		href="<?php echo F::url("{$xfa['enable']}&id={$bean->id}"); ?>"
		class="btn btn-xs px-1 btn-success scaffold-btn-enable"
		data-toggle="ajax-load"
		data-target="#<?php echo $scaffold['beanType']; ?>-row-<?php echo $bean->id; ?>"
	><i class="fa fa-undo"></i> Enable</a> <?php
endif;

// disable button
if ( isset($xfa['disable']) and !$bean->disabled ) :
	?><a
		href="<?php echo F::url("{$xfa['disable']}&id={$bean->id}"); ?>"
		class="btn btn-xs px-1 btn-warning text-white scaffold-btn-disable"
		data-toggle="ajax-load"
		data-target="#<?php echo $scaffold['beanType']; ?>-row-<?php echo $bean->id; ?>"
	><i class="far fa-trash-alt"></i> Disable</a> <?php
endif;

// delete button
if ( isset($xfa['delete']) ) :
	?><a
		href="<?php echo F::url("{$xfa['delete']}&id={$bean->id}"); ?>"
		class="btn btn-xs px-1 btn-danger scaffold-btn-delete"
		data-toggle="ajax-load"
		data-confirm="You cannot undo this.  Are you sure to delete?"
		data-target="#<?php echo $scaffold['beanType']; ?>-row-<?php echo $bean->id; ?>"
	><i class="fa fa-exclamation-triangle"></i> Delete</a> <?php
endif;
