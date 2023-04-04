<?php /*
<fusedoc>
	<io>
		<in>
			<string name="$formID" />
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
				<button type="button" class="input-group-text text-white btn-upload">Choose</button>
				<button type="button" class="input-group-text bg-white btn-remove <?php if ( empty($fieldValue) ) echo 'd-none'; ?>"><i class="fa fa-times small px-1"></i></button>
				<button type="button" class="input-group-text bg-white btn-undo d-none" data-original-image="<?php echo $fieldValue; ?>"><i class="fa fa-undo small px-1"></i></button>
			</div><?php
		endif;
		// file path
		?><input
			type="text"
			class="form-control"
			name="<?php echo $dataFieldName; ?>"
			value="<?php echo $fieldValue; ?>"
			placeholder="<?php if ( !empty($fieldConfig['placeholder']) ) echo $fieldConfig['placeholder']; ?>"
			readonly
			<?php if ( !empty($fieldConfig['required']) ) echo 'required'; ?>
			<?php if ( !empty($fieldConfig['disabled']) ) echo 'disabled'; ?>
		 />
	</div><!--/.input-group--><?php
	// (client-side) error message
	?><div class="form-text text-danger small px-1 mt-1 d-none"></div><?php
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
			src="<?php echo dirname($fieldValue).'/'.urlencode(basename($fieldValue)); ?>"
			alt="<?php echo basename($fieldValue); ?>"
			class="img-thumbnail mt-1"
		/></a><?php
	endif;
?></div><!--/.scaffold-input-file-->