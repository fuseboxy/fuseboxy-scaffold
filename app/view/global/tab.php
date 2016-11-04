<?php /*
<fusedoc>
	<description>
		this is tab sub-layout, which is supposed to be wrap by global-layout
	</description>
	<io>
		<in>
			<string name="content" scope="$layout" />
			<structure name="$tabLayout">
				<string name="style" comments="tab|pill" default="tab" />
				<string name="position" comments="left|right|top" default="left" />
				<string name="header" optional="yes" />
				<string name="footer" optional="yes" />
				<array name="nav">
					<structure name="+">
						<string name="name" />
						<string name="url" optional="yes" />
						<boolean name="active" optional="yes" />
						<structure name="button" optional="yes">
							<string name="~button name~" comments="url" />
						</structure>
						<array name="menus">
							<structure name="+">
								<string name="name" optional="yes" />
								<string name="url" optional="yes" />
								<string name="navHeader" optional="yes" />
								<list name="divider" optional="yes" delim=",">
									<string name="before|after" />
								</list>
								<boolean name="active" />
							</structure>
						</array>
					</structure>
				</array>
				<number name="navWidth" optional="yes" default="2" comments="12-base grid layout" />
			</structure>
			<!-- show below elements in tab-layout, then unset them to avoid showing in global layout again -->
			<string name="title" scope="$layout" optional="yes" />
			<array name="breadcrumb" scope="$arguments" optional="yes" />
			<string name="flash" scope="$arguments" optional="yes" />
			<array name="pagination" scope="$arguments" optional="yes" />
		</in>
		<out />
	</io>
</fusedoc>
*/

// layout config default
$tabLayout = isset($tabLayout) ? $tabLayout : array();
$tabLayout['style'] = isset($tabLayout['style']) ? $tabLayout['style'] : 'tab';
$tabLayout['position'] = isset($tabLayout['position']) ? $tabLayout['position'] : 'left';
$tabLayout['navWidth'] = isset($tabLayout['navWidth']) ? $tabLayout['navWidth'] : 2;


// display
include 'tab.body.php';


// clear layout items to avoid showing in global layout again
if ( isset($layout['title']) ) unset($layout['title']);
if ( isset($arguments['breadcrumb']) ) unset($arguments['breadcrumb']);
if ( isset($arguments['flash']) ) unset($arguments['flash']);
if ( isset($arguments['pagination']) ) unset($arguments['pagination']);