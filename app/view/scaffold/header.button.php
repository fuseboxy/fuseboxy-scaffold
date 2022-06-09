<?php /*
<fusedoc>
	<io>
		<in>
			<string name="controller" scope="$fusebox" />
			<structure name="$xfa">
				<string name="new" optional="yes" />
				<string name="quick" optional="yes" />
			</structure>
			<structure name="$scaffold">
				<string name="editMode" comments="inline|modal|inline-modal|basic" />
			</structure>
		</in>
		<out>
			<number name="count" scope="url" oncondition="xfa.new|xfa.quick" />
		</out>
	</io>
</fusedoc>
*/

// quick button
if ( isset($xfa['quick']) ) :
	?><div class="btn-group"><?php
		// main button
		?><a
			href="<?php echo F::url($xfa['quick']); ?>"
			class="btn btn-xs btn-outline-info br-0 scaffold-btn-quick"
			data-toggle="ajax-load"
			data-loading="none"
			data-mode="after"
			data-target="#<?php echo F::command('controller'); ?>-header"
		><i class="fa fa-plus"></i><span class="ml-1">Quick</span></a><?php
		// dropdown button
		?><button 
			type="button" 
			class="btn btn-xs px-1 btn-outline-info bl-0 dropdown-toggle dropdown-toggle-split scaffold-btn-quick-multiple" 
			data-toggle="dropdown" 
			aria-haspopup="true" 
			aria-expanded="false" 
		></button><?php
		// dropdown
		?><ul class="dropdown-menu dropdown-menu-right" style="min-width: 100%;"><?php
			for ( $i=2; $i<=9; $i++ ) :
				?><li class="text-center small"><a 
					href="<?php echo F::url($xfa['quick'].'&count='.$i); ?>"
					class="dropdown-item"
					data-toggle="ajax-load"
					data-loading="none"
					data-mode="after"
					data-target="#<?php echo F::command('controller'); ?>-header"
				><?php echo $i; ?></a></li><?php
			endfor;
		?></ul>
	</div> <?php
endif;


// new button
if ( isset($xfa['new']) ) :
	?><div class="btn-group"><?php
		// main button
		?><a
			href="<?php echo F::url($xfa['new']); ?>"
			class="btn btn-xs px-1 btn-info scaffold-btn-new"
			<?php if ( $scaffold['editMode'] == 'modal' ) : ?>
				data-toggle="ajax-modal"
				data-target="#global-modal-<?php echo $scaffold['modalSize']; ?>"
				data-loading="none"
			<?php elseif ( in_array($scaffold['editMode'], ['inline','inline-modal']) ) : ?>
				data-toggle="ajax-load"
				data-mode="after"
				data-target="#<?php echo F::command('controller'); ?>-header"
				data-loading="none"
			<?php endif; ?>
		><i class="fa fa-plus"></i><span class="<?php echo ( $scaffold['editMode'] == 'inline' ) ? 'ml-1' : 'mx-1'; ?>">New</span></a><?php
		// add multiple (when necessary)
		if ( $scaffold['editMode'] == 'inline' ) :
			// dropdown button
			?><button 
				type="button" 
				class="btn btn-xs px-1 btn-info dropdown-toggle dropdown-toggle-split scaffold-btn-new-multiple" 
				data-toggle="dropdown" 
				aria-haspopup="true" 
				aria-expanded="false" 
			></button><?php
			// dropdown
			?><ul class="dropdown-menu dropdown-menu-right" style="min-width: 100%;"><?php
				for ( $i=2; $i<=9; $i++ ) :
					?><li class="text-center small"><a 
						href="<?php echo F::url($xfa['new'].'&count='.$i); ?>"
						class="dropdown-item"
						data-toggle="ajax-load"
						data-mode="after"
						data-target="#<?php echo F::command('controller'); ?>-header"
						data-loading="none"
					><?php echo $i; ?></a></li><?php
				endfor;
			?></ul><?php
		endif;
	?></div> <?php
endif;


