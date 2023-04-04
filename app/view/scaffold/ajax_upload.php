<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$xfa">
				<string name="ajaxUpload" comments="ajax-upload" />
			</structure>
		</in>
		<out>
			<file name="file" scope="form" oncondition="xfa.ajaxUpload" />
		</out>
	</io>
</fusedoc>
*/
?><form 
	class="scaffold-ajax-upload d-none"
	action="<?php echo F::url($xfa['ajaxUpload']); ?>"
	method="post"
	enctype="multipart/form-data"
	data-toggle="ajax-submit"
>
	<input type="file" name="file" />
	<button type="submit">Upload</button>
</form>