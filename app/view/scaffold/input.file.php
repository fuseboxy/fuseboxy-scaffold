<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$xfa">
				<string name="ajaxUpload" />
			</structure>
			<string name="$fieldName" />
			<string name="$dataFieldName" />
			<string name="$fieldValue" />
			<structure name="$fieldConfig">
				<string name="format" comments="file|image" />
				<string name="placeholder" />
				<boolean name="required" />
				<boolean name="readonly" comments="output does not pass value; readonly does" />
				<boolean name="disabled" optional="yes" />
				<string name="style" comments="apply to preview image" />
				<string name="filesize" optional="yes" comments="max file size in bytes; add numeric value for client size checking" />
				<list name="filetype" optional="yes" delim="," comments="comma-delimited list of allowed file types (e.g. filetype=gif,jpg,png)" />
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/
// unique fieldID (for ajax-upload target)
$uniqid = F::command('controller').'-input-'.$fieldConfig['format'].'-'.str_replace('.', '-', $fieldName).'-'.Util::uuid();

// display field
?><div id="<?php echo $uniqid; ?>" class="scaffold-input-file">
	<div class="input-group input-group-sm"><?php
		// buttons
		if ( empty($fieldConfig['readonly']) and empty($fieldConfig['disabled']) ) :
			?><div class="input-group-prepend">
				<button id="choose-<?php echo $uniqid; ?>" type="button" class="input-group-text btn bg-light btn-choose disabled">Choose</button>
				<button id="remove-<?php echo $uniqid; ?>" type="button" class="input-group-text btn bg-light btn-remove <?php if ( empty($fieldValue) ) echo 'd-none'; ?>"><i class="fa fa-times small px-1"></i></button>
				<button id="undo-<?php echo $uniqid; ?>"   type="button" class="input-group-text btn bg-light btn-undo d-none" data-original-image="<?php echo $fieldValue; ?>"><i class="fa fa-undo small px-1"></i></button>
			</div><?php
		endif;
		// file path
		?><input
			type="text"
			class="form-control"
			name="<?php echo $dataFieldName; ?>"
			value="<?php echo $fieldValue; ?>"
			placeholder="<?php if ( !empty($fieldConfig['placeholder']) ) echo $fieldConfig['placeholder']; ?>"
			<?php if ( !empty($fieldConfig['required']) ) echo 'required'; ?>
			<?php if ( !empty($fieldConfig['disabled']) ) echo 'disabled'; ?>
			readonly
			data-toggle="ajax-upload"
			data-target="#<?php echo $uniqid; ?>"
			data-form-action="<?php echo F::url($xfa['ajaxUpload']); ?>"
			data-choose-button="#choose-<?php echo $uniqid; ?>"
			data-remove-button="#remove-<?php echo $uniqid; ?>"
			data-undo-button="#undo-<?php echo $uniqid; ?>"
			data-preview="#preview-<?php echo $uniqid; ?>"
		 />
	</div><!--/.input-group--><?php
	// preview image
	if ( $fieldConfig['format'] == 'image' ) :
		?><a
			href="<?php echo dirname($fieldValue).'/'.urlencode(basename($fieldValue)); ?>"
			title="<?php echo basename($fieldValue); ?>"
			class="<?php if ( empty($fieldValue) ) echo 'd-none'; ?>"
			style="<?php if (!empty($fieldConfig['style']) ) echo $fieldConfig['style']; ?>"
			target="_blank"
			data-fancybox
		><img
			id="preview-<?php echo $uniqid; ?>"
			src="<?php echo dirname($fieldValue).'/'.urlencode(basename($fieldValue)); ?>"
			alt="<?php echo basename($fieldValue); ?>"
			class="img-thumbnail mt-1"
		/></a><?php
	endif;
?></div><!--/.scaffold-input-file-->