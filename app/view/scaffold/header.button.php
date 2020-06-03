<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$xfa">
				<string name="quick" optional="yes" />
				<string name="new" optional="yes" />
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/

// quick button
if ( isset($xfa['quick']) ) :
	?><div class="btn-group"><?php
		// main button
		?><a
			href="<?php echo F::url($xfa['quick']); ?>"
			class="btn btn-sm py-0 px-1 btn-light scaffold-btn-quick"
			data-toggle="ajax-load"
			data-toggle-loading="none"
			data-toggle-mode="after"
			data-target="<?php echo "#{$scaffold['beanType']}-header"; ?>"
		><i class="fa fa-sort-amount-down"></i> Quick</a><?php
		// dropdown button
		?><button 
			type="button" 
			class="btn btn-sm py-0 px-1 btn-light dropdown-toggle dropdown-toggle-split scaffold-btn-quick-multiple" 
			data-toggle="dropdown" 
			aria-haspopup="true" 
			aria-expanded="false" 
		></button><?php
		// dropdown
		?><ul class="dropdown-menu dropdown-menu-right" style="min-width: 100%;"><?php
			for ( $i=2; $i<=9; $i++ ) :
				?><li class="dropdown-item text-center small"><?php echo $i; ?></li><?php
			endfor;
		?></ul>
	</div><?php
endif;


// new button
if ( isset($xfa['new']) ) :
	?><div class="btn-group"><?php
		// main button
		?><a
			href="<?php echo F::url($xfa['new']); ?>"
			class="btn btn-sm py-0 px-1 btn-info scaffold-btn-new"
			<?php if ( $scaffold['editMode'] == 'modal' ) : ?>
				data-toggle="ajax-modal"
				data-target="<?php echo "#{$scaffold['beanType']}-modal"; ?>"
				data-toggle-loading="none"
			<?php elseif ( $scaffold['editMode'] == 'inline' ) : ?>
				data-toggle="ajax-load"
				data-toggle-mode="after"
				data-target="<?php echo "#{$scaffold['beanType']}-header"; ?>"
				data-toggle-loading="none"
			<?php endif; ?>
		><i class="fa fa-plus"></i> New</a><?php
		// add multiple (when necessary)
		if ( !isset($xfa['quick']) ) :
			// dropdown button
			?><button 
				type="button" 
				class="btn btn-sm py-0 px-1 btn-info dropdown-toggle dropdown-toggle-split scaffold-btn-new-multiple" 
				data-toggle="dropdown" 
				aria-haspopup="true" 
				aria-expanded="false" 
			></button><?php
			// dropdown
			?><ul class="dropdown-menu dropdown-menu-right" style="min-width: 100%;"><?php
				for ( $i=2; $i<=9; $i++ ) :
					?><li class="dropdown-item text-center small"><?php echo $i; ?></li><?php
				endfor;
			?></ul><?php
		endif;
	?></div><?php
endif;


