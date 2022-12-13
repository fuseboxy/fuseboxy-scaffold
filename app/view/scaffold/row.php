<?php /*
<fusedoc>
	<io>
		<in>
			<string name="controller" scope="$fusebox" />
			<structure name="$scaffold">
				<string name="editMode" />
				<array name="listField">
					<string name="~fieldNameList~" value="~columnWidth~" />
				</array>
				<array name="fieldConfig">
					<structure name="~column~">
						<string name="format" />
						<string name="class" optional="yes" />
						<string name="style" optional="yes" />
					</structure>
				</array>
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/
// flatten options with optgroup into single-dimensional array
if ( !function_exists('scaffold_options_flatten') ) :
	function scaffold_options_flatten($options) {
		if ( !is_array($options) ) return false; 
		$result = array(); 
		foreach ( $options as $key => $val ) { 
			if ( is_array($val) ) { 
				$result += scaffold_options_flatten($val);
			} else { 
				$result += array($key => $val);
			} 
		} 
		return $result; 
	}
endif;
?><div id="<?php echo F::command('controller'); ?>-row-<?php echo $bean->id; ?>" class="<?php echo F::command('controller'); ?>-row scaffold-row small">
	<table class="table table-hover table-sm mb-0">
		<tbody>
			<tr <?php if ( !empty($bean->disabled) ) : ?>class="table-active op-50"<?php endif; ?>><?php
				// go through each item in scaffold-listField config
				foreach ( $scaffold['listField'] as $fieldNameList => $columnWidth ) :
					$fieldNameList = explode('|', $fieldNameList);
					// display : field group
					?><td class="<?php echo 'col-'.implode('-', str_replace('.', '-', $fieldNameList)); ?>" width="<?php echo $columnWidth; ?>"><?php
						// go through each field
						foreach ( $fieldNameList as $fieldIndex => $fieldName ) :
							if ( !empty($fieldName) ) :
								$fieldConfig = $scaffold['fieldConfig'][$fieldName];
								$fieldValue = $fieldConfig['value'] ?? Scaffold::nestedArrayGet($fieldName, $bean);
								// determine field format
								$isManyToMany = ( isset($fieldConfig['format']) and $fieldConfig['format'] == 'many-to-many' );
								$isOneToMany  = ( isset($fieldConfig['format']) and $fieldConfig['format'] == 'one-to-many' );
								$isCheckbox   = ( isset($fieldConfig['format']) and $fieldConfig['format'] == 'checkbox' );
								$isTextArea   = ( isset($fieldConfig['format']) and $fieldConfig['format'] == 'textarea' );
								$isWYSIWYG    = ( isset($fieldConfig['format']) and $fieldConfig['format'] == 'wysiwyg' );
								$isOutput     = ( isset($fieldConfig['format']) and $fieldConfig['format'] == 'output' );
								$isHidden     = ( isset($fieldConfig['format']) and $fieldConfig['format'] == 'hidden' );
								$isImage      = ( isset($fieldConfig['format']) and $fieldConfig['format'] == 'image' );
								$isFile       = ( isset($fieldConfig['format']) and $fieldConfig['format'] == 'file' );
								$isURL        = ( isset($fieldConfig['format']) and $fieldConfig['format'] == 'url' );
								// display : each field
								$fieldClass = array('col-'.str_replace('.', '-', $fieldName));
								if ( $fieldIndex > 0 ) $fieldClass[] = 'small text-muted';
								?><div class="<?php echo implode(' ', $fieldClass); ?>"><?php
									// image : show thumbnail
									if ( $isImage and !empty($fieldValue) ) :
										?><a
											href="<?php echo dirname($fieldValue).'/'.urlencode(basename($fieldValue)); ?>"
											title="<?php echo basename($fieldValue); ?>"
											target="_blank"
											data-fancybox
										><img
											src="<?php echo dirname($fieldValue).'/'.urlencode(basename($fieldValue)); ?>"
											alt="<?php echo basename($fieldValue); ?>"
											class="img-thumbnail mb-0 mt-1 <?php if ( !empty($bean->disabled) ) echo 'op-50'; ?>"
											style="max-width: 100%; <?php if ( !empty($fieldConfig['style']) ) echo $fieldConfig['style']; ?>"
										/></a><?php
									// file : show link
									elseif ( $isFile and !empty($fieldValue) ) :
										?><a
											href="<?php echo dirname($fieldValue).'/'.urlencode(basename($fieldValue)); ?>"
											style="word-break: break-all;"
											target="_blank"
										><?php echo basename($fieldValue); ?></a><?php
									// checkbox : turn list into items
									elseif ( $isCheckbox and !empty($fieldValue) ) :
										$arr = explode('|', $fieldValue);
										foreach ( $arr as $val ) :
											if ( !empty($val) ) :
												$options = isset($fieldConfig['options']) ? scaffold_options_flatten($fieldConfig['options']) : array();
												$output = !empty($options[$val]) ? $options[$val] : $val;
												?><div><?php echo $output; ?></div><?php
											endif;
										endforeach;
									// one-to-many & many-to-many : show multiple values (according to options)
									elseif ( $isOneToMany or $isManyToMany ) :
										$objectName = ( substr($fieldName, -3) == '_id' ) ? substr($fieldName, 0, strlen($fieldName)-3) : $fieldName;
										$associateField = ( $isOneToMany ? 'own' : 'shared' ).ucfirst($objectName);
										foreach ( $bean->$associateField as $associateBean ) :
											$val = $associateBean->id;
											if ( !empty($val) ) :
												$options = isset($fieldConfig['options']) ? scaffold_options_flatten($fieldConfig['options']) : array();
												$output = !empty($options[$val]) ? $options[$val] : "[{$col}={$val}]";
												?><div><?php echo $output; ?></div><?php
											endif;
										endforeach;
									// dropdown : show single value (according to options)
									elseif ( isset($fieldConfig['options']) ) :
										$isObjectID = ( substr($fieldName, -3) == '_id' );
										$val = $fieldValue;
										if ( !empty($val) ) :
											$options = isset($fieldConfig['options']) ? scaffold_options_flatten($fieldConfig['options']) : array();
											echo !empty($options[$val]) ? $options[$val] : ( $isObjectID ? "[{$fieldName}={$val}]" : $val );
										endif;
									// url : show link
									elseif ( $isURL ) :
										?><a
											href="<?php echo $fieldValue; ?>"
											style="word-break: break-all;"
											target="_blank"
										><?php echo $fieldValue; ?></a><?php
									// default : show field value
									elseif ( $isTextArea ) :
										echo nl2br($fieldValue);
									// wysiwyg : show html
									// default : show field value
									// output  : show custom content
									elseif ( !$isHidden ) :
										echo $fieldValue;
									endif;
								?></div><?php
							endif; // if-fieldName
						endforeach; // foreach-fieldNameList
					?></td><?php
				endforeach; // foreach-scaffold-listField
				// display : button
				?><td class="col-button text-nowrap text-right"><?php include F::appPath('view/scaffold/row.button.php'); ?></td><?php
			?></tr>
		</tbody>
	</table>
</div><!--/.scaffold-row-->