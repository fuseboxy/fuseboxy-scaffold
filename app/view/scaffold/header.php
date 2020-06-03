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
<div id="<?php echo $scaffold['beanType']; ?>-header" class="scaffold-header bg-white small">
	<table class="table table-sm table-borderless border-bottom mb-0">
		<thead>
			<tr><?php
				// go through list config
				foreach ( $scaffold['listField'] as $key => $val ) :
					$cols = array_map('trim', explode('|', is_numeric($key) ? $val : $key));
					$colWidth = is_numeric($key) ? '' : $val;
					// header field
					?><th scope="col" class="col-<?php echo implode('-', $cols); ?>" width="<?php echo $colWidth; ?>"><?php
						foreach ( $cols as $colIndex => $col ) :
							$isHR = ( strlen($col) and !strlen(str_replace('-', '', $col)));
							if ( !$isHR ) :
								$isSortByThisField = ( isset($arguments['sortField']) and $arguments['sortField'] == $col );
								$isAscendingOrder = ( empty($arguments['sortRule']) or strtolower($arguments['sortRule']) == 'asc' );
								// prepare link (when necessary)
								if ( isset($xfa['sort']) ) :
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
								$wrapperClass[] = ( $colIndex > 0 and !empty($headerText) ) ? 'small text-muted' : 'text-dark';
								$headerText = '<span class="'.implode(' ', $wrapperClass).'">'.$headerText.'</span>';
								// wrap by link (when necessary)
								if ( isset($xfa['sort']) ) :
									$headerText = '<a href="'.F::url($sortUrl);.'" class="scaffold-btn-sort">'.$headerText.'</a>';
								else :
								// separator
								if ( $colIndex > 0 ) :
									?><small class="text-muted mx-1">/</small><?php
								endif;
								// display header
								echo $headerText;
							endif; // if-not-hr
						endforeach; // foreach-col
					?></th><?php
				endforeach; // foreach-list-field
				// button
				?><th class="col-button text-right"><?php include F::appPath('view/scaffold/header.button.php'); ?></th>
			</tr>
		</thead>
	</table>
</div>