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
				<string name="editMode" comments="inline|modal|inline-modal|basic" />
				<string name="modalSize" comments="sm|md|lg|xl|max" />
				<structure name="modalField">
					<list name="~columnList~" optional="yes" value="~columnWidthList~" delim="|" />
					<string name="~line~" optional="yes" example="---" />
					<string name="~heading~" optional="yes" example="## General" comments="number of pound-signs means H1,H2,H3..." />
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
*/
$recordID = empty($bean->id) ? uuid() : $bean->id;

// display
?><form
	id="<?php echo $scaffold['beanType']; ?>-edit-<?php echo $recordID; ?>"
	class="scaffold-edit <?php if ( $scaffold['editMode'] == 'inline-modal' ) echo 'card bg-light my-3'; ?>"
	<?php if ( isset($xfa['submit']) ) : ?>
		method="post"
		action="<?php echo F::url($xfa['submit']); ?>"
	<?php endif; ?>
	<?php if ( $scaffold['editMode'] == 'modal' and !empty($bean->id) ) : ?>
		data-toggle="ajax-submit"
		data-target="#<?php echo $scaffold['beanType']; ?>-row-<?php echo $recordID; ?>"
		data-callback="function(){ $('#<?php echo $scaffold['beanType']; ?>-modal').modal('hide'); }"
	<?php elseif ( $scaffold['editMode'] == 'modal' ) : ?>
		data-toggle="ajax-submit"
		data-mode="after"
		data-target="#<?php echo $scaffold['beanType']; ?>-header"
		data-callback="function(){ $('#<?php echo $scaffold['beanType']; ?>-modal').modal('hide'); }"
	<?php elseif ( $scaffold['editMode'] == 'inline-modal' ) : ?>
		data-toggle="ajax-submit"
		data-target="#<?php echo $scaffold['beanType']; ?>-edit-<?php echo $recordID; ?>"
	<?php endif; ?>
><?php

	// title
	if ( in_array($scaffold['editMode'], ['modal','inline-modal']) ) :
		?><div class="modal-header"><?php
			?><h5 class="modal-title"><?php echo ucfirst(F::command('action')); ?></h5><?php
			// close button @ modal
			if ( $scaffold['editMode'] == 'modal' ) :
				?><button 
					type="button"
					class="close scaffold-btn-close"
					data-dismiss="modal"
					aria-label="Close"
				><span aria-hidden="true">&times;</span></button><?php
			// canel button @ inline-modal
			elseif ( $scaffold['editMode'] == 'inline-modal' and isset($xfa['cancel']) ) :
				?><a 
					href="<?php echo F::url($xfa['cancel']); ?>"
					class="close scaffold-btn-cancel"
					data-toggle="ajax-load"
					data-target="#<?php echo $scaffold['beanType']; ?>-edit-<?php echo $recordID; ?>"
				><span aria-hidden="true">&times;</span></a><?php
				?><?php
			endif;
		?></div><!--/.modal-header--><?php
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
			$isHeading = ( strlen($colList) != strlen(ltrim($colList, '#')) );
			$isLine = ( !empty($colList) and trim($colList, '-') == '' );
			// output : heading
			if ( $isHeading ) :
				$size = 'h'.( strlen($colList) - strlen(ltrim($colList, '#')) );
				?><div class="<?php echo $size; ?>"><?php echo trim(ltrim($colList, '#')); ?></div><?php
			// output : line
			elseif ( $isLine ) :
				?><hr /><?php
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

	// button @ modal
	if ( in_array($scaffold['editMode'], ['modal','inline-modal']) ) :
		?><div class="modal-footer"><?php
			// close button @ modal
			if ( $scaffold['editMode'] == 'modal' ) :
				?><button 
					type="button"
					class="btn btn-link text-dark scaffold-btn-close"
					data-dismiss="modal"
				>Close</button><?php
			// canel button @ inline-modal
			elseif ( $scaffold['editMode'] == 'inline-modal' and isset($xfa['cancel']) ) :
				?><a 
					href="<?php echo F::url($xfa['cancel']); ?>"
					class="btn btn-link text-dark scaffold-btn-cancel"
					data-toggle="ajax-load"
					data-target="#<?php echo $scaffold['beanType']; ?>-edit-<?php echo $recordID; ?>"
				>Cancel</a><?php
			endif;
			// submit button
			if ( isset($xfa['submit']) ) :
				?><button 
					type="submit" 
					class="btn btn-primary scaffold-btn-save ml-1"
				>Save changes</button><?php
			endif;
		?></div><!--/.modal-footer--><?php
	// button @ basic
	elseif ( $scaffold['editMode'] == 'basic' ) :
		?><div class="col-10 offset-2"><?php
			if ( isset($xfa['submit']) ) :
				?><button 
					type="submit"
					class="btn btn-primary scaffold-btn-save mr-1"
				>Save changes</button><?php
			endif;
			?><a 
				href="javascript:history.back();" 
				class="btn btn-link text-dark scaffold-btn-cancel"
			>Cancel</a>
		</div><?php
	endif;

?></form>