<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$xfa">
				<string name="submit" optional="yes" />
			</structure>
			<structure name="$scaffold">
				<string name="beanType" />
				<string name="editMode" comments="inline|modal|basic" />
				<string name="modalSize" comments="sm|md|lg|xl|max" />
				<structure name="modalField">
					<list name="~column list~" comments="value is column width list" delim="|" />
				</structure>
				<structure name="fieldConfig">
					<structure name="~column~">
						<string name="label" comments="display name at table/form header" />
						<string name="format" comments="normal|output|textarea|checkbox|radio" default="normal" />
						<array name="options" comments="show dropdown when specified">
							<string name="~key is option-value~" comments="value is option-text" />
						</array>
						<boolean name="readonly" comments="output does not pass value; readonly does" />
						<string name="placeholder" default="column display name" />
						<string name="help" />
						<boolean name="required" />
					</structure>
				</structure>
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/ ?>
<?php $recordID = empty($bean->id) ? uuid() : $bean->id; ?>
<form
	id="<?php echo $scaffold['beanType']; ?>-edit"
	class="scaffold-edit"
	<?php if ( isset($xfa['submit']) ) : ?>
		method="post"
		action="<?php echo F::url($xfa['submit']); ?>"
	<?php endif; ?>
	<?php if ( $scaffold['editMode'] == 'modal' ) : ?>
		<?php if ( !empty($bean->id) ) : ?>
			data-toggle-mode="replace"
			data-target="#<?php echo $scaffold['beanType']; ?>-row-<?php echo $recordID; ?>"
		<?php else : ?>
			data-toggle-mode="after"
			data-target="#<?php echo $scaffold['beanType']; ?>-header"
		<?php endif; ?>
		data-toggle-callback="function(){ $('#<?php echo $scaffold['beanType']; ?>-modal').modal('hide'); }"
		data-toggle="ajax-submit"
	<?php endif; ?>
><?php

	// title
	if ( $scaffold['editMode'] == 'modal' ) :
		?><div class="modal-header">
			<h5 class="modal-title"><?php echo ucfirst(F::command('action')); ?></h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		</div><!--/.modal-header--><?php
	endif;

	// body
	?><div class="modal-body"><?php
		// message (if any)
		if ( isset($arguments['flash']) ) :
			?><div class="alert alert-<?php echo isset($arguments['flash']['type']) ? $arguments['flash']['type'] : 'warning'; ?>"><?php
				echo isset($arguments['flash']['message']) ? $arguments['flash']['message'] : $arguments['flash'];
			?></div><?php
		endif;
		// form fields
		foreach ( $scaffold['modalField'] as $colList => $colWidthList ) :
			// output : horizontal line
			if ( $colList == str_repeat('-', strlen($colList)) ) :
				?><hr /><?php
			// output : title
			elseif ( substr($colList, 0, 1).substr($colList, -1) == '[]' ) :
				?><fieldset><legend><?php echo str_replace('[', '', str_replace(']', '', $colList)); ?></legend></fieldset><?php
			// input field
			else :
				$colList = explode('|', $colList);
				$colWidthList = explode('|', $colWidthList);
				?><div class="form-group row">
					<label class="col-2 col-form-label col-form-label-sm text-right"><?php
						foreach ( $colList as $i => $col ) :
							$headerText = $scaffold['fieldConfig'][$col]['label'];
							if ( $i == 0 ) {
								echo $headerText;
							} elseif ( !empty($headerText) ) {
								?><small class="text-muted"> / <?php echo $headerText; ?></small><?php
							}
						endforeach;
					?></label>
					<div class="col-10">
						<div class="row"><?php
							foreach ( $colList as $i => $col ) :
								?><div class="col-sm-<?php echo $colWidthList[$i]; ?>"><?php
									$field = $scaffold['fieldConfig'][$col] + array('name' => $col);
									include F::appPath('view/scaffold/input.php');
								?></div><?php
							endforeach;
						?></div><!--/.row-->
					</div><!--/.col-->
				</div><!--/.form-group--><?php
			endif;
		endforeach;
	?></div><!--/.modal-body--><?php

	// button
	if ( $scaffold['editMode'] == 'modal' ) :
		?><div class="modal-footer">
			<button type="button" class="btn btn-light scaffold-btn-close" data-dismiss="modal">Close</button> <?php
			if ( isset($xfa['submit']) ) :
				?><button type="submit" class="btn btn-primary scaffold-btn-save">Save changes</button> <?php
			endif;
		?></div><!--/.modal-footer--><?php
	elseif ( $scaffold['editMode'] == 'basic' ) :
		?><div class="col-10 offset-2"><?php
			if ( isset($xfa['submit']) ) :
				?><button type="submit" class="btn btn-primary scaffold-btn-save">Save changes</button> <?php
			endif;
			?><a href="javascript:history.back();" class="btn btn-light scaffold-btn-cancel">Cancel</a>
		</div><?php
	endif;

?></form>