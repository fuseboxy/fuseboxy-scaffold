<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$fieldConfig">
				<string name="icon" optional="yes" />
				<string name="value" />
				<string name="class" optional="yes" />
				<string name="style" optional="yes" />
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/ ?>
<div 
	class="form-control-plaintext form-control-sm <?php if ( isset($fieldConfig['class']) ) echo $fieldConfig['class']; ?>"
	<?php if ( isset($fieldConfig['style']) ) : ?>style="<?php echo $fieldConfig['style']; ?>"<?php endif; ?>
><?php
	// icon
	if ( !empty($fieldConfig['icon']) ) :
		?><i class="fa-fw <?php echo $fieldConfig['icon']; ?>"></i><?php
	endif;
	// content
	if ( !empty($fieldConfig['value']) ) echo $fieldConfig['value'];
?></div>