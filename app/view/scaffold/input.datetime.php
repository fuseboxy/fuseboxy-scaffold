<div class="input-group"><?php
	if ( !isset($field['icon']) or $field['icon'] !== false ) :
		$fieldIcon = isset($field['icon']) ? $field['icon'] : ( ($field['format'] == 'time') ? 'far fa-clock' : 'fa fa-calendar-alt' );
		?><div class="input-group-prepend">
			<span class="input-group-text">
				<i class="fa-fw <?php echo $fieldIcon; ?>"></i>
			</span>
		</div><?php
	endif;
	?><input
		type="text"
		class="form-control form-control-sm scaffold-input-<?php echo $field['format']; ?>"
		name="data[<?php echo $field['name']; ?>]"
		value="<?php echo htmlspecialchars($field['value']); ?>"
		autocomplete="off"
		<?php if ( isset($field['style']) ) : ?>style="<?php echo $field['style']; ?>"<?php endif; ?>
		<?php if ( isset($field['placeholder']) ) : ?>placeholder="<?php echo $field['placeholder']; ?>"<?php endif; ?>
		<?php if ( !empty($field['readonly']) ) echo 'readonly'; ?>
		<?php if ( !empty($field['required']) ) echo 'required'; ?>
	 />
</div>