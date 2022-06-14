<?php /*
<fusedoc>
	<io>
		<in>
			<string name="controller" scope="$fusebox" />
			<structure name="$xfa">
				<string name="submit" optional="yes" />
				<string name="cancel" optional="yes" />
			</structure>
			<structure name="$fieldLayout">
				<list name="~columnList~" optional="yes" value="~columnWidthList~" delim="|" />
				<string name="~line~" optional="yes" example="---" />
				<string name="~heading~" optional="yes" example="## General" comments="number of pound-signs means H1,H2,H3..." />
			</structure>
			<structure name="$fieldConfigAll">
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
			<structure name="$options">
				<string name="editMode" comments="modal|inline-modal|basic" />
				<number name="labelColumn" />
			</structure>
		</in>
		<out>
			<string name="$recordID" comments="pass to {edit.header} and {edit.footer}" />
		</out>
	</io>
</fusedoc>
*/
$recordID = empty($bean->id) ? Util::uuid() : $bean->id;
$formID = F::command('controller').'-edit-'.$recordID;
// display
?><form
	id="<?php echo $formID; ?>"
	class="scaffold-edit <?php if ( $options['editMode'] == 'inline-modal' ) echo 'card bg-light my-3'; ?>"
	<?php if ( isset($xfa['submit']) ) : ?>
		method="post"
		action="<?php echo F::url($xfa['submit']); ?>"
	<?php endif; ?>
	<?php if ( $options['editMode'] == 'modal' and !empty($bean->id) ) : ?>
		data-toggle="ajax-submit"
		data-target="#<?php echo F::command('controller'); ?>-row-<?php echo $recordID; ?>"
		data-callback="$('#<?php echo $formID; ?>').closest('.modal').modal('hide');"
	<?php elseif ( $options['editMode'] == 'modal' ) : ?>
		data-toggle="ajax-submit"
		data-mode="after"
		data-target="#<?php echo F::command('controller'); ?>-header"
		data-callback="$('#<?php echo $formID; ?>').closest('.modal').modal('hide');"
	<?php elseif ( $options['editMode'] == 'inline-modal' ) : ?>
		data-toggle="ajax-submit"
		data-target="#<?php echo F::command('controller'); ?>-edit-<?php echo $recordID; ?>"
	<?php endif; ?>
><?php
	// header : title
	if ( in_array($options['editMode'], ['modal','inline-modal']) ) :
		?><header class="modal-header"><?php
			include F::appPath('view/scaffold/edit.header.php');
		?></header><?php
	endif;
	// body : fields
	?><div class="modal-body"><?php
		if ( isset($arguments['flash']) ) F::alert($arguments['flash']);
		include F::appPath('view/scaffold/edit.body.php');
	?></div><?php
	// footer : buttons
	$footerClass = in_array($options['editMode'], ['modal','inline-modal']) ? 'modal-footer' : 'col-10 offset-2';
	?><footer class="<?php echo $footerClass; ?>"><?php
		include F::appPath('view/scaffold/edit.footer.php');
	?></footer>
</form>