<?php if ( isset($xfa['quick']) ) : ?>
	<a
		href="<?php echo F::url($xfa['quick']); ?>"
		class="btn btn-sm py-0 px-1 btn-default scaffold-btn-quick-new"
		data-toggle="ajax-load"
		data-toggle-loading="none"
		data-toggle-mode="after"
		data-target="<?php echo "#{$scaffold['beanType']}-header"; ?>"
	><i class="fa fa-plus"></i> Quick</a>
<?php endif; ?>
<?php if ( isset($xfa['new']) ) : ?>
	<a
		href="<?php echo F::url($xfa['new']); ?>"
		class="btn btn-sm py-0 px-1 btn-info scaffold-btn-new"
		<?php if ( $scaffold['editMode'] == 'modal' ) : ?>
			data-toggle="modal"
			data-target="<?php echo "#{$scaffold['beanType']}-modal"; ?>"
			data-toggle-loading="none"
		<?php elseif ( $scaffold['editMode'] == 'inline' ) : ?>
			data-toggle="ajax-load"
			data-toggle-mode="after"
			data-target="<?php echo "#{$scaffold['beanType']}-header"; ?>"
			data-toggle-loading="none"
		<?php endif; ?>
	><i class="fa fa-plus"></i> New</a>
<?php endif; ?>