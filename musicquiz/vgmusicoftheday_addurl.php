<?php
if ( !isset( $session ) ) {
	die();
}

function getYoutubeIdentifier( $str ) {
	$urlposition = strpos( $str, '?v=' );
	if ( $urlposition !== false ) {
		$urlposition += 3;
	} else {
		$urlposition = strpos( $str, '&v=' );
		if ( $urlposition !== false ) {
			$urlposition += 3;
		} else {
			$urlposition = strpos( $str, 'youtu.be/' );
			if ( $urlposition !== false ) {
				$urlposition += 9;
			} else {
				return null;
			}
		}
	}
	
	if ( $urlposition !== false ) {
		//remove till start, then search for the end of video identifyer
		$str = substr( $str, $urlposition );
		$urlposition = strpos( $str, '&' );
		if ( $urlposition !== false ) {
			$str = substr( $str, 0, $urlposition );
		}
		$urlposition = strpos( $str, '?' );
		if ( $urlposition !== false ) {
			$str = substr( $str, 0, $urlposition );
		}
		$urlposition = strpos( $str, '#' );
		if ( $urlposition !== false ) {
			$str = substr( $str, 0, $urlposition );
		}
		
		return $str;
	}
}

if ( $session->logged_in && $session->user->is_vgmusicoftheday() ) {
	$_GET['id'] = (int)$_GET['id'];
	if ( $_GET['id'] <= 0 ) die();

	require_once 'db.class.php';
	//require_once 'song.class.php';
	require_once 'url_container.class.php';

	$db = new db($database);
	
	if ( isset($_POST['formaction']) ) {
		$_POST['type'] = (int)$_POST['type'];
		if ( $_POST['type'] <= 0 ) die();
	
		if ( $_POST['formaction'] == 'Add File' ) {
			echo 'Uploading detected. ';
			$upload_success = false;
			if ( $_FILES["file"]["error"] > 0 ) {
				if ( $_FILES["file"]["error"] == '4' ) {
					echo 'No file uploaded.';
				} else {
					echo 'Error '.$_FILES["file"]["error"];
				}
			} else {
				if ( $_FILES["file"]["size"] > 100*1024*1024 ) {
					echo 'File too big, max filesize is 100 MB.';
				} else {
					$counter = 0;
					$filename = $_FILES["file"]["name"];
					$filename = str_replace( array('`',  '´',  'é', 'è', 'ê', 'á', 'à', 'â', 'ú', 'ù', 'û', 'ó', 'ò', 'ô', 'í', 'ì', 'î', 'ä', 'ö', 'ü', 'É', 'È', 'Ê', 'Á', 'À', 'Â', 'Ú', 'Ù', 'Û', 'Ó', 'Ò', 'Ô', 'Ì', 'Í', 'Î', 'Ä', 'Ö', 'Ü', '<', '>', ':', '"', '/', '\\', '|', '?', '*', '\0') ,
											 array('\'', '\'', 'e', 'e', 'e', 'a', 'a', 'a', 'u', 'u', 'u', 'o', 'o', 'o', 'i', 'i', 'i', 'a', 'o', 'u', 'E', 'E', 'E', 'A', 'A', 'A', 'U', 'U', 'U', 'O', 'O', 'O', 'I', 'I', 'I', 'A', 'O', 'U', '_', '_', '_', '_', '_', '_' , '_', '_', '_', '_'), $filename);
					$filename = preg_replace('/[^a-z0-9_\.\-[:space:]\(\)\[\]\{\}\&~]/i', '_', $filename); 
					$pos = strrpos($filename, '.');
					if ( $pos === false ) {
						$extension = '';
						$basename = $filename;
					} else {
						$extension = substr($filename, $pos);
						$basename = substr($filename, 0, $pos);
					}
					
					$filename = $basename.$extension;
					while (file_exists('dl/'.$filename)) {
						$filename = $basename.'_'.$counter.$extension;
						$counter++;
					}
					if ( !move_uploaded_file($_FILES["file"]["tmp_name"], 'dl/'.$filename) ) {
						echo 'File upload failed! ';
					} else {
						echo 'File uploaded! ';
						$upload_success = true;
					}
				}
			}
			
			if ( $upload_success ) {
				$urlid = $db->add_vgmusicoftheday_url( $_GET['id'], $_POST['type'], 'dl/'.$filename );
				if ( !$urlid ) {
					echo 'File uploaded successfully, but database entry failed! ';
				} else {
					echo 'Database updated! ';
				}
			}
		} else if ( $_POST['formaction'] == 'Add URL' ) {
			echo 'URL adding detected! ';
			
			if ( !isset( $_POST['url'] ) || trim($_POST['url']) == '' ) {
				echo 'No URL provided. ';
			} else {
				if ( isset($_POST['type']) ) {
					$url_replacement_successful = false;
					if ( $_POST['type'] == 1 ) {
						//convert url to youtube-v=-value
						$ytId = getYoutubeIdentifier($_POST['url']);
						if ( $ytId !== null ) {
							$_POST['url'] = 'http://www.youtube.com/watch?v='.$ytId;
							$url_replacement_successful = true;
						} else {
							echo 'Could not find Youtube video identifyer in the given URL!';
						}
					}
					
					if ( $_POST['type'] != 1 || $url_replacement_successful ) {
						$urlid = $db->add_vgmusicoftheday_url( $_GET['id'], $_POST['type'], trim($_POST['url']) );
						if ( !$urlid ) {
							echo 'Database entry failed! ';
						} else {
							echo 'Database updated! ';
						}
					}
				}
			}
		} else {
			echo '???';
		}
	}
	
	$current_song = $db->get_vgmusicoftheday_song($_GET['id']);
	
	echo '<div>'
		.'Adding a link for <b>'.$current_song->games.' - '.$current_song->names.' by '.$current_song->artist.'</b>, uploaded by '.$current_song->username.'.<br><br>The following links already exist: ';
	
	foreach ( $current_song->url as $url ) {
		if ( $url->has_icon() ) {
			echo '<a href="'.$url->url.'"><img src="'.$url->get_icon().'" title="'.$url->get_typename().'" border="0" /></a>&nbsp;';
		} else {
			echo '<a href="'.$url->url.'">['.$url->get_typename().']</a>&nbsp;';
		}
	}
	
	echo '</div><br>';
	echo '<div><a href="index.php?section=vgmotd-add-edit&id='.$_GET['id'].'">Edit this song\'s Data</a></div><br>';
	echo '<br>';
	
?><div>
<form action="index.php?section=vgmotd-urladd&id=<?php echo $_GET['id']; ?>" method="post" enctype="multipart/form-data">
<table>
	<tr>
		<td>URL:</td>
		<td><input type="text" name="url" size="120"/></td>
	</tr>
	<tr>
		<td>Type:</td>
		<td><select name="type"><?php
					$types = url_container::get_types(2);
					foreach ( $types as $type ) {
						echo '<option value="'.$type->typeid.'"><img src="'.$type->icon.'" /> '.$type->name.'</option>';
					}
				?></select></td>
	</tr>
	<tr>
		<td></td>
		<td><input type="submit" name="formaction" value="Add URL"/></td>
	</tr>
</table>
</form>

<form action="index.php?section=vgmotd-urladd&id=<?php echo $_GET['id']; ?>" method="post" enctype="multipart/form-data">
<table>
	<tr>
		<td>File:</td>
		<td><input name="file" type="file" size="100"/></td>
	</tr>
	<tr>
		<td>Type:</td>
		<td><select name="type"><?php
					$types = url_container::get_types(1);
					foreach ( $types as $type ) {
						echo '<option value="'.$type->typeid.'"><img src="'.$type->icon.'" /> '.$type->name.'</option>';
					}
				?></select></td>
	</tr>
	<tr>
		<td></td>
		<td><input type="submit" name="formaction" value="Add File"/></td>
	</tr>
</table>
</form>
</div>
<?php

} else {
	include 'main.php';
}
?>