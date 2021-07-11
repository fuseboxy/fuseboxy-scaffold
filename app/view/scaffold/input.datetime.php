<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$field">
				<string name="name" />
				<string name="value" />
				<string name="format" comments="date|time|datetime" />
				<string name="icon" optional="yes" />
				<boolean name="required" optional="yes" />
				<boolean name="readonly" optional="yes" />
				<string name="placeholder" optional="yes" />
				<string name="class" optional="yes" />
				<string name="style" optional="yes" />
			</structure>
		</in>
		<out>
			<structure name="$data" scope="form">
				<string name="~fieldName~" />
			</structure>
		</out>
	</io>
</fusedoc>
*/ ?>
<div class="input-group input-group-sm"><?php
	include F::appPath('view/scaffold/input.icon.php');
	?><input
		type="text"
		name="data[<?php echo $field['name']; ?>]"
		value="<?php echo htmlspecialchars($field['value']); ?>"
		class="form-control br-0 scaffold-input-<?php echo $field['format']; ?> <?php if ( !empty($field['class']) ) echo $field['class']; ?>"
		autocomplete="off"
		<?php if ( !empty($field['style']) ) : ?>style="<?php echo $field['style']; ?>"<?php endif; ?>
		<?php if ( !empty($field['placeholder']) ) : ?>placeholder="<?php echo $field['placeholder']; ?>"<?php endif; ?>
		<?php if ( !empty($field['readonly']) ) echo 'readonly'; ?>
		<?php if ( !empty($field['required']) ) echo 'required'; ?>
	 /><?php
	// calendar or clock
	?><div class="input-group-append">
		<span class="input-group-text bg-transparent px-2 bl-0">
			<i class="<?php echo ( $field['format'] == 'time') ? 'far fa-clock' : 'far fa-calendar-alt'; ?> op-30"></i>
		</span>
	</div>
</div>