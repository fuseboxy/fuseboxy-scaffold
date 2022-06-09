<?php /*
<fusedoc>
	<io>
		<in>
			<string name="controller" scope="$fusebox" />
			<structure name="$xfa">
				<string name="ajaxUpload" />
				<string name="ajaxUploadProgress" />
			</structure>
			<string name="$fieldName" />
			<string name="$dataFieldName" />
			<string name="$fieldValue" />
			<structure name="$fieldConfig">
				<string name="format" comments="file|image" />
				<string name="placeholder" />
				<boolean name="required" />
				<boolean name="readonly" comments="output does not pass value; readonly does" />
				<string name="style" comments="apply to preview image" />
				<string name="filesize" optional="yes" comments="max file size in bytes; add numeric value for client size checking" />
				<list name="filetype" optional="yes" delim="," comments="comma-delimited list of allowed file types (e.g. filetype=gif,jpg,png)" />
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/
$uniqid = F::command('controller').'-input-'.$fieldConfig['format'].'-'.str_replace('.', '-', $fieldName).'-'.Util::uuid();
?><div 
	id="<?php echo $uniqid; ?>"
	class="scaffold-input-file" 
	<?php if ( isset($xfa['ajaxUpload']) )         : ?>data-upload-url="<?php echo F::url("{$xfa['ajaxUpload']}&uploaderID={$uniqid}&fieldName={$fieldName}"); ?>"<?php endif; ?> 
	<?php if ( isset($xfa['ajaxUploadProgress']) ) : ?>data-progress-url="<?php echo F::url($xfa['ajaxUploadProgress']); ?>"<?php endif; ?> 
	<?php if ( isset($fieldConfig['filetype']) )   : ?>data-file-type="<?php echo $fieldConfig['filetype']; ?>"<?php endif; ?> 
	<?php if ( isset($fieldConfig['filesize']) )   : ?>data-file-size="<?php echo $fieldConfig['filesize']; ?>" data-file-size-numeric="<?php echo Scaffold::fileSizeNumeric($fieldConfig['filesize']); ?>"<?php endif; ?>
>
	<div class="input-group input-group-sm"><?php
		// buttons
		if ( empty($fieldConfig['readonly']) ) :
			?><div class="input-group-prepend">
				<button type="button" class="text-white input-group-text btn-upload">Choose</button>
				<button type="button" class="text-white input-group-text btn-remove <?php if ( empty($fieldValue) ) echo 'd-none'; ?>"><i class="fa fa-times small px-1"></i></button>
				<button type="button" class="text-white input-group-text btn-undo d-none" data-original-image="<?php echo $fieldValue; ?>"><i class="fa fa-undo small px-1"></i></button>
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
		 />
	</div><!--/.input-group--><?php
	// (client-side) error message
	?><div class="form-text text-danger small px-1 mt-1 d-none"></div><?php
	// progress bar
	?><div class="progress-row input-group input-group-sm mt-1 d-none" style="height: 31px;">
		<div class="form-control overflow-hidden p-0" style="height: 100%">
			<div class="progress rounded-0" style="height: 100%">
				<div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
			</div>
		</div>
		<div class="progress-abort input-group-append" style="height: 100%;">
			<button type="button" class="input-group-text btn-abort">Cancel</button>
		</div>
	</div><!--/.progress-row--><?php
	// preview image
	if ( $fieldConfig['format'] == 'image' ) :
		?><a
			href="<?php echo $fieldValue; ?>"
			title="<?php echo basename($fieldValue); ?>"
			class="<?php if ( empty($fieldValue) ) echo 'd-none'; ?>"
			style="<?php if (!empty($fieldConfig['style']) ) echo $fieldConfig['style']; ?>"
			target="_blank"
			data-fancybox
		><img
			alt="<?php echo basename($fieldValue); ?>"
			src="<?php echo $fieldValue; ?>"
			class="img-thumbnail mt-1"
		/></a><?php
	endif;
?></div><!--/.scaffold-input-file-->