<?php /*
<fusedoc>
	<io>
		<in>
			<string name="controller" scope="$fusebox" />
			<structure name="$xfa">
				<string name="submit" optional="yes" />
				<string name="cancel" optional="yes" />
			</structure>
			<structure name="$options">
				<string name="formType" comments="modal|inline-modal|basic" />
				<number name="labelColumn" />
			</structure>
			<string name="$formBody" />
		</in>
		<out>
			<string name="$recordID" comments="pass to {edit.header} and {edit.footer}" />
		</out>
	</io>
</fusedoc>
*/
$recordID = empty($bean->id) ? Util::uuid() : $bean->id;
$formID = F::command('controller').'-edit-'.$recordID;
// wrapper
?><div id="<?php echo $formID; ?>"><?php
	// main form
	?><form
		class="scaffold-edit <?php if ( $options['formType'] == 'inline-modal' ) echo 'card bg-light my-3'; ?>"
		<?php if ( isset($xfa['submit']) ) : ?>
			method="post"
			action="<?php echo F::url($xfa['submit']); ?>"
		<?php endif; ?>
		<?php if ( $options['formType'] == 'modal' and !empty($bean->id) ) : ?>
			data-toggle="ajax-submit"
			data-target="#<?php echo F::command('controller'); ?>-row-<?php echo $recordID; ?>"
			data-callback="$('#<?php echo $formID; ?>').closest('.modal').modal('hide');"
		<?php elseif ( $options['formType'] == 'modal' ) : ?>
			data-toggle="ajax-submit"
			data-mode="after"
			data-target="#<?php echo F::command('controller'); ?>-header"
			data-callback="$('#<?php echo $formID; ?>').closest('.modal').modal('hide');"
		<?php elseif ( $options['formType'] == 'inline-modal' ) : ?>
			data-toggle="ajax-submit"
			data-target="#<?php echo F::command('controller'); ?>-edit-<?php echo $recordID; ?>"
		<?php endif; ?>
	><?php
		// header : title
		if ( in_array($options['formType'], ['modal','inline-modal']) ) :
			?><header class="modal-header"><?php
				include F::appPath('view/scaffold/edit.header.php');
			?></header><?php
		endif;
		// body : fields
		?><div class="modal-body"><?php
			if ( isset($arguments['flash']) ) F::alert($arguments['flash']);
			echo $formBody;
		?></div><?php
		// footer : buttons
		$footerClass = in_array($options['formType'], ['modal','inline-modal']) ? 'modal-footer' : 'col-10 offset-2';
		?><footer class="<?php echo $footerClass; ?>"><?php
			include F::appPath('view/scaffold/edit.footer.php');
		?></footer>
	</form><?php
	// hidden ajax-upload form
	include F::appPath('view/scaffold/ajax_upload.php');
?></div>