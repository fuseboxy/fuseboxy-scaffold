<div class="input-group"><?php
	if ( !empty($field['icon']) ) :
		?><div class="input-group-prepend">
			<span class="input-group-text">
				<i class="fa-fw <?php echo $field['icon']; ?>"></i>
			</span>
		</div><?php
	endif;
	?><input
		type="<?php echo empty($field['format']) ? 'text' : $field['format']; ?>"
		class="form-control form-control-sm scaffold-input-<?php echo empty($field['format']) ? 'text' : $field['format']; ?>"
		name="data[<?php echo $field['name']; ?>]"
		value="<?php echo htmlspecialchars($field['value']); ?>"
		<?php if ( isset($field['style']) ) : ?>style="<?php echo $field['style']; ?>"<?php endif; ?>
		<?php if ( isset($field['placeholder']) ) : ?>placeholder="<?php echo $field['placeholder']; ?>"<?php endif; ?>
		<?php if ( !empty($field['readonly']) ) echo 'readonly'; ?>
		<?php if ( !empty($field['required']) ) echo 'required'; ?>
	 />
 </div>