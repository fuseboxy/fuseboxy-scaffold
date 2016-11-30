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
				<array name="editField">
					<structure name="~column~">
						<string name="format" />
						<!-- for [format=file] only -->
						<boolean name="preview" />
						<!-- for [format=checkbox] only -->
						<boolean name="many-to-many" />
					</structure>
				</array>
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/ ?>
<div id="<?php echo $scaffold['beanType']; ?>-row-<?php echo $bean->id; ?>" class="<?php echo $scaffold['beanType']; ?>-row scaffold-row">
	<table class="table table-hover table-condensed" style="margin-bottom: 0;">
		<tbody>
			<tr class="<?php if ( isset($bean->disabled) and $bean->disabled ) echo 'warning text-muted'; ?>">
				<?php foreach ( $scaffold['listField'] as $key => $val ) : ?>
					<?php $cols = explode('|', is_numeric($key) ? $val : $key); ?>
					<?php $colWidth = is_numeric($key) ? '' : $val; ?>
					<td class="col-<?php echo implode('-', $cols); ?>" width="<?php echo $colWidth; ?>;">
						<?php foreach ( $cols as $i => $col ) : ?>
							<?php
								$objectName = ( substr($col, -3) == '_id' ) ? str_replace('_id', '', $col) : $col;
								$isManyToMany = ( isset($scaffold['editField'][$col]['format']) and $scaffold['editField'][$col]['format'] == 'checkbox' and !empty($scaffold['editField'][$col]['many-to-many']) );
								$isOneToMany = ( isset($scaffold['editField'][$col]['format']) and $scaffold['editField'][$col]['format'] == 'checkbox' and empty($scaffold['editField'][$col]['many-to-many']) );
								$isObject = is_object($bean[$objectName]);
								$isFile = ( isset($scaffold['editField'][$col]['format']) and $scaffold['editField'][$col]['format'] == 'file' );
								$isHidden = ( isset($scaffold['editField'][$col]['format']) and $scaffold['editField'][$col]['format'] == 'hidden' );
							?>
							<div class="col-<?php echo $col; ?> <?php if ( $i != 0 ) echo 'small text-muted'; ?> <?php if ( $isHidden ) echo 'hidden'; ?>">
								<!-- file & preview : show thumbnail -->
								<?php if ( $isFile and !empty($bean[$col]) and !empty($scaffold['editField'][$col]['preview']) ) : ?>
									<div class="thumbnail" style="margin-bottom: 0; max-width: 100%; <?php if ( isset($bean->disabled) and $bean->disabled ) echo 'opacity: .5;'; ?>">
										<a href="<?php echo $bean[$col]; ?>" target="_blank"><img src="<?php echo $bean[$col]; ?>" alt="" /></a>
									</div>
								<!-- file : show link -->
								<?php elseif ( $isFile and !empty($bean[$col]) ) : ?>
									<a href="<?php echo $bean[$col]; ?>" target="_blank"><?php echo $bean[$col]; ?></a>
								<!-- many-to-many : show alias/name/etc. -->
								<?php elseif ( $isManyToMany ) : ?>
									<?php foreach ( $bean['shared'.ucfirst($objectName)] as $associateBean ) : ?>
										<div>
											<?php
												if ( isset($associateBean->alias) ) {
													echo $associateBean->alias;
												} elseif ( isset($associateBean->name) ) {
													echo $associateBean->name;
												} else {
													echo $associateBean->id;
												}
											?>
										</div>
									<?php endforeach; ?>
								<!-- one-to-many : show alias/name/etc. -->
								<?php elseif ( $isOneToMany ) : ?>
									<?php foreach ( $bean['own'.ucfirst($objectName)] as $associateBean ) : ?>
										<div>
											<?php
												if ( isset($associateBean->alias) ) {
													echo $associateBean->alias;
												} elseif ( isset($associateBean->name) ) {
													echo $associateBean->name;
												} else {
													echo $associateBean->id;
												}
											?>
										</div>
									<?php endforeach; ?>
								<!-- object : show alias/name/etc. -->
								<?php elseif ( $isObject ) : ?>
									<div>
										<?php
											if ( isset($bean[$objectName]->alias) ) {
												echo $bean[$objectName]->alias;
											} elseif ( isset($bean[$objectName]->name) ) {
												echo $bean[$objectName]->name;
											} else {
												echo $bean[$objectName]->id;
											}
										?>
									</div>
								<!-- show text -->
								<?php else : ?>
									<?php eval('echo nl2br($bean->'.$col.');'); ?>
									<?php //echo nl2br($bean[$col]); ?>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</td>
				<?php endforeach; ?>
				<td class="col-button text-nowrap">
					<div class="pull-right">
						<?php if ( isset($xfa['edit']) and  !( isset($xfa['disable']) and $bean->disabled ) ) : ?>
							<a
								href="<?php echo F::url("{$xfa['edit']}&id={$bean->id}"); ?>"
								class="btn btn-xs btn-default scaffold-btn-edit"
								<?php if ( $scaffold['editMode'] == 'modal' ) : ?>
									data-toggle="modal"
									data-target="#<?php echo $scaffold['beanType']; ?>-modal"
								<?php elseif ( $scaffold['editMode'] == 'inline' ) : ?>
									data-toggle="ajax-load"
									data-target="#<?php echo $scaffold['beanType']; ?>-row-<?php echo $bean->id; ?>"
								<?php endif; ?>
							><i class="fa fa-pencil"></i> Edit</a>
						<?php endif; ?>
						<?php if ( isset($xfa['enable']) and $bean->disabled ) : ?>
							<a
								href="<?php echo F::url("{$xfa['enable']}&id={$bean->id}"); ?>"
								class="btn btn-xs btn-success scaffold-btn-enable"
								data-toggle="ajax-load"
								data-target="#<?php echo $scaffold['beanType']; ?>-row-<?php echo $bean->id; ?>"
							><i class="fa fa-undo"></i> Enable</a>
						<?php endif; ?>
						<?php if ( isset($xfa['disable']) and !$bean->disabled ) : ?>
							<a
								href="<?php echo F::url("{$xfa['disable']}&id={$bean->id}"); ?>"
								class="btn btn-xs btn-warning scaffold-btn-disable"
								data-toggle="ajax-load"
								data-target="#<?php echo $scaffold['beanType']; ?>-row-<?php echo $bean->id; ?>"
							><i class="fa fa-trash-o"></i> Disable</a>
						<?php endif; ?>
						<?php if ( isset($xfa['delete']) ) : ?>
							<a
								href="<?php echo F::url("{$xfa['delete']}&id={$bean->id}"); ?>"
								class="btn btn-xs btn-danger scaffold-btn-delete"
								data-toggle="ajax-load"
								data-confirm="You cannot undo this.  Are you sure to delete?"
								data-target="#<?php echo $scaffold['beanType']; ?>-row-<?php echo $bean->id; ?>"
							><i class="fa fa-exclamation-triangle"></i> Delete</a>
						<?php endif; ?>
					</div>
				</td>
			</tr>
		</tbody>
	</table>
</div>