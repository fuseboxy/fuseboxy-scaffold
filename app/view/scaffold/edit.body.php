<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$fieldLayout">
				<list name="~columnList~" optional="yes" value="~columnWidthList~" delim="|" />
				<string name="~line~" optional="yes" example="---" />
				<string name="~heading~" optional="yes" example="## General" comments="number of pound-signs means H1,H2,H3..." />
			</structure>
			<structure name="$fieldConfigAll">
				<structure name="~column~">
					<string name="label" comments="display name at table/form header" />
					<string name="format" comments="normal|output|textarea|checkbox|radio" default="normal" />
					<array name="options" comments="show dropdown when specified">
						<string name="~key is option-value~" comments="value is option-text" />
					</array>
					<boolean name="readonly" comments="output does not pass value; readonly does" />
					<string name="placeholder" default="column display name" />
					<string name="help" />
					<boolean name="required" />
				</structure>
			</structure>
			<structure name="$options">
				<number name="labelColumn" />
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/
// form fields
foreach ( $fieldLayout as $fieldNameList => $fieldWidthList ) :
	// heading & line & output
	if ( Scaffold::parseFieldRow($fieldNameList, true) != 'fields' ) :
		echo Scaffold::parseFieldRow($fieldNameList);
	// field list
	else :
		$fieldNameList = explode('|', $fieldNameList);
		$fieldWidthList = explode('|', $fieldWidthList);
		?><div class="form-row"><?php
			// label column
			if ( !empty($options['labelColumn']) ) :
				?><label class="col-<?php echo $options['labelColumn']; ?> col-form-label col-form-label-sm text-right"><?php
					foreach ( $fieldNameList as $i => $fieldNameSubList ) :
						$fieldNameSubList = explode(',', $fieldNameSubList);
						foreach ( $fieldNameSubList as $fieldName ) :
							if ( !empty($fieldName) ) :
								$headerText = $fieldConfigAll[$fieldName]['label'];
								if ( $i == 0 ) :
									?><span><?php echo $headerText; ?></span><?php
								elseif ( !empty($headerText) ) :
									?><small class="text-muted"> / <?php echo $headerText; ?></small><?php
								endif;
							endif; // if-notEmpty
						endforeach; // foreach-fieldNameSubList
					endforeach; // foreach-fieldNameList
				?></label><?php
			endif;
			// field column
			?><div class="col">
				<div class="row"><?php
					foreach ( $fieldNameList as $i => $fieldNameSubList ) :
						$columnClass   = array('scaffold-col');
						$columnClass[] = !empty($fieldWidthList[$i]) ? "col-{$fieldWidthList[$i]}" : 'col';
						$columnClass[] = 'col-'.str_replace('.', '-', $fieldNameSubList));
						?><div class="<?php echo $columnClass; ?>"><?php
							$fieldNameSubList = explode(',', $fieldNameSubList);
							foreach ( $fieldNameSubList as $fieldName ) :
								if ( !empty($fieldName) ) echo Scaffold::renderInput($fieldName, $fieldConfigAll[$fieldName], $bean);
							endforeach; // foreach-fieldNameSubList
						?></div><?php
					endforeach; // foreach-fieldNameList
				?></div><!--/.row-->
			</div><!--/.col-->
		</div><!--/.form-group--><?php
	endif;
endforeach;