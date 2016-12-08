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
				<structure name="displayName" comments="display name at table header">
					<string name="~column~" />
				</structure>
			</structure>
			<structure name="$xfa">
				<string name="quick" />
				<string name="new" />
				<string name="sort" optional="yes" />
			</structure>
			<string name="sortField" scope="arguments" optional="yes" />
			<string name="sortRule" scope="arguments" optional="yes" comments="asc|desc" />
		</in>
		<out />
	</io>
</fusedoc>
*/ ?>
<div id="<?php echo $scaffold['beanType']; ?>-header" class="scaffold-header">
	<table class="table table-condensed" style="margin-bottom: 0;">
		<thead>
			<tr>
				<?php foreach ( $scaffold['listField'] as $key => $val ) : ?>
					<?php $cols = explode('|', is_numeric($key) ? $val : $key); ?>
					<?php $colWidth = is_numeric($key) ? '' : $val; ?>
					<th class="col-<?php echo implode('-', $cols); ?>" width="<?php echo $colWidth; ?>">
						<?php foreach ( $cols as $colIndex => $col ) : ?>
							<?php
								$isSortByThisField = ( isset($arguments['sortField']) and $arguments['sortField'] == $col );
								$isAscendingOrder = ( empty($arguments['sortRule']) or strtolower($arguments['sortRule']) == 'asc' );
								if ( isset($xfa['sort']) ) {
									$sortUrl = "{$xfa['sort']}&sortField={$col}";
									if ( $isSortByThisField and $isAscendingOrder ) $sortUrl .= '&sortRule=desc';
								}
								if ( isset($scaffold['displayName'][$col]) ) {
									$headerText = $scaffold['displayName'][$col];
								} elseif ( $col == 'id' ) {
									$headerText = strtoupper($col);
								} else {
									$headerText = ucwords(str_replace('_', ' ', $col));
								}
								if ( $isSortByThisField ) {
									$headerIcon = $isAscendingOrder ? 'fa fa-caret-up' : 'fa fa-caret-down';
									$headerText .= " <i class='{$headerIcon}'></i>";
								}
								if ( $colIndex > 0 and !empty($headerText) ) {
									$headerText = "<small class='text-muted'>/ {$headerText}</small>";
								}
								$headerText = "<span class='col-{$col} text-nowrap'>{$headerText}</span>";
							?>
							<?php if ( isset($xfa['sort']) ) : ?>
								<a href="<?php echo F::url($sortUrl); ?>" class="scaffold-btn-sort"><?php echo $headerText; ?></a>
							<?php else : ?>
								<?php echo $headerText; ?>
							<?php endif; ?>
						<?php endforeach; ?>
					</th>
				<?php endforeach; ?>
				<th class="col-button">
					<div class="pull-right">
						<?php if ( isset($xfa['quick']) ) : ?>
							<a
								href="<?php echo F::url($xfa['quick']); ?>"
								class="btn btn-xs btn-default scaffold-btn-quick-new"
								data-toggle="ajax-load"
								data-toggle-loading="none"
								data-toggle-mode="after"
								data-target="<?php echo "#{$scaffold['beanType']}-header"; ?>"
							><i class="fa fa-plus"></i> Quick</a>
						<?php endif; ?>
						<?php if ( isset($xfa['new']) ) : ?>
							<a
								href="<?php echo F::url($xfa['new']); ?>"
								class="btn btn-xs btn-info scaffold-btn-new"
								<?php if ( $scaffold['editMode'] == 'modal' ) : ?>
									data-toggle="modal"
									data-target="<?php echo "#{$scaffold['beanType']}-modal"; ?>"
									data-toggle-loading="none"
								<?php elseif ( $scaffold['editMode'] == 'inline' ) : ?>
									data-toggle="ajax-load"
									data-toggle-mode="after"
									data-target="<?php echo "#{$scaffold['beanType']}-header"; ?>"
									data-toggle-loading="none"
								<?php endif; ?>
							><i class="fa fa-plus"></i> New</a>
						<?php endif; ?>
					</div>
				</th>
			</tr>
		</thead>
	</table>
</div>