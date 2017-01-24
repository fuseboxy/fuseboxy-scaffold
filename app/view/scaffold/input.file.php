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
				<string name="format" comments="file" />
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
$uniqid = "{$scaffold['beanType']}-input-file-{$field['name']}-".uuid();
?>
<div
	id="<?php echo $uniqid; ?>"
	class="scaffold-input-file"
	<?php if ( isset($xfa['ajaxUpload']) ) : ?>data-upload-url="<?php echo F::url("{$xfa['ajaxUpload']}&uploaderID={$uniqid}&fieldName={$field['name']}"); ?>"<?php endif; ?>
	<?php if ( isset($xfa['ajaxUploadProgress']) ) : ?>data-progress-url="<?php echo F::url($xfa['ajaxUploadProgress']); ?>"<?php endif; ?>
	<?php if ( isset($field['filetype']) ) : ?>data-file-type="<?php echo $field['filetype']; ?>"<?php endif; ?>
	<?php if ( isset($field['filesize']) ) : ?>data-file-size="<?php echo $field['filesize']; ?>" data-file-size-numeric="<?php echo $field['filesize_numeric']; ?>"<?php endif; ?>
>
	<div class="input-group">
		<?php if ( empty($field['readonly']) ) : ?>
			<span class="input-group-btn">
				<button
					type="button"
					class="btn btn-sm btn-default btn-upload"
				>Choose</button>
				<button
					type="button"
					class="btn btn-sm btn-default btn-remove"
					style="border-right: 0; <?php if ( empty($field['value']) ) : ?>display: none;<?php endif; ?>"
				><i class="fa fa-times"></i></button>
			</span>
		<?php endif; ?>
		<input
			type="text"
			class="form-control input-sm"
			name="data[<?php echo $field['name']; ?>]"
			value="<?php echo $field['value']; ?>"
			placeholder="<?php if ( !empty($field['placeholder']) ) echo $field['placeholder']; ?>"
			readonly
			<?php if ( !empty($field['required']) ) echo 'required'; ?>
		 />
	</div>
	<div class="alert alert-danger" style="display: none; margin-bottom: 0; margin-top: 5px;"></div>
	<div class="row" style="display: none; margin-top: 5px;">
		<div class="col-xs-9 progress-wrap" style="padding-right: 1px;"></div>
		<div class="col-xs-3 progress-abort" style="padding-left: 1px;"></div>
	</div>
	<?php if ( !empty($field['preview']) ) : ?>
		<a
			href="<?php echo $field['value']; ?>"
			class="thumbnail"
			target="_blank"
			style="margin: 5px 0 0 0; width: auto; <?php if ( empty($field['value']) ) echo 'display: none;'; ?> <?php if ( !empty($field['style']) ) echo $field['style']; ?>"
			title="<?php echo basename($field['value']); ?>"
		><img
			alt="<?php echo basename($field['value']); ?>"
			src="<?php echo $field['value']; ?>"
		/></a>
	<?php endif; ?>
</div>