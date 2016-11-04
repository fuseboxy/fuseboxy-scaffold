<?php /*
<fusedoc>
	<responsibilities>
		pagination needs {$arguments['pagination']['record_count']} at least
	</responsibilities>
	<io>
		<in>
			<number name="page" scope="$arguments" optional="yes" default="1" />
			<structure name="pagination" scope="$arguments">
				<number name="record_count" />
				<number name="record_per_page" optional="yes" default="10" />
				<number name="page_visible" optional="yes" default="999" />
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/ ?>
<?php if ( isset($arguments['pagination']) ) : ?>

	<?php
		// current page
		$arguments['page'] = !empty($arguments['page']) ? $arguments['page'] : 1;

		// param default
		$arguments['pagination']['record_per_page'] = !empty($arguments['pagination']['record_per_page']) ? $arguments['pagination']['record_per_page'] : 10;
		$arguments['pagination']['page_visible'] = !empty($arguments['pagination']['page_visible']) ? $arguments['pagination']['page_visible'] : 999;

		// calculate number of pages
		$page_count = ceil( $arguments['pagination']['record_count'] / $arguments['pagination']['record_per_page'] );

		// calculate visible pages
		$visible_start = max($arguments['page'] - ceil(($arguments['pagination']['page_visible']-1)/2), 1);
		if ( $visible_start > ($page_count - $arguments['pagination']['page_visible'] + 1) ) {
			$visible_start = $page_count - $arguments['pagination']['page_visible'] + 1;
		}
		$visible_start = max($visible_start, 1);

		$visible_end = min($arguments['page'] + ceil(($arguments['pagination']['page_visible']-1)/2), $page_count);
		if ( $visible_end < $arguments['pagination']['page_visible'] ) {
			$visible_end = $arguments['pagination']['page_visible'];
		}
		$visible_end = min($visible_end, $page_count);

		// calculate prev & next batch
		$prev_batch = max($arguments['page'] - $arguments['pagination']['page_visible'], 1);
		$next_batch = min($arguments['page'] + $arguments['pagination']['page_visible'], $page_count);
		if ( $prev_batch == $visible_start ) unset($prev_batch);
		if ( $next_batch == $visible_end   ) unset($next_batch);

		// preserve all url params except current page
		$url_without_page = $_SERVER['REQUEST_URI'];
		$url_without_page = str_ireplace("&page={$arguments['page']}", '', $url_without_page);
		$url_without_page = str_ireplace("?page={$arguments['page']}", '', $url_without_page);
	?>

	<?php if ( $visible_end > 1 ) : ?>
		<ul class="pagination">
			<!-- FIRST -->
			<?php if ( $arguments['page'] > 1 ) : ?>
				<li class="first"><a href="<?php echo "{$url_without_page}&amp;page=1"; ?>">&laquo; First</a></li>
			<?php else : ?>
				<li class="first disabled"><a>&laquo; First</a></li>
			<?php endif; ?>
			<!-- PREV -->
			<?php if ( $arguments['page'] > 1 ) : ?>
				<?php $prev = $arguments['page'] - 1; ?>
				<li class="prev"><a href="<?php echo "{$url_without_page}&amp;page={$prev}"; ?>">&lsaquo; Prev</a></li>
			<?php else : ?>
				<li class="prev disabled"><a>&lsaquo; Prev</a></li>
			<?php endif; ?>
			<!-- ... -->
			<?php if ( !empty($prev_batch) ) : ?>
				<li class="prev-batch">
					<a href="<?php echo "{$url_without_page}&amp;page={$prev_batch}"; ?>">...</a>
				</li>
			<?php endif; ?>
			<!-- PAGE -->
			<?php for ($i=$visible_start; $i<=$visible_end; $i++ ) : ?>
				<?php $selected = ( !empty($arguments['page']) and $arguments['page'] == $i ); ?>
				<li class="page-<?php echo $i; ?> <?php if ( $selected ) echo 'active'; ?>">
					<?php if ( $selected ) : ?>
						<a><?php echo $i; ?></a>
					<?php else : ?>
						<a href="<?php echo "{$url_without_page}&amp;page={$i}"; ?>"><?php echo $i; ?></a>
					<?php endif; ?>
				</li>
			<?php endfor; ?>
			<!-- ... -->
			<?php if ( !empty($next_batch) ) : ?>
				<li class="next-batch">
					<a href="<?php echo "{$url_without_page}&amp;page={$next_batch}"; ?>">...</a>
				</li>
			<?php endif; ?>
			<!-- NEXT -->
			<?php if ( $arguments['page'] < $page_count ) : ?>
				<?php $next = $arguments['page'] + 1; ?>
				<li class="next"><a href="<?php echo "{$url_without_page}&amp;page={$next}"; ?>">Next &rsaquo;</a></li>
			<?php else : ?>
				<li class="next disabled"><a>Next &rsaquo;</a></li>
			<?php endif; ?>
			<!-- LAST -->
			<?php if ( $arguments['page'] < $page_count ) : ?>
				<li class="last"><a href="<?php echo "{$url_without_page}&amp;page={$page_count}"; ?>">Last &raquo;</a></li>
			<?php else : ?>
				<li class="last disabled"><a>Last &raquo;</a></li>
			<?php endif; ?>
		</ul>
	<?php endif; ?>

<?php endif; ?>