<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$scaffold">
				<string name="beanType" />
				<string name="editMode" />
				<boolean name="stickyHeader" />
				<array name="allowSort">
					<string name="~fieldName~" />
				</array>
				<array name="listField" comments="key is pipe-delimited column list; value is column width">
					<string name="~columnList~" comments="column width" />
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
*/
$headerID = isset($scaffold['beanType']) ? "{$scaffold['beanType']}-header" : '';
?><div id="<?php echo $headerID; ?>" class="scaffold-header small <?php if ( !empty($scaffold['stickyHeader']) ) echo 'sticky'; ?>">
	<table class="table table-sm table-borderless border-bottom mb-0 bg-white">
		<thead>
			<tr><?php
				// go through list config
				foreach ( $scaffold['listField'] as $key => $val ) :
					$cols = array_map('trim', explode('|', is_numeric($key) ? $val : $key));
					$colWidth = is_numeric($key) ? '' : $val;
					// header field
					?><th scope="col" class="col-<?php echo implode('-', $cols); ?>" width="<?php echo $colWidth; ?>"><?php
						foreach ( $cols as $colIndex => $col ) :
							$isDisplayHeader = !empty($scaffold['fieldConfig'][$col]['label']);
							$isLine = ( strlen($col) and !strlen(str_replace('-', '', $col)));
							if ( !$isLine and $isDisplayHeader ) :
								$isSortByThisField = ( isset($arguments['sortField']) and $arguments['sortField'] == $col );
								$isAscendingOrder = ( empty($arguments['sortRule']) or strtolower($arguments['sortRule']) == 'asc' );
								// prepare link (when necessary)
								if ( isset($xfa['sort']) and in_array($col, $scaffold['allowSort']) ) :
									$sortUrl = "{$xfa['sort']}&sortField={$col}";
									if ( $isSortByThisField and $isAscendingOrder ) $sortUrl .= '&sortRule=desc';
								endif;
								// header text
								$headerText = $scaffold['fieldConfig'][$col]['label'];
								// show sort icon (when necessary)
								if ( $isSortByThisField and !empty($headerText) ) :
									$headerIcon = $isAscendingOrder ? 'fa fa-caret-up' : 'fa fa-caret-down';
									$headerText .= "<i class='{$headerIcon} ml-1'></i>";
								endif;
								// adjust header size
								$wrapperClass = array("col-{$col} text-nowrap");
								$wrapperClass[] = $colIndex ? 'small text-muted' : 'text-dark';
								$headerText = '<span class="'.implode(' ', $wrapperClass).'">'.$headerText.'</span>';
								// wrap by link (when necessary)
								if ( isset($xfa['sort']) and in_array($col, $scaffold['allowSort']) ) :
									$headerText = '<a href="'.F::url($sortUrl).'" class="scaffold-btn-sort '.( $colIndex ? 'text-muted' : 'text-dark' ).'">'.$headerText.'</a>';
								endif;
								// separator (when necessary)
								if ( $colIndex ) :
									?><small class="text-muted mx-1">/</small><?php
								endif;
								// display header
								echo $headerText;
							endif; // if-not-line
						endforeach; // foreach-col
					?></th><?php
				endforeach; // foreach-list-field
				// button
				?><th class="col-button text-right"><?php include F::appPath('view/scaffold/header.button.php'); ?></th>
			</tr>
		</thead>
	</table>
</div>