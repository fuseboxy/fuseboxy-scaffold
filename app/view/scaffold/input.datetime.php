<?php /*
<fusedoc>
	<io>
		<in>
			<string name="$fieldName" />
			<string name="$fieldValue" />
			<structure name="$fieldConfig">
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
		name="data[<?php echo $fieldName; ?>]"
		value="<?php echo htmlspecialchars($fieldValue); ?>"
		class="form-control br-0 scaffold-input-<?php echo $fieldConfig['format']; ?> <?php if ( !empty($fieldConfig['class']) ) echo $fieldConfig['class']; ?>"
		autocomplete="off"
		<?php if ( !empty($fieldConfig['style']) ) : ?>style="<?php echo $fieldConfig['style']; ?>"<?php endif; ?>
		<?php if ( !empty($fieldConfig['placeholder']) ) : ?>placeholder="<?php echo $fieldConfig['placeholder']; ?>"<?php endif; ?>
		<?php if ( !empty($fieldConfig['readonly']) ) echo 'readonly'; ?>
		<?php if ( !empty($fieldConfig['required']) ) echo 'required'; ?>
	 /><?php
	// calendar or clock
	?><div class="input-group-append">
		<span class="input-group-text bg-white px-2 bl-0">
			<i class="<?php echo ( $fieldConfig['format'] == 'time') ? 'far fa-clock' : 'far fa-calendar-alt'; ?> op-30"></i>
		</span>
	</div>
</div>