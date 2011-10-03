<?php
	require_once('nullify.php');
	if ( $session->user->is_admin() && isset($_POST['formaction']) && $_POST['formaction'] == 'edit news entry' ) {
		$news_content = str_replace(array("\r\n", "\n", "\r"), '<br />', $_POST['content']);
		
		$news_category = ereg_replace('[[:space:]]+', ' ', $_POST['category']);
		$news_category = str_replace(array(' , ', ", ", ' ,'), ',', $news_category);
		$news_category = trim($news_category,',');

		$sql = 'UPDATE news SET title = '.NULLify($_POST['title']).' , text = '.NULLify($news_content).' , lastedit = "'.
				date('c').'" , category = '.NULLify(','.$news_category.',').' WHERE id = '.$_GET['newsid'].';';
		if (!mysql_query($sql, $database)) {
			echo 'Failed to edit news entry in database, please try again later.<br>MySQL Error: '.mysql_error();
		}
	}

	if ( $session->user->is_admin() && isset($_GET['newsid']) ) {

	$news_query_result = mysql_query('SELECT * FROM news WHERE id='.mysql_real_escape_string($_GET['newsid']), $database);
	$news = mysql_fetch_assoc($news_query_result)

	?>	<form action="index.php?section=edit&newsid=<?php echo $_GET['newsid']; ?>" method="post" enctype="multipart/form-data">
		Title: <input type="text" name="title" value="<?php echo $news['title'] ?>"/><br>
		Category: <input type="text" name="category" value="<?php echo trim($news['category'],','); ?>"/><br>
		Text:<br>
		<textarea name="content" cols="40" rows="4"><?php echo $news['text'] ?></textarea><br>
		<input type="hidden" name="MAX_FILE_SIZE" value="1024000"/>
		Icon: <input name="icon" type="file"/><br>
		<input type="submit" name="formaction" value="edit news entry"/>
		</form>
<?php	} else {
		include 'news.php';
}

?>