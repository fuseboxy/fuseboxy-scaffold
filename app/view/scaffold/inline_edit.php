<?php /*
<fusedoc>
	<io>
		<in>
			<string name="controller" scope="$fusebox" />
			<array name="$fieldLayout">
				<string name="~fieldNameList~" value="~columnWidth~" />
			</array>
			<structure name="$fieldConfigAll">
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
		</in>
		<out />
	</io>
</fusedoc>
*/
$recordID = empty($bean->id) ? Util::uuid() : $bean->id;
?><form
	id="<?php echo F::command('controller'); ?>-inline-edit-<?php echo $recordID; ?>"
	class="<?php echo F::command('controller'); ?>-inline-edit scaffold-inline-edit form-horizontal"
	<?php if ( isset($xfa['submit']) ) : ?>
		method="post"
		action="<?php echo F::url($xfa['submit']); ?>"
		data-toggle="ajax-submit"
		data-target="#<?php echo F::command('controller'); ?>-inline-edit-<?php echo $recordID; ?>"
	<?php endif; ?>
>
	<table class="table table-hover table-sm mb-0">
		<tr><?php
			foreach ( $fieldLayout as $fieldNameList => $columnWidth ) :
				$fieldNameList = explode('|', $fieldNameList);
				?><td class="col-<?php echo implode('-', str_replace('.', '-', $fieldNameList)); ?>" width="<?php echo $columnWidth; ?>;"><?php
					foreach ( $fieldNameList as $i => $fieldName ) :
						?><div class="scaffold-col col-<?php echo str_replace('.', '-', $fieldName); ?>"><?php
							if ( isset($fieldConfigAll[$fieldName]) ) :
								$fieldConfig = $fieldConfigAll[$fieldName];
								include F::appPath('view/scaffold/input.php');
							else :
								?><div class="form-control" readonly>
									<em class="small text-muted text-nowrap">Field [<?php echo $fieldName; ?>] is undefined</em>
								</div><?php
							endif;
						?></div><?php
					endforeach;
				?></td><?php
			endforeach;
			?><td class="col-button text-nowrap">
				<div class="text-right"><?php include F::appPath('view/scaffold/inline_edit.button.php'); ?></div>
			</td>
		</tr>
	</table>
</form>