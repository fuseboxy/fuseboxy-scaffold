<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$xfa">
				<string name="quick" optional="yes" />
				<string name="new" optional="yes" />
			</structure>
			<structure name="$scaffold">
				<string name="beanType" />
				<string name="editMode" comments="inline|modal|inline-modal|basic" />
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/

// quick button
if ( isset($xfa['quick']) ) :
	?><div class="btn-group ml-1"><?php
		// main button
		?><a
			href="<?php echo F::url($xfa['quick']); ?>"
			class="btn btn-sm py-0 px-1 btn-outline-info br-0 scaffold-btn-quick"
			data-toggle="ajax-load"
			data-loading="none"
			data-mode="after"
			data-target="<?php echo "#{$scaffold['beanType']}-header"; ?>"
		><i class="fa fa-bolt"></i><span class="ml-1">Quick</span></a><?php
		// dropdown button
		?><button 
			type="button" 
			class="btn btn-sm py-0 px-1 btn-outline-info bl-0 dropdown-toggle dropdown-toggle-split scaffold-btn-quick-multiple" 
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
	?><div class="btn-group ml-1"><?php
		// main button
		?><a
			href="<?php echo F::url($xfa['new']); ?>"
			class="btn btn-sm py-0 px-1 btn-info scaffold-btn-new"
			<?php if ( $scaffold['editMode'] == 'modal' ) : ?>
				data-toggle="ajax-modal"
				data-target="<?php echo "#{$scaffold['beanType']}-modal"; ?>"
				data-loading="none"
			<?php elseif ( in_array($scaffold['editMode'], ['inline','inline-modal']) ) : ?>
				data-toggle="ajax-load"
				data-mode="after"
				data-target="<?php echo "#{$scaffold['beanType']}-header"; ?>"
				data-loading="none"
			<?php endif; ?>
		><i class="fa fa-plus"></i><span class="ml-1 <?php if ( isset($xfa['quick']) ) echo 'mr-1'; ?>">New</span></a><?php
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


