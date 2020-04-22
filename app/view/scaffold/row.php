<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$scaffold">
				<string name="beanType" />
				<string name="editMode" comments="inline|modal|classic" />
				<array name="listField" comments="key is pipe-delimited column list; value is column width">
					<string name="~column-list~" comments="column width" />
				</array>
				<array name="fieldConfig">
					<structure name="~column~">
						<string name="format" />
						<string name="class" optional="yes" />
						<string name="style" optional="yes" />
						<boolean name="preview" comments="for [format=file] only" />
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
							$objectName   = ( substr($col, -3) == '_id' ) ? str_replace('_id', '', $col) : $col;
							$isManyToMany = ( isset($field['format']) and $field['format'] == 'many-to-many' );
							$isOneToMany  = ( isset($field['format']) and $field['format'] == 'one-to-many' );
							$isCheckbox   = ( isset($field['format']) and $field['format'] == 'checkbox' );
							$isFile       = ( isset($field['format']) and $field['format'] == 'file' );
							$isHidden     = ( isset($field['format']) and $field['format'] == 'hidden' );
							$isURL        = ( isset($field['format']) and $field['format'] == 'url' );
							$isWYSIWYG    = ( isset($field['format']) and $field['format'] == 'wysiwyg' );
							$isOutput     = ( isset($field['format']) and $field['format'] == 'output' );
							$isObject     = is_object($bean[$objectName]);
							// display : each field
							$fieldClass = array('col-'.$col);
							if ( $isHidden ) $fieldClass[] = 'd-none';
							if ( $colIndex > 0 ) $fieldClass[] = 'small text-muted';
							?><div class="<?php echo implode(' ', $fieldClass); ?>"><?php
								// preview : show thumbnail
								if ( !empty($bean[$col]) and !empty($field['preview']) ) :
									?><div><a
										title="<?php echo basename($bean[$col]); ?>"
										href="<?php echo $bean[$col]; ?>"
										target="_blank"
										data-fancybox
									><img
										alt="<?php echo basename($bean[$col]); ?>"
										src="<?php echo $bean[$col]; ?>"
										class="img-thumbnail mb-0 mt-1 <?php if ( !empty($bean->disabled) ) echo 'op-50'; ?>"
										style="max-width: 100%; <?php if ( !empty($field['style']) ) echo $field['style']; ?>"
									/></a></div><?php
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
										$output = !empty($field['options'][$val]) ? $field['options'][$val] : $val;
										?><div><?php echo $output; ?></div><?php
									endforeach;
								// one-to-many : show value according to options
								// many-to-many : show value according to options
								elseif ( $isOneToMany or $isManyToMany ) :
									$arr = $bean[ ( $isOneToMany ? 'own' : 'shared' ).ucfirst($objectName) ];
									foreach ( $arr as $associateBean ) :
										$val = $associateBean->id;
										$output = !empty($field['options'][$val]) ? $field['options'][$val] : "[{$col}={$val}]";
										?><div><?php echo $output; ?></div><?php
									endforeach;
								// dropdown : show value according to options
								elseif ( isset($field['options']) ) :
									$val = $bean[$col];
									$output = !empty($field['options'][$val]) ? $field['options'][$val] : "[{$col}={$val}]";
									?><div><?php echo $output; ?></div><?php
								// object : show alias/name/etc.
								elseif ( $isObject ) :
									?><div><?php
										if     ( !empty($bean[$objectName]->alias) ) echo $bean[$objectName]->alias;
										elseif ( !empty($bean[$objectName]->name ) ) echo $bean[$objectName]->name;
										elseif ( !empty($bean[$objectName]->title) ) echo $bean[$objectName]->title;
										else echo "[id={$bean[$objectName]->id}]";
									?></div><?php
								// url : show link
								elseif ( $isURL ) :
									?><div><a
										href="<?php echo $bean[$col]; ?>"
										style="word-break: break-all;"
										target="_blank"
									><?php echo $bean[$col]; ?></a></div><?php
								// wysiwyg : show html
								elseif ( $isWYSIWYG ) :
									?><div><?php echo $bean[$col]; ?></div><?php
								// output : show custom content
								elseif ( $isOutput ) :
									?><div><?php echo $field['value']; ?></div><?php
								// default : show field value
								else :
									?><div><?php echo nl2br($bean[$col]); ?></div><?php
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