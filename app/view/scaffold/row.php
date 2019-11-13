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
						<!-- for [format=file] only -->
						<boolean name="preview" />
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
			<tr <?php if ( !empty($bean->disabled) ) : ?>class="table-active; op-50;"<?php endif; ?>><?php
				// go through each item in scaffold-listField config
				foreach ( $scaffold['listField'] as $key => $val ) :
					$cols = array_map('trim', explode('|', is_numeric($key) ? $val : $key));
					$colWidth = is_numeric($key) ? '' : $val;
					// display : field
					?><td class="col-<?php echo implode('-', $cols); ?>" width="<?php echo $colWidth; ?>;"><?php
						// go through each field
						foreach ( $cols as $i => $col ) :
							// determine field format
							$objectName   = ( substr($col, -3) == '_id' ) ? str_replace('_id', '', $col) : $col;
							$isManyToMany = ( isset($scaffold['fieldConfig'][$col]['format']) and $scaffold['fieldConfig'][$col]['format'] == 'many-to-many' );
							$isOneToMany  = ( isset($scaffold['fieldConfig'][$col]['format']) and $scaffold['fieldConfig'][$col]['format'] == 'one-to-many' );
							$isCheckbox   = ( isset($scaffold['fieldConfig'][$col]['format']) and $scaffold['fieldConfig'][$col]['format'] == 'checkbox' );
							$isObject     = is_object($bean[$objectName]);
							$isFile       = ( isset($scaffold['fieldConfig'][$col]['format']) and $scaffold['fieldConfig'][$col]['format'] == 'file' );
							$isHidden     = ( isset($scaffold['fieldConfig'][$col]['format']) and $scaffold['fieldConfig'][$col]['format'] == 'hidden' );
							$isWYSIWYG    = ( isset($scaffold['fieldConfig'][$col]['format']) and $scaffold['fieldConfig'][$col]['format'] == 'wysiwyg' );
							$isURL        = ( isset($scaffold['fieldConfig'][$col]['format']) and $scaffold['fieldConfig'][$col]['format'] == 'url' );
							$isHR         = ( strlen($col) and !strlen(str_replace('-', '', $col)) );
							// display : each field
							?><div class="col-<?php echo $col; ?> <?php if ( $i != 0 ) echo 'small text-muted'; ?> <?php if ( $isHidden ) echo 'd-none'; ?>"><?php
								// preview : show thumbnail
								if ( !empty($bean[$col]) and !empty($scaffold['fieldConfig'][$col]['preview']) ) :
									?><div><a
										title="<?php echo basename($bean[$col]); ?>"
										href="<?php echo $bean[$col]; ?>"
										target="_blank"
										data-fancybox
									><img
										alt="<?php echo basename($bean[$col]); ?>"
										src="<?php echo $bean[$col]; ?>"
										class="img-thumbnail mb-0 mt-1"
										style="max-width: 100%; <?php if ( isset($bean->disabled) and $bean->disabled ) echo 'opacity: .5;'; ?> <?php if ( !empty($scaffold['fieldConfig'][$col]['style']) ) echo $scaffold['fieldConfig'][$col]['style']; ?>"
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
									?><div><?php echo str_replace('|', '</div><div>', $bean[$col]); ?></div><?php
								// many-to-many : show alias/name/etc.
								elseif ( $isManyToMany ) :
									foreach ( $bean['shared'.ucfirst($objectName)] as $associateBean ) :
										?><div><?php
											if     ( isset($associateBean->alias) ) : echo $associateBean->alias;
											elseif ( isset($associateBean->name)  ) : echo $associateBean->name;
											else                                    : echo $associateBean->id;
											endif;
										?></div><?php
									endforeach;
								// one-to-many : show alias/name/etc.
								elseif ( $isOneToMany ) :
									foreach ( $bean['own'.ucfirst($objectName)] as $associateBean ) :
										?><div><?php
											if     ( isset($associateBean->alias) ) : echo $associateBean->alias;
											elseif ( isset($associateBean->name)  ) : echo $associateBean->name;
											else                                    : echo $associateBean->id;
											endif;
										?></div><?php
									endforeach;
								// object : show alias/name/etc.
								elseif ( $isObject ) :
									?><div><?php
										if     ( isset($bean[$objectName]->alias) ) : echo $bean[$objectName]->alias;
										elseif ( isset($bean[$objectName]->name)  ) : echo $bean[$objectName]->name;
										else                                        : echo $bean[$objectName]->id;
										endif;
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
								// horizontal line
								elseif ( $isHR ) :
									?><div><hr class="my-2 mx-0" /></div><?php
								// show text
								else :
									?><div><?php echo nl2br($bean[$col]); ?></div><?php
								endif;
							?></div><!--/.col-XXX--><?php
						endforeach; // foreach-cols
					?></td><!--/.col-XXX-YYY--><?php
				endforeach; // foreach-scaffold-listField
				// display : button
				?><td class="col-button text-nowrap text-right"><?php include 'row.button.php'; ?></td><?php
			?></tr>
		</tbody>
	</table>
</div><!--/.scaffold-row-->