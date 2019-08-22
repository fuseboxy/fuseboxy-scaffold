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
				<structure name="fieldConfig">
					<structure name="~fieldName~">
						<string name="label" />
					</structure>
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
<div id="<?php echo $scaffold['beanType']; ?>-header" class="scaffold-header small">
	<table class="table table-sm table-borderless border-bottom mb-0">
		<thead>
			<tr>
				<?php foreach ( $scaffold['listField'] as $key => $val ) : ?>
					<?php $cols = array_map('trim', explode('|', is_numeric($key) ? $val : $key)); ?>
					<?php $colWidth = is_numeric($key) ? '' : $val; ?>
					<th scope="col" class="col-<?php echo implode('-', $cols); ?>" width="<?php echo $colWidth; ?>">
						<?php foreach ( $cols as $colIndex => $col ) : ?>
							<?php $isHR = ( strlen($col) and !strlen(str_replace('-', '', $col))); ?>
							<?php if ( !$isHR ) : ?>
								<?php
									$isSortByThisField = ( isset($arguments['sortField']) and $arguments['sortField'] == $col );
									$isAscendingOrder = ( empty($arguments['sortRule']) or strtolower($arguments['sortRule']) == 'asc' );
									if ( isset($xfa['sort']) ) {
										$sortUrl = "{$xfa['sort']}&sortField={$col}";
										if ( $isSortByThisField and $isAscendingOrder ) $sortUrl .= '&sortRule=desc';
									}
									$headerText = $scaffold['fieldConfig'][$col]['label'];
									if ( $isSortByThisField and !empty($headerText) ) {
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
							<?php endif; ?>
						<?php endforeach; ?>
					</th>
				<?php endforeach; ?>
				<th class="col-button">
					<div class="text-right"><?php include 'header.button.php'; ?></div>
				</th>
			</tr>
		</thead>
	</table>
</div>