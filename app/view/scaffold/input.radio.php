<?php /*
<fusedoc>
	<io>
		<in>
			<string name="$fieldName" />
			<string name="$dataFieldName" />
			<string name="$fieldValue" />
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
				<string name="~fieldName~" />
			</structure>
		</out>
	</io>
</fusedoc>
*/
$optIndex = 0;
foreach ( $fieldConfig['options'] as $optValue => $optText ) :
	if ( is_array($optText) ) :
		$optGroupLabel = $optValue;
		$optGroupItems = $optText;
		?><small><strong><?php echo $optGroupLabel; ?></strong></small><?php
		foreach ( $optGroupItems as $optValue => $optText ) :
			include F::appPath('view/scaffold/input.radio.item.php');
			$optIndex++;
		endforeach;
	else :
		include F::appPath('view/scaffold/input.radio.item.php');
		$optIndex++;
	endif;
endforeach;
if ( !empty($fieldConfig['readonly']) ) :
	?><input type="hidden" name="<?php echo $dataFieldName; ?>" value="<?php echo htmlspecialchars($fieldValue); ?>" /><?php
endif;