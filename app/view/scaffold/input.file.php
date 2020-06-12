<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$xfa">
				<string name="ajaxUpload" />
				<string name="ajaxUploadProgress" />
			</structure>
			<structure name="$field">
				<string name="name" />
				<string name="format" comments="file|image" />
				<string name="value" />
				<string name="placeholder" />
				<boolean name="required" />
				<boolean name="readonly" comments="output does not pass value; readonly does" />
				<string name="style" comments="apply to preview image" />
				<string name="filesize" optional="yes" comments="max file size in bytes" />
				<number name="filesize_numeric" optional="yes" comments="use this for comparison" />
				<list name="filetype" optional="yes" delim="," comments="comma-delimited list of allowed file types (e.g. filetype=gif,jpg,png)" />
				<boolean name="preview" optional="yes" />
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/
$uniqid = "{$scaffold['beanType']}-input-{$field['format']}-{$field['name']}-".uuid();
?><div 
	id="<?php echo $uniqid; ?>"
	class="scaffold-input-file" 
	<?php if ( isset($xfa['ajaxUpload'])         ) : ?>data-upload-url="<?php echo F::url("{$xfa['ajaxUpload']}&uploaderID={$uniqid}&fieldName={$field['name']}"); ?>"<?php endif; ?> 
	<?php if ( isset($xfa['ajaxUploadProgress']) ) : ?>data-progress-url="<?php echo F::url($xfa['ajaxUploadProgress']); ?>"<?php endif; ?> 
	<?php if ( isset($field['filetype'])         ) : ?>data-file-type="<?php echo $field['filetype']; ?>"<?php endif; ?> 
	<?php if ( isset($field['filesize'])         ) : ?>data-file-size="<?php echo $field['filesize']; ?>" data-file-size-numeric="<?php echo $field['filesize_numeric']; ?>"<?php endif; ?>
>
	<div class="input-group input-group-sm"><?php
		// buttons
		if ( empty($field['readonly']) ) :
			?><div class="input-group-prepend">
				<button type="button" class="text-white input-group-text btn-upload">Choose</button>
				<button type="button" class="text-white input-group-text btn-remove <?php if ( empty($field['value']) ) echo 'd-none'; ?>"><i class="fa fa-times small px-1"></i></button>
				<button type="button" class="text-white input-group-text btn-undo d-none" data-original-image="<?php echo $field['value']; ?>"><i class="fa fa-undo small px-1"></i></button>
			</div><?php
		endif;
		// file path
		?><input
			type="text"
			class="form-control"
			name="data[<?php echo $field['name']; ?>]"
			value="<?php echo $field['value']; ?>"
			placeholder="<?php if ( !empty($field['placeholder']) ) echo $field['placeholder']; ?>"
			readonly
			<?php if ( !empty($field['required']) ) echo 'required'; ?>
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
	// preview
	if ( !empty($field['preview']) ) :
		?><a
			href="<?php echo $field['value']; ?>"
			title="<?php echo basename($field['value']); ?>"
			class="<?php if ( empty($field['value']) ) echo 'd-none'; ?>"
			style="<?php if (!empty($field['style']) ) echo $field['style']; ?>"
			target="_blank"
			data-fancybox
		><img
			alt="<?php echo basename($field['value']); ?>"
			src="<?php echo $field['value']; ?>"
			class="img-thumbnail mt-1"
		/></a><?php
	endif;
?></div><!--/.scaffold-input-file-->