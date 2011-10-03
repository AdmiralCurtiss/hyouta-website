<?php
if ( !isset( $session ) ) {
	die();
}

	require_once('nullify.php');
	
	if ( isset($_GET['show']) && is_numeric($_GET['show']) ) {
		$show_newsentries = (int)$_GET['show'];
	} else {
		$show_newsentries = 5;
	}
	if ( isset($_GET['page']) && is_numeric($_GET['page']) ) {
		$page_newsentries = (int)$_GET['page'];
	} else {
		$page_newsentries = 1;
	}
	
	if ( isset($_GET['newsid']) ) {
		$_GET['newsid'] = (int)$_GET['newsid'];
	}

	if ($session->logged_in && $session->user->is_admin() && isset($_POST['formaction']) && $_POST['formaction'] == 'create news entry') {
		$upload_dir = 'pic/';
		foreach ( $_FILES as $fileupload ) {
			if ( !$fileupload['error'] && $fileupload['size']>0 && $fileupload['tmp_name'] && is_uploaded_file($fileupload['tmp_name']) ) {
				$imgup_filename = $fileupload['name'];
				if ( file_exists($upload_dir.$imgup_filename) ) { // check if a file of the same name is already uploaded, if yes:
					if ( $imgup_extension = strrchr($imgup_filename, '.') ) { 				 // split filename into name and extension
						$imgup_efilename = substr($imgup_filename, 0, -strlen($imgup_extension));
					} else {
						$imgup_efilename = $imgup_filename;
						$imgup_extension = '';
					}
					$imgup_filename = $imgup_efilename.'0'.$imgup_extension; // add number at end of filename (before extension)
					$imgup_picnamenum = 0;
					while ( file_exists($upload_dir.$imgup_filename) ) { // and check again if it exists
						$imgup_picnamenum++;                             // if yes, add 1 to number and try again
						$imgup_filename = $imgup_efilename.$imgup_picnamenum.$imgup_extension;
					} 
				}
				
				if ( !copy($fileupload['tmp_name'], $upload_dir.$imgup_filename) ) {
					return 'Error while trying to copy file into userpicture folder, please contact the site administrator.<br>
							You may also want to include this to speed up the error resolving process:<br>'.var_dump($_FILES);
					$icon_filename = 'NULL';
				} else {
					$icon_filename = $upload_dir.$imgup_filename;
				}
				
			} else {
				$icon_filename = 'NULL';
			}
		}
		$news_content = str_replace(array("\r\n", "\n", "\r"), '<br />', $_POST['content']);
		$news_author = $session->user->username;
		
		$news_category = ereg_replace('[[:space:]]+', ' ', $_POST['category']);
		$news_category = str_replace(array(' , ', ", ", ' ,'), ',', $news_category);
		$news_category = trim($news_category,',');
		
		$sql = 'INSERT INTO news(title, text, author, date, lastedit, icon, category)
				VALUES ('.NULLify($_POST['title']).', '.NULLify($news_content).', '.NULLify($news_author).', "'.date('c').'", "'.date('c').'", '.NULLify($icon_filename).', '.NULLify(','.$news_category.',').');';
		if (!mysql_query($sql, $database)) {
			echo 'Failed to add news entry to database, please try again later.<br>MySQL Error: '.mysql_error();
		}
	}
	
	
	if ( isset($_POST['formaction']) && $_POST['formaction'] == 'comment' && isset($_GET['newsid']) && is_numeric($_GET['newsid']) ) {
		if ( isset($_POST['content']) && $_POST['content'] != '' ) {
			$comment_content = str_replace(array('<', '>'), array('&lt;', '&gt;'), $_POST['content']);
			$comment_content = str_replace(array("\r\n", "\n", "\r"), '<br />', $comment_content);
			$comment_number_query_result = mysql_query('SELECT comments FROM news WHERE id='.$_GET['newsid'], $database);
			$comment_number_array = mysql_fetch_assoc($comment_number_query_result);
			$comment_number = $comment_number_array['comments'];
			$comment_number++;
			
			$sql = 'INSERT INTO news_comments(news_id, comment_number, username, content, date)
					VALUES ("'.$_GET['newsid'].'", "'.$comment_number.'", "'.$session->user->username.'", "'.mysql_real_escape_string(stripslashes($comment_content)).'", "'.date('c').'");';
			if (!mysql_query($sql, $database)) {
				echo 'Failed to add comment to database, please try again later.<br>MySQL Error: '.mysql_error();
				return;
			}
			$sql = 'UPDATE news SET comments = "'.$comment_number.'" WHERE id = '.$_GET['newsid'].';';
			if (!mysql_query($sql, $database)) {
				echo 'Failed to edit comment amount in database. Should not affect anything really, only the shown amount of comments is now screwed up. Will be fixed manually when someone notices, or automatically the next time the cleanup script runs.';
			}
		} else {
			$comment_error = 'You need to enter a comment to comment!';
		}
	}

	
	if ( isset($_GET['newsid']) && is_numeric($_GET['newsid']) ) {
		$news_query_result = mysql_query('SELECT * FROM news WHERE available != 0 AND id='.$_GET['newsid'], $database);
	} else if ( isset($_GET['category']) ) {
		$news_query_result = mysql_query('SELECT * FROM news WHERE available != 0 AND UPPER(category) LIKE UPPER("%,'.mysql_real_escape_string($_GET['category']).',%") ORDER BY available DESC, id DESC', $database);
		$total_amount_of_news = mysql_query('SELECT COUNT(id) as "max" FROM news WHERE available != 0 AND UPPER(category) LIKE UPPER("%,'.mysql_real_escape_string($_GET['category']).',%") ORDER BY available DESC, id DESC', $database);
	} else {
		$news_query_result = mysql_query('SELECT * FROM news WHERE available != 0 ORDER BY available DESC, id DESC', $database);
		$total_amount_of_news = mysql_query('SELECT COUNT(id) as "max" FROM news WHERE available != 0', $database);
	}
	if ( isset($total_amount_of_news) ) {
		$total_amount_of_news = mysql_fetch_assoc($total_amount_of_news);
		$total_amount_of_news = $total_amount_of_news['max'];
	} else {
		$total_amount_of_news = 1;
	}
	
	$news_amount_display = 0;
	$news_amount_read = 0;
	$news_lastpage = false;
	while ($row = mysql_fetch_assoc($news_query_result)) {
		if ( $news_lastpage ) {
			$news_lastpage = false;
			break;
		}
		if ( $news_amount_read >= (($show_newsentries*$page_newsentries)-$show_newsentries) ) {
			$news[$news_amount_display]=$row;
			$news_amount_display++;
		}
		$news_amount_read++;
		if ($news_amount_read >= $show_newsentries*$page_newsentries) {
			$news_lastpage = true;
		}
	}
	if ( $news_amount_display < $show_newsentries ) {
		$news_lastpage = true;
	}
	if ( $news_amount_display == $news_amount_read ) {
		$news_firstpage = true;
	} else {
		$news_firstpage = false;
	}	

	$news_pageselect = '';
	if ( !$news_firstpage ) {
		$news_pageselect .= '<div class="prev_page"><a href="index.php?section=news';
		if ( isset($_GET['category']) ) {
			$news_pageselect .= '&category='.$_GET['category'];
		}
		$news_pageselect .= '&show='.$show_newsentries.'&page='.($page_newsentries-1).'">&lt;- Previous Page</a></div>';
	}
	if ( !$news_lastpage ) {
		$news_pageselect .= '<div class="next_page"><a href="index.php?section=news';
		if ( isset($_GET['category']) ) {
			$news_pageselect .= '&category='.$_GET['category'];
		}
		$news_pageselect .= '&show='.$show_newsentries.'&page='.($page_newsentries+1).'">Next Page -&gt;</a></div>';
	}
	if ( ( !isset($_GET['newsid']) || !is_numeric($_GET['newsid']) ) && ceil($total_amount_of_news/$show_newsentries) > 1 ) {
	-	$news_pageselect .= '<div class="select_page">';
		for ( $i = 1; $i <= ceil($total_amount_of_news/$show_newsentries); $i++ ) {
			if ( $i != $page_newsentries ) {
				$news_pageselect .= '<a href="index.php?section=news';
				if ( isset($_GET['category']) ) {
					$news_pageselect .= '&category='.$_GET['category'];
				}
				$news_pageselect .= '&show='.$show_newsentries.'&page='.$i.'">'.$i.'</a> ';
			} else {
				$news_pageselect .= '<span class="current_page">'.$i.'</span> ';
			}
		}
		$news_pageselect .= '</div>';
	}
	
	if (isset($news)) {
		echo $news_pageselect;
		foreach ($news as $newsentry) {
?>
<div class="newsentry_outside">
 <div class="corner_top-right">
 <div class="corner_top-left">
 <div class="corner_bottom-right">
 <div class="corner_bottom-left">
  <div class="blog">
   <div class="blogentry"><?php
   
	$newsentry_date_array[0] = $newsentry['date'];
	if ( $newsentry['date'] != $newsentry['lastedit'] ) {
		$newsentry_date_array[1] = $newsentry['lastedit'];
		$amount_read_date = 2;
	} else {
		$amount_read_date = 1;
	}
	for ($i = 0; $i < $amount_read_date; $i++) {
		$newsentry_date = strftime("%d", strtotime($newsentry_date_array[$i]));
		if ($newsentry_date == '01' || $newsentry_date == '21' || $newsentry_date == '31') {
			$newsentry_date_suffix = 'st';
		} else if ($newsentry_date == '02' || $newsentry_date == '22') {
			$newsentry_date_suffix = 'nd';
		} else if ($newsentry_date == '03' || $newsentry_date == '23') {
			$newsentry_date_suffix = 'rd';
		} else {
			$newsentry_date_suffix = 'th';
		}
		if ($newsentry_date[0] == '0') {
			$newsentry_date = $newsentry_date[1];
		}
		$newsentry_date = strftime("%A, $newsentry_date$newsentry_date_suffix %B %Y at %H:%M", strtotime($newsentry_date_array[$i]));
		$newsentry_date_formatted[$i] = $newsentry_date;
	}
	
	echo '<div class="date">by '.$newsentry['author'].' on '.$newsentry_date_formatted[0];
	if ( $amount_read_date == 2 ) {
		echo '<br>last edit on '.$newsentry_date_formatted[1];
	}
	if ($newsentry['comments'] != 1) {
		$comments_plural = 's';
	} else {
		$comments_plural = '';
	}
	echo '<br><i><a href="index.php?section=news&newsid='.$newsentry['id'].'">'.$newsentry['comments'].' comment'.$comments_plural.'</a></i>';
	echo '</div>';
	if ( $newsentry['icon'] != NULL && $newsentry['icon'] != 'NULL' ){
		echo '<div class="newsentry_icon"><img src="'.$newsentry['icon'].'"></div>';
	}
    echo '<div class="title"><a href="index.php?section=news&newsid='.$newsentry['id'].'">';
	echo $newsentry['title'].'</a></div>';
    echo '<div class="category">Category: ';
	$news_categories = explode(',',trim($newsentry['category'],','));
	$news_categories_string = '';
	foreach ($news_categories as $category) {
		$news_categories_string .= '<a href="index.php?section=news&category='.$category.'">'.$category.'</a>, ';
	}
	echo substr($news_categories_string, 0, -2);
	echo '</div>';
    echo '<div class="post">'.$newsentry['text'].'</div>';
	if ( $session->logged_in && $session->user->is_admin() ) {
		echo '<a href="index.php?section=edit&newsid='.$newsentry['id'].'">Edit News Entry</a>';
	}
 ?><div class="floatclear"></div></div>
  </div>
 </div>
 </div>
 </div>
 </div>
</div><?php
		}
		if ( isset($_GET['newsid']) && is_numeric($_GET['newsid']) ) {
			$comments_query_result = mysql_query('SELECT * FROM news_comments WHERE news_id='.$_GET['newsid'].' ORDER BY comment_id ASC', $database);
			echo '<table border="1" width="100%"><tr><th width="15%">Date/Time</th><th width="10%">User</th><th width="75%">Comment</th></tr>';
			while ($comment = mysql_fetch_assoc($comments_query_result)) {
				echo '<tr><td>'.$comment['date'].'</td><td><i>'.$comment['username'].'</i></td><td>'.$comment['content'].'</td></tr>';
			}
			echo '</table>';
		
			if ( $session->logged_in ) {
				if ( isset($comment_error) ) {
					echo '<div style="text-align: center;">An error occurred while posting your comment: '.$comment_error.'</div>';
				}
	?>
		<form action="index.php?section=news&newsid=<?php echo $_GET['newsid']; ?>" method="post">
		<table class="centered">
		<tr><td>Comment:</td><td><textarea name="content" cols="40" rows="4"></textarea></td></tr>
		<tr><td></td><td><input type="hidden" name="formaction" value="comment"/><input type="submit" value="Send Comment"/></td></tr></table>
		</form>
<?php	
			}
		}
		echo $news_pageselect;
	} else {
		echo 'No news to display.';
	}
?>
