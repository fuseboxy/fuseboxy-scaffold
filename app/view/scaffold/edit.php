<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$xfa">
				<string name="submit" optional="yes" />
			</structure>
			<structure name="$scaffold">
				<string name="beanType" />
				<string name="editMode" comments="inline|modal|classic" />
				<string name="modalSize" comments="normal|large|max" />
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
	class="scaffold-edit form-horizontal"
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
>
	<!-- title -->
	<?php if ( $scaffold['editMode'] == 'modal' ) : ?>
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h4 class="modal-title"><?php echo ucfirst(F::command('action')); ?></h4>
		</div>
	<?php endif; ?>
	<div class="modal-body">
		<!-- message (if any) -->
		<?php if ( isset($arguments['flash']) ) : ?>
			<div class="alert alert-<?php echo isset($arguments['flash']['type']) ? $arguments['flash']['type'] : 'warning'; ?>">
				<?php echo isset($arguments['flash']['message']) ? $arguments['flash']['message'] : $arguments['flash']; ?>
			</div>
		<?php endif; ?>
		<!-- form fields -->
		<?php foreach ( $scaffold['modalField'] as $colList => $colWidthList ) : ?>
			<!-- output : horizontal line -->
			<?php if ( $colList == str_repeat('-', strlen($colList)) ) : ?>
				<hr />
			<!-- output : title -->
			<?php elseif ( substr($colList, 0, 1).substr($colList, -1) == '[]' ) : ?>
				<fieldset><legend><?php echo str_replace('[', '', str_replace(']', '', $colList)); ?></legend></fieldset>
			<!-- input field -->
			<?php else : ?>
				<?php $colList = explode('|', $colList); ?>
				<?php $colWidthList = explode('|', $colWidthList); ?>
				<div class="form-group">
					<label class="control-label <?php if ( isset($scaffold['modalSize']) and $scaffold['modalSize'] == 'max' ) : ?>col-sm-2<?php else : ?>col-sm-3<?php endif; ?>"><?php
						foreach ( $colList as $i => $col ) :
							$headerText = $scaffold['fieldConfig'][$col]['label'];
							if ( $i == 0 ) {
								echo $headerText;
							} elseif ( !empty($headerText) ) {
								?><small class="text-muted"> / <?php echo $headerText; ?></small><?php
							}
						endforeach;
					?></label>
					<div class="row clearfix <?php if ( isset($scaffold['modalSize']) and $scaffold['modalSize'] == 'max' ) : ?>col-sm-10<?php else : ?>col-sm-9<?php endif; ?>">
						<?php foreach ( $colList as $i => $col ) : ?>
							<div class="col-sm-<?php echo $colWidthList[$i]; ?>">
								<?php $field = $scaffold['fieldConfig'][$col] + array('name' => $col); ?>
								<?php include 'input.php'; ?>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
	<!-- button -->
	<?php if ( $scaffold['editMode'] == 'modal' ) : ?>
		<div class="modal-footer">
			<button type="button" class="btn btn-default scaffold-btn-close" data-dismiss="modal">Close</button>
			<?php if ( isset($xfa['submit']) ) : ?>
				<button type="submit" class="btn btn-primary scaffold-btn-save">Save changes</button>
			<?php endif; ?>
		</div>
	<?php elseif ( $scaffold['editMode'] == 'classic' ) : ?>
		<br />
		<div class="container-fluid">
			<div class="<?php if ( isset($scaffold['modalSize']) and $scaffold['modalSize'] == 'max' ) : ?>col-sm-10 col-sm-offset-2<?php else : ?>col-sm-9 col-sm-offset-3<?php endif; ?>">
				<?php if ( isset($xfa['submit']) ) : ?>
					<button type="submit" class="btn btn-primary btn-lg scaffold-btn-save">Save changes</button>
				<?php endif; ?>
				<a href="javascript:history.back();" class="btn btn-default btn-lg scaffold-btn-cancel">Cancel</a>
			</div>
		</div>
	<?php endif; ?>
</form>