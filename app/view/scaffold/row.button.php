<?php if ( isset($xfa['edit']) and  !( isset($xfa['disable']) and $bean->disabled ) ) : ?>
	<a
		href="<?php echo F::url("{$xfa['edit']}&id={$bean->id}"); ?>"
		class="btn btn-xs btn-default scaffold-btn-edit"
		<?php if ( $scaffold['editMode'] == 'modal' ) : ?>
			data-toggle="modal"
			data-target="#<?php echo $scaffold['beanType']; ?>-modal"
		<?php elseif ( $scaffold['editMode'] == 'inline' ) : ?>
			data-toggle="ajax-load"
			data-target="#<?php echo $scaffold['beanType']; ?>-row-<?php echo $bean->id; ?>"
		<?php endif; ?>
	><i class="fa fa-pencil"></i> Edit</a>
<?php endif; ?>
<?php if ( isset($xfa['enable']) and $bean->disabled ) : ?>
	<a
		href="<?php echo F::url("{$xfa['enable']}&id={$bean->id}"); ?>"
		class="btn btn-xs btn-success scaffold-btn-enable"
		data-toggle="ajax-load"
		data-target="#<?php echo $scaffold['beanType']; ?>-row-<?php echo $bean->id; ?>"
	><i class="fa fa-undo"></i> Enable</a>
<?php endif; ?>
<?php if ( isset($xfa['disable']) and !$bean->disabled ) : ?>
	<a
		href="<?php echo F::url("{$xfa['disable']}&id={$bean->id}"); ?>"
		class="btn btn-xs btn-warning scaffold-btn-disable"
		data-toggle="ajax-load"
		data-target="#<?php echo $scaffold['beanType']; ?>-row-<?php echo $bean->id; ?>"
	><i class="fa fa-trash-o"></i> Disable</a>
<?php endif; ?>
<?php if ( isset($xfa['delete']) ) : ?>
	<a
		href="<?php echo F::url("{$xfa['delete']}&id={$bean->id}"); ?>"
		class="btn btn-xs btn-danger scaffold-btn-delete"
		data-toggle="ajax-load"
		data-confirm="You cannot undo this.  Are you sure to delete?"
		data-target="#<?php echo $scaffold['beanType']; ?>-row-<?php echo $bean->id; ?>"
	><i class="fa fa-exclamation-triangle"></i> Delete</a>
<?php endif; ?>