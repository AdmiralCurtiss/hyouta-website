<?php
if ( !isset( $session ) ) {
	die();
}

	if ( $session->user->is_admin() ) {
	?>	<form action="index.php?section=news" method="post" enctype="multipart/form-data">
		<table>
			<tr>
				<td>
					Title:
				</td>
				<td>
					<input type="text" name="title"/>
				</td>
			</tr>
			<tr>
				<td>
					Category:
				</td>
				<td>
					 <input type="text" name="category"/>
				</td>
			</tr>
			<tr>
				<td>
					Text:
				</td>
				<td>
					<textarea name="content" cols="40" rows="4"></textarea>
				</td>
			</tr>
			<tr>
				<td>
					<input type="hidden" name="MAX_FILE_SIZE" value="1024000"/>Icon:
				</td>
				<td>
					<input name="icon" type="file"/>
				</td>
			</tr>
			<tr>
				<td></td>
				<td>
					<input type="submit" name="formaction" value="create news entry"/>
				</td>
			</tr>
		</table>
		</form>
<?php	} else {
		include 'news.php';
}

?>