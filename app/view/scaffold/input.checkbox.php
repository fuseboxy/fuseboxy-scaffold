<?php /*
<fusedoc>
	<io>
		<in>
			<string name="$fieldName" />
			<string name="$dataFieldName" />
			<array name="$fieldValue">
				<string name="+" />
			</array>
			<structure name="$fieldConfig">
				<array name="options">
					<string name="~optionValue~" value="~optionText~" optional="yes" />
					<structure name="~optGroup~" optional="yes">
						<structure name="~optionValue~" value="~optionText~" />
					</structure>
				</array>
				<string name="icon" optional="yes" />
				<boolean name="required" optional="yes" />
				<boolean name="readonly" optional="yes" />
				<boolean name="disabled" optional="yes" />
				<string name="class" optional="yes" />
				<string name="style" optional="yes" />
			</structure>
		</in>
		<out>
			<structure name="$data" scope="form">
				<array name="~fieldName~">
					<string name="+" />
				</array>
			</structure>
		</out>
	</io>
</fusedoc>
*/
// empty hidden field
// ===> avoid nothing submitted when no checkbox selected
?><input type="hidden" name="<?php echo $dataFieldName; ?>[]" value="" /><?php
// display
$optIndex = 0;
foreach ( $fieldConfig['options'] as $optValue => $optText ) :
	if ( is_array($optText) ) :
		$optGroupLabel = $optValue;
		$optGroupItems = $optText;
		?><small><strong><?php echo $optGroupLabel; ?></strong></small><?php
		foreach ( $optGroupItems as $optValue => $optText ) :
			include F::appPath('view/scaffold/input.checkbox.item.php');
			$optIndex++;
		endforeach;
	else :
		include F::appPath('view/scaffold/input.checkbox.item.php');
		$optIndex++;
	endif;
endforeach;
if ( !empty($fieldConfig['readonly']) ) :
	foreach ( $fieldValue as $val ) :
		?><input type="hidden" name="<?php echo $dataFieldName; ?>[]" value="<?php echo htmlspecialchars($val); ?>" /><?php
	endforeach;
endif;