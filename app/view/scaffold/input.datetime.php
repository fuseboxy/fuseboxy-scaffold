<div class="input-group">
	<div class="input-group-prepend">
		<span class="input-group-text"><?php
			if     ( !empty($field['icon'])     ) : $fieldIcon = $field['icon'];
			elseif ( $field['format'] == 'time' ) : $fieldIcon = 'far fa-clock';
			else                                  : $fieldIcon = 'fa fa-calendar-alt';
			endif;
			?><i class="fa-fw <?php echo $fieldIcon; ?>"></i>
		</span>
	</div>
	<input
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