<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$scaffold">
				<string name="beanType" />
				<string name="editMode" comments="inline|modal" />
				<array name="listField" comments="key is pipe-delimited column list; value is column width">
					<string name="~column-list~" comments="column width" />
				</array>
				<structure name="fieldConfig">
					<structure name="~column~">
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
<div
	id="<?php echo $scaffold['beanType']; ?>-inline-edit-<?php echo $recordID; ?>"
	class="<?php echo $scaffold['beanType']; ?>-inline-edit scaffold-inline-edit"
>
	<form
		class="form-horizontal"
		<?php if ( isset($xfa['submit']) ) : ?>
			method="post"
			action="<?php echo F::url($xfa['submit']); ?>"
			data-toggle="ajax-submit"
			data-target="#<?php echo $scaffold['beanType']; ?>-inline-edit-<?php echo $recordID; ?>"
		<?php endif; ?>
	>
		<table class="table table-hover table-sm mb-0">
			<tr>
				<?php foreach ( $scaffold['listField'] as $key => $val ) : ?>
					<?php $cols = explode('|', is_numeric($key) ? $val : $key); ?>
					<?php $colWidth = is_numeric($key) ? '' : $val; ?>
					<td class="col-<?php echo implode('-', $cols); ?>" width="<?php echo $colWidth; ?>;">
						<?php foreach ( $cols as $i => $col ) : ?>
							<div class="form-group mb-1">
								<div class="w-100"><?php
									if ( isset($scaffold['fieldConfig'][$col]) ) :
										$field = $scaffold['fieldConfig'][$col];
										$field['name'] = $col;
										include F::appPath('view/scaffold/input.php');
									else :
										?><div class="form-control" readonly>
											<em class="small text-muted text-nowrap">Field [<?php echo $col; ?>] is undefined</em>
										</div><?php
									endif;
								?></div>
							</div>
						<?php endforeach; ?>
					</td>
				<?php endforeach; ?>
				<td class="col-button text-nowrap">
					<div class="text-right"><?php include F::appPath('view/scaffold/inline_edit.button.php'); ?></div>
				</td>
			</tr>
		</table>
	</form>
</div>