<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$scaffold">
				<string name="beanType" />
				<string name="editMode" />
				<array name="listField" comments="key is pipe-delimited column list; value is column width">
					<string name="~columnList~" comments="column width" />
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
*/ ?>
<div id="<?php echo $scaffold['beanType']; ?>-row-<?php echo $bean->id; ?>" class="<?php echo $scaffold['beanType']; ?>-row scaffold-row small">
	<table class="table table-hover table-sm mb-0">
		<tbody>
			<tr <?php if ( !empty($bean->disabled) ) : ?>class="table-active op-50"<?php endif; ?>><?php
				// go through each item in scaffold-listField config
				foreach ( $scaffold['listField'] as $key => $val ) :
					$cols = array_map('trim', explode('|', is_numeric($key) ? $val : $key));
					$colWidth = is_numeric($key) ? '' : $val;
					// display : field group
					$fieldGroupClass = 'col-'.implode('-', $cols);
					?><td class="<?php echo $fieldGroupClass; ?>" width="<?php echo $colWidth; ?>"><?php
						// go through each field
						foreach ( $cols as $colIndex => $col ) :
							$field = $scaffold['fieldConfig'][$col];
							// determine field format
							$isManyToMany = ( isset($field['format']) and $field['format'] == 'many-to-many' );
							$isOneToMany  = ( isset($field['format']) and $field['format'] == 'one-to-many' );
							$isCheckbox   = ( isset($field['format']) and $field['format'] == 'checkbox' );
							$isWYSIWYG    = ( isset($field['format']) and $field['format'] == 'wysiwyg' );
							$isOutput     = ( isset($field['format']) and $field['format'] == 'output' );
							$isHidden     = ( isset($field['format']) and $field['format'] == 'hidden' );
							$isImage      = ( isset($field['format']) and $field['format'] == 'image' );
							$isFile       = ( isset($field['format']) and $field['format'] == 'file' );
							$isURL        = ( isset($field['format']) and $field['format'] == 'url' );
							// display : each field
							$fieldClass = array('col-'.$col);
							if ( $colIndex > 0 ) $fieldClass[] = 'small text-muted';
							?><div class="<?php echo implode(' ', $fieldClass); ?>"><?php
								// output : show custom content
								if ( $isOutput ) :
									if ( !empty($field['value']) ) echo $field['value'];
								// image : show thumbnail
								elseif ( $isImage and !empty($bean[$col]) ) :
									?><a
										title="<?php echo basename($bean[$col]); ?>"
										href="<?php echo $bean[$col]; ?>"
										target="_blank"
										data-fancybox
									><img
										alt="<?php echo basename($bean[$col]); ?>"
										src="<?php echo $bean[$col]; ?>"
										class="img-thumbnail mb-0 mt-1 <?php if ( !empty($bean->disabled) ) echo 'op-50'; ?>"
										style="max-width: 100%; <?php if ( !empty($field['style']) ) echo $field['style']; ?>"
									/></a><?php
								// file : show link
								elseif ( $isFile and !empty($bean[$col]) ) :
									?><a
										href="<?php echo $bean[$col]; ?>"
										style="word-break: break-all;"
										target="_blank"
									><?php echo basename($bean[$col]); ?></a><?php
								// checkbox : turn list into items
								elseif ( $isCheckbox and !empty($bean[$col]) ) :
									$arr = explode('|', $bean[$col]);
									foreach ( $arr as $val ) :
										if ( !empty($val) ) :
											$output = !empty($field['options'][$val]) ? $field['options'][$val] : $val;
											?><div><?php echo $output; ?></div><?php
										endif;
									endforeach;
								// one-to-many & many-to-many : show multiple values (according to options)
								elseif ( $isOneToMany or $isManyToMany ) :
									$objectName = ( substr($col, -3) == '_id' ) ? substr($col, 0, strlen($col)-3) : $col;
									$arr = $bean[ ( $isOneToMany ? 'own' : 'shared' ).ucfirst($objectName) ];
									foreach ( $arr as $associateBean ) :
										$val = $associateBean->id;
										if ( !empty($val) ) :
											$output = !empty($field['options'][$val]) ? $field['options'][$val] : "[{$col}={$val}]";
											?><div><?php echo $output; ?></div><?php
										endif;
									endforeach;
								// dropdown : show single value (according to options)
								elseif ( isset($field['options']) ) :
									$isObjectID = ( substr($col, -3) == '_id' );
									$val = $bean[$col];
									if ( !empty($val) ) :
										echo !empty($field['options'][$val]) ? $field['options'][$val] : ( $isObjectID ? "[{$col}={$val}]" : $val );
									endif;
								// url : show link
								elseif ( $isURL ) :
									?><a
										href="<?php echo $bean[$col]; ?>"
										style="word-break: break-all;"
										target="_blank"
									><?php echo $bean[$col]; ?></a><?php
								// wysiwyg : show html
								elseif ( $isWYSIWYG ) :
									echo $bean[$col];
								// default : show field value
								elseif ( !$isHidden ) :
									echo nl2br($bean[$col]);
								endif;
							?></div><?php
						endforeach; // foreach-cols
					?></td><?php
				endforeach; // foreach-scaffold-listField
				// display : button
				?><td class="col-button text-nowrap text-right"><?php include F::appPath('view/scaffold/row.button.php'); ?></td><?php
			?></tr>
		</tbody>
	</table>
</div><!--/.scaffold-row-->